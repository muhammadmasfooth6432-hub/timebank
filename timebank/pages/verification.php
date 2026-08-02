<?php
// pages/verification.php
// Verification Center for email and phone connectivity

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();

$pdo = getDatabaseConnection();
$user_id = getCurrentUserId();

// Fetch latest user data
$stmt = $pdo->prepare("SELECT email, phone, email_verified, phone_verified, email_verification_code, phone_verification_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

include __DIR__ . '/../includes/header.php';
?>

<style>
.verification-container {
    max-width: 900px;
    margin: 0 auto;
    padding: var(--spacing-xl) var(--spacing-md);
}

.verification-header {
    margin-bottom: var(--spacing-xl);
    text-align: center;
}

.verification-header h2 {
    font-size: var(--text-3xl);
    font-family: 'Outfit', sans-serif;
    background: var(--color-primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: var(--spacing-xs);
}

.verification-header p {
    color: var(--color-text-secondary);
}

.verification-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--spacing-lg);
}

@media (min-width: 768px) {
    .verification-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.v-card {
    background: var(--color-bg-card);
    backdrop-filter: var(--blur-glass);
    -webkit-backdrop-filter: var(--blur-glass);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform var(--transition-base), box-shadow var(--transition-base);
    position: relative;
    overflow: hidden;
}

.v-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--color-bg-tertiary);
}

.v-card.verified::before {
    background: var(--color-success);
}

.v-card.unverified::before {
    background: var(--color-warning);
}

.v-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.v-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--spacing-md);
    background: rgba(99, 102, 241, 0.1);
    color: var(--color-primary);
}

.v-card.verified .v-icon-wrapper {
    background: rgba(16, 185, 129, 0.1);
    color: var(--color-success);
}

.v-card.unverified .v-icon-wrapper {
    background: rgba(245, 158, 11, 0.1);
    color: var(--color-warning);
}

.v-title-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-sm);
}

.v-title {
    font-size: var(--text-xl);
    font-weight: var(--font-semibold);
    color: var(--color-text-primary);
    margin: 0;
}

.v-status-badge {
    padding: var(--spacing-xxs) var(--spacing-sm);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: var(--font-semibold);
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-xxs);
}

