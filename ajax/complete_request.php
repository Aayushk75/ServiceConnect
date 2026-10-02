<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['role'] !== 'mechanic') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

$requestId = (int)($_POST['request_id'] ?? 0);

$stmt = $pdo->prepare(
    "UPDATE service_requests
     SET status = 'completed', completed_at = NOW()
     WHERE id = ? AND mechanic_id = ? AND status = 'accepted'"
);
$stmt->execute([$requestId, $_SESSION['user_id']]);

echo json_encode([
    'success' => $stmt->rowCount() > 0,
    'message' => $stmt->rowCount() > 0 ? 'Job marked as completed.' : 'Could not update this job.'
]);
