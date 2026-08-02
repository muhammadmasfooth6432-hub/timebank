<?php
require_once 'config/db.php';
try {
    $pdo = getDatabaseConnection();
    echo "Database connection successful!";
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    echo "<br>Categories found: " . $stmt->fetchColumn();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>