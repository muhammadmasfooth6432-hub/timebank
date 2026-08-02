<?php
// actions/notifications/mark_read.php
// AJAX endpoint to mark notifications as read

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = getDatabaseConnection();

$notification_id = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : null;

header('Content-Type: application/json');

try {
    if ($notification_id) {
        // Mark specific notification
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ? AND is_read = 0");
        $stmt->execute([$notification_id, $user_id]);
        $success = $stmt->rowCount() > 0;
    } else {
        // Mark all as read
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        $success = true;
    }

    echo json_encode(['success' => $success]);
} catch (PDOException $e) {
    error_log("Notification mark read failed: " . $e->getMessage());
    echo json_encode(['error' => 'Server error']);
}
exit;
?>