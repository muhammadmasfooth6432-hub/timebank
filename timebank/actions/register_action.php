<?php
// actions/register_action.php
// Handle new user registration securely

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . '/register.php');
}

// Verify CSRF token
checkCsrf();

// Collect and sanitize inputs
$name = trim($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$terms_accepted = isset($_POST['terms']);

// Validation array
$errors = [];

// Name validation
if (strlen($name) < 2 || strlen($name) > 100) {
    $errors[] = "Full name must be between 2 and 100 characters.";
}

// Email validation
if (!isValidEmail($email)) {
    $errors[] = "Please enter a valid email address.";
}

// Password validation
if (!isValidPassword($password)) {
    $errors[] = "Password must be at least 8 characters long.";
} elseif ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

// Terms validation
if (!$terms_accepted) {
    $errors[] = "You must agree to the Terms of Service and Privacy Policy.";
}

// If validation fails, redirect back with errors
if (!empty($errors)) {
    require_once __DIR__ . '/../config/session.php';
    $_SESSION['error'] = implode('<br>', $errors);
    redirect(APP_URL . '/register.php');
}

// Check if email already exists in database
$pdo = getDatabaseConnection();
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    require_once __DIR__ . '/../config/session.php';
    $_SESSION['error'] = "An account with this email address already exists.";
    redirect(APP_URL . '/register.php');
}

// Hash password securely
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user with locked signup bonus
$default_role = 'user';
$locked_credits = SIGNUP_BONUS_CREDITS;

$stmt = $pdo->prepare("
    INSERT INTO users (name, email, password, role, locked_credits, available_credits, created_at)
    VALUES (?, ?, ?, ?, ?, 0, NOW())
");

$success = $stmt->execute([$name, $email, $hashed_password, $default_role, $locked_credits]);

if (!$success) {
    require_once __DIR__ . '/../config/session.php';
    $_SESSION['error'] = "Registration failed. Please try again.";
    redirect(APP_URL . '/register.php');
}

// Get new user ID
$user_id = $pdo->lastInsertId();

// Auto-login after successful registration
require_once __DIR__ . '/../config/session.php';
session_regenerate_id(true); // Prevent session fixation

$_SESSION['user_id'] = $user_id;
$_SESSION['name'] = $name;
$_SESSION['email'] = $email;
$_SESSION['role'] = $default_role;
$_SESSION['profile_image'] = 'default-avatar.png';
$_SESSION['available_credits'] = 0.00;
$_SESSION['locked_credits'] = (float)$locked_credits;
$_SESSION['logged_in'] = true;

$_SESSION['success'] = "Account created successfully! You have received 3 locked bonus credits.";
redirect(APP_URL . '/dashboard.php');
?>