<?php
// includes/admin_auth_check.php
// Middleware to enforce admin-only access

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/session.php';

if (!isLoggedIn()) {
    redirect(APP_URL . '/login.php');
}

if (getCurrentUserRole() !== 'admin') {
    $_SESSION['error'] = 'Access denied. Administrator privileges required.';
    redirect(APP_URL . '/dashboard.php');
}
?>