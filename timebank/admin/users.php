<?php
// admin/users.php
// Modern user management: view, edit roles, delete

require_once __DIR__ . '/../includes/admin_auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/csrf.php';

$pdo = getDatabaseConnection();
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    checkCsrf();
    $uid = (int)$_POST['user_id'];
    $role = $_POST['role'];
    if (in_array($role, ['user', 'provider', 'admin'])) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $uid]);
        $success = "User role updated successfully.";
    }
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    checkCsrf();
    $uid = (int)$_POST['user_id'];
    if ($uid != getCurrentUserId()) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $success = "User deleted successfully.";
    } else {
        $error = "Cannot delete your own account.";
    }
}

// Search & Fetch users
$search = trim($_GET['q'] ?? '');
$sql = "SELECT id, name, email, role, locked_credits, available_credits, profile_image, created_at FROM users WHERE 1=1";
$params = [];
if ($search) {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";
    $params = ["%$search%", "%$search%"];
}
$sql .= " ORDER BY created_at DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Stats
$stats = ['total' => 0, 'admin' => 0, 'provider' => 0, 'user' => 0];
$statStmt = $pdo->query("SELECT role, COUNT(*) FROM users GROUP BY role");
foreach ($statStmt->fetchAll(PDO::FETCH_KEY_PAIR) as $role => $count) {
    $stats['total'] += $count;
    if (isset($stats[$role])) $stats[$role] = $count;
}

$roleColors = ['admin' => 'role-admin', 'provider' => 'role-provider', 'user' => 'role-user'];
$roleLabels = ['admin' => 'Admin', 'provider' => 'Provider', 'user' => 'User'];

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
                    <h2 style="margin-bottom: var(--spacing-xs);">User Management</h2>
                    <p class="text-muted" style="margin:0;">Manage platform members, roles, and accounts.</p>
                </div>
                <div class="req-header-stats">
                    <span class="req-stat-pill"><span><?php echo number_format($stats['total']); ?></span> Total</span>
                    <span class="req-stat-pill req-pill-warn"><span><?php echo $stats['admin']; ?></span> Admins</span>
                    <span class="req-stat-pill req-pill-success"><span><?php echo $stats['provider']; ?></span> Providers</span>
                    <span class="req-stat-pill req-pill-info"><span><?php echo $stats['user']; ?></span> Users</span>
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

        <!-- Search -->
        <form method="GET" class="req-toolbar-search" style="margin-bottom: var(--spacing-xl);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" name="q" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-sm btn-primary">Search</button>
        </form>

        <?php if (empty($users)): ?>
            <div class="req-empty-state">
                <div class="req-empty-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <h3>No users found</h3>
                <p class="text-muted">No users match your search criteria.</p>
            </div>
        <?php else: ?>
            <div class="user-list">
                <?php foreach ($users as $index => $u):
                    $roleClass = $roleColors[$u['role']] ?? 'role-user';
                ?>
                    <div class="user-card" style="animation-delay: <?php echo 0.03 * $index; ?>s">
                        <div class="user-card-body">
                            <!-- User info -->
                            <div class="user-info">
                                <img src="<?php echo APP_URL; ?>/uploads/profiles/<?php echo htmlspecialchars($u['profile_image'] ?? 'default-avatar.png'); ?>"
                                     alt=""
                                     class="user-avatar"
                                     onerror="this.src='<?php echo APP_URL; ?>/assets/images/default-avatar.png'">
                                <div class="user-details">
                                    <div class="user-name"><?php echo htmlspecialchars($u['name']); ?></div>
                                    <div class="user-email"><?php echo htmlspecialchars($u['email']); ?></div>
                                    <div class="user-joined">Joined <?php echo formatDate($u['created_at']); ?></div>
                                </div>
                            </div>

                            <!-- Role form -->
                            <div class="user-role-section">
                                <form method="POST" class="user-role-form">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <span class="role-badge <?php echo $roleClass; ?>"><?php echo $roleLabels[$u['role']] ?? ucfirst($u['role']); ?></span>
                                    <select name="role" class="form-control user-role-select">
                                        <?php foreach ($roleLabels as $val => $label): ?>
                                            <option value="<?php echo $val; ?>"<?php echo $u['role'] === $val ? ' selected' : ''; ?>><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_role" class="btn btn-sm btn-outline">Update</button>
                                </form>
                            </div>

                            <!-- Credits -->
                            <div class="user-credits">
                                <div class="user-credit-chip credit-available">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <?php echo formatCredits($u['available_credits']); ?>
                                </div>
                                <div class="user-credit-chip credit-locked">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    <?php echo formatCredits($u['locked_credits']); ?>
                                </div>
                            </div>

                            <!-- Delete -->
                            <form method="POST" class="user-delete-form" onsubmit="return confirm('Delete this user permanently?');">
                                <?php echo csrfField(); ?>
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <button type="submit" name="delete_user" class="user-delete-btn" title="Delete user">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>