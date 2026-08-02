<?php
// pages/requests/send.php
// Request a service from another user

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

requireLogin();

if (!isset($_GET['service_id']) || !is_numeric($_GET['service_id'])) {
    redirect(APP_URL . '/index.php');
}

$pdo = getDatabaseConnection();
$user_id = getCurrentUserId();
$service_id = (int)$_GET['service_id'];

// Fetch service and provider details
$stmt = $pdo->prepare("
    SELECT s.*, u.name as provider_name, u.id as provider_id, c.name as category_name
    FROM services s
    JOIN users u ON s.user_id = u.id
    JOIN categories c ON s.category_id = c.id
    WHERE s.id = ?
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) die('Service not found.');
if ($service['user_id'] == $user_id) die('You cannot request your own service.');
if ($service['availability_status'] !== 'available') die('This service is currently unavailable.');

// Check for existing active request
$stmt = $pdo->prepare("
    SELECT id FROM service_requests 
    WHERE requester_id = ? AND service_id = ? AND status IN ('pending', 'accepted', 'in_progress')
");
$stmt->execute([$user_id, $service_id]);
$existing = $stmt->fetch();

if ($existing) die('You already have an active request for this service.');

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) 0;">
    <div class="request-form-wrapper">
        <div class="card">
            <h2 style="margin-bottom: var(--spacing-sm);">Request Service</h2>
            <p class="text-muted">Requesting: <strong><?php echo htmlspecialchars($service['title']); ?></strong> by <?php echo htmlspecialchars($service['provider_name']); ?></p>
            <p class="text-muted">Rate: <?php echo formatCredits($service['credit_rate']); ?> credits/hour</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-top: var(--spacing-lg);"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo APP_URL; ?>/actions/send_request.php" style="margin-top: var(--spacing-xl);">
                <?php include __DIR__ . '/../../includes/csrf.php'; echo csrfField(); ?>
                <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                
                <div class="schedule-grid">
                    <div class="form-group">
                        <label for="scheduled_date">Preferred Date</label>
                        <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="scheduled_time">Preferred Time</label>
                        <input type="time" id="scheduled_time" name="scheduled_time" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="estimated_hours">Estimated Duration (Hours)</label>
                    <input type="number" id="estimated_hours" name="estimated_hours" class="form-control" min="0.5" step="0.5" required>
                </div>

                <div class="form-group">
                    <label for="notes">Additional Notes</label>
                    <textarea id="notes" name="notes" class="form-control" rows="4" placeholder="Describe what you need help with, specific requirements, or location details..."></textarea>
                </div>

                <div class="flex justify-between" style="margin-top: var(--spacing-xl);">
                    <a href="<?php echo APP_URL; ?>/pages/services/view.php?id=<?php echo $service_id; ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>