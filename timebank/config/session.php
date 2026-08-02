<?php
// config/session.php
// Secure session configuration initializer

if (session_status() === PHP_SESSION_NONE) {
    // Secure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 when using HTTPS
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.sid_length', 48);
    ini_set('session.sid_bits_per_character', 6);
    
    session_start();
}
?>