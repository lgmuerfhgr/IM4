## Kurzbeschreibung des Projekts

* **Modul:** Interaktive Medien 4 an der Fachhochschule Graubünden (FS26)  
* **Themenfeld:** IoT-Applikation zum Thema Eltern mit kleinen Kindern  
* **Name des Projekts:** \[*Schleich*\]   
* **Team Physical Computing:** \[*Nathalie Tschanz Kaya Moser*\]   
* **Team WebApp:** \[*Elena Fankhauser Lou Gmür*\]
 
 
* Welches Problem im Alltag von Eltern mit kleinen Kindern wird gelöst? 
Viele Eltern von 2–4-jährigen Kindern stehen im Alltag vor der Herausforderung, ihre Kinder sinnvoll zu beschäftigen, ohne dabei ständig auf Bildschirme zurückzugreifen. Gleichzeitig fehlt oft die Zeit oder Energie, immer neue Geschichten, Spiele oder Lernangebote bereitzustellen.
Unser Projekt "Schleich" löst dieses Problem, indem es eine spielerische, interaktive und bildschirmfreie Beschäftigungsmöglichkeit bietet. Kinder können mit physischen Tierfiguren Geschichten, Geräusche und Lerninhalte entdecken, während Eltern eine sichere und altersgerechte Alternative zu Tablet oder Smartphone erhalten.
Zusätzlich unterstützt das System Eltern dabei, Medienkonsum bewusster zu gestalten und gemeinsame Rituale wie Vorlesen, Einschlafen oder spielerisches Lernen einfacher in den Alltag zu integrieren.

* Was ist der „Sinn und Zweck“ des Systems?
Der Sinn und Zweck des Systems ist es, digitale Interaktion mit haptischem Spielen zu verbinden und dadurch eine kindgerechte, kreative und sensorische Lernerfahrung zu schaffen.
Das System soll:
- die Fantasie und Neugier von Kindern fördern
- auditive und haptische Sinneserfahrungen kombinieren
- Eltern bei einer bewussten Medienerziehung unterstützen
- sowie gemeinsame Familienmomente durch Geschichten und Interaktion stärken
- Durch die Verbindung von physischen Schleich-Tierfiguren mit Audioinhalten entsteht eine intuitive IoT-Anwendung, die Lernen, Spielen und Storytelling altersgerecht kombiniert

### UX & Konzeption

