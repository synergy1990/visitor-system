<?php

require "db.php";

$company = trim($_POST['company']);

if ($company == "") {
    $company = "Privatperson";
}

$stmt = $db->prepare("
INSERT INTO visitors
(firstname,lastname,company,contact,persons,checkin,status)
VALUES (?,?,?,?,?,datetime('now','localtime'),'present')
");

$stmt->execute([

$_POST['firstname'],
$_POST['lastname'],
$company,
$_POST['contact'],
$_POST['persons']

]);
