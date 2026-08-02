<?php
// actions/delete_service.php
// Delete service with ownership verification

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . '/pages/services/directory.php');
}

checkCsrf();

$user_id = $_SESSION['user_id'];
$service_id = (int)($_POST['service_id'] ?? 0);

$pdo = getDatabaseConnection();

// Delete only if owned by user
$stmt = $pdo->prepare("DELETE FROM services WHERE id = ? AND user_id = ?");
$stmt->execute([$service_id, $user_id]);

if ($stmt->rowCount() > 0) {
    $_SESSION['success'] = "Service deleted successfully.";
} else {
    $_SESSION['error'] = "Could not delete service or you do not have permission.";
}

redirect(APP_URL . '/pages/services/directory.php');
?>