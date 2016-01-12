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

if ($_POST['aendern'] and $_POST['panzerung']) {
	//mysql_query("UPDATE `gebauede` SET `basis` = '".$_POST['basis']."', `forschungsanlage` = '".$_POST['forschungsanlage']."', `fabrik` = '".$_POST['fabrik']."', `raketensilo` = '".$_POST['raketensilo']."', `nbz` = '".$_POST['nbz']."', `hangar` = '".$_POST['hangar']."', `fahrwege` = '".$_POST['fahrwege']."', `missionszentrum` = '".$_POST['missionszentrum']."', `agentenzentrum` = '".$_POST['agentenzentrum']."', `raumstation` = '".$_POST['raumstation']."', `rohstofflager` = '".$_POST['rohstofflager']."', `eisenmine` = '".$_POST['eisenmine']."', `titanmine` = '".$_POST['titanmine']."', `oelpumpe` = '".$_POST['oelpumpe']."', `uranmine` = '".$_POST['uranmine']."' WHERE `omni` = '".$_SESSION['ubl']."' LIMIT 1;"); 
	mysql_query("UPDATE `forschungen` SET `panzerung` = '".$_POST['panzerung']."',
`reaktor` = '".$_POST['reaktor']."',
`panzerketten` = '".$_POST['panzerketten']."',
`motor` = '".$_POST['motor']."',
`feuerwaffen` = '".$_POST['feuerwaffen']."',
`raketen` = '".$_POST['raketen']."',
`sprengstoff` = '".$_POST['sprengstoff']."',
`spionage` = '".$_POST['spionage']."',
`fuehrung` = '".$_POST['fuehrung']."',
`minen` = '".$_POST['minen']."',
`cyborgtechnik` = '".$_POST['cyborgtechnik']."',
`rad` = '".$_POST['rad']."' WHERE `omni` = '".$_SESSION['ubl']."' LIMIT 1;"); 
}

$temp = template('forschungen');

$fosch = mysql_fetch_array(mysql_query("SELECT * FROM `forschungen` WHERE `omni` = '".$_SESSION['ubl']."';"));

$temp  = tag2value('panzerung', $fosch['panzerung'],$temp);
$temp  = tag2value('reaktor', $fosch['reaktor'],$temp);
$temp  = tag2value('panzerketten', $fosch['panzerketten'],$temp);
$temp  = tag2value('rad', $fosch['rad'],$temp);
$temp  = tag2value('motor', $fosch['motor'],$temp);
$temp  = tag2value('feuerwaffen', $fosch['feuerwaffen'],$temp);
$temp  = tag2value('raketen', $fosch['raketen'],$temp);
$temp  = tag2value('sprengstoff', $fosch['sprengstoff'],$temp);
$temp  = tag2value('spionage', $fosch['spionage'],$temp);
$temp  = tag2value('fuehrung', $fosch['fuehrung'],$temp);
$temp  = tag2value('minen', $fosch['minen'],$temp);


$content .= template('index');
$content  = tag2value('ubl', $_SESSION['ubl'],$content);
$content  = tag2value('content', $_SESSION['info'].$temp,$content);

echo $content;
?>