<?php
$page_title = "Admin Settings";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();
require_once __DIR__ . '/../includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    $site_name = sanitize($_POST['site_name']);
    $contact_email = sanitize($_POST['contact_email']);
    $help_line = sanitize($_POST['help_line']);
    $footer_text = $_POST['footer_text'];
    $about_us = $_POST['about_us'];
    $facebook_url = sanitize($_POST['facebook_url']);
    $twitter_url = sanitize($_POST['twitter_url']);
    $instagram_url = sanitize($_POST['instagram_url']);
    $privacy_policy = $_POST['privacy_policy'];
    $terms_of_service = $_POST['terms_of_service'];
    
    // Handle logo upload
    $logo_url = '';
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == 0) {
        $upload_result = uploadFile($_FILES['site_logo']);
        if ($upload_result['success']) {
            $logo_url = $upload_result['file_path'];
        }
    }
    
    $sql = "UPDATE admin_settings SET 
            site_name = '$site_name',
            contact_email = '$contact_email',
            help_line = '$help_line',
            footer_text = '$footer_text',
            about_us = '$about_us',
            facebook_url = '$facebook_url',
            twitter_url = '$twitter_url',
            instagram_url = '$instagram_url',
            privacy_policy = '$privacy_policy',
            terms_of_service = '$terms_of_service'";
    
    if ($logo_url) {
        $sql .= ", logo_url = '$logo_url'";
    }
    
    if (mysqli_query($conn, $sql)) {
        $success_message = "Settings updated successfully!";
    } else {
        $error_message = "Error updating settings: " . mysqli_error($conn);
    }
}

$settings = getSiteSettings();
?>

<div class="container mt-20">
    <div class="admin-settings">
        <h1><i class="fas fa-cog"></i> Admin Settings</h1>
        
        <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="settings-form">
            <div class="settings-section">
                <h3><i class="fas fa-globe"></i> General Settings</h3>
                
                <div class="form-group">
                    <label>Site Name</label>
                    <input type="text" name="site_name" value="<?php echo $settings['site_name']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Site Logo</label>
                    <?php if ($settings['logo_url']): ?>
                    <div class="current-logo">
                        <img src="../<?php echo $settings['logo_url']; ?>" alt="Current Logo" width="100">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="site_logo" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label>Contact Email</label>
                    <input type="email" name="contact_email" value="<?php echo $settings['contact_email']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Help Line</label>
                    <input type="text" name="help_line" value="<?php echo $settings['help_line']; ?>" required>
                </div>
            </div>
            
            <div class="settings-section">
                <h3><i class="fas fa-share-alt"></i> Social Media Links</h3>
                
                <div class="form-group">
                    <label><i class="fab fa-facebook"></i> Facebook URL</label>
                    <input type="url" name="facebook_url" value="<?php echo $settings['facebook_url']; ?>">
                </div>
                
                <div class="form-group">
                    <label><i class="fab fa-twitter"></i> Twitter URL</label>
                    <input type="url" name="twitter_url" value="<?php echo $settings['twitter_url']; ?>">
                </div>
                
                <div class="form-group">
                    <label><i class="fab fa-instagram"></i> Instagram URL</label>
                    <input type="url" name="instagram_url" value="<?php echo $settings['instagram_url']; ?>">
                </div>
            </div>
            
            <div class="settings-section">
                <h3><i class="fas fa-file-alt"></i> Content Pages</h3>
                
                <div class="form-group">
                    <label>About Us</label>
                    <textarea name="about_us" rows="5"><?php echo $settings['about_us']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Footer Text</label>
                    <textarea name="footer_text" rows="3"><?php echo $settings['footer_text']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Privacy Policy</label>
                    <textarea name="privacy_policy" rows="10"><?php echo $settings['privacy_policy']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Terms of Service</label>
                    <textarea name="terms_of_service" rows="10"><?php echo $settings['terms_of_service']; ?></textarea>
                </div>
            </div>
            
            <button type="submit" name="update_settings" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Save All Settings
            </button>
        </form>
    </div>
</div>

<style>
.admin-settings {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.settings-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.settings-section h3 {
    color: var(--primary-color);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--primary-color);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #555;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="url"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    resize: vertical;
}

.current-logo {
    margin-bottom: 10px;
}

.current-logo img {
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn-lg {
    padding: 15px 30px;
    font-size: 16px;
}

.alert {
    padding: 15px;
    border-radius: 5px;
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>