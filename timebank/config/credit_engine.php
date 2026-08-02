<?php
// config/credit_engine.php
// Secure credit calculation and transfer engine using PDO transactions

function calculateCredits($pdo, $request_id, $actual_hours = null) {
    $stmt = $pdo->prepare("
        SELECT s.credit_rate, sr.scheduled_time
        FROM service_requests sr
        JOIN services s ON sr.service_id = s.id
        WHERE sr.id = ?
    ");
    $stmt->execute([$request_id]);
    $data = $stmt->fetch();
    
    if (!$data) return 0;
    
    $hours = $actual_hours ?? max(0.5, (strtotime($data['scheduled_time'] ?? time()) - time()) / 3600);
    return round($data['credit_rate'] * $hours, 2);
}

function transferCredits($pdo, $from_user_id, $to_user_id, $amount, $request_id, $description) {
    if ($amount <= 0) return false;

    $pdo->beginTransaction();
    try {
        // Verify balance
        $stmt = $pdo->prepare("SELECT available_credits FROM users WHERE id = ?");
        $stmt->execute([$from_user_id]);
        $balance = (float)$stmt->fetchColumn();

        if ($balance < $amount) {
            $pdo->rollBack();
            return false;
        }

        // Deduct from requester
        $stmt = $pdo->prepare("UPDATE users SET available_credits = available_credits - ? WHERE id = ?");
        $stmt->execute([$amount, $from_user_id]);

        // Add to provider
        $stmt = $pdo->prepare("UPDATE users SET available_credits = available_credits + ? WHERE id = ?");
        $stmt->execute([$amount, $to_user_id]);

        // Log transaction
        $stmt = $pdo->prepare("
            INSERT INTO transactions 
            (from_user_id, to_user_id, service_request_id, credits_amount, transaction_type, description, created_at)
            VALUES (?, ?, ?, ?, 'transfer', ?, NOW())
        ");
        $stmt->execute([$from_user_id, $to_user_id, $request_id, $amount, $description]);

        // Unlock signup bonus if applicable
        unlockLockedCredits($pdo, $to_user_id, $request_id);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Credit Transfer Error: " . $e->getMessage());
        return false;
    }
}

function unlockLockedCredits($pdo, $user_id, $request_id) {
    $stmt = $pdo->prepare("SELECT locked_credits FROM users WHERE id = ? AND locked_credits > 0 FOR UPDATE");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    if ($result && $result['locked_credits'] > 0) {
        $locked_amount = (float)$result['locked_credits'];
        
        $stmt = $pdo->prepare("
            UPDATE users 
            SET available_credits = available_credits + ?, locked_credits = 0 
            WHERE id = ?
        ");
        $stmt->execute([$locked_amount, $user_id]);
        
        $stmt = $pdo->prepare("
            INSERT INTO transactions 
            (to_user_id, service_request_id, credits_amount, transaction_type, description, created_at)
            VALUES (?, ?, ?, 'bonus_unlock', 'Signup bonus unlocked after first completed service', NOW())
        ");
        $stmt->execute([$user_id, $request_id, $locked_amount]);
    }
}

function getTransactionHistory($pdo, $user_id, $limit = 50, $offset = 0) {
    $sql = "
        SELECT t.id, t.credits_amount, t.transaction_type, t.description, t.created_at,
               u_from.name as from_name, u_to.name as to_name,
               sr.service_id
        FROM transactions t
        LEFT JOIN users u_from ON t.from_user_id = u_from.id
        LEFT JOIN users u_to ON t.to_user_id = u_to.id
        LEFT JOIN service_requests sr ON t.service_request_id = sr.id
        WHERE t.from_user_id = ? OR t.to_user_id = ?
        ORDER BY t.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $user_id, $limit, $offset]);
    return $stmt->fetchAll();
}
?>