<?php

require "db.php";

$id = $_POST['id'];

$v = $db->query("SELECT status FROM visitors WHERE id=$id")->fetch();

if ($v['status'] == "present") {

    $db->exec("
UPDATE visitors
SET
status='absent',
checkout=datetime('now','localtime')
WHERE id=$id
");

} else {

    $db->exec("
UPDATE visitors
SET
status='present',
checkin=datetime('now','localtime'),
checkout=NULL
WHERE id=$id
");

}
