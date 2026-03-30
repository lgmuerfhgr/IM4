<?php

/*********************************************************
* api/sensordata/read_sensordata.php
* - Liest Sensordata aller dem Benutzer zugeordneten Geräte
* - Sortiert Ergebnisse nach Startzeit (absteigend)
* - Gibt Daten bzw. Fehler als JSON zurück
* - vorausgesetzt: Benutzer-Authentifizierung ist gegeben / Session ist aktiv (Prüfung zu Beginn)

* Server-seitiger Code: wird auf dem Server ausgeführt
* Aufgerufen clientseitig in js/index.js; durch ein Client-Login-Formular (index.html)
* Server-Interaktion mit: ../../system/config.php (PDO/DB-Verbindung), PHP-Session
* Verwendete Datenbanktabellen: sensordata, user_has_device
*********************************************************/

header('Content-Type: application/json');
include_once '../../system/config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login first']);
    exit();
}

try {

    // Fetch sensordata for all devices connected to this user
    $query = "SELECT s.id, s.starttime, s.endtime
              FROM sensordata s
              INNER JOIN user_has_device uhd ON uhd.device_id = s.device_id
              WHERE uhd.user_id = ?
              ORDER BY s.starttime DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    // $stmt->execute();

    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // eg. [{id: 23, starttime: '2026-03-21 20:50:27', endtime: '2026-03-21 20:50:37'}{...}]
    echo json_encode($history);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error fetching sensordata: ' . $e->getMessage()
    ]);
}
?>