<?php

/*********************************************************
* api/profile/read_profile.php
* - Liest Benutzername aus users
* - Zählt Schreie aus sensordata für alle verbundenen Geräte
* - Listet die letzten 10 Schreie (starttime, endtime)
* - Listet verbundene Geräte (device_code)
* - Gibt alle Daten als JSON zurück
* - vorausgesetzt: Benutzer-Authentifizierung ist gegeben / Session ist aktiv (Prüfung zu Beginn)
*
* Server-seitiger Code: wird auf dem Server ausgeführt
* Aufgerufen clientseitig in js/profile.js; durch ein Client-Login-Formular (profile.html)
* verwendete Datenbanktabellen: 
* users, sensordata, user_has_device, devices
*********************************************************/

header('Content-Type: application/json');
include_once '../../system/config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Please login first']);
    exit();
}

try {
    // First, just get basic user info
    $userQuery = "SELECT name FROM users WHERE id = ?";
    $stmt = $pdo->prepare($userQuery);
    $stmt->execute([$_SESSION['user_id']]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userInfo) {
        echo json_encode(['error' => 'User not found']);
        exit();
    }

    // Then get total cries across all connected devices
    $scoreQuery = "SELECT COUNT(*) as total_cries
                   FROM sensordata s
                   INNER JOIN user_has_device uhd ON uhd.device_id = s.device_id
                   WHERE uhd.user_id = ?";
    
    $stmt = $pdo->prepare($scoreQuery);
    $stmt->execute([$_SESSION['user_id']]);
    $scoreInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    $userInfo['total_cries'] = (int)$scoreInfo['total_cries'];

    // Finally get latest crying events across all connected devices
    $activitiesQuery = "SELECT s.starttime, s.endtime
                       FROM sensordata s
                       INNER JOIN user_has_device uhd ON uhd.device_id = s.device_id
                       WHERE uhd.user_id = ?
                       ORDER BY s.starttime DESC
                       LIMIT 10";
    
    $stmt = $pdo->prepare($activitiesQuery);
    $stmt->execute([$_SESSION['user_id']]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get connected devices
    $devicesQuery = "SELECT d.id, d.device_code
                     FROM devices d
                     INNER JOIN user_has_device uhd ON uhd.device_id = d.id
                     WHERE uhd.user_id = ?
                     ORDER BY uhd.id ASC";
    $stmt = $pdo->prepare($devicesQuery);
    $stmt->execute([$_SESSION['user_id']]);
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'user' => $userInfo,
        'activities' => $activities,
        'devices' => $devices
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage(),
        'details' => $e->getTrace()
    ]);
}
?>