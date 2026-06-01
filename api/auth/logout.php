<?php

/*********************************************************
* /api/auth/logout.php
* - Startet eine Session
* - Löscht alle Session-Daten
* - Beendet die Session (Logout)
*********************************************************/

// logout.php
session_start();
$_SESSION = [];
session_destroy();

exit;
?>