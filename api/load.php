@ -1,26 +0,0 @@
<?php
/*****************************************************
 * Kapitel 12: Website2DB > Schritt 2: Website -> DB
 * load.php
 * JSON-Daten vom MC empfangen und in die Datenbank einfügen
 * JSON-Body vom ESP32 empfangen
 * wert (Tag-UID) und device_id auslesen
 * Beides per INSERT in Tabelle sensordata schreiben
 * figure_id = Tag-UID, device_id = z.B. "box_001"
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