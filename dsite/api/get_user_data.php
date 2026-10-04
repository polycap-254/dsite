<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $user = getUserById($user_id);
    
    if ($user) {
        $html = '
        <form onsubmit="return updateUser(event, ' . $user_id . ')">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="' . htmlspecialchars($user['full_name']) . '" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="' . htmlspecialchars($user['email']) . '" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" class="form-control" rows="3">' . htmlspecialchars($user['bio']) . '</textarea>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="' . htmlspecialchars($user['location']) . '" class="form-control">
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="user" ' . ($user['role'] == 'user' ? 'selected' : '') . '>User</option>
                    <option value="moderator" ' . ($user['role'] == 'moderator' ? 'selected' : '') . '>Moderator</option>
                    <option value="admin" ' . ($user['role'] == 'admin' ? 'selected' : '') . '>Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Account Status</label>
                <select name="account_status" class="form-control">
                    <option value="active" ' . ($user['account_status'] == 'active' ? 'selected' : '') . '>Active</option>
                    <option value="suspended" ' . ($user['account_status'] == 'suspended' ? 'selected' : '') . '>Suspended</option>
                    <option value="deactivated" ' . ($user['account_status'] == 'deactivated' ? 'selected' : '') . '>Deactivated</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Update User</button>
        </form>';
        
        echo json_encode(['success' => true, 'html' => $html]);
    } else {
        echo json_encode(['success' => false, 'error' => 'User not found']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No user ID provided']);
}
?>