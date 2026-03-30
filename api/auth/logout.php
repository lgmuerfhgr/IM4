<?php

/*********************************************************
* /api/auth/logout.php
* - Startet eine Session
* - Löscht alle Session-Daten
* - Beendet die Session (Logout)

* Server-seitiger Code: wird auf dem Server ausgeführt
* Aufgerufen clientseitig in js/profile.js; durch ein Client-Login-Formular (profile.html)
*********************************************************/

// logout.php
session_start();
$_SESSION = [];
session_destroy();

exit;
?>