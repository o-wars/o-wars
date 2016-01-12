<?php
//////////////////////////////////
// raumstation.php              //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
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
include 'raumstation_preise.php';
//include 'debuglib.php';

// check session
logincheck();

// html head setzen
//$content = template('head');
//$content = tag2value('onload', '', $content);

// get playerinfo template and replace tags
/*
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);
$content .= $status;
*/

// ressourcen berechnen
$ressis = ressistand($_SESSION[user][omni]);
//$content .= $ressis['html'];

$dbh = db_connect();
$result = mysql_query("SELECT * FROM `raumstation` WHERE `omni` = '".$_SESSION['user']['omni']."';");
$raumstation = mysql_fetch_array($result);

$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION[user][omni]."';";
$selectResult   = mysql_query($select);
$row = mysql_fetch_array($selectResult);
$gebaeude = $row;

if ($_GET[abbrechen] == 1){
	$select = "UPDATE `raumstation` SET `nextscanner` = '0', `nextplasma` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$raumstation['nextplasma'] = 0;
	$raumstation['nextscanner'] = 0;
	$selectResult   = mysql_query($select);
	$abbrechen = "Der aktuelle Bauvorgang wurde abgebrochen.";
}

$select = "SELECT * FROM `munition` WHERE `omni` = '".$_SESSION['user']['omni']."';";
$result = mysql_query($select);
$rounds = @mysql_num_rows($result);
	
if ($_POST['uran_verbauen'] > 0){
	$select = "SELECT * FROM `munition` WHERE `omni` = '".$_SESSION['user']['omni']."' AND `finished` > '".date('U')."';";
	$result = mysql_query($select);
	$rows    = mysql_num_rows($result);
	
	if ($ressis['uran'] >= $_POST['uran_verbauen'] and $ressis['titan'] >= ($_POST['uran_verbauen']/10) and $rows == 0 and $rounds < ($raumstation['plasma']*2)){
		$select = "INSERT INTO `munition` ( `id` , `omni` , `damage` , `finished` ) VALUES ( '', '".$_SESSION['user']['omni']."', '".round((($_POST['uran_verbauen']/5+(($_POST['uran_verbauen']/20)*$raumstation['plasma']))/2),0)."', '".(date('U')+($_POST['uran_verbauen']/$raumstation['plasma']))."' );";
		mysql_query($select);
		$select = "UPDATE `ressis` SET `titan` = '".($ressis['titan']-($_POST['uran_verbauen']/10))."', `uran` = '".($ressis['uran']-$_POST['uran_verbauen'])."' WHERE `omni` = '".$_SESSION['user']['omni']."';";
		mysql_query($select);
	} else { $munerror = 'Du hast nicht genug Ressourcen f&uuml;r diesen Auftrag.'; }
}

