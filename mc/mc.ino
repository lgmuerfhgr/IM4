 /******************************************************************************************
 * Kap. 13: Sende NFC-Tag UID an Server
 * mc.ino
 * Libraries: "Arduino_JSON" by Arduino, "Adafruit_PN532" by Adafruit
 * PN532 im I2C-Modus (Schalter: 1/0)
 * Anschluss:
 *   PN532 SDA <-> ESP32-C6: GPIO 6
 *   PN532 SCL <-> ESP32-C6: GPIO 7
 *   PN532 Vcc <-> ESP32-C6: 3.3V
 *   PN532 GND <-> ESP32-C6: GND
 * Passe serverURL, SSID und Passwort an.
 ******************************************************************************************/

#include <WiFi.h>
#include <HTTPClient.h>
#include <Arduino_JSON.h>
#include <Wire.h>
#include <Adafruit_PN532.h>

// I2C Pins
#define SDA_PIN     6
#define SCL_PIN     7
#define PN532_IRQ   2
#define PN532_RESET 3

Adafruit_PN532 nfc(PN532_IRQ, PN532_RESET, &Wire);

// WLAN & Server
const char* ssid      = "Zyxel_63E9";
const char* pass      = "7f3tj3n4e4p4n3x8";
const char* serverURL = "https://im4.im-hs26.ch/api/load.php";

bool isWlanConnected = false;
int  led = LED_BUILTIN;

// NFC Timing
String lastTagID          = "";
unsigned long tagDetectedMillis  = 0;
unsigned long previousScanMillis = 0;
const long scanInterval  = 250;   // alle 250ms scannen
const long lockDuration  = 5000;  // gleicher Tag erst nach 5s erneut senden

// ─────────────────────────────────────────────
void setup() {
  Serial.begin(115200);
  while (!Serial) delay(100);

  pinMode(led, OUTPUT);
  rgbLedWrite(led, 0, 255, 0);   // rot: noch nicht verbunden

  // NFC initialisieren
  Wire.begin(SDA_PIN, SCL_PIN);
  nfc.begin();
  if (!nfc.getFirmwareVersion()) {
    Serial.println("❌ Kein PN532 gefunden! Bitte Verkabelung prüfen.");
    while (1);
  }
  nfc.SAMConfig();
  Serial.println("NFC-Reader bereit.");

  connectWiFi();
}

// ─────────────────────────────────────────────
void loop() {
  if (!is_wlan_connected()) return;

  unsigned long currentMillis = millis();

  // Alle 250ms nach Tag scannen
  if (currentMillis - previousScanMillis < scanInterval) return;
  previousScanMillis = currentMillis;

  ////////////////////////////////////////////////////////////// NFC Tag auslesen

  uint8_t uid[7];
  uint8_t uidLength;
  bool success = nfc.readPassiveTargetID(PN532_MIFARE_ISO14443A, uid, &uidLength, 30);

  if (!success) return;  // kein Tag aufgelegt → nichts tun

  // UID als HEX-String
  String detectedID = "";
  for (uint8_t i = 0; i < uidLength; i++) {
    if (uid[i] < 0x10) detectedID += "0";
    detectedID += String(uid[i], HEX);
  }
  detectedID.toUpperCase();

  // Nur senden wenn: neuer Tag ODER 5-Sekunden-Sperre abgelaufen
  bool isNewTag      = (detectedID != lastTagID);
  bool lockExpired   = (currentMillis - tagDetectedMillis >= lockDuration);
  if (!isNewTag && !lockExpired) return;

  lastTagID         = detectedID;
  tagDetectedMillis = currentMillis;
  Serial.println("Tag erkannt: " + detectedID);

  ////////////////////////////////////////////////////////////// JSON zusammenbauen

  JSONVar dataObject;
  dataObject["wert"] = detectedID;
  dataObject["device_id"] = "box_001";
  String jsonString = JSON.stringify(dataObject);

  ////////////////////////////////////////////////////////////// HTTP POST an Server

  HTTPClient http;
  http.begin(serverURL);
  http.addHeader("Content-Type", "application/json");
  int httpResponseCode = http.POST(jsonString);

  if (httpResponseCode > 0) {
    Serial.printf("HTTP %d — Response: %s\n", httpResponseCode, http.getString().c_str());
  } else {
    Serial.printf("POST fehlgeschlagen: %d\n", httpResponseCode);
  }
  http.end();
}

// ─────────────────────────────────────────────
void connectWiFi() {
  Serial.printf("\nVerbinde mit WLAN %s", ssid);
  WiFi.begin(ssid, pass);
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 40) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  if (WiFi.status() == WL_CONNECTED) {
    Serial.printf("\nWiFi verbunden — IP: %s\n", WiFi.localIP().toString().c_str());
    rgbLedWrite(led, 255, 0, 0);   // grün
  } else {
    Serial.println("\n❌ WiFi Verbindung fehlgeschlagen!");
  }
}

bool is_wlan_connected() {
  if (WiFi.status() != WL_CONNECTED) {
    if (isWlanConnected) {
      Serial.println("WiFi-Verbindung verloren, reconnect...");
      rgbLedWrite(led, 0, 255, 0);   // rot
      isWlanConnected = false;
    }
    connectWiFi();
    return false;
  }
  return true;
}