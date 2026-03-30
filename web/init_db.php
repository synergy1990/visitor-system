<?php

$db = new PDO("sqlite:/database/visitors.db");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("
CREATE TABLE IF NOT EXISTS visitors (
id INTEGER PRIMARY KEY AUTOINCREMENT,
firstname TEXT,
lastname TEXT,
company TEXT,
contact TEXT,
persons INTEGER,
checkin DATETIME,
checkout DATETIME,
status TEXT
);
");

$db->exec("
CREATE TABLE IF NOT EXISTS users(
id INTEGER PRIMARY KEY AUTOINCREMENT,
pin TEXT
);
");

$count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

if ($count == 0) {
    $db->prepare("INSERT INTO users(pin) VALUES (?)")->execute(["0000"]);
}

echo "Database initialized.";
