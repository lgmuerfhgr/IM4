<?php
/*********************************************************
 * api/device/connect_device.php
 * - Empfängt serial_id per POST (JSON)
 * - Prüft ob diese Box in der DB existiert
 * - Prüft ob sie bereits vergeben ist
 * - Verknüpft die Box mit dem eingeloggten User (user_id)
 * - Gibt serial_id bei Erfolg zurück, Fehlermeldung sonst
 *
 * verwendete Datenbanktabellen: boxes
 *********************************************************/

header('Content-Type: application/json');
include_once '../../system/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Bitte zuerst einloggen.']);
    exit();
}

$userId = (int) $_SESSION['user_id'];
$data = getJsonInput();

// trim() entfernt auch \r\n – nötig wegen dirty data in der DB
$serial_id = trim($data['serial_id'] ?? '');

if ($serial_id === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Kein Box-Code angegeben.']);
    exit();
}

try {
    // TRIM() im SQL damit auch DB-seitige \r\n-Einträge matchen
    $stmt = $pdo->prepare("SELECT id, serial_id, user_id FROM boxes WHERE TRIM(serial_id) = ?");
    $stmt->execute([$serial_id]);
    $box = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$box) {
        http_response_code(404);
        echo json_encode(['error' => 'Kein Gerät mit diesem Code gefunden.']);
        exit();
    }

    // Box gehört bereits diesem User → als Erfolg behandeln
    if ((int)$box['user_id'] === $userId) {
        echo json_encode(['success' => true, 'serial_id' => trim($box['serial_id'])]);
        exit();
    }

    // Box ist bereits einem anderen User zugewiesen → Konflikt
    if (!empty($box['user_id'])) {
        http_response_code(409);
        echo json_encode(['error' => 'Diese Box ist bereits mit einem anderen Profil verbunden.']);
        exit();
    }

    // Box ist frei → verknüpfen
    $stmt = $pdo->prepare("UPDATE boxes SET user_id = ? WHERE id = ?");
    $stmt->execute([$userId, $box['id']]);

    echo json_encode(['success' => true, 'serial_id' => trim($box['serial_id'])]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
?>