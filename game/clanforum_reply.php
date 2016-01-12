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

if (!$_POST['tid']) {$_POST['tid'] = $_GET['tid'];}
$select = "SELECT * FROM `clanforum_threads` WHERE `id` = '".$_POST['tid']."';";
$result = mysql_query($select);
$row = mysql_fetch_array($result);
if (!$row){
	die ($content.'<b>Dieser Beitrag existiert nicht.</b>'.template('footer'));
} elseif ($clanid != $row['cid']) {
	die ($content.'<b>Dieser Beitrag ist einem anderen Clan.</b>'.template('footer'));
}

if (!$_POST['text']) {
	$content .= template('clanforum_reply');
	$content = tag2value('topic', $row['subject'], $content);
	$content = tag2value('tid', $row['id'], $content);
} else {
	mysql_query("INSERT INTO `clanforum_posts` ( `id` , `tid` , `uid` , `text` , `time` ) VALUES ( '', '".$_POST['tid']."', '".$_SESSION['user']['omni']."', '".$_POST['text']."', '".date("U")."');");
	mysql_query("UPDATE `clanforum_threads` SET `time` = '".date('U')."' WHERE `id` = '".$_POST['tid']."' LIMIT 1;");
	$content .= '<br />Dein Beitrag wurde hinzugef&uuml;gt.<br /><a href="clanboard.php?'.SID.'">Klicke hier um zur &Uuml;bersicht zur&uuml;ck zu kehren.</a>';
	$content .= '<script language="JavaScript" type="text/javascript">
	<!-- 
	function showthread() {
	setTimeout(\'location.href="clanforum_showthread.php?tid='.$_POST['tid'].'&amp;'.SID.'"\',2000); 
	}
	showthread();
	-->
	</script>
	';
}

// generierte seite ausgeben
echo $content.template('footer');
?>