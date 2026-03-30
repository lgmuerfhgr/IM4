<?php

/********************************************************
* api/tracks/update_selected_tracks.php
* - Liest track_id und selected aus POST-JSON
* - Ermittelt erstes verbundenes Gerät des Nutzers (-> Tabelle user_has_device)
* - Fügt oder entfernt Track-Auswahl für das Gerät (-> Tabelle device_tracks)
* - Gibt Erfolg oder Fehler als JSON zurück
* - vorausgesetzt: Benutzer-Authentifizierung ist gegeben / Session ist aktiv (Prüfung zu Beginn)

* Server-seitiger Code: wird auf dem Server ausgeführt
* Aufgerufen clientseitig in js/settings.js; durch ein Client-Login-Formular (settings.html)
* verwendete Datenbanktabellen: user_has_device, device_tracks
*********************************************************/

header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
include_once '../../system/config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login first']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));
// z. B.  {"track_id":10,"selected":1}  // immer wenn auf settings.html ein Listen-Item an-/abgewählt wird, wird für den jeweiligen Listeneintrag eine solche Nachricht an dieses PHP-Skript geschickt.

if (!isset($data->track_id) || !isset($data->selected)) {
    echo json_encode(['error' => 'track_id and selected are required']);
    exit();
}

try {
    $userId  = $_SESSION['user_id'];
    $trackId = (int)$data->track_id;

    // Get the user's first connected device
    $deviceStmt = $pdo->prepare("SELECT device_id FROM user_has_device WHERE user_id = ? LIMIT 1");
    $deviceStmt->execute([$userId]);
    $deviceRow = $deviceStmt->fetch(PDO::FETCH_ASSOC);

    if (!$deviceRow) {
        echo json_encode(['error' => 'No device connected. Please connect a device first.']);
        exit();
    }

    $deviceId = $deviceRow['device_id'];

    if ($data->selected) {
        // Add selection (IGNORE avoids duplicate-key errors)
        $query = "INSERT IGNORE INTO device_tracks (device_id, track_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$deviceId, $trackId]);
    } else {
        // Remove selection
        $query = "DELETE FROM device_tracks WHERE device_id = ? AND track_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$deviceId, $trackId]);
    }

    echo json_encode(['success' => true, 'message' => 'Track setting updated']);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error updating track: ' . $e->getMessage()
    ]);
}
?>
