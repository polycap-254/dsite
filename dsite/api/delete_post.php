<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if (isset($_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if user owns the post or is admin
    $check_sql = "SELECT user_id FROM posts WHERE id = $post_id";
    $result = mysqli_query($conn, $check_sql);
    $post = mysqli_fetch_assoc($result);
    
    if ($post && ($post['user_id'] == $user_id || isAdmin())) {
        mysqli_query($conn, "DELETE FROM posts WHERE id = $post_id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No post_id provided']);
}
?>