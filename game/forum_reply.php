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
$ressis = ressistand($_SESSION['user']['omni']);
$content .= $ressis['html'];

$content .= '<br /><b>O-Wars Foren:</b><br />';

// mit datenbank verbinden
$dbh = db_connect();

if (!$_POST['tid']) {$_POST['tid'] = $_GET['tid'];}
if (!$_POST['fid']) {$_POST['fid'] = $_GET['fid'];}
$select = "SELECT * FROM `forum_foren` WHERE `group` <= '".$_SESSION['user']['group']."' AND `id` = '".$_POST['fid']."';";
$result = mysql_query($select);
$row = mysql_fetch_array($result);
if (!$row and $_POST['fid'] < 1000){
	die ($content.'<b>Dieses Forum existiert nicht.</b>'.template('footer'));
} elseif ($_POST['fid'] > 1000) {
	$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$clanid = $row['clanid'];
	if ($row['clanid'] != ($_POST['fid']-1000)) {
		die ($content.'<b>Dieses Forum existiert nicht1.</b>'.template('footer'));			
	}
}

$select = "SELECT * FROM `forum_threads` WHERE `id` = '".$_POST['tid']."';";
$result = mysql_query($select);
$row = mysql_fetch_array($result);
if (!$row){
	die ($content.'<b>Dieser Beitrag existiert nicht.</b>'.template('footer'));
}

if (!$_POST['text']) {
	$content .= template('forum_reply');
	$content = tag2value('topic',  $row['subject'],    $content);
	$content = tag2value('tid',    $row['id'],         $content);
	$content = tag2value('fid',    $row['fid'],        $content);
	$content = tag2value('bbcode', template('bbcode'), $content);
} else {
	mysql_query("INSERT INTO `forum_posts` ( `id` , `fid` , `tid` , `uid` , `text` , `time` ) VALUES ( '', '".$_POST['fid']."', '".$_POST['tid']."', '".$_SESSION['user']['omni']."', '".htmlspecialchars($_POST['text'])."', '".date("U")."');");
	mysql_query("UPDATE `forum_threads` SET `time` = '".date('U')."' WHERE `id` = '".$_POST['tid']."' LIMIT 1;");
	
	if($_POST['fid']<600)
	{
	 $f = fopen('forum.txt', 'a');
	 fputs($f, "Neue Antwort im Forum von ".$_SESSION['user']['name']."\n");
	}
	
	echo '<html>
	<head>
	<title>O-Wars --- der Online-Krieg </title>
	<link rel="icon" href="favicon.ico" type="image/ico" />
	<link rel="STYLESHEET" type="text/css" href="./templates/standard/stylesheet.css" />
	<meta http-equiv="Refresh" CONTENT="3;URL=forum_showthread.php?'.SID.'&amp;tid='.$_POST['tid'].'&amp;page=last#bottom" />
	</head>
	<body>
	<table width="100%" height="80%"><tr><td width="100%" height="100%" align="center" valign="middle">
	<center>
	<table border="1" cellspacing="0" cellpadding="0" style="background-color:#e2e2e2;"><tr><td align="center" valign="middle" width="450px">
	<p style="font-size:14px;">
	<br />
	Dein Beitrag wurde hinzugef&uuml;gt,<br />du wirst nun automatisch zu deinem Beitrag weitergeleitet.<br />
	</p>
	<a href="forum_showthread.php?'.SID.'&amp;tid='.$_POST['tid'].'&amp;page=last#bottom">Klicke hier um zu deinem Beitrag zu gelangen.</a><br />
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