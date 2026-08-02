<?php
// actions/complete_service.php
// Finalize service, transfer credits, refresh session, and notify users

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/credit_engine.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/notification_helper.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) redirect(APP_URL . '/login.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(APP_URL . '/pages/requests/manage.php');

checkCsrf();

$provider_id = $_SESSION['user_id'];
$request_id = (int)($_POST['request_id'] ?? 0);
$actual_hours = isset($_POST['actual_hours']) && is_numeric($_POST['actual_hours']) ? (float)$_POST['actual_hours'] : 1.0;

if ($actual_hours < 0.5) {
    $_SESSION['error'] = "Service duration must be at least 0.5 hours.";
    redirect(APP_URL . '/pages/requests/manage.php');
}

$pdo = getDatabaseConnection();

// Verify request ownership and status
$stmt = $pdo->prepare("
    SELECT sr.id, sr.requester_id, sr.status, sr.service_id, s.title, s.credit_rate
    FROM service_requests sr
    JOIN services s ON sr.service_id = s.id
    WHERE sr.id = ? AND sr.provider_id = ? AND sr.status IN ('accepted', 'in_progress')
");
$stmt->execute([$request_id, $provider_id]);
$req = $stmt->fetch();

if (!$req) {
    $_SESSION['error'] = "Invalid request or already completed.";
    redirect(APP_URL . '/pages/requests/manage.php');
}

// Calculate credits
$credits = round($req['credit_rate'] * $actual_hours, 2);
$description = "Payment for: " . $req['title'] . " ($actual_hours hrs)";

// Mark service as completed
$stmt = $pdo->prepare("UPDATE service_requests SET status = 'completed', completed_at = NOW() WHERE id = ?");
$stmt->execute([$request_id]);

// Execute secure transfer
$transfer_success = transferCredits($pdo, $req['requester_id'], $provider_id, $credits, $request_id, $description);

if ($transfer_success) {
    // CRITICAL: Refresh session credit balance so header/dashboard updates immediately
    $stmt = $pdo->prepare("SELECT available_credits, locked_credits FROM users WHERE id = ?");
    $stmt->execute([$provider_id]);
    $updated_balance = $stmt->fetch();
    $_SESSION['available_credits'] = (float)$updated_balance['available_credits'];
    $_SESSION['locked_credits'] = (float)$updated_balance['locked_credits'];

    createNotification($pdo, $req['requester_id'], 'Service Completed & Charged', 
        "Your service is complete. $credits credits transferred. Please leave a rating.", 'completion', $request_id);
    
    createNotification($pdo, $provider_id, 'Credits Received', 
        "You earned $credits credits. Your locked bonus has been processed.", 'credit', $request_id);

    $_SESSION['success'] = "Service completed. $credits credits transferred successfully.";
} else {
    // Revert status if transfer failed to prevent workflow break
    $pdo->prepare("UPDATE service_requests SET status = 'in_progress', completed_at = NULL WHERE id = ?")
        ->execute([$request_id]);
    $_SESSION['error'] = "Credit transfer failed. Request reverted to in-progress. Check available balance.";
    error_log("Credit transfer failed for request ID: $request_id");
}

redirect(APP_URL . '/pages/requests/manage.php');
?>