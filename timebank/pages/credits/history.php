<?php
// pages/credits/history.php
// Transaction ledger and credit history

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/credit_engine.php';
require_once __DIR__ . '/../../includes/auth_check.php';

requireLogin();

$pdo = getDatabaseConnection();
$user_id = getCurrentUserId();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$transactions = getTransactionHistory($pdo, $user_id, $limit, $offset);

// Get current balances
$stmt = $pdo->prepare("SELECT available_credits, locked_credits FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$balances = $stmt->fetch();

include __DIR__ . '/../../includes/header.php';
?>

<div class="container" style="padding: var(--spacing-xl) 0;">
    <h2 style="margin-bottom: var(--spacing-xl);">Credit History & Ledger</h2>

    <div class="balance-summary">
        <div class="summary-card">
            <h4>Available Credits</h4>
            <div class="value text-success"><?php echo formatCredits($balances['available_credits']); ?></div>
        </div>
        <div class="summary-card">
            <h4>Locked Credits</h4>
            <div class="value text-warning"><?php echo formatCredits($balances['locked_credits']); ?></div>
        </div>
        <div class="summary-card">
            <h4>Total Earned (Lifetime)</h4>
            <div class="value text-success">
                <?php
                $stmt = $pdo->prepare("SELECT COALESCE(SUM(credits_amount),0) as total FROM transactions WHERE to_user_id = ? AND transaction_type IN ('transfer','bonus_unlock')");
                $stmt->execute([$user_id]);
                echo formatCredits($stmt->fetchColumn());
                ?>
            </div>
        </div>
    </div>

    <div class="card" style="overflow-x: auto;">
        <h3 style="margin-bottom: var(--spacing-md);">Transaction Ledger</h3>
        <?php if (empty($transactions)): ?>
            <p class="text-muted" style="padding: var(--spacing-xl); text-align: center;">No transactions recorded yet.</p>
        <?php else: ?>
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>From/To</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
    <?php foreach ($transactions as $tx): 
        $from_id = $tx['from_user_id'] ?? null;
        $to_id = $tx['to_user_id'] ?? null;
        $is_sender = ($from_id == $user_id);
    ?>
        <tr>
            <td><?php echo formatDate($tx['created_at']); ?></td>
            <td>
                <span class="service-status status-<?php 
                    echo $tx['transaction_type'] === 'transfer' ? 'available' : 
                    ($tx['transaction_type'] === 'bonus_unlock' ? 'busy' : 'unavailable'); 
                ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $tx['transaction_type'])); ?>
                </span>
            </td>
            <td><?php echo htmlspecialchars($tx['description'] ?: '-'); ?></td>
            <td>
                <?php if ($is_sender): ?>
                    Sent to: <?php echo htmlspecialchars($tx['to_name'] ?? 'Unknown'); ?>
                <?php else: ?>
                    Received from: <?php echo htmlspecialchars($tx['from_name'] ?? 'System'); ?>
                <?php endif; ?>
            </td>
            <td class="<?php echo $is_sender ? 'credit-negative' : 'credit-positive'; ?>">
                <?php echo ($is_sender ? '-' : '+') . formatCredits($tx['credits_amount']); ?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>