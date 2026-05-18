<?php
require_once __DIR__ . "/../_bootstrap.php";

$data = getJsonInput();

$boxSerialId = trim($data["box_serial_id"] ?? "");
$figureSerialId = trim($data["figure_serial_id"] ?? "");

if ($boxSerialId === "" || $figureSerialId === "") {
    http_response_code(400);
    echo json_encode(["error" => "box_serial_id und figure_serial_id sind erforderlich"]);
    exit;
}

$maxCachedStoriesPerBox = 20;

try {
    $pdo->beginTransaction();

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

    if (empty($box["user_id"])) {
        throw new RuntimeException("Box ist noch mit keinem User verbunden");
    }

    $boxId = (int) $box["id"];
    $userId = (int) $box["user_id"];

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

    $figureId = (int) $figure["id"];
    $animalId = (int) $figure["animal_id"];

    $stmt = $pdo->prepare("
        SELECT id, title, audio_path
        FROM stories
        WHERE animal_id = ?
        ORDER BY RAND()
        LIMIT 1
    ");
    $stmt->execute([$animalId]);
    $story = $stmt->fetch();

    if (!$story) {
        throw new RuntimeException("Keine Story für diese Tierart gefunden");
    }

    $storyId = (int) $story["id"];

    $stmt = $pdo->prepare("
        INSERT INTO sensor_data (box_id, figure_id, story_id, timestamp)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$boxId, $figureId, $storyId]);

    $stmt = $pdo->prepare("
        SELECT id, play_count
        FROM user_story_progress
        WHERE user_id = ?
        AND story_id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId, $storyId]);
    $progress = $stmt->fetch();

    if ($progress) {
        $stmt = $pdo->prepare("
            UPDATE user_story_progress
            SET play_count = play_count + 1
            WHERE id = ?
        ");
        $stmt->execute([(int) $progress["id"]]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO user_story_progress (user_id, story_id, play_count)
            VALUES (?, ?, 1)
        ");
        $stmt->execute([$userId, $storyId]);
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM user_figures
        WHERE user_id = ?
        AND figure_id = ?
        LIMIT 1
    ");
    $stmt->execute([$userId, $figureId]);

    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("
            INSERT INTO user_figures (user_id, figure_id)
            VALUES (?, ?)
        ");
        $stmt->execute([$userId, $figureId]);
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM box_story_cache
        WHERE box_id = ?
        AND story_id = ?
        LIMIT 1
    ");
    $stmt->execute([$boxId, $storyId]);
    $cached = $stmt->fetch();

    if ($cached) {
        $stmt = $pdo->prepare("
            UPDATE box_story_cache
            SET on_device = 1
            WHERE id = ?
        ");
        $stmt->execute([(int) $cached["id"]]);
    } else {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS total
            FROM box_story_cache
            WHERE box_id = ?
            AND on_device = 1
        ");
        $stmt->execute([$boxId]);
        $cacheCount = (int) $stmt->fetch()["total"];

        if ($cacheCount >= $maxCachedStoriesPerBox) {
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

            if ($cacheToRemove) {
                $stmt = $pdo->prepare("
                    UPDATE box_story_cache
                    SET on_device = 0
                    WHERE id = ?
                ");
                $stmt->execute([(int) $cacheToRemove["id"]]);
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO box_story_cache (box_id, story_id, on_device)
            VALUES (?, ?, 1)
        ");
        $stmt->execute([$boxId, $storyId]);
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "story" => [
            "id" => $storyId,
            "title" => $story["title"],
            "audio_path" => $story["audio_path"],
        ],
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);
    echo json_encode(["error" => $e->getMessage()]);
}