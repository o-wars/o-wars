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
$ressis = ressistand($_SESSION[user][omni]);
$content .= $ressis['html'];

// mit datenbank verbinden
$dbh = db_connect();

if (!$_POST['fid']) {$_POST['fid'] = $_GET['fid'];}
$select = "SELECT * FROM `forum_foren` WHERE `group` <= '".$_SESSION['user']['group']."' AND `id` = '".$_POST['fid']."';";
$result = mysql_query($select);
$row = mysql_fetch_array($result);
if (!$row and $_GET['fid'] < 1000){
	die ($content.'<b>Dieses For1um existiert nicht.</b>'.template('footer'));
} elseif ($_GET['fid'] > 1000) {
	$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$clanid = $row['clanid'];
	if ($row['clanid'] != ($_GET['fid']-1000)) {
		die ($content.'<b>Dieses 1Forum existiert nicht.</b>'.template('footer'));			
	}
}

$name = '<font style="font-size:12px"><b>'.$row['name'].'</b></font>';
$links = '<i><b><a href="forum_newthread.php?'.SID.'&amp;fid='.$_GET['fid'].'"><img src="img/forum_thread.gif" alt="neuer beitrag" /></a> <a href="forum.php?'.SID.'"><img src="img/forum_index.gif" alt="uebersicht" /></a></b></i>';

$content .= template('forum_threads');
$piece .= template('forum_threads_piece');

$content = tag2value('name', $name, $content);

$select = "SELECT * FROM `forum_threads` WHERE `fid` = '".$_GET['fid']."' AND `wichtig` = '1' ORDER BY `time` DESC;";
$result = mysql_query($select);

do {
	$row = @mysql_fetch_array($result);
	$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$row['uid']."';";
	$result2 = mysql_query($select);
	$user = mysql_fetch_array($result2);
	if (!$user){ $user['name'] = 'gel&ouml;schter Spieler';}
	if ($row){
		$select = "SELECT * FROM `forum_posts` WHERE `tid` = '".$row['id']."' ORDER BY `id` DESC;";
		$result3 = mysql_query($select);
		$lastpost = @mysql_fetch_array($result3);
		
		$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$lastpost['uid']."';";
		$result4 = mysql_query($select);
		$lastuser = mysql_fetch_array($result4);
		if (!$lastuser){ $lastuser['name'] = 'gel&ouml;schter Spieler';}
		$newpiece = tag2value('poster', $user['name'], $piece);
		$newpiece = tag2value('subject', '<a href="forum_showthread.php?tid='.$row['id'].'&amp;'.SID.'"><b>Wichtig: </b>'.$row['subject'].'</a>', $newpiece);
		$newpiece = tag2value('tid', $row['id'], $newpiece);
		$newpiece = tag2value('replys', @mysql_num_rows($result3)-1, $newpiece);
		$newpiece = tag2value('lastpost', $lastuser['name'].'<br /><i>'.date('d.m.y - H:i',$lastpost['time']).'</i>', $newpiece);
		$content .= $newpiece;
	}
} while ($row);

// pages
$result = mysql_query("SELECT * FROM `forum_threads` WHERE `fid` = '".$_GET['fid']."' AND `wichtig` = '0' ORDER BY `time` DESC;");

$count  = mysql_num_rows($result)/20;
$count = explode('.',$count);
$c = $count[0];
if ($count[1] != 0) { $c++; }
if (!$_GET['page']) { $_GET['page'] = 1; }

$i = 0;
do {
	$i++;
	if ($_GET['page'] == $i) { $pages .= " <b>[".$i."]</b>"; }
	else { $pages .= ' <a href="forum_showforum.php?fid='.$_GET['fid'].'&amp;'.SID.'&amp;page='.$i.'">['.$i.']</a>'; }
} while ($c > $i);

$limit = ($_GET['page'] * 20-20).','.(20);

$select = "SELECT * FROM `forum_threads` WHERE `fid` = '".$_GET['fid']."' AND `wichtig` = '0' ORDER BY `time` DESC LIMIT ".$limit.";";
$result = mysql_query($select);

do {
	$row = @mysql_fetch_array($result);
	$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$row['uid']."';";
	$result2 = mysql_query($select);
	$user = mysql_fetch_array($result2);
	if (!$user){ $user['name'] = 'gel&ouml;schter Spieler';}
	if ($row){
		$select = "SELECT * FROM `forum_posts` WHERE `tid` = '".$row['id']."' ORDER BY `id` DESC;";
		$result3 = mysql_query($select);
		$lastpost = @mysql_fetch_array($result3);
		
		$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$lastpost['uid']."';";
		$result4 = mysql_query($select);
		$lastuser = mysql_fetch_array($result4);
		if (!$lastuser){ $lastuser['name'] = 'gel&ouml;schter Spieler';}
		$newpiece = tag2value('poster', $user['name'], $piece);
		$newpiece = tag2value('subject', '<a href="forum_showthread.php?tid='.$row['id'].'&amp;'.SID.'">'.$row['subject'].'</a>', $newpiece);
		$newpiece = tag2value('tid', $row['id'], $newpiece);
		$newpiece = tag2value('replys', @mysql_num_rows($result3)-1, $newpiece);
		$newpiece = tag2value('lastpost', $lastuser['name'].'<br /><i>'.date('d.m.y - H:i',$lastpost['time']).'</i>', $newpiece);
		$content .= $newpiece;
	}
} while ($row);

$content .= template('forum_threads_end');

$content = tag2value('links', $links, $content);
$content = tag2value('pages', $pages, $content);

if (!$newpiece){
	die ($content.'<b>Dieses Forum ist leer.</b>'.template('footer'));
}

// generierte seite ausgeben
echo $content.template('footer');
?>