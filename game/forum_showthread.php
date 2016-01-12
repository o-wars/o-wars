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
include "inc/bbcode.php";

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

if ($_SESSION['user']['group'] >= 600) {
	$content .= template('overlib');
}

$select = "SELECT * FROM `forum_threads` WHERE `id` = '".$_GET['tid']."';";
$result = mysql_query($select);
$row = mysql_fetch_array($result);
if (!$row){
	die ($content.'<b>Dieser Beitrag existiert nicht.</b>'.template('footer'));
}

$select = "SELECT * FROM `forum_foren` WHERE `group` <= '".$_SESSION['user']['group']."' AND `id` = '".$row['fid']."';";
$result = mysql_query($select);
$row2 = mysql_fetch_array($result);
if (!$row2 and $row['fid'] < 1000){
	die ($content.'<b>Dieses Forum existiert nicht.</b>'.template('footer'));
} elseif ($row['fid'] > 1000) {
	$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$row3 = mysql_fetch_array($result);
	$row2['id'] = $row3['clanid']+1000;
	if ($row3['clanid'] != ($row['fid']-1000)) {
		die ($content.'<b>Dieses Forum existiert nicht.</b>'.template('footer'));			
	}
}

$name  = '<font style="font-size:12px"><b>'.$row2['name'].' // '.$row['subject'].'</b></font>';

$links = '<b><i><a href="forum_reply.php?tid='.$_GET['tid'].'&amp;fid='.$row2['id'].'&amp;'.SID.'"><img src="img/forum_reply.gif" alt="antworten" /></a> <a href="forum_showforum.php?fid='.$row['fid'].'&amp;'.SID.'"><img src="img/forum_index.gif" alt="uebersicht" /></a></i></b>';

$content .= template('forum_showthread');
$piece .= template('forum_showthread_piece');

// pages
$result = mysql_query("SELECT * FROM `forum_posts` WHERE `tid` = '".$_GET['tid']."' ORDER BY `id` ASC;");

$count  = mysql_num_rows($result)/20;
$count = explode('.',$count);
$c = $count[0];
if ($count[1] != 0) { $c++; }
if ($_GET['page'] == "last") {
	$_GET['page'] = $c;
} elseif (!$_GET['page']) { $_GET['page'] = 1; }

$i = 0;
do {
	$i++;
	if ($_GET['page'] == $i) { $pages .= " <b>[".$i."]</b>"; }
	else { $pages .= ' <a href="forum_showthread.php?tid='.$_GET['tid'].'&amp;'.SID.'&amp;page='.$i.'">['.$i.']</a>'; }
} while ($c > $i);

$limit = ($_GET['page'] * 20-20).','.(20);

$select = "SELECT * FROM `forum_threads` WHERE `id` = '".$_GET['tid']."' LIMIT 1;";
$result = mysql_query($select);
$row = mysql_fetch_array($result);

$content = tag2value('topic', $row['subject'], $content);
$content = tag2value('name', $name, $content);

$select = "SELECT * FROM `forum_posts` WHERE `tid` = '".$_GET['tid']."' ORDER BY `id` ASC LIMIT ".$limit.";";
$result = mysql_query($select);

