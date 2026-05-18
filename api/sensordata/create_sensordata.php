<?php
header('Content-Type: application/json');

// Lädt die Datenbankverbindung.
include_once '../../system/config.php';

session_start();

// Prüft, ob ein User angemeldet ist.
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit();
}

$data = getJsonInput();

// Liest die Seriennummer der Box aus den Eingabedaten. trim() entfernt unnötige Leerzeichen am Anfang und Ende.
$boxSerialId = trim($data["box_serial_id"] ?? "");

// Liest die Seriennummer der Figur aus den Eingabedaten.
$figureSerialId = trim($data["figure_serial_id"] ?? "");

// Prüft, ob beide benötigten Werte vorhanden sind.
if ($boxSerialId === "" || $figureSerialId === "") {
    http_response_code(400);
    echo json_encode(["error" => "box_serial_id und figure_serial_id sind erforderlich"]);
    exit;
}

// Definiert, wie viele Stories maximal pro Box als espeichert werden dürfen.
$maxCachedStoriesPerBox = 50;

try {
    $pdo->beginTransaction();

    // Sucht die Box anhand ihrer Seriennummer.
    $stmt = $pdo->prepare("
        SELECT id, user_id
        FROM boxes
        WHERE serial_id = ?
        LIMIT 1
    ");
    $stmt->execute([$boxSerialId]);
    $box = $stmt->fetch();

    if (!$box) {
        throw new RuntimeException("Box nicht gefunden");
    }

    // Prüft, ob die Box bereits einem User zugeordnet ist.
    if (empty($box["user_id"])) {
        throw new RuntimeException("Box ist noch mit keinem User verbunden");
    }

    // Speichert die Box-ID und User-ID als Integer für die weitere Verarbeitung.
    $boxId = (int) $box["id"];
    $userId = (int) $box["user_id"];

    // Sucht die Figur anhand ihrer Seriennummer.
    $stmt = $pdo->prepare("
        SELECT id, animal_id
        FROM figures
        WHERE serial_id = ?
        LIMIT 1
    ");
    $stmt->execute([$figureSerialId]);
    $figure = $stmt->fetch();

    if (!$figure) {
        throw new RuntimeException("Figur nicht gefunden");
    }

    // Speichert die Figur-ID und die zugehörige Tierart-ID.
    $figureId = (int) $figure["id"];
    $animalId = (int) $figure["animal_id"];

    // Wählt zufällig eine Story aus, die zur Tierart der Figur gehört.
    $stmt = $pdo->prepare("
        SELECT id, title, audio_path
        FROM stories
        WHERE animal_id = ?
        ORDER BY RAND()
        LIMIT 1
    ");
    $stmt->execute([$animalId]);
    $story = $stmt->fetch();

    // Falls es für diese Tierart keine Story gibt, wird ein Fehler ausgelöst.
    if (!$story) {
        throw new RuntimeException("Keine Story für diese Tierart gefunden");
    }

    // Speichert die ausgewählte Story-ID.
    $storyId = (int) $story["id"];

    // Speichert den erkannten Sensor-Event: welche Box, welche Figur, welche Story und zu welchem Zeitpunkt.
    $stmt = $pdo->prepare("
        INSERT INTO sensor_data (box_id, figure_id, story_id, timestamp)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$boxId, $figureId, $storyId]);

    // Prüft, ob für diesen User und diese Story bereits ein Fortschrittseintrag existiert.
    $stmt = $pdo->prepare("
        SELECT id, play_count
        FROM user_story_progress
        WHERE user_id = ?
        AND story_id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId, $storyId]);
    $progress = $stmt->fetch();

    // Falls bereits ein Fortschrittseintrag existiert, wird die Anzahl der Abspielvorgänge um 1 erhöht.
    if ($progress) {
        $stmt = $pdo->prepare("
            UPDATE user_story_progress
            SET play_count = play_count + 1
            WHERE id = ?
        ");
        $stmt->execute([(int) $progress["id"]]);
    } else {
        // Falls noch kein Fortschrittseintrag existiert, wird ein neuer Eintrag mit play_count = 1 erstellt.
        $stmt = $pdo->prepare("
            INSERT INTO user_story_progress (user_id, story_id, play_count)
            VALUES (?, ?, 1)
        ");
        $stmt->execute([$userId, $storyId]);
    }

    // Prüft, ob diese Figur bereits dem User zugeordnet ist.
    $stmt = $pdo->prepare("
        SELECT id
        FROM user_figures
        WHERE user_id = ?
        AND figure_id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId, $figureId]);

    // Falls die Figur dem User noch nicht zugeordnet ist, wird diese Verbindung neu gespeichert.
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO user_figures (user_id, figure_id)
            VALUES (?, ?)
        ");
        $stmt->execute([$userId, $figureId]);
    }

    // Prüft, ob diese Story bereits im Cache der Box existiert.
    $stmt = $pdo->prepare("
        SELECT id
        FROM box_story_cache
        WHERE box_id = ?
        AND story_id = ?
        LIMIT 1
    ");
    $stmt->execute([$boxId, $storyId]);
    $cached = $stmt->fetch();

    // Falls die Story bereits im Cache existiert, wird sie als auf dem Gerät vorhanden markiert.
    if ($cached) {
        $stmt = $pdo->prepare("
            UPDATE box_story_cache
            SET on_device = 1
            WHERE id = ?
        ");
        $stmt->execute([(int) $cached["id"]]);
    } else {

        // Falls die Story noch nicht im Cache existiert, wird zuerst gezählt, wie viele Stories aktuell auf dieser Box gespeichert sind.
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM box_story_cache
            WHERE box_id = ?
            AND on_device = 1
        ");
        $stmt->execute([$boxId]);
        $cacheCount = (int) $stmt->fetch()["total"];

        // Wenn die maximale Anzahl gespeicherter Stories erreicht ist, muss eine bestehende Story vom Gerät entfernt werden.
        if ($cacheCount >= $maxCachedStoriesPerBox) {

            // Sucht die Story, die am seltensten abgespielt wurde. Bei gleicher Abspielzahl wird der ältere Cache-Eintrag gewählt.
            $stmt = $pdo->prepare("
                SELECT bsc.id
                FROM box_story_cache bsc
                LEFT JOIN user_story_progress usp
                    ON usp.story_id = bsc.story_id
                    AND usp.user_id = ?
                WHERE bsc.box_id = ?
                AND bsc.on_device = 1
                ORDER BY COALESCE(usp.play_count, 0) ASC, bsc.id ASC
                LIMIT 1
            ");
            $stmt->execute([$userId, $boxId]);
            $cacheToRemove = $stmt->fetch();

            // Falls ein Cache-Eintrag gefunden wurde, wird dieser als nicht mehr auf dem Gerät vorhanden markiert.
            if ($cacheToRemove) {
                $stmt = $pdo->prepare("
                    UPDATE box_story_cache
                    SET on_device = 0
                    WHERE id = ?
                ");
                $stmt->execute([(int) $cacheToRemove["id"]]);
            }
        }

        // Fügt die neue Story in den Cache der Box ein, und markiert sie als auf dem Gerät vorhanden.
        $stmt = $pdo->prepare("
            INSERT INTO box_story_cache (box_id, story_id, on_device)
            VALUES (?, ?, 1)
        ");
        $stmt->execute([$boxId, $storyId]);
    }

    // Speichert alle Änderungen dauerhaft, da bis hierhin kein Fehler aufgetreten ist.
    $pdo->commit();

    // Gibt eine erfolgreiche JSON-Antwort mit den Story-Daten zurück.
    echo json_encode([
        "success" => true,
        "story" => [
            "id" => $storyId,
            "title" => $story["title"],
            "audio_path" => $story["audio_path"],
        ],
    ]);
} catch (Throwable $e) {
    // Falls während der Verarbeitung ein Fehler auftritt,
    // wird die Transaktion zurückgerollt.
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Gibt den Fehler als JSON-Antwort zurück.
    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}