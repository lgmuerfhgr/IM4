<?php
require_once __DIR__ . "/../_bootstrap.php";

$userId = requireLogin();
$storyId = (int) ($_GET["id"] ?? 0);

if ($storyId <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Ungültige Story-ID"]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        s.id,
        s.title,
        s.audio_path,
        usp.play_count
    FROM user_story_progress usp
    INNER JOIN stories s ON s.id = usp.story_id
    WHERE usp.user_id = ?
    AND s.id = ?
    LIMIT 1
");
$stmt->execute([$userId, $storyId]);
$story = $stmt->fetch();

if (!$story) {
    http_response_code(404);
    echo json_encode(["error" => "Geschichte nicht gefunden oder noch nicht freigeschaltet"]);
    exit;
}

echo json_encode($story);