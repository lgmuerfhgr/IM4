/******************************************************************************************************
 * mc.ino
 * - measure audio volume (dB) with INMP441 microphone
 * - after x seconds audio level over threshold:
 * - play a random audio track + stop it as soon as noise is over
 * - you can select the audio tracks of your choice in the db. The selection will be played randomnloy
 * - create an entry into database table with timestamp + another timestamp as soon as noise is over
 *
 * microphone INMP441: Connect ...
 * INMP441: VDD  <->  ESP32-C6: 3.3V 
 * INMP441: GND  <->  ESP32-C6: GND 
 * INMP441: SD   <->  ESP32-C6: GPIO 13 
 * INMP441: SCK  <->  ESP32-C6: GPIO 2 
 * INMP441: WS   <->  ESP32-C6: GPIO 23 
 * INMP441: L/R  <->  ESP32-C6: GND 
 *
 * Audio player: Connect ...
 * audio player: Vin  <->  ESP32-C6: 5V 
 * audio player: GND  <->  ESP32-C6: GND  (per npn transistor)
 * audio player: RX   <->  ESP32-C6: GPIO6
 * audio player: TX   <->  ESP32-C6: GPIO7
 * basis transistor   <->  ESP32-C6: GPIO5
 *
 * Install Library "Arduino_JSON" by Arduino
 *
 * You probably need to shift the value of AUDIOVOLUME_OFFSET in get_audiovolume.h 
 * define AUDIOVOLUME_THRESHOLD, from which audio level is_screaming should be 1
 * For testing use a smartphone decible tester like https://apps.apple.com/ch/app/dezibel-x-dba-l%C3%A4rm-messger%C3%A4t/id448155923?l=de-DE
 ******************************************************************************************************/

// modify settings below
const String SERVERURL_GET_SELECTED_TRACKS ="https://nestfunk.hausmaenner.ch/api/tracks/mc_get_selected_tracks.php";
const String SERVERURL_WRITE_SENSORDATA ="https://nestfunk.hausmaenner.ch/api/sensordata/mc_write_sensordata.php";
const int SERIAL_NUMBER = 1;        // Seriennummer fest eincodiert, sollte bei jedem Gerät anders sein. used in write_sensordata_into_db.h
#define AUDIOVOLUME_THRESHOLD 80    // used in loop() of mc.ino | application triggers from this volume on (dB)
  


#include <HTTPClient.h>
#include <Arduino_JSON.h> 
#include "helper_functions.h"
#include "update_selected_tracks_from_db.h"
#include "write_sensordata_into_db.h"

////////////////////////////////////////////////////////////// WLAN + Server
#include <WiFi.h>
// #include "connectWiFi_hochschule.h"                 // activate this line when aonnecting with edu network (eg. eduroam)
#include "connectWiFi_zuhause.h"                       // activate this line when connecting with home network

////////////////////////////////////////////////////////////// audio trigger
#include "get_audiovolume.h"
#include "audioplayer.h"
int is_screaming = 0;
int prev_is_screaming = 0;
unsigned long audiotrigger_startTime = 0;
bool audio_already_started_playing = false;

void setup() {
  Serial.begin(115200);
  delay(3000);
  setup_audiovolume_tester();                                    // function is in get_audiovolume.h
  initAudioPlayer();                                             // function is in audioplayer.h
  init_audio_history_array();                                    // Initialize audio history array (buffer) with 0: if the audio volume > the threshold during 60% of the last x seconds --> bridging breaks. 
  digitalWrite(BUILTIN_LED, 0);
  Serial.println("Start WLAN connection...");
  connectWiFi();                                                 // connectWiFi() is in connectWiFi_hochschule.h AND connectWiFi_zuhause.h. Activate on top
  
  if (WiFi.status() == WL_CONNECTED) {
    update_selected_tracks();                                    // fetch selected tracks from database. // function is in update_selected_tracks_from_db.h
  }
  Serial.print("serialnumber: ");
  Serial.println("SERIAL_NUMBER");
  Serial.println("------------------------------------");
  Serial.println("selected tracks");

  for (int i = 0; i < num_selected_tracks; i++) {                // num_selected_tracks will be set in update_selected_tracks_from_db.h
    Serial.print(selected_tracks_ids[i]);                        // selected_tracks_ids in update_selected_tracks_from_db.h
    Serial.println(selected_tracks_titles[i]);                   // selected_tracks_titles in update_selected_tracks_from_db.h
  }
  Serial.println("------------------------------------");
}


void loop(){
    float audiovolume = get_audiovolume();                       // audiotrigger
    int current_noise_detected = audiovolume > AUDIOVOLUME_THRESHOLD? 1:0;
    is_screaming = isMostlyLoud(current_noise_detected);         // function in helper_functions() // 50% LOGIC: is_heulsession = 1 if the audio volume > the threshold during 70% of the last x seconds --> bridging breaks

    ////////////////////////////////////////////////////////////// 3 options of signal interpretation:
    ///// case 1: audio trigger just detected
    if (is_screaming == 1 && prev_is_screaming == 0) {
        audiotrigger_startTime = millis();                       // remember start time
        audio_already_started_playing = false;                             
        digitalWrite(BUILTIN_LED, 1);                            // turn LED on for feedback
        // Serial.println("Baby started screaming");
    }

    ///// case 2: audio trigger has been detected already before and is still active -> play audio if mic detects loud noise long enough without interrupt and save in db
    if (is_screaming == 1 && !audio_already_started_playing) {
        if (millis() - audiotrigger_startTime >= 2500) {         // warte noch ein paar Sekunden, bis Musik losgeht und DB Eintrag getätigt wird.
            Serial.println("permanent audio started");
            int next_track_nr = getRandomTrackId();    
            // Serial.println(next_track_nr);
            playTrack(next_track_nr);                            // in audioplayer.h  |  playTrack() vs. stopTrack()

            String next_track_title = getRandomTrackName();
            // Serial.print("Next track title: ");
            // Serial.println(next_track_title);
            audio_already_started_playing = true;  
            write_sensordata_into_db(is_screaming);              // in write_sensordata_into_db.h
        }
    }

    ///// case 3: audio trigger is not being detected anymore.
    if (is_screaming == 0 && prev_is_screaming == 1) {
        digitalWrite(BUILTIN_LED, 0);                            // turn LED off
        Serial.println("permanent audio ended");
        write_sensordata_into_db(is_screaming);                  // in write_sensordata_into_db.h
        stopTrackAfterDelay(5000);                               // in audioplayer.h  |  stopTrack() vs. playTrack()
        audio_already_started_playing = false; 
    }

    prev_is_screaming = is_screaming;

    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi-Verbindung verloren, reconnect...");
        rgbLedWrite(RGB_BUILTIN, 0, 10, 0);                      // rot (GRB)
        connectWiFi();
    }
    else{  // WL_CONNECTED
        if(is_screaming == 0){
          rgbLedWrite(RGB_BUILTIN, 10, 0, 0);                    // grün (GRB)
        }
    }
}