if ($_POST['beschuss'] and $_POST['target'] and $_POST['round']){
	$select = "SELECT * FROM `munition` WHERE `omni` = '".$_SESSION['user']['omni']."' and `id` = '".number_format($_POST['round'],0,'','')."';";
	$result = mysql_query($select);
	$munition = mysql_fetch_array($result);
	
	$select = "SELECT * FROM `missionen` WHERE `id` = '".number_format($_POST['target'],0,'','')."' AND `pid` = '".number_format($_POST['pid'],0,'','')."';";
	$result = mysql_query($select);
	$target = mysql_fetch_array($result);
/*	
	//gegenangriff bei beschuss von 0
	if ($target['start'] == 0 and $target['type'] != 1){
			$eh_type = rand(5,11);
			$eh[$eh_type] = round($gebaeude['hangar']*25/$einh[$eh_type]['size'],0);
			$eh[$eh_type] = rand($eh[$eh_type]/1.3,$eh[$eh_type]);
			$select = "INSERT INTO `missionen` ( `id` , `type` , `start` , `ziel` , `started` , `ankunft` , `return` , `speed` , `parsed` , `einh1` , `einh2` , `einh3` , `einh4` , `einh5` , `einh6` , `einh7` , `einh8` , `einh9` , `einh10` , `einh11` , `einh12` , `einh13` , `einh14` , `einh15` ) VALUES ( '', '1', '0', '".$_SESSION['user']['omni']."', '".date('U')."', '".(date('U')+(3600*2.5))."', '".(date('U')+20000)."', '666', '0', '".$eh[1]."', '".$eh[2]."', '".$eh[3]."', '".$eh[4]."', '".$eh[5]."', '".$eh[6]."', '".$eh[7]."', '".$eh[8]."', '".$eh[9]."', '".$eh[10]."', '".$eh[11]."', '".$eh[12]."', '".$eh[13]."', '".$eh[14]."', '".$eh[15]."' );";
			mysql_query($select);
			$eid = mysql_insert_id($dbh);
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '1', '".$eid."', '".(date(U)+felder2time(($entfernung*10/$maxspeed)))."');";
			$selectResult   = mysql_query($select);
	}
*/	
	$select = "SELECT * FROM `user` WHERE `omni` = '".$target['start']."';";
	$result = mysql_query($select);
	$points = mysql_fetch_array($result);
	
	$mypoints = str_replace('.','',$_SESSION['user']['points']);
	
	//echo $points['points']." < ".$mypoints;
	
	if ($points['points'] < ($mypoints/3) and $_POST['sicher'] != 1 and $points['points'] > 0 and $points['points'] < 30000) {  
		$form = '		<form enctype="multipart/form-data" action="raumstation.php?'.SID.'" method="post">
						<input type="hidden" name="target" value="'.$_POST['target'].'" />
						<input type="hidden" name="pid" value="'.$_POST['pid'].'" />
						<input type="hidden" name="round" value="'.$_POST['round'].'" />
						<input type="hidden" name="sicher" value="1" />
						<input type="hidden" name="beschuss" value="1" />
						<input type="submit" name="submit" value="trotzdem feuern!" />
						</form><br /><br />';
		echo showpage($content."<br /><br /><b>Diese Mission geh&ouml;rt einem schw&auml;cheren Spieler!<br /></b>Daher w&uuml;rde dieser Plasmabeschuss um den Faktor ".number_format($mypoints/$points['points'],2,',','.')." runtergerechnet und w&uuml;rde daher nur <b>".number_format($munition['damage']/($mypoints/$points['points']),0)."</b> Schaden verursachen.<br /><b>Willst du das aktzeptieren?</b>".$form, $_SESSION['user']['omni'],$onload);
		die();
	}
	
	if (!$munition){die($content."CHEATVERSUCH???!!!");};

	if ($_POST['sicher'] and $points['points'] < 30000 and $points['points'] < ($mypoints/3) and $points['points'] > 0) {  
		$munition['damage'] = number_format($munition['damage']/($mypoints/$points['points']),0,'','');
	}	
	
	if ($target['parsed'] == 1){die(template('head')."<br /><br /><b>Diese Mission kann nicht beschossen werden, da sie auf dem R&uuml;ckweg ist.</b>");}
	if ($target['start'] == "0"){die(template('head')."<br /><br /><b>Diese Mission kann nicht beschossen werden, da sie 0 geh&ouml;rt.</b>");}
	
	mysql_query("DELETE FROM `munition` WHERE `omni` = '".$_SESSION['user']['omni']."' and `id` = '".number_format($_POST['round'],0,'','')."' LIMIT 1;");
	mysql_query("INSERT INTO `plasmalog` ( `id` , `timestamp` , `omni` , `target` , mission ,  `damage` ) VALUES ( '', UNIX_TIMESTAMP( ) , '".$_SESSION['user']['omni']."', '".$target['start']."', '".$target['id']."', '".$munition['damage']."' );");	
	// hier kommt dann die schleife mit dem beschuss.
	do {
			$kills = 0;
			if ($target['einh1'] and $munition['damage'] >= $einh[1]['def']){ 
				do {
					$v['1']++; $kills++;
					$target['einh1']--;
					$munition['damage'] -= $einh[1]['def'];
				} while ($target['einh1'] and $munition['damage'] >= $einh[1]['def']);
			}
			if ($target['einh2'] and $munition['damage'] >= $einh[2]['def']){ 
				do {
					$v['2']++; $kills++;
					$target['einh2']--;
					$munition['damage'] -= $einh[2]['def'];
				} while ($target['einh2'] and $munition['damage'] >= $einh[2]['def']);
			}
			if ($target['einh3'] and $munition['damage'] >= $einh[3]['def']){ 
				do {
					$v['3']++; $kills++;
					$target['einh3']--;
					$munition['damage'] -= $einh[3]['def'];
				} while ($target['einh3'] and $munition['damage'] >= $einh[3]['def']);
			}
			if ($target['einh4'] and $munition['damage'] >= $einh[4]['def']){ 
				do {
					$v['4']++; $kills++;
					$target['einh4']--;
					$munition['damage'] -= $einh[4]['def'];
				} while ($target['einh4'] and $munition['damage'] >= $einh[4]['def']);
			}
			if ($target['einh5'] and $munition['damage'] >= $einh[5]['def']){ 
				do {
					$v['5']++; $kills++;
					$target['einh5']--;
					$munition['damage'] -= $einh[5]['def'];
				} while ($target['einh5'] and $munition['damage'] >= $einh[5]['def']);
			}
			if ($target['einh12'] and $munition['damage'] >= $einh[12]['def']){ 
				do {
					$v['12']++; $kills++;
					$target['einh12']--;
					$munition['damage'] -= $einh[12]['def'];
				} while ($target['einh12'] and $munition['damage'] >= $einh[12]['def']);
			}
			if ($target['einh13'] and $munition['damage'] >= $einh[13]['def']){ 
				do {
					$v['13']++; $kills++;
					$target['einh13']--;
					$munition['damage'] -= $einh[13]['def'];
				} while ($target['einh13'] and $munition['damage'] >= $einh[13]['def']);
			}
			if ($target['einh6'] and $munition['damage'] >= $einh[6]['def']){ 
				do {
					$v['6']++; $kills++;
					$target['einh6']--;
					$munition['damage'] -= $einh[6]['def'];
				} while ($target['einh6'] and $munition['damage'] >= $einh[6]['def']);
			}
			/* Panther sind nun auch Plasmaimmun
			if ($target['einh7'] and $munition['damage'] >= $einh[7]['def']){ 
				do {
					$v['7']++; $kills++;
					$target['einh7']--;
					$munition['damage'] -= $einh[7]['def'];
				} while ($target['einh7'] and $munition['damage'] >= $einh[7]['def']);
			}
			*/
			if ($target['einh8'] and $munition['damage'] >= $einh[8]['def']){ 
				do {
					$v['8']++; $kills++;
					$target['einh8']--;
					$munition['damage'] -= $einh[8]['def'];
				} while ($target['einh8'] and $munition['damage'] >= $einh[8]['def']);
			}
			if ($target['einh10'] and $munition['damage'] >= $einh[10]['def']){ 
				do {
					$v['10']++; $kills++;
					$target['einh10']--;
					$munition['damage'] -= $einh[10]['def'];
				} while ($target['einh10'] and $munition['damage'] >= $einh[10]['def']);
			}
			if ($target['einh11'] and $munition['damage'] >= $einh[11]['def']){ 
				do {
					$v['11']++; $kills++;
					$target['einh11']--;
					$munition['damage'] -= $einh[11]['def'];
				} while ($target['einh11'] and $munition['damage'] >= $einh[11]['def']);
			}
			if ($target['einh15'] and $munition['damage'] >= $einh[15]['def']){ 
				do {
					$v['15']++; $kills++;
					$target['einh15']--;
					$munition['damage'] -= $einh[15]['def'];
				} while ($target['einh15'] and $munition['damage'] >= $einh[15]['def']);
			}
	} while ($munition['damage'] and $kills != 0);
	unset($units);
	$einheit = 0;
	do {
		$einheit++;
		if ($v[$einheit]){
			$punkte    += ($v[$einheit]*$einh[$einheit]['def']);
			$verluste .= $v[$einheit].' x '.$einh[$einheit]['name'].'<br />';
		}
	} while ($einheit < 15);
	if (!$verluste) { $verluste = 'Es wurde nichts zerst&ouml;rt.'; }
	else { 
		$verluste = 'Es wurden folgende Einheiten zerst&ouml;rt:<br />'.$verluste; 
		$select = "UPDATE `user` SET `plasmapunkte` = plasmapunkte + ".($punkte-$munition['damage'])." WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
		mysql_query($select);
	}
	$einheit = 0;
	do {
		$einheit++;
		$units += $target['einh'.$einheit];
	} while ($einheit < 15);	
	if ($units == 0 and $target){
		mysql_query("DELETE FROM `missionen` WHERE `id` = '".number_format($_POST['target'],0,'','')."' AND `pid` = '".number_format($_POST['pid'],0,'','')."' LIMIT 1;");
	} elseif ($verluste and $target) {
		mysql_query("UPDATE `missionen` SET `einh1` = '".$target['einh1']."', `einh2` = '".$target['einh2']."', `einh3` = '".$target['einh3']."', `einh4` = '".$target['einh4']."', `einh5` = '".$target['einh5']."', `einh6` = '".$target['einh6']."', `einh7` = '".$target['einh7']."', `einh8` = '".$target['einh8']."', `einh10` = '".$target['einh10']."', `einh11` = '".$target['einh11']."', `einh12` = '".$target['einh12']."', `einh13` = '".$target['einh13']."', `einh15` = '".$target['einh15']."' WHERE `id` = '".number_format($_POST['target'],0,'','')."' AND `pid` = '".number_format($_POST['pid'],0,'','')."' LIMIT 1;");
	}

	$i=0;
	do {
		$i++;
		if (!$v[$i]){$v[$i]=0;}
	} while ($i<15);
	
	$select = "UPDATE `stats` SET `dp1` = dp1 + ".$v[1].", `dp2` = dp2 + ".$v[2].", `dp3` = dp3 + ".$v[3].", `dp4` = dp4 + ".$v[4].", `dp5` = dp5 + ".$v[5].", `dp6` = dp6 + ".$v[6].", `dp7` = dp7 + ".$v[7].", `dp8` = dp8 + ".$v[8].", `dp9` = dp9 + ".$v[9].", `dp10` = dp10 + ".$v[10].", `dp11` = dp11 + ".$v[11].", `dp12` = dp12 + ".$v[12].", `dp13` = dp13 + ".$v[13].", `dp14` = dp14 + ".$v[14].", `dp15` = dp15 + ".$v[15]." WHERE `id` = ".$_SESSION['user']['omni'].";";
	mysql_query($select);	
	$select = "UPDATE `stats` SET `vp1` = vp1 + ".$v[1].", `vp2` = vp2 + ".$v[2].", `vp3` = vp3 + ".$v[3].", `vp4` = vp4 + ".$v[4].", `vp5` = vp5 + ".$v[5].", `vp6` = vp6 + ".$v[6].", `vp7` = vp7 + ".$v[7].", `vp8` = vp8 + ".$v[8].", `vp9` = vp9 + ".$v[9].", `vp10` = vp10 + ".$v[10].", `vp11` = vp11 + ".$v[11].", `vp12` = vp12 + ".$v[12].", `vp13` = vp13 + ".$v[13].", `vp14` = vp14 + ".$v[14].", `vp15` = vp15 + ".$v[15]." WHERE `id` = ".$target['start'].";";
	mysql_query($select);	
	$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Missionszentrum', '".$target['start']."', '".date('U')."', '0', 'Plasmabeschuss von ".$_SESSION['user']['omni']." auf Mission #".$target['id']."', '".$verluste."' );";
	mysql_query($select);	
	$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Raumstation', '".$_SESSION['user']['omni']."', '".date('U')."', '0', 'Plasmabeschuss auf Mission #".htmlentities($_POST['target'])."', '".$verluste."' );";
	mysql_query($select);	}

