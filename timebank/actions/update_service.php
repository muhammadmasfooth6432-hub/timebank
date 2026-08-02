<?php
// actions/update_service.php
// Update existing service listing

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
    redirect(APP_URL . '/pages/services/directory.php');
}

checkCsrf();

$user_id = $_SESSION['user_id'];
$service_id = (int)($_POST['service_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$availability_status = $_POST['availability_status'] ?? 'available';
$errors = [];

if (strlen($title) < 3 || strlen($title) > 150) {
    $errors[] = "Title must be 3-150 characters.";
}
if (empty($description) || strlen($description) < 20) {
    $errors[] = "Description must be at least 20 characters.";
}
if (!in_array($availability_status, ['available', 'busy', 'unavailable'])) {
    $errors[] = "Invalid status.";
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    redirect(APP_URL . "/pages/services/edit.php?id=$service_id");
}

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("
    SELECT s.id, s.availability_status, s.title, c.name as category_name 
    FROM services s
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE s.id = ? AND s.user_id = ?
");
$stmt->execute([$service_id, $user_id]);
$old_service = $stmt->fetch();
if (!$old_service) {
    die('Access denied.');
}
$old_status = $old_service['availability_status'];
$category_name = $old_service['category_name'] ?? 'General';

$stmt = $pdo->prepare("UPDATE services SET title = ?, description = ?, availability_status = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
$update_success = $stmt->execute([$title, $description, $availability_status, $service_id, $user_id]);

if ($update_success) {
    $_SESSION['success'] = "Service updated successfully.";
    if ($availability_status === 'available' && $old_status !== 'available') {
        // Fetch all other users
        $userStmt = $pdo->prepare("SELECT id FROM users WHERE id != ?");
        $userStmt->execute([$user_id]);
        $other_users = $userStmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($other_users as $other_user_id) {
            createNotification(
                $pdo,
                $other_user_id,
                'Service Available',
                "Service \"$title\" is now available again in category \"$category_name\".",
                'system',
                $service_id
            );
        }
    }
} else {
    $_SESSION['error'] = "Failed to update service. Please try again.";
}
redirect(APP_URL . '/pages/services/directory.php');
?>