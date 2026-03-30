<?php

/*********************************************************
* api/device/connect_device.php
* - Verbindet ein Gerät mit einem eingeloggten Benutzer anhand device_code (z. B. 1234)
* - Selbstregistrierung des Geräts, falls nicht vorhanden
* - Zwischentabelle user_has_devices verknüpft Benutzer (Tabelle user) mit Gerät (devices)
* - Wählt alle Tracks für das Gerät aus (device_tracks)
* - Gibt Erfolg oder Fehler als JSON zurück

* Server-seitiger Code: wird auf dem Server ausgeführt (direkter API-Endpunkt)
* verwendete Datenbanktabellen: devices, user_has_device, device_tracks, tracks
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

if (!isset($data->device_code) || trim($data->device_code) === '') {
    echo json_encode(['error' => 'device_code is required']);
    exit();
}

$deviceCode = trim($data->device_code);
$userId = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // Find or create the device
    $stmt = $pdo->prepare("SELECT id FROM devices WHERE device_code = ?");
    $stmt->execute([$deviceCode]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$device) {
        // Device self-registers on first connection
        $insert = $pdo->prepare("INSERT INTO devices (device_code) VALUES (?)");
        $insert->execute([$deviceCode]);
        $deviceId = (int)$pdo->lastInsertId();

        // Seed all tracks as selected for this new device
        $seedTracks = $pdo->prepare("
            INSERT INTO device_tracks (device_id, track_id)
            SELECT ?, id FROM tracks
        ");
        $seedTracks->execute([$deviceId]);
    } else {
        $deviceId = (int)$device['id'];
    }

    // Link user to device (IGNORE avoids duplicate-key errors)
    $link = $pdo->prepare("INSERT IGNORE INTO user_has_device (user_id, device_id) VALUES (?, ?)");
    $link->execute([$userId, $deviceId]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Device connected',
        'device_id' => $deviceId,
        'device_code' => $deviceCode
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Failed to connect device: ' . $e->getMessage()]);
}
?>
