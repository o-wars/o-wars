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

$content .= '<br /><b>Clan-Forum:</b><br /><i><b><a href="clanforum_newthread.php?'.SID.'">Neuen Beitrag schreiben</a> / <a href="clanforum.php?'.SID.'">Altes Clanforum anzeigen</a></b></i><br /><br />';

// mit datenbank verbinden
$dbh = db_connect();

$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
$result = mysql_query($select);
$row = mysql_fetch_array($result);
$clanid = $row['clanid'];
if (!$row){
	die ($content.'<b>Du bist in keinem Clan.</b>'.template('footer'));
}

$content .= template('clanforum_threads');
$piece .= template('clanforum_threads_piece');

$select = "SELECT * FROM `clanforum_threads` WHERE `cid` = '".$clanid."' GROUP BY `time` DESC LIMIT 50;";
$result = mysql_query($select);

do {
	$row = mysql_fetch_array($result);
	$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$row['uid']."';";
	$result2 = mysql_query($select);
	$user = mysql_fetch_array($result2);
	
	if ($row){
		$select = "SELECT * FROM `clanforum_posts` WHERE `tid` = '".$row['id']."' GROUP BY `id` DESC LIMIT 50;";
		$result3 = mysql_query($select);
		$lastpost = mysql_fetch_array($result3);
		
		$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$lastpost['uid']."';";
		$result4 = mysql_query($select);
		$lastuser = mysql_fetch_array($result4);
		
		$newpiece = tag2value('poster', $user['name'], $piece);
		$newpiece = tag2value('subject', '<a href="clanforum_showthread.php?tid='.$row['id'].'&amp;'.SID.'">'.$row['subject'].'</a>', $newpiece);
		$newpiece = tag2value('replys', mysql_num_rows($result3)-1, $newpiece);
		$newpiece = tag2value('lastpost', $lastuser['name'].'<br /><i>'.date('d.m.y - H:i',$lastpost['time']).'</i>', $newpiece);
		$content .= $newpiece;
	}
} while ($row);

$content .= template('clanforum_threads_end');

if (!$newpiece){
	die ($content.'<b>Das Clanforum ist leer.</b>'.template('footer'));
}

// generierte seite ausgeben
echo $content.template('footer');
?>