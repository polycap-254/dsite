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
                // Delete old profile pic if it exists and isn't default
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
                // Delete old cover pic if it exists
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
            $user = getUserById($user_id); // Refresh user data
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
            $settings = getUserSettings($user_id); // Refresh settings
        } else {
            $settings_error = "Error updating settings: " . mysqli_error($conn);
        }
    }
}

// Get user's posts
$posts_sql = "SELECT * FROM posts WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 10";
$posts_result = mysqli_query($conn, $posts_sql);

$followers_count = getFollowersCount($user_id);
$following_count = getFollowingCount($user_id);
$is_following = isLoggedIn() ? isFollowing($_SESSION['user_id'], $user_id) : false;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="profile-container">
    <!-- Cover Photo -->
    <div class="cover-photo" style="background-image: url('../<?php echo isset($user['cover_pic']) && $user['cover_pic'] ? $user['cover_pic'] : 'assets/uploads/default-cover.jpg'; ?>')">
        <?php if ($is_own_profile): ?>
        <button class="change-cover-btn" onclick="document.getElementById('cover_pic_input').click()">
            <i class="fas fa-camera"></i> Change Cover
        </button>
        <?php endif; ?>
    </div>
    
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-avatar-section">
            <img src="../<?php echo $user['profile_pic'] ? $user['profile_pic'] : 'assets/uploads/default-avatar.png'; ?>" alt="Profile" class="profile-avatar-large">
            <?php if ($is_own_profile): ?>
            <button class="change-avatar-btn" onclick="document.getElementById('profile_pic_input').click()">
                <i class="fas fa-camera"></i>
            </button>
            <?php endif; ?>
        </div>
        
        <div class="profile-info">
            <div class="profile-name-section">
                <h1><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h1>
                <?php if (isset($user['is_verified']) && $user['is_verified']): ?>
                <span class="verified-badge" title="Verified">
                    <i class="fas fa-check-circle"></i>
                </span>
                <?php endif; ?>
                <span class="online-status-badge <?php echo isset($user['is_online']) && $user['is_online'] ? 'online' : 'offline'; ?>">
                    <?php echo isset($user['is_online']) && $user['is_online'] ? 'Online' : 'Offline'; ?>
                </span>
            </div>
            <p class="username">@<?php echo htmlspecialchars($user['username']); ?></p>
            
            <?php if (isset($user['bio']) && $user['bio']): ?>
            <p class="bio"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
            <?php endif; ?>
            
            <div class="profile-details">
                <?php if (isset($user['location']) && $user['location']): ?>
                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['location']); ?></span>
                <?php endif; ?>
                <?php if (isset($user['occupation']) && $user['occupation']): ?>
                <span><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($user['occupation']); ?></span>
                <?php endif; ?>
                <?php if (isset($user['education']) && $user['education']): ?>
                <span><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($user['education']); ?></span>
                <?php endif; ?>
                <span><i class="fas fa-venus-mars"></i> <?php echo isset($user['gender']) ? ucfirst($user['gender']) : 'Not specified'; ?></span>
                <span><i class="fas fa-calendar"></i> Joined <?php echo isset($user['created_at']) ? date('F Y', strtotime($user['created_at'])) : 'Recently'; ?></span>
            </div>
            
            <?php if (isset($user['interests']) && $user['interests']): ?>
            <div class="interests">
                <?php foreach (explode(',', $user['interests']) as $interest): 
                    $interest = trim($interest);
                    if ($interest):
                ?>
                <span class="interest-tag"><?php echo htmlspecialchars($interest); ?></span>
                <?php endif; endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="profile-actions">
    <?php if ($is_own_profile): ?>
        <button class="btn btn-primary" onclick="showEditProfile()">
            <i class="fas fa-edit"></i> Edit Profile
        </button>
        <button class="btn btn-outline" onclick="showSettings()">
            <i class="fas fa-cog"></i> Settings
        </button>
    <?php else: ?>
        <button class="btn btn-primary follow-btn <?php echo $is_following ? 'following' : ''; ?>" 
                id="follow-btn-<?php echo $user_id; ?>"
                data-user-id="<?php echo $user_id; ?>"
                onclick="followUser(<?php echo $user_id; ?>)"
                style="<?php echo $is_following ? 'background-color: #4caf50; color: white; border-color: #4caf50;' : ''; ?>">
            <?php if ($is_following): ?>
                <i class="fas fa-user-check"></i> Following
            <?php else: ?>
                <i class="fas fa-user-plus"></i> Follow
            <?php endif; ?>
        </button>
        <button class="btn btn-outline" onclick="startChat(<?php echo $user_id; ?>)">
            <i class="fas fa-comment"></i> Message
        </button>
    <?php endif; ?>
