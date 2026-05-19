#line 1 "/Users/nathalietschanz/Desktop/01_Studium/Fächer/Semester-4/IM4/Schleich/IM4/mc/password_hochschule.h"
/**********************************************************************************************************
 * password_hochschule.h
 * - Hier befinden sich die Zugangsdaten für das WPA2-Enterprise-verschlüsselte Netzwerk, zB. eduroam an der Hochschule
 * - Diese Datei ist geheim und muss in .gitignore aufgenommen werden
 **********************************************************************************************************/ 


#pragma once

// Werte werden eingebunden in connect_hochschule.h

const char *ssid = "MMP_MediaApp";
#define EAP_IDENTITY "fiessjan@fhgr.ch"    
const char *EAP_PASSWORD = "mypassword";