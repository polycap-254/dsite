<?php
$page_title = "Manage Users";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$where = [];
if ($search) {
    $where[] = "(username LIKE '%$search%' OR full_name LIKE '%$search%' OR email LIKE '%$search%')";
}
if ($status) {
    $where[] = "account_status = '$status'";
}
$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total users
$total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM users $where_clause");
$total_users = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_users / $limit);

// Get users
$users_sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$users_result = mysqli_query($conn, $users_sql);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-container">
    <!-- Include sidebar -->
    <div class="admin-sidebar">
        <!-- Same sidebar as dashboard -->
        <div class="admin-profile">
            <img src="../<?php echo $_SESSION['profile_pic']; ?>" alt="Admin" class="admin-avatar">
            <h3><?php echo $_SESSION['full_name']; ?></h3>
            <span class="admin-badge">Administrator</span>
        </div>
        <nav class="admin-nav">
            <a href="dashboard.php" class="admin-nav-item">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="users.php" class="admin-nav-item active">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="posts.php" class="admin-nav-item">
                <i class="fas fa-newspaper"></i> Posts
            </a>
            <a href="settings.php" class="admin-nav-item">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="../pages/index.php" class="admin-nav-item">
                <i class="fas fa-external-link-alt"></i> View Site
            </a>
        </nav>
    </div>
    
    <div class="admin-main">
        <div class="admin-header">
            <h1><i class="fas fa-users"></i> Manage Users</h1>
            <div class="admin-header-actions">
                <button class="btn btn-primary" onclick="exportUsers()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
        
        <!-- Search and Filter -->
        <div class="filter-bar">
            <form method="GET" class="filter-form">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search users..." 
                           value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                </div>
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="suspended" <?php echo $status == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                    <option value="deactivated" <?php echo $status == 'deactivated' ? 'selected' : ''; ?>>Deactivated</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
        
        <!-- Users Table -->
        <div class="admin-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Posts</th>
                                <th>Followers</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = mysqli_fetch_assoc($users_result)): 
                                $post_count = mysqli_fetch_assoc(mysqli_query($conn, 
                                    "SELECT COUNT(*) as count FROM posts WHERE user_id = {$user['id']}"))['count'];
                                $follower_count = getFollowersCount($user['id']);
                            ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
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
                                <td>
                                    <span class="role-badge <?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $user['account_status']; ?>">
                                        <?php echo ucfirst($user['account_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $post_count; ?></td>
                                <td><?php echo $follower_count; ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="../pages/profile.php?user=<?php echo $user['username']; ?>" 
                                           class="btn-icon" title="View Profile" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn-icon" title="Edit" onclick="editUser(<?php echo $user['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($user['account_status'] == 'active'): ?>
                                        <button class="btn-icon warning" title="Suspend" 
                                                onclick="suspendUser(<?php echo $user['id']; ?>)">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        <?php elseif ($user['account_status'] == 'suspended'): ?>
                                        <button class="btn-icon success" title="Activate" 
                                                onclick="activateUser(<?php echo $user['id']; ?>)">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button class="btn-icon danger" title="Delete" 
                                                onclick="deleteUser(<?php echo $user['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" 
                       class="page-link <?php echo $page == $i ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.filter-bar {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.filter-form {
    display: flex;
    gap: 15px;
    align-items: center;
}

.search-box {
    position: relative;
    flex: 1;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
}

.search-box input {
    padding-left: 40px;
}

.role-badge {
    padding: 3px 12px;
    border-radius: 15px;
    font-size: 0.85em;
    font-weight: 500;
}

.role-badge.admin { background: #fee2e8; color: #e91e63; }
.role-badge.moderator { background: #fff3e0; color: #ff9800; }
.role-badge.user { background: #e3f2fd; color: #2196f3; }

.btn-icon.warning:hover { background: #fff3e0; color: #ff9800; }
.btn-icon.success:hover { background: #d4edda; color: #4caf50; }

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.page-link {
    padding: 8px 15px;
    background: #f0f0f0;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
}

.page-link.active {
    background: #e91e63;
    color: white;
}

.page-link:hover {
    background: #e91e63;
    color: white;
}
</style>

<script>
function activateUser(userId) {
    if (confirm('Activate this user?')) {
        fetch('../api/update_user_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'user_id=' + userId + '&status=active'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
        });
    }
}

function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone!')) {
        fetch('../api/delete_user.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'user_id=' + userId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
        });
    }
}

function exportUsers() {
    window.location.href = '../api/export_users.php';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>