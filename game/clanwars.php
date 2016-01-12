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
$piece = template('clanwars_piece');

$select = "SELECT * FROM `clanwars` WHERE `ended` = 0 ORDER BY `id` ASC;";
$result = mysql_query($select);

do {
	$row = @mysql_fetch_array($result, MYSQL_ASSOC);
	if ($row){
		$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$row['clan1']."';";
		$result2 = mysql_query($select);
		$clan1   = mysql_fetch_array($result2);	

		$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$row['clan2']."';";
		$result2 = mysql_query($select);
		$clan2   = mysql_fetch_array($result2);			

		if ($row['kampfpunkte1'] < 0){ $row['kampfpunkte1'] = '<font class="red">'.$row['kampfpunkte1'].'</font>'; }
		if ($row['kampfpunkte2'] < 0){ $row['kampfpunkte2'] = '<font class="red">'.$row['kampfpunkte2'].'</font>'; }
		if ($row['ressis1'] < 0){ $row['ressis1'] = '<font class="red">'.$row['ressis1'].'</font>'; }
		if ($row['ressis2'] < 0){ $row['ressis2'] = '<font class="red">'.$row['ressis2'].'</font>'; }

		if ($clan1['aufgeloest'] >= 1 and $row['ended'] == 0){ 
			mysql_query("UPDATE `clanwars` SET `ended` = '".date('U')."' WHERE `id` = '".$row['id']."' LIMIT 1;");
		}
		if ($clan2['aufgeloest'] >= 1 and $row['ended'] == 0){ 
			mysql_query("UPDATE `clanwars` SET `ended` = '".date('U')."' WHERE `id` = '".$row['id']."' LIMIT 1;");
		}
		
		if (!$clan1){ $clan1['tag'] = '<font class="red">aufgel&ouml;st</font>';}
		if (!$clan2){ $clan2['tag'] = '<font class="red">aufgel&ouml;st</font>';}
		if ($clan1['aufgeloest'] >= 1){ $clan1['tag'] = '<font class="red">'.$clan1['tag'].'</font>';}
		if ($clan2['aufgeloest'] >= 1){ $clan2['tag'] = '<font class="red">'.$clan2['tag'].'</font>';}
				
		$i++;
		$newpiece = tag2value('id', $row['id'], $piece);
		$newpiece = tag2value('start',date('d.m.y',$row['started']), $newpiece);
		$newpiece = tag2value('clan1','<a href="claninfo.php?'.SID.'&clan='.$clan1['clanid'].'">'.$clan1['tag'].'</a>', $newpiece);
		$newpiece = tag2value('kp1',$row['kampfpunkte1'], $newpiece);
		$newpiece = tag2value('pluenderung1',$row['ressis1'], $newpiece);
		$newpiece = tag2value('clan2','<a href="claninfo.php?'.SID.'&clan='.$clan2['clanid'].'">'.$clan2['tag'].'</a>', $newpiece);
		$newpiece = tag2value('kp2',$row['kampfpunkte2'], $newpiece);
		$newpiece = tag2value('pluenderung2',$row['ressis2'], $newpiece);
		$ranking .= $newpiece;
	}
} while($row);

if ($_GET['archiv'] == 1){
$select = "SELECT * FROM `clanwars` WHERE `ended` != 0 ORDER BY `ended` DESC;";
$result = mysql_query($select);

do {
	$row = @mysql_fetch_array($result, MYSQL_ASSOC);
	if ($row){
		$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$row['clan1']."';";
		$result2 = mysql_query($select);
		$clan1   = mysql_fetch_array($result2);	

		$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$row['clan2']."';";
		$result2 = mysql_query($select);
		$clan2   = mysql_fetch_array($result2);			

		if ($row['kampfpunkte1'] < 0){ $row['kampfpunkte1'] = '<font class="red">'.$row['kampfpunkte1'].'</font>'; }
		if ($row['kampfpunkte2'] < 0){ $row['kampfpunkte2'] = '<font class="red">'.$row['kampfpunkte2'].'</font>'; }
		if ($row['ressis1'] < 0){ $row['ressis1'] = '<font class="red">'.$row['ressis1'].'</font>'; }
		if ($row['ressis2'] < 0){ $row['ressis2'] = '<font class="red">'.$row['ressis2'].'</font>'; }
		if (!$clan1){ $clan1['tag'] = '<font class="red">aufgel&ouml;st</font>';}
		if (!$clan2){ $clan2['tag'] = '<font class="red">aufgel&ouml;st</font>';}
		if ($clan1['aufgeloest'] >= 1){ $clan1['tag'] = '<font class="red">'.$clan1['tag'].'</font>';}
		if ($clan2['aufgeloest'] >= 1){ $clan2['tag'] = '<font class="red">'.$clan2['tag'].'</font>';}
		
		$i++;
		$newpiece = tag2value('id', $row['id'], $piece);
		$newpiece = tag2value('start',time2str($row['ended']-$row['started']), $newpiece);
		$newpiece = tag2value('clan1','<a href="claninfo.php?'.SID.'&clan='.$clan1['clanid'].'">'.$clan1['tag'].'</a>', $newpiece);
		$newpiece = tag2value('kp1',$row['kampfpunkte1'], $newpiece);
		$newpiece = tag2value('pluenderung1',$row['ressis1'], $newpiece);
		$newpiece = tag2value('clan2','<a href="claninfo.php?'.SID.'&clan='.$clan2['clanid'].'">'.$clan2['tag'].'</a>', $newpiece);
		$newpiece = tag2value('kp2',$row['kampfpunkte2'], $newpiece);
		$newpiece = tag2value('pluenderung2',$row['ressis2'], $newpiece);
		$ranking2 .= $newpiece;
	}
} while($row);
} else {
		$ranking2 = '<tr><td colspan="8"><center><a href="clanwars.php?archiv=1&amp;'.SID.'">Klicke hier um die beendeten Clanwars anzuzeigen.</a></center></td></tr>';
}

// get page html
$template .= template('clanwars');
$template = tag2value('ranking', $ranking, $template);
$template = tag2value('ranking2', $ranking2, $template);
$content .= $template;

// send page to browser
$content = str_replace('%onload%', $onload, $content);
echo $content.template('footer');
?>