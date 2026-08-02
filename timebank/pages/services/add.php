<?php
// pages/services/add.php
// Create new service listing

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../includes/auth_check.php';

requireLogin();

$pdo = getDatabaseConnection();
$user_id = getCurrentUserId();

// Fetch categories with credit rates
$stmt = $pdo->query("SELECT id, name, credit_per_hour FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Preserve form data on error
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

include __DIR__ . '/../../includes/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) 0;">
    <h2 style="margin-bottom: var(--spacing-lg);">Offer a New Service</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo APP_URL; ?>/actions/add_service.php">
        <?php include __DIR__ . '/../../includes/csrf.php'; echo csrfField(); ?>

        <div class="edit-grid">
            <div class="card">
                <div class="form-group">
                    <label for="title">Service Title</label>
                    <input type="text" id="title" name="title" class="form-control" required 
                           value="<?php echo htmlspecialchars($form_data['title'] ?? ''); ?>"
                           placeholder="e.g. Web Development, Home Tutoring, Logo Design">
                </div>

                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    data-rate="<?php echo $cat['credit_per_hour']; ?>"
                                    <?php echo ($form_data['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="availability_status">Availability</label>
                    <select id="availability_status" name="availability_status" class="form-control" required>
                        <option value="available" <?php echo ($form_data['availability_status'] ?? 'available') === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="busy" <?php echo ($form_data['availability_status'] ?? '') === 'busy' ? 'selected' : ''; ?>>Busy</option>
                        <option value="unavailable" <?php echo ($form_data['availability_status'] ?? '') === 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                    </select>
                </div>
            </div>

            <div class="card">
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="6" required
                              placeholder="Describe what you offer, your experience, and what clients can expect..."><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="credit_rate">Credit Rate (per hour)</label>
                    <input type="text" id="credit_rate" name="credit_rate" class="form-control readonly-input" 
                           readonly value="<?php echo $form_data['credit_rate'] ?? '0.00'; ?>" 
                           placeholder="Auto-filled based on category">
                    <small class="text-muted">Credit rates are determined by category and cannot be manually changed.</small>
                </div>
            </div>
        </div>

        <div class="flex justify-between" style="margin-top: var(--spacing-xl);">
            <a href="<?php echo APP_URL; ?>/pages/services/directory.php" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Service</button>
        </div>
    </form>
</div>

<script>
    document.getElementById('category_id')?.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const rate = selected.dataset.rate || '0.00';
        document.getElementById('credit_rate').value = parseFloat(rate).toFixed(2);
    });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>