do {

	$row = mysql_fetch_array($result);
	$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$row['uid']."';";
	$result2 = mysql_query($select);
	$user = mysql_fetch_array($result2);
	if (!$user){ $user['name'] = 'gel&ouml;schter Spieler';}
	if ($row){
		//$select = "SELECT * FROM `forum_posts` WHERE `tid` = '".$row['id']."' GROUP BY `id` DESC LIMIT 50;";
		//$result3 = mysql_query($select);
		
		
		$select = "SELECT * FROM `clans` WHERE 1 AND `userid` = '".$row['uid']."';";
		$result3 = mysql_query($select);
		$clan = mysql_fetch_array($result3);
		if ($clan) { 
			$select = "SELECT * FROM `clan_info` WHERE 1 AND `clanid` = '".$clan['clanid']."';";
			$result3 = mysql_query($select);
			$clan = mysql_fetch_array($result3);
		}
		
		$posts = @mysql_num_rows(
			mysql_query("SELECT * FROM `forum_posts` WHERE `uid` = ".$user['omni'].";"));
		
		if ($user['kampfpunkte'] >= 25000) { $orden = '&nbsp;<img src="img/orden4.gif" alt="O" /><br />'; }	
		elseif ($user['kampfpunkte'] >= 10000) { $orden = '&nbsp;<img src="img/orden3.gif" alt="O" /><br />'; }
		elseif ($user['kampfpunkte'] >=  5000) { $orden = '&nbsp;<img src="img/orden2.gif" alt="O" /><br />'; }
		elseif ($user['kampfpunkte'] >=  2500) { $orden = '&nbsp;<img src="img/orden1.gif" alt="O" /><br />'; }
		else { $orden = '&nbsp;<br />'; }
		
		if     ($user['group'] == 666) { $group = "O-Wars Admin"; $orden = '&nbsp;<img src="img/orden1.gif" alt="O" /> <img src="img/orden2.gif" alt="O" /> <img src="img/orden3.gif" alt="O" /> <img src="img/orden4.gif" alt="O" /><br />'; }
		elseif ($user['group'] == 665) { $group = "O-Wars Co-Admin"; }
		elseif ($user['group'] == 664) { $group = "O-Wars Co-Admin"; }
		elseif ($user['group'] == 600) { $group = "O-Wars Helpdesk &amp;<br />&nbsp;Forenpirat"; }
		elseif ($user['group'] == 601) { $group = "O-Wars Moderator"; }
		elseif ($user['group'] ==   2) { $group = "O-Wars Member"; } // ohne zahlencode
		elseif ($user['group'] ==   1) { $group = "Spaml&uuml;mmel"; }
		elseif ($user['group'] >    0) { $group = "O-Wars Staff"; }
		elseif ($user['supporter'] > date('U')) { $group = "O-Wars Supporter"; }
		else                           { $group = "O-Wars Member"; }

		$userinfo  = '<a name="p'.$row['id'].'"></a>&nbsp;<b><a href="profil.php?ubl='.$user['omni'].'&amp;'.SID.'">'.$user['name'].'</a></b><br />';
		$userinfo .= '&nbsp;<i>'.$group.'</i><br />'.$orden;
		$userinfo .= '&nbsp;UBL: <b>'.$user['omni'].'</b><br />';
		if ($clan) { $userinfo .= '&nbsp;Clan: <b><a href="claninfo.php?&clan='.$clan['clanid'].'&amp;'.SID.'">'.$clan['tag'].'</a></b><br />'; }
		$userinfo .= '&nbsp;AP: <b>'.number_format($user['points'],0,',','.').'</b><br />';
		$userinfo .= '&nbsp;KP: <b>'.number_format($user['kampfpunkte'],2,',','.').'</b><br />';
		$userinfo .= '&nbsp;Posts: <b>'.number_format($posts,0,',','.').'</b><br />&nbsp;Posted: <b>';
		$userinfo .= date('d.m.y </\b><\i>H:i</\i>',$row['time']);
				
		if ($user['sig']) {
			$row['text'] .= '<br /><br />'.$user['sig'];
		}
		
		if ($_SESSION['user']['group'] >= 600) {
			$row['text'] .= '<br /><br /><p align="right"><a onClick=\'overlib("<center><a href=\"forum_edit.php?'.SID.'&amp;pid='.$row['id'].'\">Beitrag bearbeiten</a><br /><a href=\"forum_del.php?'.SID.'&amp;pid='.$row['id'].'\">Beitrag l&ouml;schen</a><br /><a href=\"forum_archiv.php?'.SID.'&amp;tid='.$row['tid'].'\">Beitrag &rarr; Archiv</a><br /><a class=\"red\" onClick=\"cClick()\">Fenster schliessen</a></center>")\'><img src="img/forum_del.gif" alt="loeschen" /></a></p>';
		}

		if ($_SESSION['user']['omni'] == $user['omni']) {
			$row['text'] .= '<p align="right"><a href="forum_edit.php?'.SID.'&amp;pid='.$row['id'].'">Beitrag bearbeiten.</a></p>';
		}		
		
		$row['text'] = bbcode($row['text'], $_SESSION['badwords']);
		
		$newpiece = tag2value('text', nl2br($row['text']), $piece);
		$newpiece = tag2value('userinfo', $userinfo, $newpiece);
		$content .= $newpiece;
		
		unset($orden);
	}
} while ($row);

$content .= template('forum_showthread_end');

$content = tag2value('links', $links, $content);
$content = tag2value('pages', $pages, $content);

if (!$newpiece){
	die ($content.'<b>Der Beitrag ist leer.</b>'.template('footer'));
}

// generierte seite ausgeben
echo $content.template('footer');
?>