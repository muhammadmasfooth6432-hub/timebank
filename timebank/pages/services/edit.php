<?php
// pages/services/edit.php
// Edit existing service

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

requireLogin();

$pdo = getDatabaseConnection();
$user_id = getCurrentUserId();
$service_id = (int)($_GET['id'] ?? 0);

// Verify ownership
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND user_id = ?");
$stmt->execute([$service_id, $user_id]);
$service = $stmt->fetch();

if (!$service) {
    die('Service not found or access denied.');
}

// Fetch categories
$cat_stmt = $pdo->query("SELECT id, name, credit_per_hour FROM categories ORDER BY name");
$categories = $cat_stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) 0;">
    <h2 style="margin-bottom: var(--spacing-lg);">Edit Service</h2>

    <form method="POST" action="<?php echo APP_URL; ?>/actions/update_service.php">
        <?php include __DIR__ . '/../../includes/csrf.php'; echo csrfField(); ?>
        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">

        <div class="edit-grid">
            <div class="card">
                <div class="form-group">
                    <label for="title">Service Title</label>
                    <input type="text" id="title" name="title" class="form-control" required value="<?php echo htmlspecialchars($service['title']); ?>">
                </div>

                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control" required disabled>
                        <option value="<?php echo $service['category_id']; ?>" selected>
                            <?php
                            $cat_stmt2 = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
                            $cat_stmt2->execute([$service['category_id']]);
                            echo htmlspecialchars($cat_stmt2->fetchColumn());
                            ?>
                        </option>
                    </select>
                    <small class="text-muted">Category cannot be changed after creation.</small>
                </div>

                <div class="form-group">
                    <label for="availability_status">Availability</label>
                    <select id="availability_status" name="availability_status" class="form-control" required>
                        <option value="available" <?php echo $service['availability_status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="busy" <?php echo $service['availability_status'] === 'busy' ? 'selected' : ''; ?>>Busy</option>
                        <option value="unavailable" <?php echo $service['availability_status'] === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                    </select>
                </div>
            </div>

            <div class="card">
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="6" required><?php echo htmlspecialchars($service['description']); ?></textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-between" style="margin-top: var(--spacing-xl);">
            <a href="<?php echo APP_URL; ?>/pages/services/directory.php" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Service</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>