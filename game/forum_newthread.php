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

$content .= '<br /><b>O-Wars Foren:</b><br />';

// mit datenbank verbinden
$dbh = db_connect();

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

if (!$_POST['text']) {
	// formular anzeigen fuer neuen beitrag
	
	$content .= template('forum_newthread');
	$content = tag2value('fid',    $_GET['fid'],       $content);
	$content = tag2value('bbcode', template('bbcode'), $content);
	
} else {
	if (!$_POST['topic']) { $_POST['topic'] = "kein Betreff"; }
	else { $_POST['topic'] = substr($_POST['topic'],0,45); }
	$result = mysql_query("INSERT INTO `forum_threads` ( `id` , `uid` , `fid` , `time` , `subject` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_POST['fid']."', '".date('U')."', '".htmlspecialchars($_POST['topic'])."');");
	$iid = mysql_insert_id();
	mysql_query("INSERT INTO `forum_posts` ( `id` , `fid` , `tid` , `uid` , `text` , `time` ) VALUES ( '', '".$_POST['fid']."', '".$iid."', '".$_SESSION['user']['omni']."', '".htmlspecialchars($_POST['text'])."', '".date("U")."');");
	
	if($_POST['fid']<600)
	{
	 $f = fopen('forum.txt', 'a');
	 fputs($f, "Neues Thema im Forum von ".$_SESSION['user']['name']."\n");
	 fputs($f, "Titel: ".$_POST['topic']."\n");
	}
	
	echo '<html>
	<head>
	<title>O-Wars --- der Online-Krieg </title>
	<link rel="icon" href="favicon.ico" type="image/ico" />
	<link rel="STYLESHEET" type="text/css" href="./templates/standard/stylesheet.css" />
	<meta http-equiv="Refresh" CONTENT="3;URL=forum_showthread.php?'.SID.'&amp;tid='.$iid.'" />
	</head>
	<body>
	<table width="100%" height="80%"><tr><td width="100%" height="100%" align="center" valign="middle">
	<center>
	<table border="1" cellspacing="0" cellpadding="0" style="background-color:#e2e2e2;"><tr><td align="center" valign="middle" width="450px">
	<p style="font-size:14px;">
	<br />
	Dein Beitrag wurde hinzugef&uuml;gt,<br />du wirst nun automatisch zu deinem Beitrag weitergeleitet.<br />
	</p>
	<a href="forum_showthread.php?'.SID.'&amp;tid='.$iid.'">Klicke hier um zu deinem Beitrag zu gelangen.</a><br />
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