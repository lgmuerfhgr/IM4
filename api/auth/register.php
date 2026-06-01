<?php

/*********************************************************
* api/auth/register.php
* - Prüft, ob die Anfrage per POST gesendet wurde
* - Liest und validiert Name, E-Mail und Passwort
* - Prüft, ob die E-Mail bereits vergeben ist
* - Hasht das Passwort sicher
* - Legt einen neuen Benutzer in der Datenbank an
* - Gibt Erfolgs- oder Fehlermeldungen als JSON zurück

* Verwendete Datenbanktabellen: users
*********************************************************/

session_start();
header('Content-Type: application/json');

require_once '../../system/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$name || !$email || !$password) {
        echo json_encode(["status" => "error", "message" => "Name, email and password are required"]);
        exit;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "Email is already in use"]);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user with name
    $insert = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :pass)");
    $insert->execute([
        ':name'  => $name,
        ':email' => $email,
        ':pass'  => $hashedPassword
    ]);

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
