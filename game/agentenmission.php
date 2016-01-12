<?php
//////////////////////////////////
// agenten.php                  //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

// check session
logincheck();

// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);
$content .= $status;

// mit datenbank verbinden
$dbh = db_connect();

$ressis = ressistand($_SESSION['user']['omni']);

$result = mysql_query("SELECT * FROM `agenten` WHERE `omni` = '".$_SESSION['user']['omni']."' AND `id` = '".htmlentities($_GET['id'])."';");
$row = mysql_fetch_array($result);

$content .= template('agenten_mission');
//$content .= $row['name'];


// html head setzen
$top = template('head');
$top = tag2value('onload', $onload, $top);

// ressourcen berechnen
$ressis = ressistand($_SESSION[user][omni]);
$top .= $ressis['html'];

// generierte seite ausgeben
echo $top.$content.template('footer');
?>