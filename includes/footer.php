<footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Visit Us</h3>
                    <div class="footer-info">
                        <p><i class="fas fa-map-marker-alt"></i> 123 Museum Street<br>City, State 12345</p>
                        <p><i class="fas fa-phone"></i> (555) 123-4567</p>
                        <p><i class="fas fa-envelope"></i> info@nmac.org</p>
                    </div>
                    <div class="footer-hours">
                        <h4>Hours</h4>
                        <p>Tuesday - Sunday: 10:00 AM - 5:00 PM<br>
                        Thursday: 10:00 AM - 8:00 PM<br>
                        Closed Mondays</p>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="exhibitions.php">Current Exhibitions</a></li>
                        <li><a href="events.php">Upcoming Events</a></li>
                        <li><a href="collections.php">Collections</a></li>
                        <li><a href="virtual-tours.php">Virtual Tours</a></li>
                        <li><a href="membership.php">Membership</a></li>
                        <li><a href="visit.php">Plan Your Visit</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>About</h3>
                    <ul class="footer-links">
                        <li><a href="about.php">Our Story</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="careers.php">Careers</a></li>
                        <li><a href="press.php">Press</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                        <li><a href="terms.php">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Stay Connected</h3>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                    
                    <div class="newsletter-signup">
                        <h4>Newsletter</h4>
                        <form id="newsletter-form" class="newsletter-form">
                            <input type="email" name="email" placeholder="Your email address" required>
                            <button type="submit">Subscribe</button>
                        </form>
                        <div id="newsletter-message" class="newsletter-message"></div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; 2024 National Museum of Art & Culture. All rights reserved.</p>
                    <div class="footer-bottom-links">
                        <a href="accessibility.php">Accessibility</a>
                        <a href="privacy.php">Privacy</a>
                        <a href="terms.php">Terms</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
    <?php if (isset($page_scripts) && is_array($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
