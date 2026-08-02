<?php
// includes/rating_helper.php
// Reputation calculation and rating retrieval helpers

function calculateReputation($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT COALESCE(AVG(rating_value), 0) as avg_score, COUNT(*) as total_reviews 
        FROM ratings WHERE reviewee_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function getUserRatings($pdo, $user_id, $limit = 10) {
    $stmt = $pdo->prepare("
        SELECT r.rating_value, r.review_text, r.created_at,
               u.name as reviewer_name, u.profile_image,
               s.title as service_title
        FROM ratings r
        JOIN users u ON r.reviewer_id = u.id
        JOIN service_requests sr ON r.service_request_id = sr.id
        JOIN services s ON sr.service_id = s.id
        WHERE r.reviewee_id = ?
        ORDER BY r.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

function generateStarHTML($rating) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    $empty = 5 - $full - ($half ? 1 : 0);
    
    $html = '';
    for ($i = 0; $i < $full; $i++) $html .= '&#9733;';
    if ($half) $html .= '&#9733;'; // Simplified full star for display
    for ($i = 0; $i < $empty; $i++) $html .= '&#9734;';
    
    return $html;
}
?>