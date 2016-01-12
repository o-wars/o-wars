<?php
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

// mit datenbank verbinden
$dbh = db_connect();

$res = mysql_query("SELECT * FROM `nachrichten` WHERE `to`=1 AND `gelesen`=0");

echo mysql_num_rows($res).' Nachrichten';
?>
