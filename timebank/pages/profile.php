<?php
// pages/profile.php
// Modern user profile view

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/rating_helper.php';

requireLogin();

$pdo = getDatabaseConnection();
$current_user_id = getCurrentUserId();

$target_id = isset($_GET['id']) ? (int)$_GET['id'] : $current_user_id;

$stmt = $pdo->prepare("
    SELECT id, name, email, phone, role, profile_image, bio, skills, availability,
           locked_credits, available_credits, created_at, phone_verified, email_verified
    FROM users WHERE id = ?
");
$stmt->execute([$target_id]);
$user = $stmt->fetch();

if (!$user) {
    die('User not found.');
}

$is_own_profile = ($target_id == $current_user_id);
$skills_array = !empty($user['skills']) ? array_map('trim', explode(',', $user['skills'])) : [];

// Stats
$servicesCount = $pdo->prepare("SELECT COUNT(*) FROM services WHERE user_id = ?");
$servicesCount->execute([$target_id]);
$servicesCount = $servicesCount->fetchColumn();

$completedCount = $pdo->prepare("SELECT COUNT(*) FROM service_requests WHERE (requester_id = ? OR provider_id = ?) AND status = 'completed'");
$completedCount->execute([$target_id, $target_id]);
$completedCount = $completedCount->fetchColumn();

$rep = calculateReputation($pdo, $target_id);
$score = round($rep['avg_score'], 1);
$count = (int)$rep['total_reviews'];

// Recent services preview
$recentServices = $pdo->prepare("
    SELECT s.id, s.title, s.credit_rate, s.availability_status, c.name as category_name
    FROM services s
    JOIN categories c ON s.category_id = c.id
    WHERE s.user_id = ?
    ORDER BY s.created_at DESC
    LIMIT 3
");
$recentServices->execute([$target_id]);
$recentServices = $recentServices->fetchAll();

// Reviews with reviewer avatars
$reviews = [];
if ($count > 0) {
    $reviews = getUserRatings($pdo, $target_id, 5);
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) 0;">
    <!-- Profile Hero -->
    <div class="profile-hero">
        <div class="profile-hero-glow"></div>
        <div class="profile-hero-inner">
            <img src="<?php echo APP_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['profile_image'] ?: 'default-avatar.png'); ?>"
                 alt="<?php echo htmlspecialchars($user['name']); ?>"
                 class="profile-hero-avatar"
                 onerror="this.src='<?php echo APP_URL; ?>/assets/images/default-avatar.png'">
            <div class="profile-hero-info">
                <h1 class="profile-hero-name"><?php echo htmlspecialchars($user['name']); ?></h1>
                <div class="profile-hero-meta">
                    <span class="profile-hero-role"><?php echo ucfirst($user['role']); ?></span>
                    <span class="profile-hero-since">Member since <?php echo formatDate($user['created_at']); ?></span>
                </div>
                <?php if ($is_own_profile): ?>
                    <a href="<?php echo APP_URL; ?>/pages/edit_profile.php" class="btn btn-primary btn-sm" style="margin-top: var(--spacing-sm);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        Edit Profile
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stats Ribbon -->
    <div class="profile-ribbon">
        <div class="profile-ribbon-stat glass-green">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <div>
                <div class="profile-ribbon-label">Available</div>
                <div class="profile-ribbon-value"><?php echo formatCredits($user['available_credits']); ?></div>
            </div>
        </div>
        <div class="profile-ribbon-stat glass-amber">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <div>
                <div class="profile-ribbon-label">Locked</div>
                <div class="profile-ribbon-value"><?php echo formatCredits($user['locked_credits']); ?></div>
            </div>
        </div>
        <div class="profile-ribbon-stat glass-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline></svg>
            <div>
                <div class="profile-ribbon-label">Services</div>
                <div class="profile-ribbon-value"><?php echo $servicesCount; ?></div>
            </div>
        </div>
        <div class="profile-ribbon-stat glass-info">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <div>
                <div class="profile-ribbon-label">Completed</div>
                <div class="profile-ribbon-value"><?php echo $completedCount; ?></div>
            </div>
        </div>
        <div class="profile-ribbon-stat glass-purple">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            <div>
                <div class="profile-ribbon-label">Rating</div>
                <div class="profile-ribbon-value"><?php echo $score > 0 ? $score : '0.0'; ?></div>
            </div>
        </div>
    </div>

    <!-- Two-Column Content -->
    <div class="profile-grid">
        <div class="profile-left">
            <!-- About -->
            <div class="card profile-card">
                <div class="profile-card-header">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <h3>About</h3>
                </div>
                <p class="profile-bio"><?php echo nl2br(htmlspecialchars($user['bio'] ?: 'No bio added yet.')); ?></p>
            </div>

            <!-- Availability -->
            <div class="card profile-card">
                <div class="profile-card-header">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <h3>Availability</h3>
                </div>
                <p class="profile-bio"><?php echo nl2br(htmlspecialchars($user['availability'] ?: 'No availability set.')); ?></p>
            </div>

            <!-- Services Preview -->
            <div class="card profile-card">
                <div class="profile-card-header">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline></svg>
                    <h3>Services</h3>
                </div>
                <?php if (empty($recentServices)): ?>
                    <div class="profile-empty">
                        <p class="text-muted">No services listed yet.</p>
                        <?php if ($is_own_profile): ?>
                            <a href="<?php echo APP_URL; ?>/pages/services/add.php" class="btn btn-sm btn-primary">Create Service</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="profile-service-list">
                        <?php foreach ($recentServices as $svc): ?>
                            <a href="<?php echo APP_URL; ?>/pages/services/view.php?id=<?php echo $svc['id']; ?>" class="profile-service-item">
                                <div class="profile-service-dot status-<?php echo strtolower($svc['availability_status']); ?>"></div>
                                <div class="profile-service-info">
                                    <div class="profile-service-title"><?php echo htmlspecialchars($svc['title']); ?></div>
                                    <div class="profile-service-meta"><?php echo htmlspecialchars($svc['category_name']); ?></div>
                                </div>
                                <div class="profile-service-rate"><?php echo formatCredits($svc['credit_rate']); ?></div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?php echo APP_URL; ?>/pages/services/directory.php" class="btn btn-sm btn-outline" style="width: 100%; margin-top: var(--spacing-md);">View All Services</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="profile-right">
            <!-- Skills -->
            <div class="card profile-card">
                <div class="profile-card-header">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>
                    <h3>Skills</h3>
                </div>
                <?php if (!empty($skills_array)): ?>
                    <div class="tag-list">
                        <?php foreach ($skills_array as $skill): ?>
                            <span class="tag"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No skills listed yet.</p>
                <?php endif; ?>
            </div>

            <!-- Contact & Trust -->
            <div class="card profile-card">
                <div class="profile-card-header" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <h3 style="margin: 0;">Contact & Trust</h3>
                    </div>
                    <?php if ($is_own_profile): ?>
                        <a href="<?php echo APP_URL; ?>/pages/verification.php" class="btn btn-outline btn-xs" style="padding: var(--spacing-xxs) var(--spacing-xs); font-size: var(--text-xs);">Verify</a>
                    <?php endif; ?>
                </div>
                <div class="profile-contact-item" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xs);">
                    <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <?php if ($user['email_verified']): ?>
                        <span style="color: var(--color-success); font-size: var(--text-xs); font-weight: var(--font-semibold); display: flex; align-items: center; gap: 2px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> Verified
                        </span>
                    <?php else: ?>
                        <span style="color: var(--color-warning); font-size: var(--text-xs); font-weight: var(--font-semibold);">Unverified</span>
                    <?php endif; ?>
                </div>

                <div class="profile-contact-item" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: var(--spacing-xs);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                        <span><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'No phone added'; ?></span>
                    </div>
                    <?php if (!empty($user['phone'])): ?>
                        <?php if ($user['phone_verified']): ?>
                            <span style="color: var(--color-success); font-size: var(--text-xs); font-weight: var(--font-semibold); display: flex; align-items: center; gap: 2px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg> Verified
                            </span>
                        <?php else: ?>
                            <span style="color: var(--color-warning); font-size: var(--text-xs); font-weight: var(--font-semibold);">Unverified</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews -->
    <div class="card profile-reviews" style="margin-top: var(--spacing-xl);">
        <div class="profile-reviews-header">
            <h3>Reviews</h3>
            <?php if ($count > 0): ?>
                <div class="profile-reviews-score">
                    <span class="profile-reviews-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="<?php echo $i <= round($score) ? '#fbbf24' : 'none'; ?>" stroke="<?php echo $i <= round($score) ? '#fbbf24' : 'currentColor'; ?>" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        <?php endfor; ?>
                    </span>
                    <span class="profile-reviews-count"><?php echo $score; ?> &middot; <?php echo $count; ?> review<?php echo $count !== 1 ? 's' : ''; ?></span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($count > 0): ?>
            <div class="profile-review-list">
                <?php foreach ($reviews as $r): ?>
                    <div class="profile-review-card">
                        <div class="profile-review-top">
                            <img src="<?php echo APP_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($r['profile_image'] ?: 'default-avatar.png'); ?>"
                                 alt="<?php echo htmlspecialchars($r['reviewer_name']); ?>"
                                 class="profile-review-avatar"
                                 onerror="this.src='<?php echo APP_URL; ?>/assets/images/default-avatar.png'">
                            <div class="profile-review-meta">
                                <div class="profile-review-name"><?php echo htmlspecialchars($r['reviewer_name']); ?></div>
                                <div class="profile-review-service"><?php echo htmlspecialchars($r['service_title']); ?></div>
                            </div>
                            <div class="profile-review-stars-small">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="<?php echo $i <= $r['rating_value'] ? '#fbbf24' : 'none'; ?>" stroke="<?php echo $i <= $r['rating_value'] ? '#fbbf24' : 'currentColor'; ?>" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="profile-review-text"><?php echo nl2br(htmlspecialchars($r['review_text'] ?: 'No written comment.')); ?></p>
                        <div class="profile-review-date"><?php echo formatDate($r['created_at']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="profile-empty">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <p class="text-muted">No reviews yet. Complete a service to leave feedback.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>