<?php
// actions/submit_rating.php
// Handle rating submission and reputation update

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/notification_helper.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) redirect(APP_URL . '/login.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(APP_URL . '/index.php');

checkCsrf();

$requester_id = $_SESSION['user_id'];
$request_id = (int)($_POST['request_id'] ?? 0);
$provider_id = (int)($_POST['provider_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$review_text = trim($_POST['review_text'] ?? '');

// Validation
$errors = [];
if ($rating < 1 || $rating > 5) $errors[] = "Please select a valid rating (1-5 stars).";
if (strlen($review_text) > 1000) $errors[] = "Review must be under 1000 characters.";

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    redirect(APP_URL . "/pages/ratings/submit.php?request_id=$request_id");
}

$pdo = getDatabaseConnection();

// Verify ownership and completion again
$stmt = $pdo->prepare("
    SELECT id FROM service_requests 
    WHERE id = ? AND requester_id = ? AND provider_id = ? AND status = 'completed'
");
$stmt->execute([$request_id, $requester_id, $provider_id]);
if (!$stmt->fetch()) {
    redirect(APP_URL . '/pages/requests/history.php');
}

// Check for duplicate
$stmt = $pdo->prepare("SELECT id FROM ratings WHERE service_request_id = ?");
$stmt->execute([$request_id]);
if ($stmt->fetch()) {
    redirect(APP_URL . '/pages/requests/history.php');
}

// Insert rating
$stmt = $pdo->prepare("
    INSERT INTO ratings (service_request_id, reviewer_id, reviewee_id, rating_value, review_text, created_at)
    VALUES (?, ?, ?, ?, ?, NOW())
");
$stmt->execute([$request_id, $requester_id, $provider_id, $rating, $review_text]);

// Notify provider
createNotification($pdo, $provider_id, 'New Review Received', 
    "You received a new $rating-star rating for your service.", 'review', $request_id);

$_SESSION['success'] = "Rating submitted successfully. Thank you for your feedback!";
redirect(APP_URL . '/pages/requests/history.php');
?>