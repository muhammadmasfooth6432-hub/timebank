<?php
// admin/transactions.php
// Modern global transaction audit log

require_once __DIR__ . '/../includes/admin_auth_check.php';
require_once __DIR__ . '/../config/db.php';
$pdo = getDatabaseConnection();

$filter_type = $_GET['type'] ?? 'all';
$allowed = ['all','transfer','bonus','earn','spend'];
if (!in_array($filter_type, $allowed)) $filter_type = 'all';

$sql = "
    SELECT t.id, t.credits_amount, t.transaction_type, t.description, t.created_at,
           uf.name as from_name, ut.name as to_name
    FROM transactions t
    LEFT JOIN users uf ON t.from_user_id = uf.id
    LEFT JOIN users ut ON t.to_user_id = ut.id
";
$params = [];
if ($filter_type !== 'all') {
    $sql .= " WHERE t.transaction_type = ?";
    $params[] = $filter_type;
}
$sql .= " ORDER BY t.created_at DESC LIMIT 100";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Stats
$stats = $pdo->query("SELECT COALESCE(SUM(credits_amount),0) as total, COUNT(*) as count, COALESCE(AVG(credits_amount),0) as avg FROM transactions")->fetch();
$typeCounts = $pdo->query("SELECT transaction_type, COUNT(*) FROM transactions GROUP BY transaction_type")->fetchAll(PDO::FETCH_KEY_PAIR);

$txIcons = [
    'transfer' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>',
    'bonus'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>',
    'earn'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>',
    'spend'    => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>',
];

$txColors = [
    'transfer' => 'tx-blue',
    'bonus'    => 'tx-purple',
    'earn'     => 'tx-green',
    'spend'    => 'tx-amber',
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
                    <h2 style="margin-bottom: var(--spacing-xs);">Transaction Audit Log</h2>
                    <p class="text-muted" style="margin:0;">Global credit movement and transfers.</p>
                </div>
                <div class="req-header-stats">
                    <span class="req-stat-pill"><span><?php echo number_format($stats['count']); ?></span> Total</span>
                    <span class="req-stat-pill req-pill-success"><span><?php echo formatCredits($stats['total']); ?></span> Volume</span>
                    <span class="req-stat-pill req-pill-info"><span><?php echo formatCredits($stats['avg']); ?></span> Avg</span>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <?php
        $tabs = ['all' => 'All', 'transfer' => 'Transfers', 'bonus' => 'Bonus', 'earn' => 'Earn', 'spend' => 'Spend'];
        ?>
        <div class="req-filter-bar" style="margin-bottom: var(--spacing-lg);">
            <?php foreach ($tabs as $type => $label): ?>
                <a href="?type=<?php echo $type; ?>" class="req-filter-tab<?php echo $filter_type === $type ? ' active' : ''; ?>"><?php echo $label; ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($transactions)): ?>
            <div class="req-empty-state">
                <div class="req-empty-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <h3>No transactions</h3>
                <p class="text-muted">No transaction records match the selected filter.</p>
            </div>
        <?php else: ?>
            <div class="tx-list">
                <?php foreach ($transactions as $index => $t):
                    $txType = $t['transaction_type'];
                    $txClass = $txColors[$txType] ?? 'tx-blue';
                    $txIcon = $txIcons[$txType] ?? $txIcons['transfer'];
                    $isPositive = strpos($txType, 'earn') !== false || strpos($txType, 'bonus') !== false;
                    $amountClass = $isPositive ? 'credit-positive' : 'credit-negative';
                    $prefix = $isPositive ? '+' : '−';
                ?>
                    <div class="tx-card <?php echo $txClass; ?>" style="animation-delay: <?php echo 0.03 * $index; ?>s">
                        <div class="tx-card-border"></div>
                        <div class="tx-card-body">
                            <div class="tx-main">
                                <div class="tx-icon-wrap">
                                    <?php echo $txIcon; ?>
                                </div>
                                <div class="tx-flow">
                                    <div class="tx-party">
                                        <span class="tx-label">From</span>
                                        <span class="tx-name"><?php echo htmlspecialchars($t['from_name'] ?? 'System'); ?></span>
                                    </div>
                                    <div class="tx-arrow">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                    </div>
                                    <div class="tx-party">
                                        <span class="tx-label">To</span>
                                        <span class="tx-name"><?php echo htmlspecialchars($t['to_name'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="tx-meta">
                                <div class="tx-amount <?php echo $amountClass; ?>">
                                    <span class="tx-prefix"><?php echo $prefix; ?></span>
                                    <?php echo formatCredits($t['credits_amount']); ?>
                                </div>
                                <span class="tx-type-badge"><?php echo ucfirst($txType); ?></span>
                                <span class="tx-date"><?php echo formatDate($t['created_at']); ?></span>
                            </div>
                        </div>
                        <?php if (!empty($t['description'])): ?>
                            <div class="tx-desc"><?php echo htmlspecialchars($t['description']); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>