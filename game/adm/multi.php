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

$content .= template('index');
$content  = tag2value('ubl', $_SESSION['ubl'],$content);
$content  = tag2value('content', '<b>Multi-Hunter</b>'.$temp,$content);

echo $content;
?>