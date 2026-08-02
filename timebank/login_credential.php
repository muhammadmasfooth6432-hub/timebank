<?php
// login_credential.php
// Seeding and utility helper to easily switch between test accounts.

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = getDatabaseConnection();

$test_accounts = [
    [
        'name' => 'Admin Test',
        'email' => 'admin@timebank.com',
        'password' => 'admin123',
        'role' => 'admin',
        'locked_credits' => 0.00,
        'available_credits' => 10.00
    ],
    [
        'name' => 'Provider Test',
        'email' => 'provider@timebank.com',
        'password' => 'provider123',
        'role' => 'provider',
        'locked_credits' => 0.00,
        'available_credits' => 10.00
    ],
    [
        'name' => 'User Test',
        'email' => 'user@timebank.com',
        'password' => 'user123',
        'role' => 'user',
        'locked_credits' => 3.00,
        'available_credits' => 10.00
    ]
];

$creation_messages = [];

// Seed the default test accounts if they do not exist
foreach ($test_accounts as $account) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$account['email']]);
    if (!$stmt->fetch()) {
        $hashed = password_hash($account['password'], PASSWORD_DEFAULT);
        $insert = $pdo->prepare("
            INSERT INTO users (name, email, password, role, locked_credits, available_credits, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $insert->execute([
            $account['name'],
            $account['email'],
            $hashed,
            $account['role'],
            $account['locked_credits'],
            $account['available_credits']
        ]);
        $creation_messages[] = "Created {$account['role']} account: <strong>{$account['email']}</strong>";
    }
}

// Handle Quick Login action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_login') {
    $role = $_POST['role'] ?? '';
    
    $target_email = '';
    foreach ($test_accounts as $account) {
        if ($account['role'] === $role) {
            $target_email = $account['email'];
            break;
        }
    }
    
    if ($target_email) {
        $stmt = $pdo->prepare("
            SELECT id, name, email, role, profile_image, locked_credits, available_credits 
            FROM users WHERE email = ?
        ");
        $stmt->execute([$target_email]);
        $user = $stmt->fetch();
        
        if ($user) {
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_image'] = $user['profile_image'] ?: 'default-avatar.png';
            $_SESSION['available_credits'] = (float)$user['available_credits'];
            $_SESSION['locked_credits'] = (float)$user['locked_credits'];
            $_SESSION['logged_in'] = true;
            
            $_SESSION['success'] = "Quick logged in as " . htmlspecialchars($user['name']) . "!";
            redirect(APP_URL . '/dashboard.php');
        } else {
            $_SESSION['error'] = "Could not find seeded user in database. Please check your DB connection.";
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<style>
.credential-card {
    transition: transform var(--transition-base), box-shadow var(--transition-base), border-color var(--transition-base) !important;
}
.credential-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg), 0 0 25px rgba(99, 102, 241, 0.15) !important;
    border-color: rgba(99, 102, 241, 0.4) !important;
}
.copy-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--color-border);
    color: var(--color-text-secondary);
    border-radius: var(--radius-sm);
    padding: var(--spacing-xxs) var(--spacing-xs);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: var(--text-xs);
    transition: all var(--transition-fast);
}
.copy-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--color-text-primary);
    border-color: var(--color-primary-light);
}
.credential-field {
    background: rgba(0, 0, 0, 0.2);
    border-radius: var(--radius-sm);
    padding: var(--spacing-xs) var(--spacing-sm);
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: var(--font-mono);
    font-size: var(--text-sm);
    margin-bottom: var(--spacing-sm);
}
.role-badge-admin {
    background: rgba(139, 92, 246, 0.15);
    color: #a78bfa;
    border: 1px solid rgba(139, 92, 246, 0.3);
    padding: var(--spacing-xxs) var(--spacing-sm);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: var(--font-semibold);
    text-transform: uppercase;
}
.role-badge-provider {
    background: rgba(14, 165, 233, 0.15);
    color: #38bdf8;
    border: 1px solid rgba(14, 165, 233, 0.3);
    padding: var(--spacing-xxs) var(--spacing-sm);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: var(--font-semibold);
    text-transform: uppercase;
}
.role-badge-user {
    background: rgba(16, 185, 129, 0.15);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.3);
    padding: var(--spacing-xxs) var(--spacing-sm);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: var(--font-semibold);
    text-transform: uppercase;
}
.credits-pill {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--spacing-xs) var(--spacing-sm);
    display: flex;
    justify-content: space-between;
    font-size: var(--text-sm);
}
.capabilities-list {
    list-style: none;
    padding-left: 0;
    margin: var(--spacing-md) 0;
    font-size: var(--text-sm);
    color: var(--color-text-secondary);
}
.capabilities-list li {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    margin-bottom: var(--spacing-xs);
}
.capabilities-list svg {
    color: var(--color-success);
    flex-shrink: 0;
}
</style>

