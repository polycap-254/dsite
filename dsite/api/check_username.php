<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (isset($_GET['username'])) {
    $username = sanitize($_GET['username']);
    $sql = "SELECT id FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    
    echo json_encode(['exists' => mysqli_num_rows($result) > 0]);
} else {
    echo json_encode(['error' => 'No username provided']);
}
?>