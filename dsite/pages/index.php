<?php
$page_title = "Home";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get unread notifications count for sidebar
$unread_count = isLoggedIn() ? getUnreadNotificationsCount($_SESSION['user_id']) : 0;

// Get featured users (newest members)
$featured_sql = "SELECT id, username, full_name, profile_pic, bio, location, gender, created_at 
                FROM users 
                WHERE account_status = 'active' 
                AND role = 'user' 
                ORDER BY created_at DESC 
                LIMIT 8";
$featured_result = mysqli_query($conn, $featured_sql);

// Get online users
$online_sql = "SELECT id, username, full_name, profile_pic, location 
              FROM users 
              WHERE is_online = 1 
              AND account_status = 'active' 
              AND role = 'user' 
              LIMIT 6";
$online_result = mysqli_query($conn, $online_sql);

// Get recent posts if logged in
$posts = [];
if (isLoggedIn()) {
    $posts_sql = "SELECT p.*, u.username, u.full_name, u.profile_pic,
                  (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
                  (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
                  (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = {$_SESSION['user_id']}) as user_liked
                  FROM posts p 
                  JOIN users u ON p.user_id = u.id 
                  WHERE p.privacy = 'public' 
                  OR (p.privacy = 'followers' AND p.user_id IN (
                      SELECT following_id FROM follows WHERE follower_id = {$_SESSION['user_id']}
                  ))
                  OR p.user_id = {$_SESSION['user_id']}
                  ORDER BY p.created_at DESC 
                  LIMIT 5";
    $posts_result = mysqli_query($conn, $posts_sql);
    while ($row = mysqli_fetch_assoc($posts_result)) {
        $posts[] = $row;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section (Non-logged in users) -->
<?php if (!isLoggedIn()): ?>
<section class="hero">
    <div class="hero-content">
        <h1>Find Your Perfect Match 💕</h1>
        <p>Join millions of singles looking for love, friendship, and meaningful connections</p>
        <div class="hero-buttons">
            <a href="signup.php" class="btn btn-primary btn-lg">Join Free Today</a>
            <a href="#how-it-works" class="btn btn-outline btn-lg">How It Works</a>
        </div>
        <div class="hero-stats">
            <div class="stat">
                <span class="stat-number">1M+</span>
                <span class="stat-label">Members</span>
            </div>
            <div class="stat">
                <span class="stat-number">50K+</span>
                <span class="stat-label">Success Stories</span>
            </div>
            <div class="stat">
                <span class="stat-number">100K+</span>
                <span class="stat-label">Daily Chats</span>
            </div>
        </div>
    </div>
    <div class="hero-image">
        <img src="../assets/uploads/hero-dating.svg" alt="Dating" onerror="this.style.display='none'">
    </div>
</section>
<?php endif; ?>

<div class="home-container">
    <?php if (isLoggedIn()): ?>
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-card">
            <img src="../<?php echo $_SESSION['profile_pic']; ?>" alt="Profile" class="welcome-avatar">
            <div class="welcome-text">
                <h2>Welcome back, <?php echo $_SESSION['full_name'] ?: $_SESSION['username']; ?>! 👋</h2>
                <p>Discover new connections and exciting conversations</p>
            </div>
            <div class="quick-actions">
                <a href="posts.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Post
                </a>
                <a href="profile.php" class="btn btn-outline">
                    <i class="fas fa-user-edit"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Left Sidebar -->
        <div class="sidebar-left">
            <!-- User Profile Card -->
            <div class="card profile-card">
                <div class="profile-cover"></div>
                <img src="../<?php echo $_SESSION['profile_pic']; ?>" alt="Profile" class="profile-avatar">
                <h3><?php echo $_SESSION['full_name'] ?: $_SESSION['username']; ?></h3>
                <p class="username">@<?php echo $_SESSION['username']; ?></p>
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo getFollowingCount($_SESSION['user_id']); ?></span>
                        <span class="stat-label">Following</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo getFollowersCount($_SESSION['user_id']); ?></span>
                        <span class="stat-label">Followers</span>
                    </div>
                </div>
                <a href="profile.php" class="btn btn-outline btn-block">View Profile</a>
            </div>
            
            <!-- Quick Links -->
            <div class="card quick-links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="chat.php"><i class="fas fa-comments"></i> Messages</a></li>
                    <li><a href="notifications.php"><i class="fas fa-bell"></i> Notifications 
                        <?php if ($unread_count > 0): ?>
                        <span class="badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                    <li><a href="go_live.php"><i class="fas fa-video"></i> Go Live</a></li>
                    <li><a href="profile.php#settings"><i class="fas fa-cog"></i> Settings</a></li>
                </ul>
            </div>
        </div>
        
        <!-- Main Feed -->
        <div class="main-feed">
            <!-- Create Post -->
            <div class="card create-post-card">
                <div class="create-post-header">
                    <img src="../<?php echo $_SESSION['profile_pic']; ?>" alt="Profile" class="user-avatar-small">
                    <button class="create-post-btn" onclick="window.location.href='posts.php?action=create'">
                        What's on your mind, <?php echo $_SESSION['username']; ?>?
                    </button>
                </div>
                <div class="create-post-actions">
                    <button class="post-action-btn" onclick="window.location.href='posts.php?action=create&type=image'">
                        <i class="fas fa-image"></i> Photo
                    </button>
                    <button class="post-action-btn" onclick="window.location.href='posts.php?action=create&type=video'">
                        <i class="fas fa-video"></i> Video
                    </button>
                    <button class="post-action-btn" onclick="window.location.href='go_live.php'">
                        <i class="fas fa-broadcast-tower"></i> Go Live
                    </button>
                </div>
            </div>
            
            <!-- Posts Feed -->
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                <div class="card post-card" id="post-<?php echo $post['id']; ?>">
                    <!-- Post Header -->
                    <div class="post-header">
                        <img src="../<?php echo $post['profile_pic']; ?>" alt="Profile" class="user-avatar-small">
                        <div class="post-user-info">
                            <a href="profile.php?user=<?php echo $post['username']; ?>" class="post-username">
                                <?php echo $post['full_name']; ?>
                            </a>
                            <span class="post-time"><?php echo timeAgo($post['created_at']); ?></span>
                        </div>
                        <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                        <div class="post-options">
                            <button class="btn-icon" onclick="deletePost(<?php echo $post['id']; ?>)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Post Content -->
                    <div class="post-content">
                        <?php if ($post['content']): ?>
                        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($post['media_type'] != 'text' && $post['media_urls']): 
                            $media = json_decode($post['media_urls'], true);
                            if (is_array($media)):
                        ?>
                        <div class="post-media">
                            <?php if ($post['media_type'] == 'image'): ?>
                                <img src="../<?php echo $media[0]; ?>" alt="Post image" class="post-image">
                            <?php elseif ($post['media_type'] == 'video'): ?>
                                <video controls class="post-video">
                                    <source src="../<?php echo $media[0]; ?>" type="video/mp4">
                                </video>
                            <?php endif; ?>
                        </div>
                        <?php endif; endif; ?>
                    </div>
                    
                    <!-- Post Stats -->
                    <div class="post-stats-bar">
                        <span class="stat-item">
                            <span id="likes-count-<?php echo $post['id']; ?>"><?php echo $post['likes_count']; ?></span> likes
                        </span>
                        <span class="stat-item">
                            <span><?php echo $post['comments_count']; ?></span> comments
                        </span>
                        <span class="stat-item">
                            <span><?php echo $post['shares_count']; ?></span> shares
                        </span>
                    </div>
                    
                    <!-- Post Actions - Horizontal Layout (NO onclick attributes) -->
                    <div class="post-actions-horizontal">
                        <button class="action-btn like-btn <?php echo $post['user_liked'] ? 'liked' : ''; ?>" 
                                id="like-btn-<?php echo $post['id']; ?>"
                                data-post-id="<?php echo $post['id']; ?>"
                                style="<?php echo $post['user_liked'] ? 'color: #e91e63;' : ''; ?>">
                            <i class="<?php echo $post['user_liked'] ? 'fas' : 'far'; ?> fa-heart"></i>
                            <span><?php echo $post['user_liked'] ? 'Liked' : 'Like'; ?></span>
                        </button>
                        
                        <button class="action-btn comment-btn" 
                                onclick="focusComment(<?php echo $post['id']; ?>)">
                            <i class="far fa-comment"></i>
                            <span>Comment</span>
                        </button>
                        
                        <button class="action-btn share-btn" 
                                onclick="sharePost(<?php echo $post['id']; ?>)">
                            <i class="far fa-share-square"></i>
                            <span>Share</span>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card empty-state">
                    <i class="fas fa-newspaper"></i>
                    <h3>No posts yet</h3>
                    <p>Follow people to see their posts here</p>
                    <a href="posts.php?action=create" class="btn btn-primary">Create Your First Post</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Right Sidebar -->
        <div class="sidebar-right">
            <!-- Online Users -->
            <div class="card">
                <h3><i class="fas fa-circle online-dot"></i> Online Now</h3>
                <?php if (mysqli_num_rows($online_result) > 0): ?>
                    <div class="online-users">
                        <?php while ($user = mysqli_fetch_assoc($online_result)): ?>
                        <div class="online-user">
                            <img src="../<?php echo $user['profile_pic']; ?>" alt="Profile" class="user-avatar-small">
                            <div class="online-user-info">
                                <a href="profile.php?user=<?php echo $user['username']; ?>">
                                    <?php echo $user['full_name'] ?: $user['username']; ?>
                                </a>
                                <?php if ($user['location']): ?>
                                <span class="user-location">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo $user['location']; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <span class="online-status"></span>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-text">No users online</p>
                <?php endif; ?>
            </div>
            
            <!-- Suggestions -->
            <div class="card">
                <h3>Suggested For You</h3>
                <?php
                if (isLoggedIn()) {
                    $suggestions_sql = "SELECT id, username, full_name, profile_pic, location 
                                       FROM users 
                                       WHERE id != {$_SESSION['user_id']} 
                                       AND account_status = 'active' 
                                       AND role = 'user'
                                       ORDER BY RAND() 
                                       LIMIT 5";
                    $suggestions_result = mysqli_query($conn, $suggestions_sql);
                    
                    while ($user = mysqli_fetch_assoc($suggestions_result)):
                        $is_following_suggestion = isFollowing($_SESSION['user_id'], $user['id']);
                ?>
                <div class="suggestion-item">
                    <img src="../<?php echo $user['profile_pic']; ?>" alt="Profile" class="user-avatar-small">
                    <div class="suggestion-info">
                        <a href="profile.php?user=<?php echo $user['username']; ?>">
                            <?php echo $user['full_name'] ?: $user['username']; ?>
                        </a>
                        <?php if ($user['location']): ?>
                        <span class="user-location"><?php echo $user['location']; ?></span>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-sm follow-suggestion-btn <?php echo $is_following_suggestion ? 'following' : ''; ?>"
                            id="follow-btn-<?php echo $user['id']; ?>"
                            data-user-id="<?php echo $user['id']; ?>"
                            style="<?php echo $is_following_suggestion ? 'background-color: #4caf50; color: white;' : ''; ?>">
                        <?php echo $is_following_suggestion ? 'Following' : 'Follow'; ?>
                    </button>
                </div>
                <?php 
                    endwhile;
                }
                ?>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Non-logged in sections -->
    <!-- Featured Members -->
    <section class="featured-section" id="featured">
        <h2>Featured Members</h2>
        <div class="featured-grid">
            <?php while ($user = mysqli_fetch_assoc($featured_result)): ?>
            <div class="featured-card">
                <img src="../<?php echo $user['profile_pic']; ?>" alt="Profile" class="featured-avatar">
                <h3><?php echo $user['full_name'] ?: $user['username']; ?></h3>
                <?php if ($user['location']): ?>
                <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo $user['location']; ?></p>
                <?php endif; ?>
                <?php if ($user['bio']): ?>
                <p class="bio"><?php echo substr($user['bio'], 0, 100); ?>...</p>
                <?php endif; ?>
                <a href="signup.php" class="btn btn-primary btn-block">Connect</a>
            </div>
            <?php endwhile; ?>
        </div>
    </section>
    
    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <h2>How It Works</h2>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <i class="fas fa-user-plus"></i>
                <h3>Create Profile</h3>
                <p>Sign up and create your perfect dating profile</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <i class="fas fa-search"></i>
                <h3>Find Matches</h3>
                <p>Browse through profiles and find your match</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <i class="fas fa-comments"></i>
                <h3>Start Chatting</h3>
                <p>Send messages and get to know each other</p>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <i class="fas fa-heart"></i>
                <h3>Find Love</h3>
                <p>Meet your perfect match and start a relationship</p>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<style>
/* ============ ALL YOUR EXISTING CSS STYLES HERE ============ */
/* Keep all the CSS you already have, but REMOVE these duplicate sections: */
/* - Remove the old .action-btn styles (keep only .post-actions-horizontal ones) */
/* - Remove the old .post-actions styles */
/* - Remove any inline button styles that conflict */

/* Make sure you have these key styles: */

/* Post Actions - Horizontal Layout */
.post-actions-horizontal {
    display: flex;
    align-items: center;
    justify-content: space-around;
    padding: 8px 10px;
    border-top: 1px solid #e8e8e8;
    border-bottom: 1px solid #e8e8e8;
    background: #fafafa;
}

.post-actions-horizontal .action-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 20px;
    background: none;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    color: #65676b;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    flex: 1;
    justify-content: center;
    position: relative;
}

.post-actions-horizontal .action-btn:hover {
    background: #f0f2f5;
}

.post-actions-horizontal .action-btn i {
    font-size: 18px;
    transition: all 0.2s ease;
}

/* Like button */
.post-actions-horizontal .like-btn:hover {
    color: #e91e63;
    background: #fce4ec;
}

.post-actions-horizontal .like-btn.liked {
    color: #e91e63 !important;
}

/* Comment button */
.post-actions-horizontal .comment-btn:hover {
    color: #2196f3;
    background: #e3f2fd;
}

/* Share button */
.post-actions-horizontal .share-btn:hover {
    color: #4caf50;
    background: #e8f5e9;
}

/* Post Stats Bar */
.post-stats-bar {
    display: flex;
    justify-content: space-between;
    padding: 8px 15px;
    font-size: 13px;
    color: #65676b;
    background: #fafafa;
    border-top: 1px solid #f0f0f0;
}

/* Follow Button */
.follow-suggestion-btn {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85em;
    border: 1px solid #e91e63;
    background: white;
    color: #e91e63;
    cursor: pointer;
    transition: all 0.2s ease;
}

.follow-suggestion-btn.following {
    background-color: #4caf50 !important;
    color: white !important;
    border-color: #4caf50 !important;
}

/* Rest of your CSS remains the same */
/* ... keep all other styles ... */
</style>
<style>
.posts-container {
    max-width: 800px;
    margin: 0 auto;
}

.posts-layout {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Create Post */
.create-post-section {
    padding: 20px;
}

.post-options {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.media-preview {
    margin: 15px 0;
}

.preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px;
}

.preview-item {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: 8px;
}

.preview-item img, .preview-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remove-media {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    cursor: pointer;
}

/* Post Cards */
.post-card {
    padding: 0;
    overflow: hidden;
}

.post-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 15px;
}

.post-user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
}

.post-username {
    font-weight: 600;
    color: #333;
    text-decoration: none;
}

.post-meta {
    display: flex;
    gap: 10px;
    font-size: 0.85em;
    color: #999;
}

.privacy-badge {
    background: #f0f0f0;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.8em;
}

.post-content {
    padding: 0 15px 15px;
}

.post-text {
    margin-bottom: 15px;
    line-height: 1.6;
    color: #333;
}

.post-media-grid {
    margin-top: 10px;
}

.post-media-grid.grid-1 .media-item {
    width: 100%;
}

.post-media-grid.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 5px;
}

.post-media-grid.grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 1fr 1fr;
    gap: 5px;
}

