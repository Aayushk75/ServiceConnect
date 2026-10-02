<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

if (!is_logged_in() || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}

$issue_type   = trim($_POST['issue_type'] ?? '');
$description  = trim($_POST['description'] ?? '');
$vehicle_info = trim($_POST['vehicle_info'] ?? '');
$latitude     = $_POST['latitude'] ?? null;
$longitude    = $_POST['longitude'] ?? null;

if ($issue_type === '' || $latitude === '' || $longitude === '' || $latitude === null || $longitude === null) {
    echo json_encode(['success' => false, 'message' => 'Issue type and location are required.']);
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO service_requests (user_id, issue_type, description, vehicle_info, latitude, longitude, status)
     VALUES (?, ?, ?, ?, ?, ?, 'pending')"
);
$stmt->execute([$_SESSION['user_id'], $issue_type, $description, $vehicle_info, $latitude, $longitude]);

echo json_encode(['success' => true, 'message' => 'Request submitted! Nearby mechanics have been notified.', 'request_id' => $pdo->lastInsertId()]);
