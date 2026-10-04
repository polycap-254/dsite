<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

if (isset($_POST['room_id'])) {
    $room_id = (int)$_POST['room_id'];
    $user_id = $_SESSION['user_id'];
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $file_url = null;
    $message_type = 'text';
    
    // Verify user is participant
    $verify_sql = "SELECT * FROM chat_participants WHERE room_id = $room_id AND user_id = $user_id";
    $verify_result = mysqli_query($conn, $verify_sql);
    
    if (mysqli_num_rows($verify_result) == 0) {
        echo json_encode(['success' => false, 'error' => 'Not a participant in this chat']);
        exit();
    }
    
    // Handle file upload
    if (isset($_FILES['chat_file']) && $_FILES['chat_file']['error'] === UPLOAD_ERR_OK) {
        $upload_result = uploadFile($_FILES['chat_file']);
        
        if ($upload_result['success']) {
            $file_url = $upload_result['file_path'];
            $ext = strtolower(pathinfo($_FILES['chat_file']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $message_type = 'image';
            } elseif (in_array($ext, ['mp4', 'avi', 'mov', 'webm'])) {
                $message_type = 'video';
            } else {
                $message_type = 'file';
            }
            
            // If no message text, set filename as message
            if (empty($message)) {
                $message = $_FILES['chat_file']['name'];
            }
        } else {
            echo json_encode(['success' => false, 'error' => $upload_result['message']]);
            exit();
        }
    }
    
    // Validate that there's either text or file
    if (empty($message) && !$file_url) {
        echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
        exit();
    }
    
    // Sanitize message
    $message = mysqli_real_escape_string($conn, $message);
    $file_url_sql = $file_url ? "'" . mysqli_real_escape_string($conn, $file_url) . "'" : "NULL";
    
    // Insert message
    $sql = "INSERT INTO messages (room_id, sender_id, message, file_url, message_type) 
            VALUES ($room_id, $user_id, '$message', $file_url_sql, '$message_type')";
    
    if (mysqli_query($conn, $sql)) {
        $message_id = mysqli_insert_id($conn);
        
        // Get the inserted message for response
        $msg_sql = "SELECT m.*, u.username, u.full_name, u.profile_pic 
                    FROM messages m 
                    JOIN users u ON m.sender_id = u.id 
                    WHERE m.id = $message_id";
        $msg_result = mysqli_query($conn, $msg_sql);
        $msg_data = mysqli_fetch_assoc($msg_result);
        
        // Send notifications to other participants
        $participants_sql = "SELECT user_id FROM chat_participants WHERE room_id = $room_id AND user_id != $user_id";
        $participants_result = mysqli_query($conn, $participants_sql);
        
        if ($participants_result) {
            $notification_message = $message_type == 'image' ? 'sent you an image' : 
                                   ($message_type == 'file' ? 'sent you a file' : 'sent you a message');
            
            while ($participant = mysqli_fetch_assoc($participants_result)) {
                createNotification($participant['user_id'], $user_id, 'message', $room_id, $notification_message);
            }
        }
        
        echo json_encode([
            'success' => true,
            'message_id' => $message_id,
            'message' => $msg_data
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No room_id provided']);
}
?>