<?php

/***************************************************************
* api/tracks/read_tracks.php
* - Listet alle Tracks auf, markiert, ob sie auf dem Gerät ausgewählt sind
* - Ermittelt das erste verbundene Gerät des Nutzers
* - Gibt die Track-Liste als JSON zurück
* - vorausgesetzt: Benutzer-Authentifizierung ist gegeben / Session ist aktiv (Prüfung zu Beginn)

* Server-seitiger Code: wird auf dem Server ausgeführt
* Aufgerufen clientseitig in js/settings.js; durch ein Client-Login-Formular (settings.html)
* Server-Interaktion mit: ../../system/config.php (enthält DB-Verbindung)
* verwendete Datenbanktabellen: user_has_device, device_tracks, tracks
***************************************************************/

header('Content-Type: application/json');
include_once '../../system/config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login first']);
    exit();
}

try {
    $userId = $_SESSION['user_id'];

    // Get the user's first connected device
    $deviceStmt = $pdo->prepare("SELECT device_id FROM user_has_device WHERE user_id = ? LIMIT 1");
    $deviceStmt->execute([$userId]);
    $deviceRow = $deviceStmt->fetch(PDO::FETCH_ASSOC);

    if (!$deviceRow) {
        echo json_encode(['error' => 'No device connected. Please connect a device first.']);
        exit();
    }

    $deviceId = $deviceRow['device_id'];

    // LEFT JOIN so every track appears; selected = 1 when the device has it in device_tracks
    $query = "SELECT t.id, t.title,
                     CASE WHEN dt.device_id IS NOT NULL THEN 1 ELSE 0 END AS selected
              FROM tracks t
              LEFT JOIN device_tracks dt ON dt.track_id = t.id AND dt.device_id = ?
              ORDER BY t.title ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$deviceId]);

    $tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // $tracks: z. B. [{"id": 1,"title": "Another brick in the wall","selected": 0},{"id": 2,"title": "Back in black","selected": 1},{...}]
    
    echo json_encode($tracks);

} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error fetching tracks: ' . $e->getMessage()
    ]);
}
?>
