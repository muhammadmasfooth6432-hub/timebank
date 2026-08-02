<?php
// includes/notification_helper.php
// Centralized notification creation helper

function createNotification($pdo, $user_id, $title, $message, $type, $related_id = null) {
    if (empty($user_id)) return false;
    
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, type, is_read, related_entity_id, created_at)
        VALUES (?, ?, ?, ?, 0, ?, NOW())
    ");
    return $stmt->execute([$user_id, $title, $message, $type, $related_id]);
}
?>