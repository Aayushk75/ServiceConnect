<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['role'] !== 'mechanic') {
    http_response_code(403);
    echo json_encode(['count' => 0]);
    exit;
}

$count = $pdo->query("SELECT COUNT(*) c FROM service_requests WHERE status = 'pending'")->fetch()['c'];
echo json_encode(['count' => (int)$count]);
