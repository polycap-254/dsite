<?php
$page_title = "Chats";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

// Start a new chat with a user
if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $other_user_id = (int)$_GET['user'];
    
    // Check if chat room already exists
    $check_sql = "SELECT cr.id FROM chat_rooms cr 
                  JOIN chat_participants cp1 ON cr.id = cp1.room_id AND cp1.user_id = {$_SESSION['user_id']}
                  JOIN chat_participants cp2 ON cr.id = cp2.room_id AND cp2.user_id = $other_user_id
                  WHERE cr.type = 'direct'";
    $result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($result) > 0) {
        $room = mysqli_fetch_assoc($result);
        $room_id = $room['id'];
    } else {
        // Create new chat room
        mysqli_query($conn, "INSERT INTO chat_rooms (type, created_by) VALUES ('direct', {$_SESSION['user_id']})");
        $room_id = mysqli_insert_id($conn);
        
        // Add participants
        mysqli_query($conn, "INSERT INTO chat_participants (room_id, user_id) VALUES ($room_id, {$_SESSION['user_id']})");
        mysqli_query($conn, "INSERT INTO chat_participants (room_id, user_id) VALUES ($room_id, $other_user_id)");
    }
    
    header("Location: chat.php?room=$room_id");
    exit();
}

// Get user's chat rooms
$rooms_sql = "SELECT cr.*, 
              (SELECT COUNT(*) FROM messages WHERE room_id = cr.id AND is_read = 0 AND sender_id != {$_SESSION['user_id']}) as unread_count,
              (SELECT message FROM messages WHERE room_id = cr.id ORDER BY created_at DESC LIMIT 1) as last_message,
              (SELECT created_at FROM messages WHERE room_id = cr.id ORDER BY created_at DESC LIMIT 1) as last_message_time
              FROM chat_rooms cr 
              JOIN chat_participants cp ON cr.id = cp.room_id 
              WHERE cp.user_id = {$_SESSION['user_id']}
              ORDER BY last_message_time DESC";
$rooms_result = mysqli_query($conn, $rooms_sql);

// Get active chat room
$active_room = null;
$messages = [];
$other_user = null;

