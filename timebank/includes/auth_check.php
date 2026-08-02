<?php
// includes/auth_check.php
// Middleware to check authentication and role access

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/functions.php';

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect(APP_URL . '/login.php');
    }
}

function requireRole($role) {
    requireLogin();
    if (getCurrentUserRole() !== $role) {
        die('Access denied. Insufficient permissions.');
    }
}

function requireAdmin() {
    requireRole('admin');
}
?>