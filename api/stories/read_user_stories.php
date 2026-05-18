<?php
require_once __DIR__ . "/../_bootstrap.php";

$userId = requireLogin();

$stmt = $pdo->prepare("
    SELECT
        s.id,
        s.title,
        s.audio_path,
        usp.play_count
    FROM user_story_progress usp
    INNER JOIN stories s ON s.id = usp.story_id
    WHERE usp.user_id = ?
    ORDER BY usp.play_count DESC, s.title ASC
");
$stmt->execute([$userId]);

echo json_encode($stmt->fetchAll());