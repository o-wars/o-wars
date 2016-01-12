<?php
//////////////////////////////////
// Einstellungen                //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////
// Kommentare:
// - Status Spieler
// - Passwort aendern
// - basisnamen aendern
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

$dbh = db_connect();
$piece = template('rank_piece');

$select = "SELECT * FROM `user` WHERE `omni` >='".($_GET['sector']*500+1)."' AND `omni` <='".($_GET['sector']*500+500)."' ORDER BY `points` DESC;";
$result = mysql_query($select);

do {
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	if ($row){
		$select = "SELECT * FROM `clans` WHERE 1 AND `userid` =".$row['omni'].";";
		$result2 = mysql_query($select, $dbh);
		$clan   = mysql_fetch_array($result2);
	
		if ($clan['clanid']) { 
			$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$clan['clanid']."';";
			$result2 = mysql_query($select, $dbh);
			$clan   = mysql_fetch_array($result2);	
		}
		
		$i++;
		if ($row['omni'] == $_SESSION['user']['omni']){
			$newpiece = tag2value('name','<b>'.$row['name'].'</b>', $piece);
			$newpiece = tag2value('rank','<b>'.$i.'</b>', $newpiece);
			$newpiece = tag2value('ubl','<b>'.$row['omni'].'</b>', $newpiece);
			$newpiece = tag2value('base','<b>'.$row['base'].'</b>', $newpiece);
			
			if ($clan['clanid']) { 
				$newpiece = tag2value('clan','<b>'.'<a href="claninfo.php?'.SID.'&amp;clan='.$clan['clanid'].'">'.$clan['tag'].'</a></b>', $newpiece);
			} else {
				$newpiece = tag2value('clan','-', $newpiece);
			}
			
			$newpiece = tag2value('points','<b>'.number_format($row['points'],0,'','.').'</b>', $newpiece);
			$ranking .= $newpiece;
		} else {
			$newpiece = tag2value('name',$row['name'], $piece);
			$newpiece = tag2value('rank',$i, $newpiece);
			$newpiece = tag2value('ubl',$row['omni'], $newpiece);
			$newpiece = tag2value('base',$row['base'], $newpiece);
			if ($clan['clanid']) { 
				$newpiece = tag2value('clan','<a href="claninfo.php?'.SID.'&amp;clan='.$clan['clanid'].'">'.$clan['tag'].'</a>', $newpiece);
			} else {
				$newpiece = tag2value('clan','-', $newpiece);
			}
			$newpiece = tag2value('points',number_format($row['points'],0,'','.'), $newpiece);
			$ranking .= $newpiece;
		}
	}
} while($row and $i < 10);

$i=0;
$ranking1 = $ranking;
$ranking = '';

$select = "SELECT * FROM `user` WHERE `omni` >='".($_GET['sector']*500+1)."' AND `omni` <='".($_GET['sector']*500+500)."' ORDER BY `kampfpunkte` DESC;";
$result = mysql_query($select);

