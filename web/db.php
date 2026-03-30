<?php

session_start();

$db = new PDO("sqlite:/database/visitors.db");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


/* Session Timeout Server */

$timeout = 30;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {
    session_destroy();
}

$_SESSION['LAST_ACTIVITY'] = time();
