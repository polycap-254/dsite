<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if (isset($_POST['user_id']) && isset($_POST['status'])) {
    $user_id = (int)$_POST['user_id'];
    $status = sanitize($_POST['status']);
    
    $allowed_statuses = ['active', 'suspended', 'deactivated'];
    if (in_array($status, $allowed_statuses)) {
        mysqli_query($conn, "UPDATE users SET account_status = '$status' WHERE id = $user_id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
}
?>