$raumstation['scanner']++;
$raumstation['plasma']++;

$content .= template('raumstation');

$content = tag2value('scanner_eisen' , ($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['eisen'], $content );
$content = tag2value('scanner_titan' , ($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['titan'], $content );
$content = tag2value('scanner_uran'  , ($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['uran'], $content );
$content = tag2value('scanner_oel'   , ($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['oel'], $content );
$content = tag2value('scanner_gold'  , ($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['gold'], $content );
$content = tag2value('scanner_chanje', ($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['chanje'], $content );
$content = tag2value('scanner_dauer',  time2str(($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['zeit']), $content );
$content = tag2value('lvl_scanner',    ($raumstation['scanner']-1), $content );

$content = tag2value('plasma_eisen' , ($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['eisen'], $content );
$content = tag2value('plasma_titan' , ($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['titan'], $content );
$content = tag2value('plasma_uran'  , ($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['uran'], $content );
$content = tag2value('plasma_oel'   , ($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['oel'], $content );
$content = tag2value('plasma_gold'  , ($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['gold'], $content );
$content = tag2value('plasma_chanje', ($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['chanje'], $content );
$content = tag2value('plasma_dauer',  time2str(($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['zeit']), $content );
$content = tag2value('lvl_plasma',    ($raumstation['plasma']-1), $content );

$bauen['scanner'] = '<a href="raumstation.php?'.SID.'&amp;bau=scanner">bauen</a>'; 
$bauen['plasma']  = '<a href="raumstation.php?'.SID.'&amp;bau=plasma">bauen</a>'; 

if ($ressis['eisen'] < (($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['eisen'])) { $bauen['scanner'] = '<font style="color: red;">zu teuer</font>'; $teuer = 1;}
if ($ressis['titan'] < (($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['titan'])) { $bauen['scanner'] = '<font style="color: red;">zu teuer</font>'; $teuer = 1;}
if ($ressis['oel'] < (($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['oel'])) { $bauen['scanner'] = '<font style="color: red;">zu teuer</font>'; $teuer = 1;}
if ($ressis['uran'] < (($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['uran'])) { $bauen['scanner'] = '<font style="color: red;">zu teuer</font>'; $teuer = 1;}
if ($ressis['gold'] < (($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['gold'])) { $bauen['scanner'] = '<font style="color: red;">zu teuer</font>'; $teuer = 1;}
if ($ressis['chanje'] < (($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['chanje'])) { $bauen['scanner'] = '<font style="color: red;">zu teuer</font>'; $teuer = 1;}

if ($ressis['eisen'] < (($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['eisen'])) { $bauen['plasma'] = '<font style="color: red;">zu teuer</font>'; $teuer2 = 2;}
if ($ressis['titan'] < (($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['titan'])) { $bauen['plasma'] = '<font style="color: red;">zu teuer</font>'; $teuer2 = 2;}
if ($ressis['oel'] < (($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['oel'])) { $bauen['plasma'] = '<font style="color: red;">zu teuer</font>'; $teuer2 = 2;}
if ($ressis['uran'] < (($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['uran'])) { $bauen['plasma'] = '<font style="color: red;">zu teuer</font>'; $teuer2 = 2;}
if ($ressis['gold'] < (($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['gold'])) { $bauen['plasma'] = '<font style="color: red;">zu teuer</font>'; $teuer2 = 2;}
if ($ressis['chanje'] < (($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['chanje'])) { $bauen['plasma'] = '<font style="color: red;">zu teuer</font>'; $teuer2 = 2;}

$result = mysql_query("SELECT * FROM `raumstation` WHERE `omni` = '".$_SESSION['user']['omni']."';");
$raumstation = mysql_fetch_array($result);

if ($gebaeude['raumstation'] <= ($raumstation['scanner'])) {
	$teuer = 1;
	$bauen['scanner'] = '<font style="color: #57ae4b;">zu hoch</font>';
}

if ($gebaeude['raumstation'] <= ($raumstation['plasma'])) {
	$teuer = 2;
	$bauen['plasma'] = '<font style="color: #57ae4b;">zu hoch</font>';
}

$raumstation['scanner']++;
$raumstation['plasma']++;

if ($teuer != 1 and $_GET['bau'] == 'scanner' and !$raumstation['nextscanner'] and !$raumstation['nextplasma'] and $bauen['scanner'] != '<font style="color: #57ae4b;">zu hoch</font>') {
	mysql_query("UPDATE `raumstation` SET `nextscanner` = '".(date('U')+(($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['zeit']))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;");
	$raumstation['nextscanner'] = (date('U')+(($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['zeit']));
	$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '6', '".$_SESSION['user']['omni']."', '".$raumstation['nextscanner']."');";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen']-($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['eisen'])."',`titan` = '".($ressis['titan']-($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['titan'])."',`oel` = '".($ressis['oel']-($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['oel'])."',`uran` = '".($ressis['uran']-($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['uran'])."',`gold` = '".($ressis['gold']-($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['gold'])."',`chanje` = '".($ressis['chanje']-($raumstation['scanner']*$raumstation['scanner'])*$rst['scanner']['chanje'])."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);	
}

if ($teuer2 != 2 and $_GET['bau'] == 'plasma' and !$raumstation['nextscanner'] and !$raumstation['nextplasma'] and $bauen['plasma'] != '<font style="color: #57ae4b;">zu hoch</font>') {
	mysql_query("UPDATE `raumstation` SET `nextplasma` = '".(date('U')+(($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['zeit']))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;");
	$raumstation['nextplasma'] = (date('U')+(($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['zeit']));
	$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '6', '".$_SESSION['user']['omni']."', '".$raumstation['nextplasma']."');";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen']-($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['eisen'])."',`titan` = '".($ressis['titan']-($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['titan'])."',`oel` = '".($ressis['oel']-($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['oel'])."',`uran` = '".($ressis['uran']-($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['uran'])."',`gold` = '".($ressis['gold']-($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['gold'])."',`chanje` = '".($ressis['chanje']-($raumstation['plasma']*$raumstation['plasma'])*$rst['plasma']['chanje'])."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);	
}

if ($raumstation['nextscanner'] or $raumstation['nextplasma']) {
	$abbrechen = 'Derzeitigen Bauvorgang <b><a href="raumstation.php?'.SID.'&abbrechen=1"><font color="#b90101">ABBRECHEN</font></a></b><br />(Alle Ressourcen f&uuml;r diesen Auftrag gehen verloren!)';
}

if ($_GET[abbrechen] == 1){
	$select = "UPDATE `raumstation` SET `nextscanner` = '0', `nextplasma` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$selectResult   = mysql_query($select);
	$abbrechen = "Der aktuelle Bauvorgang wurde abgebrochen.";
}

if ($raumstation['nextscanner']) {
	$bauen['scanner'] = countdown(round($raumstation['nextscanner']-date('U'))); 
	$bauen['plasma']  = '-'; 
}

if ($raumstation['nextplasma']) {
	$bauen['plasma'] = countdown(round($raumstation['nextplasma']-date('U'))); 
	$bauen['scanner']= '-'; 
}

$select = "SELECT * FROM `munition` WHERE `omni` = '".$_SESSION['user']['omni']."' AND `finished` <= '".date('U')."';";
$result = mysql_query($select);
if (@mysql_num_rows($result) == 0){
	$munition = '<option value="0">keine Munition vorhanden</option>';
} else {
	do {
		$row = mysql_fetch_array($result);
		if ($row){ $munition .= '<option value="'.$row['id'].'">'.$row['damage'].' Schaden</option>'; }
	} while ($row);
}

$select = "SELECT * FROM `munition` WHERE `omni` = '".$_SESSION['user']['omni']."' AND `finished` > '".date('U')."';";
$result = mysql_query($select);
$row    = @mysql_fetch_array($result);
if (!$row) {
	$munbauen  = template('munition_bauen');
} else {
	$muninbau = 1;
	$munbauen  = template('munition_bauen_countdown');
	$munbauen = tag2value('restdauer',countdown($row['finished']-date('U')), $munbauen );
	$munbauen = tag2value('schaden',$row['damage'], $munbauen );
}

if ($muninbau != 1 and $rounds >= (($raumstation['plasma']-1)*2)){ $munbauen  = template('munition_bauen_voll'); }

// scanner

if ($_POST['submit'] == "Kosten berechnen"){
	$to_position  = position($_POST['scanbase']);
	$own_position = position($_SESSION['user']['omni']);
	$own_pos = ($own_position[x]+($own_position[y]+($own_position[z]*20)));
	$to_pos  = ($to_position[x]+($to_position[y]+($to_position[z]*20)));

	if ( $own_position[x] > $to_position[x] ) { $entfernung = $own_position[x] - $to_position[x]; }
	else { $entfernung = $to_position[x] - $own_position[x]; }
	
	if ( ( $own_position[y] + ( $own_position[z] * 20 ) ) > ( $to_position[y] + ( $to_position[z] * 20 ) ) ) { $entfernung += ( $own_position[y] + ( $own_position[z] * 20 ) ) - ( $to_position[y] + ( $to_position[z] * 20 ) ); }
	else { $entfernung += ( $to_position[y] + ( $to_position[z] * 20 ) ) - ( $own_position[y] + ( $own_position[z] * 20 ) ); }	

	$kosten     = $entfernung*2.5*($raumstation['scanner']*30)+250;
	$scankosten = $kosten.' Uran';
}



$result = mysql_query("SELECT * FROM `scans` WHERE `userid` = '".$_SESSION['user']['omni']."';");

if ($_POST['submit'] == "scannen" and $_POST['scanbase'] == number_format($_POST['scanbase'],0,'','') and mysql_num_rows($result) == 0 and $raumstation['scanner'] > 1){
	$to_position  = position($_POST['scanbase']);
	$own_position = position($_SESSION['user']['omni']);
	$own_pos = ($own_position[x]+($own_position[y]+($own_position[z]*20)));
	$to_pos  = ($to_position[x]+($to_position[y]+($to_position[z]*20)));

	if ( $own_position[x] > $to_position[x] ) { $entfernung = $own_position[x] - $to_position[x]; }
	else { $entfernung = $to_position[x] - $own_position[x]; }
	
	if ( ( $own_position[y] + ( $own_position[z] * 20 ) ) > ( $to_position[y] + ( $to_position[z] * 20 ) ) ) { $entfernung += ( $own_position[y] + ( $own_position[z] * 20 ) ) - ( $to_position[y] + ( $to_position[z] * 20 ) ); }
	else { $entfernung += ( $to_position[y] + ( $to_position[z] * 20 ) ) - ( $own_position[y] + ( $own_position[z] * 20 ) ); }	

	$kosten     = $entfernung*2.5*($raumstation['scanner']*30)+250;
	$scankosten = $kosten.' Uran';
	
	$reichweite = $raumstation['scanner']*10-10;
	
	$result = mysql_query("SELECT * FROM `forschungen` WHERE `omni` = '".$_SESSION['user']['omni']."';");
	$forschungen = mysql_fetch_array($result);
	
	$result = mysql_query("SELECT * FROM `forschungen` WHERE `omni` = '".$_POST['scanbase']."';");
	$target_forschungen = mysql_fetch_array($result);
	
	if ($target_forschungen['spionage'] != 0 and $forschungen['spionage'] != 0) {
		$dauer = number_format(($target_forschungen['spionage']/$forschungen['spionage'])*(40-( $raumstation['scanner'] * 1.5 ))*60,0,'','');
	}
	
	$missionen = mysql_query("SELECT * FROM `missionen` WHERE `type` != '3' and `start` = '".$_POST['scanbase']."';");

	$scan = 'Es wurde die Basis bei '.$_POST['scanbase'].' gescannt.<br />Aktive Missionen: '.mysql_num_rows($missionen).'<br /><br />';
	
	do {
		$data = mysql_fetch_array($missionen);
		if ($data){
			if ($data[einh1]) { $units .= $data[einh1].' '.$einh[1][name].'<br />';}
			if ($data[einh2]) { $units .= $data[einh2].' '.$einh[2][name].'<br />';}
			if ($data[einh3]) { $units .= $data[einh3].' '.$einh[3][name].'<br />';}
			if ($data[einh4]) { $units .= $data[einh4].' '.$einh[4][name].'<br />';}
			if ($data[einh5]) { $units .= $data[einh5].' '.$einh[5][name].'<br />';}
			if ($data[einh6]) { $units .= $data[einh6].' '.$einh[6][name].'<br />';}
			if ($data[einh7]) { $units .= $data[einh7].' '.$einh[7][name].'<br />';}
			if ($data[einh8]) { $units .= $data[einh8].' '.$einh[8][name].'<br />';}
			if ($data[einh9]) { $units .= $data[einh9].' '.$einh[9][name].'<br />';}
			if ($data[einh10]) { $units .= $data[einh10].' '.$einh[10][name].'<br />';}
			if ($data[einh11]) { $units .= $data[einh11].' '.$einh[11][name].'<br />';}
			if ($data[einh12]) { $units .= $data[einh12].' '.$einh[12][name].'<br />';}
			if ($data[einh13]) { $units .= $data[einh13].' '.$einh[13][name].'<br />';}
			if ($data[einh14]) { $units .= $data[einh14].' '.$einh[14][name].'<br />';}
			if ($data[einh15]) { $units .= $data[einh15].' '.$einh[15][name].'<br />';}
			$scan .= "<b>Mission:</b><br />".$units."geplante R&uuml;ckkehr: ".date('H:i d.m.y', ($data['return'] + (rand(( 0 - (30 - $raumstation['scanner']*2 + 1 )), ( 30 - ( $raumstation['scanner']*2 - 1 )))*60))).' (+/- '.( 30 - ( $raumstation['scanner']*2 - 1 )).' Minuten)<br /><br />';
			unset($units);
		}
	} while ($data);
	
	$ressis['uran'] -= $kosten;
	if ($ressis['uran'] < 0){ $ressis['uran'] += $kosten; $content .= '<b>Du hast nicht genug Uran</b>'; }
	elseif ($entfernung*2.5 > $reichweite) { $ressis['uran'] += $kosten; $content .= '<b>Dieser Spieler ist ausserhalb der Reichweite deines Scanners.</b>'; }
	else {
		mysql_query("UPDATE `ressis` SET `uran` = '".$ressis[uran]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;");
		mysql_query("INSERT INTO `scans` ( `id` , `userid` , `start` , `date` , `text` ) VALUES ( '', '".$_SESSION['user']['omni']."', '".date('U')."', '".(date('U')+$dauer)."', '".$scan."' );");
	}
}

if ($_POST['delscan'] == 1){
	mysql_query("DELETE FROM `scans` WHERE `userid` = '".$_SESSION['user']['omni']."';");
}

// end scanner

$content = tag2value('munition_bauen', '<b>'.$verluste.'</b><br />'.$munbauen, $content );
$content = tag2value('select', $munition, $content );

$result = mysql_query("SELECT * FROM `scans` WHERE `date` > '".date('U')."' and `userid` = '".$_SESSION['user']['omni']."';");
$result2 = mysql_query("SELECT * FROM `scans` WHERE `date` <= '".date('U')."' and `userid` = '".$_SESSION['user']['omni']."';");
if ($raumstation['scanner'] < 2) {
	$content = tag2value('scanner', template('keinscanner'), $content );
} elseif (mysql_num_rows($result) == 0 and mysql_num_rows($result2) == 0) {
	$content = tag2value('scanner', template('scanner'), $content );
	if (!$scankosten){$scankosten = "noch nicht berechnet";}
	$content = tag2value('scankosten', $scankosten, $content );
	$content = tag2value('scanbase', $_POST['scanbase'], $content );
	$content = tag2value('ungenauigkeit', 30 - ( $raumstation['scanner']*2 - 1 ), $content );
	$content = tag2value('lvl_scanner', ( ($raumstation['scanner'] - 1)*1.5), $content );
	$content = tag2value('kosten', ($raumstation['scanner']*30-30), $content );
	$content = tag2value('reichweite', ($raumstation['scanner']*10-10), $content );
	
} elseif (mysql_num_rows($result2) != 0) {
	$row = mysql_fetch_array($result2);
	$content = tag2value('scanner', template('decrypted'), $content );
	$content = tag2value('info', $row['text'], $content );	
} else {
	$row = mysql_fetch_array($result);
	$content = tag2value('scanner', template('decrypt'), $content );
	$content = tag2value('restdauer', percentbar(($row['date']-date(U)),($row['date']-$row['start']),310), $content );
}

$content = tag2value('id', $_POST['target'], $content );
$content = tag2value('pid', $_POST['pid'], $content );

if ($raumstation['scanner'] > 10) {
	$bauen['scanner'] = 'max'; 
}
if ($raumstation['plasma'] > 10) {
	$bauen['plasma'] = 'max'; 
}

$content = tag2value('scanner_bauen', $bauen['scanner'], $content );
$content = tag2value('plasma_bauen',  $bauen['plasma'],  $content );
//$content   = tag2value("onload",$onload,$content);
$content   = tag2value("abbrechen",$abbrechen,$content);
// generierte seite ausgeben
echo showpage($content.template('footer'), $_SESSION['user']['omni'],$onload);
//echo $content;
//show_vars();
?>