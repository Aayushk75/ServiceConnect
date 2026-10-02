<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

$userId = (int)($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? 'verify'; // 'verify' or 'revoke'
$value  = $action === 'verify' ? 1 : 0;

$stmt = $pdo->prepare("UPDATE users SET is_verified = ? WHERE id = ? AND role = 'mechanic'");
$stmt->execute([$value, $userId]);

echo json_encode(['success' => true, 'message' => $action === 'verify' ? 'Mechanic verified.' : 'Verification revoked.']);
