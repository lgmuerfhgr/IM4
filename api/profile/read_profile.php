<?php
/*********************************************************
 * api/profile/read_profile.php
 * - Liest Benutzername aus users
 * - Listet verbundene Boxen aus boxes
 * - Gibt alle Daten als JSON zurück
 *
 * verwendete Datenbanktabellen:
 * users, boxes, user_figures, figures, animals
 *********************************************************/

header('Content-Type: application/json');
include_once '../../system/config.php';

// session_start() wird bereits in config.php aufgerufen – nicht nochmals aufrufen

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    // Benutzerdaten laden
    $stmt = $pdo->prepare("SELECT id, email, name FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userInfo) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit();
    }

    // Alle Boxen des Users laden
    $stmt = $pdo->prepare("SELECT id, serial_id FROM boxes WHERE user_id = ? ORDER BY id ASC");
    $stmt->execute([$userId]);
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'user'    => $userInfo,
        'devices' => $devices,
        'figures' => $figures
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>