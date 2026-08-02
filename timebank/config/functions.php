<?php
// config/functions.php
// Helper functions used throughout the application

// Sanitize user input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate password strength
function isValidPassword($password) {
    return strlen($password) >= 8;
}

// Generate CSRF token
function generateCsrfToken() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCsrfToken($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Redirect helper
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Get current user ID from session
function getCurrentUserId() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['user_id'] ?? null;
}

// Get current user role from session
function getCurrentUserRole() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['role'] ?? null;
}

// Check if user is logged in
function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Check if user has specific role
function hasRole($required_role) {
    return isLoggedIn() && getCurrentUserRole() === $required_role;
}

// Format credit display
function formatCredits($amount) {
    return number_format($amount, 2);
}

// Format date for display
function formatDate($date_string) {
    return date('M d, Y g:i A', strtotime($date_string));
}

// Handle file upload
function handleFileUpload($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error code: ' . $file['error']];
    }
    
    $file_size = $file['size'];
    $file_type = $file['type'];
    $file_name = $file['name'];
    
    if ($file_size > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'error' => 'File too large. Max 5MB allowed.'];
    }
    
    if (!in_array($file_type, $allowed_types)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF allowed.'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_filename = uniqid('avatar_', true) . '.' . $extension;
    $destination = UPLOAD_PATH . $new_filename;
    
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to save uploaded file.'];
    }
    
    return ['success' => true, 'filename' => $new_filename];
}
?>