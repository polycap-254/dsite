<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if (isset($_GET['room_id'])) {
    $room_id = (int)$_GET['room_id'];
    $user_id = $_SESSION['user_id'];
    
    // Verify user is participant
    $verify_sql = "SELECT * FROM chat_participants WHERE room_id = $room_id AND user_id = $user_id";
    if (mysqli_num_rows(mysqli_query($conn, $verify_sql)) > 0) {
        $messages_sql = "SELECT m.*, u.username, u.full_name, u.profile_pic 
                        FROM messages m 
                        JOIN users u ON m.sender_id = u.id 
                        WHERE m.room_id = $room_id 
                        ORDER BY m.created_at ASC 
                        LIMIT 100";
        $messages_result = mysqli_query($conn, $messages_sql);
        
        $html = '';
        while ($msg = mysqli_fetch_assoc($messages_result)) {
            $is_mine = $msg['sender_id'] == $user_id;
            
            $html .= '<div class="message ' . ($is_mine ? 'message-sent' : 'message-received') . '">';
            
            if (!$is_mine) {
                $html .= '<img src="../' . $msg['profile_pic'] . '" alt="Avatar" class="message-avatar">';
            }
            
            $html .= '<div class="message-content">';
            
            if ($msg['message_type'] == 'text') {
                $html .= '<div class="message-bubble"><p>' . htmlspecialchars($msg['message']) . '</p></div>';
            } elseif ($msg['message_type'] == 'image') {
                $html .= '<div class="message-bubble">';
                $html .= '<img src="../' . $msg['file_url'] . '" alt="Image" class="message-image" onclick="openLightbox(\'../' . $msg['file_url'] . '\')">';
                if ($msg['message']) {
                    $html .= '<p>' . htmlspecialchars($msg['message']) . '</p>';
                }
                $html .= '</div>';
            } elseif ($msg['message_type'] == 'file') {
                $html .= '<div class="message-bubble message-file">';
                $html .= '<i class="fas fa-file"></i>';
                $html .= '<a href="../' . $msg['file_url'] . '" download>' . ($msg['message'] ?: 'Download File') . '</a>';
                $html .= '</div>';
            }
            
            $html .= '<span class="message-time">' . date('H:i', strtotime($msg['created_at'])) . '</span>';
            $html .= '</div>';
            
            if ($is_mine) {
                $html .= '<img src="../' . $_SESSION['profile_pic'] . '" alt="Avatar" class="message-avatar">';
            }
            
            $html .= '</div>';
        }
        
        // Mark messages as read
        mysqli_query($conn, "UPDATE messages SET is_read = 1 WHERE room_id = $room_id AND sender_id != $user_id");
        
        echo json_encode(['success' => true, 'html' => $html]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Not a participant']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No room_id provided']);
}
?>