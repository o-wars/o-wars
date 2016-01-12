<?php
//////////////////////////////////
// clanrank.php                 //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include("functions.php");

// check session
logincheck();

// get html head
$content = template('head');
// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);

// add playerinfo to html
$content .= $status;

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION[user][omni]);
$content .= $ressis['html'];

$dbh = db_connect();
$piece = template('clanrank_piece');

// pages
$result = mysql_query("SELECT * FROM `clan_info` WHERE `aufgeloest` = '0' ORDER BY `points` DESC;");

$count  = mysql_num_rows($result)/100;
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
	else { $pages .= ' <a href="clanrank.php?'.SID.'&amp;page='.$i.'">['.$i.']</a>'; }
} while ($c > $i);
$i = $_GET['page'] * 100-100;
$limit = ($_GET['page'] * 100-100).','.(100);

$select = "SELECT * FROM `clan_info` WHERE `aufgeloest` = '0' ORDER BY `points` DESC LIMIT ".$limit.";";
$result = mysql_query($select);

do {
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	if ($row){
		$select = "SELECT * FROM `clans` WHERE `clanid` =".$row['clanid'].";";
		$result2 = mysql_query($select);
		$members = mysql_numrows($result2);
	
		if ($clan['clanid']) { 
			$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$clan['clanid']."';";
			$result2 = mysql_query($select);
			$clan   = mysql_fetch_array($result2);	
		}
		
		$i++;
		$newpiece = tag2value('name',$row['name'], $piece);
		$newpiece = tag2value('rank',$i, $newpiece);
		$newpiece = tag2value('clanid',$row['clanid'], $newpiece);
		$newpiece = tag2value('members',$members, $newpiece);
		$newpiece = tag2value('tag',$row['tag'], $newpiece);
		$newpiece = tag2value('base',$row['base'], $newpiece);
		$newpiece = tag2value('link','<a href="claninfo.php?'.SID.'&amp;clan='.$row['clanid'].'">mehr</a>', $newpiece);
		$newpiece = tag2value('onclick','location.href=\'claninfo.php?'.SID.'&amp;clan='.$row['clanid'].'\'', $newpiece);
		$newpiece = tag2value('points',number_format($row['points'],0,'','.'), $newpiece);
		$ranking .= $newpiece;
	}
} while($row);


// get page html
$template .= template('clanrank');
$template = tag2value('ranking', $ranking, $template);
$content .= $template;
$content = tag2value('pages', $pages, $content);
$content = tag2value('update', date('H:i - d.m.y',filectime('temp/timestamps/points')), $content);
// send page to browser
$content = str_replace('%onload%', $onload, $content);
echo $content.template('footer');
?>