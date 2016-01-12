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

$content .= '<br /><b>O-Wars Foren&uuml;bersicht:</b><br />';

// mit datenbank verbinden
$dbh = db_connect();

$content .= template('forum_foren');
$piece .= template('forum_foren_piece');

$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
$result = mysql_query($select);
$row = mysql_fetch_array($result);
$clanid = $row['clanid'];
if ($row){
		$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$row['clanid']."';";
		$result = mysql_query($select);
		$row = mysql_fetch_array($result);
		
		$row['id'] = $row['clanid'] + 1000;
		$select = "SELECT * FROM `forum_threads` WHERE `fid` = '".$row['id']."' ORDER BY `time` DESC;";
		$result2 = mysql_query($select);
		$lastthread = @mysql_fetch_array($result2);
		
		$select = "SELECT * FROM `forum_posts` WHERE `fid` = '".$row['id']."' ORDER BY `id` DESC;";
		$result3 = mysql_query($select);
		$lastpost = @mysql_fetch_array($result3);

		$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$lastpost['uid']."';";
		$result4 = mysql_query($select);
		$lastuser = @mysql_fetch_array($result4);
		if (!$lastuser){ $lastuser['name'] = 'gel&ouml;schter Spieler';}
					
		if ($lastpost) { $last = '<font style="font-size:11px">&nbsp;<a href="forum_showthread.php?tid='.$lastthread['id'].'&amp;page=last&amp;'.SID.'#bottom">'.substr($lastthread['subject'],0,29).'...</a><br /></font>'.$lastuser['name'].' <i>'.date('H:i - d.m.',$lastpost['time']).'</i>'; }
		else { $last = "-"; }
		
		$newpiece = tag2value('subject', '<a href="forum_showforum.php?fid='.$row['id'].'&amp;'.SID.'" class="red"><b>Internes Forum des '.$row['name'].' Clans</b></a>', $piece);
		$newpiece = tag2value('desc', 'Hier kommen nur Mitglieder des '.$row['name'].' Clan rein.', $newpiece);
		$newpiece = tag2value('topics', number_format(@mysql_num_rows($result2),0), $newpiece);
		$newpiece = tag2value('replys', number_format(@mysql_num_rows($result3),0), $newpiece);
		$newpiece = tag2value('lastpost', $last, $newpiece);
		$content .= $newpiece;
}

$select = "SELECT * FROM `forum_foren` WHERE `group` <= '".$_SESSION['user']['group']."' ORDER BY `id` ASC;";
$result = mysql_query($select);

do {
	$row = @mysql_fetch_array($result);
	if ($row){
		$select = "SELECT * FROM `forum_threads` WHERE `fid` = '".$row['id']."' ORDER BY `time` DESC;";
		$result2 = mysql_query($select);
		$lastthread = @mysql_fetch_array($result2);
		
		$select = "SELECT * FROM `forum_posts` WHERE `fid` = '".$row['id']."' ORDER BY `id` DESC;";
		$result3 = mysql_query($select);
		$lastpost = @mysql_fetch_array($result3);

		$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$lastpost['uid']."';";
		$result4 = mysql_query($select);
		$lastuser = @mysql_fetch_array($result4);
		if (!$lastuser){ $lastuser['name'] = 'gel&ouml;schter Spieler';}
		
		if ($lastpost) { $last = '<font style="font-size:11px">&nbsp;<a href="forum_showthread.php?tid='.$lastthread['id'].'&amp;page=last&amp;'.SID.'#bottom">'.substr($lastthread['subject'],0,29).'...</a><br /></font>'.$lastuser['name'].' <i>'.date('H:i - d.m.',$lastpost['time']).'</i>'; }
		else { $last = "-"; }
		
		$newpiece = tag2value('subject', '<a href="forum_showforum.php?fid='.$row['id'].'&amp;'.SID.'">'.$row['name'].'</a>', $piece);
		$newpiece = tag2value('desc', $row['desc'], $newpiece);
		$newpiece = tag2value('topics', number_format(@mysql_num_rows($result2),0), $newpiece);
		$newpiece = tag2value('replys', number_format(@mysql_num_rows($result3),0), $newpiece);
		$newpiece = tag2value('lastpost', $last, $newpiece);
		$content .= $newpiece;
	}
} while ($row);

$content .= template('forum_foren_end');

if (!$newpiece){
	die ($content.'<b>Dieses Forum ist leer.</b>'.template('footer'));
}

// generierte seite ausgeben
echo $content.template('footer');
?>