.post-media-grid.grid-3 .media-item:first-child {
    grid-row: 1 / 3;
}

.post-media-grid.grid-4 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 5px;
}

.media-item {
    overflow: hidden;
    border-radius: 8px;
    cursor: pointer;
}

.media-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.media-item:hover img {
    transform: scale(1.05);
}

.post-stats-bar {
    display: flex;
    justify-content: space-around;
    padding: 10px 15px;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 0.9em;
    color: #666;
}

.post-actions {
    display: flex;
    padding: 5px;
}

/* Post Actions - Horizontal Layout */
.post-actions-horizontal {
    display: flex;
    align-items: center;
    justify-content: space-around;
    padding: 8px 10px;
    border-top: 1px solid #e8e8e8;
    border-bottom: 1px solid #e8e8e8;
    background: #fafafa;
}

.post-actions-horizontal .action-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 20px;
    background: none;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    color: #65676b;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    flex: 1;
    justify-content: center;
}

.post-actions-horizontal .action-btn:hover {
    background: #f0f2f5;
}

.post-actions-horizontal .action-btn i {
    font-size: 18px;
    transition: all 0.2s ease;
}

.post-actions-horizontal .action-btn .count {
    font-size: 13px;
    color: #65676b;
    margin-left: 2px;
}

/* Like button specific */
.post-actions-horizontal .like-btn:hover {
    color: #e91e63;
    background: #fce4ec;
}

