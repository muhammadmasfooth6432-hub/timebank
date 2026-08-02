<?php
// actions/verification_send.php
// Generate and dispatch verification codes (simulated via notifications)

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

if ($type === 'email') {
    // Fetch current user email
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $email = $stmt->fetchColumn();
    
    if (!$email) {
        $_SESSION['error'] = "User email not found.";
        redirect(APP_URL . '/pages/verification.php');
    }
    
    // Generate code
    $code = strval(rand(100000, 999999));
    
    // Update DB
    $stmt = $pdo->prepare("UPDATE users SET email_verification_code = ?, email_verified = 0 WHERE id = ?");
    $stmt->execute([$code, $user_id]);
    
    // Create simulated notification
    $title = "Email Verification Code";
    $message = "To complete your email verification, use code: {$code}. (Simulated email sent to {$email})";
    createNotification($pdo, $user_id, $title, $message, 'system');
    
    $_SESSION['success'] = "Verification code sent! Check your Notification Center (bell icon) for the simulated email.";
    redirect(APP_URL . '/pages/verification.php');

} elseif ($type === 'phone') {
    $phone = sanitizeInput($_POST['phone'] ?? '');
    
    // Validate phone number format (basic digits and length check)
    $clean_phone = preg_replace('/[^\d+]/', '', $phone);
    if (strlen($clean_phone) < 7 || strlen($clean_phone) > 15) {
        $_SESSION['error'] = "Please enter a valid mobile phone number (7 to 15 digits).";
        redirect(APP_URL . '/pages/verification.php');
    }
    
    // Check if phone number is verified on another account
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? AND phone_verified = 1 AND id != ?");
    $stmt->execute([$clean_phone, $user_id]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "This phone number is already verified on another account.";
        redirect(APP_URL . '/pages/verification.php');
    }
    
    // Generate code
    $code = strval(rand(100000, 999999));
    
    // Update phone number and save verification code (reset status if new phone number)
    $stmt = $pdo->prepare("UPDATE users SET phone = ?, phone_verification_code = ?, phone_verified = 0 WHERE id = ?");
    $stmt->execute([$clean_phone, $code, $user_id]);
    
    // Create simulated notification
    $title = "SMS Connectivity Code";
    $message = "SMS Connectivity Test to {$clean_phone}: Your verification code is {$code}.";
    createNotification($pdo, $user_id, $title, $message, 'system');
    
    $_SESSION['success'] = "SMS verification code sent! Check your Notification Center (bell icon) for the simulated SMS.";
    redirect(APP_URL . '/pages/verification.php');

} else {
    $_SESSION['error'] = "Invalid verification type requested.";
    redirect(APP_URL . '/pages/verification.php');
}
?>
