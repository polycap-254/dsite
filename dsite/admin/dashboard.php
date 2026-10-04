<?php
$page_title = "Admin Dashboard";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Get statistics
$stats = [];

// Total users
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$stats['total_users'] = mysqli_fetch_assoc($result)['count'];

// Active users today
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE DATE(last_seen) = CURDATE()");
$stats['active_today'] = mysqli_fetch_assoc($result)['count'];

// Online users
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE is_online = 1");
$stats['online_now'] = mysqli_fetch_assoc($result)['count'];

// Total posts
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM posts");
$stats['total_posts'] = mysqli_fetch_assoc($result)['count'];

// Posts today
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM posts WHERE DATE(created_at) = CURDATE()");
$stats['posts_today'] = mysqli_fetch_assoc($result)['count'];

// Total comments
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM comments");
$stats['total_comments'] = mysqli_fetch_assoc($result)['count'];

// Total likes
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM likes");
$stats['total_likes'] = mysqli_fetch_assoc($result)['count'];

// Total messages
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM messages");
$stats['total_messages'] = mysqli_fetch_assoc($result)['count'];

// Live streams
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM live_streams WHERE is_active = 1");
$stats['active_streams'] = mysqli_fetch_assoc($result)['count'];

// New users today
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
$stats['new_today'] = mysqli_fetch_assoc($result)['count'];

// Recent users
$recent_users_sql = "SELECT id, username, full_name, email, profile_pic, created_at, account_status 
                    FROM users 
                    ORDER BY created_at DESC 
                    LIMIT 10";
$recent_users = mysqli_query($conn, $recent_users_sql);

// Recent posts
$recent_posts_sql = "SELECT p.*, u.username, u.full_name 
                    FROM posts p 
                    JOIN users u ON p.user_id = u.id 
                    ORDER BY p.created_at DESC 
                    LIMIT 10";
$recent_posts = mysqli_query($conn, $recent_posts_sql);

