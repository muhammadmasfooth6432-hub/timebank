<?php
// pages/notifications/all.php
// Beautiful modern layout for viewing and managing all notifications

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

requireLogin();

$pdo = getDatabaseConnection();
$user_id = getCurrentUserId();
$current_page = 'notifications';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fetch total count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
$countStmt->execute([$user_id]);
$total_notifications = $countStmt->fetchColumn();
$total_pages = ceil($total_notifications / $limit);

// Fetch notifications
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$notifications = $stmt->fetchAll();

// Count unread notifications
$unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$unreadStmt->execute([$user_id]);
$unread_count = (int)$unreadStmt->fetchColumn();

include __DIR__ . '/../../includes/header.php';
?>

<style>
/* Notification Page Custom Styles */
.noti-container {
    padding: var(--spacing-xl) 0;
    max-width: 800px;
    margin: 0 auto;
}

.noti-header-card {
    position: relative;
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg) var(--spacing-xl);
    margin-bottom: var(--spacing-lg);
    overflow: hidden;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--shadow-lg);
}

.noti-header-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -20%;
    width: 60%;
    height: 200%;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
    pointer-events: none;
}

.noti-title-area h2 {
    margin: 0 0 var(--spacing-xxs) 0;
    background: var(--color-primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.noti-title-area h2::after {
    display: none;
}

.noti-title-area p {
    margin: 0;
    font-size: var(--text-sm);
    color: var(--color-text-muted);
}

.noti-badge-count {
    display: inline-flex;
    align-items: center;
    background: rgba(99, 102, 241, 0.2);
    border: 1px solid rgba(99, 102, 241, 0.3);
    color: var(--color-primary-light);
    font-size: var(--text-xs);
    font-weight: var(--font-semibold);
    padding: 2px var(--spacing-sm);
    border-radius: var(--radius-full);
    margin-left: var(--spacing-xs);
}

.noti-actions-bar {
    display: flex;
    gap: var(--spacing-sm);
}

.noti-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-xl);
}

.page-notification-card {
    display: flex;
    gap: var(--spacing-md);
    background: var(--color-bg-card);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--spacing-md) var(--spacing-lg);
    transition: all var(--transition-base);
    text-decoration: none;
    color: inherit;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.page-notification-card::after {
    display: none !important;
}

.page-notification-card:hover {
    transform: translateY(-2px);
    border-color: var(--color-primary-light);
    background: var(--color-bg-secondary);
    box-shadow: var(--shadow-md), 0 0 15px rgba(99, 102, 241, 0.1);
}

.page-notification-card.unread {
    border-left: 4px solid var(--color-primary);
    background: rgba(99, 102, 241, 0.04);
}

.page-notification-card.unread::before {
    content: '';
    position: absolute;
    top: var(--spacing-sm);
    right: var(--spacing-sm);
    width: 8px;
    height: 8px;
    background: var(--color-primary);
    border-radius: var(--radius-full);
    box-shadow: 0 0 8px var(--color-primary);
}

.noti-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: var(--radius-full);
    flex-shrink: 0;
}

/* Colors for Notification Types */
.type-request { background: rgba(14, 165, 233, 0.12); color: var(--color-secondary); border: 1px solid rgba(14, 165, 233, 0.2); }
.type-approval { background: rgba(16, 185, 129, 0.12); color: var(--color-success); border: 1px solid rgba(16, 185, 129, 0.2); }
.type-rejection { background: rgba(239, 68, 68, 0.12); color: var(--color-danger); border: 1px solid rgba(239, 68, 68, 0.2); }
.type-completion { background: rgba(139, 92, 246, 0.12); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.2); }
.type-credit { background: rgba(16, 185, 129, 0.12); color: var(--color-success); border: 1px solid rgba(16, 185, 129, 0.2); }
.type-review { background: rgba(245, 158, 11, 0.12); color: var(--color-warning); border: 1px solid rgba(245, 158, 11, 0.2); }
.type-system { background: rgba(244, 114, 182, 0.12); color: var(--color-accent); border: 1px solid rgba(244, 114, 182, 0.2); }

.noti-body {
    flex-grow: 1;
}

.noti-body-title {
    font-size: var(--text-base);
    font-weight: var(--font-semibold);
    margin-bottom: var(--spacing-xxs);
    color: var(--color-text-primary);
}

.noti-body-msg {
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
    margin-bottom: var(--spacing-xs);
    line-height: 1.5;
}

.noti-body-time {
    font-size: var(--text-xs);
    color: var(--color-text-muted);
}

.noti-empty {
    text-align: center;
    padding: var(--spacing-xxxl) var(--spacing-xl);
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
}

.noti-empty-icon {
    color: var(--color-text-muted);
    margin-bottom: var(--spacing-md);
    opacity: 0.7;
}

.noti-empty h3 {
    margin-bottom: var(--spacing-xs);
}

/* Pagination Styling */
.noti-pagination {
    display: flex;
    justify-content: center;
    gap: var(--spacing-xs);
    margin-top: var(--spacing-lg);
}

.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: var(--radius-sm);
    background: var(--color-bg-secondary);
    border: 1px solid var(--color-border);
    color: var(--color-text-secondary);
    font-weight: var(--font-semibold);
    transition: all var(--transition-fast);
}

.page-btn:hover {
    border-color: var(--color-primary);
    color: var(--color-text-primary);
    background: var(--color-bg-tertiary);
}

.page-btn.active {
    background: var(--color-primary-gradient);
    border-color: transparent;
    color: white;
}

.page-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}
</style>

