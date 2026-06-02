<?php

/*********************************************************
* api/profile/update_profile.php
* - Aktualisiert den Benutzernamen in users
* - Empfängt JSON-Daten mit name
* - Gibt JSON-Antwort zurück
*
* verwendete Datenbanktabellen: users
*********************************************************/

header('Content-Type: application/json');
include_once '../../system/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->name) || trim($data->name) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Name is required']);
    exit();
}

$name = trim($data->name);

try {
    $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
    $stmt->execute([$name, $_SESSION['user_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Name updated successfully'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error updating name: ' . $e->getMessage()
    ]);
}

?>