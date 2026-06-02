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
- die Fantasie und Neugier von Kindern fördern,
- auditive und haptische Sinneserfahrungen kombinieren,
- Eltern bei einer bewussten Medienerziehung unterstützen,
- sowie gemeinsame Familienmomente durch Geschichten und Interaktion stärken.
- Durch die Verbindung von physischen Schleich-Tierfiguren mit Audioinhalten entsteht eine intuitive IoT-Anwendung, die Lernen, Spielen und Storytelling altersgerecht kombiniert.

### UX & Konzeption

* **Figma:** [https://www.figma.com/design/jE9s2LsLBBLJzRlK5c1EQX/Designs?node-id=0-1&t=Dn9kSZ3bHQu5CE9I-1]
* **User Flow \+ Screen Flow** [figma.com/board/zzdbwM68T6rEWDDPCfGAzx/User_Flow_Guess_the_animal?t=7AGZWCf2vAZ6Ks6F-0]

* *Welche Features waren angedacht?*
Die Grundidee des Projekts war einen Art tiptoi Spiel zu kreieren mit Kindergeschichten.  


* *Welche Features wurden nicht umgesetzt? (Warum)*
Beim Design haben wir zunächst mehrere zusätzliche Seiten und Funktionen geplant. Im Verlauf des Prozesses entschieden wir uns jedoch, einige davon wieder zu entfernen, da wir das Projekt dadurch einfacher, verständlicher und benutzerfreundlicher gestalten konnten. Dazu gehörten beispielsweise spezielle „Herzliche Glückwunsch“-Seiten.
Ausserdem war ursprünglich vorgesehen, die Geschichten direkt auf der Box abzuspielen und dort zu speichern, sodass die Nutzung vollständig ohne Bildschirm möglich wäre. Aufgrund der technischen Komplexität sowie begrenzter Speicher- und Kapazitätsmöglichkeiten liess sich diese Idee jedoch nicht sinnvoll umsetzen. Deshalb entschieden wir uns für eine vereinfachte Lösung: Die Geschichten werden nun direkt über die Web-App abgespielt. Dadurch weicht das Endprodukt zwar teilweise von der ursprünglichen Idee einer komplett bildschirmfreien Kinderaktivität ab, ermöglicht aber eine realistischere und technisch umsetzbare Umsetzung des Projekts.

### Setup

* **WebApp:** [https://im4.im-hs26.ch/]
* **Video-Dokumentation:** [Link zum Video auf Youtube](http://link.zum.video) 

#### Installationsanleitung WebApp

***verständliche** Schritt-für-Schritt-Anleitung für Aussenstehende, um das Projekt zu klonen und auf einem eigenen Server zu installieren*

1. *Was benötige ich an Infrastruktur?*  
Infomaniak Webhosting
phpMyAdmin
GitHub Repository
Visual Studio Code
Browser (Chrome, Safari, Google) auf Laptop oder Handy

2. *Was muss ich auf meinem Webserver installieren?*  
??

3. *Wie kann ich die Datenbank importieren?* 
In phpMyAdmin eine neue Datenbank erstellen. Unter "Importieren" die Datei 612bjf_im4.sql auswählen und importieren.
Alle Tabellen (animals, boxes, figures, sensordata, stories, users, user_story_progress) werden angelegt. 

4. *Wo muss ich die DB-Credentials eintragen?*  
Bei VisualStudio die Datei “system/config.php” öffnen und folgende Werte anpassen:

DB_HOST → Datenbankserver (z.B. localhost oder 612bjf.myd.infomaniak.com)
DB_NAME → Name der Datenbank
DB_USER → Datenbankbenutzer
DB_PASS → Datenbankpasswort

5. *Audio-Dateien*  
Audio-Dateien (.mp3) in den Ordner audio/ auf dem Server ablegen. Dateinamen müssen mit den Einträgen in der stories.audio_path Spalte auf der Datenbank übereinstimmen.

6. *Wie nehme ich das physische Artefakt in Betrieb?*
??
Box mit User-Profil verknüpfen: Profil-Seite öffnen → Box-Code eingeben → Verknüpfen.
Tierfigur auf Box stellen → Geschichte erscheint automatisch im Browser.

## technische Details

// Hier sollte das Verständnis ersichtlich sein / Wie stehen die Dateien in Beziehung zueinander, Wie reden Die Dateien miteinander, Wie ist der Weg der Daten

* **Projektstruktur / Code-Struktur:** \[*Hinweis: Der Code selbst muss im Repository liegen und im Kopfbereich jeder Datei eine kurze Zusammenfassung enthalten.*\]  

Schleich/
│
├── index.html              ← Hauptseite: Sensordata - Alle freigeschalteten Stories des Users
├── login.html              ← Startseite: Konto erstellen / anmelden
├── loginpage.html          ← Login-Formular
├── profile.html            ← Profil, Box verbinden, Logout
├── register.html           ← Registrierungs-Formular
├── story.html              ← Unterseite: Story mit Titel, Audio

│
├── js/
│   ├── index.js            ← lädt/sortiert freigeschaltete Geschichten nach play_count
│   ├── login.js            ← Login-Formular absenden
│   ├── register.js         ← Registrierung absenden
│   ├── story.js            ← Story laden, Titel und Audio
│   └── profile.js          ← Profil laden, Geräte verwalten, Logout
│
├── css/
│   ├── design.css          ← Farben und responsivness
│   ├── style.css           ← Hauptstyles (Layout, Typografie, Charts)
│   ├── nav.css             ← Bottom-Navigation & Profil-Shortcut
│   ├── login_register.css  ← Styles für Login/Register-Seiten
│   ├── profile.css         ← Styles für Profil-Seite
│   └── scoreboard.css      ← Tabellen-Styles
│
├── api/                    ← ⭐ Alle Backend-Endpoints (geben JSON zurück)
│   ├── auth/
│   │   ├── auth.php        ← Session prüfen ("Bin ich eingeloggt?")
│   │   ├── login.php       ← Login verarbeiten
│   │   ├── register.php    ← Registrierung verarbeiten
│   │   └── logout.php      ← Session zerstören
│   ├── device/
│   │   ├── connect_device.php     ← Gerät mit Code verbinden
│   │   ├── disconnect_device.php  ← Gerät trennen
│   │   └── list_devices.php        ← Geräte des Users auflisten
│   ├── profile/
│   │   ├── read_profile.php        ← Profildaten laden
│   │   └── update_profile.php      ← Namen ändern
│   ├── tracks/
│   │   ├── read_tracks.php        ← Alle Tracks mit Auswahl laden
│   │   └── update_selected_tracks.php ← Track-Auswahl ändern
│   └── sensordata/
│       ├── read_sensordata.php        ← Sensordata laden (wann hat das Baby geweint?)

│
├── system/
│   ├── config.php.blank    ← Vorlage für DB-Konfiguration
│   ├── config.php          ← Echte DB-Zugangsdaten (gitignored!)
│   └── setup.sql           ← Datenbank-Schema + Seed-Daten
│
└── assets/
    └── background.jpg      ← Hintergrundbild für Login/Register
```


* **Datenschnittstelle: \[***zwischen WebApp und Physical Computing*\]  
- NFC-Figur wird auf Box gestellt → Microcontroller erkennt NFC-Tag 
- Hardware schreibt Eintrag in Tabelle sensordata (figure_id + device_id + Zeitstempel) 
- Browser pollt alle 3 Sekunden api/sensor/poll_story.php
- PHP prüft: gibt es neuen sensordata-Eintrag für eine Box dieses Users?
- figure_id → figures → animal_id → zufällige Story aus stories
- play_count in user_story_progress wird um 1 erhöht (oder neu angelegt)
- Story-Daten werden als JSON an den Browser zurückgegeben
- Audio-Player Overlay erscheint automatisch und spielt die Geschichte ab

* **ERM:** \[*Erklärung und Schaubild*\]  
Hauptbeziehungen:
users → boxes (1:n, ein User hat mehrere Boxen)
animals → figures (1:n, ein Tier hat mehrere Figuren)
animals → stories (1:n, ein Tier hat mehrere Geschichten)
sensordata → figures (n:1, via figure_id = serial_id)
sensordata → boxes (n:1, via device_id = serial_id)
users ↔ stories (n:m via user_story_progress, mit play_count)

* **Authentifizierung:** \[*Erklärung*\]
Session-basiert (PHP Sessions mit session_start() in config.php)
Login speichert user_id in $_SESSION
Jeder API-Endpoint prüft isset($_SESSION['user_id']), sonst HTTP 401
Browser prüft via api/auth/auth.php – bei 401 Redirect zu login.html
Logout: session_destroy() + Redirect

## Known bugs
* Was funktioniert noch nicht einwandfrei? 
Autoplay blockiert: Browser blockieren Audio-Autoplay ohne vorherige User-Interaktion. Player erscheint, User muss manuell Play drücken.

* Was könnte noch verbessert werden?
 Polling-Delay: 3-Sekunden-Intervall bedeutet bis zu 3 Sek. Verzögerung nach NFC-Kontakt. Es gäbe vielleicht andere Lösungsansätze, wie ohne Verzögerung nach NFC Kontakt, eine Geschichte abgespielt wird


## Umsetzungsprozess

* **Reflexion / Erfahrung / Lernfortschritt:** *Was haben wir gelernt? Würden wir es nochmal genauso machen? Was war gut, was war schlecht?*  


Lernfortschritt: Datenbank Verständnis. Intensiv damit auseinandergesetzt, welche Tabellen für dieses Projekt nötig sind und warum und welche Verknüpfungen sie untereinander haben müssen.


* **Herausforderungen & Lösungen:** \[*Verworfene Ansätze, Fehler, Umplanungen*\]  



* **KI-Einsatz:** *Dokumentation der verwendeten KI-Tools und deren Nutzen (KI ist nicht verboten)*  
Claude wurde eingesetzt für: Struktur-Entscheidungen der Datenbank, Hilfe bei PHP und Javascript und Debugging.
Konkret generiert mit KI: poll_story.php, index.js (Polling + Overlay), auto-player.css, connect_device.php, disconnect_device.php, read_profile.php.

Wir haben jeweils viel Zeit in das Prompten investiert, und dabei genau überlegt: Wie funktioniert unsere Website? Wie steht sie in Verbindung mit der DB, und wann muss wo was abgefragt werden. Danach war der KI-Einsatz sehr hilfreich, weil es uns ermöglichte, schnell und effizient zu coden.


* **Fazit:** …



## Inhaltsverzeichnis

1. [Architektur-Überblick](#1-architektur-überblick)
2. [Warum Frontend und Backend trennen?](#2-warum-frontend-und-backend-trennen)
3. [Authentication (Login-System) erklärt](#3-authentication-login-system-erklärt)
4. [Projektstruktur](#4-projektstruktur)
5. [Datenbank-Schema](#5-datenbank-schema)
6. [API-Referenz](#6-api-referenz)
7. [Frontend: Wie die Seiten funktionieren](#7-frontend-wie-die-seiten-funktionieren)
8. [Installation](#8-installation)
9. [Troubleshooting](#9-troubleshooting)

---


### 3.1 Die Grundidee: Sessions & Cookies


1. Du loggst dich ein (Email + Passwort)
2. Der Server erstellt eine Session (eine Art Gedächtnis) und speichert deine User-ID darin
3. Der Server schickt dir ein Session-Cookie (eine kleine ID) zurück
4. Bei jedem weiteren Request schickt dein Browser dieses Cookie automatisch mit
5. Der Server liest das Cookie, findet die Session, und weiss wieder wer du bist
```

**Der Ablauf:**

```
User öffnet index.html
  └─► JavaScript ruft checkAuth() auf
       └─► fetch("api/auth/auth.php") mit Session-Cookie
            └─► PHP prüft: Gibt es user_id in der Session?
                 ├─► JA  → 200 OK + User-Daten als JSON → Seite wird geladen
                 └─► NEIN → 401 Unauthorized → JavaScript leitet zu login.html weiter
```

### 3.5 Logout

Beim Logout wird die Session serverseitig zerstört:

**Frontend (`profile.js`):**

```javascript
async function logout() {
  await fetch("api/auth/logout.php");
  window.location.href = "login.html";
}
```

**Backend (`api/auth/logout.php`):**

```php
session_start();
$_SESSION = [];       // Alle Session-Daten löschen
session_destroy();    // Session komplett zerstören
```

### 3.6 Zusammenfassung Auth-Flow

```
                    ┌──────────────┐
                    │  register.   │
                    │  html        │
                    └──────┬───────┘
                           │ POST name, email, password
                           ▼
                    ┌──────────────┐
                    │  register.   │──► password_hash() ──► INSERT INTO users
                    │  php         │
                    └──────┬───────┘
                           │ Erfolg → Redirect
                           ▼
                    ┌──────────────┐
                    │  login.      │
                    │  html        │
                    └──────┬───────┘
                           │ POST email, password
                           ▼
                    ┌──────────────┐
                    │  login.      │──► password_verify() ──► Session setzen
                    │  php         │
                    └──────┬───────┘
                           │ Erfolg → Redirect
                           ▼
              ┌────────────────────────────┐
              │  Geschützte Seiten         │
              │  (index, settings, profile)│
              │                            │
              │  checkAuth() bei jedem     │
              │  Seitenaufruf              │
              └────────────┬───────────────┘
                           │ Logout
                           ▼
                    ┌──────────────┐
                    │  logout.php  │──► session_destroy()
                    └──────────────┘
```

---

## 4. Projektstruktur

```


### Die API-Ordnerstruktur folgt einem Muster:

```
api/{feature}/{action}.php
```

Beispiele:

- `api/auth/login.php` → Feature: Auth, Action: Login
- `api/tracks/read.php` → Feature: Tracks, Action: Lesen
- `api/device/connect_device.php` → Feature: Gerät, Action: Verbinden

---

## 5. Datenbank-Schema

Die App nutzt **MySQL/MariaDB** mit folgenden Tabellen:

```
┌──────────────┐       ┌──────────────────┐       ┌──────────────┐
│    users     │       │  user_has_device  │       │   devices    │
├──────────────┤       ├──────────────────┤       ├──────────────┤
│ id (PK)      │◄──────│ user_id (FK)     │       │ id (PK)      │
│ email        │       │ device_id (FK)   │──────►│ device_code  │
│ password     │       └──────────────────┘       └──────┬───────┘
│ name         │                                         │
└──────────────┘                                         │
                                                         │
                       ┌──────────────────┐              │
                       │  device_tracks   │              │
                       ├──────────────────┤              │
                       │ device_id (FK)   │──────────────┘
                       │ track_id (FK)    │──────────────┐
                       └──────────────────┘              │
                                                         │
                       ┌──────────────────┐       ┌──────┴───────┐
                       │  sensordata      │       │   tracks     │
                       ├──────────────────┤       ├──────────────┤
                       │ id (PK)          │       │ id (PK)      │
                       │ device_id (FK)   │       │ title        │
                       │ starttime        │       └──────────────┘
                       │ endtime          │
                       └──────────────────┘
```

### Tabellen im Detail:

| Tabelle           | Zweck                                                     | Wichtige Spalten                                       |
| ----------------- | --------------------------------------------------------- | ------------------------------------------------------ |
| `users`           | Benutzerkonten                                            | `email` (unique), `password` (gehashter Wert!), `name` |
| `devices`         | Physische Babyphone-Geräte                                | `device_code` (unique, steht auf dem Gerät)            |
| `user_has_device` | Welcher User hat welches Gerät (many-to-many)             | `user_id`, `device_id`                                 |
| `tracks`          | Verfügbare Beruhigungssongs                               | `title`                                                |
| `device_tracks`   | Welche Tracks auf welchem Gerät aktiv sind (many-to-many) | `device_id`, `track_id`                                |
| `sensordata`      | Wann hat das Baby geweint?                                | `device_id`, `starttime`, `endtime`                    |

### Warum Zwischentabellen (Junction Tables)?

`user_has_device` und `device_tracks` sind **Zwischentabellen** für Many-to-Many-Beziehungen:

- Ein User kann **mehrere** Geräte haben
- Ein Gerät kann **mehreren** Usern gehören (z.B. Mutter + Vater)
- Ein Gerät kann **mehrere** Tracks haben
- Ein Track kann auf **mehreren** Geräten aktiv sein

---

## 6. API-Referenz

Alle Endpoints befinden sich unter `api/` und geben **JSON** zurück. Geschützte Endpoints prüfen die Session und geben `401` zurück, wenn der User nicht eingeloggt ist.

### Authentication

| Endpoint                | Methode | Geschützt | Beschreibung                  |
| ----------------------- | ------- | --------- | ----------------------------- |
| `api/auth/register.php` | POST    | Nein      | Neuen Account erstellen       |
| `api/auth/login.php`    | POST    | Nein      | Einloggen (Session starten)   |
| `api/auth/auth.php`     | GET     | Ja        | Prüfen ob eingeloggt          |
| `api/auth/logout.php`   | GET     | Ja        | Ausloggen (Session zerstören) |

### Geräte

| Endpoint                           | Methode | Geschützt | Beschreibung               |
| ---------------------------------- | ------- | --------- | -------------------------- |
| `api/device/list.php`              | GET     | Ja        | Geräte des Users auflisten |
| `api/device/connect_device.php`    | POST    | Ja        | Gerät per Code verbinden   |
| `api/device/disconnect_device.php` | POST    | Ja        | Gerät trennen              |

### Profil

| Endpoint                 | Methode | Geschützt | Beschreibung               |
| ------------------------ | ------- | --------- | -------------------------- |
| `api/profile/read.php`   | GET     | Ja        | Profildaten + Geräte laden |
| `api/profile/update.php` | POST    | Ja        | Namen ändern               |

### Tracks (Playlist)

| Endpoint                                | Methode | Geschützt | Beschreibung                  |
| --------------------------------------- | ------- | --------- | ----------------------------- |
| `api/tracks/read.php`                   | GET     | Ja        | Alle Tracks mit Auswahlstatus |
| `api/tracks/update_selected_tracks.php` | POST    | Ja        | Track-Auswahl ändern          |

### Sensordata - wann hat das Baby geeint?

| Endpoint                             | Methode | Geschützt | Beschreibung     |
| ------------------------------------ | ------- | --------- | ---------------- |
| `api/sensordata/read_sensordata.php` | GET     | Ja        | Sensordata laden |

### Beispiel-Requests

**Login:**

```javascript
const response = await fetch("api/auth/login.php", {
  method: "POST",
  headers: { "Content-Type": "application/x-www-form-urlencoded" },
  body: new URLSearchParams({ email: "anna@test.ch", password: "123456" }),
});
const result = await response.json();
// → { "status": "success" }
```

**Track-Auswahl ändern (JSON-Body):**

```javascript
const response = await fetch("api/tracks/update_selected_tracks.php", {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({ track_id: 3, selected: 1 }),
});
```

> Beachte: Auth-Endpoints nutzen `application/x-www-form-urlencoded` (wie ein normales HTML-Formular), während andere Endpoints `application/json` nutzen. Beides funktioniert - es ist einfach eine Konvention.

---

## 7. Frontend: Wie die Seiten funktionieren

### Allgemeines Pattern

Jede geschützte Seite folgt dem gleichen Muster:

```javascript
// 1. Auth prüfen
async function loadPage() {
  const isAuthorized = await checkAuth();
  if (!isAuthorized) return; // → Redirect zu login.html

  // 2. Daten von der API laden
  const response = await fetch("api/..../read.php");
  const data = await response.json();

  // 3. Daten ins HTML rendern
  data.forEach((item) => {
    const row = document.createElement("tr");
    row.innerHTML = `<td>${item.title}</td>`;
    tbody.appendChild(row);
  });
}

// 4. Beim Laden der Seite aufrufen
document.addEventListener("DOMContentLoaded", loadPage);
```

### Seitenübersicht

| Seite           | Zweck                         | API-Calls                                                                                                                                                     |
| --------------- | ----------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `login.html`    | Anmeldung                     | `POST api/auth/login.php`                                                                                                                                     |
| `register.html` | Registrierung                 | `POST api/auth/register.php`                                                                                                                                  |
| `index.html`    | Sensordata (Charts + Tabelle) | `GET api/auth/auth.php`, `GET api/sensordata/read.php`                                                                                                        |
| `settings.html` | Playlist verwalten            | `GET api/auth/auth.php`, `GET api/tracks/read.php`, `POST api/tracks/update_selected_tracks.php`                                                              |
| `profile.html`  | Profil, Geräte, Logout        | `GET api/auth/auth.php`, `GET api/profile/read.php`, `POST api/device/connect_device.php`, `POST api/device/disconnect_device.php`, `GET api/auth/logout.php` |

### Chart.js für Diagramme

Die Sensordata-Seite (`index.html`) nutzt [Chart.js](https://www.chartjs.org/) um zwei Balkendiagramme zu rendern:

- **Heulzeit nach Tag** - Wie viele Minuten pro Tag geweint wurde
- **Heulen nach Uhrzeit** - Zu welcher Tageszeit am meisten geweint wird

Die Daten werden per API geladen und mit JavaScript in Chart.js-kompatible Strukturen transformiert.

---

## 8. Installation

### 1. Repository klonen

```bash
git clone <repository-url>
```

### 2. Datenbank einrichten

- Erstelle eine neue MySQL/MariaDB-Datenbank bei deinem Hoster (z.B. [Infomaniak](https://www.infomaniak.com/de/support/faq/1981/mysqlmariadb-benutzer-und-datenbanken-verwalten)).
- Importiere `system/setup.sql` in die Datenbank - das erstellt alle Tabellen und fügt Standard-Tracks ein.

### 3. Konfiguration

- Kopiere `system/config.php.blank` und benenne die Kopie in `system/config.php` um.
- Trage deine Datenbank-Zugangsdaten ein:

```php
$host = 'localhost';        // DB-Host (bei Infomaniak anders)
$db   = 'meine_datenbank'; // Name deiner Datenbank
$user = 'mein_user';       // DB-Benutzername
$pass = 'mein_passwort';   // DB-Passwort
```

> `config.php` ist in `.gitignore` eingetragen und wird **nicht** ins Repository gepusht. So bleiben deine Zugangsdaten privat.

### 4. Hochladen

- Lade alle Dateien per FTP/SFTP auf deinen Webserver hoch.
- Erstelle eine FTP-Verbindung gemäss [Anleitung im MMP 101](https://github.com/Interaktive-Medien/101-MMP/blob/main/resources/sftp.md).

### 5. Testen

- Öffne die Seite im Browser → du solltest auf `login.html` landen.
- Erstelle einen Account über `register.html`.
- Logge dich ein → du landest auf `index.html`.

---

## 9. Troubleshooting

- **Login funktioniert nicht nach Datei-Verschiebung:** Cache im Browser löschen oder in einem privaten Tab testen. PHP-Sessions können bei Pfadänderungen Probleme machen.
- **Datenbank-Fehler:** Prüfe die Zugangsdaten in `system/config.php`. Nutze `system/test_connection.php` um die Verbindung zu testen.
- **Keine Daten auf der Hauptseite:** Verbinde zuerst ein Gerät auf der Profilseite (beliebigen Code eingeben) und erstelle dann Demo-Daten über den Button auf der Hauptseite.
- **401 Unauthorized bei API-Calls:** Stelle sicher, dass `credentials: "include"` bei fetch-Requests gesetzt ist, wenn Frontend und Backend auf verschiedenen Domains laufen.
