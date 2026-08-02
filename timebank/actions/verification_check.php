<?php
// actions/verification_check.php
// Validate entered verification codes and mark accounts as verified

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/notification_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login
if (!isLoggedIn()) {
    redirect(APP_URL . '/login.php');
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . '/pages/verification.php');
}

// Verify CSRF
checkCsrf();

$user_id = getCurrentUserId();
$pdo = getDatabaseConnection();
$type = $_POST['type'] ?? '';
$code = trim($_POST['code'] ?? '');

if (empty($code)) {
    $_SESSION['error'] = "Please enter the verification code.";
    redirect(APP_URL . '/pages/verification.php');
}

if ($type === 'email') {
    // Fetch stored email verification details
    $stmt = $pdo->prepare("SELECT email, email_verification_code FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user || empty($user['email_verification_code'])) {
        $_SESSION['error'] = "No pending email verification code found. Please request a new one.";
        redirect(APP_URL . '/pages/verification.php');
    }
    
    if ($user['email_verification_code'] === $code) {
        // Code is correct, update status
        $stmt = $pdo->prepare("UPDATE users SET email_verified = 1, email_verification_code = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Notify user
        createNotification($pdo, $user_id, "Email Verification Successful", "Your main email address ({$user['email']}) has been successfully verified.", "system");
        
        $_SESSION['success'] = "Email verified successfully! A trust badge has been updated on your account.";
    } else {
        $_SESSION['error'] = "Incorrect verification code. Please check and try again.";
    }
    redirect(APP_URL . '/pages/verification.php');

} elseif ($type === 'phone') {
    // Fetch stored phone verification details
    $stmt = $pdo->prepare("SELECT phone, phone_verification_code FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user || empty($user['phone_verification_code'])) {
        $_SESSION['error'] = "No pending mobile verification code found. Please request a new one.";
        redirect(APP_URL . '/pages/verification.php');
    }
    
    if ($user['phone_verification_code'] === $code) {
        // Code is correct, update status
        $stmt = $pdo->prepare("UPDATE users SET phone_verified = 1, phone_verification_code = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Notify user
        createNotification($pdo, $user_id, "Phone Verification Successful", "Your mobile phone connectivity ({$user['phone']}) has been successfully verified.", "system");
        
        $_SESSION['success'] = "Mobile phone connectivity verified successfully!";
    } else {
        $_SESSION['error'] = "Incorrect verification code. Please check and try again.";
    }
    redirect(APP_URL . '/pages/verification.php');

} else {
    $_SESSION['error'] = "Invalid verification check type.";
    redirect(APP_URL . '/pages/verification.php');
}
?>
