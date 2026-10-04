<?php
$page_title = "Login";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        // Check if user exists
        $sql = "SELECT * FROM users WHERE username = '$username' OR email = '$username'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            
            // Check account status
            if ($user['account_status'] == 'suspended') {
                $error = "Your account has been suspended. Please contact support.";
            } elseif ($user['account_status'] == 'deactivated') {
                $error = "This account has been deactivated.";
            } elseif (password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['profile_pic'] = $user['profile_pic'];
                $_SESSION['role'] = $user['role'];
                
                // Update online status
                $updateSql = "UPDATE users SET is_online = 1, last_seen = NOW() WHERE id = " . $user['id'];
                mysqli_query($conn, $updateSql);
                
                // Redirect based on role
                if ($user['role'] == 'admin') {
                    header('Location: ../admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "No account found with that username or email";
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <img src="../<?php echo $settings['logo_url'] ? $settings['logo_url'] : 'assets/uploads/default-logo.png'; ?>" 
                 alt="Logo" class="auth-logo">
            <h1>Welcome Back! 💕</h1>
            <p>Login to find your perfect match</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['registered'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Registration successful! Please login.
        </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form" id="loginForm">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Username or Email
                </label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       class="form-control"
                       placeholder="Enter username or email"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       required>
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="password-field">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control"
                           placeholder="Enter your password"
                           required>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Don't have an account? <a href="signup.php">Sign Up Now</a></p>
            <p class="or-divider"><span>OR</span></p>
            <div class="social-login">
                <button class="btn-social facebook">
                    <i class="fab fa-facebook-f"></i> Facebook
                </button>
                <button class="btn-social google">
                    <i class="fab fa-google"></i> Google
                </button>
            </div>
        </div>
    </div>
    
    <div class="auth-side-content">
        <h2>Find Your Perfect Match</h2>
        <ul class="feature-list">
            <li><i class="fas fa-heart"></i> Connect with like-minded people</li>
            <li><i class="fas fa-comments"></i> Real-time chat & messaging</li>
            <li><i class="fas fa-shield-alt"></i> Safe & secure platform</li>
            <li><i class="fas fa-video"></i> Live streaming features</li>
            <li><i class="fas fa-users"></i> Join a growing community</li>
        </ul>
    </div>
</div>

<style>
.auth-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: calc(100vh - 70px);
    gap: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.auth-card {
    background: white;
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    max-width: 500px;
    width: 100%;
    margin: 0 auto;
}

.auth-side-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 60px;
    color: white;
}

.auth-side-content h2 {
    font-size: 2.5em;
    margin-bottom: 30px;
}

.auth-side-content .feature-list {
    list-style: none;
    padding: 0;
}

.auth-side-content .feature-list li {
    margin: 20px 0;
    font-size: 1.2em;
    display: flex;
    align-items: center;
    gap: 15px;
}

.auth-side-content .feature-list li i {
    font-size: 1.5em;
    opacity: 0.9;
}

.auth-header {
    text-align: center;
    margin-bottom: 30px;
}

.auth-logo {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin-bottom: 20px;
    border: 3px solid #e91e63;
}

.auth-header h1 {
    color: #333;
    margin-bottom: 10px;
}

.auth-header p {
    color: #666;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #555;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #e91e63;
}

.password-field {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    cursor: pointer;
}

.forgot-password {
    color: #e91e63;
    text-decoration: none;
}

.btn-block {
    width: 100%;
    padding: 15px;
    font-size: 16px;
    background: linear-gradient(135deg, #e91e63, #c2185b);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.3s, box-shadow 0.3s;
}

.btn-block:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(233, 30, 99, 0.3);
}

.auth-footer {
    text-align: center;
    margin-top: 30px;
}

.auth-footer a {
    color: #e91e63;
    text-decoration: none;
    font-weight: 500;
}

.or-divider {
    margin: 20px 0;
    position: relative;
}

.or-divider span {
    background: white;
    padding: 0 15px;
    color: #999;
    position: relative;
    z-index: 1;
}

.or-divider:before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e0e0e0;
}

.social-login {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.btn-social {
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
}

.btn-social.facebook {
    color: #1877f2;
}

.btn-social.google {
    color: #db4437;
}

.btn-social:hover {
    background: #f5f5f5;
    transform: translateY(-2px);
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

@media (max-width: 768px) {
    .auth-container {
        grid-template-columns: 1fr;
    }
    
    .auth-side-content {
        display: none;
    }
    
    .auth-card {
        padding: 20px;
    }
}
</style>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Form validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    
    if (!username || !password) {
        e.preventDefault();
        alert('Please fill in all fields');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>