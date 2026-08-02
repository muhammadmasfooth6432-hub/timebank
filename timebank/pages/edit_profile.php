<?php
// pages/edit_profile.php
// Edit user profile form

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();

$pdo = getDatabaseConnection();
$user_id = getCurrentUserId();

// Fetch current user data
$stmt = $pdo->prepare("SELECT name, email, phone, profile_image, bio, skills, availability FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

include __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) 0;">
    <h2 style="margin-bottom: var(--spacing-lg);">Edit Profile</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo APP_URL; ?>/actions/update_profile.php" enctype="multipart/form-data">
        <?php include __DIR__ . '/../includes/csrf.php'; echo csrfField(); ?>

        <div class="edit-grid">
            <div>
                <div class="form-group">
                    <label for="profile_image">Profile Picture</label>
                    <div class="avatar-upload-wrapper">
                        <img src="<?php echo APP_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" 
                             alt="Current Avatar" id="avatar-preview"
                             onerror="this.src='<?php echo APP_URL; ?>/assets/images/default-avatar.png'">
                        <div>
                            <label for="avatar-input" class="file-input-label">Choose Image</label>
                            <input type="file" id="avatar-input" name="profile_image" accept="image/jpeg,image/png,image/gif">
                            <small class="text-muted" style="display: block; margin-top: var(--spacing-xxs);">Max 5MB. JPG, PNG, GIF</small>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" required value="<?php echo htmlspecialchars($user['name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user['email']); ?>">
                    <small class="text-muted">This cannot be changed once updated.</small>
                </div>

                <div class="form-group">
                    <label for="phone">Mobile Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+1234567890">
                    <small class="text-muted">Changing this will require mobile re-verification.</small>
                </div>

                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" class="form-control" rows="4" placeholder="Tell the community about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <div class="edit-grid" style="margin-top: var(--spacing-xl);">
            <div class="card">
                <div class="form-group">
                    <label for="skills">Skills</label>
                    <textarea id="skills" name="skills" class="form-control" rows="4" placeholder="e.g. Web Development, Cooking, Spanish Tutoring (comma separated)"><?php echo htmlspecialchars($user['skills'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="card">
                <div class="form-group">
                    <label for="availability">Availability</label>
                    <textarea id="availability" name="availability" class="form-control" rows="4" placeholder="e.g. Weekdays after 5 PM, Weekends 9 AM - 3 PM"><?php echo htmlspecialchars($user['availability'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-between" style="margin-top: var(--spacing-xl);">
            <a href="<?php echo APP_URL; ?>/pages/profile.php" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>

<script>
    // Image preview functionality
    document.getElementById('avatar-input')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('avatar-preview').src = event.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>