<?php
header('Content-Type: application/json');
include_once '../../system/config.php';

$userId = requireLogin();
$data = getJsonInput();

$deviceId = (int) ($data["device_id"] ?? 0);

if ($deviceId <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Ungültige Box-ID"]);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE boxes
    SET user_id = NULL
    WHERE id = ?
    AND user_id = ?
");
$stmt->execute([$deviceId, $userId]);

echo json_encode(["success" => true]);