<?php

/*********************************************************
* api/auth/login.php
* - Session initialisieren, Cookie-Einstellungen auf dem Server sichern
* - Entgegennahme und Validierung von POST-Login-Daten (Email, Passwort)
* - Prüfung der Nutzerexistenz und Passwort-Validierung gegen Datenbank (-> in Tabelle users)
* - Session-Handling bei erfolgreichem Login
* - Rückgabe von JSON-Antworten für Erfolg oder Fehler
*
* Server-seitiger Code: wird auf dem Server ausgeführt (API-Endpunkt)
* Aufgerufen clientseitig in js/login.js; durch ein Client-Login-Formular (login.html)
* verlinkt mit: ../../system/config.php (enthält DB-Konfiguration)
* verwendete Datenbanktabelle: users
*********************************************************/

// login.php
ini_set('session.cookie_httponly', 1);
// ini_set('session.cookie_secure', 1); // if using HTTPS
session_start();
header('Content-Type: application/json');

require_once '../../system/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        echo json_encode(["status" => "error", "message" => "Email and password are required"]);
        exit;
    }

    // Check user in DB
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email']   = $email;

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
