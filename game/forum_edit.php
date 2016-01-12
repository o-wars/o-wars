<?php
//////////////////////////////////
// clanboard.php                //
//////////////////////////////////
// Letzte Aenderung: 01.11.2005 //
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
$ressis = ressistand($_SESSION['user']['omni']);
$content .= $ressis['html'];

$content .= '<br />';

// mit datenbank verbinden
$dbh = db_connect();

if (!$_GET['pid']) { $_GET['pid'] = $_POST['pid']; }

$select = "SELECT * FROM `forum_posts` WHERE `id` = '".$_GET['pid']."';";
$result = mysql_query($select);
$row = mysql_fetch_array($result);
if (!$row){
	die ($content.'<b>Dieser Beitrag existiert nicht.</b>'.template('footer'));
} elseif ($row['uid'] != $_SESSION['user']['omni'] and $_SESSION['user']['group'] < 600) {
	die($content.'<b>da haste keine rechte dran an dem post :)</b>'.template('footer'));
}

if (!$_POST['text']) {
	$content .= template('forum_edit');
	$content = tag2value('text', $row['text'], $content);
	$content = tag2value('pid',  $row['id'],   $content);
	$content = tag2value('fid',  $row['fid'],  $content);
} else {
	mysql_query("UPDATE `forum_posts` SET `text` = '".htmlspecialchars($_POST['text'])."\n\n[i]Zuletzt am ".date('d.m.y \u\m H:i')." Uhr von [b]".$_SESSION['user']['name']."[/b] editiert[/i]' WHERE `id` = '".$_POST['pid']."' LIMIT 1 ;");
	
	echo '<html>
	<head>
	<title>O-Wars --- der Online-Krieg </title>
	<link rel="icon" href="favicon.ico" type="image/ico" />
	<link rel="STYLESHEET" type="text/css" href="./templates/standard/stylesheet.css" />
	<meta http-equiv="Refresh" CONTENT="3;URL=forum_showthread.php?'.SID.'&amp;tid='.$row['tid'].'&amp;page=last#bottom" />
	</head>
	<body>
	<table width="100%" height="80%"><tr><td width="100%" height="100%" align="center" valign="middle">
	<center>
	<table border="1" cellspacing="0" cellpadding="0" style="background-color:#e2e2e2;"><tr><td align="center" valign="middle" width="450px">
	<p style="font-size:14px;">
	<br />
	Dein Beitrag wurde hinzugef&uuml;gt,<br />du wirst nun automatisch zu deinem Beitrag weitergeleitet.<br />
	</p>
	<a href="forum_showthread.php?'.SID.'&amp;tid='.$row['tid'].'&amp;page=last#bottom">Klicke hier um zu deinem Beitrag zu gelangen.</a><br />
	<a href="forum.php?'.SID.'">Klicke hier um zur Foren&uuml;bersicht zur&uuml;ck zu kehren.</a>
	<br /><br /></td></tr></table>
	</center>
	</td></tr></table>
	</body>
	</html>';
	die();
}

// generierte seite ausgeben
echo $content.template('footer');
?>