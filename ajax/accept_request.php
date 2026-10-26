<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['role'] !== 'mechanic') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

/*$stmt = $pdo->prepare("SELECT is_verified FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetch()['is_verified']) {
    echo json_encode(['success' => false, 'message' => 'Your account is pending admin verification.']);
    exit;
}
*/
$requestId = (int)($_POST['request_id'] ?? 0);

// Atomic "first mechanic to accept wins" — the WHERE status='pending' clause
// prevents two mechanics from accepting the same job simultaneously
// (this is the "single-point dispatch failure" fix mentioned in the research).
$stmt = $pdo->prepare(
    "UPDATE service_requests
     SET mechanic_id = ?, status = 'accepted', accepted_at = NOW()
     WHERE id = ? AND status = 'pending'"
);
$stmt->execute([$_SESSION['user_id'], $requestId]);

if ($stmt->rowCount() === 0) {
    echo json_encode(['success' => false, 'message' => 'Sorry — another mechanic already accepted this job.']);
} else {
    echo json_encode(['success' => true, 'message' => 'Job accepted! Contact the driver to coordinate.']);
}
