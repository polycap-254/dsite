<?php
$page_title = "Posts";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$action = isset($_GET['action']) ? $_GET['action'] : 'view';
$post_id = isset($_GET['post']) ? (int)$_GET['post'] : 0;

// Handle post creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_post'])) {
    $content = sanitize($_POST['content']);
    $privacy = sanitize($_POST['privacy']);
    $media_type = 'text';
    $media_urls = null;
    
    // Handle media uploads
    $uploaded_files = [];
    if (isset($_FILES['media']) && !empty($_FILES['media']['name'][0])) {
        $files = $_FILES['media'];
        $file_count = count($files['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($files['error'][$i] === 0) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                $upload = uploadFile($file);
                if ($upload['success']) {
                    $uploaded_files[] = $upload['file_path'];
                    
                    // Determine media type
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $media_type = 'image';
                    } elseif (in_array($ext, ['mp4', 'avi', 'mov'])) {
                        $media_type = 'video';
                    }
                }
            }
        }
        
        if (!empty($uploaded_files)) {
            $media_urls = json_encode($uploaded_files);
        }
    }
    
    if (!empty($content) || !empty($uploaded_files)) {
        $sql = "INSERT INTO posts (user_id, content, media_urls, media_type, privacy) 
                VALUES ({$_SESSION['user_id']}, '$content', " . 
                ($media_urls ? "'$media_urls'" : "NULL") . ", '$media_type', '$privacy')";
        
        if (mysqli_query($conn, $sql)) {
            header('Location: posts.php?success=1');
            exit();
        }
    }
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_comment'])) {
    $post_id = (int)$_POST['post_id'];
    $comment_content = sanitize($_POST['comment_content']);
    $parent_comment_id = isset($_POST['parent_comment_id']) ? (int)$_POST['parent_comment_id'] : null;
    
    if (!empty($comment_content)) {
        $sql = "INSERT INTO comments (post_id, user_id, parent_comment_id, content) 
                VALUES ($post_id, {$_SESSION['user_id']}, " . 
                ($parent_comment_id ? $parent_comment_id : "NULL") . ", '$comment_content')";
        
        if (mysqli_query($conn, $sql)) {
            // Update comment count
            mysqli_query($conn, "UPDATE posts SET comments_count = comments_count + 1 WHERE id = $post_id");
            
            // Get post owner for notification
            $post_query = mysqli_query($conn, "SELECT user_id FROM posts WHERE id = $post_id");
            $post_data = mysqli_fetch_assoc($post_query);
            
            if ($post_data['user_id'] != $_SESSION['user_id']) {
                createNotification($post_data['user_id'], $_SESSION['user_id'], 'comment', $post_id, 'commented on your post');
            }
        }
    }
}

// Handle share
if (isset($_GET['share']) && is_numeric($_GET['share'])) {
    $share_post_id = (int)$_GET['share'];
    
    $check_sql = "SELECT id FROM shares WHERE post_id = $share_post_id AND user_id = {$_SESSION['user_id']}";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) == 0) {
        $sql = "INSERT INTO shares (post_id, user_id) VALUES ($share_post_id, {$_SESSION['user_id']})";
        mysqli_query($conn, $sql);
        
        // Update share count
        mysqli_query($conn, "UPDATE posts SET shares_count = shares_count + 1 WHERE id = $share_post_id");
        
        echo json_encode(['success' => true]);
        exit();
    }
}

// Get posts for feed
$where_clause = "WHERE (p.privacy = 'public' OR (p.privacy = 'followers' AND p.user_id IN (
    SELECT following_id FROM follows WHERE follower_id = {$_SESSION['user_id']}
)) OR p.user_id = {$_SESSION['user_id']})";

