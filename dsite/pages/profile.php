<?php
$page_title = "Profile";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

// Check if viewing another user's profile
$profile_user = null;
$is_own_profile = true;

if (isset($_GET['user'])) {
    $profile_user = getUserByUsername($_GET['user']);
    if ($profile_user) {
        $is_own_profile = ($profile_user['id'] == $_SESSION['user_id']);
    } else {
        header('Location: index.php');
        exit();
    }
}

// If viewing own profile, get full data
if ($is_own_profile) {
    $user_id = $_SESSION['user_id'];
    $user = getUserById($user_id);
    $settings = getUserSettings($user_id);
} else {
    $user = $profile_user;
    $user_id = $user['id'];
}

// Handle profile update
if ($is_own_profile && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = sanitize($_POST['full_name']);
        $bio = isset($_POST['bio']) ? sanitize($_POST['bio']) : '';
        $location = isset($_POST['location']) ? sanitize($_POST['location']) : '';
        $occupation = isset($_POST['occupation']) ? sanitize($_POST['occupation']) : '';
        $education = isset($_POST['education']) ? sanitize($_POST['education']) : '';
        $interests = isset($_POST['interests']) ? sanitize($_POST['interests']) : '';
        $gender = isset($_POST['gender']) ? sanitize($_POST['gender']) : 'prefer-not-to-say';
        $interested_in = isset($_POST['interested_in']) ? sanitize($_POST['interested_in']) : 'both';
        
        // Handle profile picture upload
        $profile_pic = $user['profile_pic'];
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            $upload_result = uploadFile($_FILES['profile_pic']);
            if ($upload_result['success']) {
                if ($profile_pic && $profile_pic != 'assets/uploads/default-avatar.png' && file_exists('../' . $profile_pic)) {
                    unlink('../' . $profile_pic);
                }
                $profile_pic = $upload_result['file_path'];
            }
        }
        
        // Handle cover picture upload
        $cover_pic = isset($user['cover_pic']) ? $user['cover_pic'] : '';
        if (isset($_FILES['cover_pic']) && $_FILES['cover_pic']['error'] === 0) {
            $upload_result = uploadFile($_FILES['cover_pic']);
            if ($upload_result['success']) {
                if ($cover_pic && file_exists('../' . $cover_pic)) {
                    unlink('../' . $cover_pic);
                }
                $cover_pic = $upload_result['file_path'];
            }
        }
        
        $sql = "UPDATE users SET 
                full_name = '$full_name',
                bio = '$bio',
                location = '$location',
                occupation = '$occupation',
                education = '$education',
                interests = '$interests',
                gender = '$gender',
                interested_in = '$interested_in',
                profile_pic = '$profile_pic',
                cover_pic = " . ($cover_pic ? "'$cover_pic'" : "NULL") . "
                WHERE id = $user_id";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['full_name'] = $full_name;
            $_SESSION['profile_pic'] = $profile_pic;
            $success = "Profile updated successfully!";
            $user = getUserById($user_id);
        } else {
            $error = "Error updating profile: " . mysqli_error($conn);
        }
    }
    
    // Handle settings update
    if (isset($_POST['update_settings'])) {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
        $profile_visibility = isset($_POST['profile_visibility']) ? sanitize($_POST['profile_visibility']) : 'public';
        $show_online_status = isset($_POST['show_online_status']) ? 1 : 0;
        $allow_messages_from = isset($_POST['allow_messages_from']) ? sanitize($_POST['allow_messages_from']) : 'everyone';
        $theme = isset($_POST['theme']) ? sanitize($_POST['theme']) : 'light';
        
        $sql = "UPDATE user_settings SET 
                email_notifications = $email_notifications,
                push_notifications = $push_notifications,
                profile_visibility = '$profile_visibility',
                show_online_status = $show_online_status,
                allow_messages_from = '$allow_messages_from',
                theme = '$theme'
                WHERE user_id = $user_id";
        
        if (mysqli_query($conn, $sql)) {
            $settings_success = "Settings updated successfully!";
            $settings = getUserSettings($user_id);
        } else {
            $settings_error = "Error updating settings: " . mysqli_error($conn);
        }
    }
}

