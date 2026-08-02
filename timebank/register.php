<?php
// register.php - Modern registration page

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard.php');
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

include __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Join Time Bank</h2>
        <p>Start exchanging skills with your community today</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo APP_URL; ?>/actions/register_action.php" class="auth-form">
            <?php include __DIR__ . '/includes/csrf.php'; echo csrfField(); ?>
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" 
                       required 
                       value="<?php echo sanitizeInput($_POST['name'] ?? ''); ?>"
                       placeholder="John Doe"
                       autocomplete="name">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" 
                       required 
                       value="<?php echo sanitizeInput($_POST['email'] ?? ''); ?>"
                       placeholder="you@example.com"
                       autocomplete="email">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" 
                       required minlength="8"
                       placeholder="Minimum 8 characters"
                       autocomplete="new-password">
                <small class="text-muted" style="display: block; margin-top: var(--spacing-xs);">
                    Use 8+ characters with a mix of letters, numbers & symbols
                </small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       class="form-control" required
                       placeholder="Re-enter your password"
                       autocomplete="new-password">
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: flex-start; gap: var(--spacing-xs); cursor: pointer;">
                    <input type="checkbox" name="terms" required style="margin-top: 3px;">
                    <span style="font-size: var(--text-sm); color: var(--color-text-secondary);">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </span>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: var(--spacing-md);">
                Create Account - Get 3 Free Credits
            </button>
        </form>
        
        <div class="auth-footer">
            <span>Already have an account?</span>
            <a href="<?php echo APP_URL; ?>/login.php">Sign in instead</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>