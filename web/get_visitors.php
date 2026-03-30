<?php

require "db.php";

if (!isset($_SESSION['user'])) {
    exit;
}


/* Besucher laden (Liste zeigt weiterhin heute + gestern) */

$data = $db->query("
SELECT
id,
firstname,
lastname,
company,
contact,
persons,
checkin,
checkout,
status
FROM visitors
WHERE date(checkin) >= date('now','-1 day')
ORDER BY checkin DESC
")->fetchAll(PDO::FETCH_ASSOC);



/* Statistik nur für HEUTE berechnen */

$total = 0;
$present = 0;

foreach ($data as $v) {

    /* nur heutige Einträge zählen */

    if (date("Y-m-d", strtotime($v['checkin'])) == date("Y-m-d")) {

        $total += (int)$v['persons'];

        if ($v['status'] == "present") {
            $present += (int)$v['persons'];
        }

    }

}

$absent = $total - $present;


/* JSON zurückgeben */

echo json_encode([
"stats" => [
"total" => $total,
"present" => $present,
"absent" => $absent
],
"visitors" => $data
]);