</div>
    
    <!-- Profile Stats -->
    <div class="profile-stats-bar">
        <div class="stat">
            <span class="number"><?php echo mysqli_num_rows($posts_result); ?></span>
            <span class="label">Posts</span>
        </div>
        <div class="stat">
            <span class="number" id="followers-count"><?php echo $followers_count; ?></span>
            <span class="label">Followers</span>
        </div>
        <div class="stat">
            <span class="number"><?php echo $following_count; ?></span>
            <span class="label">Following</span>
        </div>
    </div>
    
    <!-- Profile Content -->
    <div class="profile-content">
        <div class="posts-section">
            <h2>Posts</h2>
            <?php if (mysqli_num_rows($posts_result) > 0): ?>
                <div class="profile-posts-grid">
                    <?php while ($post = mysqli_fetch_assoc($posts_result)): ?>
                    <div class="profile-post-card" onclick="window.location.href='posts.php?post=<?php echo $post['id']; ?>'">
                        <?php if ($post['media_type'] == 'image' && $post['media_urls']): 
                            $media = json_decode($post['media_urls'], true);
                            if (is_array($media) && isset($media[0])):
                        ?>
                            <img src="../<?php echo $media[0]; ?>" alt="Post" class="post-thumbnail">
                        <?php elseif ($post['media_type'] == 'video'): ?>
                            <div class="video-thumbnail">
                                <i class="fas fa-play"></i>
                            </div>
                        <?php endif; ?>
                        <div class="post-overlay">
                            <p><?php echo $post['content'] ? substr(htmlspecialchars($post['content']), 0, 100) : ''; ?></p>
                            <div class="post-stats-mini">
                                <span><i class="fas fa-heart"></i> <?php echo $post['likes_count']; ?></span>
                                <span><i class="fas fa-comment"></i> <?php echo $post['comments_count']; ?></span>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="text-post-preview">
                            <i class="fas fa-quote-right"></i>
                            <p><?php echo $post['content'] ? substr(htmlspecialchars($post['content']), 0, 150) : 'No content'; ?></p>
                        </div>
                        <div class="post-overlay">
                            <div class="post-stats-mini">
                                <span><i class="fas fa-heart"></i> <?php echo $post['likes_count']; ?></span>
                                <span><i class="fas fa-comment"></i> <?php echo $post['comments_count']; ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-camera"></i>
                    <h3>No posts yet</h3>
                    <?php if ($is_own_profile): ?>
                    <p>Share your first post with the community!</p>
                    <a href="posts.php?action=create" class="btn btn-primary">Create Your First Post</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<?php if ($is_own_profile): ?>
<div id="editProfileModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Profile</h2>
            <button class="close-btn" onclick="closeModal('editProfileModal')">&times;</button>
        </div>
        
        <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="profile-form">
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
                <div class="file-upload-area">
                    <input type="file" name="profile_pic" id="profile_pic_input" accept="image/*" style="display: none;" onchange="previewImage(this, 'profile_preview')">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('profile_pic_input').click()">
                        <i class="fas fa-camera"></i> Change Profile Picture
                    </button>
                    <div id="profile_preview" class="image-preview"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Cover Photo</label>
                <div class="file-upload-area">
                    <input type="file" name="cover_pic" id="cover_pic_input" accept="image/*" style="display: none;" onchange="previewImage(this, 'cover_preview')">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('cover_pic_input').click()">
                        <i class="fas fa-image"></i> Change Cover Photo
                    </button>
                    <div id="cover_preview" class="image-preview"></div>
                </div>
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
        
        <?php if (isset($settings_success)): ?>
        <div class="alert alert-success"><?php echo $settings_success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($settings_error)): ?>
        <div class="alert alert-danger"><?php echo $settings_error; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="settings-form">
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
<?php endif; ?>

<style>
/* Previous CSS styles remain the same */
/* ... (keep all the previous styles) ... */

