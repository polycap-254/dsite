<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if (isset($_GET['q'])) {
    $query = sanitize($_GET['q']);
    
    $users_sql = "SELECT id, username, full_name, profile_pic 
                  FROM users 
                  WHERE id != {$_SESSION['user_id']} 
                  AND account_status = 'active' 
                  AND (username LIKE '%$query%' OR full_name LIKE '%$query%')
                  ORDER BY full_name 
                  LIMIT 20";
    $users_result = mysqli_query($conn, $users_sql);
    
    $html = '';
    
    if (mysqli_num_rows($users_result) > 0) {
        while ($user = mysqli_fetch_assoc($users_result)) {
            $html .= '<div class="user-item" onclick="startDirectChat(' . $user['id'] . ')">';
            $html .= '<img src="../' . $user['profile_pic'] . '" alt="Avatar" class="user-avatar">';
            $html .= '<div class="user-info">';
            $html .= '<span class="user-name">' . ($user['full_name'] ?: $user['username']) . '</span>';
            $html .= '<span class="user-username">@' . $user['username'] . '</span>';
            $html .= '</div></div>';
        }
    } else {
        $html = '<p class="empty-text">No users found</p>';
    }
    
    echo json_encode(['success' => true, 'html' => $html]);
} else {
    echo json_encode(['success' => false, 'error' => 'No query provided']);
}
?>