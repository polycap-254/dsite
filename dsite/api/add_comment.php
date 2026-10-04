<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if (isset($_POST['post_id']) && isset($_POST['comment_content'])) {
    $post_id = (int)$_POST['post_id'];
    $content = sanitize($_POST['comment_content']);
    $parent_id = isset($_POST['parent_comment_id']) ? (int)$_POST['parent_comment_id'] : null;
    $user_id = $_SESSION['user_id'];
    
    $sql = "INSERT INTO comments (post_id, user_id, parent_comment_id, content) 
            VALUES ($post_id, $user_id, " . ($parent_id ? $parent_id : "NULL") . ", '$content')";
    
    if (mysqli_query($conn, $sql)) {
        // Update comment count
        mysqli_query($conn, "UPDATE posts SET comments_count = comments_count + 1 WHERE id = $post_id");
        
        // Get updated count
        $count_query = mysqli_query($conn, "SELECT comments_count FROM posts WHERE id = $post_id");
        $count = mysqli_fetch_assoc($count_query);
        
        // Send notification
        $post_query = mysqli_query($conn, "SELECT user_id FROM posts WHERE id = $post_id");
        $post = mysqli_fetch_assoc($post_query);
        
        if ($post && $post['user_id'] != $user_id) {
            $type = $parent_id ? 'reply' : 'comment';
            createNotification($post['user_id'], $user_id, $type, $post_id, 'commented on your post');
        }
        
        echo json_encode([
            'success' => true,
            'comments_count' => $count['comments_count']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to add comment']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
}
?>