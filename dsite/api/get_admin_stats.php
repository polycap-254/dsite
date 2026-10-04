<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false]);
    exit();
}

$stats = [];

// Total users
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$stats['total_users'] = mysqli_fetch_assoc($result)['count'];

// Online users
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE is_online = 1");
$stats['online_now'] = mysqli_fetch_assoc($result)['count'];

// Active streams
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM live_streams WHERE is_active = 1");
$stats['active_streams'] = mysqli_fetch_assoc($result)['count'];

// Posts today
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM posts WHERE DATE(created_at) = CURDATE()");
$stats['posts_today'] = mysqli_fetch_assoc($result)['count'];

echo json_encode(['success' => true, 'stats' => $stats]);
?>