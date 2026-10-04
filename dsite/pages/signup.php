<?php
$page_title = "Sign Up";
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
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize($_POST['full_name']);
    $gender = sanitize($_POST['gender']);
    $birth_date = sanitize($_POST['birth_date']);
    $interested_in = sanitize($_POST['interested_in']);
    
    // Validation
    $errors = [];
    
    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    // Check age (must be 18+)
    $birth_timestamp = strtotime($birth_date);
    $age = (time() - $birth_timestamp) / (365.25 * 24 * 60 * 60);
    if ($age < 18) {
        $errors[] = "You must be at least 18 years old";
    }
    
    // Check if username exists
    $checkSql = "SELECT id FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $checkSql);
    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Username already taken";
    }
    
    // Check if email exists
    $checkSql = "SELECT id FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkSql);
    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Email already registered";
    }
    
    if (empty($errors)) {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        
        // Insert user
        $sql = "INSERT INTO users (username, email, password_hash, full_name, gender, birth_date, interested_in, verification_token) 
                VALUES ('$username', '$email', '$password_hash', '$full_name', '$gender', '$birth_date', '$interested_in', '$verification_token')";
        
        if (mysqli_query($conn, $sql)) {
            $user_id = mysqli_insert_id($conn);
            
            // Create default user settings
            $settingsSql = "INSERT INTO user_settings (user_id) VALUES ($user_id)";
            mysqli_query($conn, $settingsSql);
            
            $_SESSION['registration_success'] = true;
            header('Location: login.php?registered=1');
            exit();
        } else {
            $errors[] = "Registration failed: " . mysqli_error($conn);
        }
    }
    
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-container">
    <div class="auth-side-content">
        <h2>Start Your Love Journey</h2>
        <ul class="feature-list">
            <li><i class="fas fa-heart"></i> Create your perfect profile</li>
            <li><i class="fas fa-search"></i> Find compatible matches</li>
            <li><i class="fas fa-comments"></i> Chat with interesting people</li>
            <li><i class="fas fa-shield-alt"></i> Safe & secure dating</li>
            <li><i class="fas fa-star"></i> It's FREE to join!</li>
        </ul>
    </div>
    
    <div class="auth-card">
        <div class="auth-header">
            <img src="../<?php echo $settings['logo_url'] ? $settings['logo_url'] : 'assets/uploads/default-logo.png'; ?>" 
                 alt="Logo" class="auth-logo">
            <h1>Join <?php echo $settings['site_name']; ?> 💝</h1>
            <p>Create your account and find your perfect match</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form" id="signupForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="full_name">
                        <i class="fas fa-user"></i> Full Name
                    </label>
                    <input type="text" 
                           id="full_name" 
                           name="full_name" 
                           class="form-control"
                           placeholder="Enter your full name"
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-at"></i> Username
                    </label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control"
                           placeholder="Choose a username"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-control"
                       placeholder="Enter your email"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-field">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control"
                               placeholder="Create password (min. 6 characters)"
                               required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i> Confirm Password
                    </label>
                    <div class="password-field">
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-control"
                               placeholder="Confirm your password"
                               required>
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="gender">
                        <i class="fas fa-venus-mars"></i> I am
                    </label>
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="">Select gender</option>
                        <option value="male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                        <option value="non-binary" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'non-binary') ? 'selected' : ''; ?>>Non-Binary</option>
                        <option value="other" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="interested_in">
                        <i class="fas fa-heart"></i> Interested in
                    </label>
                    <select id="interested_in" name="interested_in" class="form-control" required>
                        <option value="">Select preference</option>
                        <option value="male" <?php echo (isset($_POST['interested_in']) && $_POST['interested_in'] == 'male') ? 'selected' : ''; ?>>Men</option>
                        <option value="female" <?php echo (isset($_POST['interested_in']) && $_POST['interested_in'] == 'female') ? 'selected' : ''; ?>>Women</option>
                        <option value="both" <?php echo (isset($_POST['interested_in']) && $_POST['interested_in'] == 'both') ? 'selected' : ''; ?>>Both</option>
                        <option value="all" <?php echo (isset($_POST['interested_in']) && $_POST['interested_in'] == 'all') ? 'selected' : ''; ?>>Everyone</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="birth_date">
                    <i class="fas fa-birthday-cake"></i> Date of Birth
                </label>
                <input type="date" 
                       id="birth_date" 
                       name="birth_date" 
                       class="form-control"
                       value="<?php echo isset($_POST['birth_date']) ? $_POST['birth_date'] : ''; ?>"
                       required>
            </div>
            
            <div class="terms-checkbox">
                <label>
                    <input type="checkbox" required>
                    I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Login Here</a></p>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const toggleIcon = passwordInput.nextElementSibling.querySelector('i');
    
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

// Real-time username availability check
document.getElementById('username').addEventListener('blur', function() {
    const username = this.value;
    if (username.length >= 3) {
        fetch('../api/check_username.php?username=' + username)
            .then(response => response.json())
            .then(data => {
                const usernameInput = document.getElementById('username');
                if (data.exists) {
                    usernameInput.style.borderColor = '#f44336';
                    // Show error
                    let errorSpan = document.getElementById('username-error');
                    if (!errorSpan) {
                        errorSpan = document.createElement('span');
                        errorSpan.id = 'username-error';
                        errorSpan.style.color = '#f44336';
                        errorSpan.style.fontSize = '12px';
                        usernameInput.parentElement.appendChild(errorSpan);
                    }
                    errorSpan.textContent = 'Username already taken';
                } else {
                    usernameInput.style.borderColor = '#4caf50';
                    const errorSpan = document.getElementById('username-error');
                    if (errorSpan) errorSpan.remove();
                }
            });
    }
});

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strength = checkPasswordStrength(password);
    
    let strengthBar = document.getElementById('password-strength');
    if (!strengthBar) {
        strengthBar = document.createElement('div');
        strengthBar.id = 'password-strength';
        strengthBar.style.height = '4px';
        strengthBar.style.marginTop = '5px';
        strengthBar.style.borderRadius = '2px';
        strengthBar.style.transition = 'all 0.3s';
        this.parentElement.appendChild(strengthBar);
    }
    
    const colors = ['#f44336', '#ff9800', '#ffeb3b', '#4caf50'];
    const widths = ['25%', '50%', '75%', '100%'];
    
    strengthBar.style.width = widths[strength];
    strengthBar.style.backgroundColor = colors[strength];
});

function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) strength++;
    return Math.min(strength, 3);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>