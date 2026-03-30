<?php

require "db.php";

$pin = $_POST['pin'];

$stmt = $db->prepare("SELECT * FROM users WHERE pin=?");

$stmt->execute([$pin]);

if ($stmt->fetch()) {

    if (isset($_POST['type']) && $_POST['type'] === 'evacuation') {
        $_SESSION['evacuation'] = true;
    } else {
        $_SESSION['user'] = true;
    }

    echo "ok";

}