if (isset($_GET['room']) && is_numeric($_GET['room'])) {
    $room_id = (int)$_GET['room'];
    
    // Verify user is participant
    $verify_sql = "SELECT * FROM chat_participants WHERE room_id = $room_id AND user_id = {$_SESSION['user_id']}";
    if (mysqli_num_rows(mysqli_query($conn, $verify_sql)) > 0) {
        $room_sql = "SELECT * FROM chat_rooms WHERE id = $room_id";
        $active_room = mysqli_fetch_assoc(mysqli_query($conn, $room_sql));
        
        // Get messages
        $messages_sql = "SELECT m.*, u.username, u.full_name, u.profile_pic 
                        FROM messages m 
                        JOIN users u ON m.sender_id = u.id 
                        WHERE m.room_id = $room_id 
                        ORDER BY m.created_at ASC 
                        LIMIT 50";
        $messages_result = mysqli_query($conn, $messages_sql);
        while ($msg = mysqli_fetch_assoc($messages_result)) {
            $messages[] = $msg;
        }
        
        // Mark messages as read
        mysqli_query($conn, "UPDATE messages SET is_read = 1 WHERE room_id = $room_id AND sender_id != {$_SESSION['user_id']}");
        
        // Get other participant info for direct chats
        if ($active_room && $active_room['type'] == 'direct') {
            $other_sql = "SELECT u.* FROM users u 
                          JOIN chat_participants cp ON u.id = cp.user_id 
                          WHERE cp.room_id = $room_id 
                          AND cp.user_id != {$_SESSION['user_id']}";
            $other_result = mysqli_query($conn, $other_sql);
            $other_user = mysqli_fetch_assoc($other_result);
        }
    }
}

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $room_id = (int)$_POST['room_id'];
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $file_url = null;
    $message_type = 'text';
    
    // Handle file upload
    if (isset($_FILES['chat_file']) && $_FILES['chat_file']['error'] === 0) {
        $upload = uploadFile($_FILES['chat_file']);
        if ($upload['success']) {
            $file_url = $upload['file_path'];
            $ext = strtolower(pathinfo($_FILES['chat_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $message_type = 'image';
            } elseif (in_array($ext, ['mp4', 'avi', 'mov', 'webm'])) {
                $message_type = 'video';
            } else {
                $message_type = 'file';
            }
            if (empty($message)) {
                $message = $_FILES['chat_file']['name'];
            }
        }
    }
    
    if (!empty($message) || $file_url) {
        $message = mysqli_real_escape_string($conn, $message);
        $file_url_sql = $file_url ? "'" . mysqli_real_escape_string($conn, $file_url) . "'" : "NULL";
        
        $sql = "INSERT INTO messages (room_id, sender_id, message, file_url, message_type) 
                VALUES ($room_id, {$_SESSION['user_id']}, '$message', $file_url_sql, '$message_type')";
        
        if (mysqli_query($conn, $sql)) {
            // Send notifications to other participants
            $participants_sql = "SELECT user_id FROM chat_participants WHERE room_id = $room_id AND user_id != {$_SESSION['user_id']}";
            $participants_result = mysqli_query($conn, $participants_sql);
            
            if ($participants_result) {
                $notif_msg = $message_type == 'image' ? 'sent you an image' : 
                            ($message_type == 'file' ? 'sent you a file' : 'sent you a message');
                
                while ($participant = mysqli_fetch_assoc($participants_result)) {
                    createNotification($participant['user_id'], $_SESSION['user_id'], 'message', $room_id, $notif_msg);
                }
            }
        }
    }
    
    header("Location: chat.php?room=$room_id");
    exit();
}

// Create group chat
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_group'])) {
    $group_name = sanitize($_POST['group_name']);
    $members = isset($_POST['members']) ? $_POST['members'] : [];
    
    if (!empty($group_name) && !empty($members)) {
        mysqli_query($conn, "INSERT INTO chat_rooms (name, type, created_by) VALUES ('$group_name', 'group', {$_SESSION['user_id']})");
        $group_room_id = mysqli_insert_id($conn);
        
        // Add creator
        mysqli_query($conn, "INSERT INTO chat_participants (room_id, user_id) VALUES ($group_room_id, {$_SESSION['user_id']})");
        
        // Add members
        foreach ($members as $member_id) {
            $member_id = (int)$member_id;
            mysqli_query($conn, "INSERT INTO chat_participants (room_id, user_id) VALUES ($group_room_id, $member_id)");
        }
        
        header("Location: chat.php?room=$group_room_id&group_created=1");
        exit();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="chat-container">
    <div class="chat-layout">
        <!-- Chat List Sidebar -->
        <div class="chat-sidebar">
            <div class="chat-sidebar-header">
                <h2><i class="fas fa-comments"></i> Chats</h2>
                <button class="btn btn-sm btn-primary" onclick="showNewChatModal()">
                    <i class="fas fa-plus"></i> New
                </button>
            </div>
            
            <div class="chat-search">
                <input type="text" class="form-control" placeholder="Search chats..." id="chatSearch">
            </div>
            
            <div class="chat-list">
                <?php if (mysqli_num_rows($rooms_result) > 0): ?>
                    <?php while ($room = mysqli_fetch_assoc($rooms_result)): 
                        $room_name = $room['name'];
                        $room_avatar = 'assets/uploads/default-avatar.png';
                        
                        if ($room['type'] == 'direct') {
                            $other_sql = "SELECT u.username, u.full_name, u.profile_pic FROM users u 
                                         JOIN chat_participants cp ON u.id = cp.user_id 
                                         WHERE cp.room_id = {$room['id']} 
                                         AND cp.user_id != {$_SESSION['user_id']}";
                            $other = mysqli_fetch_assoc(mysqli_query($conn, $other_sql));
                            if ($other) {
                                $room_name = $other['full_name'] ?: $other['username'];
                                $room_avatar = $other['profile_pic'];
                            }
                        }
                    ?>
                    <a href="chat.php?room=<?php echo $room['id']; ?>" 
                       class="chat-item <?php echo (isset($_GET['room']) && $_GET['room'] == $room['id']) ? 'active' : ''; ?>">
                        <img src="../<?php echo $room_avatar; ?>" alt="Avatar" class="chat-avatar">
                        <div class="chat-item-info">
                            <div class="chat-item-header">
                                <span class="chat-name"><?php echo htmlspecialchars($room_name); ?></span>
                                <span class="chat-time">
                                    <?php echo $room['last_message_time'] ? timeAgo($room['last_message_time']) : ''; ?>
                                </span>
                            </div>
                            <div class="chat-item-preview">
                                <span class="last-message">
                                    <?php echo $room['last_message'] ? substr(htmlspecialchars($room['last_message']), 0, 30) : 'Start chatting...'; ?>
                                </span>
                                <?php if ($room['unread_count'] > 0): ?>
                                <span class="unread-badge"><?php echo $room['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <p>No chats yet</p>
                        <button class="btn btn-primary btn-sm" onclick="showNewChatModal()">Start a Chat</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Chat Main Area -->
        <div class="chat-main">
            <?php if ($active_room): ?>
                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="chat-header-info">
                        <?php if ($active_room['type'] == 'direct' && $other_user): ?>
                            <img src="../<?php echo $other_user['profile_pic']; ?>" alt="Avatar" class="chat-header-avatar">
                            <div>
                                <h3><?php echo htmlspecialchars($other_user['full_name'] ?: $other_user['username']); ?></h3>
                                <span class="online-status-text <?php echo isset($other_user['is_online']) && $other_user['is_online'] ? 'online' : 'offline'; ?>">
                                    <?php echo isset($other_user['is_online']) && $other_user['is_online'] ? 'Online' : 'Offline'; ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div>
                                <h3><?php echo htmlspecialchars($active_room['name']); ?> <small>(Group)</small></h3>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="chat-header-actions">
                        <button class="btn-icon" title="Video Call">
                            <i class="fas fa-video"></i>
                        </button>
                        <button class="btn-icon" title="Voice Call">
                            <i class="fas fa-phone"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Messages Area -->
                <div class="chat-messages" id="chatMessages">
                    <?php foreach ($messages as $msg): 
                        $is_mine = $msg['sender_id'] == $_SESSION['user_id'];
                    ?>
                    <div class="message <?php echo $is_mine ? 'message-sent' : 'message-received'; ?>">
                        <?php if (!$is_mine): ?>
                        <img src="../<?php echo $msg['profile_pic']; ?>" alt="Avatar" class="message-avatar">
                        <?php endif; ?>
                        <div class="message-content">
                            <?php if ($msg['message_type'] == 'text'): ?>
                                <div class="message-bubble">
                                    <p><?php echo htmlspecialchars($msg['message']); ?></p>
                                </div>
                            <?php elseif ($msg['message_type'] == 'image'): ?>
                                <div class="message-bubble">
                                    <img src="../<?php echo $msg['file_url']; ?>" alt="Image" class="message-image" 
                                         onclick="openLightbox('../<?php echo $msg['file_url']; ?>')">
                                    <?php if ($msg['message'] && $msg['message'] != basename($msg['file_url'])): ?>
                                        <p><?php echo htmlspecialchars($msg['message']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($msg['message_type'] == 'file'): ?>
                                <div class="message-bubble message-file">
                                    <i class="fas fa-file"></i>
                                    <a href="../<?php echo $msg['file_url']; ?>" download><?php echo htmlspecialchars($msg['message']); ?></a>
                                </div>
                            <?php endif; ?>
                            <span class="message-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
                        </div>
                        <?php if ($is_mine): ?>
                        <img src="../<?php echo $_SESSION['profile_pic']; ?>" alt="Avatar" class="message-avatar">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Message Input -->
                <div class="chat-input">
                    <form method="POST" enctype="multipart/form-data" id="messageForm" onsubmit="return sendMessage(event)">
                        <input type="hidden" name="room_id" value="<?php echo $active_room['id']; ?>">
                        <div class="message-input-group">
                            <label for="chatFile" class="attach-btn" title="Attach file">
                                <i class="fas fa-paperclip"></i>
                                <input type="file" name="chat_file" id="chatFile" style="display: none;" 
                                       accept="image/*,video/*,.pdf,.doc,.docx" onchange="handleFileSelect(this)">
                            </label>
                            <input type="text" name="message" class="message-input" 
                                   placeholder="Type a message..." autocomplete="off" id="messageInput">
                            <button type="submit" name="send_message" class="send-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div id="filePreview" class="file-preview" style="display:none;"></div>
                    </form>
                </div>
            <?php else: ?>
                <div class="chat-empty">
                    <i class="fas fa-comments"></i>
                    <h2>Your Messages</h2>
                    <p>Select a conversation or start a new one</p>
                    <button class="btn btn-primary" onclick="showNewChatModal()">
                        <i class="fas fa-plus"></i> New Chat
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- New Chat Modal -->
<div id="newChatModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>New Chat</h2>
            <button class="close-btn" onclick="closeModal('newChatModal')">&times;</button>
        </div>
        
        <div class="new-chat-tabs">
            <button class="tab-btn active" onclick="switchTab('direct')">Direct Message</button>
            <button class="tab-btn" onclick="switchTab('group')">Group Chat</button>
        </div>
        
        <!-- Direct Message Tab -->
        <div id="directChatTab" class="tab-content">
            <div class="form-group">
                <label>Search Users</label>
                <input type="text" class="form-control" placeholder="Search by username..." 
                       onkeyup="searchUsers(this.value)">
            </div>
            <div class="users-list" id="usersList">
                <?php
                $users_sql = "SELECT id, username, full_name, profile_pic 
                             FROM users 
                             WHERE id != {$_SESSION['user_id']} 
                             AND account_status = 'active' 
                             ORDER BY full_name 
                             LIMIT 20";
                $users_result = mysqli_query($conn, $users_sql);
                
                while ($user = mysqli_fetch_assoc($users_result)):
                ?>
                <div class="user-item" onclick="startDirectChat(<?php echo $user['id']; ?>)">
                    <img src="../<?php echo $user['profile_pic']; ?>" alt="Avatar" class="user-avatar-small">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></span>
                        <span class="user-username">@<?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Group Chat Tab -->
        <div id="groupChatTab" class="tab-content" style="display: none;">
            <form method="POST">
                <div class="form-group">
                    <label>Group Name</label>
                    <input type="text" name="group_name" class="form-control" placeholder="Enter group name" required>
                </div>
                <div class="form-group">
                    <label>Add Members</label>
                    <div class="members-list">
                        <?php
                        mysqli_data_seek($users_result, 0);
                        while ($user = mysqli_fetch_assoc($users_result)):
                        ?>
                        <label class="member-checkbox">
                            <input type="checkbox" name="members[]" value="<?php echo $user['id']; ?>">
                            <img src="../<?php echo $user['profile_pic']; ?>" alt="Avatar">
                            <span><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></span>
                        </label>
                        <?php endwhile; ?>
                    </div>
                </div>
                <button type="submit" name="create_group" class="btn btn-primary btn-block">Create Group</button>
            </form>
        </div>
    </div>
</div>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <span class="close-lightbox">&times;</span>
    <img id="lightbox-img" src="" alt="Lightbox">
</div>

<style>
/* Chat Layout */
.chat-container {
    height: calc(100vh - 60px - 60px);
    margin: -20px;
    display: flex;
}

.chat-layout {
    display: flex;
    width: 100%;
    height: 100%;
}

/* Chat Sidebar */
.chat-sidebar {
    width: 350px;
    background: white;
    border-right: 1px solid #e0e0e0;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
}

.chat-sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
}

.chat-sidebar-header h2 {
    font-size: 1.2em;
    color: #333;
}

.chat-search {
    padding: 12px 15px;
}

.chat-search input {
    width: 100%;
    padding: 8px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 20px;
    font-size: 14px;
    background: #f5f5f5;
}

.chat-list {
    flex: 1;
    overflow-y: auto;
}

.chat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    text-decoration: none;
    color: inherit;
    transition: background 0.2s;
    border-bottom: 1px solid #f5f5f5;
}

.chat-item:hover,
.chat-item.active {
    background: #f5f5f5;
}

.chat-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.chat-item-info {
    flex: 1;
    min-width: 0;
}

.chat-item-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 3px;
}

.chat-name {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.chat-time {
    font-size: 11px;
    color: #999;
    flex-shrink: 0;
}

.chat-item-preview {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.last-message {
    font-size: 13px;
    color: #666;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 180px;
}

.unread-badge {
    background: #e91e63;
    color: white;
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 11px;
    flex-shrink: 0;
}

/* Chat Main */
.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #f5f5f5;
    min-width: 0;
}

.chat-header {
    background: white;
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e0e0e0;
}

.chat-header-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-header-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
}

.chat-header-info h3 {
    font-size: 1em;
    margin-bottom: 2px;
}

.chat-header-info small {
    color: #999;
    font-weight: normal;
}

.online-status-text {
    font-size: 12px;
}

.online-status-text.online {
    color: #4caf50;
}

.online-status-text.offline {
    color: #999;
}

.chat-header-actions {
    display: flex;
    gap: 5px;
}

/* Chat Messages */
.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.message {
    display: flex;
    gap: 8px;
    max-width: 70%;
    align-items: flex-end;
}

.message-sent {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-received {
    align-self: flex-start;
}

.message-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.message-content {
    display: flex;
    flex-direction: column;
}

.message-bubble {
    padding: 10px 14px;
    border-radius: 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    max-width: 100%;
    word-wrap: break-word;
}

.message-received .message-bubble {
    background: white;
    border-bottom-left-radius: 4px;
}

.message-sent .message-bubble {
    background: #e91e63;
    color: white;
    border-bottom-right-radius: 4px;
}

.message-bubble p {
    margin: 0;
    font-size: 14px;
    line-height: 1.4;
}

.message-image {
    max-width: 250px;
    border-radius: 10px;
    cursor: pointer;
}

.message-file {
    display: flex;
    align-items: center;
    gap: 8px;
}

.message-file a {
    color: inherit;
    text-decoration: underline;
}

.message-time {
    font-size: 11px;
    color: #999;
    margin-top: 3px;
    padding: 0 4px;
}

.message-sent .message-time {
    text-align: right;
}

/* Chat Input */
.chat-input {
    background: white;
    padding: 12px 20px;
    border-top: 1px solid #e0e0e0;
}

.message-input-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.attach-btn {
    cursor: pointer;
    color: #666;
    font-size: 1.2em;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s;
}

.attach-btn:hover {
    background: #f0f0f0;
    color: #e91e63;
}

.message-input {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 25px;
    outline: none;
    font-size: 14px;
    font-family: inherit;
}

.message-input:focus {
    border-color: #e91e63;
}

.send-btn {
    background: #e91e63;
    color: white;
    border: none;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.send-btn:hover {
    background: #c2185b;
    transform: scale(1.05);
}

.file-preview {
    padding: 8px 12px;
    margin-top: 8px;
    background: #f9f9f9;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
}

.file-preview img {
    max-width: 60px;
    max-height: 60px;
    border-radius: 5px;
}

.file-preview .remove-file {
    background: #ff4444;
    color: white;
    border: none;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    margin-left: auto;
}

/* Chat Empty */
.chat-empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #666;
}

.chat-empty i {
    font-size: 4em;
    color: #ccc;
    margin-bottom: 15px;
}

.chat-empty h2 {
    margin-bottom: 8px;
}

.chat-empty p {
    margin-bottom: 20px;
    font-size: 14px;
}

/* New Chat Modal */
.new-chat-tabs {
    display: flex;
    border-bottom: 2px solid #e0e0e0;
    margin-bottom: 20px;
}

.tab-btn {
    flex: 1;
    padding: 10px;
    background: none;
    border: none;
    cursor: pointer;
    color: #666;
    font-weight: 500;
    transition: all 0.2s;
}

.tab-btn.active {
    color: #e91e63;
    border-bottom: 2px solid #e91e63;
    margin-bottom: -2px;
}

.users-list {
    max-height: 350px;
    overflow-y: auto;
}

.user-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.user-item:hover {
    background: #f5f5f5;
}

.user-info {
    flex: 1;
}

.user-name {
    font-weight: 500;
    display: block;
}

.user-username {
    font-size: 12px;
    color: #999;
}

.members-list {
    max-height: 300px;
    overflow-y: auto;
}

.member-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    cursor: pointer;
    border-radius: 8px;
    transition: background 0.2s;
}

.member-checkbox:hover {
    background: #f5f5f5;
}

.member-checkbox img {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
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
    z-index: 3000;
    justify-content: center;
    align-items: center;
}

.lightbox img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 8px;
}

