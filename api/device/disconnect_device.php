<?php
/*********************************************************
 * api/device/disconnect_device.php
 * - Trennt eine Box vom eingeloggten User (setzt user_id auf NULL)
 * - Sicherheitscheck: nur eigene Boxen trennbar
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
$deviceId = (int) ($data['device_id'] ?? 0);

if ($deviceId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Ungültige Box-ID.']);
    exit();
}

try {
    // Sicherheitscheck: Box muss diesem User gehören
    $stmt = $pdo->prepare("SELECT id FROM boxes WHERE id = ? AND user_id = ?");
    $stmt->execute([$deviceId, $userId]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Box nicht gefunden oder gehört nicht dir.']);
        exit();
    }

    $stmt = $pdo->prepare("UPDATE boxes SET user_id = NULL WHERE id = ? AND user_id = ?");
    $stmt->execute([$deviceId, $userId]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Datenbankfehler: ' . $e->getMessage()]);
}
?>