<div class="container noti-container">
    <!-- Page Header -->
    <div class="noti-header-card">
        <div class="noti-title-area">
            <h2>
                Notification Center
                <?php if ($unread_count > 0): ?>
                    <span class="noti-badge-count" id="pageUnreadBadge"><?php echo $unread_count; ?> new</span>
                <?php endif; ?>
            </h2>
            <p>Stay updated on requests, credits, and community services.</p>
        </div>
        <div class="noti-actions-bar">
            <?php if ($unread_count > 0): ?>
                <button class="btn btn-outline btn-sm" id="pageMarkAllBtn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: var(--spacing-xxs);"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Mark all read
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Notification List -->
    <?php if (empty($notifications)): ?>
        <div class="noti-empty">
            <div class="noti-empty-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </div>
            <h3>No notifications</h3>
            <p class="text-muted">You're all caught up! When things happen, you'll see them here.</p>
        </div>
    <?php else: ?>
        <div class="noti-list">
            <?php foreach ($notifications as $n):
                // Resolve icons and colors based on notification type
                $typeClass = 'type-' . htmlspecialchars($n['type']);
                $iconSvg = '';
                
                switch ($n['type']) {
                    case 'request':
                        $iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';
                        break;
                    case 'approval':
                        $iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                        break;
                    case 'rejection':
                        $iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
                        break;
                    case 'completion':
                        $iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                        break;
                    case 'credit':
                        $iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>';
                        break;
                    case 'review':
                        $iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
                        break;
                    case 'system':
                    default:
                        $iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
                        break;
                }

                // Resolve click link
                $link = '#';
                if ($n['related_entity_id']) {
                    if (in_array($n['type'], ['request', 'approval', 'rejection', 'completion', 'review'])) {
                        // Check if user is requester or provider for this request
                        $reqCheck = $pdo->prepare("SELECT requester_id, provider_id FROM service_requests WHERE id = ?");
                        $reqCheck->execute([$n['related_entity_id']]);
                        $requestData = $reqCheck->fetch();
                        if ($requestData) {
                            if ($requestData['requester_id'] == $user_id) {
                                $link = APP_URL . "/pages/requests/history.php";
                            } else if ($requestData['provider_id'] == $user_id) {
                                $link = APP_URL . "/pages/requests/manage.php";
                            }
                        }
                    } else if ($n['type'] === 'system') {
                        $link = APP_URL . "/pages/services/view.php?id=" . $n['related_entity_id'];
                    }
                }
            ?>
                <a href="<?php echo htmlspecialchars($link); ?>" 
                   class="page-notification-card <?php echo $n['is_read'] == 0 ? 'unread' : ''; ?>" 
                   data-id="<?php echo $n['id']; ?>">
                    
                    <div class="noti-icon-wrapper <?php echo $typeClass; ?>">
                        <?php echo $iconSvg; ?>
                    </div>
                    
                    <div class="noti-body">
                        <div class="noti-body-title"><?php echo htmlspecialchars($n['title']); ?></div>
                        <div class="noti-body-msg"><?php echo htmlspecialchars($n['message']); ?></div>
                        <div class="noti-body-time"><?php echo formatDate($n['created_at']); ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="noti-pagination">
                <a href="?page=<?php echo $page - 1; ?>" class="page-btn <?php echo $page <= 1 ? 'disabled' : ''; ?>" aria-label="Previous page">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </a>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="page-btn <?php echo $page === $i ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <a href="?page=<?php echo $page + 1; ?>" class="page-btn <?php echo $page >= $total_pages ? 'disabled' : ''; ?>" aria-label="Next page">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pageMarkAllBtn = document.getElementById('pageMarkAllBtn');
    const pageUnreadBadge = document.getElementById('pageUnreadBadge');
    
    if (pageMarkAllBtn) {
        pageMarkAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            fetch((window.APP_URL || '') + '/actions/notifications/mark_read.php', {
                method: 'POST'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.page-notification-card.unread').forEach(card => {
                        card.classList.remove('unread');
                    });
                    
                    // Update dropdown bell badge if visible
                    const badge = document.getElementById('notificationBadge');
                    if (badge) {
                        badge.textContent = '0';
                        badge.classList.add('empty');
                    }
                    
                    if (pageUnreadBadge) pageUnreadBadge.remove();
                    pageMarkAllBtn.remove();
                }
            })
            .catch(err => console.error('Mark all read failed:', err));
        });
    }

    // Mark single notification as read on click/redirect
    document.querySelectorAll('.page-notification-card.unread').forEach(card => {
        card.addEventListener('click', function(e) {
            // Stop if it's already read
            if (!this.classList.contains('unread')) return;

            const id = this.dataset.id;
            const formData = new FormData();
            formData.append('notification_id', id);

            // Fetch request (we don't preventDefault so user is redirected to the actual href link)
            fetch((window.APP_URL || '') + '/actions/notifications/mark_read.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.classList.remove('unread');
                    
                    // Update header bell count
                    const badge = document.getElementById('notificationBadge');
                    if (badge) {
                        let current = parseInt(badge.textContent) || 0;
                        let newCount = Math.max(0, current - 1);
                        badge.textContent = newCount > 0 ? (newCount > 99 ? '99+' : newCount) : '0';
                        badge.classList.toggle('empty', newCount === 0);
                    }
                    
                    // Update local unread count on page
                    if (pageUnreadBadge) {
                        let currentUnread = parseInt(pageUnreadBadge.textContent) || 0;
                        let newUnread = Math.max(0, currentUnread - 1);
                        if (newUnread === 0) {
                            pageUnreadBadge.remove();
                            if (pageMarkAllBtn) pageMarkAllBtn.remove();
                        } else {
                            pageUnreadBadge.textContent = newUnread + ' new';
                        }
                    }
                }
            })
            .catch(err => console.error('Mark read failed:', err));
        });
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
