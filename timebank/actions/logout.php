<?php
// actions/logout.php
// Secure session destruction and user logout

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = [];

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session data server-side
session_destroy();

// Redirect with success message
session_start();
$_SESSION['success'] = "You have been logged out successfully.";
redirect(APP_URL . '/index.php');
?>