<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Get all site settings from admin
 */
function getSiteSettings() {
    global $conn;
    $sql = "SELECT * FROM admin_settings LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    // Return default settings if none exist
    return [
        'site_name' => 'LoveConnect',
        'logo_url' => 'assets/uploads/default-logo.png',
        'contact_email' => 'support@loveconnect.com',
        'help_line' => '+1-800-LOVE',
        'footer_text' => '© ' . date('Y') . ' LoveConnect. All rights reserved.',
        'facebook_url' => '',
        'twitter_url' => '',
        'instagram_url' => '',
        'about_us' => 'Connecting hearts worldwide.',
        'privacy_policy' => '',
        'terms_of_service' => ''
    ];
}

/**
 * Get user settings, create if not exists
 */
function getUserSettings($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    
    $sql = "SELECT * FROM user_settings WHERE user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    } else {
        // Create default settings
        $insertSql = "INSERT INTO user_settings (user_id) VALUES ($user_id)";
        if (mysqli_query($conn, $insertSql)) {
            return getUserSettings($user_id);
        }
    }
    
    // Return default settings if creation fails
    return [
        'user_id' => $user_id,
        'email_notifications' => 1,
        'push_notifications' => 1,
        'profile_visibility' => 'public',
        'show_online_status' => 1,
        'allow_messages_from' => 'everyone',
        'language' => 'en',
        'theme' => 'light'
    ];
}

/**
 * Get user by ID
 */
function getUserById($id) {
    global $conn;
    $id = (int)$id;
    $sql = "SELECT * FROM users WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Get user by username
 */
function getUserByUsername($username) {
    global $conn;
    $username = mysqli_real_escape_string($conn, $username);
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Get user by email
 */
function getUserByEmail($email) {
    global $conn;
    $email = mysqli_real_escape_string($conn, $email);
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Require login - redirects to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit();
    }
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require admin access
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../pages/index.php');
        exit();
    }
}

/**
 * Check if user is moderator or admin
 */
function isModerator() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'moderator');
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    global $conn;
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return mysqli_real_escape_string($conn, $data);
}

/**
 * Clean output for display
 */
function clean($data) {
    if (is_array($data)) {
        return array_map('clean', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Upload file with improved error handling
 */
function uploadFile($file, $target_dir = "../assets/uploads/") {
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0755, true)) {
            return ["success" => false, "message" => "Failed to create upload directory."];
        }
    }
    
    // Check if directory is writable
    if (!is_writable($target_dir)) {
        return ["success" => false, "message" => "Upload directory is not writable."];
    }
    
    // Check for upload errors
    if (!isset($file['error']) || is_array($file['error'])) {
        return ["success" => false, "message" => "Invalid file parameters."];
    }
    
    // Check error value
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ["success" => false, "message" => "No file was uploaded."];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ["success" => false, "message" => "File is too large. Maximum size is 10MB."];
        case UPLOAD_ERR_PARTIAL:
            return ["success" => false, "message" => "File was only partially uploaded."];
        default:
            return ["success" => false, "message" => "Unknown upload error occurred."];
    }
    
    // Check file size (10MB max)
    $maxFileSize = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $maxFileSize) {
        return ["success" => false, "message" => "File is too large. Maximum size is 10MB."];
    }
    
    // Get file information
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $file_mime = mime_content_type($file['tmp_name']);
    
    // Allowed file types
    $allowed_extensions = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'video' => ['mp4', 'avi', 'mov', 'webm', '3gp']
    ];
    
    $allowed_mimes = [
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'video' => ['video/mp4', 'video/avi', 'video/quicktime', 'video/webm', 'video/3gpp']
    ];
    
    $all_allowed_extensions = array_merge($allowed_extensions['image'], $allowed_extensions['video']);
    $all_allowed_mimes = array_merge($allowed_mimes['image'], $allowed_mimes['video']);
    
    // Validate extension
    if (!in_array($file_extension, $all_allowed_extensions)) {
        return ["success" => false, "message" => "Invalid file type. Allowed: " . implode(', ', $all_allowed_extensions)];
    }
    
    // Validate MIME type
    if (!in_array($file_mime, $all_allowed_mimes)) {
        return ["success" => false, "message" => "Invalid file format detected."];
    }
    
    // For images, verify it's actually an image
    if (in_array($file_mime, $allowed_mimes['image'])) {
        $image_info = getimagesize($file['tmp_name']);
        if ($image_info === false) {
            return ["success" => false, "message" => "File is not a valid image."];
        }
    }
    
    // Generate unique filename
    $file_name = time() . '_' . bin2hex(random_bytes(16)) . '.' . $file_extension;
    $target_file = $target_dir . $file_name;
    
    // Attempt to move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Set proper file permissions
        chmod($target_file, 0644);
        
        // Optimize image if it's an image file
        if (in_array($file_mime, $allowed_mimes['image'])) {
            optimizeImage($target_file, $target_file);
        }
        
        return [
            "success" => true, 
            "file_path" => "assets/uploads/" . $file_name,
            "file_name" => $file_name,
            "file_size" => filesize($target_file)
        ];
    } else {
        return ["success" => false, "message" => "Failed to save uploaded file."];
    }
}

