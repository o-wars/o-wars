<?php
//////////////////////////////////
// claninfo.php                 //
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
$content = template('head');
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

$_GET['clan'] = number_format($_GET['clan'],0,'','');

if ($_GET['clan']){
	$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$_GET['clan']."' LIMIT 1;";
	$result = mysql_query($select);
	$row    = mysql_fetch_array($result);
	
	if (!$row['info']) { $row['info'] = 'keine'; }
	
	if ($row['img']){ $img = '<center><img src="'.$row['img'].'" alt="clan" /></center><br />'; }
	
	$select = "SELECT * FROM `clans` WHERE `clanid` = '".$_GET['clan']."';";
	$result = mysql_query($select);
	$members= mysql_num_rows($result);
	
	$row['info'] = bbcode($row['info'], $_SESSION['badwords']);

	$select = "SELECT * FROM `clanwars` WHERE `clan1` =".$row['clanid']." AND `ended` = 0;";
	$result = mysql_query($select);
	
	$kriege .= "<b>Kriege:</b> ";
	
	do {
		$clan1  = mysql_fetch_array($result);
		if ($clan1){
			$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$clan1['clan2']."';";
			$result2 = mysql_query($select);
			$row1   = mysql_fetch_array($result2);
			$kriege.= '<a href="claninfo.php?clan='.$row1['clanid'].'&amp;'.SID.'">'.$row1['tag'].'</a> ';
		}
	} while ($clan1);
	
	$select = "SELECT * FROM `clanwars` WHERE `clan2` =".$row['clanid']." AND `ended` = 0;";
	$result = mysql_query($select);
	
	do {
		$clan2 = mysql_fetch_array($result);
		if ($clan2){
			$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$clan2['clan1']."';";
			$result2 = mysql_query($select);
			$row1   = mysql_fetch_array($result2);
			$kriege.= '<a href="claninfo.php?clan='.$row1['clanid'].'&amp;'.SID.'">'.$row1['tag'].'</a> ';
		}
	} while ($clan2);	
	
	$row['info'] = $kriege.'<br /><br />'.$row['info'];
	
	$content .= '<br /><br />';
	
	if ($members) {$points = number_format(($row['points']/$members),2);}
	else {$points = '0';}
	
	if ($row['aufgeloest'] >= 1) 
	{
	  $aufgeloest = '<font class="red">aufgel&ouml;st seit '.date("d.m.y H:i", $row['aufgeloest']).'</font>'; 
	}
	
	$content .= '<table style="background-color: rgb(226, 226, 226); font-size: 12px;" border="1" cellspacing="0">
	<tbody>
		<tr align="center" style="background-image:url(templates/standard/table_head.gif);">
			<td style="width: 600px;">
				<b>Claninfo: '.$row['name'].' - '.$row['tag'].' 
			</td>
		</tr>
		<tr align="left">
			<td>
				'.$img.'
				<center><b>Mitglieder: '.$members.' -- Punkte: '.$row['points'].' -- Durchschnitt:
				'.$points.'<br />'.$aufgeloest.'</b></center><br /><br />
				<b>Claninfo:</b><br />
				'.nl2br($row['info']).'
			</td>
		</tr>	
	';
} else {
	$content .= "<br /><br /><b>Dieser Clan existiert nicht.</b>";
}

echo $content;
?>
