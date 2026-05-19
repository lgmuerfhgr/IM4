<?php
/*********************************************************
 * api/device/connect_device.php
 * - Empfängt serial_id per POST (JSON)
 * - Prüft ob diese Box in der DB existiert
 * - Prüft ob sie bereits vergeben ist (eigener oder fremder User)
 * - Verknüpft die Box mit dem eingeloggten User (user_id)
 * - Gibt serial_id bei Erfolg zurück, Fehlermeldung sonst
 *
 * verwendete Datenbanktabellen: boxes
 *********************************************************/

header('Content-Type: application/json');
include_once '../../system/config.php';

// session_start() wird bereits in config.php aufgerufen – nicht nochmals aufrufen

// Nur eingeloggte User dürfen Boxen verbinden
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Bitte zuerst einloggen.']);
    exit();
}

$userId = $_SESSION['user_id'];

// getJsonInput() aus config.php liest und dekodiert den JSON-Body
$data = getJsonInput();

$serial_id = trim($data['serial_id'] ?? '');

if ($serial_id === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Kein Box-Code angegeben.']);
    exit();
}

try {
    // Prüfen ob eine Box mit dieser serial_id existiert
    $stmt = $pdo->prepare("SELECT id, serial_id, user_id FROM boxes WHERE serial_id = ?");
    $stmt->execute([$serial_id]);
    $box = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$box) {
        // serial_id unbekannt → Fehlermeldung
        http_response_code(404);
        echo json_encode(['error' => 'Kein Gerät mit diesem Code gefunden.']);
        exit();
    }

    // Box gehört bereits diesem User → als Erfolg behandeln
    if ((int)$box['user_id'] === (int)$userId) {
        echo json_encode(['success' => true, 'serial_id' => $box['serial_id']]);
        exit();
    }

    // Box ist bereits einem anderen User zugewiesen → Konflikt
    if (!empty($box['user_id'])) {
        http_response_code(409);
        echo json_encode(['error' => 'Diese Box ist bereits mit einem anderen Profil verbunden.']);
        exit();
    }

    // Box ist frei → user_id eintragen (Verknüpfung herstellen)
    $stmt = $pdo->prepare("UPDATE boxes SET user_id = ? WHERE id = ?");
    $stmt->execute([$userId, $box['id']]);

    // serial_id zurückgeben, damit JS den Namen im Feedback anzeigen kann
    echo json_encode(['success' => true, 'serial_id' => $box['serial_id']]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
?>

/*********************************************************
* api/device/connect_device.php
* - Verbindet ein Gerät mit einem eingeloggten Benutzer anhand device_code (z. B. 1234)
* - Selbstregistrierung des Geräts, falls nicht vorhanden
* - Zwischentabelle user_has_devices verknüpft Benutzer (Tabelle user) mit Gerät (devices)
* - Wählt alle Tracks für das Gerät aus (device_tracks)
* - Gibt Erfolg oder Fehler als JSON zurück

* Server-seitiger Code: wird auf dem Server ausgeführt (direkter API-Endpunkt)
* verwendete Datenbanktabellen: devices, user_has_device, device_tracks, tracks



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
*********************************************************/
