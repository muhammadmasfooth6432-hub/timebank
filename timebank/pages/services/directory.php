<?php
// pages/services/directory.php
// Modern service directory with filters and stats

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

requireLogin();

$pdo = getDatabaseConnection();
$user_id = getCurrentUserId();

$stmt = $pdo->prepare("
    SELECT s.id, s.title, s.description, s.credit_rate, s.availability_status, s.created_at,
           c.id as category_id, c.name as category_name
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
");
$stmt->execute([$user_id]);
$services = $stmt->fetchAll();

$total = count($services);
$active = count(array_filter($services, fn($s) => strtolower($s['availability_status']) === 'available'));
$avgRate = $total ? round(array_sum(array_column($services, 'credit_rate')) / $total, 1) : 0;

$catAccents = [1 => 'cat-accent-1', 2 => 'cat-accent-2', 3 => 'cat-accent-3', 4 => 'cat-accent-4', 5 => 'cat-accent-5'];
$catIcons = [1 => 'code', 2 => 'pen', 3 => 'music', 4 => 'camera', 5 => 'help'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) 0;">
    <div class="page-header-bar">
        <div>
            <h2 style="margin-bottom: var(--spacing-xs);">My Services</h2>
            <p class="text-muted" style="margin:0;">Manage offerings and keep your availability up to date.</p>
        </div>
        <div class="page-header-stats">
            <div class="stat-pill"><span><?php echo $total; ?></span> Total</div>
            <div class="stat-pill success"><span><?php echo $active; ?></span> Active</div>
            <div class="stat-pill"><span><?php echo formatCredits($avgRate); ?></span> Avg Rate</div>
        </div>
    </div>

    <div class="toolbar">
        <div class="toolbar-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
            <input type="text" id="serviceSearch" placeholder="Search your services..." class="form-control">
        </div>
        <div class="filter-tabs" id="filterTabs">
            <button class="active" data-filter="all">All</button>
            <button data-filter="available">Available</button>
            <button data-filter="busy">Busy</button>
            <button data-filter="unavailable">Unavailable</button>
        </div>
        <a href="<?php echo APP_URL; ?>/pages/services/add.php" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Add Service
        </a>
    </div>

    <?php if (empty($services)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            </div>
            <h3>No services listed yet</h3>
            <p class="text-muted">Start earning credits by offering your skills to the community.</p>
            <a href="<?php echo APP_URL; ?>/pages/services/add.php" class="btn btn-primary" style="margin-top: var(--spacing-md);">Create Your First Service</a>
        </div>
    <?php else: ?>
        <div class="services-grid" id="servicesGrid">
            <?php foreach ($services as $index => $service):
                $accentClass = $catAccents[$service['category_id']] ?? 'cat-accent-1';
                $statusClass = 'status-' . strtolower($service['availability_status']);
            ?>
                <article class="card service-card <?php echo $accentClass; ?>" data-status="<?php echo strtolower($service['availability_status']); ?>" data-title="<?php echo htmlspecialchars(strtolower($service['title'])); ?>" style="animation-delay: <?php echo $index * 60; ?>ms;">
                    <div class="service-card-top">
                        <div class="service-icon-badge">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        </div>
                        <span class="service-category"><?php echo htmlspecialchars($service['category_name']); ?></span>
                        <span class="service-status <?php echo $statusClass; ?>">
                            <span class="status-dot"></span>
                            <?php echo ucfirst($service['availability_status']); ?>
                        </span>
                    </div>

                    <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p class="service-desc"><?php echo htmlspecialchars(substr($service['description'], 0, 110)) . (strlen($service['description']) > 110 ? '...' : ''); ?></p>

                    <div class="service-meta">
                        <div class="credit-chip">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <?php echo formatCredits($service['credit_rate']); ?> <small>credits/hr</small>
                        </div>
                        <span class="service-date">Added <?php echo date('M j, Y', strtotime($service['created_at'])); ?></span>
                    </div>

                    <div class="service-actions">
                        <a href="<?php echo APP_URL; ?>/pages/services/view.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-outline" title="View">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </a>
                        <a href="<?php echo APP_URL; ?>/pages/services/edit.php?id=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </a>
                        <form method="POST" action="<?php echo APP_URL; ?>/actions/delete_service.php" style="margin:0;" onsubmit="return confirm('Delete this service?');">
                            <?php include __DIR__ . '/../../includes/csrf.php'; echo csrfField(); ?>
                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete" style="background: var(--color-danger); color: white;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
(function() {
    const searchInput = document.getElementById('serviceSearch');
    const tabs = document.querySelectorAll('#filterTabs button');
    const cards = document.querySelectorAll('.service-card');

    function filter() {
        const term = searchInput.value.toLowerCase();
        const activeTab = document.querySelector('#filterTabs button.active');
        const statusFilter = activeTab ? activeTab.dataset.filter : 'all';

        cards.forEach(card => {
            const title = card.dataset.title;
            const status = card.dataset.status;
            const matchesTerm = title.includes(term);
            const matchesStatus = statusFilter === 'all' || status === statusFilter;
            card.style.display = (matchesTerm && matchesStatus) ? '' : 'none';
        });
    }

    searchInput?.addEventListener('input', filter);
    tabs.forEach(tab => tab.addEventListener('click', () => {
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        filter();
    }));
})();
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>