.post-actions-horizontal .like-btn.liked {
    color: #e91e63;
}

.post-actions-horizontal .like-btn.liked i {
    animation: heartPulse 0.3s ease;
}

/* Comment button specific */
.post-actions-horizontal .comment-btn:hover {
    color: #2196f3;
    background: #e3f2fd;
}

/* Share button specific */
.post-actions-horizontal .share-btn:hover {
    color: #4caf50;
    background: #e8f5e9;
}

/* Divider between buttons */
.post-actions-horizontal .action-btn:not(:last-child)::after {
    content: '';
    position: absolute;
    right: 0;
    top: 25%;
    height: 50%;
    width: 1px;
    background: #e0e0e0;
}

.post-actions-horizontal .action-btn {
    position: relative;
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .post-actions-horizontal .action-btn {
        padding: 8px 10px;
        font-size: 13px;
    }
    
    .post-actions-horizontal .action-btn span {
        display: none; /* Hide text on mobile, show only icons */
    }
    
    .post-actions-horizontal .action-btn .count {
        display: inline; /* Keep count visible */
    }
}

.action-btn {
    flex: 1;
    background: none;
    border: none;
    padding: 10px;
    cursor: pointer;
    color: #666;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.action-btn:hover {
    background: #f5f5f5;
}

.like-btn.liked {
    color: #e91e63;
}

