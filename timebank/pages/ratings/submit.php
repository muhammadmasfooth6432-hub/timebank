<?php
// pages/ratings/submit.php
// Submit rating and review for completed service

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/rating_helper.php';

requireLogin();

$pdo = getDatabaseConnection();
$user_id = getCurrentUserId();
$request_id = (int)($_GET['request_id'] ?? 0);

if (!$request_id) redirect(APP_URL . '/pages/requests/history.php');

// Verify request exists, is completed, and belongs to current user as requester
$stmt = $pdo->prepare("
    SELECT sr.*, s.title as service_title, u.name as provider_name, u.id as provider_id
    FROM service_requests sr
    JOIN services s ON sr.service_id = s.id
    JOIN users u ON sr.provider_id = u.id
    WHERE sr.id = ? AND sr.requester_id = ? AND sr.status = 'completed'
");
$stmt->execute([$request_id, $user_id]);
$request = $stmt->fetch();

if (!$request) {
    die('Request not found, not completed, or access denied.');
}

// Check for existing rating
$stmt = $pdo->prepare("SELECT id FROM ratings WHERE service_request_id = ?");
$stmt->execute([$request_id]);
if ($stmt->fetch()) {
    redirect(APP_URL . '/pages/requests/history.php?msg=already_rated');
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) 0; max-width: 700px;">
    <div class="card">
        <h2 style="margin-bottom: var(--spacing-sm);">Rate Your Experience</h2>
        <p class="text-muted">Service: <strong><?php echo htmlspecialchars($request['service_title']); ?></strong></p>
        <p class="text-muted">Provider: <strong><?php echo htmlspecialchars($request['provider_name']); ?></strong></p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo APP_URL; ?>/actions/submit_rating.php" style="margin-top: var(--spacing-xl);">
            <?php include __DIR__ . '/../../includes/csrf.php'; echo csrfField(); ?>
            <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
            <input type="hidden" name="provider_id" value="<?php echo $request['provider_id']; ?>">
            
            <div class="form-group">
                <label>Rating</label>
                <div class="star-rating">
                    <input type="radio" id="star5" name="rating" value="5" required>
                    <label for="star5" title="5 stars">&#9733;</label>
                    <input type="radio" id="star4" name="rating" value="4">
                    <label for="star4" title="4 stars">&#9733;</label>
                    <input type="radio" id="star3" name="rating" value="3">
                    <label for="star3" title="3 stars">&#9733;</label>
                    <input type="radio" id="star2" name="rating" value="2">
                    <label for="star2" title="2 stars">&#9733;</label>
                    <input type="radio" id="star1" name="rating" value="1">
                    <label for="star1" title="1 star">&#9733;</label>
                </div>
            </div>

            <div class="form-group">
                <label for="review_text">Written Review (Optional)</label>
                <textarea id="review_text" name="review_text" class="form-control" rows="5" 
                          placeholder="Describe your experience. What went well? Any suggestions?"></textarea>
            </div>

            <div class="flex justify-between" style="margin-top: var(--spacing-xl);">
                <a href="<?php echo APP_URL; ?>/pages/requests/history.php" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Submit Review</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>