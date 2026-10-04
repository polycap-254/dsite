<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false, 
        'error' => 'You must be logged in to follow users'
    ]);
    exit();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid request method'
    ]);
    exit();
}

// Get user_id to follow
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if ($user_id <= 0) {
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid user ID'
    ]);
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Can't follow yourself
if ($user_id == $current_user_id) {
    echo json_encode([
        'success' => false, 
        'error' => 'You cannot follow yourself'
    ]);
    exit();
}

// Check if user exists
$check_user = mysqli_query($conn, "SELECT id, username FROM users WHERE id = $user_id");
if (mysqli_num_rows($check_user) == 0) {
    echo json_encode([
        'success' => false, 
        'error' => 'User not found'
    ]);
    exit();
}

// Check if already following
$check_follow = mysqli_query($conn, 
    "SELECT * FROM follows WHERE follower_id = $current_user_id AND following_id = $user_id"
);

if (mysqli_num_rows($check_follow) > 0) {
    // Unfollow
    $delete_sql = "DELETE FROM follows WHERE follower_id = $current_user_id AND following_id = $user_id";
    
    if (mysqli_query($conn, $delete_sql)) {
        // Get updated followers count
        $count_result = mysqli_query($conn, 
            "SELECT COUNT(*) as count FROM follows WHERE following_id = $user_id"
        );
        $count_data = mysqli_fetch_assoc($count_result);
        
        echo json_encode([
            'success' => true,
            'action' => 'unfollowed',
            'followers_count' => (int)$count_data['count'],
            'message' => 'Unfollowed successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Failed to unfollow user: ' . mysqli_error($conn)
        ]);
    }
} else {
    // Follow
    $insert_sql = "INSERT INTO follows (follower_id, following_id) VALUES ($current_user_id, $user_id)";
    
    if (mysqli_query($conn, $insert_sql)) {
        // Get updated followers count
        $count_result = mysqli_query($conn, 
            "SELECT COUNT(*) as count FROM follows WHERE following_id = $user_id"
        );
        $count_data = mysqli_fetch_assoc($count_result);
        
        // Create notification
        $notification_sql = "INSERT INTO notifications (user_id, from_user_id, type, reference_id, message) 
                            VALUES ($user_id, $current_user_id, 'follow', $current_user_id, 'started following you')";
        mysqli_query($conn, $notification_sql);
        
        echo json_encode([
            'success' => true,
            'action' => 'followed',
            'followers_count' => (int)$count_data['count'],
            'message' => 'Followed successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Failed to follow user: ' . mysqli_error($conn)
        ]);
    }
}

mysqli_close($conn);
?>