* **Figma:** [https://www.figma.com/design/jE9s2LsLBBLJzRlK5c1EQX/Designs?node-id=0-1&t=Dn9kSZ3bHQu5CE9I-1]
* **User Flow \+ Screen Flow** [figma.com/board/zzdbwM68T6rEWDDPCfGAzx/User_Flow_Guess_the_animal?t=7AGZWCf2vAZ6Ks6F-0]

* *Welche Features waren angedacht?*
Die Grundidee des Projekts war einen Art tiptoi Spiel zu kreieren mit Kindergeschichten.  


* *Welche Features wurden nicht umgesetzt? (Warum)*
Beim Design haben wir zunächst mehrere zusätzliche Seiten und Funktionen geplant. Im Verlauf des Prozesses entschieden wir uns jedoch, einige davon wieder zu entfernen, da wir das Projekt dadurch einfacher, verständlicher und benutzerfreundlicher gestalten konnten. Dazu gehörten beispielsweise spezielle „Herzliche Glückwunsch“-Seiten. Ausserdem war ursprünglich vorgesehen, die Geschichten direkt auf der Box abzuspielen und dort zu speichern, sodass die Nutzung vollständig ohne Bildschirm möglich wäre. Aufgrund der technischen Komplexität sowie begrenzter Speicher- und Kapazitätsmöglichkeiten liess sich diese Idee jedoch nicht sinnvoll umsetzen. Deshalb entschieden wir uns für eine vereinfachte Lösung: Die Geschichten werden nun direkt über die Web-App abgespielt. Dadurch weicht das Endprodukt zwar teilweise von der ursprünglichen Idee einer komplett bildschirmfreien Kinderaktivität ab, ermöglicht aber eine realistischere und technisch umsetzbare Umsetzung des Projekts.

### Setup

* **WebApp:** [https://im4.im-hs26.ch/]
* **Video-Dokumentation:** [Link zum Video auf Youtube](http://link.zum.video) 

#### Installationsanleitung WebApp

***verständliche** Schritt-für-Schritt-Anleitung für Aussenstehende, um das Projekt zu klonen und auf einem eigenen Server zu installieren*

1. *Was benötige ich an Infrastruktur?*  
- Infomaniak Webhosting (oder vergleichbarer Hoster mit PHP 7.4+ und MariaDB)
- phpMyAdmin (ist bei Infomaniak bereits inklusive)
- GitHub-Account + Git oder GitHub Desktop zum Klonen des Repositories
- Visual Studio Code (oder ein anderer Code-Editor)
- Browser (Chrome, Firefox oder Safari) auf Laptop oder Handy

2. *Was muss ich auf meinem Webserver installieren?*  
Bei Infomaniak muss nichts manuell installiert werden – PHP und MariaDB sind bereits vorinstalliert.
Folgendes wird benötigt und ist bei Infomaniak inklusive:
- PHP 7.4 oder höher
- MariaDB / MySQL
- phpMyAdmin
- FTP-Zugang (z.B. via FileZilla) zum Hochladen der Dateien

3. *Wie kann ich die Datenbank importieren?* 
  1. In phpMyAdmin einloggen und eine neue Datenbank erstellen
  2. Die neu erstellte Datenbank anklicken.
  3. Oben auf den Tab **„Importieren"** klicken.
  4. Datei `system/612bjf_im4.sql` auswählen und importieren.
  5. Folgende Tabellen werden automatisch angelegt:
    - `animals` – Tierarten
    - `boxes` – physische Boxen
    - `figures` – NFC-Tierfiguren
    - `sensordata` – Kontakt-Events der Tierfiguren mit der Box
    - `stories` – Geschichten
    - `users` – Benutzerkonten
    - `user_story_progress` – Abspielhistorie pro User

4. *Wo muss ich die DB-Credentials eintragen?*  
Bei VisualStudio die Datei system/config.php öffnen und folgende Werte anpassen:
```php
$host = 'localhost';        // DB-Host (bei Infomaniak anders)
$db   = 'meine_datenbank'; // Name deiner Datenbank
$user = 'mein_user';       // DB-Benutzername
$pass = 'mein_passwort';   // DB-Passwort
```
> ⚠️ **Wichtig:** `config.php` in der `.gitignore` Datei eintragen, damit die Zugangsdaten nicht auf GitHub hochgeladen werden!

So geht's:
  Datei `.gitignore` im Projektordner öffnen (oder neu erstellen).
  Folgende Zeile hinzufügen:
  ```
  system/config.php
  ```

5. *Audio-Dateien*  
  1. Alle `.mp3` Dateien in den Ordner `audio/` auf dem Server hochladen (via FTP/FileZilla).
  2. Die Dateinamen müssen **exakt** mit den Einträgen in der Datenbankspalte `stories.audio_path` übereinstimmen – inklusive Gross-/Kleinschreibung.

6. *Wie nehme ich das physische Artefakt in Betrieb?*
?? MÜSSEN WIR NOCH AUSFÜLLEN
Box mit User-Profil verknüpfen: Profil-Seite öffnen → Box-Code eingeben → Verknüpfen.
Tierfigur auf Box stellen → Geschichte erscheint automatisch im Browser.

#### Bauanleitung Physical Computing

* ***Was muss ich wie bauen, verbinden, installieren?***  
* *ergänze: **Komponentenplan** (betrifft Physical Computing, vgl. Slides Kapitel 15): Schaubild enthält*  
  * *die eingesetzten Komponenten*  
  * *die verbundenen Sensoren und Aktoren*  
  * *die Programme (mit Dateinamen)*  
  * *die Kommunikationswege*  
* *ergänze: **Steckplan** (betrifft Physical Computing, vgl. Slides Kapitel 15): generiert z.B. mit Fritzing (empfohlen), Tinkercad, Wokwi*  
  * *beachtet die [Fritzing Parts](https://github.com/Interaktive-Medien/im_physical_computing/tree/main/15_Intro_Projektdoku) extra für euch*  
* *ggf. **Bildmaterial***


## Technische Details

* **Projektstruktur / Code-Struktur:** 

Schleich/
│
├── index.html              ← Hauptseite: alle freigeschalteten Stories des Users
├── login.html              ← Startseite: Konto erstellen / anmelden
├── loginpage.html          ← Login-Formular
├── profile.html            ← Profil, Box verbinden, Logout
├── register.html           ← Registrierungs-Formular
├── story.html              ← Unterseite: Story mit Titel und Audio
│
├── js/
│   ├── index.js            ← lädt und sortiert freigeschaltete Geschichten nach play_count
│   ├── login.js            ← Login-Formular absenden
│   ├── register.js         ← Registrierung absenden
│   ├── story.js            ← angeklickte Story laden mit Titel und Audio
│   └── profile.js          ← Profil laden, Box verwalten, Logout
│
├── css/
│   ├── auto-player.css     ← Styles für den automatisch erscheinenden Audio-Player
│   ├── design.css          ← Allgemeine Farben und Responsivness
│   ├── login.css           ← Styles für login.html
│   ├── loginpage.css       ← Styles für loginpage.html
│   ├── profile.css         ← Styles für profile.html
│   ├── register.css        ← Styles für register.html
│   └── style.css           ← Styles für index.html
│
├── api/
│   ├── auth/
│   │   ├── auth.php               ← Session prüfen ("Bin ich eingeloggt?")
│   │   ├── login.php              ← Login verarbeiten
│   │   ├── register.php           ← Registrierung verarbeiten
│   │   └── logout.php             ← Session zerstören
│   ├── device/
│   │   ├── connect_device.php     ← Box mit User verbinden
│   │   └── disconnect_device.php  ← Box von User trennen
│   ├── profile/
│   │   ├── read_profile.php       ← Profildaten laden
│   │   └── update_profile.php     ← Namen ändern
│   ├── sensor/
│   │   └── poll_story.php         ← alle 3 Sek. DB-Abfrage nach neuem Eintrag in sensordata
│   └── stories/
│       ├── read_story.php         ← lädt einzelne Story für story.html
│       └── read_user_stories.php  ← lädt alle freigeschalteten Stories, sortiert nach Wiedergabe
│
├── system/
│   ├── config.php          ← DB-Zugangsdaten (gitignored!)
│   └── 612bjf_im4.sql      ← Datenbank-Struktur und Testdaten
│
├── assets/
│   └── Tier-Illustrationen, Menü-Icons
│
└── audio/
    └── alle .mp3 Audiodateien der Stories

* **Datenschnittstelle: \[***zwischen WebApp und Physical Computing*\]  
Physical Computing: 
  1. NFC-Figur wird auf Box gestellt → Microcontroller erkennt NFC-Tag
  2. Hardware schreibt direkt einen Eintrag in die Tabelle `sensordata`:
    - `figure_id` → Seriennummer der NFC-Figur
    - `device_id` → Seriennummer der Box
    - `zeit` → Zeitstempel (automatisch)NFC-Figur wird auf Box gestellt → Microcontroller erkennt NFC-Tag 
WebApp: 
  3. Browser pollt alle 3 Sekunden `api/sensor/poll_story.php`
  4. PHP prüft: gibt es einen neuen `sensordata`-Eintrag für eine Box dieses Users?
  5. `figure_id` → `figures` → `animal_id` → zufällige Story aus `stories`
  6. `play_count` in `user_story_progress` wird um 1 erhöht (oder neu angelegt)
  7. Story-Daten werden als JSON an den Browser zurückgegeben
  8. Audio-Player Overlay erscheint automatisch und spielt die Geschichte ab

* **ERM:** \[*Erklärung und Schaubild*\]  
Hauptbeziehungen:
users → boxes (1:n, ein User hat mehrere Boxen)
users →  stories (n:m, ein User hat mehrere Stories)
animals → figures (1:n, ein Tier hat mehrere physische Figuren)
animals → stories (1:n, ein Tier hat mehrere Geschichten)

* **Authentifizierung:** \[*Erklärung*\]
Session-basiert via PHP. Nach dem Login wird die `user_id` in `$_SESSION` gespeichert. Jeder API-Endpoint prüft dies – fehlt die Session, kommt HTTP 401 zurück und der Browser leitet zu `login.html` weiter.

  1. `index.html` lädt → JavaScript ruft `checkAuth()` auf
  2. `fetch("api/auth/auth.php")` wird mit Session-Cookie gesendet
  3. PHP prüft ob `user_id` in der Session existiert
    - **JA** → User-Daten als JSON → Seite wird geladen
    - **NEIN** → HTTP 401 → Redirect zu `login.html`

  **Ablauf beim Login:**
  1. User gibt Email + Passwort ein
  2. Server prüft Passwort, erstellt Session und speichert `user_id`
  3. Browser erhält ein Session-Cookie (kleine ID)
  4. Bei jedem weiteren Request schickt der Browser dieses Cookie automatisch mit
  5. Server liest Cookie → findet Session → weiss wer eingeloggt ist

  Logout: `session_destroy()` löscht die Session serverseitig → Redirect zu `login.html`

## Known Bugs

* **Autoplay blockiert:**
Browser blockieren Audio-Autoplay ohne vorherige User-Interaktion (Sicherheitsmassnahme der Browser). Der Player erscheint zwar automatisch, der User muss aber manuell auf Play drücken.

* **Polling-Delay:**
Das 3-Sekunden-Intervall bedeutet bis zu 3 Sekunden Verzögerung nach NFC-Kontakt. Eine mögliche Verbesserung wäre Server-Sent Events (SSE): Der Server schickt dabei aktiv eine Nachricht an den Browser sobald ein neuer Eintrag in `sensordata` erscheint – ohne Verzögerung und ohne wiederholte Abfragen.

## Umsetzungsprozess

* **Lernfortschritt:**
Das WebApp Team
  Datenbankverständnis: Wir haben uns intensiv damit auseinandergesetzt, welche Tabellen für das Projekt nötig sind und  warum, und welche Verknüpfungen sie untereinander haben müssen. Ausserdem haben wir gelernt, wie eine WebApp mit einer Datenbank kommuniziert – via PHP API-Endpoints – und wie Session-basierte Authentifizierung funktioniert.
Das Physical Computing Team
  MUSS NOCH ERGÄNZT WERDEN 

* **Herausforderungen & Lösungen:** \[*Verworfene Ansätze, Fehler, Umplanungen*\]  
WebApp Team
  Die Datenbankstruktur war eine echte Herausforderung: Die komplexe Idee musste auf das Wesentliche heruntergebrochen werden. Das hat Zeit gekostet, hat sich aber gelohnt. Die finale Struktur ist klar und nachvollziehbar. Durch dieses Verständnis konnten wir unser Projekt präziser beschreiben und bessere Prompts für die KI erstellen, was uns im weiteren Verlauf viel Zeit gespart hat.

Das Physical Computing Team
  MUSS NOCH ERGÄNZT WERDEN 

Allgemein
  Es war  ursprünglich vorgesehen, die Geschichten direkt auf einer Musikbox abzuspielen und dort zu speichern, sodass die Nutzung vollständig ohne Bildschirm möglich ist. Doch wegen der Komplexität, entschieden wir uns für eine vereinfachte Lösung über die WebApp.

* **KI-Einsatz:** *Dokumentation der verwendeten KI-Tools und deren Nutzen (KI ist nicht verboten)*  
Das WebApp Team
  Wir haben jeweils viel Zeit in das Prompten investiert, und dabei genau überlegt: Wie funktioniert unsere Website? Wie steht sie in Verbindung mit der DB, und wann muss wo was abgefragt werden? Danach war der KI-Einsatz sehr hilfreich, weil es uns ermöglichte, schnell und effizient zu coden.
  Wir setzten Claude ein für Struktur-Entscheidungen der Datenbank, Hilfe bei PHP und Javascript und Debugging. Konkret generierten wir mit KI: poll_story.php, index.js (Polling + Overlay), auto-player.css, connect_device.php, disconnect_device.php, read_profile.php

* **Fazit:** …
Der Einstieg war etwas holprig. Doch die Entscheidung, die Geschichten auf der Website abzuspielen anstelle über eine physische Box, erleichterte unsere Arbeit enorm. Mithilfe von Claude und genauem Prompten ging es im Gesamten gut voran. Natürlich gab es Auf und Abs und etliche Debuggings, aber im Grossen und Ganzen sind wir happy mit dem Endresultat und würden beim nächsten Mal ähnlich vorgehen.