/**
 * Optimize image for web
 */
/**
 * Optimize image for web (requires GD library)
 */
function optimizeImage($source, $destination, $max_width = 1200, $quality = 80) {
    // Check if GD library is available
    if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) {
        // GD not available, just return true to continue
        return true;
    }
    
    // Get image info
    $info = @getimagesize($source);
    if ($info === false) {
        return false;
    }
    
    $width = $info[0];
    $height = $info[1];
    $mime = $info['mime'];
    
    // Only resize if image is larger than max width
    if ($width <= $max_width) {
        return true; // No optimization needed
    }
    
    // Calculate new dimensions
    $new_width = $max_width;
    $new_height = (int)(($height / $width) * $new_width);
    
    // Create new image
    $new_image = @imagecreatetruecolor($new_width, $new_height);
    if (!$new_image) {
        return false;
    }
    
    // Handle transparency for PNG
    if ($mime == 'image/png') {
        @imagealphablending($new_image, false);
        @imagesavealpha($new_image, true);
    }
    
    // Load source image based on type
    $source_image = null;
    switch ($mime) {
        case 'image/jpeg':
            if (function_exists('imagecreatefromjpeg')) {
                $source_image = @imagecreatefromjpeg($source);
            }
            break;
        case 'image/png':
            if (function_exists('imagecreatefrompng')) {
                $source_image = @imagecreatefrompng($source);
            }
            break;
        case 'image/gif':
            if (function_exists('imagecreatefromgif')) {
                $source_image = @imagecreatefromgif($source);
            }
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $source_image = @imagecreatefromwebp($source);
            }
            break;
    }
    
    if (!$source_image) {
        @imagedestroy($new_image);
        return false;
    }
    
    // Resize
    @imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Save optimized image
    $success = false;
    switch ($mime) {
        case 'image/jpeg':
            if (function_exists('imagejpeg')) {
                $success = @imagejpeg($new_image, $destination, $quality);
            }
            break;
        case 'image/png':
            if (function_exists('imagepng')) {
                $png_quality = (int)(($quality / 100) * 9);
                $success = @imagepng($new_image, $destination, $png_quality);
            }
            break;
        case 'image/gif':
            if (function_exists('imagegif')) {
                $success = @imagegif($new_image, $destination);
            }
            break;
        case 'image/webp':
            if (function_exists('imagewebp')) {
                $success = @imagewebp($new_image, $destination, $quality);
            }
            break;
    }
    
    // Clean up
    @imagedestroy($source_image);
    @imagedestroy($new_image);
    
    return $success;
}

/**
 * Delete file if it exists
 */
function deleteFile($file_path) {
    $full_path = __DIR__ . '/../' . $file_path;
    if (file_exists($full_path) && is_file($full_path)) {
        return unlink($full_path);
    }
    return false;
}

/**
 * Create notification
 */
function createNotification($user_id, $from_user_id, $type, $reference_id, $message) {
    global $conn;
    
    // Don't notify yourself
    if ($user_id == $from_user_id) {
        return false;
    }
    
    $user_id = (int)$user_id;
    $from_user_id = $from_user_id ? (int)$from_user_id : 'NULL';
    $reference_id = $reference_id ? (int)$reference_id : 'NULL';
    $type = mysqli_real_escape_string($conn, $type);
    $message = mysqli_real_escape_string($conn, $message);
    
    $sql = "INSERT INTO notifications (user_id, from_user_id, type, reference_id, message) 
            VALUES ($user_id, $from_user_id, '$type', $reference_id, '$message')";
    
    return mysqli_query($conn, $sql);
}

/**
 * Get unread notifications count
 */
function getUnreadNotificationsCount($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    
    $sql = "SELECT COUNT(*) as count FROM notifications 
            WHERE user_id = $user_id AND is_read = 0";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    return 0;
}

/**
 * Get notifications with pagination
 */
