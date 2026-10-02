<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

$requestId = (int)($_POST['request_id'] ?? 0);
$rating    = (int)($_POST['rating'] ?? 0);
$comment   = trim($_POST['comment'] ?? '');

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating.']);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT * FROM service_requests WHERE id = ? AND user_id = ? AND status = 'completed'"
);
$stmt->execute([$requestId, $_SESSION['user_id']]);
$request = $stmt->fetch();

if (!$request) {
    echo json_encode(['success' => false, 'message' => 'Request not found or not completed yet.']);
    exit;
}

// Prevent duplicate reviews for the same request
$dupe = $pdo->prepare("SELECT id FROM reviews WHERE request_id = ?");
$dupe->execute([$requestId]);
if ($dupe->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You already rated this job.']);
    exit;
}

$pdo->beginTransaction();
try {
    $ins = $pdo->prepare(
        "INSERT INTO reviews (request_id, user_id, mechanic_id, rating, comment) VALUES (?, ?, ?, ?, ?)"
    );
    $ins->execute([$requestId, $_SESSION['user_id'], $request['mechanic_id'], $rating, $comment]);

    // Recalculate mechanic's average rating
    $agg = $pdo->prepare("SELECT AVG(rating) avg_r, COUNT(*) cnt FROM reviews WHERE mechanic_id = ?");
    $agg->execute([$request['mechanic_id']]);
    $result = $agg->fetch();

    $upd = $pdo->prepare("UPDATE users SET avg_rating = ?, rating_count = ? WHERE id = ?");
    $upd->execute([round($result['avg_r'], 1), $result['cnt'], $request['mechanic_id']]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Thanks for your feedback!']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
