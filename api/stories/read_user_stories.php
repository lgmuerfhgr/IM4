<?php

header('Content-Type: application/json');
include_once '../../system/config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit();
}

try {
    $query = "
        SELECT
            s.id,
            s.title,
            s.audio_path,
            usp.play_count
        FROM user_story_progress usp
        INNER JOIN stories s ON s.id = usp.story_id
        WHERE usp.user_id = ?
        ORDER BY usp.play_count DESC, s.title ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);

    $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stories);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

?>