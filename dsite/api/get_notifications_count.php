<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (isLoggedIn()) {
    $count = getUnreadNotificationsCount($_SESSION['user_id']);
    echo json_encode(['count' => $count, 'success' => true]);
} else {
    echo json_encode(['count' => 0, 'success' => false, 'error' => 'Not logged in']);
}
?>