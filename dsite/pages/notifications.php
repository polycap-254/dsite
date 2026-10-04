<?php
$page_title = "Notifications";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Mark all as read if requested
if (isset($_GET['mark_read'])) {
    mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
    header('Location: notifications.php');
    exit();
}

// Mark single notification as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $notif_id = (int)$_GET['read'];
    mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE id = $notif_id AND user_id = $user_id");
}

// Delete notification
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $notif_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM notifications WHERE id = $notif_id AND user_id = $user_id");
}

// Get notifications
$notifications_sql = "SELECT n.*, u.username, u.full_name, u.profile_pic 
                     FROM notifications n 
                     LEFT JOIN users u ON n.from_user_id = u.id 
                     WHERE n.user_id = $user_id 
                     ORDER BY n.created_at DESC 
                     LIMIT 50";
$notifications_result = mysqli_query($conn, $notifications_sql);

// Get counts
$total_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread
    FROM notifications 
    WHERE user_id = $user_id";
$counts = mysqli_fetch_assoc(mysqli_query($conn, $total_sql));

require_once __DIR__ . '/../includes/header.php';
?>

<div class="notifications-container">
    <div class="notifications-header">
        <div class="header-left">
            <h1><i class="fas fa-bell"></i> Notifications</h1>
            <?php if ($counts['unread'] > 0): ?>
            <span class="unread-count"><?php echo $counts['unread']; ?> new</span>
            <?php endif; ?>
        </div>
        <div class="header-actions">
            <?php if ($counts['unread'] > 0): ?>
            <a href="?mark_read=1" class="btn btn-outline btn-sm">
                <i class="fas fa-check-double"></i> Mark All Read
            </a>
            <?php endif; ?>
            <button class="btn btn-outline btn-sm" onclick="toggleFilter()">
                <i class="fas fa-filter"></i> Filter
            </button>
        </div>
    </div>
    
    <!-- Filter Tabs -->
    <div class="filter-tabs" id="filterTabs">
        <button class="filter-tab active" onclick="filterNotifications('all')">All</button>
        <button class="filter-tab" onclick="filterNotifications('unread')">Unread</button>
        <button class="filter-tab" onclick="filterNotifications('like')">Likes</button>
        <button class="filter-tab" onclick="filterNotifications('comment')">Comments</button>
        <button class="filter-tab" onclick="filterNotifications('follow')">Follows</button>
        <button class="filter-tab" onclick="filterNotifications('message')">Messages</button>
    </div>
    
    <!-- Notifications List -->
    <div class="notifications-list">
        <?php if (mysqli_num_rows($notifications_result) > 0): ?>
            <?php 
            $current_date = '';
            while ($notification = mysqli_fetch_assoc($notifications_result)): 
                $notif_date = date('Y-m-d', strtotime($notification['created_at']));
                
                // Show date separator
                if ($current_date != $notif_date):
                    $current_date = $notif_date;
                    $today = date('Y-m-d');
                    $yesterday = date('Y-m-d', strtotime('-1 day'));
                    
                    if ($notif_date == $today) {
                        $date_label = 'Today';
                    } elseif ($notif_date == $yesterday) {
                        $date_label = 'Yesterday';
                    } else {
                        $date_label = date('F j, Y', strtotime($notif_date));
                    }
            ?>
                <div class="date-separator">
                    <span><?php echo $date_label; ?></span>
                </div>
            <?php endif; ?>
            
            <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>" 
                 data-type="<?php echo $notification['type']; ?>"
                 id="notif-<?php echo $notification['id']; ?>">
                
                <!-- Notification Icon -->
                <div class="notification-icon <?php echo $notification['type']; ?>">
                    <?php
                    switch($notification['type']) {
                        case 'like':
                            echo '<i class="fas fa-heart"></i>';
                            break;
                        case 'comment':
                        case 'reply':
                            echo '<i class="fas fa-comment"></i>';
                            break;
                        case 'follow':
                            echo '<i class="fas fa-user-plus"></i>';
                            break;
                        case 'share':
                            echo '<i class="fas fa-share"></i>';
                            break;
                        case 'message':
                            echo '<i class="fas fa-envelope"></i>';
                            break;
                        default:
                            echo '<i class="fas fa-bell"></i>';
                    }
                    ?>
                </div>
                
                <!-- Notification Content -->
                <div class="notification-content">
                    <?php if ($notification['from_user_id']): ?>
                    <img src="../<?php echo $notification['profile_pic']; ?>" alt="Avatar" class="notification-avatar">
                    <?php endif; ?>
                    
                    <div class="notification-text">
                        <?php if ($notification['from_user_id']): ?>
                        <a href="profile.php?user=<?php echo $notification['username']; ?>" class="notification-user">
                            <?php echo $notification['full_name'] ?: $notification['username']; ?>
                        </a>
                        <?php endif; ?>
                        
                        <span class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></span>
                        
                        <div class="notification-time">
                            <?php echo timeAgo($notification['created_at']); ?>
                            <?php if (!$notification['is_read']): ?>
                            <span class="unread-dot"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="notification-actions">
                    <?php
                    // Determine link based on notification type
                    $link = '#';
                    switch($notification['type']) {
                        case 'like':
                        case 'comment':
                        case 'reply':
                        case 'share':
                            $link = "posts.php?post={$notification['reference_id']}";
                            break;
                        case 'follow':
                            $link = "profile.php?user={$notification['username']}";
                            break;
                        case 'message':
                            $link = "chat.php?room={$notification['reference_id']}";
                            break;
                    }
                    ?>
                    <a href="<?php echo $link; ?>" class="btn-icon" title="View">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    <button class="btn-icon" title="Delete" onclick="deleteNotification(<?php echo $notification['id']; ?>)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <h3>No Notifications</h3>
                <p>You're all caught up! When you get notifications, they'll show up here.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Load More -->
    <?php if (mysqli_num_rows($notifications_result) == 50): ?>
    <div class="load-more">
        <button class="btn btn-outline" onclick="loadMoreNotifications()">
            <i class="fas fa-spinner"></i> Load More
        </button>
    </div>
    <?php endif; ?>
