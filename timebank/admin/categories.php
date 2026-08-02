<?php
// admin/categories.php
// Modern category & credit rate management

require_once __DIR__ . '/../includes/admin_auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

$pdo = getDatabaseConnection();
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $name = trim($_POST['name'] ?? '');
    $rate = (float)($_POST['credit_per_hour'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    $id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

    if (empty($name) || $rate <= 0) {
        $error = "Category name and valid credit rate required.";
    } elseif (isset($_POST['add_category'])) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description, credit_per_hour) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $desc, $rate])) $success = "Category added.";
    } elseif (isset($_POST['update_category']) && $id) {
        $stmt = $pdo->prepare("UPDATE categories SET name=?, description=?, credit_per_hour=? WHERE id=?");
        if ($stmt->execute([$name, $desc, $rate, $id])) $success = "Category updated.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $cid = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if ($stmt->execute([$cid])) { $_SESSION['success'] = "Category deleted."; redirect($_SERVER['PHP_SELF']); }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Stats
$catCount = count($categories);
$avgRate = $catCount > 0 ? round(array_sum(array_column($categories, 'credit_per_hour')) / $catCount, 1) : 0;

$catColors = ['cat-blue', 'cat-green', 'cat-purple', 'cat-amber', 'cat-pink', 'cat-orange'];
$catIcons = [
    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>',
    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>',
    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M2 12h20"></path><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>',
    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline></svg>',
    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path></svg>',
];

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="admin-content">
        <!-- Hero Header -->
        <div class="admin-page-header">
            <div class="admin-page-header-glow"></div>
            <div class="admin-page-header-inner">
                <div>
                    <h2 style="margin-bottom: var(--spacing-xs);">Category Management</h2>
                    <p class="text-muted" style="margin:0;">Organize services and set credit rates.</p>
                </div>
                <div class="req-header-stats">
                    <span class="req-stat-pill"><span><?php echo $catCount; ?></span> Categories</span>
                    <span class="req-stat-pill req-pill-info"><span><?php echo formatCredits($avgRate); ?></span> Avg Rate</span>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($success): ?>
            <div class="admin-toast toast-success">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="admin-toast toast-error">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <div class="cat-layout">
            <!-- Form Card -->
            <div class="card cat-form-card">
                <div class="cat-form-header">
                    <div class="cat-form-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"></path></svg>
                    </div>
                    <h3 style="margin:0;">Add Category</h3>
                </div>
                <form method="POST">
                    <?php echo csrfField(); ?>
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Web Design" required>
                    </div>
                    <div class="form-group">
                        <label>Credits per Hour</label>
                        <input type="number" step="0.01" name="credit_per_hour" class="form-control" placeholder="e.g. 10" min="0.1" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description..."></textarea>
                    </div>
                    <input type="hidden" name="category_id" value="0">
                    <button type="submit" name="add_category" class="btn btn-primary" style="width:100%;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align: middle; margin-right: 6px;"><path d="M12 5v14M5 12h14"></path></svg>
                        Add Category
                    </button>
                </form>
            </div>

            <!-- Category Grid -->
            <div class="cat-grid">
                <?php foreach ($categories as $index => $c):
                    $colorClass = $catColors[$index % count($catColors)];
                    $icon = $catIcons[$index % count($catIcons)];
                ?>
                    <div class="cat-card <?php echo $colorClass; ?>" style="animation-delay: <?php echo 0.05 * $index; ?>s">
                        <div class="cat-card-glow"></div>
                        <div class="cat-card-body">
                            <div class="cat-card-top">
                                <div class="cat-card-icon"><?php echo $icon; ?></div>
                                <a href="?delete=<?php echo $c['id']; ?>" class="cat-delete-btn" onclick="return confirm('Delete this category? Existing services will lose category mapping.');" title="Delete">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </a>
                            </div>
                            <div class="cat-card-name"><?php echo htmlspecialchars($c['name']); ?></div>
                            <div class="cat-card-rate"><?php echo formatCredits($c['credit_per_hour']); ?> <span>credits/hr</span></div>
                            <?php if (!empty($c['description'])): ?>
                                <div class="cat-card-desc"><?php echo htmlspecialchars($c['description']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>