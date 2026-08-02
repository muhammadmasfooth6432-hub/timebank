<?php
// login.php - Modern authentication page

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isLoggedIn()) {
    redirect(APP_URL . '/dashboard.php');
}

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

include __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p>Sign in to continue exchanging services</p>
        
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
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo APP_URL; ?>/actions/login_action.php" class="auth-form">
            <?php include __DIR__ . '/includes/csrf.php'; echo csrfField(); ?>
            
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
                       required 
                       placeholder="Enter your password"
                       autocomplete="current-password">
            </div>
            
            <div class="form-options">
                <label style="display: flex; align-items: center; gap: var(--spacing-xs); cursor: pointer;">
                    <input type="checkbox" name="remember"> 
                    <span>Remember me</span>
                </label>
                <a href="#" style="font-size: var(--text-sm);">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: var(--spacing-md);">
                Sign In
            </button>
        </form>
        
        <div class="auth-footer">
            <span>Don't have an account?</span>
            <a href="<?php echo APP_URL; ?>/register.php">Create free account</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>