<?php
//////////////////////////////////
// Komplettuebersicht           //
//////////////////////////////////
// Letzte Aenderung: 20.02.2005 //
// Version:          0.10a      //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";
include "einheiten_preise.php";
include "raketen_preise.php";
include "def_preise.php";
include 'forschung_preise.php';
include 'gebaeude_preise.php';

//include "debuglib.php";
//show_vars();

// check session
logincheck();

// html head setzen
$content = template('head');
//$content = tag2value('onload', '', $content);

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

$dbh = db_connect();
$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION[user][omni]."';";
$selectResult   = mysql_query($select);
$gebaeude = mysql_fetch_array($selectResult);

// forschungen
$select = "SELECT * FROM `forschungen` WHERE `omni` = '".($_SESSION[user][omni])."';";
$result = mysql_query($select);
$forschung  = mysql_fetch_array($result);

$content .= template('uebersicht');

// clanlimit
$result = mysql_query("SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';");
$clans  = mysql_fetch_array($result);
$members = mysql_num_rows(mysql_query("SELECT * FROM clans WHERE clanid = '".$clans['clanid']."';"));	
$users   = mysql_num_rows(mysql_query("SELECT * FROM user;"));

$rate = round($members/($users / 100),2);
	
if ($rate >= 20) {
	$content = tag2value('clan_bonus', '<br /><b><font class="red">Dein Clan hat mehr wie 20% aller Spieler als Mitglied, daher entf&auml;llt dein Rohstoffbonus.</font></b>', $content);
} else {
	$content = tag2value('clan_bonus', '', $content);
}

// aktive missionen
$select = "SELECT * FROM `missionen` WHERE `start` = '".$_SESSION[user][omni]."' AND `ankunft` > '".date(U)."' AND `parsed` != '1' ORDER BY `ankunft` ASC;";
$result = mysql_query($select);
$piece   = template('active_missions');

	$content = tag2value('missions_active', mysql_num_rows($result), $content);
	$content = tag2value('missions_max', ($gebaeude[missionszentrum]*2), $content);

	do {
		$row = mysql_fetch_array($result);
		if ($row['return'] > $row['ankunft']){
			if ($row[type] == 1){ $mission = 'angreifen'; }
			elseif ($row[type] == 2){ $mission = 'transportieren'; }
			elseif ($row[type] == 3){ $mission = '&uuml;berf&uuml;hren'; }
			elseif ($row[type] == 4){ $mission = 'sammeln'; }
			$i = 0;
			do {
				$i++;
				$size += $row['einh'.$i] * $einh[$i]['size'];			
			} while ($i < 15);			
			
			$newpiece = str_replace('%type%', $mission, $piece);
			$newpiece = str_replace('%ziel%', $row[ziel], $newpiece);
			$newpiece = str_replace('%size%', $size, $newpiece);
			$newpiece = str_replace('%units%', ($row[einh1]+$row[einh2]+$row[einh3]+$row[einh4]+$row[einh5]+$row[einh6]+$row[einh7]+$row[einh8]+$row[einh9]+$row[einh10]+$row[einh11]+$row[einh12]+$row[einh13]+$row[einh14]+$row[einh15]), $newpiece);
			$newpiece = str_replace('%speed%', $row[speed], $newpiece);
			$newpiece = str_replace('%ankunft%', date('H:i - d.m',$row[ankunft]), $newpiece);
			$newpiece = str_replace('%countdown%', countdown($row[ankunft]-date('U')), $newpiece);
			$newpiece = str_replace('%mehr%', '<a href="mission.php?'.SID.'&amp;id='.$row[id].'">mehr</a>', $newpiece);
			$newpiece = str_replace('%link%', 'mission.php?'.SID.'&amp;id='.$row[id], $newpiece);
			$newpiece = str_replace('%info%', '<table width=100% cellspacing=0 cellpadding=0><tr><td>Eisen:</td><td align=right>'.$row['eisen'].'</td></tr><tr><td>Titan:</td><td align=right>'.$row['titan'].'</td></tr><tr><td>Oel:</td><td align=right>'.$row['oel'].'</td></tr><tr><td>Uran:</td><td align=right>'.$row['uran'].'</td></tr><tr><td>Gold:</td><td align=right>'.$row['gold'].'</td></tr><tr><td>Chanje:</td><td align=right>'.$row['chanje'].'</td></tr></table>', $newpiece);
			$missions .= $newpiece;
			$size = 0;
		}
	} while ($row);

	$content = tag2value('active_missions', $missions, $content);
	
	// rueckkehr
	$select = "SELECT * FROM `missionen` WHERE `start` = '".$_SESSION[user][omni]."' AND `return` > '".date(U)."'ORDER BY `return` ASC;";
	$result = mysql_query($select);
	
