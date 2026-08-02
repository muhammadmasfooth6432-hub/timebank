<?php
// actions/send_request.php
// Handle new service request submission

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/notification_helper.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) redirect(APP_URL . '/login.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(APP_URL . '/index.php');

checkCsrf();

$user_id = $_SESSION['user_id'];
$service_id = (int)($_POST['service_id'] ?? 0);
$date = trim($_POST['scheduled_date'] ?? '');
$time = trim($_POST['scheduled_time'] ?? '');
$hours = (float)($_POST['estimated_hours'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

$errors = [];
if (empty($date) || strtotime($date) === false) $errors[] = "Valid date required.";
if (empty($time)) $errors[] = "Time is required.";
if ($hours < 0.5) $errors[] = "Minimum duration is 0.5 hours.";

$scheduled_time = $date ? "$date $time:00" : '';
if ($scheduled_time && strtotime($scheduled_time) < time()) {
    $errors[] = "Scheduled time cannot be in the past.";
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    redirect(APP_URL . "/pages/requests/send.php?service_id=$service_id");
}

$pdo = getDatabaseConnection();

// Verify service still available
$stmt = $pdo->prepare("SELECT user_id, availability_status FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service || $service['availability_status'] !== 'available') {
    redirect(APP_URL . "/pages/requests/send.php?service_id=$service_id");
}

// Insert request
$stmt = $pdo->prepare("
    INSERT INTO service_requests (service_id, requester_id, provider_id, status, scheduled_time, notes, created_at)
    VALUES (?, ?, ?, 'pending', ?, ?, NOW())
");
$stmt->execute([$service_id, $user_id, $service['user_id'], $scheduled_time, $notes]);

$request_id = $pdo->lastInsertId();

// Notify provider
createNotification($pdo, $service['user_id'], "New Service Request", "You received a new request for your service.", "request", $request_id);

$_SESSION['success'] = "Request sent successfully! You will be notified when the provider responds.";
redirect(APP_URL . '/pages/requests/history.php');
?>