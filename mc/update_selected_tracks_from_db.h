/******************************************************************************************************
 * update_selected_tracks_from_db.h
 * - HTTP POST request to server (PHP) to get the tracks selected by the user for this device
 * - the db query will be performed only once at start.
 * - selected tracks will be stored in an array and will be played back in random order when the baby starts crying
 * - this script calls api/tracks/mc_get_selected_tracks.php on the server
******************************************************************************************************/



// called on setup() function, once at start: select the songs that should be played (GET Request)
int selected_tracks_ids[15];                                   // es können auch weniger als 15 Tracks ausgewählt sein. 15 ist eben die maximale Grösse
String selected_tracks_titles[15];
int num_selected_tracks = 0;
int randomTrackIndex;

void update_selected_tracks(){                                  // called in mc.ino
    HTTPClient http;
    http.begin(SERVERURL_GET_SELECTED_TRACKS);                  // mc_get_selected_tracks.php: dort wird eine Datenbankabfrage gemacht: SELECT t.id, t.title FROM tracks t JOIN device_tracks dt ON t.id = dt.track_id WHERE dt.device_id = :device_id;";
    JSONVar requestObj;
    requestObj["serialnumber"] = SERIAL_NUMBER;                 // constant in mc.ino
    String jsonString = JSON.stringify(requestObj);
    http.addHeader("Content-Type", "application/json");         // Header setzen: Wir sagen dem Server, dass wir JSON senden
    int httpResponseCode = http.POST(jsonString);               // sollte 200 (OK) sein

    if (httpResponseCode > 0) {
        String response = http.getString();                     // Get the response payload as a string    // z.B. [{"id":12,"title":"Sympathy for the devil"},{"id":13,"title":"Under the bridge"}]
        // Serial.println("Response from api/tracks/mc_get_selected_tracks.php: " + response);            

        JSONVar myObject = JSON.parse(response);
        if (JSON.typeof(myObject) == "undefined") {
            Serial.println("Response parsing (fetching track selection from mc_get_selected_tracks.php) failed");
        } else {
            for (int i = 0; i < 15 && i < myObject.length(); i++) {       // Access the "selected" field of each object
                selected_tracks_ids[i] = (int)myObject[i]["id"];
                selected_tracks_titles[i] = (String)myObject[i]["title"];
                num_selected_tracks++;
                
                // Serial.print("Track ");
                // Serial.print((int)myObject[i]["id"]);
                // Serial.println(myObject[i]["title"]);
            }
        }
    } else {
        Serial.print("Error on sending GET (update_selected_tracks_from:db): ");
        Serial.println(httpResponseCode);
    }
    http.end();
}

// Pick a random track id (1-15) to play it
int getRandomTrackId() {
    randomTrackIndex = random(0, num_selected_tracks); 
    return selected_tracks_ids[randomTrackIndex];
}

String getRandomTrackName() {
    String randomTrackName = selected_tracks_titles[randomTrackIndex]; 
    Serial.print("randomTrackName: ");
    Serial.println(randomTrackName);
    return randomTrackName;
}
