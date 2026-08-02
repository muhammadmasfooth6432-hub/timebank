<?php
// actions/add_service.php
// Process new service creation

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/notification_helper.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    redirect(APP_URL . '/login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . '/pages/services/add.php');
}

checkCsrf();

$user_id = $_SESSION['user_id'];
$title = trim($_POST['title'] ?? '');
$category_id = (int)($_POST['category_id'] ?? 0);
$description = trim($_POST['description'] ?? '');
$availability_status = $_POST['availability_status'] ?? 'available';
$errors = [];

// Validation
if (strlen($title) < 3 || strlen($title) > 150) {
    $errors[] = "Service title must be between 3 and 150 characters.";
}
if (empty($description) || strlen($description) < 20) {
    $errors[] = "Please provide a detailed description (at least 20 characters).";
}
if (!in_array($availability_status, ['available', 'busy', 'unavailable'])) {
    $errors[] = "Invalid availability status.";
}

// Validate category and fetch credit rate
$pdo = getDatabaseConnection();
$stmt = $pdo->prepare("SELECT id, name, credit_per_hour FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch();

if (!$category) {
    $errors[] = "Please select a valid category.";
} else {
    $credit_rate = (float)$category['credit_per_hour'];
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    $_SESSION['form_data'] = $_POST;
    redirect(APP_URL . '/pages/services/add.php');
}

// Insert service
$stmt = $pdo->prepare("
    INSERT INTO services (user_id, category_id, title, description, credit_rate, availability_status, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
");

$success = $stmt->execute([$user_id, $category_id, $title, $description, $credit_rate, $availability_status]);

if ($success) {
    $_SESSION['success'] = "Service created successfully!";
    if ($availability_status === 'available') {
        $service_id = $pdo->lastInsertId();
        $category_name = $category['name'] ?? 'General';
        
        // Fetch all other users
        $userStmt = $pdo->prepare("SELECT id FROM users WHERE id != ?");
        $userStmt->execute([$user_id]);
        $other_users = $userStmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($other_users as $other_user_id) {
            createNotification(
                $pdo,
                $other_user_id,
                'New Service Available',
                "New service \"$title\" is now available in category \"$category_name\".",
                'system',
                $service_id
            );
        }
    }
} else {
    $_SESSION['error'] = "Failed to create service. Please try again.";
}

redirect(APP_URL . '/pages/services/directory.php');
?>