<?php
/*****************************************************
 * Kapitel 12: Website2DB > Schritt 2: Website -> DB
 * load.php
 * JSON-Daten vom MC empfangen und in die Datenbank einfügen
 ****************************************************/

require_once("../system/config.php");

###################################### Empfangen der JSON-Daten

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

###################################### Werte auslesen

$wert      = $input["wert"]      ?? null;   // NFC Tag UID
$device_id = $input["device_id"] ?? null;   // Geräte-ID (optional)

###################################### Ein einziger INSERT mit beiden Werten

$sql  = "INSERT INTO sensordata (figure_id, device_id) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$wert, $device_id]);

?>
