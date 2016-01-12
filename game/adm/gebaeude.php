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

if (!$_SESSION['ubl']) {
	 die ('error');
}

if ($_POST['aendern'] and $_POST['basis']) {
	mysql_query("UPDATE `gebauede` SET `basis` = '".$_POST['basis']."', `forschungsanlage` = '".$_POST['forschungsanlage']."', `fabrik` = '".$_POST['fabrik']."', `raketensilo` = '".$_POST['raketensilo']."', `nbz` = '".$_POST['nbz']."', `hangar` = '".$_POST['hangar']."', `fahrwege` = '".$_POST['fahrwege']."', `missionszentrum` = '".$_POST['missionszentrum']."', `agentenzentrum` = '".$_POST['agentenzentrum']."', `raumstation` = '".$_POST['raumstation']."', `rohstofflager` = '".$_POST['rohstofflager']."', `eisenmine` = '".$_POST['eisenmine']."', `titanmine` = '".$_POST['titanmine']."', `oelpumpe` = '".$_POST['oelpumpe']."', `uranmine` = '".$_POST['uranmine']."' WHERE `omni` = '".$_SESSION['ubl']."' LIMIT 1;"); 
}

$temp = template('gebaeude');

$geb = mysql_fetch_array(mysql_query("SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION['ubl']."';"));

$temp  = tag2value('basis', $geb['basis'],$temp);
$temp  = tag2value('forschungsanlage', $geb['forschungsanlage'],$temp);
$temp  = tag2value('fabrik', $geb['fabrik'],$temp);
$temp  = tag2value('raketensilo', $geb['raketensilo'],$temp);
$temp  = tag2value('nbz', $geb['nbz'],$temp);
$temp  = tag2value('hangar', $geb['hangar'],$temp);
$temp  = tag2value('fahrwege', $geb['fahrwege'],$temp);
$temp  = tag2value('missionszentrum', $geb['missionszentrum'],$temp);
$temp  = tag2value('raumstation', $geb['raumstation'],$temp);
$temp  = tag2value('rohstofflager', $geb['rohstofflager'],$temp);
$temp  = tag2value('eisenmine', $geb['eisenmine'],$temp);
$temp  = tag2value('titanmine', $geb['titanmine'],$temp);
$temp  = tag2value('oelpumpe', $geb['oelpumpe'],$temp);
$temp  = tag2value('uranmine', $geb['uranmine'],$temp);



$content .= template('index');
$content  = tag2value('ubl', $_SESSION['ubl'],$content);
$content  = tag2value('content', $_SESSION['info'].$temp,$content);

echo $content;
?>