$posts_sql = "SELECT p.*, u.username, u.full_name, u.profile_pic,
              (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
              (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
              (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = {$_SESSION['user_id']}) as user_liked
              FROM posts p 
              JOIN users u ON p.user_id = u.id 
              $where_clause 
              ORDER BY p.created_at DESC 
              LIMIT 20";
$posts_result = mysqli_query($conn, $posts_sql);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="posts-container">
    <div class="posts-layout">
        <!-- Create Post Section -->
        <div class="create-post-section card">
            <h2><i class="fas fa-plus-circle"></i> Create Post</h2>
            <form method="POST" enctype="multipart/form-data" id="createPostForm">
                <div class="form-group">
                    <textarea name="content" class="form-control" rows="4" 
                              placeholder="What's on your mind? Share your thoughts, photos, or videos..."></textarea>
                </div>
                
                <div class="media-preview" id="mediaPreview" style="display: none;">
                    <div class="preview-grid" id="previewGrid"></div>
                </div>
                
                <div class="post-options">
                    <div class="form-group">
                        <label>Add Media</label>
                        <input type="file" name="media[]" id="mediaInput" multiple accept="image/*,video/*" 
                               onchange="previewMedia(this)">
                    </div>
                    
                    <div class="form-group">
                        <label>Privacy</label>
                        <select name="privacy" class="form-control">
                            <option value="public">Public</option>
                            <option value="followers">Followers Only</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="create_post" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Post
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Posts Feed -->
        <div class="posts-feed">
            <?php if (mysqli_num_rows($posts_result) > 0): ?>
                <?php while ($post = mysqli_fetch_assoc($posts_result)): ?>
                <div class="card post-card" id="post-<?php echo $post['id']; ?>">
                    <!-- Post Header -->
                    <div class="post-header">
                        <div class="post-user">
                            <img src="../<?php echo $post['profile_pic']; ?>" alt="Profile" class="user-avatar">
                            <div class="post-user-info">
                                <a href="profile.php?user=<?php echo $post['username']; ?>" class="post-username">
                                    <?php echo $post['full_name']; ?>
                                </a>
                                <div class="post-meta">
                                    <span class="post-time"><?php echo timeAgo($post['created_at']); ?></span>
                                    <?php if ($post['privacy'] != 'public'): ?>
                                    <span class="privacy-badge">
                                        <i class="fas fa-<?php echo $post['privacy'] == 'followers' ? 'users' : 'lock'; ?>"></i>
                                        <?php echo ucfirst($post['privacy']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                        <div class="post-menu">
                            <button class="btn-icon" onclick="deletePost(<?php echo $post['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Post Content -->
                    <div class="post-content">
                        <?php if ($post['content']): ?>
                        <p class="post-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($post['media_urls']): 
                            $media = json_decode($post['media_urls'], true);
                            if (is_array($media)):
                        ?>
                        <div class="post-media-grid <?php echo count($media) > 1 ? 'grid-' . min(count($media), 4) : ''; ?>">
                            <?php foreach ($media as $media_url): ?>
                                <?php if ($post['media_type'] == 'image'): ?>
                                <div class="media-item">
                                    <img src="../<?php echo $media_url; ?>" alt="Post image" 
                                         onclick="openLightbox('../<?php echo $media_url; ?>')">
                                </div>
                                <?php else: ?>
                                <div class="media-item">
                                    <video controls>
                                        <source src="../<?php echo $media_url; ?>" type="video/mp4">
                                    </video>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; endif; ?>
                    </div>
                    
                    <!-- Post Actions -->
                    <div class="post-stats-bar">
                        <span class="stat-item">
                            <span id="likes-count-<?php echo $post['id']; ?>"><?php echo $post['likes_count']; ?></span> likes
                        </span>
                        <span class="stat-item">
                            <span id="comments-count-<?php echo $post['id']; ?>"><?php echo $post['comments_count']; ?></span> comments
                        </span>
                        <span class="stat-item">
                            <?php echo $post['shares_count']; ?> shares
                        </span>
                    </div>
                    
                    <div class="post-actions">
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
                        <button class="action-btn" onclick="toggleComments(<?php echo $post['id']; ?>)">
                            <i class="far fa-comment"></i> Comment
                        </button>
                        <button class="action-btn" onclick="sharePost(<?php echo $post['id']; ?>)">
                            <i class="far fa-share-square"></i> Share
                        </button>
                    </div>
                    
                    <!-- Comments Section -->
                    <div class="comments-section" id="comments-<?php echo $post['id']; ?>" style="display: none;">
                        <!-- Add Comment -->
                        <form method="POST" class="add-comment-form" onsubmit="return submitComment(event, <?php echo $post['id']; ?>)">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <div class="comment-input-group">
                                <img src="../<?php echo $_SESSION['profile_pic']; ?>" alt="Profile" class="comment-avatar">
                                <input type="text" name="comment_content" class="comment-input" 
                                       placeholder="Write a comment..." required>
                                <button type="submit" name="add_comment" class="btn btn-sm btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                        
                        <!-- Comments List -->
                        <div class="comments-list" id="comments-list-<?php echo $post['id']; ?>">
                            <?php
                            $comments_sql = "SELECT c.*, u.username, u.full_name, u.profile_pic 
                                           FROM comments c 
                                           JOIN users u ON c.user_id = u.id 
                                           WHERE c.post_id = {$post['id']} AND c.parent_comment_id IS NULL 
                                           ORDER BY c.created_at DESC 
                                           LIMIT 10";
                            $comments_result = mysqli_query($conn, $comments_sql);
                            
                            while ($comment = mysqli_fetch_assoc($comments_result)):
                            ?>
                            <div class="comment-item">
                                <img src="../<?php echo $comment['profile_pic']; ?>" alt="Profile" class="comment-avatar">
                                <div class="comment-content">
                                    <div class="comment-header">
                                        <a href="profile.php?user=<?php echo $comment['username']; ?>" class="comment-username">
                                            <?php echo $comment['full_name']; ?>
                                        </a>
                                        <span class="comment-time"><?php echo timeAgo($comment['created_at']); ?></span>
                                    </div>
                                    <p class="comment-text"><?php echo htmlspecialchars($comment['content']); ?></p>
                                    <button class="reply-btn" onclick="showReplyForm(<?php echo $comment['id']; ?>, <?php echo $post['id']; ?>)">
                                        <i class="fas fa-reply"></i> Reply
                                    </button>
                                    
                                    <!-- Replies -->
                                    <?php
                                    $replies_sql = "SELECT c.*, u.username, u.full_name, u.profile_pic 
                                                  FROM comments c 
                                                  JOIN users u ON c.user_id = u.id 
                                                  WHERE c.parent_comment_id = {$comment['id']} 
                                                  ORDER BY c.created_at ASC";
                                    $replies_result = mysqli_query($conn, $replies_sql);
                                    
                                    while ($reply = mysqli_fetch_assoc($replies_result)):
                                    ?>
                                    <div class="reply-item">
                                        <img src="../<?php echo $reply['profile_pic']; ?>" alt="Profile" class="comment-avatar-small">
                                        <div class="reply-content">
                                            <a href="profile.php?user=<?php echo $reply['username']; ?>" class="comment-username">
                                                <?php echo $reply['full_name']; ?>
                                            </a>
                                            <p class="comment-text"><?php echo htmlspecialchars($reply['content']); ?></p>
                                            <span class="comment-time"><?php echo timeAgo($reply['created_at']); ?></span>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                    
                                    <!-- Reply Form -->
                                    <div class="reply-form" id="reply-form-<?php echo $comment['id']; ?>" style="display: none;">
                                        <form onsubmit="return submitReply(event, <?php echo $post['id']; ?>, <?php echo $comment['id']; ?>)">
                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                            <input type="hidden" name="parent_comment_id" value="<?php echo $comment['id']; ?>">
                                            <div class="comment-input-group">
                                                <input type="text" name="comment_content" class="comment-input" 
                                                       placeholder="Write a reply..." required>
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card empty-state">
                    <i class="fas fa-newspaper"></i>
                    <h3>No Posts Yet</h3>
                    <p>Create your first post or follow people to see their posts</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="close-lightbox">&times;</span>
    <img id="lightbox-img" src="" alt="Lightbox">
</div>

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

<script>
// Preview media before upload
function previewMedia(input) {
    const preview = document.getElementById('mediaPreview');
    const previewGrid = document.getElementById('previewGrid');
    
    previewGrid.innerHTML = '';
    
    if (input.files && input.files.length > 0) {
        preview.style.display = 'block';
        
        for (let i = 0; i < input.files.length; i++) {
            const file = input.files[i];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                
                if (file.type.startsWith('image/')) {
                    div.innerHTML = `<img src="${e.target.result}" alt="Preview">
                        <button class="remove-media" onclick="removeMedia(this)">×</button>`;
                } else if (file.type.startsWith('video/')) {
                    div.innerHTML = `<video src="${e.target.result}" controls></video>
                        <button class="remove-media" onclick="removeMedia(this)">×</button>`;
                }
                
                previewGrid.appendChild(div);
            };
            
            reader.readAsDataURL(file);
        }
    } else {
        preview.style.display = 'none';
    }
}

function removeMedia(btn) {
    btn.parentElement.remove();
    if (document.querySelectorAll('.preview-item').length === 0) {
        document.getElementById('mediaPreview').style.display = 'none';
    }
}

// Like post via AJAX
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
            const likeBtn = document.getElementById('like-btn-' + postId);
            const likesCount = document.getElementById('likes-count-' + postId);
            const icon = likeBtn.querySelector('i');
            
            if (data.action === 'liked') {
                likeBtn.classList.add('liked');
                icon.className = 'fas fa-heart';
            } else {
                likeBtn.classList.remove('liked');
                icon.className = 'far fa-heart';
            }
            
            likesCount.textContent = data.likes_count;
        }
    });
}

