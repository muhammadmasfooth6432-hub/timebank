<?php
// dashboard.php
// Modern user dashboard

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

requireLogin();

$pdo = getDatabaseConnection();
$user_id = getCurrentUserId();
$user_role = getCurrentUserRole();
$current_page = 'dashboard';

// Fetch fresh credit balances from database
$stmt = $pdo->prepare("SELECT locked_credits, available_credits, profile_image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch();

$_SESSION['locked_credits'] = (float)$userData['locked_credits'];
$_SESSION['available_credits'] = (float)$userData['available_credits'];

// Stats
$servicesCount = $pdo->prepare("SELECT COUNT(*) FROM services WHERE user_id = ?");
$servicesCount->execute([$user_id]);
$servicesCount = $servicesCount->fetchColumn();

$requestsCount = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE requester_id = ? OR provider_id = ?");
$requestsCount->execute([$user_id, $user_id]);
$requestsCount = $requestsCount->fetchColumn();

// Recent services
$recentServices = $pdo->prepare("
    SELECT s.id, s.title, s.credit_rate, s.availability_status, c.name as category_name
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
    LIMIT 3
");
$recentServices->execute([$user_id]);
$recentServices = $recentServices->fetchAll();

// Recent requests
$recentRequests = $pdo->prepare("
    SELECT sr.id, sr.status, s.title as service_title, u.name as other_party,
           CASE WHEN sr.requester_id = ? THEN 'sent' ELSE 'received' END as direction,
           sr.created_at
    FROM service_requests sr
    JOIN services s ON sr.service_id = s.id
    JOIN users u ON (CASE WHEN sr.requester_id = ? THEN sr.provider_id ELSE sr.requester_id END) = u.id
    WHERE sr.requester_id = ? OR sr.provider_id = ?
    ORDER BY sr.created_at DESC
    LIMIT 4
");
$recentRequests->execute([$user_id, $user_id, $user_id, $user_id]);
$recentRequests = $recentRequests->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) 0;">
    <!-- Welcome Hero -->
    <div class="dash-hero">
        <div class="dash-hero-glow"></div>
        <div class="dash-hero-content">
            <img src="<?php echo APP_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($userData['profile_image'] ?: 'default-avatar.png'); ?>"
                 alt="Profile"
                 class="dash-hero-avatar"
                 onerror="this.src='<?php echo APP_URL; ?>/assets/images/default-avatar.png'">
            <div class="dash-hero-text">
                <div class="dash-hero-greeting">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong></div>
                <div class="dash-hero-meta">
                    <span class="dash-role-badge"><?php echo ucfirst($user_role); ?></span>
                    <span class="dash-id">Account #<?php echo $user_id; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="dash-stats">
        <div class="dash-stat glass-green">
            <div class="dash-stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div class="dash-stat-body">
                <div class="dash-stat-label">Available Credits</div>
                <div class="dash-stat-value"><?php echo formatCredits($_SESSION['available_credits']); ?></div>
            </div>
        </div>
        <div class="dash-stat glass-amber">
            <div class="dash-stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            </div>
            <div class="dash-stat-body">
                <div class="dash-stat-label">Locked Credits</div>
                <div class="dash-stat-value"><?php echo formatCredits($_SESSION['locked_credits']); ?></div>
            </div>
        </div>
        <div class="dash-stat glass-primary">
            <div class="dash-stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline></svg>
            </div>
            <div class="dash-stat-body">
                <div class="dash-stat-label">My Services</div>
                <div class="dash-stat-value"><?php echo $servicesCount; ?></div>
            </div>
        </div>
        <div class="dash-stat glass-info">
            <div class="dash-stat-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            </div>
            <div class="dash-stat-body">
                <div class="dash-stat-label">Total Requests</div>
                <div class="dash-stat-value"><?php echo $requestsCount; ?></div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="dash-section-title">Quick Actions</div>
    <div class="dash-actions">
        <a href="<?php echo APP_URL; ?>/index.php" class="dash-action">
            <div class="dash-action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
            </div>
            <span>Browse</span>
        </a>
        <a href="<?php echo APP_URL; ?>/pages/services/directory.php" class="dash-action">
            <div class="dash-action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
            </div>
            <span>My Services</span>
        </a>
        <a href="<?php echo APP_URL; ?>/pages/requests/manage.php" class="dash-action">
            <div class="dash-action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
            </div>
            <span>Requests</span>
        </a>
        <a href="<?php echo APP_URL; ?>/pages/credits/history.php" class="dash-action">
            <div class="dash-action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <span>Credits</span>
        </a>
        <a href="<?php echo APP_URL; ?>/pages/profile.php" class="dash-action">
            <div class="dash-action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
            <span>Profile</span>
        </a>
        <?php if ($user_role === 'admin'): ?>
        <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="dash-action">
            <div class="dash-action-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
            </div>
            <span>Admin</span>
        </a>
        <?php endif; ?>
    </div>

    <!-- Recent Activity Grid -->
    <div class="dash-activity-grid">
        <!-- Recent Services -->
        <div class="card dash-panel">
            <div class="dash-panel-header">
                <h3>Recent Services</h3>
                <a href="<?php echo APP_URL; ?>/pages/services/directory.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <?php if (empty($recentServices)): ?>
                <div class="dash-empty">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline></svg>
                    <p class="text-muted">No services yet.</p>
                    <a href="<?php echo APP_URL; ?>/pages/services/add.php" class="btn btn-sm btn-primary">Create First Service</a>
                </div>
            <?php else: ?>
                <div class="dash-service-list">
                    <?php foreach ($recentServices as $svc): ?>
                        <a href="<?php echo APP_URL; ?>/pages/services/view.php?id=<?php echo $svc['id']; ?>" class="dash-service-item">
                            <div class="dash-service-dot status-<?php echo strtolower($svc['availability_status']); ?>"></div>
                            <div class="dash-service-info">
                                <div class="dash-service-title"><?php echo htmlspecialchars($svc['title']); ?></div>
                                <div class="dash-service-meta"><?php echo htmlspecialchars($svc['category_name']); ?></div>
                            </div>
                            <div class="dash-service-rate"><?php echo formatCredits($svc['credit_rate']); ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Requests -->
        <div class="card dash-panel">
            <div class="dash-panel-header">
                <h3>Recent Requests</h3>
                <a href="<?php echo APP_URL; ?>/pages/requests/history.php" class="btn btn-sm btn-outline">View All</a>
            </div>
            <?php if (empty($recentRequests)): ?>
                <div class="dash-empty">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                    <p class="text-muted">No requests yet.</p>
                </div>
            <?php else: ?>
                <div class="dash-request-list">
                    <?php foreach ($recentRequests as $req):
                        $statusClass = 'status-' . strtolower($req['status']);
                        $directionLabel = $req['direction'] === 'sent' ? 'To' : 'From';
                    ?>
                        <div class="dash-request-item">
                            <div class="dash-request-dot <?php echo $statusClass; ?>"></div>
                            <div class="dash-request-info">
                                <div class="dash-request-title"><?php echo htmlspecialchars($req['service_title']); ?></div>
                                <div class="dash-request-meta"><?php echo $directionLabel; ?> <?php echo htmlspecialchars($req['other_party']); ?> &middot; <?php echo ucfirst($req['status']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>