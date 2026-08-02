<?php
// includes/sidebar.php
// Modern admin navigation sidebar

$current_admin_page = basename($_SERVER['PHP_SELF']);

$nav_items = [
    'Overview' => [
        ['label' => 'Dashboard', 'url' => APP_URL . '/admin/dashboard.php', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>', 'page' => 'dashboard.php'],
        ['label' => 'Transactions', 'url' => APP_URL . '/admin/transactions.php', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>', 'page' => 'transactions.php'],
    ],
    'Management' => [
        ['label' => 'Users', 'url' => APP_URL . '/admin/users.php', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>', 'page' => 'users.php'],
        ['label' => 'Categories', 'url' => APP_URL . '/admin/categories.php', 'icon' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>', 'page' => 'categories.php'],
    ],
];
?>
<aside class="admin-sidebar">
    <div class="admin-sidebar-brand">
        <div class="admin-brand-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
        <span class="admin-brand-text">TimeBank</span>
    </div>

    <nav class="admin-sidebar-nav">
        <?php $i = 0; foreach ($nav_items as $section => $items): ?>
            <div class="admin-sidebar-section" style="animation-delay: <?php echo 0.05 * $i; ?>s">
                <div class="admin-sidebar-section-title"><?php echo $section; ?></div>
                <?php foreach ($items as $item): 
                    $is_active = $current_admin_page === $item['page'];
                ?>
                    <a href="<?php echo $item['url']; ?>" class="admin-sidebar-link <?php echo $is_active ? 'active' : ''; ?>">
                        <span class="admin-sidebar-link-icon"><?php echo $item['icon']; ?></span>
                        <span class="admin-sidebar-link-text"><?php echo $item['label']; ?></span>
                        <?php if ($is_active): ?>
                            <span class="admin-sidebar-link-indicator"></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php $i++; endforeach; ?>
    </nav>

    <div class="admin-sidebar-footer">
        <?php if (isset($_SESSION['user_id']) && isset($_SESSION['user_name'])): ?>
            <div class="admin-sidebar-user">
                <img src="<?php echo APP_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($_SESSION['profile_image'] ?? 'default-avatar.png'); ?>"
                     alt=""
                     class="admin-sidebar-avatar"
                     onerror="this.src='<?php echo APP_URL; ?>/assets/images/default-avatar.png'">
                <div class="admin-sidebar-user-info">
                    <div class="admin-sidebar-user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                    <div class="admin-sidebar-user-role">Administrator</div>
                </div>
            </div>
        <?php endif; ?>
        <a href="<?php echo APP_URL; ?>/index.php" class="admin-sidebar-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Back to Site
        </a>
    </div>
</aside>