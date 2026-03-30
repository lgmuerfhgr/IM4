<?php
/*********************************************************
* api/auth/auth.php
* - Prüft, ob ein Benutzer eingeloggt ist (Session)
* - Gibt JSON mit Benutzerinformationen (email, user_id) zurück
* - Gibt bei nicht eingeloggtem Benutzer einen 401-Fehler als JSON zurück

* Server-seitiger Code: wird auf dem Server ausgeführt (direkter API-Endpunkt)
* aufgerufen von: js/profile.js, settings.js
* verwendete Datenbanktabellen: keine (nur Session-Daten)
*********************************************************/


// index.php (API that returns JSON about the logged-in user)
session_start();

if (!isset($_SESSION['user_id'])) {
    // Instead of redirect, return a 401 JSON response
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// If they are logged in:
header('Content-Type: application/json');

// Maybe return the user’s email or user_id here
// (assuming you stored email at login time)
echo json_encode([
    "email" => $_SESSION['email'] ?? null,
    "user_id" => $_SESSION['user_id']
]);
