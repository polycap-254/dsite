<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if (isset($_POST['id'])) {
    $notif_id = (int)$_POST['id'];
    $user_id = $_SESSION['user_id'];
    
    mysqli_query($conn, "DELETE FROM notifications WHERE id = $notif_id AND user_id = $user_id");
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No id provided']);
}
?>