function getNotifications($user_id, $limit = 20, $offset = 0) {
    global $conn;
    $user_id = (int)$user_id;
    $limit = (int)$limit;
    $offset = (int)$offset;
    
    $sql = "SELECT n.*, u.username, u.full_name, u.profile_pic 
            FROM notifications n 
            LEFT JOIN users u ON n.from_user_id = u.id 
            WHERE n.user_id = $user_id 
            ORDER BY n.created_at DESC 
            LIMIT $limit OFFSET $offset";
    
    $result = mysqli_query($conn, $sql);
    $notifications = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
    }
    
    return $notifications;
}

/**
 * Mark notification as read
 */
function markNotificationRead($notification_id, $user_id) {
    global $conn;
    $notification_id = (int)$notification_id;
    $user_id = (int)$user_id;
    
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = $notification_id AND user_id = $user_id";
    return mysqli_query($conn, $sql);
}

/**
 * Mark all notifications as read
 */
function markAllNotificationsRead($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = $user_id";
    return mysqli_query($conn, $sql);
}

/**
 * Delete notification
 */
function deleteNotification($notification_id, $user_id) {
    global $conn;
    $notification_id = (int)$notification_id;
    $user_id = (int)$user_id;
    
    $sql = "DELETE FROM notifications WHERE id = $notification_id AND user_id = $user_id";
    return mysqli_query($conn, $sql);
}

/**
 * Follow/unfollow user
 */
function followUser($follower_id, $following_id) {
    global $conn;
    $follower_id = (int)$follower_id;
    $following_id = (int)$following_id;
    
    // Can't follow yourself
    if ($follower_id == $following_id) {
        return ['action' => 'error', 'message' => 'Cannot follow yourself'];
    }
    
    // Check if already following
    $checkSql = "SELECT * FROM follows WHERE follower_id = $follower_id AND following_id = $following_id";
    $result = mysqli_query($conn, $checkSql);
    
    if (mysqli_num_rows($result) > 0) {
        // Unfollow
        $sql = "DELETE FROM follows WHERE follower_id = $follower_id AND following_id = $following_id";
        mysqli_query($conn, $sql);
        return 'unfollowed';
    } else {
        // Follow
        $sql = "INSERT INTO follows (follower_id, following_id) VALUES ($follower_id, $following_id)";
        if (mysqli_query($conn, $sql)) {
            createNotification($following_id, $follower_id, 'follow', $follower_id, 'started following you');
            return 'followed';
        }
        return 'error';
    }
}

/**
 * Get followers count
 */
function getFollowersCount($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    
    $sql = "SELECT COUNT(*) as count FROM follows WHERE following_id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    return 0;
}

/**
 * Get following count
 */
function getFollowingCount($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    
    $sql = "SELECT COUNT(*) as count FROM follows WHERE follower_id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    return 0;
}

/**
 * Check if user is following another user
 */
function isFollowing($follower_id, $following_id) {
    global $conn;
    $follower_id = (int)$follower_id;
    $following_id = (int)$following_id;
    
    $sql = "SELECT * FROM follows WHERE follower_id = $follower_id AND following_id = $following_id";
    $result = mysqli_query($conn, $sql);
    return mysqli_num_rows($result) > 0;
}

/**
 * Get followers list
 */
function getFollowers($user_id, $limit = 20, $offset = 0) {
    global $conn;
    $user_id = (int)$user_id;
    $limit = (int)$limit;
    $offset = (int)$offset;
    
    $sql = "SELECT u.id, u.username, u.full_name, u.profile_pic, u.bio, f.created_at as followed_since
            FROM follows f 
            JOIN users u ON f.follower_id = u.id 
            WHERE f.following_id = $user_id 
            ORDER BY f.created_at DESC 
            LIMIT $limit OFFSET $offset";
    
    $result = mysqli_query($conn, $sql);
    $followers = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $followers[] = $row;
        }
    }
    
    return $followers;
}

/**
 * Get following list
 */
function getFollowing($user_id, $limit = 20, $offset = 0) {
    global $conn;
    $user_id = (int)$user_id;
    $limit = (int)$limit;
    $offset = (int)$offset;
    
    $sql = "SELECT u.id, u.username, u.full_name, u.profile_pic, u.bio, f.created_at as followed_since
            FROM follows f 
            JOIN users u ON f.following_id = u.id 
            WHERE f.follower_id = $user_id 
            ORDER BY f.created_at DESC 
            LIMIT $limit OFFSET $offset";
    
    $result = mysqli_query($conn, $sql);
    $following = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $following[] = $row;
        }
    }
    
    return $following;
}

/**
 * Block user
 */