// User growth data (last 7 days)
$growth_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = '$date'");
    $growth_data[] = [
        'date' => date('M d', strtotime($date)),
        'count' => mysqli_fetch_assoc($result)['count']
    ];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-container">
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-profile">
            <img src="../<?php echo $_SESSION['profile_pic']; ?>" alt="Admin" class="admin-avatar">
            <h3><?php echo $_SESSION['full_name']; ?></h3>
            <span class="admin-badge">Administrator</span>
        </div>
        
        <nav class="admin-nav">
            <a href="dashboard.php" class="admin-nav-item active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="users.php" class="admin-nav-item">
                <i class="fas fa-users"></i> Users
                <span class="badge"><?php echo $stats['total_users']; ?></span>
            </a>
            <a href="posts.php" class="admin-nav-item">
                <i class="fas fa-newspaper"></i> Posts
                <span class="badge"><?php echo $stats['total_posts']; ?></span>
            </a>
            <a href="reports.php" class="admin-nav-item">
                <i class="fas fa-flag"></i> Reports
            </a>
            <a href="settings.php" class="admin-nav-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="moderation.php" class="admin-nav-item">
                <i class="fas fa-shield-alt"></i> Moderation
            </a>
            <a href="analytics.php" class="admin-nav-item">
                <i class="fas fa-chart-bar"></i> Analytics
            </a>
            <a href="../pages/index.php" class="admin-nav-item">
                <i class="fas fa-external-link-alt"></i> View Site
            </a>
        </nav>
    </div>
    
    <!-- Admin Main Content -->
    <div class="admin-main">
        <!-- Admin Header -->
        <div class="admin-header">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
            <div class="admin-header-actions">
                <button class="btn btn-outline btn-sm" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <span class="current-time"><?php echo date('F j, Y - h:i A'); ?></span>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo number_format($stats['total_users']); ?></span>
                    <span class="stat-label">Total Users</span>
                    <span class="stat-change positive">
                        <i class="fas fa-arrow-up"></i> +<?php echo $stats['new_today']; ?> today
                    </span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon online">
                    <i class="fas fa-circle"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo number_format($stats['online_now']); ?></span>
                    <span class="stat-label">Online Now</span>
                    <span class="stat-change">
                        <?php echo number_format($stats['active_today']); ?> active today
                    </span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon posts">
                    <i class="fas fa-newspaper"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo number_format($stats['total_posts']); ?></span>
                    <span class="stat-label">Total Posts</span>
                    <span class="stat-change positive">
                        +<?php echo $stats['posts_today']; ?> today
                    </span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon engagement">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo number_format($stats['total_likes']); ?></span>
                    <span class="stat-label">Total Likes</span>
                    <span class="stat-change">
                        <?php echo number_format($stats['total_comments']); ?> comments
                    </span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon messages">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo number_format($stats['total_messages']); ?></span>
                    <span class="stat-label">Total Messages</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon streams">
                    <i class="fas fa-broadcast-tower"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-value"><?php echo $stats['active_streams']; ?></span>
                    <span class="stat-label">Live Streams</span>
                </div>
            </div>
        </div>
        
        <div class="admin-grid">
            <!-- User Growth Chart -->
            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> User Growth (Last 7 Days)</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                </div>
                <div class="card-body">
                    <div class="quick-actions-grid">
                        <a href="users.php?action=add" class="quick-action">
                            <i class="fas fa-user-plus"></i>
                            <span>Add User</span>
                        </a>
                        <a href="settings.php" class="quick-action">
                            <i class="fas fa-cog"></i>
                            <span>Site Settings</span>
                        </a>
                        <a href="moderation.php" class="quick-action">
                            <i class="fas fa-shield-alt"></i>
                            <span>Moderation</span>
                        </a>
                        <a href="announcement.php" class="quick-action">
                            <i class="fas fa-bullhorn"></i>
                            <span>Announcement</span>
                        </a>
                        <a href="backup.php" class="quick-action">
                            <i class="fas fa-database"></i>
                            <span>Backup</span>
                        </a>
                        <a href="reports.php" class="quick-action">
                            <i class="fas fa-flag"></i>
                            <span>Reports</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Recent Users -->
            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="fas fa-user-clock"></i> Recent Users</h3>
                    <a href="users.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = mysqli_fetch_assoc($recent_users)): ?>
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <img src="../<?php echo $user['profile_pic']; ?>" alt="Avatar" class="table-avatar">
                                            <div>
                                                <strong><?php echo $user['full_name'] ?: $user['username']; ?></strong>
                                                <small>@<?php echo $user['username']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo timeAgo($user['created_at']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $user['account_status']; ?>">
                                            <?php echo ucfirst($user['account_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="../pages/profile.php?user=<?php echo $user['username']; ?>" 
                                               class="btn-icon" title="View Profile">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn-icon" title="Edit User" 
                                                    onclick="editUser(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if ($user['account_status'] != 'suspended'): ?>
                                            <button class="btn-icon danger" title="Suspend User" 
                                                    onclick="suspendUser(<?php echo $user['id']; ?>)">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Recent Posts -->
            <div class="admin-card">
                <div class="card-header">
                    <h3><i class="fas fa-clock"></i> Recent Posts</h3>
                    <a href="posts.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Author</th>
                                    <th>Content</th>
                                    <th>Type</th>
                                    <th>Likes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($post = mysqli_fetch_assoc($recent_posts)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $post['full_name'] ?: $post['username']; ?></strong>
                                    </td>
                                    <td>
                                        <?php echo substr(htmlspecialchars($post['content']), 0, 80); ?>...
                                    </td>
                                    <td>
                                        <span class="type-badge <?php echo $post['media_type']; ?>">
                                            <?php echo ucfirst($post['media_type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-heart" style="color:#e91e63;"></i> 
                                        <?php echo $post['likes_count']; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="../pages/posts.php?post=<?php echo $post['id']; ?>" 
                                               class="btn-icon" title="View Post">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn-icon danger" title="Delete Post" 
                                                    onclick="deletePost(<?php echo $post['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit User</h2>
            <button class="close-btn" onclick="closeModal('editUserModal')">&times;</button>
        </div>
        <div id="editUserForm"></div>
    </div>
</div>

<style>
/* Admin Layout */
.admin-container {
    display: flex;
    min-height: calc(100vh - 70px);
    margin: -20px -20px 0 -20px;
}

/* Admin Sidebar */
.admin-sidebar {
    width: 280px;
    background: #1a1a2e;
    color: white;
    padding: 20px 0;
    position: fixed;
    top: 70px;
    bottom: 0;
    left: 0;
    overflow-y: auto;
    z-index: 100;
}

.admin-profile {
    text-align: center;
    padding: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    margin-bottom: 20px;
}

.admin-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid #e91e63;
    margin-bottom: 10px;
}

.admin-badge {
    display: inline-block;
    background: #e91e63;
    color: white;
    padding: 3px 12px;
    border-radius: 15px;
    font-size: 0.8em;
    margin-top: 10px;
}

.admin-nav-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 25px;
    color: #ccc;
    text-decoration: none;
    transition: all 0.3s;
    justify-content: space-between;
}

.admin-nav-item:hover,
.admin-nav-item.active {
    background: rgba(255,255,255,0.1);
    color: white;
    border-left: 4px solid #e91e63;
}

.admin-nav-item i {
    width: 20px;
}

/* Admin Main Content */
.admin-main {
    flex: 1;
    margin-left: 280px;
    padding: 20px;
    background: #f5f5f5;
    min-height: 100vh;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.current-time {
    color: #666;
    font-size: 0.9em;
    margin-left: 15px;
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5em;
}

.stat-icon.users { background: #e3f2fd; color: #2196f3; }
.stat-icon.online { background: #e8f5e9; color: #4caf50; }
.stat-icon.posts { background: #fff3e0; color: #ff9800; }
.stat-icon.engagement { background: #fee2e8; color: #e91e63; }
.stat-icon.messages { background: #f3e5f5; color: #9c27b0; }
.stat-icon.streams { background: #e0f2f1; color: #009688; }

.stat-value {
    display: block;
    font-size: 1.8em;
    font-weight: bold;
    color: #333;
}

.stat-label {
    color: #666;
    font-size: 0.9em;
}

.stat-change {
    display: block;
    font-size: 0.85em;
    margin-top: 5px;
    color: #666;
}

.stat-change.positive {
    color: #4caf50;
}

/* Admin Grid */
.admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
}

.admin-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.card-body {
    padding: 20px;
}

/* Quick Actions */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.quick-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 10px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
}

.quick-action:hover {
    background: #e91e63;
    color: white;
    transform: translateY(-3px);
}

.quick-action i {
    font-size: 2em;
}

/* Table Styles */
.table-responsive {
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th,
.admin-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.admin-table th {
    background: #f9f9f9;
    font-weight: 600;
    color: #333;
}

.admin-table tbody tr:hover {
    background: #f9f9f9;
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.table-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
}

.status-badge {
    padding: 3px 12px;
    border-radius: 15px;
    font-size: 0.85em;
    font-weight: 500;
}

.status-badge.active { background: #d4edda; color: #155724; }
.status-badge.suspended { background: #f8d7da; color: #721c24; }
.status-badge.deactivated { background: #e2e3e5; color: #383d41; }

.type-badge {
    padding: 3px 12px;
    border-radius: 15px;
    font-size: 0.85em;
}

.type-badge.text { background: #e3f2fd; color: #2196f3; }
.type-badge.image { background: #fff3e0; color: #ff9800; }
.type-badge.video { background: #fee2e8; color: #e91e63; }

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn-icon {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-icon:hover {
    background: #f0f0f0;
    color: #333;
}

.btn-icon.danger:hover {
    background: #fee2e8;
    color: #e91e63;
}

/* Chart Container */
.chart-container {
    height: 300px;
    position: relative;
}

@media (max-width: 1024px) {
    .admin-sidebar {
        width: 0;
        padding: 0;
        overflow: hidden;
    }
    
    .admin-main {
        margin-left: 0;
    }
    
    .admin-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// User Growth Chart
const ctx = document.getElementById('growthChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: [<?php echo implode(',', array_map(function($d) { return "'" . $d['date'] . "'"; }, $growth_data)); ?>],
        datasets: [{
            label: 'New Users',
            data: [<?php echo implode(',', array_map(function($d) { return $d['count']; }, $growth_data)); ?>],
            borderColor: '#e91e63',
            backgroundColor: 'rgba(233, 30, 99, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#e91e63',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Edit User
function editUser(userId) {
    fetch('../api/get_user_data.php?id=' + userId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editUserForm').innerHTML = data.html;
                document.getElementById('editUserModal').style.display = 'flex';
            }
        });
}

// Suspend User
function suspendUser(userId) {
    if (confirm('Are you sure you want to suspend this user?')) {
        fetch('../api/suspend_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'user_id=' + userId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

// Delete Post
function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
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
                location.reload();
            }
        });
    }
}

// Close modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

// Auto-refresh stats every 60 seconds
setInterval(function() {
    fetch('../api/get_admin_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stats
                document.querySelectorAll('.stat-value').forEach((el, index) => {
                    // Update with new data
                });
            }
        });
}, 60000);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>