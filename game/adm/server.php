<?php
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "admin.php";

// check session
logincheck();

// mit datenbank verbinden
$dbh = db_connect();

if ($_POST['ubl']) {
	$_SESSION['ubl'] = $_POST['ubl'];
}

$content .= template('index');
$content  = tag2value('ubl', $_SESSION['ubl'],$content);
$content  = tag2value('content', '<img src="img.php" alt="xxx" />',$content);

echo $content;
?>