function blockUser($blocker_id, $blocked_id, $reason = '') {
    global $conn;
    $blocker_id = (int)$blocker_id;
    $blocked_id = (int)$blocked_id;
    $reason = mysqli_real_escape_string($conn, $reason);
    
    // Check if already blocked
    $checkSql = "SELECT * FROM blocked_users WHERE blocker_id = $blocker_id AND blocked_id = $blocked_id";
    $result = mysqli_query($conn, $checkSql);
    
    if (mysqli_num_rows($result) > 0) {
        // Unblock
        $sql = "DELETE FROM blocked_users WHERE blocker_id = $blocker_id AND blocked_id = $blocked_id";
        mysqli_query($conn, $sql);
        return 'unblocked';
    } else {
        // Block
        $sql = "INSERT INTO blocked_users (blocker_id, blocked_id, reason) VALUES ($blocker_id, $blocked_id, '$reason')";
        mysqli_query($conn, $sql);
        
        // Also unfollow
        mysqli_query($conn, "DELETE FROM follows WHERE follower_id = $blocker_id AND following_id = $blocked_id");
        mysqli_query($conn, "DELETE FROM follows WHERE follower_id = $blocked_id AND following_id = $blocker_id");
        
        return 'blocked';
    }
}

/**
 * Check if user is blocked
 */
function isBlocked($user_id, $other_user_id) {
    global $conn;
    $user_id = (int)$user_id;
    $other_user_id = (int)$other_user_id;
    
    $sql = "SELECT * FROM blocked_users WHERE blocker_id = $other_user_id AND blocked_id = $user_id";
    $result = mysqli_query($conn, $sql);
    return mysqli_num_rows($result) > 0;
}

/**
 * Like/Unlike post
 */
function likePost($user_id, $post_id) {
    global $conn;
    $user_id = (int)$user_id;
    $post_id = (int)$post_id;
    
    // Check if already liked
    $check_sql = "SELECT * FROM likes WHERE user_id = $user_id AND post_id = $post_id";
    $result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($result) > 0) {
        // Unlike
        mysqli_query($conn, "DELETE FROM likes WHERE user_id = $user_id AND post_id = $post_id");
        mysqli_query($conn, "UPDATE posts SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = $post_id");
        return 'unliked';
    } else {
        // Like
        mysqli_query($conn, "INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)");
        mysqli_query($conn, "UPDATE posts SET likes_count = likes_count + 1 WHERE id = $post_id");
        
        // Notify post owner
        $post_query = mysqli_query($conn, "SELECT user_id FROM posts WHERE id = $post_id");
        $post = mysqli_fetch_assoc($post_query);
        
        if ($post && $post['user_id'] != $user_id) {
            createNotification($post['user_id'], $user_id, 'like', $post_id, 'liked your post');
        }
        
        return 'liked';
    }
}

/**
 * Share post
 */
function sharePost($user_id, $post_id) {
    global $conn;
    $user_id = (int)$user_id;
    $post_id = (int)$post_id;
    
    // Check if already shared
    $check_sql = "SELECT * FROM shares WHERE user_id = $user_id AND post_id = $post_id";
    $result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($result) == 0) {
        mysqli_query($conn, "INSERT INTO shares (user_id, post_id) VALUES ($user_id, $post_id)");
        mysqli_query($conn, "UPDATE posts SET shares_count = shares_count + 1 WHERE id = $post_id");
        
        // Notify post owner
        $post_query = mysqli_query($conn, "SELECT user_id FROM posts WHERE id = $post_id");
        $post = mysqli_fetch_assoc($post_query);
        
        if ($post && $post['user_id'] != $user_id) {
            createNotification($post['user_id'], $user_id, 'share', $post_id, 'shared your post');
        }
        
        return true;
    }
    return false;
}

/**
 * Format time as "time ago"
 */
function timeAgo($timestamp) {
    if (!$timestamp) return '';
    
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    
    if ($time_difference < 0) {
        return 'Just now';
    }
    
    $seconds = $time_difference;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
    
    if ($seconds <= 60) {
        return "Just now";
    } else if ($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } else if ($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } else if ($days <= 7) {
        return ($days == 1) ? "Yesterday" : "$days days ago";
    } else if ($weeks <= 4.3) {
        return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago";
    } else if ($months <= 12) {
        return ($months == 1) ? "1 month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "1 year ago" : "$years years ago";
    }
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Get post by ID
 */
function getPostById($post_id) {
    global $conn;
    $post_id = (int)$post_id;
    
    $sql = "SELECT p.*, u.username, u.full_name, u.profile_pic
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.id = $post_id";
    
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    return null;
}

/**
 * Get posts with filters
 */
function getPosts($filters = [], $limit = 20, $offset = 0) {
    global $conn;
    $limit = (int)$limit;
    $offset = (int)$offset;
    
    $where = ["1=1"];
    
    if (isset($filters['user_id'])) {
        $user_id = (int)$filters['user_id'];
        $where[] = "p.user_id = $user_id";
    }
    
    if (isset($filters['privacy'])) {
        $privacy = mysqli_real_escape_string($conn, $filters['privacy']);
        $where[] = "p.privacy = '$privacy'";
    }
    
    if (isset($filters['media_type'])) {
        $media_type = mysqli_real_escape_string($conn, $filters['media_type']);
        $where[] = "p.media_type = '$media_type'";
    }
    
    $where_clause = implode(' AND ', $where);
    
    $sql = "SELECT p.*, u.username, u.full_name, u.profile_pic
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            WHERE $where_clause 
            ORDER BY p.created_at DESC 
            LIMIT $limit OFFSET $offset";
    
    $result = mysqli_query($conn, $sql);
    $posts = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $posts[] = $row;
        }
    }
    
    return $posts;
}

