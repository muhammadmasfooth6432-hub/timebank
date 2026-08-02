<?php
// actions/handle_request.php
// Handle accept, reject, start actions for service requests

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/notification_helper.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) redirect(APP_URL . '/login.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(APP_URL . '/pages/requests/manage.php');

checkCsrf();

$provider_id = $_SESSION['user_id'];
$request_id = (int)($_POST['request_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!in_array($action, ['accept', 'reject', 'start'])) {
    redirect(APP_URL . '/pages/requests/manage.php');
}

$pdo = getDatabaseConnection();

// Verify request belongs to provider
$stmt = $pdo->prepare("SELECT id, requester_id, status FROM service_requests WHERE id = ? AND provider_id = ?");
$stmt->execute([$request_id, $provider_id]);
$req = $stmt->fetch();

if (!$req) redirect(APP_URL . '/pages/requests/manage.php');

$new_status = '';
$title = '';
$message = '';

switch ($action) {
    case 'accept':
        if ($req['status'] !== 'pending') die('Invalid request state.');
        $new_status = 'in_progress';
        $title = 'Request Accepted';
        $message = 'Your service request has been accepted by the provider.';
        break;
    case 'reject':
        if ($req['status'] !== 'pending') die('Invalid request state.');
        $new_status = 'rejected';
        $title = 'Request Rejected';
        $message = 'Your service request was declined.';
        break;
    case 'start':
        if ($req['status'] !== 'accepted') die('Invalid request state.');
        $new_status = 'in_progress';
        $title = 'Service Started';
        $message = 'The provider has started working on your service.';
        break;
}

// Update status
$stmt = $pdo->prepare("UPDATE service_requests SET status = ? WHERE id = ?");
$stmt->execute([$new_status, $request_id]);

// Notify requester
createNotification($pdo, $req['requester_id'], $title, $message, $new_status === 'rejected' ? 'rejection' : 'approval', $request_id);

redirect(APP_URL . '/pages/requests/manage.php');
?>