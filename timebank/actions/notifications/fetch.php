<?php
// actions/notifications/fetch.php
// AJAX endpoint to fetch notifications and unread count

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = getDatabaseConnection();

// Get unread count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$countStmt->execute([$user_id]);
$unread_count = (int)$countStmt->fetchColumn();

// Get latest 8 notifications
$stmt = $pdo->prepare("
    SELECT id, title, message, type, is_read, related_entity_id, created_at 
    FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 8
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode([
    'count' => $unread_count,
    'notifications' => array_map(function($n) {
        return [
            'id' => $n['id'],
            'title' => $n['title'],
            'message' => $n['message'],
            'type' => $n['type'],
            'related_entity_id' => $n['related_entity_id'],
            'is_read' => (int)$n['is_read'],
            'time' => formatDate($n['created_at'])
        ];
    }, $notifications)
]);
exit;
?>