/**
 * Delete post
 */
function deletePost($post_id, $user_id) {
    global $conn;
    $post_id = (int)$post_id;
    $user_id = (int)$user_id;
    
    // Check if user owns the post or is admin
    $post = getPostById($post_id);
    
    if ($post && ($post['user_id'] == $user_id || isAdmin())) {
        // Delete media files
        if ($post['media_urls']) {
            $media = json_decode($post['media_urls'], true);
            if (is_array($media)) {
                foreach ($media as $file) {
                    deleteFile($file);
                }
            }
        }
        
        mysqli_query($conn, "DELETE FROM posts WHERE id = $post_id");
        return true;
    }
    return false;
}

/**
 * Search users
 */
function searchUsers($query, $limit = 20) {
    global $conn;
    $query = mysqli_real_escape_string($conn, $query);
    $limit = (int)$limit;
    
    $sql = "SELECT id, username, full_name, profile_pic, bio, location
            FROM users 
            WHERE account_status = 'active' 
            AND (username LIKE '%$query%' OR full_name LIKE '%$query%' OR bio LIKE '%$query%')
            LIMIT $limit";
    
    $result = mysqli_query($conn, $sql);
    $users = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }
    
    return $users;
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = ['message' => $message, 'type' => $type];
    header("Location: $url");
    exit();
}

/**
 * Get flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Display flash message
 */
function displayFlashMessage() {
    $message = getFlashMessage();
    if ($message) {
        $type = htmlspecialchars($message['type']);
        $text = htmlspecialchars($message['message']);
        echo "<div class='alert alert-$type'>$text</div>";
    }
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function isStrongPassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return strlen($password) >= 8 
        && preg_match('/[A-Z]/', $password) 
        && preg_match('/[a-z]/', $password) 
        && preg_match('/[0-9]/', $password);
}

/**
 * Calculate age from birth date
 */
function calculateAge($birth_date) {
    $birth = new DateTime($birth_date);
    $today = new DateTime('today');
    return $birth->diff($today)->y;
}

/**
 * Check if user can message another user
 */
function canMessage($from_user_id, $to_user_id) {
    $settings = getUserSettings($to_user_id);
    
    if ($settings['allow_messages_from'] == 'none') {
        return false;
    }
    
    if ($settings['allow_messages_from'] == 'friends') {
        return isFollowing($to_user_id, $from_user_id) && isFollowing($from_user_id, $to_user_id);
    }
    
    return !isBlocked($from_user_id, $to_user_id);
}

/**
 * Log activity
 */
function logActivity($user_id, $action, $details = '') {
    global $conn;
    $user_id = (int)$user_id;
    $action = mysqli_real_escape_string($conn, $action);
    $details = mysqli_real_escape_string($conn, $details);
    $ip = mysqli_real_escape_string($conn, $_SERVER['REMOTE_ADDR'] ?? '');
    
    $sql = "INSERT INTO activity_logs (user_id, action, details, ip_address) 
            VALUES ($user_id, '$action', '$details', '$ip')";
    
    return mysqli_query($conn, $sql);
}

/**
 * Get user's online status
 */
function isUserOnline($user_id) {
    global $conn;
    $user_id = (int)$user_id;
    
    $sql = "SELECT is_online, last_seen FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['is_online'] == 1;
    }
    return false;
}

/**
 * Update user's online status
 */
function updateOnlineStatus($user_id, $status = 1) {
    global $conn;
    $user_id = (int)$user_id;
    $status = (int)$status;
    
    $sql = "UPDATE users SET is_online = $status, last_seen = NOW() WHERE id = $user_id";
    return mysqli_query($conn, $sql);
}
?>