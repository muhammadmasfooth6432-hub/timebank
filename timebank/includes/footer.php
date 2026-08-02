<?php
// includes/footer.php
// Modern footer with enhanced styling
?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand -->
                <div class="footer-col footer-brand-col">
                    <div class="flex items-center gap-sm" style="margin-bottom: var(--spacing-md);">
                        <span class="nav-brand-logo" style="width: 32px; height: 32px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </span>
                        <strong style="color: var(--color-text-primary); font-size: var(--text-lg);">Time Bank</strong>
                    </div>
                    <p class="footer-desc">Exchange skills, build community. Your time is the currency.</p>
                    <div class="footer-socials">
                        <a href="#" aria-label="Twitter"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53A4.48 4.48 0 0 0 12 8v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg></a>
                        <a href="#" aria-label="GitHub"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg></a>
                        <a href="#" aria-label="Discord"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-col">
                    <h4>Explore</h4>
                    <ul>
                        <li><a href="<?php echo APP_URL; ?>/index.php">Browse Services</a></li>
                        <li><a href="<?php echo APP_URL; ?>/pages/services/directory.php">My Services</a></li>
                        <li><a href="<?php echo APP_URL; ?>/pages/requests/history.php">Request History</a></li>
                        <li><a href="<?php echo APP_URL; ?>/pages/credits/history.php">Credit Ledger</a></li>
                    </ul>
                </div>

                <!-- For Members -->
                <div class="footer-col">
                    <h4>For Members</h4>
                    <ul>
                        <li><a href="<?php echo APP_URL; ?>/pages/profile.php">My Profile</a></li>
                        <li><a href="<?php echo APP_URL; ?>/pages/edit_profile.php">Edit Profile</a></li>
                        <li><a href="<?php echo APP_URL; ?>/index.php#how-it-works">How It Works</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div class="footer-col">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <button class="back-to-top" id="backToTop" aria-label="Back to top">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="18 15 12 9 6 15"></polyline>
        </svg>
    </button>
    
    <script>
        // Mobile navigation toggle
        document.getElementById('navToggle')?.addEventListener('click', function() {
            const menu = document.getElementById('navMenu');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            this.classList.toggle('active');
            menu?.classList.toggle('active');
        });
        
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('siteHeader');
            if (header) {
                header.classList.toggle('scrolled', window.scrollY > 50);
            }
        });
    </script>
</body>
</html>