$piece   = template('return_missions');

	do {
		$row = mysql_fetch_array($result);
		if ($row){
			if ($row[type] == 1){ $mission = 'angreifen'; }
			elseif ($row[type] == 2){ $mission = 'transportieren'; }
			elseif ($row[type] == 3){ $mission = '&uuml;berf&uuml;hren'; }
			elseif ($row[type] == 4){ $mission = 'sammeln'; }
						
			$i = 0;
			do {
				$i++;
				$size += $row['einh'.$i] * $einh[$i]['size'];			
			} while ($i < 15);
			
			if ($size > $ressis['hangar']){ $size = '<font color="red">'.$size.'</font>'; }			
			
			$newpiece = str_replace('%type%', $mission, $piece);
			$newpiece = str_replace('%ziel%', $row[ziel], $newpiece);
			$newpiece = str_replace('%size%', $size, $newpiece);
			$newpiece = str_replace('%units%', ($row[einh1]+$row[einh2]+$row[einh3]+$row[einh4]+$row[einh5]+$row[einh6]+$row[einh7]+$row[einh8]+$row[einh9]+$row[einh10]+$row[einh11]+$row[einh12]+$row[einh13]+$row[einh14]+$row[einh15]), $newpiece);
			$newpiece = str_replace('%speed%', $row[speed], $newpiece);
			$newpiece = str_replace('%ankunft%', date('H:i - d.m',$row['return']), $newpiece);
			$newpiece = str_replace('%countdown%', countdown($row['return']-date('U')), $newpiece);
			$newpiece = str_replace('%mehr%', '<a href="mission.php?'.SID.'&amp;id='.$row[id].'">mehr</a>', $newpiece);
			$newpiece = str_replace('%link%', 'mission.php?'.SID.'&amp;id='.$row[id], $newpiece);
			$newpiece = str_replace('%info%', '<table width=100% cellspacing=0 cellpadding=0><tr><td>Eisen:</td><td align=right>'.$row['eisen'].'</td></tr><tr><td>Titan:</td><td align=right>'.$row['titan'].'</td></tr><tr><td>Oel:</td><td align=right>'.$row['oel'].'</td></tr><tr><td>Uran:</td><td align=right>'.$row['uran'].'</td></tr><tr><td>Gold:</td><td align=right>'.$row['gold'].'</td></tr><tr><td>Chanje:</td><td align=right>'.$row['chanje'].'</td></tr></table>', $newpiece);
			$missionr .= $newpiece;
			$size = 0;
		}
	} while ($row);

	$content = tag2value('return_missions', $missionr, $content);
	
	// aufgedeckte missionen
	$select = "SELECT * FROM `missionen` WHERE `ziel` = '".$_SESSION[user][omni]."' AND `ankunft` > '".date(U)."' AND `parsed` != '1' ORDER BY `ankunft` ASC;";
	$result = mysql_query($select);
	
	do {
		$row = mysql_fetch_array($result);
		if ($row){
			$row['type'] == '1' ? $piece = template('detected_missions_attack') : $piece = template('detected_missions');
			if ($row[einh1]) { $einheiten .= $row[einh1].' <a href="javascript:popUp(\'details_einh.php?id=1\',400)">'.$einh[1][name].'</a><br />';}
			if ($row[einh2]) { $einheiten .= $row[einh2].' <a href="javascript:popUp(\'details_einh.php?id=2\',400)">'.$einh[2][name].'</a><br />';}
			if ($row[einh3]) { $einheiten .= $row[einh3].' <a href="javascript:popUp(\'details_einh.php?id=3\',400)">'.$einh[3][name].'</a><br />';}
			if ($row[einh4]) { $einheiten .= $row[einh4].' <a href="javascript:popUp(\'details_einh.php?id=4\',400)">'.$einh[4][name].'</a><br />';}
			if ($row[einh5]) { $einheiten .= $row[einh5].' <a href="javascript:popUp(\'details_einh.php?id=5\',400)">'.$einh[5][name].'</a><br />';}
			if ($row[einh6]) { $einheiten .= $row[einh6].' <a href="javascript:popUp(\'details_einh.php?id=6\',400)">'.$einh[6][name].'</a><br />';}
			if ($row[einh7]) { $einheiten .= $row[einh7].' <a href="javascript:popUp(\'details_einh.php?id=7\',400)">'.$einh[7][name].'</a><br />';}
			if ($row[einh8]) { $einheiten .= $row[einh8].' <a href="javascript:popUp(\'details_einh.php?id=8\',400)">'.$einh[8][name].'</a><br />';}
			if ($row[einh9]) { $einheiten .= $row[einh9].' <a href="javascript:popUp(\'details_einh.php?id=9\',400)">'.$einh[9][name].'</a><br />';}
			if ($row[einh10]) { $einheiten .= $row[einh10].' <a href="javascript:popUp(\'details_einh.php?id=10\',400)">'.$einh[10][name].'</a><br />';}
			if ($row[einh11]) { $einheiten .= $row[einh11].' <a href="javascript:popUp(\'details_einh.php?id=11\',400)">'.$einh[11][name].'</a><br />';}
			if ($row[einh12]) { $einheiten .= $row[einh12].' <a href="javascript:popUp(\'details_einh.php?id=12\',400)">'.$einh[12][name].'</a><br />';}
			if ($row[einh13]) { $einheiten .= $row[einh13].' <a href="javascript:popUp(\'details_einh.php?id=13\',400)">'.$einh[13][name].'</a><br />';}
			if ($row[einh14]) { $einheiten .= $row[einh14].' <a href="javascript:popUp(\'details_einh.php?id=14\',400)">'.$einh[14][name].'</a><br />';}
			if ($row[einh15]) { $einheiten .= $row[einh15].' <a href="javascript:popUp(\'details_einh.php?id=15\',400)">'.$einh[15][name].'</a><br />';}
			if ($row[type] == 1){ $mission = 'angreifen'; }
			elseif ($row[type] == 2){ $mission = 'transportieren'; }
			elseif ($row[type] == 3){ $mission = '&uuml;berf&uuml;hren'; }
			elseif ($row[type] == 4){ $mission = 'sammeln'; }
			$units = ($row[einh1]+$row[einh2]+$row[einh3]+$row[einh4]+$row[einh5]+$row[einh6]+$row[einh7]+$row[einh8]+$row[einh9]+$row[einh10]+$row[einh11]+$row[einh12]+$row[einh13]+$row[einh14]+$row[einh15]);
			$ankunft = date('H:i - d.m',$row[ankunft]);
			
			$select = "SELECT * FROM `forschungen` WHERE `omni` = '".$row['start']."' LIMIT 1;";
			$enemy_forsch = mysql_query($select);
			$enemy_forsch = mysql_fetch_array($enemy_forsch);
			
			$spiodiff = ($enemy_forsch['spionage'] - $forschung['spionage']);

			if ($spiodiff >= 1) { $einheiten = 'unbekannt'; }			
			if ($spiodiff >= 2) { $row[speed] = '?'; }			
			if ($spiodiff >= 4) { 
				$ankunft = date('H:i',$row[ankunft]);
				$ankunft = substr($ankunft, 0, 4).'X'.date(' - d.m',$row[ankunft]);
			}
			if ($spiodiff >= 6) { $units = 'unbekannt'; }
			if ($spiodiff >= 8) { $row[pid] = '?'; }

			
			$newpiece = str_replace('%id%', $row[id], $piece);
			$newpiece = str_replace('%pid%', $row[pid], $newpiece);
			$newpiece = str_replace('%type%', $mission, $newpiece);
			$newpiece = str_replace('%start%', $row[start], $newpiece);
			$newpiece = str_replace('%units%', $units, $newpiece);
			$newpiece = str_replace('%speed%', $row[speed], $newpiece);
			$newpiece = str_replace('%ankunft%', $ankunft, $newpiece);
			$newpiece = str_replace('%einheiten%', $einheiten, $newpiece);
			$missiona .= $newpiece;
			unset($einheiten);
		}
	} while ($row);

	$content = tag2value('detected_missions', $missiona, $content);
	
	$content .= template('footer');

	$select    = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION['user']['omni']."';";
	$result    = mysql_query($select);
	$row       = mysql_fetch_array($result, MYSQL_ASSOC);

	if ($row['nextbasis'] != 0) { $geb = 'Basis'; $g_lvl = $row['basis']; $geb_time = $row['nextbasis']; $dauer = $kosten['basis']['zeit'] * ++$gebaeude['basis'];}
	elseif ($row['nextforschungsanlage'] != 0) { $geb = 'Forschungsanlage'; $g_lvl = $row['forschungsanlage']; $geb_time = $row['nextforschungsanlage']; $dauer = $kosten['forschungsanlage']['zeit'] * ++$gebaeude['forschungsanlage'];}
	elseif ($row['nextfabrik'] != 0) { $geb = 'Fabrik'; $g_lvl = $row['fabrik']; $geb_time = $row['nextfabrik']; $dauer = $kosten['fabrik']['zeit'] * ++$gebaeude['fabrik'];}
	elseif ($row['nextraketensilo'] != 0) { $geb = 'Raketensilo'; $g_lvl = $row['raketensilo']; $geb_time = $row['nextraketensilo']; $dauer = $kosten['raketensilo']['zeit'] * ++$gebaeude['raketensilo'];}
	elseif ($row['nextnbz'] != 0) { $geb = 'NBZ'; $geb_time = $row['nextnbz']; $g_lvl = $row['nbz']; $dauer = $kosten['nbz']['zeit'] * ++$gebaeude['nbz'];}
	elseif ($row['nexthangar'] != 0) { $geb = 'Hangar'; $g_lvl = $row['hangar']; $geb_time = $row['nexthangar']; $dauer = $kosten['hangar']['zeit'] * ++$gebaeude['hangar'];}
	elseif ($row['nextfahrwege'] != 0) { $geb = 'Fahrwege'; $g_lvl = $row['fahrwege']; $geb_time = $row['nextfahrwege']; $dauer = $kosten['fahrwege']['zeit'] * ++$gebaeude['fahrwege'];}
	elseif ($row['nextmissionszentrum'] != 0) { $geb = 'Missionszentrum'; $g_lvl = $row['missionszentrum']; $geb_time = $row['nextmissionszentrum']; $dauer = $kosten['missionszentrum']['zeit'] * ++$gebaeude['missionszentrum'];}
	elseif ($row['nextagentenzentrum'] != 0) { $geb = 'Agentenzentrum'; $g_lvl = $row['agentenzentrum']; $geb_time = $row['nextagentenzentrum']; $dauer = $kosten['agentenzentrum']['zeit'] * ++$gebaeude['agentenzentrum'];}
	elseif ($row['nextraumstation'] != 0) { $geb = 'Raumstation'; $g_lvl = $row['raumstation']; $geb_time = $row['nextraumstation']; $dauer = $kosten['raumstation']['zeit'] * ++$gebaeude['raumstation'];}
	elseif ($row['nextrohstofflager'] != 0) { $geb = 'Rohstofflager'; $g_lvl = $row['rohstofflager']; $geb_time = $row['nextrohstofflager']; $dauer = $kosten['rohstofflager']['zeit'] * ++$gebaeude['rohstofflager'];}
	elseif ($row['nexteisenmine'] != 0) { $geb = 'Eisenmine'; $g_lvl = $row['eisenmine']; $geb_time = $row['nexteisenmine']; $dauer = $kosten['eisenmine']['zeit'] * ++$gebaeude['eisenmine'];}
	elseif ($row['nexttitanmine'] != 0) { $geb = 'Titanmine'; $g_lvl = $row['titanmine']; $geb_time = $row['nexttitanmine']; $dauer = $kosten['titanmine']['zeit'] * ++$gebaeude['titanmine'];}
	elseif ($row['nextoelpumpe'] != 0) { $geb = 'Oelpumpe'; $g_lvl = $row['oelpumpe']; $geb_time = $row['nextoelpumpe']; $dauer = $kosten['oelpumpe']['zeit'] * ++$gebaeude['oelpumpe'];}
	elseif ($row['nexturanmine'] != 0) { $geb = 'Uranmine'; $g_lvl = $row['uranmine']; $geb_time = $row['nexturanmine']; $dauer = $kosten['uranmine']['zeit'] * ++$gebaeude['uranmine'];}
	else {$no_building = 1;}
	
	if (!$no_building) {
		$gebaeude  = '<b>'.$geb.' '.++$g_lvl.':</b>'.percentbar( ( $geb_time - date('U') ), $dauer, 205 );
	} else {
		$gebaeude  = '<b>-----</b>';
	}

	$select    = "SELECT * FROM `forschungen` WHERE `omni` = '".$_SESSION['user']['omni']."';";
	$result    = mysql_query($select);
	$row       = mysql_fetch_array($result, MYSQL_ASSOC);
	
	if ($row['nextpanzerung'] != 0) 		{ $for = 'Panzerung'; $f_lvl = $row['panzerung']; $for_time = $row['nextpanzerung']; $dauer = $kosten['panzerung']['zeit'] * ++$row['panzerung'];}
	elseif ($row['nextreaktor'] != 0) 		{ $for = 'Reaktor'; $f_lvl = $row['reaktor']; $for_time = $row['nextreaktor']; $dauer = $kosten['reaktor']['zeit'] * ++$row['reaktor'];}
	elseif ($row['nextpanzerketten'] != 0) 	{ $for = 'Panzerketten'; $f_lvl = $row['panzerketten']; $for_time = $row['nextpanzerketten']; $dauer = $kosten['panzerketten']['zeit'] * ++$row['panzerketten'];}
	elseif ($row['nextmotor'] != 0) 		{ $for = 'Motor'; $f_lvl = $row['motor']; $for_time = $row['nextmotor']; $dauer = $kosten['motor']['zeit'] * ++$row['motor'];}
	elseif ($row['nextfeuerwaffen'] != 0) 	{ $for = 'Feuerwaffen'; $f_lvl = $row['feuerwaffen']; $for_time = $row['nextfeuerwaffen']; $dauer = $kosten['feuerwaffen']['zeit'] * ++$row['feuerwaffen'];}
	elseif ($row['nextraketen'] != 0) 		{ $for = 'Raketen'; $f_lvl = $row['raketen']; $for_time = $row['nextraketen']; $dauer = $kosten['raketen']['zeit'] * ++$row['raketen'];}
	elseif ($row['nextsprengstoff'] != 0) 	{ $for = 'Sprengstoff'; $f_lvl = $row['sprengstoff']; $for_time = $row['nextsprengstoff']; $dauer = $kosten['sprengstoff']['zeit'] * ++$row['sprengstoff'];}
	elseif ($row['nextspionage'] != 0) 		{ $for = 'Spionage'; $f_lvl = $row['spionage']; $for_time = $row['nextspionage']; $dauer = $kosten['spionage']['zeit'] * ++$row['spionage'];}
	elseif ($row['nextfuehrung'] != 0) 		{ $for = 'F&uuml;hrung'; $f_lvl = $row['fuehrung']; $for_time = $row['nextfuehrung']; $dauer = $kosten['fuehrung']['zeit'] * ++$row['fuehrung'];}	
	elseif ($row['nextminen'] != 0) 		{ $for = 'Minentechnik'; $f_lvl = $row['minen']; $for_time = $row['nextminen']; $dauer = $kosten['minen']['zeit'] * ++$row['minen'];}
	elseif ($row['nextcyborgtechnik'] != 0) { $for = 'Cyborgtechnik'; $f_lvl = $row['cyborgtechnik']; $for_time = $row['nextcyborgtechnik']; $dauer = $kosten['cyborgtechnik']['zeit'] * ++$row['cyborgtechnik'];}
	elseif ($row['nextrad'] != 0) 			{ $for = 'Radverst&auml;rkungen'; $f_lvl = $row['rad']; $for_time = $row['nextrad']; $dauer = $kosten['rad']['zeit'] * ++$row['rad'];}
	else {$no_forschung = 1;}
	
	if (!$no_forschung) {
		$forschung  = '<b>'.$for.' '.++$f_lvl.':</b>'.percentbar( ( $for_time - date('U') ), $dauer, 205 );
	} else {
		$forschung  = '<b>-----</b>';
	}

	$select = "SELECT * FROM `fabrik` WHERE `omni` = ".($_SESSION[user][omni])." ORDER BY fertigstellung ASC;";
	$result = mysql_query($select);
	$rows   = mysql_numrows($result);
	$row    = mysql_fetch_array($result);
	$i=0;
	if ($row){
		do {
			$i++;
			if ($row['type'] < 1000){$fabrik .= '<b>'.$einh[$row['type']]['name'].' - '.countdown(($row['fertigstellung']-date('U'))).'</b><br />';}
			elseif ($row['type'] < 2000){$row['type'] -= 1000; $fabrik .= '<b>'.$def[$row['type']]['name'].' - '.countdown(($row['fertigstellung']-date('U'))).'</b><br />';}
			elseif ($row['type'] < 3000){$row['type'] -= 2000; $fabrik .= '<b>'.$rak[$row['type']]['name'].' - '.countdown(($row['fertigstellung']-date('U'))).'</b><br />';}
			$row    = mysql_fetch_array($result);		
		} while ($row AND $i < 3);
	} else {
		$fabrik  = '<b>-----</b>';
	}

	$select = "SELECT * FROM `defense` WHERE `omni` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$defender_def = mysql_fetch_array($result);

	$select = "SELECT * FROM `hangar` WHERE `omni` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$defender = mysql_fetch_array($result);

	// forschungen
	$select = "SELECT * FROM `forschungen` WHERE `omni` = '".($_SESSION[user][omni])."';";
	$result = mysql_query($select);
	$for  = mysql_fetch_array($result);
	
	$count = 0;
	do {
		$count++;
		$type = 'def'.$count;
		$d_anz = $d_anz+$defender_def[$type];
		$d_off += ($def[$count][off]+($def[$count][off]/10*$for['fuehrung']))*$defender_def[$type];
		$d_def += ($def[$count][def]+($def[$count][def]/10*$for['fuehrung']))*$defender_def[$type];
	} while ( 10 > $count );
	
	$count = 0;
	do {
		$count++;
		$type = 'einh'.$count;
		$d_anz = $d_anz+$defender[$type];
		//echo $d_anz.' / ';
		$u_off += ($einh[$count][off]+($einh[$count][off]/10*$for['fuehrung']))*$defender[$type];
		$u_def += ($einh[$count][def]+($einh[$count][def]/10*$for['fuehrung']))*$defender[$type];
	} while ( 15 > $count );	
	
	$a_off = $u_off + $d_off;
	$a_def = $u_def + $d_def;
	
	$content   = tag2value('unit_off', number_format($u_off,2,',','.'), $content);
	$content   = tag2value('unit_def', number_format($u_def,2,',','.'), $content);
	$content   = tag2value('unit_all', number_format($u_off+$u_def,2,',','.'), $content);
	$content   = tag2value('def_off', number_format($d_off,2,',','.'), $content);
	$content   = tag2value('def_def', number_format($d_def,2,',','.'), $content);
	$content   = tag2value('def_all', number_format($d_off+$d_def,2,',','.'), $content);
	$content   = tag2value('all_off', number_format($a_off,2,',','.'), $content);
	$content   = tag2value('all_def', number_format($a_def,2,',','.'), $content);
	$content   = tag2value('all_all', number_format($a_off+$a_def,2,',','.'), $content);
	
	$content   = tag2value('gebaeude', $gebaeude, $content);
	$content   = tag2value('forschung', $forschung, $content);
	$content   = tag2value('fabrik', $fabrik, $content);
	$content   = tag2value('fabrik_ges', $rows, $content);
	$content   = tag2value("onload",$onload,$content);
	
	// generierte seite ausgeben
	echo $content;
?>