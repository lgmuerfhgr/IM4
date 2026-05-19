<?php
// JSON-Header und DB-Verbindung laden
header('Content-Type: application/json');
include_once '../../system/config.php';

// Session starten und Auth prüfen
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit();
}

// Eingeloggten User holen und Story-ID aus URL lesen
$userId = $_SESSION['user_id'];
$storyId = (int) ($_GET["id"] ?? 0);

// Ungültige ID abfangen
if ($storyId <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Ungültige Story-ID"]);
    exit;
}

// Story laden – nur wenn sie dem eingeloggten User gehört
$stmt = $pdo->prepare("
    SELECT
        s.id,
        s.title,
        s.intro,
         s.animal_id,
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

// Story nicht gefunden oder kein Zugriff
if (!$story) {
    http_response_code(404);
    echo json_encode(["error" => "Geschichte nicht gefunden oder noch nicht freigeschaltet"]);
    exit;
}

// Story als JSON zurückgeben
echo json_encode($story);