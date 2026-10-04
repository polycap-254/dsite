<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if (isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Don't allow deleting yourself
    if ($user_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'Cannot delete yourself']);
        exit();
    }
    
    // Delete user (cascading will handle related data)
    mysqli_query($conn, "DELETE FROM users WHERE id = $user_id");
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No user ID provided']);
}
?>