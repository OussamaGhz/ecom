</main>
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Company Info -->
                <div class="footer-column">
                    <h4 class="footer-title">ShopEasy</h4>
                    <p>Shop with confidence. Quality products, easy returns, and secure checkout.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-column">
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                    </ul>
                </div>
                
                <!-- Account -->
                <div class="footer-column">
                    <h4 class="footer-title">My Account</h4>
                    <ul class="footer-links">
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                        <li><a href="order_history.php">Order History</a></li>
                        <li><a href="cart.php">Cart</a></li>
                    </ul>
                </div>
                
                <!-- Newsletter -->
                <div class="footer-column">
                    <h4 class="footer-title">Newsletter</h4>
                    <p>Subscribe to receive updates, access to exclusive deals, and more.</p>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Enter your email" required>
                        <button type="submit" class="btn btn-primary btn-sm">Subscribe</button>
                    </form>
                </div>
            </div>
            
            <!-- Bottom Footer -->
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> ShopEasy. All rights reserved.</p>
                <div class="footer-legal">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Shipping Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Theme toggle button (for light/dark mode) -->
    <button id="theme-toggle" class="theme-toggle" aria-label="Toggle Dark Mode">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Main JavaScript -->
    <script src="assets/js/main.js"></script>
    
    <!-- Page specific scripts (will be included in individual pages) -->
    <?php if (isset($page_specific_js)): ?>
        <script src="<?php echo $page_specific_js; ?>"></script>
    <?php endif; ?>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('.mobile-nav').classList.toggle('active');
            this.querySelector('i').classList.toggle('fa-bars');
            this.querySelector('i').classList.toggle('fa-times');
        });
        
        // Theme toggle functionality
        document.getElementById('theme-toggle').addEventListener('click', function() {
            document.body.classList.toggle('dark-theme');
            const icon = this.querySelector('i');
            if (document.body.classList.contains('dark-theme')) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                localStorage.setItem('theme', 'dark');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
                localStorage.setItem('theme', 'light');
            }
        });
        
        // Apply saved theme preference
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-theme');
            document.querySelector('#theme-toggle i').classList.remove('fa-moon');
            document.querySelector('#theme-toggle i').classList.add('fa-sun');
        }
    </script>
</body>
</html>