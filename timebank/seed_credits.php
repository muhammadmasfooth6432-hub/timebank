<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';

try {
    $pdo = getDatabaseConnection();
    
    // Update all users to have 50.00 available credits
    $stmt = $pdo->prepare("UPDATE users SET available_credits = 50.00");
    $stmt->execute();
    
    echo "Successfully updated all users to have 50.00 available credits for testing!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