<div class="container" style="padding: var(--spacing-xxl) 0;">
    <div class="text-center" style="margin-bottom: var(--spacing-xxl);">
        <h1 class="text-gradient" style="font-size: var(--text-4xl); margin-bottom: var(--spacing-xs);">Test Credentials Generator</h1>
        <p class="text-muted" style="font-size: var(--text-lg); max-width: 600px; margin: 0 auto;">
            Seed default role-based test accounts into your database and instantly sign in to explore their features.
        </p>
    </div>

    <!-- DB Seeding Status -->
    <?php if (!empty($creation_messages)): ?>
        <div class="alert alert-success" style="margin-bottom: var(--spacing-xl); text-align: left; display: flex; align-items: flex-start; gap: var(--spacing-md);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-success); flex-shrink: 0; margin-top: 3px;">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <div>
                <strong style="display: block; font-size: var(--text-base); margin-bottom: 5px;">Database Seeding Successful</strong>
                <ul style="margin: 0; padding-left: var(--spacing-md); line-height: 1.6;">
                    <?php foreach ($creation_messages as $msg): ?>
                        <li><?php echo $msg; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- Role Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--spacing-lg);">
        
        <!-- Admin Card -->
        <div class="card credential-card" style="border: 1px solid var(--color-border); border-top: 5px solid #8b5cf6; background: var(--color-bg-card); backdrop-filter: var(--blur-glass); border-radius: var(--radius-lg); padding: var(--spacing-lg); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="flex justify-between items-center" style="margin-bottom: var(--spacing-md);">
                    <h3 style="margin: 0;">Admin Role</h3>
                    <span class="role-badge-admin">Admin</span>
                </div>
                <p class="text-muted" style="font-size: var(--text-sm); margin-bottom: var(--spacing-md);">
                    High-level platform administrator. Full privileges to oversee all transactions, categories, and members.
                </p>
                
                <div class="form-group">
                    <label style="font-size: var(--text-xs); color: var(--color-text-muted); display: block; margin-bottom: 4px;">Email</label>
                    <div class="credential-field">
                        <span>admin@timebank.com</span>
                        <button class="copy-btn" onclick="copyToClipboard('admin@timebank.com', this)">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            Copy
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-size: var(--text-xs); color: var(--color-text-muted); display: block; margin-bottom: 4px;">Password</label>
                    <div class="credential-field">
                        <span>admin123</span>
                        <button class="copy-btn" onclick="copyToClipboard('admin123', this)">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            Copy
                        </button>
                    </div>
                </div>

                <div class="credits-pill" style="margin-bottom: var(--spacing-md);">
                    <span style="color: var(--color-text-secondary);">Test Balance:</span>
                    <strong style="color: var(--color-success);">10.00 credits</strong>
                </div>

                <h4 style="font-size: var(--text-sm); margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-border); padding-bottom: 4px;">Features & Privileges</h4>
                <ul class="capabilities-list">
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Access to Admin Dashboard
                    </li>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Manage categories & service rates
                    </li>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        View system audit logs & statistics
                    </li>
                </ul>
            </div>
            
            <form method="POST" action="" style="margin-top: var(--spacing-md);">
                <input type="hidden" name="action" value="quick_login">
                <input type="hidden" name="role" value="admin">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: var(--spacing-sm); background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%); box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                    Quick Login as Admin
                </button>
            </form>
        </div>

        <!-- Provider Card -->
        <div class="card credential-card" style="border: 1px solid var(--color-border); border-top: 5px solid #0ea5e9; background: var(--color-bg-card); backdrop-filter: var(--blur-glass); border-radius: var(--radius-lg); padding: var(--spacing-lg); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="flex justify-between items-center" style="margin-bottom: var(--spacing-md);">
                    <h3 style="margin: 0;">Provider Role</h3>
                    <span class="role-badge-provider">Provider</span>
                </div>
                <p class="text-muted" style="font-size: var(--text-sm); margin-bottom: var(--spacing-md);">
                    Service Provider account. Offers skills, handles incoming requests, and earns credits for completed tasks.
                </p>
                
                <div class="form-group">
                    <label style="font-size: var(--text-xs); color: var(--color-text-muted); display: block; margin-bottom: 4px;">Email</label>
                    <div class="credential-field">
                        <span>provider@timebank.com</span>
                        <button class="copy-btn" onclick="copyToClipboard('provider@timebank.com', this)">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            Copy
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-size: var(--text-xs); color: var(--color-text-muted); display: block; margin-bottom: 4px;">Password</label>
                    <div class="credential-field">
                        <span>provider123</span>
                        <button class="copy-btn" onclick="copyToClipboard('provider123', this)">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            Copy
                        </button>
                    </div>
                </div>

                <div class="credits-pill" style="margin-bottom: var(--spacing-md);">
                    <span style="color: var(--color-text-secondary);">Test Balance:</span>
                    <strong style="color: var(--color-success);">10.00 credits</strong>
                </div>

                <h4 style="font-size: var(--text-sm); margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-border); padding-bottom: 4px;">Features & Privileges</h4>
                <ul class="capabilities-list">
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Create & publish service listings
                    </li>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Manage requests from members
                    </li>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Complete tasks & receive time credits
                    </li>
                </ul>
            </div>
            
            <form method="POST" action="" style="margin-top: var(--spacing-md);">
                <input type="hidden" name="action" value="quick_login">
                <input type="hidden" name="role" value="provider">
                <button type="submit" class="btn btn-secondary" style="width: 100%; padding: var(--spacing-sm); background: linear-gradient(135deg, #0ea5e9 0%, #22d3ee 100%); box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                    Quick Login as Provider
                </button>
            </form>
        </div>

        <!-- Regular User Card -->
        <div class="card credential-card" style="border: 1px solid var(--color-border); border-top: 5px solid #10b981; background: var(--color-bg-card); backdrop-filter: var(--blur-glass); border-radius: var(--radius-lg); padding: var(--spacing-lg); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="flex justify-between items-center" style="margin-bottom: var(--spacing-md);">
                    <h3 style="margin: 0;">Regular User</h3>
                    <span class="role-badge-user">User</span>
                </div>
                <p class="text-muted" style="font-size: var(--text-sm); margin-bottom: var(--spacing-md);">
                    Standard community member account. Can browse & request services and exchange time credits.
                </p>
                
                <div class="form-group">
                    <label style="font-size: var(--text-xs); color: var(--color-text-muted); display: block; margin-bottom: 4px;">Email</label>
                    <div class="credential-field">
                        <span>user@timebank.com</span>
                        <button class="copy-btn" onclick="copyToClipboard('user@timebank.com', this)">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            Copy
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-size: var(--text-xs); color: var(--color-text-muted); display: block; margin-bottom: 4px;">Password</label>
                    <div class="credential-field">
                        <span>user123</span>
                        <button class="copy-btn" onclick="copyToClipboard('user123', this)">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            Copy
                        </button>
                    </div>
                </div>

                <div class="credits-pill" style="margin-bottom: var(--spacing-md);">
                    <span style="color: var(--color-text-secondary);">Test Balance:</span>
                    <strong style="color: var(--color-success);">10.00 (+3.00 locked) credits</strong>
                </div>

                <h4 style="font-size: var(--text-sm); margin-bottom: var(--spacing-sm); border-bottom: 1px solid var(--color-border); padding-bottom: 4px;">Features & Privileges</h4>
                <ul class="capabilities-list">
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Browse service catalog
                    </li>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Request services from providers
                    </li>
                    <li>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Write service reviews & ratings
                    </li>
                </ul>
            </div>
            
            <form method="POST" action="" style="margin-top: var(--spacing-md);">
                <input type="hidden" name="action" value="quick_login">
                <input type="hidden" name="role" value="user">
                <button type="submit" class="btn btn-outline" style="width: 100%; padding: var(--spacing-sm); border-color: #10b981; color: #10b981; background: rgba(16, 185, 129, 0.05);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #10b981;"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                    Quick Login as User
                </button>
            </form>
        </div>

    </div>
</div>

<script>
function copyToClipboard(text, btnElement) {
    if (!navigator.clipboard) {
        // Fallback for non-https connection
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showCopySuccess(btnElement);
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
        }
        document.body.removeChild(textArea);
        return;
    }
    navigator.clipboard.writeText(text).then(() => {
        showCopySuccess(btnElement);
    }).catch(err => {
        console.error('Async: Could not copy text: ', err);
    });
}

function showCopySuccess(btnElement) {
    const originalHTML = btnElement.innerHTML;
    btnElement.innerHTML = `
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span style="color: #10b981; font-weight: bold;">Copied!</span>
    `;
    setTimeout(() => {
        btnElement.innerHTML = originalHTML;
    }, 1500);
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
