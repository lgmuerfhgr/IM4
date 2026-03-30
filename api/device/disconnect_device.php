<?php

/*********************************************************
* api/device/disconnect_device.php
* - lösche die Verknüpfung zwischen Benutzer und Gerät in der Tabelle user_has_device

* Server-seitiger Code: wird auf dem Server ausgeführt
* Client-seitig aufgerufen in: 
* - profile.js (durch Klick auf "Gerät trennen" in profil.html)
* Server-Interaktion mit: ../../system/config.php
* verwendete Datenbanktabellen: user_has_device
*********************************************************/

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
include_once '../../system/config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->device_id)) {
    echo json_encode(['error' => 'device_id is required']);
    exit();
}

$userId = $_SESSION['user_id'];
$deviceId = (int)$data->device_id;

try {
    $stmt = $pdo->prepare("DELETE FROM user_has_device WHERE user_id = ? AND device_id = ?");
    $stmt->execute([$userId, $deviceId]);

    echo json_encode(['success' => true, 'message' => 'Device disconnected']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to disconnect device: ' . $e->getMessage()]);
}
?>
