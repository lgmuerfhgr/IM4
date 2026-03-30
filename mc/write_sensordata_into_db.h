/******************************************************************************************************
 * write_sensordata_into_db.h
 * - HTTP POST request to server (PHP) in order to save sensordata into database
 * - when the baby starts crying (after a break) a new dataset in the database table will be created
 * - after the baby has stopped crying, this dataset will be completed wth an end timestamp (UPDATE)
 * - this script calls api/sensordata/mc_write_sensordata.php on the server
******************************************************************************************************/


int scream_id = 0;                                // HTTP POST request: entry id from database table will be stored here

// diese Funktion wird 2 mal aufgerufen: wenn das Baby beginnt bzw. aufhört zu weinen -> schreib in DB)
void write_sensordata_into_db(int is_screaming){
    // Serial.println("entering write_sensordata_into_db()");
    JSONVar dataObject;                                      // construct JSON
    dataObject["is_screaming"] = is_screaming;
    dataObject["scream_id"] = scream_id;                
    dataObject["serialnumber"] = SERIAL_NUMBER;                // SERIAL_NUMBER befindet sich in mc.ino

    String jsonString = JSON.stringify(dataObject);
    Serial.print("write_sensordata_into_db.h: jsonString: ");
    Serial.println(jsonString);           

    ////////////////////////////////////////////////////////////// start HTTP connection and perform a POST query
    HTTPClient http;
    http.begin(SERVERURL_WRITE_SENSORDATA);
    http.addHeader("Content-Type", "application/json");
    int httpResponseCode = http.POST(jsonString);                             // httpResponseCode == 200 wenn alles klappt
    // Serial.printf("HTTP Response code: %d\n", httpResponseCode);           // 200 wenn alles klappt
   
    /**************************************
    Bei diesem HTTP POST Request werden in api/sensordata/mc_write_sensordata.php folgende DB-Operationen durchgeführt:
    - start crying:  INSERT INTO sensordata (device_id, starttime) VALUES (:device_id, NOW())
    - stop crying:   UPDATE sensordata SET endtime = NOW() WHERE id = :scream_id
    ***************************************/

    ////////////////////////////////////////////////////////////// process HTTP response
    if (httpResponseCode > 0) {                                               // 200 wenn alles klappt
        String response = http.getString();
        // Serial.println("Response: " + response);                           // z.B. Response: {"status":"success","message":"inserted into db: started screaming","scream_id":"116"}


        // parse JSON response - nur zur Info / logging

        JSONVar myObject = JSON.parse(response);
        if (JSON.typeof(myObject) != "undefined") {
            if (myObject.hasOwnProperty("scream_id")) {
                int received_scream_id = cast_int(myObject["scream_id"]);     // function cast_int() is in helper_functions.h: (eg. "19" -> 19)
                
                // Baby beginnt zu schreien
                if(received_scream_id != scream_id){
                    scream_id = received_scream_id;
                    Serial.printf("db: new scream_id: %d\n", received_scream_id);
                }

                // Baby hört auf zu schreien
                else if(received_scream_id == scream_id){                     // wenn wenn der Server keine neue ID liefert, war die aktuelle Schreiperiode bisher noch nicht abgeschlossen. Jetzt aber.
                    Serial.printf("db: scream ended (scream_id: %d) \n", received_scream_id);
                    Serial.println("------------------------------------");
                }
            }
        } else {
            Serial.println("Response parsing (transmitting sensordata to server) failed");
        }
    } else {
        Serial.printf("Error on sending POST: %d\n", httpResponseCode);
    }
    http.end();
}