// Get user's posts
$posts_sql = "SELECT * FROM posts WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 10";
$posts_result = mysqli_query($conn, $posts_sql);
$posts_count = mysqli_num_rows($posts_result);

$followers_count = getFollowersCount($user_id);
$following_count = getFollowingCount($user_id);
$is_following = isLoggedIn() ? isFollowing($_SESSION['user_id'], $user_id) : false;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="profile-page">
    <!-- Cover Photo -->
    <div class="profile-cover-wrapper">
        <div class="profile-cover" style="background-image: url('../<?php echo isset($user['cover_pic']) && $user['cover_pic'] ? $user['cover_pic'] : 'assets/uploads/default-cover.jpg'; ?>')">
            <?php if ($is_own_profile) { ?>
            <button class="cover-change-btn" onclick="document.getElementById('cover_pic_input').click()">
                <i class="fas fa-camera"></i> Change Cover
            </button>
            <?php } ?>
        </div>
    </div>
    
    <!-- Profile Main -->
    <div class="profile-main">
        <div class="profile-sidebar">
            <!-- Profile Picture -->
            <div class="profile-pic-wrapper">
                <div class="profile-pic-container">
                    <img src="../<?php echo $user['profile_pic'] ? $user['profile_pic'] : 'assets/uploads/default-avatar.png'; ?>" 
                         alt="Profile" class="profile-pic">
                    <?php if ($is_own_profile) { ?>
                    <button class="pic-change-btn" onclick="document.getElementById('profile_pic_input').click()">
                        <i class="fas fa-camera"></i>
                    </button>
                    <?php } ?>
                </div>
            </div>
            
            <!-- User Info -->
            <div class="user-main-info">
                <h1 class="user-name">
                    <?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>
                    <?php if (isset($user['is_verified']) && $user['is_verified']) { ?>
                    <span class="verify-badge" title="Verified"><i class="fas fa-check-circle"></i></span>
                    <?php } ?>
                </h1>
                <p class="user-handle">@<?php echo htmlspecialchars($user['username']); ?></p>
                
                <span class="status-badge <?php echo isset($user['is_online']) && $user['is_online'] ? 'online' : 'offline'; ?>">
                    <span class="status-dot"></span>
                    <?php echo isset($user['is_online']) && $user['is_online'] ? 'Online' : 'Offline'; ?>
                </span>
                
                <?php if (isset($user['bio']) && $user['bio']) { ?>
                <p class="user-bio"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                <?php } ?>
            </div>
            
            <!-- Stats -->
            <div class="user-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $posts_count; ?></span>
                    <span class="stat-label">Posts</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="followers-count"><?php echo $followers_count; ?></span>
                    <span class="stat-label">Followers</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $following_count; ?></span>
                    <span class="stat-label">Following</span>
                </div>
            </div>
            
            <!-- Details -->
            <div class="user-details-list">
                <?php if (isset($user['location']) && $user['location']) { ?>
                <div class="detail-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?php echo htmlspecialchars($user['location']); ?></span>
                </div>
                <?php } ?>
                <?php if (isset($user['occupation']) && $user['occupation']) { ?>
                <div class="detail-item">
                    <i class="fas fa-briefcase"></i>
                    <span><?php echo htmlspecialchars($user['occupation']); ?></span>
                </div>
                <?php } ?>
                <?php if (isset($user['education']) && $user['education']) { ?>
                <div class="detail-item">
                    <i class="fas fa-graduation-cap"></i>
                    <span><?php echo htmlspecialchars($user['education']); ?></span>
                </div>
                <?php } ?>
                <div class="detail-item">
                    <i class="fas fa-venus-mars"></i>
                    <span><?php echo isset($user['gender']) ? ucfirst($user['gender']) : 'Not specified'; ?></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Joined <?php echo isset($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : 'Recently'; ?></span>
                </div>
            </div>
            
            <!-- Interests -->
            <?php if (isset($user['interests']) && $user['interests']) { ?>
            <div class="user-interests">
                <h4>Interests</h4>
                <div class="interests-list">
                    <?php 
                    $interests_array = explode(',', $user['interests']);
                    foreach ($interests_array as $interest) { 
                        $interest = trim($interest);
                        if ($interest) {
                    ?>
                    <span class="interest-tag"><?php echo htmlspecialchars($interest); ?></span>
                    <?php } } ?>
                </div>
            </div>
            <?php } ?>
            
            <!-- Action Buttons -->
            <div class="profile-action-buttons">
                <?php if ($is_own_profile) { ?>
                    <button class="btn btn-primary btn-block" onclick="showEditProfile()">
                        <i class="fas fa-edit"></i> Edit Profile
                    </button>
                    <button class="btn btn-outline btn-block" onclick="showSettings()">
                        <i class="fas fa-cog"></i> Settings
                    </button>
                <?php } else { ?>
                    <button class="btn btn-primary btn-block follow-btn <?php echo $is_following ? 'following' : ''; ?>" 
                            id="follow-btn-<?php echo $user_id; ?>"
                            data-user-id="<?php echo $user_id; ?>"
                            style="<?php echo $is_following ? 'background-color: #4caf50; color: white; border-color: #4caf50;' : ''; ?>">
                        <?php if ($is_following) { ?>
                            <i class="fas fa-user-check"></i> Following
                        <?php } else { ?>
                            <i class="fas fa-user-plus"></i> Follow
                        <?php } ?>
                    </button>
                    <button class="btn btn-outline btn-block" onclick="startChat(<?php echo $user_id; ?>)">
                        <i class="fas fa-comment"></i> Message
                    </button>
                <?php } ?>
            </div>
        </div>
        
        <!-- Posts Section -->
        <div class="profile-posts-area">
            <div class="posts-header">
                <h2><i class="fas fa-newspaper"></i> Posts</h2>
                <?php if ($is_own_profile) { ?>
                <a href="posts.php?action=create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> New Post
                </a>
                <?php } ?>
            </div>
            
            <?php if ($posts_count > 0) { ?>
                <div class="posts-grid">
                    <?php 
                    mysqli_data_seek($posts_result, 0);
                    while ($post = mysqli_fetch_assoc($posts_result)) { 
                    ?>
                    <div class="post-card-item" onclick="window.location.href='posts.php?post=<?php echo $post['id']; ?>'">
                        <?php if ($post['media_type'] == 'image' && $post['media_urls']) { 
                            $media = json_decode($post['media_urls'], true);
                            if (is_array($media) && isset($media[0])) {
                        ?>
                            <div class="post-thumb">
                                <img src="../<?php echo $media[0]; ?>" alt="Post">
                            </div>
                        <?php } } elseif ($post['media_type'] == 'video') { ?>
                            <div class="post-thumb video-thumb">
                                <i class="fas fa-play-circle"></i>
                            </div>
                        <?php } else { ?>
                            <div class="post-thumb text-thumb">
                                <i class="fas fa-quote-right"></i>
                                <p><?php echo $post['content'] ? substr(htmlspecialchars($post['content']), 0, 100) : ''; ?></p>
                            </div>
                        <?php } ?>
                        <div class="post-card-overlay">
                            <div class="post-card-stats">
                                <span><i class="fas fa-heart"></i> <?php echo $post['likes_count']; ?></span>
                                <span><i class="fas fa-comment"></i> <?php echo $post['comments_count']; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="no-posts">
                    <div class="no-posts-icon">
                        <i class="fas fa-camera-retro"></i>
                    </div>
                    <h3>No posts yet</h3>
                    <?php if ($is_own_profile) { ?>
                    <p>Share your first post with the community!</p>
                    <a href="posts.php?action=create" class="btn btn-primary">Create Your First Post</a>
                    <?php } else { ?>
                    <p>This user hasn't posted anything yet.</p>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php if ($is_own_profile) { ?>
<!-- Edit Profile Modal -->
<div id="editProfileModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Profile</h2>
            <button class="close-btn" onclick="closeModal('editProfileModal')">&times;</button>
        </div>
        
        <?php if (isset($success)) { ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php } ?>
        
        <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label>Occupation</label>
                    <input type="text" name="occupation" value="<?php echo htmlspecialchars($user['occupation'] ?? ''); ?>" class="form-control">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Education</label>
                    <input type="text" name="education" value="<?php echo htmlspecialchars($user['education'] ?? ''); ?>" class="form-control">
                </div>
                <div class="form-group">
                    <label>Interests (comma-separated)</label>
                    <input type="text" name="interests" value="<?php echo htmlspecialchars($user['interests'] ?? ''); ?>" class="form-control" placeholder="e.g., Travel, Music, Sports">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" class="form-control">
                        <option value="male" <?php echo ($user['gender'] ?? '') == 'male' ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($user['gender'] ?? '') == 'female' ? 'selected' : ''; ?>>Female</option>
                        <option value="non-binary" <?php echo ($user['gender'] ?? '') == 'non-binary' ? 'selected' : ''; ?>>Non-Binary</option>
                        <option value="other" <?php echo ($user['gender'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                        <option value="prefer-not-to-say" <?php echo ($user['gender'] ?? '') == 'prefer-not-to-say' ? 'selected' : ''; ?>>Prefer not to say</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Interested In</label>
                    <select name="interested_in" class="form-control">
                        <option value="male" <?php echo ($user['interested_in'] ?? '') == 'male' ? 'selected' : ''; ?>>Men</option>
                        <option value="female" <?php echo ($user['interested_in'] ?? '') == 'female' ? 'selected' : ''; ?>>Women</option>
                        <option value="both" <?php echo ($user['interested_in'] ?? '') == 'both' ? 'selected' : ''; ?>>Both</option>
                        <option value="all" <?php echo ($user['interested_in'] ?? '') == 'all' ? 'selected' : ''; ?>>Everyone</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Profile Picture</label>
                <input type="file" name="profile_pic" id="profile_pic_input" accept="image/*" style="display: none;" onchange="previewImage(this, 'profile_preview')">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('profile_pic_input').click()">
                    <i class="fas fa-camera"></i> Change Profile Picture
                </button>
                <div id="profile_preview" class="image-preview"></div>
            </div>
            
            <div class="form-group">
                <label>Cover Photo</label>
                <input type="file" name="cover_pic" id="cover_pic_input" accept="image/*" style="display: none;" onchange="previewImage(this, 'cover_preview')">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('cover_pic_input').click()">
                    <i class="fas fa-image"></i> Change Cover Photo
                </button>
                <div id="cover_preview" class="image-preview"></div>
            </div>
            
            <button type="submit" name="update_profile" class="btn btn-primary btn-block">Save Changes</button>
        </form>
    </div>
</div>

<!-- Settings Modal -->
<div id="settingsModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Settings</h2>
            <button class="close-btn" onclick="closeModal('settingsModal')">&times;</button>
        </div>
        
        <?php if (isset($settings_success)) { ?>
        <div class="alert alert-success"><?php echo $settings_success; ?></div>
        <?php } ?>
        
        <?php if (isset($settings_error)) { ?>
        <div class="alert alert-danger"><?php echo $settings_error; ?></div>
        <?php } ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Profile Visibility</label>
                <select name="profile_visibility" class="form-control">
                    <option value="public" <?php echo ($settings['profile_visibility'] ?? 'public') == 'public' ? 'selected' : ''; ?>>Public</option>
                    <option value="friends" <?php echo ($settings['profile_visibility'] ?? '') == 'friends' ? 'selected' : ''; ?>>Friends Only</option>
                    <option value="private" <?php echo ($settings['profile_visibility'] ?? '') == 'private' ? 'selected' : ''; ?>>Private</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Allow Messages From</label>
                <select name="allow_messages_from" class="form-control">
                    <option value="everyone" <?php echo ($settings['allow_messages_from'] ?? 'everyone') == 'everyone' ? 'selected' : ''; ?>>Everyone</option>
                    <option value="friends" <?php echo ($settings['allow_messages_from'] ?? '') == 'friends' ? 'selected' : ''; ?>>Friends Only</option>
                    <option value="none" <?php echo ($settings['allow_messages_from'] ?? '') == 'none' ? 'selected' : ''; ?>>No One</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Theme</label>
                <select name="theme" class="form-control">
                    <option value="light" <?php echo ($settings['theme'] ?? 'light') == 'light' ? 'selected' : ''; ?>>Light</option>
                    <option value="dark" <?php echo ($settings['theme'] ?? '') == 'dark' ? 'selected' : ''; ?>>Dark</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="email_notifications" <?php echo ($settings['email_notifications'] ?? 1) ? 'checked' : ''; ?>>
                    Email Notifications
                </label>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="push_notifications" <?php echo ($settings['push_notifications'] ?? 1) ? 'checked' : ''; ?>>
                    Push Notifications
                </label>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="show_online_status" <?php echo ($settings['show_online_status'] ?? 1) ? 'checked' : ''; ?>>
                    Show Online Status
                </label>
            </div>
            
            <button type="submit" name="update_settings" class="btn btn-primary btn-block">Save Settings</button>
        </form>
    </div>
</div>
<?php } ?>

<style>
/* ============ PROFILE PAGE STYLES ============ */
.profile-page {
    max-width: 1100px;
    margin: 0 auto;
}

.profile-cover-wrapper {
    border-radius: 0 0 20px 20px;
    overflow: hidden;
    margin-bottom: 0;
}

.profile-cover {
    height: 280px;
    background-size: cover;
    background-position: center;
    position: relative;
    background-color: #667eea;
    background-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.cover-change-btn {
    position: absolute;
    bottom: 20px;
    right: 20px;
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s;
    backdrop-filter: blur(5px);
}

.cover-change-btn:hover {
    background: rgba(0,0,0,0.7);
}

.profile-main {
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 30px;
    padding: 0 20px;
    margin-top: -80px;
    position: relative;
    z-index: 1;
}

.profile-sidebar {
    background: white;
    border-radius: 15px;
    padding: 0 0 20px 0;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    position: sticky;
    top: 80px;
    height: fit-content;
}

.profile-pic-wrapper {
    display: flex;
    justify-content: center;
    margin-top: -60px;
    margin-bottom: 15px;
}

.profile-pic-container {
    position: relative;
    width: 130px;
    height: 130px;
}

.profile-pic {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    border: 5px solid white;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    object-fit: cover;
    display: block;
}

.pic-change-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: #e91e63;
    color: white;
    border: 3px solid white;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.pic-change-btn:hover {
    background: #c2185b;
    transform: scale(1.1);
}

.user-main-info {
    text-align: center;
    padding: 0 20px;
}

.user-name {
    font-size: 1.4em;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.verify-badge {
    color: #2196f3;
    font-size: 0.8em;
}

.user-handle {
    color: #65676b;
    font-size: 14px;
    margin-bottom: 10px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 15px;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.status-badge.online {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-badge.online .status-dot {
    background: #4caf50;
}

.status-badge.offline {
    background: #fafafa;
    color: #999;
}

.status-badge.offline .status-dot {
    background: #ccc;
}

.user-bio {
    color: #444;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 5px;
}

.user-stats {
    display: flex;
    justify-content: space-around;
    padding: 15px 20px;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
    margin: 15px 0;
}

.stat-item {
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    padding: 5px;
    border-radius: 8px;
}

.stat-item:hover {
    background: #f5f5f5;
}

.stat-number {
    display: block;
    font-size: 1.3em;
    font-weight: 700;
    color: #e91e63;
}

.stat-label {
    font-size: 12px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.user-details-list {
    padding: 0 20px;
    margin: 15px 0;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    color: #555;
    font-size: 14px;
    border-bottom: 1px solid #fafafa;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item i {
    width: 20px;
    color: #e91e63;
    font-size: 15px;
    text-align: center;
}

.user-interests {
    padding: 0 20px;
    margin: 15px 0;
}

.user-interests h4 {
    font-size: 13px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
}

.interests-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.interest-tag {
    background: #f0f2f5;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    color: #555;
    transition: all 0.2s;
}

.interest-tag:hover {
    background: #e91e63;
    color: white;
}

.profile-action-buttons {
    padding: 15px 20px 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.profile-action-buttons .btn {
    padding: 12px;
    font-size: 14px;
}

.profile-posts-area {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.posts-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.posts-header h2 {
    font-size: 1.3em;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 10px;
}

.posts-header h2 i {
    color: #e91e63;
}

.posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 12px;
}

.post-card-item {
    position: relative;
    aspect-ratio: 1;
    background: #f5f5f5;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s;
}

.post-card-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.post-thumb {
    width: 100%;
    height: 100%;
}

.post-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-thumb {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #1a1a2e;
    color: white;
    font-size: 3em;
}

.text-thumb {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.text-thumb i {
    font-size: 2em;
    margin-bottom: 10px;
    opacity: 0.7;
}

.text-thumb p {
    font-size: 13px;
    text-align: center;
    line-height: 1.4;
    opacity: 0.9;
}

.post-card-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 15px;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    opacity: 0;
    transition: opacity 0.3s;
}

.post-card-item:hover .post-card-overlay {
    opacity: 1;
}

.post-card-stats {
    display: flex;
    justify-content: center;
    gap: 20px;
    color: white;
    font-size: 13px;
}

.post-card-stats i {
    margin-right: 5px;
}

.no-posts {
    text-align: center;
    padding: 60px 20px;
}

.no-posts-icon {
    font-size: 4em;
    color: #ddd;
    margin-bottom: 15px;
}

.no-posts h3 {
    color: #666;
    margin-bottom: 8px;
}

.no-posts p {
    color: #999;
    margin-bottom: 20px;
    font-size: 14px;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2000;
}

.modal-content {
    background: white;
    border-radius: 15px;
    padding: 30px;
    max-width: 600px;
    width: 90%;
    max-height: 85vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.modal-header h2 {
    font-size: 1.3em;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.8em;
    cursor: pointer;
    color: #999;
    padding: 0;
    line-height: 1;
}

.close-btn:hover {
    color: #333;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #444;
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 10px 14px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #e91e63;
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 14px;
}

.image-preview {
    margin-top: 10px;
}

.image-preview img {
    max-width: 150px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 18px;
    font-size: 14px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 900px) {
    .profile-main {
        grid-template-columns: 1fr;
        margin-top: -60px;
    }
    
    .profile-sidebar {
        position: static;
    }
    
    .profile-cover {
        height: 200px;
    }
    
    .posts-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
}

@media (max-width: 480px) {
    .profile-cover {
        height: 150px;
    }
    
    .profile-pic {
        width: 100px;
        height: 100px;
    }
    
    .profile-pic-container {
        width: 100px;
        height: 100px;
    }
    
    .profile-pic-wrapper {
        margin-top: -40px;
    }
    
    .posts-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function previewImage(input, previewId) {
    var preview = document.getElementById(previewId);
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function showEditProfile() {
    document.getElementById('editProfileModal').style.display = 'flex';
}

function showSettings() {
    document.getElementById('settingsModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

function startChat(userId) {
    window.location.href = 'chat.php?user=' + userId;
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() { 
                if (alert.parentElement) alert.style.display = 'none'; 
            }, 500);
        });
    }, 3000);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>