/******************************************************************************************************
 * helper_functions.h
 * - Hilfsfunktionen für Datentypen, Audio-Glättung und Zeitsteuerung
 ******************************************************************************************************/

// --- 1. DATEN-KONVERTIERUNG ---
int cast_int(JSONVar idValue) {
    if (JSON.typeof(idValue) == "string") {
        String temp = (const char*)idValue;
        return temp.toInt();
    } 
    else if (JSON.typeof(idValue) == "number") {
        return (int)idValue;
    }
    return 0;
}

// --- 2. AUDIO-SMOOTHING (RINGPUFFER) ---
#define BUFFER_SIZE_SMOOTH 40 
int heul_history[BUFFER_SIZE_SMOOTH];
int history_index = 0;
unsigned long last_history_update = 0;
int last_result = 0; // Merker für den geglätteten Zustand

void init_audio_history_array() {
    for(int i = 0; i < BUFFER_SIZE_SMOOTH; i++) {
        heul_history[i] = 0;
    }
}

int isMostlyLoud(int current_noise_detected) {
    // Nur alle 100ms den Puffer aktualisieren
    if (millis() - last_history_update >= 100) {
        last_history_update = millis();
        
        heul_history[history_index] = current_noise_detected;
        history_index = (history_index + 1) % BUFFER_SIZE_SMOOTH;

        int count_ones = 0;
        for (int i = 0; i < BUFFER_SIZE_SMOOTH; i++) {
            if (heul_history[i] == 1) count_ones++;
        }

        // Logik: Wenn mehr als 50% der Werte im 4s-Fenster "laut" waren
        if (count_ones >= (BUFFER_SIZE_SMOOTH * 0.5)) {
            last_result = 1;
        } else {
            last_result = 0;
        }
    }
    // Immer den letzten berechneten Status zurückgeben
    return last_result;
}