.close-lightbox {
    position: absolute;
    top: 20px;
    right: 40px;
    color: white;
    font-size: 40px;
    cursor: pointer;
}

/* Responsive */
@media (max-width: 768px) {
    .chat-sidebar {
        display: none;
    }
    
    .chat-sidebar.active {
        display: flex;
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 100%;
        z-index: 100;
    }
    
    .message {
        max-width: 85%;
    }
}
</style>

<script>
// Scroll to bottom on load
function scrollToBottom() {
    const messagesDiv = document.getElementById('chatMessages');
    if (messagesDiv) {
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
}

scrollToBottom();

// Load messages function
function loadMessages() {
    const roomId = <?php echo isset($active_room) ? $active_room['id'] : 'null'; ?>;
    if (!roomId) return;
    
    fetch('../api/get_messages.php?room_id=' + roomId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('chatMessages').innerHTML = data.html;
                scrollToBottom();
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        });
}

// Refresh messages every 3 seconds
setInterval(loadMessages, 3000);

// Handle file selection
function handleFileSelect(input) {
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';
    preview.style.display = 'none';
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        preview.style.display = 'flex';
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <span>${file.name}</span>
                    <button type="button" class="remove-file" onclick="removeFile()">×</button>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = `
                <i class="fas fa-file"></i>
                <span>${file.name}</span>
                <button type="button" class="remove-file" onclick="removeFile()">×</button>
            `;
        }
    }
}

