<?php
// includes/csrf.php
// CSRF protection utilities

require_once __DIR__ . '/../config/functions.php';

if (!function_exists('csrfField')) {
    function csrfField() {
        $token = generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('checkCsrf')) {
    function checkCsrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!verifyCsrfToken($token)) {
                die('CSRF token validation failed. Request blocked.');
            }
        }
    }
}
?>