do {
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	if ($row){
		$select = "SELECT * FROM `clans` WHERE 1 AND `userid` =".$row['omni'].";";
		$result2 = mysql_query($select, $dbh);
		$clan   = mysql_fetch_array($result2);
	
		if ($clan['clanid']) { 
			$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$clan['clanid']."';";
			$result2 = mysql_query($select, $dbh);
			$clan   = mysql_fetch_array($result2);	
		}
		
		$i++;
		if ($row['omni'] == $_SESSION['user']['omni']){
			$newpiece = tag2value('name','<b>'.$row['name'].'</b>', $piece);
			$newpiece = tag2value('rank','<b>'.$i.'</b>', $newpiece);
			$newpiece = tag2value('ubl','<b>'.$row['omni'].'</b>', $newpiece);
			$newpiece = tag2value('base','<b>'.$row['base'].'</b>', $newpiece);
			
			if ($clan['clanid']) { 
				$newpiece = tag2value('clan','<b>'.'<a href="claninfo.php?'.SID.'&amp;clan='.$clan['clanid'].'">'.$clan['tag'].'</a></b>', $newpiece);
			} else {
				$newpiece = tag2value('clan','-', $newpiece);
			}
			
			$newpiece = tag2value('points','<b>'.number_format($row['kampfpunkte'],0,'','.').'</b>', $newpiece);
			$ranking .= $newpiece;
		} else {
			$newpiece = tag2value('name',$row['name'], $piece);
			$newpiece = tag2value('rank',$i, $newpiece);
			$newpiece = tag2value('ubl',$row['omni'], $newpiece);
			$newpiece = tag2value('base',$row['base'], $newpiece);
			if ($clan['clanid']) { 
				$newpiece = tag2value('clan','<a href="claninfo.php?'.SID.'&amp;clan='.$clan['clanid'].'">'.$clan['tag'].'</a>', $newpiece);
			} else {
				$newpiece = tag2value('clan','-', $newpiece);
			}
			$newpiece = tag2value('points',number_format($row['kampfpunkte'],0,'','.'), $newpiece);
			$ranking .= $newpiece;
		}
	}
} while($row and $i < 10);

$i=0;
$ranking2 = $ranking;
$ranking = '';

$select = "SELECT * FROM `user` WHERE `omni` >='".($_GET['sector']*500+1)."' AND `omni` <='".($_GET['sector']*500+500)."' ORDER BY `plasmapunkte` DESC;";
$result = mysql_query($select);

do {
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	if ($row){
		$select = "SELECT * FROM `clans` WHERE 1 AND `userid` =".$row['omni'].";";
		$result2 = mysql_query($select, $dbh);
		$clan   = mysql_fetch_array($result2);
	
		if ($clan['clanid']) { 
			$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$clan['clanid']."';";
			$result2 = mysql_query($select, $dbh);
			$clan   = mysql_fetch_array($result2);	
		}
		
		$i++;
		if ($row['omni'] == $_SESSION['user']['omni']){
			$newpiece = tag2value('name','<b>'.$row['name'].'</b>', $piece);
			$newpiece = tag2value('rank','<b>'.$i.'</b>', $newpiece);
			$newpiece = tag2value('ubl','<b>'.$row['omni'].'</b>', $newpiece);
			$newpiece = tag2value('base','<b>'.$row['base'].'</b>', $newpiece);
			
			if ($clan['clanid']) { 
				$newpiece = tag2value('clan','<b>'.'<a href="claninfo.php?'.SID.'&amp;clan='.$clan['clanid'].'">'.$clan['tag'].'</a></b>', $newpiece);
			} else {
				$newpiece = tag2value('clan','-', $newpiece);
			}
			
			$newpiece = tag2value('points','<b>'.number_format($row['plasmapunkte'],0,'','.').'</b>', $newpiece);
			$ranking .= $newpiece;
		} else {
			$newpiece = tag2value('name',$row['name'], $piece);
			$newpiece = tag2value('rank',$i, $newpiece);
			$newpiece = tag2value('ubl',$row['omni'], $newpiece);
			$newpiece = tag2value('base',$row['base'], $newpiece);
			if ($clan['clanid']) { 
				$newpiece = tag2value('clan','<a href="claninfo.php?'.SID.'&amp;clan='.$clan['clanid'].'">'.$clan['tag'].'</a>', $newpiece);
			} else {
				$newpiece = tag2value('clan','-', $newpiece);
			}
			$newpiece = tag2value('points',number_format($row['plasmapunkte'],0,'','.'), $newpiece);
			$ranking .= $newpiece;
		}
	}
} while($row and $i < 10);

$i=0;
$ranking3 = $ranking;
$ranking = '';


// get page html
$template .= template('sectorrank');
$template = tag2value('ranking1', $ranking1, $template);
$template = tag2value('ranking2', $ranking2, $template);
$template = tag2value('ranking3', $ranking3, $template);
$template = tag2value('sector', number_format($_GET['sector'],0), $template);
$content .= $template;

// send page to browser
$content = str_replace('%onload%', $onload, $content);
echo $content.template('footer');
?>