function removeFile() {
    document.getElementById('chatFile').value = '';
    document.getElementById('filePreview').style.display = 'none';
}

// Send message via AJAX
function sendMessage(event) {
    event.preventDefault();
    
    const form = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    const fileInput = document.getElementById('chatFile');
    const formData = new FormData(form);
    
    // Check if there's text or file
    if (!messageInput.value.trim() && !fileInput.files[0]) {
        return false;
    }
    
    // Disable send button temporarily
    const sendBtn = form.querySelector('.send-btn');
    if (sendBtn) {
        sendBtn.disabled = true;
        sendBtn.style.opacity = '0.6';
    }
    
    fetch('../api/send_message.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Send message response:', data);
        
        if (data.success) {
            // Clear inputs
            messageInput.value = '';
            fileInput.value = '';
            document.getElementById('filePreview').style.display = 'none';
            
            // Reload messages immediately
            loadMessages();
        } else {
            alert('Error: ' + (data.error || 'Failed to send message'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to send message. Please try again.');
    })
    .finally(() => {
        // Re-enable send button
        if (sendBtn) {
            sendBtn.disabled = false;
            sendBtn.style.opacity = '1';
        }
    });
    
    return false;
}

// Submit message with Enter key
document.getElementById('messageInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (this.value.trim() || document.getElementById('chatFile').files[0]) {
            sendMessage(e);
        }
    }
});

// New chat modal functions
function showNewChatModal() {
    document.getElementById('newChatModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function switchTab(tab) {
    document.getElementById('directChatTab').style.display = tab === 'direct' ? 'block' : 'none';
    document.getElementById('groupChatTab').style.display = tab === 'group' ? 'block' : 'none';
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

function startDirectChat(userId) {
    window.location.href = 'chat.php?user=' + userId;
}

function searchUsers(query) {
    if (query.length >= 2) {
        fetch('../api/search_users.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('usersList').innerHTML = data.html;
                }
            });
    }
}

// Lightbox functions
function openLightbox(src) {
    document.getElementById('lightbox').style.display = 'flex';
    document.getElementById('lightbox-img').src = src;
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
}

// Close modal on outside click
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
    if (event.target.classList.contains('lightbox')) {
        event.target.style.display = 'none';
    }
}

// Mobile sidebar toggle
function toggleChatSidebar() {
    document.querySelector('.chat-sidebar').classList.toggle('active');
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>