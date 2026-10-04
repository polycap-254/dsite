<?php
require_once __DIR__ . '/../config/database.php';
session_start();

// Update user's online status if logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sql = "UPDATE users SET is_online = 0, last_seen = NOW() WHERE id = $user_id";
    mysqli_query($conn, $sql);
}

// Destroy session
session_destroy();

// Redirect to login
header('Location: login.php?logged_out=1');
exit();
?>