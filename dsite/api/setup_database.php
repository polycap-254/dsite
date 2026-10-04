<?php
// Run this file once to set up the database

// Termux-specific MySQL connection
// Try different connection methods
$host = 'localhost';
$user = 'root';
$password = ''; // or your MariaDB password

// Method 1: Try with socket path (common in Termux)
$socket = '/data/data/com.termux/files/usr/tmp/mysql.sock';

// Attempt connection with socket
$conn = mysqli_connect($host, $user, $password, '', 0, $socket);

// If that fails, try without socket
if (!$conn) {
    // Try standard connection
    $conn = mysqli_connect($host, $user, $password);
    
    // If still failing, try with 127.0.0.1 instead of localhost
    if (!$conn) {
        $conn = mysqli_connect('127.0.0.1', $user, $password);
    }
}

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . "\n
    \nTroubleshooting steps:
    1. Make sure MariaDB is installed: pkg install mariadb
    2. Start MariaDB: mysqld_safe &
    3. Check if socket exists: ls -la /data/data/com.termux/files/usr/tmp/mysql.sock
    4. Or set a password: mysqladmin -u root password 'yourpassword'
    ");
}

echo "Connected successfully!\n";

// Rest of your database setup code continues here...
// Create database
$sql = "CREATE DATABASE IF NOT EXISTS dating_site CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!mysqli_query($conn, $sql)) {
    die("Error creating database: " . mysqli_error($conn));
}

mysqli_select_db($conn, 'dating_site');

// Set SQL mode to avoid strict timestamp issues
mysqli_query($conn, "SET sql_mode = ''");

// Create tables
$tables = [
    // Admin Settings Table
    "CREATE TABLE IF NOT EXISTS admin_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        site_name VARCHAR(100) DEFAULT 'LoveConnect',
        logo_url VARCHAR(255) DEFAULT 'assets/uploads/default-logo.png',
        contact_email VARCHAR(100) DEFAULT 'support@loveconnect.com',
        help_line VARCHAR(20) DEFAULT '+1-800-LOVE',
        footer_text TEXT,
        facebook_url VARCHAR(255),
        twitter_url VARCHAR(255),
        instagram_url VARCHAR(255),
        about_us TEXT,
        privacy_policy TEXT,
        terms_of_service TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    // Users Settings Table
    "CREATE TABLE IF NOT EXISTS user_settings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT UNIQUE,
        email_notifications TINYINT(1) DEFAULT 1,
        push_notifications TINYINT(1) DEFAULT 1,
        profile_visibility ENUM('public','friends','private') DEFAULT 'public',
        show_online_status TINYINT(1) DEFAULT 1,
        allow_messages_from ENUM('everyone','friends','none') DEFAULT 'everyone',
        language VARCHAR(10) DEFAULT 'en',
        theme ENUM('light','dark') DEFAULT 'light',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // Users Table
    "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        bio TEXT,
        profile_pic VARCHAR(255) DEFAULT 'assets/uploads/default-avatar.png',
        cover_pic VARCHAR(255),
        gender ENUM('male','female','non-binary','prefer-not-to-say', 'other') DEFAULT 'prefer-not-to-say',
        interested_in ENUM('male','female','both','all') DEFAULT 'both',
        birth_date DATE,
        location VARCHAR(100),
        occupation VARCHAR(100),
        education VARCHAR(100),
        interests TEXT,
        is_online TINYINT(1) DEFAULT 0,
        last_seen DATETIME,
        is_verified TINYINT(1) DEFAULT 0,
        verification_token VARCHAR(64),
        role ENUM('user','admin','moderator') DEFAULT 'user',
        account_status ENUM('active','suspended','deactivated') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // Follows System
    "CREATE TABLE IF NOT EXISTS follows (
        follower_id INT NOT NULL,
        following_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (follower_id, following_id)
    )",

    // Posts Table
    "CREATE TABLE IF NOT EXISTS posts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        content TEXT,
        media_urls TEXT,
        media_type ENUM('text','image','video') DEFAULT 'text',
        privacy ENUM('public','followers','private') DEFAULT 'public',
        likes_count INT DEFAULT 0,
        comments_count INT DEFAULT 0,
        shares_count INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // Likes Table
    "CREATE TABLE IF NOT EXISTS likes (
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, post_id)
    )",

    // Comments Table
    "CREATE TABLE IF NOT EXISTS comments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        parent_comment_id INT DEFAULT NULL,
        content TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // Shares Table
    "CREATE TABLE IF NOT EXISTS shares (
        id INT PRIMARY KEY AUTO_INCREMENT,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        shared_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // Chat Rooms
    "CREATE TABLE IF NOT EXISTS chat_rooms (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100),
        type ENUM('direct','group') DEFAULT 'direct',
        created_by INT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // Chat Participants
    "CREATE TABLE IF NOT EXISTS chat_participants (
        room_id INT NOT NULL,
        user_id INT NOT NULL,
        joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (room_id, user_id)
    )",

    // Messages
    "CREATE TABLE IF NOT EXISTS messages (
        id INT PRIMARY KEY AUTO_INCREMENT,
        room_id INT NOT NULL,
        sender_id INT NOT NULL,
        message TEXT,
        file_url VARCHAR(255),
        message_type ENUM('text','image','video','file') DEFAULT 'text',
        is_read TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // Notifications
    "CREATE TABLE IF NOT EXISTS notifications (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        from_user_id INT,
        type ENUM('like','comment','reply','follow','share','message','system'),
        reference_id INT,
        message TEXT,
        is_read TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // Live Streams
    "CREATE TABLE IF NOT EXISTS live_streams (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        stream_key VARCHAR(64) UNIQUE,
        title VARCHAR(200),
        description TEXT,
        thumbnail_url VARCHAR(255),
        is_active TINYINT(1) DEFAULT 0,
        viewers_count INT DEFAULT 0,
        started_at DATETIME,
        ended_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )",

    // Blocked Users
    "CREATE TABLE IF NOT EXISTS blocked_users (
        blocker_id INT NOT NULL,
        blocked_id INT NOT NULL,
        reason TEXT,
        blocked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (blocker_id, blocked_id)
    )"
];

// Execute all table creations
$success = true;
$errors = [];

foreach ($tables as $sql) {
    if (!mysqli_query($conn, $sql)) {
        $errors[] = "Error: " . mysqli_error($conn);
        $success = false;
    }
}

if ($success) {
    // Insert default admin settings
    $defaultSettings = "INSERT INTO admin_settings (site_name, contact_email, help_line, footer_text) 
                       SELECT 'LoveConnect', 'support@loveconnect.com', '+1-800-LOVE', '© 2024 LoveConnect. All rights reserved.'
                       WHERE NOT EXISTS (SELECT 1 FROM admin_settings)";
    mysqli_query($conn, $defaultSettings);
    
    // Create admin user if not exists
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $createAdmin = "INSERT INTO users (username, email, password_hash, full_name, role, is_verified) 
                    SELECT 'admin', 'admin@loveconnect.com', '$adminPassword', 'Site Admin', 'admin', TRUE
                    WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin')";
    mysqli_query($conn, $createAdmin);
    
    echo "\n✅ Database setup completed successfully!\n";
    echo "Admin credentials:\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
    echo "Email: admin@loveconnect.com\n";
    
} else {
    echo "\n❌ Setup failed!\n";
    foreach ($errors as $error) {
        echo $error . "\n";
    }
}

// Close connection
mysqli_close($conn);
?>