</div>

<style>
.notifications-container {
    max-width: 700px;
    margin: 0 auto;
}

.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding: 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.header-left h1 {
    font-size: 1.5em;
    color: #333;
}

.unread-count {
    background: #e91e63;
    color: white;
    padding: 3px 12px;
    border-radius: 15px;
    font-size: 0.85em;
    font-weight: 500;
}

.header-actions {
    display: flex;
    gap: 10px;
}

/* Filter Tabs */
.filter-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    padding: 15px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow-x: auto;
}

.filter-tab {
    padding: 8px 20px;
    border: none;
    background: #f5f5f5;
    border-radius: 20px;
    cursor: pointer;
    color: #666;
    white-space: nowrap;
    transition: all 0.3s;
}

.filter-tab.active,
.filter-tab:hover {
    background: #e91e63;
    color: white;
}

/* Date Separator */
.date-separator {
    text-align: center;
    margin: 20px 0;
    position: relative;
}

.date-separator span {
    background: #f5f5f5;
    padding: 5px 15px;
    border-radius: 15px;
    color: #666;
    font-size: 0.9em;
    position: relative;
    z-index: 1;
}

.date-separator:before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e0e0e0;
}

/* Notification Items */
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.notification-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: all 0.3s;
    position: relative;
}

.notification-item:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transform: translateX(5px);
}

.notification-item.unread {
    background: #fef5f8;
    border-left: 4px solid #e91e63;
}

.notification-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2em;
    flex-shrink: 0;
}

.notification-icon.like {
    background: #fee2e8;
    color: #e91e63;
}

.notification-icon.comment,
.notification-icon.reply {
    background: #e3f2fd;
    color: #2196f3;
}

.notification-icon.follow {
    background: #e8f5e9;
    color: #4caf50;
}

.notification-icon.message {
    background: #fff3e0;
    color: #ff9800;
}

.notification-icon.share {
    background: #f3e5f5;
    color: #9c27b0;
}

.notification-content {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 10px;
}

.notification-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
}

.notification-text {
    flex: 1;
}

.notification-user {
    font-weight: 600;
    color: #333;
    text-decoration: none;
    margin-right: 5px;
}

.notification-user:hover {
    color: #e91e63;
}

.notification-message {
    color: #555;
}

.notification-time {
    display: block;
    font-size: 0.85em;
    color: #999;
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.unread-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #e91e63;
    display: inline-block;
}

.notification-actions {
    display: flex;
    gap: 5px;
    opacity: 0;
    transition: opacity 0.3s;
}

.notification-item:hover .notification-actions {
    opacity: 1;
}

.btn-icon {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.3s;
    text-decoration: none;
}

.btn-icon:hover {
    background: #f5f5f5;
    color: #333;
}

.load-more {
    text-align: center;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .notification-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .notification-actions {
        opacity: 1;
        align-self: flex-end;
    }
    
    .filter-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
    }
}
</style>

<script>
// Filter notifications
function filterNotifications(type) {
    // Update active tab
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Filter items
    document.querySelectorAll('.notification-item').forEach(item => {
        if (type === 'all') {
            item.style.display = 'flex';
        } else if (type === 'unread') {
            item.style.display = item.classList.contains('unread') ? 'flex' : 'none';
        } else {
            item.style.display = item.dataset.type === type ? 'flex' : 'none';
        }
    });
}

// Toggle filter visibility
function toggleFilter() {
    const filterTabs = document.getElementById('filterTabs');
    filterTabs.style.display = filterTabs.style.display === 'none' ? 'flex' : 'none';
}

// Delete notification
function deleteNotification(notifId) {
    if (confirm('Delete this notification?')) {
        fetch('../api/delete_notification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + notifId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('notif-' + notifId).remove();
                
                // Update count
                const countBadge = document.querySelector('.unread-count');
                if (countBadge) {
                    const currentCount = parseInt(countBadge.textContent);
                    if (currentCount > 1) {
                        countBadge.textContent = (currentCount - 1) + ' new';
                    } else {
                        countBadge.remove();
                    }
                }
            }
        });
    }
}

// Load more notifications
let page = 1;
function loadMoreNotifications() {
    page++;
    fetch('../api/get_notifications.php?page=' + page)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector('.notifications-list').insertAdjacentHTML('beforeend', data.html);
                
                if (!data.has_more) {
                    document.querySelector('.load-more').style.display = 'none';
                }
            }
        });
}

// Real-time polling for new notifications
setInterval(function() {
    fetch('../api/get_notifications_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.count > 0) {
                // Update notification badge in header
                const badge = document.querySelector('.notification-badge');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';
                }
            }
        });
}, 30000); // Check every 30 seconds
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>