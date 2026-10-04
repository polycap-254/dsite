<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$settings = getSiteSettings();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . $settings['site_name'] : $settings['site_name']; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Main Header -->
    <header class="main-header">
        <div class="header-container">
            <!-- Logo and Site Name -->
            <div class="logo-section">
                <a href="index.php" class="logo-link">
                    <img src="../<?php echo $settings['logo_url'] ? $settings['logo_url'] : 'assets/uploads/default-logo.png'; ?>" 
                         alt="<?php echo $settings['site_name']; ?>" 
                         class="site-logo">
                    <span class="site-name"><?php echo $settings['site_name']; ?></span>
                </a>
            </div>
            
            <!-- Navigation Menu -->
            <nav class="main-nav">
                <ul class="nav-list">
                    <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Home
                    </a></li>
                    
                    <?php if (isLoggedIn()): ?>
                    <li><a href="posts.php" class="<?php echo $current_page == 'posts.php' ? 'active' : ''; ?>">
                        <i class="fas fa-newspaper"></i> Posts
                    </a></li>
                    
                    <li><a href="chat.php" class="<?php echo $current_page == 'chat.php' ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i> Chats
                        <span class="badge chat-badge" style="display:none;">0</span>
                    </a></li>
                    
                    <li><a href="notifications.php" class="<?php echo $current_page == 'notifications.php' ? 'active' : ''; ?>">
                        <i class="fas fa-bell"></i> Notifications
                        <?php 
                        $unread_count = getUnreadNotificationsCount($_SESSION['user_id']);
                        if ($unread_count > 0): 
                        ?>
                        <span class="badge notification-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                    
                    <li><a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> Profile
                    </a></li>
                    
                    <li><a href="go_live.php" class="<?php echo $current_page == 'go_live.php' ? 'active' : ''; ?>">
                        <i class="fas fa-video"></i> Go Live
                    </a></li>
                    <?php endif; ?>
                    
                    <?php if (isLoggedIn() && isAdmin()): ?>
                    <li><a href="../admin/dashboard.php">
                        <i class="fas fa-cog"></i> Admin
                    </a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <!-- User Menu -->
            <div class="user-section">
                <?php if (isLoggedIn()): ?>
                <div class="user-menu">
                    <img src="../<?php echo isset($_SESSION['profile_pic']) ? $_SESSION['profile_pic'] : 'assets/uploads/default-avatar.png'; ?>" 
                         alt="Profile" class="user-avatar">
                    <span class="username"><?php echo $_SESSION['username']; ?></span>
                    <div class="dropdown-menu">
                        <a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a>
                        <a href="profile.php#settings"><i class="fas fa-cog"></i> Settings</a>
                        <?php if (isAdmin()): ?>
                        <a href="../admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Admin Panel</a>
                        <?php endif; ?>
                        <a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
                <?php else: ?>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="signup.php" class="btn btn-primary">Sign Up</a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Back Button -->
            <button onclick="history.back()" class="btn-back" title="Go Back">
                <i class="fas fa-arrow-left"></i> Back
            </button>
            
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>
    <!-- Mobile Bottom Navigation -->
    <?php if (isLoggedIn()): ?>
    <nav class="mobile-bottom-nav" aria-label="Mobile navigation">
        <a href="../pages/index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>

        <a href="../pages/index.php" class="<?php echo $current_page === 'index.php' ? '' : ''; ?>">
            <i class="fas fa-compass"></i>
            <span>Discover</span>
        </a>

        <a href="../pages/chat.php" class="<?php echo $current_page === 'chat.php' ? 'active' : ''; ?>">
            <i class="fas fa-comment-dots"></i>
            <span>Chats</span>
            <?php if (isset($unread_messages) && $unread_messages > 0): ?>
                <span class="mobile-nav-badge"><?php echo $unread_messages; ?></span>
            <?php endif; ?>
        </a>

        <a href="../pages/notifications.php" class="<?php echo $current_page === 'notifications.php' ? 'active' : ''; ?>">
            <i class="fas fa-bell"></i>
            <span>Alerts</span>
            <?php
            $mobile_unread = getUnreadNotificationsCount($_SESSION['user_id']);
            if ($mobile_unread > 0):
            ?>
                <span class="mobile-nav-badge"><?php echo $mobile_unread > 99 ? '99+' : $mobile_unread; ?></span>
            <?php endif; ?>
        </a>

        <a href="../pages/profile.php" class="<?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>
    <?php endif; ?>

    <!-- Main Content Container -->
    <main class="main-content">    
    <!-- Main Content Container -->
    <main class="main-content">
