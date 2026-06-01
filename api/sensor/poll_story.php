<?php
/*********************************************************
 * api/sensor/poll_story.php
 *
 * Wird alle paar Sekunden vom eingeloggten User aufgerufen.
 *
 * Ablauf:
 *  1. User muss eingeloggt sein
 *  2. Alle Boxen des Users werden geladen
 *  3. In sensordata wird gesucht ob es einen NEUEN Eintrag gibt
 *     (neuer als der zuletzt gesehene, gespeichert in $_SESSION)
 *     der zu einer dieser Boxen gehört
 *  4. figure_id → animal_id → zufällige Story mit dieser animal_id
 *  5. user_story_progress wird angelegt oder play_count erhöht
 *  6. Story-Daten werden zurückgegeben
 *
 * verwendete Tabellen:
 *   boxes, sensordata, figures, stories, user_story_progress
 *********************************************************/

header('Content-Type: application/json');
include_once '../../system/config.php';

// Auth
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit();
}

$userId = (int) $_SESSION['user_id'];

try {
    // 1. Alle serial_ids der Boxen des Users holen
    $stmt = $pdo->prepare("SELECT serial_id FROM boxes WHERE user_id = ?");
    $stmt->execute([$userId]);
    $boxes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($boxes)) {
        // Kein Box verbunden → kein Trigger möglich
        echo json_encode(['story' => null]);
        exit();
    }

    // 2. Letzten gesehenen sensordata-Eintrag aus Session holen
    //    Beim ersten Aufruf: aktueller höchster ID-Stand als Baseline setzen
    if (!isset($_SESSION['last_seen_sensor_id'])) {
        $stmt = $pdo->query("SELECT COALESCE(MAX(id), 0) FROM sensordata");
        $_SESSION['last_seen_sensor_id'] = (int) $stmt->fetchColumn();
        echo json_encode(['story' => null]);
        exit();
    }

    $lastSeenId = (int) $_SESSION['last_seen_sensor_id'];

    // 3. Neueste sensordata-Einträge suchen die neuer sind als last_seen
    //    und zu einer Box dieses Users gehören
    $placeholders = implode(',', array_fill(0, count($boxes), '?'));

    $stmt = $pdo->prepare("
        SELECT sd.id, sd.figure_id
        FROM sensordata sd
        WHERE sd.id > ?
          AND sd.device_id IN ($placeholders)
        ORDER BY sd.id DESC
        LIMIT 1
    ");
    $stmt->execute(array_merge([$lastSeenId], $boxes));
    $sensor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sensor) {
        // Kein neuer Eintrag
        echo json_encode(['story' => null]);
        exit();
    }

    // last_seen aktualisieren damit dieselbe Auslösung nicht wiederholt wird
    $_SESSION['last_seen_sensor_id'] = (int) $sensor['id'];

    $figureSerialId = $sensor['figure_id'];

    // 4a. figure_id (serial_id) → animal_id
    $stmt = $pdo->prepare("SELECT animal_id FROM figures WHERE serial_id = ?");
    $stmt->execute([$figureSerialId]);
    $figure = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$figure) {
        // Figur unbekannt → ignorieren
        echo json_encode(['story' => null]);
        exit();
    }

    $animalId = (int) $figure['animal_id'];

    // 4b. Alle Stories mit dieser animal_id holen und eine zufällig wählen
    $stmt = $pdo->prepare("SELECT id, title, intro, audio_path, animal_id FROM stories WHERE animal_id = ?");
    $stmt->execute([$animalId]);
    $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($stories)) {
        echo json_encode(['story' => null]);
        exit();
    }

    $story = $stories[array_rand($stories)];
    $storyId = (int) $story['id'];

    // 5. user_story_progress: play_count erhöhen oder neuen Eintrag anlegen
    $stmt = $pdo->prepare("
        INSERT INTO user_story_progress (user_id, story_id, play_count)
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE play_count = play_count + 1
    ");
    $stmt->execute([$userId, $storyId]);

    // 6. Story-Daten zurückgeben
    echo json_encode(['story' => $story]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>