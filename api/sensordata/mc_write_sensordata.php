<?php

 /*************************************************************
 * mc_write_sensordata.php
 * - receive data as a JSON string from the mc on the server 
 * - insert into database (-> Tabelle sensordata)
 
 * Server-seitiger Code: wird auf dem Server ausgeführt
 * Aufgerufen clientseitig am ESP32 (mc.ino)
 * verwendete Datenbanktabellen: sensordata
 *************************************************************/

include_once '../../system/config.php';
header('Content-Type: application/json'); // sets Content-Type of the answer to JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    ###################################### receive JSON data
    
    $inputJSON = file_get_contents('php://input'); // JSON-Daten aus dem Body der Anfrage
    $input = json_decode($inputJSON, true); // Dekodieren der JSON-Daten in ein Array
    
    $is_screaming = $input["is_screaming"];
    $scream_id = $input["scream_id"];   
    $serialnumber = $input["serialnumber"];   
    // $device_id = 1;   
    
    try{ 
        // baby started screaming -> create new entry in sensordata with starttime = NOW() and return the id of the new entry (scream_id) to the mc
        if ($is_screaming == 1){
            $sql = "INSERT INTO sensordata (device_id, starttime)
                SELECT id, NOW()
                FROM devices
                WHERE device_code = :serialnumber;";

                // wenn die Seriennummer "1" ist, wird in die Spalte device_id der Tabelle sensordata evtl. eine andere id (z. B. 5) eingetragen, wenn die Seriennummer in der Tabelle devices bei der id = 5 hinterlegt ist.

            $result = $pdo->prepare($sql);
            $result->execute([':serialnumber' => $serialnumber]);
            $scream_id = $pdo->lastInsertId();    // lastInserId() liefert die ID des zuletzt eingefügten Datensatzes
            echo json_encode([
                "status" => "success", 
                "message" => "inserted into db: started screaming", 
                "scream_id" => $scream_id
            ]);
        }
        else if ($is_screaming == 0){
            $sql = "UPDATE sensordata SET endtime = NOW() WHERE id = :scream_id";
            $result = $pdo->prepare($sql);
            $result->execute(['scream_id' => $scream_id]);
            echo json_encode([
                "status" => "success", 
                "message" => "inserted into db: stopped screaming", 
                "scream_id" => $scream_id
            ]);
        }
    } 
    
    catch (Exception $e) {
        echo "Database error: " . $e->getMessage();
        exit;
    }
}
?>