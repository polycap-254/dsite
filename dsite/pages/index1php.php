<?php
$page_title = "Home";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

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
                  (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count
                  FROM posts p 
                  JOIN users u ON p.user_id = u.id 
                  WHERE p.privacy = 'public' 
                  OR (p.privacy = 'followers' AND p.user_id IN (
                      SELECT following_id FROM follows WHERE follower_id = {$_SESSION['user_id']}
                  ))
                  ORDER BY p.created_at DESC 
                  LIMIT 5";
    $posts_result = mysqli_query($conn, $posts_sql);
    while ($row = mysqli_fetch_assoc($posts_result)) {
        $posts[] = $row;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
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
                            <button class="btn-option" onclick="deletePost(<?php echo $post['id']; ?>)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="post-content">
                        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                        
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
                    
                    <div class="post-stats">
                        <span><?php echo $post['likes_count']; ?> likes</span>
                        <span><?php echo $post['comments_count']; ?> comments</span>
                    </div>
                    
                    <button class="action-btn like-btn <?php echo isset($post['user_liked']) && $post['user_liked'] ? 'liked' : ''; ?>" 
        id="like-btn-<?php echo $post['id']; ?>"
        data-post-id="<?php echo $post['id']; ?>"
        style="<?php echo isset($post['user_liked']) && $post['user_liked'] ? 'color: #e91e63;' : ''; ?>">
    <?php if (isset($post['user_liked']) && $post['user_liked']): ?>
        <i class="fas fa-heart"></i> Liked
    <?php else: ?>
        <i class="far fa-heart"></i> Like
    <?php endif; ?>
</button>
                        <button class="action-btn comment-btn" onclick="focusComment(<?php echo $post['id']; ?>)">
                            <i class="far fa-comment"></i> Comment
                        </button>
                        <button class="action-btn share-btn" onclick="sharePost(<?php echo $post['id']; ?>)">
                            <i class="far fa-share-square"></i> Share
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
    <?php 
    $is_following_suggestion = isFollowing($_SESSION['user_id'], $user['id']);
    ?>
    <button class="btn btn-sm follow-suggestion-btn <?php echo $is_following_suggestion ? 'following' : ''; ?>"
            id="follow-btn-<?php echo $user['id']; ?>"
            data-user-id="<?php echo $user['id']; ?>"
            onclick="followUser(<?php echo $user['id']; ?>)"
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
/* Hero Section */
.hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 80px 0;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 50px;
    margin: -20px -20px 0 -20px;
}

.hero-content {
    max-width: 500px;
    padding: 20px;
}

.hero-content h1 {
    font-size: 3em;
    margin-bottom: 20px;
}

.hero-content p {
    font-size: 1.2em;
    margin-bottom: 30px;
    opacity: 0.9;
}

.hero-buttons {
    display: flex;
    gap: 15px;
    margin-bottom: 40px;
}

.hero-stats {
    display: flex;
    gap: 30px;
}

.stat {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 2em;
    font-weight: bold;
}

.stat-label {
    font-size: 0.9em;
    opacity: 0.8;
}

/* Home Container */
.home-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

/* Welcome Section (Logged In) */
.welcome-section {
    margin-bottom: 30px;
}

.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    border-radius: 15px;
    color: white;
    display: flex;
    align-items: center;
    gap: 20px;
}

.welcome-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid white;
}

.welcome-text {
    flex: 1;
}

.quick-actions {
    display: flex;
    gap: 10px;
}

/* Content Grid */
.content-grid {
    display: grid;
    grid-template-columns: 250px 1fr 300px;
    gap: 20px;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

/* Profile Card */
.profile-card {
    text-align: center;
}

.profile-cover {
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin: -20px -20px 0 -20px;
    border-radius: 15px 15px 0 0;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 4px solid white;
    margin-top: -40px;
}

.profile-stats {
    display: flex;
    justify-content: space-around;
    margin: 20px 0;
}

.stat-item {
    text-align: center;
}

.stat-value {
    display: block;
    font-size: 1.2em;
    font-weight: bold;
    color: #e91e63;
}

/* Quick Links */
.quick-links ul {
    list-style: none;
    padding: 0;
}

.quick-links li {
    margin: 10px 0;
}

.quick-links li a {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #333;
    text-decoration: none;
    padding: 10px;
    border-radius: 8px;
    transition: background 0.3s;
}

.quick-links li a:hover {
    background: #f5f5f5;
}

/* Create Post */
.create-post-card {
    padding: 15px;
}

.create-post-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.user-avatar-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.create-post-btn {
    flex: 1;
    padding: 10px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 25px;
    background: #f5f5f5;
    text-align: left;
    color: #666;
    cursor: pointer;
    transition: border-color 0.3s;
}

.create-post-btn:hover {
    border-color: #e91e63;
}

.create-post-actions {
    display: flex;
    gap: 10px;
    justify-content: space-around;
    border-top: 1px solid #e0e0e0;
    padding-top: 15px;
}

.post-action-btn {
    background: none;
    border: none;
    padding: 8px 15px;
    border-radius: 20px;
    cursor: pointer;
    color: #666;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 5px;
}

.post-action-btn:hover {
    background: #f5f5f5;
    color: #e91e63;
}

/* Post Cards */
.post-card {
    padding: 0;
}

.post-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
}