.v-status-badge.verified {
    background: rgba(16, 185, 129, 0.15);
    color: var(--color-success);
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.v-status-badge.unverified {
    background: rgba(245, 158, 11, 0.15);
    color: var(--color-warning);
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.v-desc {
    color: var(--color-text-secondary);
    font-size: var(--text-sm);
    margin-bottom: var(--spacing-lg);
    line-height: 1.5;
    flex-grow: 1;
}

.v-field-display {
    background: rgba(15, 23, 42, 0.4);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    padding: var(--spacing-sm) var(--spacing-md);
    margin-bottom: var(--spacing-lg);
    font-family: var(--font-mono);
    color: var(--color-text-primary);
    word-break: break-all;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.v-field-change-btn {
    font-size: var(--text-xs);
    color: var(--color-primary);
    text-decoration: none;
    font-family: var(--font-family);
    font-weight: var(--font-medium);
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
}

.v-field-change-btn:hover {
    text-decoration: underline;
}

.v-action-section {
    border-top: 1px solid var(--color-border);
    padding-top: var(--spacing-lg);
    margin-top: auto;
}

.code-input-group {
    display: flex;
    gap: var(--spacing-xs);
    margin-top: var(--spacing-sm);
}

.code-input-group input {
    flex-grow: 1;
}

.text-success-muted {
    color: var(--color-success);
    font-size: var(--text-sm);
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    margin-top: var(--spacing-md);
}

.phone-edit-form {
    margin-bottom: var(--spacing-lg);
}
</style>

<div class="verification-container">
    <div class="verification-header">
        <h2>Verification Center</h2>
        <p>Confirm your main identity connections to build trust in the Time Bank community</p>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="verification-grid">
        <!-- EMAIL CARD -->
        <div class="v-card <?php echo $user['email_verified'] ? 'verified' : 'unverified'; ?>">
            <div>
                <div class="v-icon-wrapper">
                    <?php if ($user['email_verified']): ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <?php else: ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <?php endif; ?>
                </div>
                
                <div class="v-title-row">
                    <h3 class="v-title">Main Email Address</h3>
                    <span class="v-status-badge <?php echo $user['email_verified'] ? 'verified' : 'unverified'; ?>">
                        <?php if ($user['email_verified']): ?>
                            ✓ Verified
                        <?php else: ?>
                            ⚠ Unverified
                        <?php endif; ?>
                    </span>
                </div>
                
                <p class="v-desc">Verifying your email ensures you receive service requests, transaction updates, and platform communications reliably.</p>
                
                <div class="v-field-display">
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
            </div>

            <div class="v-action-section">
                <?php if ($user['email_verified']): ?>
                    <div class="text-success-muted">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Verified main email address connection</span>
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?php echo APP_URL; ?>/actions/verification_send.php">
                        <?php include __DIR__ . '/../includes/csrf.php'; echo csrfField(); ?>
                        <input type="hidden" name="type" value="email">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Send Verification Code</button>
                    </form>

                    <?php if (!empty($user['email_verification_code'])): ?>
                        <form method="POST" action="<?php echo APP_URL; ?>/actions/verification_check.php" style="margin-top: var(--spacing-md);">
                            <?php include __DIR__ . '/../includes/csrf.php'; echo csrfField(); ?>
                            <input type="hidden" name="type" value="email">
                            <label for="email_code" class="text-muted" style="font-size: var(--text-xs);">Enter Code sent to Email</label>
                            <div class="code-input-group">
                                <input type="text" id="email_code" name="code" class="form-control" placeholder="6-digit code" required pattern="\d{6}" maxlength="6">
                                <button type="submit" class="btn btn-success">Verify</button>
                            </div>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- PHONE CARD -->
        <div class="v-card <?php echo $user['phone_verified'] ? 'verified' : 'unverified'; ?>">
            <div>
                <div class="v-icon-wrapper">
                    <?php if ($user['phone_verified']): ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <?php else: ?>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                    <?php endif; ?>
                </div>

                <div class="v-title-row">
                    <h3 class="v-title">Mobile Connectivity</h3>
                    <span class="v-status-badge <?php echo $user['phone_verified'] ? 'verified' : 'unverified'; ?>">
                        <?php if ($user['phone_verified']): ?>
                            ✓ Verified
                        <?php else: ?>
                            ⚠ Unverified
                        <?php endif; ?>
                    </span>
                </div>

                <p class="v-desc">Verifying mobile connectivity validates your device contact info, enhancing community trust for secure face-to-face exchanges.</p>

                <!-- Phone number display and edit toggle -->
                <div id="phone-display-container" style="display: <?php echo empty($user['phone']) ? 'none' : 'block'; ?>;">
                    <div class="v-field-display">
                        <span id="phone-text"><?php echo htmlspecialchars($user['phone'] ?? ''); ?></span>
                        <?php if (!$user['phone_verified']): ?>
                            <button type="button" class="v-field-change-btn" id="edit-phone-btn">Change</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="phone-input-container" style="display: <?php echo empty($user['phone']) ? 'block' : 'none'; ?>;" class="phone-edit-form">
                    <form method="POST" action="<?php echo APP_URL; ?>/actions/verification_send.php" id="phone-number-form">
                        <?php include __DIR__ . '/../includes/csrf.php'; echo csrfField(); ?>
                        <input type="hidden" name="type" value="phone">
                        <div class="form-group" style="margin-bottom: var(--spacing-sm);">
                            <label for="phone" class="text-muted" style="font-size: var(--text-xs);">Mobile Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" placeholder="+1234567890" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <?php echo empty($user['phone']) ? 'Add & Verify Mobile' : 'Verify Mobile'; ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="v-action-section">
                <?php if ($user['phone_verified']): ?>
                    <div class="text-success-muted">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Verified mobile device connection</span>
                    </div>
                <?php else: ?>
                    <?php if (!empty($user['phone']) && empty($user['phone_verification_code'])): ?>
                        <!-- If phone is set but code not sent yet, show a button to send code to existing number -->
                        <form method="POST" action="<?php echo APP_URL; ?>/actions/verification_send.php">
                            <?php include __DIR__ . '/../includes/csrf.php'; echo csrfField(); ?>
                            <input type="hidden" name="type" value="phone">
                            <input type="hidden" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Send SMS Verification Code</button>
                        </form>
                    <?php endif; ?>

                    <?php if (!empty($user['phone_verification_code'])): ?>
                        <!-- Code pending entry form -->
                        <form method="POST" action="<?php echo APP_URL; ?>/actions/verification_check.php">
                            <?php include __DIR__ . '/../includes/csrf.php'; echo csrfField(); ?>
                            <input type="hidden" name="type" value="phone">
                            <label for="phone_code" class="text-muted" style="font-size: var(--text-xs);">Enter Code sent via SMS</label>
                            <div class="code-input-group">
                                <input type="text" id="phone_code" name="code" class="form-control" placeholder="6-digit code" required pattern="\d{6}" maxlength="6">
                                <button type="submit" class="btn btn-success">Verify</button>
                            </div>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editPhoneBtn = document.getElementById('edit-phone-btn');
    const phoneDisplayContainer = document.getElementById('phone-display-container');
    const phoneInputContainer = document.getElementById('phone-input-container');

    if (editPhoneBtn) {
        editPhoneBtn.addEventListener('click', function() {
            phoneDisplayContainer.style.display = 'none';
            phoneInputContainer.style.display = 'block';
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
