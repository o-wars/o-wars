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

$links = '<b><i><a href="clanforum_reply.php?tid='.$_GET['tid'].'&amp;'.SID.'">Auf diesen Beitrag antworten.</a> / <a href="clanboard.php?'.SID.'">Zur&uuml;ck zur &Uuml;bersicht</a></i></b><br /><br />';

$content .= $links;

$content .= template('clanforum_showthread');
$piece .= template('clanforum_showthread_piece');

$select = "SELECT * FROM `clanforum_threads` WHERE `id` = '".$_GET['tid']."' LIMIT 1;";
$result = mysql_query($select);
$row = mysql_fetch_array($result);

$content = tag2value('topic', $row['subject'], $content);

$select = "SELECT * FROM `clanforum_posts` WHERE `tid` = '".$_GET['tid']."' ORDER BY `id` ASC;";
$result = mysql_query($select);
do {
	$row = mysql_fetch_array($result);
	$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$row['uid']."';";
	$result2 = mysql_query($select);
	$user = mysql_fetch_array($result2);
	
	if ($row){
		$select = "SELECT * FROM `clanforum_posts` WHERE `tid` = '".$row['id']."' GROUP BY `id` DESC LIMIT 50;";
		$result3 = mysql_query($select);
		$userinfo  = '<b>'.$user['name'].'</b><br />';
		$userinfo .= 'UBL: '.$user['omni'].'<br /><br />';
		$userinfo .= date('H:i - d.m.Y',$row['time']);
		$newpiece = tag2value('text', nl2br($row['text']), $piece);
		$newpiece = tag2value('userinfo', $userinfo, $newpiece);
		$content .= $newpiece;
	}
} while ($row);

$content .= template('clanforum_showthread_end');

if (!$newpiece){
	die ($content.'<b>Der Beitrag ist leer.</b>'.template('footer'));
}

$content = tag2value('links', $links, $content);

// generierte seite ausgeben
echo $content.template('footer');
?>