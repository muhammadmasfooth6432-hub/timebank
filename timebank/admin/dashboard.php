<?php
// admin/dashboard.php
// Modern admin overview with analytics

require_once __DIR__ . '/../includes/admin_auth_check.php';
require_once __DIR__ . '/../config/db.php';

$pdo = getDatabaseConnection();

$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$stats['users'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM services WHERE availability_status = 'available'");
$stats['services'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM service_requests");
$stats['requests'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COALESCE(SUM(credits_amount), 0) FROM transactions");
$stats['total_credits'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM service_requests WHERE status = 'pending'");
$stats['pending'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COALESCE(AVG(rating_value), 0) FROM ratings");
$stats['avg_rating'] = round($stmt->fetchColumn(), 1);

$recent_users = $pdo->query("SELECT id, name, email, created_at, profile_image, role FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recent_requests = $pdo->query("
    SELECT sr.id, sr.status, sr.created_at, u.name as requester, s.title as service
    FROM service_requests sr
    JOIN users u ON sr.requester_id = u.id
    JOIN services s ON sr.service_id = s.id
    ORDER BY sr.created_at DESC LIMIT 5
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="admin-content">
        <!-- Page Header -->
        <div class="admin-page-header">
            <div class="admin-page-header-glow"></div>
            <div class="admin-page-header-inner">
                <div>
                    <h2 style="margin-bottom: var(--spacing-xs);">Platform Overview</h2>
                    <p class="text-muted" style="margin:0;">Real-time analytics and platform health.</p>
                </div>
                <a href="<?php echo APP_URL; ?>/admin/transactions.php" class="btn btn-outline btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    View Reports
                </a>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="admin-stat-grid">
            <div class="admin-stat-card stat-blue">
                <div class="admin-stat-glow"></div>
                <div class="admin-stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <div class="admin-stat-info">
                    <div class="admin-stat-label">Total Users</div>
                    <div class="admin-stat-value" data-target="<?php echo $stats['users']; ?>">0</div>
                </div>
            </div>
            <div class="admin-stat-card stat-green">
                <div class="admin-stat-glow"></div>
                <div class="admin-stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline></svg>
                </div>
                <div class="admin-stat-info">
                    <div class="admin-stat-label">Active Services</div>
                    <div class="admin-stat-value" data-target="<?php echo $stats['services']; ?>">0</div>
                </div>
            </div>
            <div class="admin-stat-card stat-amber">
                <div class="admin-stat-glow"></div>
                <div class="admin-stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
                <div class="admin-stat-info">
                    <div class="admin-stat-label">Total Requests</div>
                    <div class="admin-stat-value" data-target="<?php echo $stats['requests']; ?>">0</div>
                </div>
            </div>
            <div class="admin-stat-card stat-purple">
                <div class="admin-stat-glow"></div>
                <div class="admin-stat-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <div class="admin-stat-info">
                    <div class="admin-stat-label">Credits Exchanged</div>
                    <div class="admin-stat-value" data-target="<?php echo $stats['total_credits']; ?>" data-suffix=" credits">0</div>
                </div>
            </div>
            <div class="admin-stat-card stat-orange admin-stat-wide">
                <div class="admin-stat-glow"></div>
                <div class="admin-stat-body">
                    <div class="admin-stat-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div class="admin-stat-info">
                        <div class="admin-stat-label">Pending Requests</div>
                        <div class="admin-stat-value" data-target="<?php echo $stats['pending']; ?>">0</div>
                    </div>
                </div>
                <div class="admin-stat-extra">
                    <span style="font-size: var(--text-xs); color: var(--color-text-muted); margin-bottom: 2px;">
                        <?php echo $stats['requests'] > 0 ? round(($stats['pending'] / $stats['requests']) * 100) : 0; ?>% of total
                    </span>
                    <div class="admin-stat-bar-track">
                        <div class="admin-stat-bar-fill" style="width: <?php echo $stats['requests'] > 0 ? round(($stats['pending'] / $stats['requests']) * 100) : 0; ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="admin-stat-card stat-pink admin-stat-wide">
                <div class="admin-stat-glow"></div>
                <div class="admin-stat-body">
                    <div class="admin-stat-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    </div>
                    <div class="admin-stat-info">
                        <div class="admin-stat-label">Avg Rating</div>
                        <div class="admin-stat-value" data-target="<?php echo $stats['avg_rating']; ?>" data-decimals="1">0</div>
                    </div>
                </div>
                <div class="admin-stat-extra">
                    <div class="admin-stat-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="<?php echo $i <= round($stats['avg_rating']) ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        <?php endfor; ?>
                    </div>
                    <span style="font-size: var(--text-xs); color: var(--color-text-muted);">
                        <?php echo $stats['avg_rating']; ?> / 5.0
                    </span>
                </div>
            </div>
        </div>

        <!-- Activity Panels -->
        <div class="admin-activity-grid">
            <!-- Recent Users -->
            <div class="card admin-panel">
                <div class="admin-panel-header">
                    <h3>Recent Users</h3>
                    <a href="<?php echo APP_URL; ?>/admin/users.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="admin-user-list">
                    <?php foreach ($recent_users as $u):
                        $roleClass = 'role-' . $u['role'];
                    ?>
                        <a href="<?php echo APP_URL; ?>/pages/profile.php?id=<?php echo $u['id']; ?>" class="admin-user-row">
                            <img src="<?php echo APP_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($u['profile_image'] ?: 'default-avatar.png'); ?>"
                                 alt=""
                                 class="admin-user-avatar"
                                 onerror="this.src='<?php echo APP_URL; ?>/assets/images/default-avatar.png'">
                            <div class="admin-user-info">
                                <div class="admin-user-name"><?php echo htmlspecialchars($u['name']); ?></div>
                                <div class="admin-user-email"><?php echo htmlspecialchars($u['email']); ?></div>
                            </div>
                            <span class="role-badge <?php echo $roleClass; ?>"><?php echo ucfirst($u['role']); ?></span>
                            <span class="admin-user-date"><?php echo formatDate($u['created_at']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Requests -->
            <div class="card admin-panel">
                <div class="admin-panel-header">
                    <h3>Recent Requests</h3>
                    <a href="<?php echo APP_URL; ?>/pages/requests/manage.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="admin-request-list">
                    <?php foreach ($recent_requests as $r):
                        $statusClass = 'status-' . strtolower($r['status']);
                    ?>
                        <div class="admin-request-row">
                            <div class="admin-request-dot <?php echo $statusClass; ?>"></div>
                            <div class="admin-request-info">
                                <div class="admin-request-service"><?php echo htmlspecialchars($r['service']); ?></div>
                                <div class="admin-request-meta">by <?php echo htmlspecialchars($r['requester']); ?></div>
                            </div>
                            <span class="service-status <?php echo $statusClass; ?>">
                                <span class="status-dot"></span>
                                <?php echo ucfirst($r['status']); ?>
                            </span>
                            <span class="admin-request-date"><?php echo formatDate($r['created_at']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Quick Nav -->
        <div class="dash-section-title" style="margin-top: var(--spacing-xl);">Management</div>
        <div class="dash-actions">
            <a href="<?php echo APP_URL; ?>/admin/users.php" class="dash-action">
                <div class="dash-action-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <span>Users</span>
            </a>
            <a href="<?php echo APP_URL; ?>/admin/transactions.php" class="dash-action">
                <div class="dash-action-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <span>Transactions</span>
            </a>
            <a href="<?php echo APP_URL; ?>/admin/categories.php" class="dash-action">
                <div class="dash-action-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                </div>
                <span>Categories</span>
            </a>
        </div>
    </main>
</div>

<script>
(function() {
    const animate = (el) => {
        const target = parseFloat(el.dataset.target);
        const decimals = parseInt(el.dataset.decimals || '0');
        const suffix = el.dataset.suffix || '';
        if (isNaN(target)) return;
        const duration = 1200;
        const start = performance.now();
        const tick = (now) => {
            const p = Math.min((now - start) / duration, 1);
            const ease = 1 - Math.pow(1 - p, 3);
            const val = (target * ease).toFixed(decimals);
            el.textContent = (decimals > 0 ? parseFloat(val) : Math.floor(target * ease).toLocaleString()) + suffix;
            if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    };
    document.querySelectorAll('.admin-stat-value[data-target]').forEach(animate);
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>