// Toggle comments
function toggleComments(postId) {
    const commentsSection = document.getElementById('comments-' + postId);
    if (commentsSection.style.display === 'none') {
        commentsSection.style.display = 'block';
        loadComments(postId);
    } else {
        commentsSection.style.display = 'none';
    }
}

// Load comments via AJAX
function loadComments(postId) {
    fetch('../api/get_comments.php?post_id=' + postId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('comments-list-' + postId).innerHTML = data.html;
            }
        });
}

// Submit comment via AJAX
function submitComment(event, postId) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('../api/add_comment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            form.reset();
            loadComments(postId);
            document.getElementById('comments-count-' + postId).textContent = data.comments_count;
        }
    });
    
    return false;
}

// Submit reply via AJAX
function submitReply(event, postId, commentId) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('../api/add_comment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            form.reset();
            document.getElementById('reply-form-' + commentId).style.display = 'none';
            loadComments(postId);
        }
    });
    
    return false;
}

// Show reply form
function showReplyForm(commentId, postId) {
    const replyForm = document.getElementById('reply-form-' + commentId);
    replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
}

// Share post
function sharePost(postId) {
    fetch('posts.php?share=' + postId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Post shared successfully!');
            }
        });
}

// Delete post
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

// Lightbox
function openLightbox(src) {
    document.getElementById('lightbox').style.display = 'flex';
    document.getElementById('lightbox-img').src = src;
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>