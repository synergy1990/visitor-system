<?php

require "db.php";

$pin = $_POST['pin'];

$stmt = $db->prepare("SELECT * FROM users WHERE pin=?");

$stmt->execute([$pin]);

if ($stmt->fetch()) {

    $_SESSION['user'] = true;

    echo "ok";

}
