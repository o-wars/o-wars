<?php
//////////////////////////////////
// Komplettuebersicht           //
//////////////////////////////////
// Letzte Aenderung: 20.02.2005 //
// Version:          0.10a      //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";
include "einheiten_preise.php";
include "raketen_preise.php";
include "def_preise.php";
include 'forschung_preise.php';
include 'gebaeude_preise.php';

// check session
logincheck();

// html head setzen
$content = template('head');
//$content = tag2value('onload', '', $content);

// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);
$content .= $status;

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION[user][omni]);
$content .= $ressis['html'];

$content .= template('techtree');

$dbh = db_connect();

$result = mysql_query("SELECT * FROM gebauede WHERE omni = '".$_SESSION['user']['omni']."';");
$row    = mysql_fetch_array($result);
for ($i=1; $i <= $row['basis']; $i++) {
	$content = str_replace('Basis ('.$i.')', '<font color="green">Basis ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['forschungsanlage']; $i++) {
	$content = str_replace('Forschungsanlage ('.$i.')', '<font color="green">Forschungsanlage ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['fabrik']; $i++) {
	$content = str_replace('Fabrik ('.$i.')', '<font color="green">Fabrik ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['raumstation']; $i++) {
	$content = str_replace('Raumstation ('.$i.')', '<font color="green">Raumstation ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['fahrwege']; $i++) {
	$content = str_replace('Fahrwege ('.$i.')', '<font color="green">Fahrwege ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['raketensilo']; $i++) {
	$content = str_replace('Raketensilo ('.$i.')', '<font color="green">Raketensilo ('.$i.')</font>', $content);
}

$result = mysql_query("SELECT * FROM forschungen WHERE omni = '".$_SESSION['user']['omni']."';");
$row    = mysql_fetch_array($result);
for ($i=1; $i <= $row['feuerwaffen']; $i++) {
	$content = str_replace('Feuerwaffen ('.$i.')', '<font color="green">Feuerwaffen ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['sprengstoff']; $i++) {
	$content = str_replace('Sprengstoff ('.$i.')', '<font color="green">Sprengstoff ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['raketen']; $i++) {
	$content = str_replace('Raketen ('.$i.')', '<font color="green">Raketen ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['fuehrung']; $i++) {
	$content = str_replace('F&uuml;hrung ('.$i.')', '<font color="green">F&uuml;hrung ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['rad']; $i++) {
	$content = str_replace('Radverst&auml;rkungen ('.$i.')', '<font color="green">Radverst&auml;rkungen ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['panzerung']; $i++) {
	$content = str_replace('Panzerung ('.$i.')', '<font color="green">Panzerung ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['motor']; $i++) {
	$content = str_replace('Motor ('.$i.')', '<font color="green">Motor ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['panzerketten']; $i++) {
	$content = str_replace('Panzerketten ('.$i.')', '<font color="green">Panzerketten ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['reaktor']; $i++) {
	$content = str_replace('Reaktor ('.$i.')', '<font color="green">Reaktor ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['spionage']; $i++) {
	$content = str_replace('Spionage ('.$i.')', '<font color="green">Spionage ('.$i.')</font>', $content);
}
for ($i=1; $i <= $row['minen']; $i++) {
	$content = str_replace('Minentechnik ('.$i.')', '<font color="green">Minentechnik ('.$i.')</font>', $content);
}

// generierte seite ausgeben
echo $content.template('footer');
?>