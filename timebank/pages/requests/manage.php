<?php
// pages/requests/manage.php
// Modern provider dashboard for managing incoming requests

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

requireLogin();

$pdo = getDatabaseConnection();
$provider_id = getCurrentUserId();

$status_filter = $_GET['status'] ?? 'all';
$allowed_statuses = ['all', 'pending', 'accepted', 'in_progress', 'completed', 'cancelled'];
if (!in_array($status_filter, $allowed_statuses)) $status_filter = 'all';

// Fetch requests
$sql = "
    SELECT sr.*, s.title as service_title, s.credit_rate, c.name as category,
           u.name as requester_name, u.profile_image, u.email as requester_email
    FROM service_requests sr
    JOIN services s ON sr.service_id = s.id
    JOIN categories c ON s.category_id = c.id
    JOIN users u ON sr.requester_id = u.id
    WHERE sr.provider_id = ?
";
$params = [$provider_id];
if ($status_filter !== 'all') {
    if ($status_filter === 'accepted') {
        $sql .= " AND sr.status IN ('accepted', 'in_progress')";
    } else {
        $sql .= " AND sr.status = ?";
        $params[] = $status_filter;
    }
}
$sql .= " ORDER BY sr.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Count stats for header (combine accepted and in_progress under Active)
$counts = ['all' => 0, 'pending' => 0, 'accepted' => 0, 'completed' => 0];
$countStmt = $pdo->prepare("SELECT status, COUNT(*) FROM service_requests WHERE provider_id = ? GROUP BY status");
$countStmt->execute([$provider_id]);
foreach ($countStmt->fetchAll(PDO::FETCH_KEY_PAIR) as $st => $c) {
    $counts['all'] += $c;
    if ($st === 'accepted' || $st === 'in_progress') {
        $counts['accepted'] += $c;
    } else if (isset($counts[$st])) {
        $counts[$st] = $c;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) 0;">
    <!-- Hero Header -->
    <div class="req-page-header">
        <div class="req-page-header-glow"></div>
        <div class="req-page-header-inner">
            <div>
                <h2 style="margin-bottom: var(--spacing-xs);">Manage Requests</h2>
                <p class="text-muted" style="margin:0;">Review and manage service bookings from users.</p>
            </div>
            <div class="req-header-stats">
                <span class="req-stat-pill"><span><?php echo $counts['all']; ?></span> Total</span>
                <span class="req-stat-pill req-pill-warn"><span><?php echo $counts['pending']; ?></span> Pending</span>
                <span class="req-stat-pill req-pill-info"><span><?php echo $counts['accepted']; ?></span> Active</span>
                <span class="req-stat-pill req-pill-success"><span><?php echo $counts['completed']; ?></span> Done</span>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="req-filter-bar">
        <a href="?status=all" class="req-filter-tab <?php echo $status_filter === 'all' ? 'active' : '';?>">All</a>
        <a href="?status=pending" class="req-filter-tab <?php echo $status_filter === 'pending' ? 'active' : '';?>">Pending</a>
        <a href="?status=accepted" class="req-filter-tab <?php echo $status_filter === 'accepted' ? 'active' : '';?>">Active</a>
        <a href="?status=completed" class="req-filter-tab <?php echo $status_filter === 'completed' ? 'active' : '';?>">Completed</a>
        <a href="?status=cancelled" class="req-filter-tab <?php echo $status_filter === 'cancelled' ? 'active' : '';?>">Cancelled</a>
    </div>

    <?php if (empty($requests)): ?>
        <div class="req-empty-state">
            <div class="req-empty-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
            </div>
            <h3>No requests found</h3>
            <p class="text-muted"><?php echo $status_filter === 'all' ? 'Service requests will appear here when users book your services.' : 'No ' . $status_filter . ' requests found.'; ?></p>
        </div>
    <?php else: ?>
        <div class="req-list">
            <?php foreach ($requests as $index => $req):
                $statusClass = 'status-' . strtolower($req['status']);
            ?>
                <div class="req-card <?php echo $statusClass; ?>" style="animation-delay: <?php echo 0.05 * $index; ?>s">
                    <div class="req-card-border"></div>
                    <div class="req-card-body">
                        <!-- Top row: service info + status -->
                        <div class="req-card-top">
                            <div class="req-service-info">
                                <div class="req-service-name"><?php echo htmlspecialchars($req['service_title']); ?></div>
                                <div class="req-service-meta">
                                    <span class="req-category-badge"><?php echo htmlspecialchars($req['category']); ?></span>
                                    <span class="req-rate-chip"><?php echo formatCredits($req['credit_rate']); ?> credits/hr</span>
                                    <span class="req-date"><?php echo formatDate($req['created_at']); ?></span>
                                </div>
                            </div>
                            <span class="service-status <?php echo $statusClass; ?>">
                                <span class="status-dot"></span>
                                <?php echo ucfirst(str_replace('_', ' ', $req['status'])); ?>
                            </span>
                        </div>

                        <!-- Middle: requester -->
                        <div class="req-requester">
                            <img src="<?php echo APP_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($req['profile_image'] ?? 'default-avatar.png'); ?>"
                                 alt=""
                                 class="req-requester-avatar"
                                 onerror="this.src='<?php echo APP_URL; ?>/assets/images/default-avatar.png'">
                            <div class="req-requester-info">
                                <div class="req-requester-name"><?php echo htmlspecialchars($req['requester_name']); ?></div>
                                <div class="req-requester-email"><?php echo htmlspecialchars($req['requester_email']); ?></div>
                            </div>
                        </div>

                        <!-- Details: scheduled + notes -->
                        <?php if ($req['scheduled_time'] || !empty($req['notes'])): ?>
                            <div class="req-details">
                                <?php if ($req['scheduled_time']): ?>
                                    <div class="req-detail-row">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <span>Scheduled: <?php echo formatDate($req['scheduled_time']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($req['notes'])): ?>
                                    <div class="req-detail-row">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                        <span><?php echo nl2br(htmlspecialchars($req['notes'])); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Actions -->
                        <div class="req-actions">
                            <?php if ($req['status'] === 'pending'): ?>
                                <form method="POST" action="<?php echo APP_URL; ?>/actions/handle_request.php" style="margin:0; display:flex; gap: var(--spacing-sm);">
                                    <?php include __DIR__ . '/../../includes/csrf.php'; echo csrfField(); ?>
                                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                    <button type="submit" name="action" value="accept" class="btn btn-sm btn-primary req-btn-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        Accept
                                    </button>
                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline req-btn-icon">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        Reject
                                    </button>
                                </form>
                            <?php elseif ($req['status'] === 'accepted' || $req['status'] === 'in_progress'): ?>
                                <button type="button" class="btn btn-sm btn-success req-btn-icon open-completion-modal" data-request-id="<?php echo $req['id']; ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    Mark Completed
                                </button>
                            <?php elseif ($req['status'] === 'completed'): ?>
                                <span class="req-earned-badge">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <?php echo formatCredits($req['credit_rate'] * ($req['actual_hours'] ?? 1)); ?> earned
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Completion Modal -->
<div class="completion-modal" id="completionModal">
    <div class="modal-glass">
        <div class="modal-glow"></div>
        <div class="modal-header">
            <h3 style="margin:0;">Complete Service</h3>
            <button type="button" class="modal-close close-modal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <form method="POST" action="<?php echo APP_URL; ?>/actions/complete_service.php">
            <?php include __DIR__ . '/../../includes/csrf.php'; echo csrfField(); ?>
            <input type="hidden" name="request_id" id="modal_request_id">
            <div class="form-group">
                <label for="actual_hours">Actual Hours Worked</label>
                <input type="number" id="actual_hours" name="actual_hours" class="form-control" min="0.5" step="0.5" value="1.0" required>
                <small class="text-muted">Used to calculate final credit transfer</small>
            </div>
            <div class="flex gap-md" style="margin-top: var(--spacing-lg);">
                <button type="button" class="btn btn-outline close-modal" style="flex:1;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:2;">Confirm & Transfer Credits</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('completionModal');
    const requestIdInput = document.getElementById('modal_request_id');

    document.querySelectorAll('.open-completion-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            requestIdInput.value = this.dataset.requestId;
            modal.classList.add('active');
        });
    });

    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', () => modal.classList.remove('active'));
    });

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.remove('active');
    });
})();
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>