.post-username {
    font-weight: 500;
    color: #333;
    text-decoration: none;
}

.post-time {
    display: block;
    font-size: 0.8em;
    color: #999;
}

.post-content {
    padding: 0 15px 15px;
}

.post-image, .post-video {
    width: 100%;
    border-radius: 10px;
    margin-top: 10px;
}

.post-stats {
    display: flex;
    justify-content: space-between;
    padding: 10px 15px;
    color: #666;
    font-size: 0.9em;
    border-top: 1px solid #f0f0f0;
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
    color: #e91e63;
}

/* Online Users */
.online-dot {
    color: #4caf50;
    font-size: 0.5em;
}

.online-user {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.online-status {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4caf50;
    margin-left: auto;
}

/* Follow Button Styles */
.follow-btn {
    transition: all 0.3s ease;
    position: relative;
}

.follow-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.follow-btn.following {
    background-color: #4caf50 !important;
    color: white !important;
    border-color: #4caf50 !important;
}

.follow-btn.following:hover {
    background-color: #f44336 !important;
    border-color: #f44336 !important;
}

.follow-btn.following:hover::after {
    content: 'Unfollow';
}

.follow-btn.following:hover span,
.follow-btn.following:hover i {
    display: none;
}

.follow-suggestion-btn {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.85em;
}

.follow-suggestion-btn.following {
    background-color: #4caf50 !important;
    color: white !important;
}

/* Toast Notification */
.custom-toast button {
    background: none;
    border: none;
    color: white;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0 5px;
    opacity: 0.8;
}

.custom-toast button:hover {
    opacity: 1;
}

/* Suggestions */
.suggestion-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.suggestion-info {
    flex: 1;
}

.suggestion-info a {
    color: #333;
    text-decoration: none;
    font-weight: 500;
}

.btn-sm {
    padding: 5px 15px;
    font-size: 0.9em;
}

/* Featured Section (Non-logged in) */
.featured-section {
    padding: 60px 0;
}

.featured-section h2 {
    text-align: center;
    margin-bottom: 40px;
    font-size: 2em;
}

.featured-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.featured-card {
    background: white;
    border-radius: 15px;
    padding: 30px 20px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.featured-card:hover {
    transform: translateY(-5px);
}

.featured-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    margin-bottom: 15px;
    border: 3px solid #e91e63;
}

/* How It Works */
.how-it-works {
    padding: 60px 0;
    background: #f9f9f9;
    margin: 0 -20px;
    padding: 60px 20px;
}

.how-it-works h2 {
    text-align: center;
    margin-bottom: 40px;
    font-size: 2em;
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.step-card {
    text-align: center;
    padding: 30px;
    background: white;
    border-radius: 15px;
    position: relative;
}

.step-number {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2em;
    font-weight: bold;
    margin: 0 auto 15px;
}

.step-card i {
    font-size: 2em;
    color: #e91e63;
    margin: 15px 0;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 4em;
    color: #ccc;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #666;
    margin-bottom: 10px;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .sidebar-left, .sidebar-right {
        display: none;
    }
    
    .hero {
        flex-direction: column;
        text-align: center;
        padding: 40px 20px;
    }
}

@media (max-width: 768px) {
    .welcome-card {
        flex-direction: column;
        text-align: center;
    }
    
    .quick-actions {
        flex-direction: column;
    }
    
    .hero-buttons {
        flex-direction: column;
    }
    
    .hero-stats {
        justify-content: center;
    }
}
</style>

<script>
// Like Post
function likePost(postId) {
    fetch('../api/like_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'post_id=' + postId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const likeBtn = event.target.closest('.like-btn');
            const statsDiv = likeBtn.closest('.post-card').querySelector('.post-stats');
            
            if (data.action === 'liked') {
                likeBtn.innerHTML = '<i class="fas fa-heart"></i> Liked';
                likeBtn.style.color = '#e91e63';
            } else {
                likeBtn.innerHTML = '<i class="far fa-heart"></i> Like';
                likeBtn.style.color = '#666';
            }
            
            // Update likes count
            const likesSpan = statsDiv.querySelector('span:first-child');
            likesSpan.textContent = data.likes_count + ' likes';
        }
    });
}

// Follow User
function followUser(userId, button) {
    fetch('../api/follow_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'user_id=' + userId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.action === 'followed') {
                button.textContent = 'Following';
                button.classList.add('following');
            } else {
                button.textContent = 'Follow';
                button.classList.remove('following');
            }
        }
    });
}

// Focus Comment
function focusComment(postId) {
    window.location.href = 'posts.php?post=' + postId + '#comments';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>