<?php
// actions/migrate.php
// Safely run database updates to add verification fields

require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/plain');

try {
    $pdo = getDatabaseConnection();
    
    // Check if the 'phone' column already exists in the 'users' table
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        $sql = "ALTER TABLE users 
                ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER email,
                ADD COLUMN phone_verified TINYINT(1) DEFAULT 0 AFTER role,
                ADD COLUMN email_verified TINYINT(1) DEFAULT 0 AFTER phone_verified,
                ADD COLUMN phone_verification_code VARCHAR(10) DEFAULT NULL AFTER availability,
                ADD COLUMN email_verification_code VARCHAR(10) DEFAULT NULL AFTER phone_verification_code";
        
        $pdo->exec($sql);
        echo "SUCCESS: Database migration completed successfully. Added phone, phone_verified, email_verified, and verification code columns.\n";
    } else {
        echo "INFO: Database migration skipped. Columns already exist.\n";
    }
} catch (Exception $e) {
    echo "ERROR: Database migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
