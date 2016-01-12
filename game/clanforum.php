<?php
//////////////////////////////////
// clanboard.php                //
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

// html head setzen
$content  = template('head');
$content = tag2value('onload', '', $content);

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

$content .= '<br /><b>Clan-Forum:</b><br />';

// mit datenbank verbinden
$dbh = db_connect();

$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
$result = mysql_query($select);
$row = mysql_fetch_array($result);
$clanid = $row['clanid'];
if (!$row){
	die ($content.'<b>Du bist in keinem Clan.</b>'.template('footer'));
}

if ($_POST['text']) {
	$_POST['text'] = htmlspecialchars(str_replace("'",'`',$_POST['text']));
	$select = "INSERT INTO `clanboard` ( `id` , `userid` , `clanid` , `date` , `text` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$clanid."', '".date('U')."', '".$_POST['text']."');";
	mysql_query($select);
}

$content .= '<form enctype="multipart/form-data" action="clanboard.php?'.SID.'" method="post">
<textarea name="text" style="width:680px;height:100px"></textarea><br /><input type="submit" name="submit" value="Eintragen!"></form>';

$piece .= template('clanboard');

$select = "SELECT * FROM `clanboard` WHERE `clanid` = '".$clanid."' GROUP BY `id` DESC LIMIT 50;";
$result = mysql_query($select);

do {
	$row = mysql_fetch_array($result);
	$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$row['userid']."';";
	$result2 = mysql_query($select);
	$user = mysql_fetch_array($result2);
	
	if ($row){
		$newpiece = tag2value('name', $user['name'], $piece);
		$newpiece = tag2value('ubl', $row['userid'], $newpiece);
		$newpiece = tag2value('time', date('d.m.Y H:i',$row['date']), $newpiece);
		$newpiece = tag2value('text', nl2br($row['text']), $newpiece);
		$content .= $newpiece;
	}
} while ($row);

if (!$newpiece){
	die ($content.'<b>Das Clanforum ist leer.</b>'.template('footer'));
}

// generierte seite ausgeben
echo $content.template('footer');
?>