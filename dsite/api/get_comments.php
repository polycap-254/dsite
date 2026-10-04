<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if (isset($_GET['post_id'])) {
    $post_id = (int)$_GET['post_id'];
    
    $comments_sql = "SELECT c.*, u.username, u.full_name, u.profile_pic 
                    FROM comments c 
                    JOIN users u ON c.user_id = u.id 
                    WHERE c.post_id = $post_id AND c.parent_comment_id IS NULL 
                    ORDER BY c.created_at DESC 
                    LIMIT 20";
    $comments_result = mysqli_query($conn, $comments_sql);
    
    $html = '';
    
    while ($comment = mysqli_fetch_assoc($comments_result)) {
        $html .= '<div class="comment-item">';
        $html .= '<img src="../' . $comment['profile_pic'] . '" alt="Profile" class="comment-avatar">';
        $html .= '<div class="comment-content">';
        $html .= '<div class="comment-header">';
        $html .= '<a href="profile.php?user=' . $comment['username'] . '" class="comment-username">' . $comment['full_name'] . '</a>';
        $html .= '<span class="comment-time">' . timeAgo($comment['created_at']) . '</span>';
        $html .= '</div>';
        $html .= '<p class="comment-text">' . htmlspecialchars($comment['content']) . '</p>';
        $html .= '<button class="reply-btn" onclick="showReplyForm(' . $comment['id'] . ', ' . $post_id . ')">';
        $html .= '<i class="fas fa-reply"></i> Reply</button>';
        
        // Get replies
        $replies_sql = "SELECT c.*, u.username, u.full_name, u.profile_pic 
                       FROM comments c 
                       JOIN users u ON c.user_id = u.id 
                       WHERE c.parent_comment_id = {$comment['id']} 
                       ORDER BY c.created_at ASC";
        $replies_result = mysqli_query($conn, $replies_sql);
        
        while ($reply = mysqli_fetch_assoc($replies_result)) {
            $html .= '<div class="reply-item">';
            $html .= '<img src="../' . $reply['profile_pic'] . '" alt="Profile" class="comment-avatar-small">';
            $html .= '<div class="reply-content">';
            $html .= '<a href="profile.php?user=' . $reply['username'] . '" class="comment-username">' . $reply['full_name'] . '</a>';
            $html .= '<p class="comment-text">' . htmlspecialchars($reply['content']) . '</p>';
            $html .= '<span class="comment-time">' . timeAgo($reply['created_at']) . '</span>';
            $html .= '</div></div>';
        }
        
        // Reply form
        $html .= '<div class="reply-form" id="reply-form-' . $comment['id'] . '" style="display: none;">';
        $html .= '<form onsubmit="return submitReply(event, ' . $post_id . ', ' . $comment['id'] . ')">';
        $html .= '<input type="hidden" name="post_id" value="' . $post_id . '">';
        $html .= '<input type="hidden" name="parent_comment_id" value="' . $comment['id'] . '">';
        $html .= '<div class="comment-input-group">';
        $html .= '<input type="text" name="comment_content" class="comment-input" placeholder="Write a reply..." required>';
        $html .= '<button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-paper-plane"></i></button>';
        $html .= '</div></form></div>';
        
        $html .= '</div></div>';
    }
    
    echo json_encode(['success' => true, 'html' => $html]);
} else {
    echo json_encode(['success' => false, 'error' => 'No post_id provided']);
}
?>