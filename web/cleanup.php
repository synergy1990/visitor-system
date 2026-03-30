<?php

require "db.php";

date_default_timezone_set("Europe/Berlin");

$now = date("H:i");
$today = date("Y-m-d");


/* automatische 18:30 Abmeldung */

if ($now >= "18:30") {

    $stmt = $db->prepare("
UPDATE visitors
SET status='absent',
checkout=?
WHERE status='present'
");

    $stmt->execute([date("Y-m-d H:i:s")]);

}



/* DSGVO Löschung nach 2 Werktagen */

$weekday = date("N");

$deleteDate = new DateTime();


if ($weekday == 1) {

    /* Montag → Freitag löschen */

    $deleteDate->modify("-3 days");

} else {

    /* sonst → 2 Tage */

    $deleteDate->modify("-2 days");

}

$limit = $deleteDate->format("Y-m-d");

$stmt = $db->prepare("
DELETE FROM visitors
WHERE date(checkin) < ?
");

$stmt->execute([$limit]);
