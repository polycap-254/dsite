    </main>
    
    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-container">
            <!-- Footer Top Section -->
            <div class="footer-grid">
                <!-- About Section -->
                <div class="footer-section">
                    <h3>About <?php echo $settings['site_name']; ?></h3>
                    <p><?php echo $settings['about_us'] ? substr($settings['about_us'], 0, 150) . '...' : 'Connecting hearts worldwide.'; ?></p>
                    <div class="social-links">
                        <?php if ($settings['facebook_url']): ?>
                        <a href="<?php echo $settings['facebook_url']; ?>" target="_blank" class="social-icon">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($settings['twitter_url']): ?>
                        <a href="<?php echo $settings['twitter_url']; ?>" target="_blank" class="social-icon">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php if ($settings['instagram_url']): ?>
                        <a href="<?php echo $settings['instagram_url']; ?>" target="_blank" class="social-icon">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="pages/about.php">About Us</a></li>
                        <li><a href="pages/privacy.php">Privacy Policy</a></li>
                        <li><a href="pages/terms.php">Terms of Service</a></li>
                        <?php if (!isLoggedIn()): ?>
                        <li><a href="signup.php">Join Now</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul class="contact-info">
                        <li>
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:<?php echo $settings['contact_email']; ?>">
                                <?php echo $settings['contact_email']; ?>
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span><?php echo $settings['help_line']; ?></span>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Available Worldwide</span>
                        </li>
                    </ul>
                </div>
                
                <!-- Help & Support -->
                <div class="footer-section">
                    <h3>Help & Support</h3>
                    <ul>
                        <li><a href="pages/faq.php">FAQ</a></li>
                        <li><a href="pages/safety.php">Safety Tips</a></li>
                        <li><a href="pages/guidelines.php">Community Guidelines</a></li>
                        <li><a href="pages/report.php">Report Issue</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p><?php echo $settings['footer_text'] ? $settings['footer_text'] : '© ' . date('Y') . ' ' . $settings['site_name'] . '. All rights reserved.'; ?></p>
                <?php if (isAdmin()): ?>
                <a href="../admin/settings.php" class="admin-edit-link">
                    <i class="fas fa-edit"></i> Edit Footer Content
                </a>
                <?php endif; ?>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
</body>
</html>