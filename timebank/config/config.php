<?php
// config/config.php
// Global configuration constants

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'timebank_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'Time Bank P2P Exchange');
define('APP_URL', 'http://localhost/timebank');
define('UPLOAD_PATH', __DIR__ . '/../uploads/profiles/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Session settings
define('SESSION_LIFETIME', 3600); // 1 hour

// Credit system settings
define('SIGNUP_BONUS_CREDITS', 3.00);
define('MIN_CREDIT_TRANSFER', 0.50);

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');
?>