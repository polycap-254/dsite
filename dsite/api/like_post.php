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
        'error' => 'You must be logged in'
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

// Get post_id
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

if ($post_id <= 0) {
    echo json_encode([
        'success' => false, 
        'error' => 'Invalid post ID'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if post exists
$post_check = mysqli_query($conn, "SELECT id, user_id FROM posts WHERE id = $post_id");
if (mysqli_num_rows($post_check) == 0) {
    echo json_encode([
        'success' => false, 
        'error' => 'Post not found'
    ]);
    exit();
}

$post_data = mysqli_fetch_assoc($post_check);

// Check if already liked
$check_like = mysqli_query($conn, 
    "SELECT * FROM likes WHERE user_id = $user_id AND post_id = $post_id"
);

if (mysqli_num_rows($check_like) > 0) {
    // Unlike
    $delete_sql = "DELETE FROM likes WHERE user_id = $user_id AND post_id = $post_id";
    
    if (mysqli_query($conn, $delete_sql)) {
        // Update likes count
        mysqli_query($conn, "UPDATE posts SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = $post_id");
        
        // Get updated count
        $count_result = mysqli_query($conn, "SELECT likes_count FROM posts WHERE id = $post_id");
        $count_data = mysqli_fetch_assoc($count_result);
     
        echo json_encode([
            'success' => true,
            'action' => 'unliked',
            'likes_count' => (int)$count_data['likes_count'],
            'message' => 'Post unliked'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Failed to unlike post'
        ]); 
    }
} else {
    // Like
    $insert_sql = "INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)";
    
    if (mysqli_query($conn, $insert_sql)) {
        // Update likes count
        mysqli_query($conn, "UPDATE posts SET likes_count = likes_count + 1 WHERE id = $post_id");
        
        // Get updated count
        $count_result = mysqli_query($conn, "SELECT likes_count FROM posts WHERE id = $post_id");
        $count_data = mysqli_fetch_assoc($count_result);
        
        // Create notification if not liking own post
        if ($post_data['user_id'] != $user_id) {
            createNotification($post_data['user_id'], $user_id, 'like', $post_id, 'liked your post');
        }
        
        echo json_encode([
            'success' => true,
            'action' => 'liked',
            'likes_count' => (int)$count_data['likes_count'],
            'message' => 'Post liked'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'error' => 'Failed to like post'
        ]); 
    }
}

mysqli_close($conn);
?>