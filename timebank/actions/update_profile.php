<?php
// actions/update_profile.php
// Securely update user profile data

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

// Require authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    redirect(APP_URL . '/login.php');
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . '/pages/edit_profile.php');
}

// Verify CSRF
checkCsrf();

$user_id = $_SESSION['user_id'];
$errors = [];

// Sanitize inputs
$name = trim($_POST['name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$phone = trim($_POST['phone'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$skills = trim($_POST['skills'] ?? '');
$availability = trim($_POST['availability'] ?? '');

$clean_phone = !empty($phone) ? preg_replace('/[^\d+]/', '', $phone) : null;

// Validation
if (strlen($name) < 2 || strlen($name) > 100) {
    $errors[] = "Name must be between 2 and 100 characters.";
}
if (!isValidEmail($email)) {
    $errors[] = "Invalid email format.";
}
if ($clean_phone) {
    if (strlen($clean_phone) < 7 || strlen($clean_phone) > 15) {
        $errors[] = "Please enter a valid mobile phone number (7 to 15 digits).";
    }
}

$pdo = getDatabaseConnection();

// Check if email is already taken by another user
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->execute([$email, $user_id]);
if ($stmt->fetch()) {
    $errors[] = "This email is already in use by another account.";
}

// Check if phone is already taken by another verified user
if ($clean_phone) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? AND phone_verified = 1 AND id != ?");
    $stmt->execute([$clean_phone, $user_id]);
    if ($stmt->fetch()) {
        $errors[] = "This phone number is already verified on another account.";
    }
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    redirect(APP_URL . '/pages/edit_profile.php');
}

// Check if phone changed to determine if we reset phone_verified
$stmt = $pdo->prepare("SELECT phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_phone = $stmt->fetchColumn();
$phone_changed = ($current_phone !== $clean_phone);

// Handle profile image upload
$profile_image = null;
if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $upload_result = handleFileUpload($_FILES['profile_image']);
    if ($upload_result['success']) {
        $profile_image = $upload_result['filename'];
        // Delete old image if it's not the default
        $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $old_image = $stmt->fetchColumn();
        if ($old_image && $old_image !== 'default-avatar.png' && file_exists(UPLOAD_PATH . $old_image)) {
            unlink(UPLOAD_PATH . $old_image);
        }
    } else {
        $errors[] = $upload_result['error'];
    }
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    redirect(APP_URL . '/pages/edit_profile.php');
}

// Update database
$fields = ['name = ?', 'email = ?', 'phone = ?', 'bio = ?', 'skills = ?', 'availability = ?'];
$params = [$name, $email, $clean_phone, $bio, $skills, $availability];

if ($phone_changed) {
    $fields[] = 'phone_verified = 0';
    $fields[] = 'phone_verification_code = NULL';
}

if ($profile_image) {
    $fields[] = 'profile_image = ?';
    $params[] = $profile_image;
}

$sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
$params[] = $user_id;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// Update session variables
$_SESSION['name'] = $name;
$_SESSION['email'] = $email;
if ($profile_image) {
    $_SESSION['profile_image'] = $profile_image;
}

$_SESSION['success'] = "Profile updated successfully.";
redirect(APP_URL . '/pages/profile.php');
?>