/* Additional styles for file upload */
.file-upload-area {
    margin-top: 10px;
}

.image-preview {
    margin-top: 10px;
    max-width: 200px;
}

.image-preview img {
    max-width: 100%;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.text-post-preview {
    padding: 20px;
    background: #f9f9f9;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.text-post-preview i {
    font-size: 2em;
    color: #ccc;
    margin-bottom: 10px;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
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
</style>
<style>
.profile-container {
    max-width: 1200px;
    margin: 0 auto;
}

/* Cover Photo */
.cover-photo {
    height: 300px;
    background-size: cover;
    background-position: center;
    border-radius: 15px;
    position: relative;
    background-color: #667eea;
}

.change-cover-btn {
    position: absolute;
    bottom: 20px;
    right: 20px;
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 20px;
    cursor: pointer;
    transition: background 0.3s;
}

.change-cover-btn:hover {
    background: rgba(0,0,0,0.7);
}

/* Profile Header */
.profile-header {
    display: flex;
    align-items: flex-start;
    gap: 30px;
    padding: 0 30px;
    margin-top: -60px;
}

.profile-avatar-section {
    position: relative;
}

.profile-avatar-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    border: 5px solid white;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

.change-avatar-btn {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: #e91e63;
    color: white;
    border: none;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-info {
    flex: 1;
    padding-top: 60px;
}

.profile-name-section {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 5px;
}

.verified-badge {
    color: #2196f3;
    font-size: 1.2em;
}

.online-status-badge {
    padding: 3px 10px;
    border-radius: 15px;
    font-size: 0.8em;
    font-weight: 500;
}

.online-status-badge.online {
    background: #d4edda;
    color: #155724;
}

.online-status-badge.offline {
    background: #f8d7da;
    color: #721c24;
}

.username {
    color: #666;
    margin-bottom: 15px;
}

.bio {
    color: #333;
    margin-bottom: 20px;
    line-height: 1.6;
}

.profile-details {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
}

.profile-details span {
    color: #666;
    font-size: 0.9em;
}

.profile-details i {
    color: #e91e63;
    margin-right: 5px;
}

.interests {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.interest-tag {
    background: #f0f0f0;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9em;
    color: #666;
}

.profile-actions {
    padding-top: 70px;
    display: flex;
    gap: 10px;
}

/* Profile Stats Bar */
.profile-stats-bar {
    display: flex;
    justify-content: space-around;
    padding: 20px;
    background: white;
    border-radius: 10px;
    margin: 30px 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.stat {
    text-align: center;
}

.stat .number {
    display: block;
    font-size: 1.5em;
    font-weight: bold;
    color: #e91e63;
}

.stat .label {
    color: #666;
    font-size: 0.9em;
}

/* Posts Section */
.posts-section {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.posts-section h2 {
    margin-bottom: 20px;
}

.profile-posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.profile-post-card {
    position: relative;
    aspect-ratio: 1;
    background: #f5f5f5;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
}

.post-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-thumbnail {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #333;
    color: white;
    font-size: 2em;
}

.post-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 15px;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    color: white;
    opacity: 0;
    transition: opacity 0.3s;
}

.profile-post-card:hover .post-overlay {
    opacity: 1;
}

/* Modal Styles */
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
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.close-btn {
    background: none;
    border: none;
    font-size: 2em;
    cursor: pointer;
    color: #666;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .profile-info {
        padding-top: 0;
    }
    
    .profile-details {
        justify-content: center;
    }
    
    .interests {
        justify-content: center;
    }
    
    .profile-actions {
        padding-top: 20px;
    }
    
    .cover-photo {
        height: 200px;
    }
}
</style>
<script>
// Preview image before upload
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '200px';
            img.style.borderRadius = '10px';
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

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

function followUser(userId) {
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
            const btn = document.getElementById('follow-btn-' + userId);
            const followersCount = document.getElementById('followers-count');
            
            if (data.action === 'followed') {
                btn.textContent = 'Following';
                btn.classList.add('following');
            } else {
                btn.textContent = 'Follow';
                btn.classList.remove('following');
            }
            
            followersCount.textContent = data.followers_count;
        }
    });
}

function startChat(userId) {
    window.location.href = 'chat.php?user=' + userId;
}

// Auto-hide alerts after 3 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        });
    }, 3000);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>