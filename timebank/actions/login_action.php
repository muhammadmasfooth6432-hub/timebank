<?php
// actions/login_action.php
// Handle secure user authentication

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . '/login.php');
}

// Verify CSRF token
checkCsrf();

// Collect inputs
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$remember_me = isset($_POST['remember']);

// Basic validation
$errors = [];
if (!isValidEmail($email)) {
    $errors[] = "Please enter a valid email address.";
}
if (empty($password)) {
    $errors[] = "Password is required.";
}

if (!empty($errors)) {
    require_once __DIR__ . '/../config/session.php';
    $_SESSION['error'] = implode('<br>', $errors);
    redirect(APP_URL . '/login.php');
}

// Query database for user
$pdo = getDatabaseConnection();
$stmt = $pdo->prepare("
    SELECT id, name, email, password, role, profile_image, locked_credits, available_credits 
    FROM users WHERE email = ?
");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Verify password using constant-time comparison
if (!$user || !password_verify($password, $user['password'])) {
    require_once __DIR__ . '/../config/session.php';
    $_SESSION['error'] = "Invalid email or password.";
    redirect(APP_URL . '/login.php');
}

// Successful authentication
require_once __DIR__ . '/../config/session.php';
session_regenerate_id(true); // Critical security step

// Populate session variables
$_SESSION['user_id'] = $user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$_SESSION['profile_image'] = $user['profile_image'] ?: 'default-avatar.png';
$_SESSION['available_credits'] = (float)$user['available_credits'];
$_SESSION['locked_credits'] = (float)$user['locked_credits'];
$_SESSION['logged_in'] = true;

// Handle remember me functionality
if ($remember_me) {
    ini_set('session.cookie_lifetime', 86400 * 30); // 30 days
    ini_set('session.gc_maxlifetime', 86400 * 30);
}

// Redirect to original requested page or dashboard
$redirect_url = $_SESSION['redirect_after_login'] ?? APP_URL . '/dashboard.php';
unset($_SESSION['redirect_after_login']);

$_SESSION['success'] = "Welcome back, " . htmlspecialchars($user['name']) . "!";
redirect($redirect_url);
?>