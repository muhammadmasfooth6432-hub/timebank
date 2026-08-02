<?php
// pages/services/view.php
// Modern public service detail view

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect(APP_URL . '/index.php');
}

$service_id = (int)$_GET['id'];
$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("
    SELECT s.*, c.name as category_name, u.name as provider_name, u.profile_image, u.bio as provider_bio, u.id as provider_id, u.phone_verified, u.email_verified
    FROM services s
    JOIN categories c ON s.category_id = c.id
    JOIN users u ON s.user_id = u.id
    WHERE s.id = ?
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    die('Service not found.');
}

$isOwner = isLoggedIn() && $service['user_id'] == getCurrentUserId();
$canRequest = isLoggedIn() && !$isOwner;

include __DIR__ . '/../../includes/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) 0;">
    <!-- Breadcrumb -->
    <nav class="view-breadcrumb">
        <a href="<?php echo APP_URL; ?>/index.php">Browse</a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        <span><?php echo htmlspecialchars($service['category_name']); ?></span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg>
        <span class="bc-active"><?php echo htmlspecialchars($service['title']); ?></span>
    </nav>

    <div class="view-layout">
        <!-- Left: Main Info -->
        <div class="view-main">
            <div class="card view-card" style="margin-bottom: var(--spacing-lg);">
                <div class="view-header">
                    <span class="service-category">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <?php echo htmlspecialchars($service['category_name']); ?>
                    </span>
                    <span class="service-status status-<?php echo strtolower($service['availability_status']); ?>">
                        <span class="status-dot"></span>
                        <?php echo ucfirst($service['availability_status']); ?>
                    </span>
                </div>

                <h1 class="view-title"><?php echo htmlspecialchars($service['title']); ?></h1>

                <div class="view-stats">
                    <div class="view-stat">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <div>
                            <div class="view-stat-label">Rate</div>
                            <div class="view-stat-value text-success"><?php echo formatCredits($service['credit_rate']); ?> credits/hr</div>
                        </div>
                    </div>
                    <div class="view-stat">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <div>
                            <div class="view-stat-label">Provider</div>
                            <div class="view-stat-value"><?php echo htmlspecialchars($service['provider_name']); ?></div>
                        </div>
                    </div>
                    <div class="view-stat">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        <div>
                            <div class="view-stat-label">Listed</div>
                            <div class="view-stat-value"><?php echo formatDate($service['created_at']); ?></div>
                        </div>
                    </div>
                </div>

                <div class="view-desc">
                    <h3 style="margin-bottom: var(--spacing-md);">About This Service</h3>
                    <p><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
                </div>
            </div>

            <!-- Provider Card -->
            <div class="card view-card provider-strip">
                <div class="provider-strip-inner">
                    <img src="<?php echo APP_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($service['profile_image'] ?: 'default-avatar.png'); ?>"
                         alt="<?php echo htmlspecialchars($service['provider_name']); ?>"
                         onerror="this.src='<?php echo APP_URL; ?>/assets/images/default-avatar.png'">
                    <div class="provider-strip-info">
                        <div class="provider-strip-name" style="display: flex; align-items: center; gap: var(--spacing-xs);">
                            <?php echo htmlspecialchars($service['provider_name']); ?>
                            <?php if ($service['email_verified'] && $service['phone_verified']): ?>
                                <span class="trust-badge" title="Identity Verified (Email & Mobile Phone Connected)" style="display: inline-flex; align-items: center; justify-content: center; background: rgba(16, 185, 129, 0.15); color: var(--color-success); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--radius-full); padding: 2px 6px; font-size: 10px; font-weight: var(--font-semibold); gap: 2px;">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Verified Provider
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="provider-strip-bio"><?php echo htmlspecialchars($service['provider_bio'] ?: 'No bio available.'); ?></p>
                    </div>
                    <a href="<?php echo APP_URL; ?>/pages/profile.php?id=<?php echo $service['provider_id']; ?>" class="btn btn-outline btn-sm">View Profile</a>
                </div>
            </div>
        </div>

        <!-- Right: Sticky CTA -->
        <aside class="view-sidebar">
            <div class="card view-cta-card">
                <div class="view-cta-rate">
                    <div class="view-cta-label">Credit Rate</div>
                    <div class="view-cta-price"><?php echo formatCredits($service['credit_rate']); ?><small>/hr</small></div>
                </div>

                <?php if ($canRequest): ?>
                    <a href="<?php echo APP_URL; ?>/pages/requests/send.php?service_id=<?php echo $service['id']; ?>" class="btn btn-primary btn-lg" style="width: 100%; margin-bottom: var(--spacing-sm);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 2L11 13"></path><path d="M22 2l-7 20-4-9-9-4 20-7z"></path></svg>
                        Request Service
                    </a>
                    <p class="text-muted text-center" style="font-size: var(--text-xs); margin: 0;">You'll spend <?php echo formatCredits($service['credit_rate']); ?> credits per hour.</p>
                <?php elseif ($isOwner): ?>
                    <div class="btn btn-lg" style="width: 100%; background: var(--color-bg-tertiary); color: var(--color-text-muted); cursor: default; margin-bottom: var(--spacing-sm);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path></svg>
                        Your Service
                    </div>
                    <a href="<?php echo APP_URL; ?>/pages/services/edit.php?id=<?php echo $service['id']; ?>" class="btn btn-outline btn-sm" style="width: 100%;">Edit Service</a>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>/login.php" class="btn btn-primary btn-lg" style="width: 100%; margin-bottom: var(--spacing-sm);">
                        Log In to Request
                    </a>
                    <p class="text-muted text-center" style="font-size: var(--text-xs); margin: 0;">New here? <a href="<?php echo APP_URL; ?>/register.php">Create an account</a></p>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>