/* Comments Section */
.comments-section {
    border-top: 1px solid #f0f0f0;
    padding: 15px;
    background: #fafafa;
}

.add-comment-form {
    margin-bottom: 15px;
}

.comment-input-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.comment-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
}

.comment-avatar-small {
    width: 25px;
    height: 25px;
    border-radius: 50%;
}

.comment-input {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 20px;
    outline: none;
}

.comments-list {
    max-height: 400px;
    overflow-y: auto;
}

.comment-item {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.comment-content {
    flex: 1;
}

.comment-username {
    font-weight: 600;
    color: #333;
    text-decoration: none;
    font-size: 0.9em;
}

.comment-time {
    font-size: 0.8em;
    color: #999;
    margin-left: 10px;
}

.comment-text {
    margin: 5px 0;
    color: #555;
}

.reply-btn {
    background: none;
    border: none;
    color: #999;
    font-size: 0.85em;
    cursor: pointer;
    padding: 5px;
}

.reply-item {
    display: flex;
    gap: 10px;
    margin: 10px 0 10px 40px;
}

.reply-content {
    flex: 1;
}

.reply-form {
    margin: 10px 0 10px 40px;
}

/* Lightbox */
.lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.9);
    z-index: 2000;
    justify-content: center;
    align-items: center;
}

.lightbox img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 10px;
}

.close-lightbox {
    position: absolute;
    top: 20px;
    right: 40px;
    color: white;
    font-size: 40px;
    cursor: pointer;
}

@media (max-width: 768px) {
    .post-options {
        flex-direction: column;
    }
}
</style>

<!-- IMPORTANT: Remove all old JavaScript functions and use only this -->
<script>
// Focus Comment
function focusComment(postId) {
    window.location.href = 'posts.php?post=' + postId + '#comments';
}

// Share Post
function sharePost(postId) {
    fetch('../api/share_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'post_id=' + postId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Post shared successfully!');
        }
    });
}

// Delete Post
function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post?')) {
        fetch('../api/delete_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'post_id=' + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('post-' + postId).remove();
            }
        });
    }
}

// Note: likePost and followUser functions are now handled by main.js event delegation
// DO NOT add onclick="likePost()" or onclick="followUser()" to buttons
// The event listeners in main.js will handle all clicks automatically
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>