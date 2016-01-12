<?php
//////////////////////////////////
// Komplettuebersicht           //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////
// Kommentare:
// - Ressistand
// - Status Spieler
// - Uebersicht Missionen
// - Uebersicht klon-Missionen
// - Status Nachrichten
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include("functions.php");
include('einheiten_preise.php');
include('def_preise.php');

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
$ressis = ressistand($_SESSION['user']['omni']);
$content .= $ressis['html'];

$dbh = db_connect();

$content .= '<br /><br />';
if ($_GET['missid'] and $_GET['missid'] == number_format($_GET['missid'],0,'','') AND $_GET['abbrechen'] == 1){
	$select  = "SELECT * FROM `missionen` where `id` = '".$_GET['missid']."' AND `start` = '".$_SESSION['user']['omni']."' AND `parsed` != '1';";
	$mission = mysql_query($select);
	$data    = mysql_fetch_array($mission);
	if (!$data) { 
		// $content .= '<center>Du hast keine Berechtigung diese Mission abzubrechen!</center><br /><br />'; 
	}
	else {
		$return = (date('U')-$data['started'])+date('U');
		$select = "UPDATE `missionen` SET `return` = '".$return."', `parsed` = '1' WHERE `id` = '".$_GET['missid']."' LIMIT 1;";
		mysql_query($select);
		$select = "UPDATE `events` SET `date` = '".$return."' WHERE `type` = '1' AND `eid` = '".$_GET['missid']."' LIMIT 1;";
		mysql_query($select);
		
		$log = 'Mission #'.$_GET['missid'].' von '.$_SESSION['user']['omni'].' abgebrochen.';
		mysql_query("INSERT INTO `log` ( `id` , `time` , `action` ) VALUES ( '', UNIX_TIMESTAMP(), '".$log."' );");
	}
	$_GET['id'] = $_GET['missid'];
}
// mission betrachten
if ($_GET['id'] and $_GET['id'] == number_format($_GET['id'],0,'','')){
	
	$select  = "SELECT * FROM `missionen` where `id` = '".$_GET['id']."' AND `start` = '".$_SESSION['user']['omni']."';";
	$mission = mysql_query($select);
	$data    = mysql_fetch_array($mission);
	if (!$data) { 
		// $content .= '<center>Du hast keine M&ouml;glichkeit diese Mission zu betrachten!</center><br /><br />'; 
	}
	else {
		if ($data['einh1']) { $units .= $data['einh1'].' <a href="javascript:popUp(\'details_einh.php?id=1\',400)">'.$einh[1]['name'].'</a><br />';}
		if ($data['einh2']) { $units .= $data['einh2'].' <a href="javascript:popUp(\'details_einh.php?id=2\',400)">'.$einh[2]['name'].'</a><br />';}
		if ($data['einh3']) { $units .= $data['einh3'].' <a href="javascript:popUp(\'details_einh.php?id=3\',400)">'.$einh[3]['name'].'</a><br />';}
		if ($data['einh4']) { $units .= $data['einh4'].' <a href="javascript:popUp(\'details_einh.php?id=4\',400)">'.$einh[4]['name'].'</a><br />';}
		if ($data['einh5']) { $units .= $data['einh5'].' <a href="javascript:popUp(\'details_einh.php?id=5\',400)">'.$einh[5]['name'].'</a><br />';}
		if ($data['einh6']) { $units .= $data['einh6'].' <a href="javascript:popUp(\'details_einh.php?id=6\',400)">'.$einh[6]['name'].'</a><br />';}
		if ($data['einh7']) { $units .= $data['einh7'].' <a href="javascript:popUp(\'details_einh.php?id=7\',400)">'.$einh[7]['name'].'</a><br />';}
		if ($data['einh8']) { $units .= $data['einh8'].' <a href="javascript:popUp(\'details_einh.php?id=8\',400)">'.$einh[8]['name'].'</a><br />';}
		if ($data['einh9']) { $units .= $data['einh9'].' <a href="javascript:popUp(\'details_einh.php?id=9\',400)">'.$einh[9]['name'].'</a><br />';}
		if ($data['einh10']) { $units .= $data['einh10'].' <a href="javascript:popUp(\'details_einh.php?id=10\',400)">'.$einh[10]['name'].'</a><br />';}
		if ($data['einh11']) { $units .= $data['einh11'].' <a href="javascript:popUp(\'details_einh.php?id=11\',400)">'.$einh[11]['name'].'</a><br />';}
		if ($data['einh12']) { $units .= $data['einh12'].' <a href="javascript:popUp(\'details_einh.php?id=12\',400)">'.$einh[12]['name'].'</a><br />';}
		if ($data['einh13']) { $units .= $data['einh13'].' <a href="javascript:popUp(\'details_einh.php?id=13\',400)">'.$einh[13]['name'].'</a><br />';}
		if ($data['einh14']) { $units .= $data['einh14'].' <a href="javascript:popUp(\'details_einh.php?id=14\',400)">'.$einh[14]['name'].'</a><br />';}
		if ($data['einh15']) { $units .= $data['einh15'].' <a href="javascript:popUp(\'details_einh.php?id=15\',400)">'.$einh[15]['name'].'</a><br />';}
		
		$restzeit = $data['ankunft']-date(U);
		$dauer    = $data['ankunft']-$data[started];
		$strecke = 'hinweg';
		if ($data['parsed'] == 1){ $restzeit = $data['return']-date(U); $strecke = 'r&uuml;ckweg';}

		if ($data['type'] == 1){ $mission = 'angreifen'; }
		elseif ($data['type'] == 2){ $mission = 'transportieren'; }
		elseif ($data['type'] == 3){ $mission = '&uuml;berf&uuml;hren'; }
		elseif ($data['type'] == 4){ $mission = 'sammeln'; }
		
		if (!$data['parsed']){
			$abbruch = countup(date('U')-$data['started']);
		} else {
			$abbruch = '---';
		}
			
		$content .= '
		<br /><center><table border="1" cellspacing="0" class="standard">
	<tr align="center"><th colspan="2" style="width:300px"><center><b>Missionsdetails f&uuml;r Mission '.$_GET['id'].':</b><br /></th></tr>
	<tr class="standard" align="center"><td style="width:150px">Mission</td><td style="width:150px">'.$mission.' ('.$strecke.')</td></tr>
	<tr class="standard" align="center"><td style="width:150px">Ziel</td><td style="width:150px">'.$data['ziel'].'</td></tr>
	<tr class="standard" align="center"><td style="width:150px">Eisen</td><td style="width:150px">'.$data['eisen'].'</td></tr>
	<tr class="standard" align="center"><td style="width:150px">Titan</td><td style="width:150px">'.$data['titan'].'</td></tr>
	<tr class="standard" align="center"><td style="width:150px">Oel</td><td style="width:150px">'.$data['oel'].'</td></tr>
	<tr class="standard" align="center"><td style="width:150px">Uran</td><td style="width:150px">'.$data['uran'].'</td></tr>
	<tr class="standard" align="center"><td style="width:150px">Gold</td><td style="width:150px">'.$data['gold'].'</td></tr>
	<tr class="standard" align="center"><td style="width:150px">Chanje</td><td style="width:150px">'.$data['chanje'].'</td></tr>	
	<tr class="standard" align="center"><td style="width:150px">R&uuml;ckkehr bei Abbruch</td><td style="width:150px">'.$abbruch.'</td></tr>
	<tr class="standard" align="center"><td style="width:150px">Einheiten</td><td style="width:150px;" align="left">'.$units.'</td></tr>';

                $abbruch = 'mission.php?'.SID.'&amp;missid='.$_GET['id'].'&amp;abbrechen=1';

		$content .= '<tr align="center"><td colspan="2" style="width:300px; height:20px"><br />'.percentbar($restzeit,$dauer,290).'</td></tr>';
		if (!$data['parsed']){$content .= '<tr align="center"><td colspan="2" style="width:300px"><br /><b>Mission <a href="#" onclick="check(\'document.location.href=\\\''.$abbruch.'\\\'\', \'Willst du diese Mission wirklich abbrechen?\')"><font class="red">ABBRECHEN</b></font></a><br /><br /></td></tr></table><br /><br /></center>';}
		else { $content .= '</table><br /><br /></center>'; }
	}
}

$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION['user']['omni']."';";
$selectResult   = mysql_query($select);
$gebaeude = mysql_fetch_array($selectResult);

$select = "SELECT * FROM `missionen` WHERE `start` = '".$_SESSION['user']['omni']."' AND `ankunft` > '".date(U)."' AND `parsed` != '1' ORDER BY `ankunft` ASC;";
$result = mysql_query($select);

if ($_POST[starten] == 'Mission starten'){ 
	$anzahl    = ($_POST['anz1']+$_POST['anz2']+$_POST['anz3']+$_POST['anz4']+$_POST['anz5']+$_POST['anz6']+$_POST['anz7']+$_POST['anz8']+$_POST['anz9']+$_POST['anz10']+$_POST['anz11']+$_POST['anz12']+$_POST['anz13']+$_POST['anz14']+$_POST['anz15']);
	if ($_POST[target] == '' or $_POST[target] != number_format($_POST[target],0,'','')){ die ($content.'<center>Fehler beim starten der Mission, kein Ziel angeben.</center></body></html>'); }
	if ($_POST['mission'] == '' or $_POST['mission'] != number_format($_POST['mission'],0,'','')){ die ($content.'<center>Fehler beim starten der Mission, Aufgabe nicht klar.</center></body></html>'); }
	if ($_POST['anz15'] > 0 and $_POST['mission'] == 1){ die ($content.'<center>Fehler beim starten der Mission, Sammler k&ouml;nnen nicht angreifen.</center></body></html>'); }
	if (mysql_num_rows($result) >= ($gebaeude[missionszentrum]*2)){ die ($content.'<center>Du hast bereits '.mysql_num_rows($result).' von '.($gebaeude[missionszentrum]*2).' m&ouml;glichen Missionen laufen.</center></body></html>'); }
	$to_position  = position($_POST[target]);
	$own_position = position($_SESSION['user']['omni']);
	#$own_pos = ($own_position['x']+($own_position['y']+($own_position['z']*500)));
	$own_pos = ($own_position['x']+($own_position['y']+($own_position['z']*20)));
	$to_pos  = ($to_position['x']+($to_position['y']+($to_position['z']*20)));

	if ( $own_position['x'] > $to_position['x'] ) { $entfernung = $own_position['x'] - $to_position['x']; }
	else { $entfernung = $to_position['x'] - $own_position['x']; }
	
	if ( ( $own_position['y'] + ( $own_position['z'] * 20 ) ) > ( $to_position['y'] + ( $to_position['z'] * 20 ) ) ) { $entfernung += ( $own_position['y'] + ( $own_position['z'] * 20 ) ) - ( $to_position['y'] + ( $to_position['z'] * 20 ) ); }
	else { $entfernung += ( $to_position['y'] + ( $to_position['z'] * 20 ) ) - ( $own_position['y'] + ( $own_position['z'] * 20 ) ); }	

	$verbrauch = (($_POST['anz1']*$einh[1]['verbrauch'])+($_POST['anz2']*$einh[2]['verbrauch'])+($_POST['anz3']*$einh[3]['verbrauch'])+($_POST['anz4']*$einh[4]['verbrauch'])+($_POST['anz5']*$einh[5]['verbrauch'])+($_POST['anz6']*$einh[6]['verbrauch'])+($_POST['anz7']*$einh[7]['verbrauch'])+($_POST['anz8']*$einh[8]['verbrauch'])+($_POST['anz9']*$einh[9]['verbrauch'])+($_POST['anz10']*$einh[10]['verbrauch'])+($_POST['anz11']*$einh[11]['verbrauch'])+($_POST['anz12']*$einh[12]['verbrauch'])+($_POST['anz13']*$einh[13]['verbrauch'])+($_POST['anz14']*$einh[14]['verbrauch'])+($_POST['anz15']*$einh[15]['verbrauch']))/10*$entfernung;
	$platz     = ($_POST['anz1']*$einh[1]['space'])+($_POST['anz2']*$einh[2]['space'])+($_POST['anz3']*$einh[3]['space'])+($_POST['anz4']*$einh[4]['space'])+($_POST['anz5']*$einh[5]['space'])+($_POST['anz6']*$einh[6]['space'])+($_POST['anz7']*$einh[7]['space'])+($_POST['anz8']*$einh[8]['space'])+($_POST['anz9']*$einh[9]['space'])+($_POST['anz10']*$einh[10]['space'])+($_POST['anz11']*$einh[11]['space'])+($_POST['anz12']*$einh[12]['space'])+($_POST['anz13']*$einh[13]['space'])+($_POST['anz14']*$einh[14]['space'])+($_POST['anz15']*$einh[15]['space']);
	$maxspeed = 1000;
	
	// soldaten in transporter packen.
	$soldaten = $_POST['anz1']+$_POST['anz2']+$_POST['anz3']+$_POST['anz4'];
	$tr_space = ($_POST['anz12']*3)+($_POST['anz13']*10);
	if (($tr_space - $soldaten) >= 0) {unset( $soldaten); }
		
	do {
		$i++;
		if ($soldaten){ 
			if ($_POST['anz1'] and $einh[1]['speed'] < $maxspeed){ $maxspeed = $einh[1]['speed']; }
			if ($_POST['anz2'] and $einh[2]['speed'] < $maxspeed){ $maxspeed = $einh[2]['speed']; }
			if ($_POST['anz3'] and $einh[3]['speed'] < $maxspeed){ $maxspeed = $einh[3]['speed']; }
			if ($_POST['anz4'] and $einh[4]['speed'] < $maxspeed){ $maxspeed = $einh[4]['speed']; }
		}
		if ($_POST['anz5'] and $einh[5]['speed'] < $maxspeed){ $maxspeed = $einh[5]['speed']; }
		if ($_POST['anz6'] and $einh[6]['speed'] < $maxspeed){ $maxspeed = $einh[6]['speed']; }
		if ($_POST['anz7'] and $einh[7]['speed'] < $maxspeed){ $maxspeed = $einh[7]['speed']; }
		if ($_POST['anz8'] and $einh[8]['speed'] < $maxspeed){ $maxspeed = $einh[8]['speed']; }
		if ($_POST['anz9'] and $einh[9]['speed'] < $maxspeed){ $maxspeed = $einh[9]['speed']; }
		if ($_POST['anz10'] and $einh[10]['speed'] < $maxspeed){ $maxspeed = $einh[10]['speed']; }
		if ($_POST['anz11'] and $einh[11]['speed'] < $maxspeed){ $maxspeed = $einh[11]['speed']; }
		if ($_POST['anz12'] and $einh[12]['speed'] < $maxspeed){ $maxspeed = $einh[12]['speed']; }
		if ($_POST['anz13'] and $einh[13]['speed'] < $maxspeed){ $maxspeed = $einh[13]['speed']; }
		if ($_POST['anz14'] and $einh[14]['speed'] < $maxspeed){ $maxspeed = $einh[14]['speed']; }
		if ($_POST['anz15'] and $einh[15]['speed'] < $maxspeed){ $maxspeed = $einh[15]['speed']; }
	} while ($i < 15);
	$i=0;
	
	if ($_POST['speed'] == 10){ $speedstep10 = selected; }
	elseif ($_POST['speed'] == 20){ $speedstep20 = selected; }
	elseif ($_POST['speed'] == 30){ $speedstep30 = selected; }
	elseif ($_POST['speed'] == 40){ $speedstep40 = selected; }
	elseif ($_POST['speed'] == 50){ $speedstep50 = selected; }
	elseif ($_POST['speed'] == 60){ $speedstep60 = selected; }
	elseif ($_POST['speed'] == 70){ $speedstep70 = selected; }
	elseif ($_POST['speed'] == 80){ $speedstep80 = selected; }
	elseif ($_POST['speed'] == 90){ $speedstep90 = selected; }
	else { $speedstep100 = selected; $_POST['speed'] == 100;}
	
	if ($_POST['speed']){ 
		$maxspeed  = ($maxspeed/100)*$_POST['speed'];
		$verbrauch = round(($verbrauch/100)*$_POST['speed'],0);
	}
	$verbrauch_einh = round(($verbrauch/30),0);
	if ($verbrauch_einh == 0){$verbrauch_einh = 1;}
	
	$_POST['eisen'] = number_format($_POST['eisen'],0,'','');
	$_POST['titan'] = number_format($_POST['titan'],0,'','');
	$_POST['oel'] = number_format($_POST['oel'],0,'','');
	$_POST['uran'] = number_format($_POST['uran'],0,'','');
	$_POST['gold'] = number_format($_POST['gold'],0,'','');
	$_POST['chanje'] = number_format($_POST['chanje'],0,'','');
	
	$restplatz = ($platz-($verbrauch_einh)-$_POST['eisen']-$_POST['titan']-$_POST['oel']-$_POST['uran']-$_POST['gold']-$_POST['chanje']);
	
	if ($restplatz < 0){ die ($content.'<center>Fehler beim starten der Mission, nicht genug Platz f&uuml;r alle Ressourcen.</center></body></html>'); }
	
	if ($restplatz < 0){ $red = 'color:red;'; }
	
	$hangar = new_units_check($_SESSION['user']['omni']);
	
	do {
		$i++;
		$einheit = 'einh'.$i;
		$anz  = 'anz'.$i;
		if ($hangar[$einheit] < $_POST[$anz]){ die ($content.'<center>Fehler beim starten der Mission, nicht genug '.$einh[$i]['name'].' vorhanden.</center></body></html>'); }
	} while ($i < 15);
	
	if ($_POST['mission'] == 1){ $mission = 'angreifen'; 
		$_POST['eisen']  = 0;
		$_POST['titan']  = 0;
		$_POST['oel']    = 0;
		$_POST['uran']   = 0;
		$_POST['gold']   = 0;
		$_POST['chanje'] = 0;
	}
	elseif ($_POST['mission'] == 2){ $mission = 'transportieren'; }
	elseif ($_POST['mission'] == 3){ $mission = '&uuml;berf&uuml;hren'; }
	elseif ($_POST['mission'] == 4){ $mission = 'sammeln'; 
		$_POST['eisen']  = 0;
		$_POST['titan']  = 0;
		$_POST['oel']    = 0;
		$_POST['uran']   = 0;
		$_POST['gold']   = 0;
		$_POST['chanje'] = 0;
	}
	else { die ($content.'<center>Fehler beim starten der Mission, kein Missions-Ziel angegeben.</center></body></html>'); }
	
	if ($ressis['eisen'] < $_POST['eisen']){ die ($content.'<center>Fehler beim starten der Mission zu wenig Eisen.</center></body></html>'); }
	if ($ressis['titan'] < $_POST['titan']){ die ($content.'<center>Fehler beim starten der Mission zu wenig Titan.</center></body></html>'); }
	if ($ressis['oel'] < ($_POST['oel']+$verbrauch_einh)){ die ($content.'<center>Fehler beim starten der Mission zu wenig Oel.</center></body></html>'); }
	if ($ressis['uran'] < $_POST['uran']){ die ($content.'<center>Fehler beim starten der Mission zu wenig Uran.</center></body></html>'); }
	if ($ressis['gold'] < $_POST['gold']){ die ($content.'<center>Fehler beim starten der Mission zu wenig Gold.</center></body></html>'); }
	if ($ressis['chanje'] < $_POST['chanje']){ die ($content.'<center>Fehler beim starten der Mission zu wenig Chanje.</center></body></html>'); }
	
	
	$select = "SELECT * FROM `user` WHERE `omni` = '".$_POST['target']."';";
	$selectResult   = mysql_query($select);
	$target_detail  = mysql_fetch_array($selectResult, MYSQL_ASSOC);
	
	$points = str_replace('.','',$_SESSION['user']['points']);
	
	// multi schutz
	if ($_SESSION['user']['ip'] == $target_detail['ip'] and $_POST['mission'] != 4 and $_SESSION['user']['omni'] != $target_detail['omni']){
		$select = "INSERT INTO `nachrichten` ( `id` , `from` , `from_name` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_SESSION[user]['name']."', '1', '".date(U)."', '0', '[b][color=\"#b90101\"]MULTI[/color][/b]', 'Hallo!<br /> ich wollte grade eine Multi Mission zu ".$target_detail['omni']." starten.<br /> Bitte behalte meinen und diesen Acc im Auge.<br /><br />MfG<br /> Der potentielle Multi<br /><br />Missionstyp: ".$_POST['mission']."');";
		mysql_query($select);
		die ($content.'<center><b>Und wiedereinmal hat der glorreiche Multi-Schutz verhindert das eine Mission gestartet wurde.</b></center></body></html>');
	}
	$i=0;
	$array = explode(':', $_COOKIE["spacecookie"]);
	do {
		if (md5($target_detail['ip']) == $array[$i] and $_POST['mission'] != 1 and $_POST['mission'] != 4 and $_SESSION['user']['omni'] != $target_detail['omni']){
			$select = "INSERT INTO `nachrichten` ( `id` , `from` , `from_name` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_SESSION[user]['name']."', '1', '".date(U)."', '0', '<b><font color=\\'#b90101\\'>MULTI</font></b>', 'Hallo!<br /> ich wollte grade eine Multi Mission zu ".$target_detail['omni']." starten.<br /> Bitte behalte meinen und diesen Acc im Auge.<br /><br />MfG<br /> Der potentielle Multi!');";
			mysql_query($select);
			die ($content.'<center><b>Und wiedereinmal hat der glorreiche Multi-Schutz verhindert das eine Mission gestartet wurde!</b></center></body></html>');
		}
		$i++;
	} while ($array[$i]);	
	
	$check = 1;
	if ($target_detail['points'] > 50000 and $points > 50000) { $check = 0; }
	
	// n00b schutz
	if ($check) {
		if ($target_detail['timestamp'] != 0 AND (date('U') - $target_detail['timestamp']) > 1209600 AND $_POST['mission'] == 3) { die ($content.'<center>Fehler beim starten der Mission, du kannst keine &Uuml;berf&uuml;hrungen zu inaktiven Spielern starten!</center></body></html>'); }
		elseif ((date('U') - $target_detail['timestamp']) > 1209600) {}
		elseif ($target_detail['points'] < ( $points / 3 ) AND $_POST['mission'] == 1 AND $target_detail){ die ($content.'<center>Fehler beim starten der Mission, suche dir einen st&auml;rkeren Gegner!</center></body></html>'); }
		elseif ($points < ( $target_detail['points'] / 3 ) AND $_POST['mission'] == 1 AND $target_detail){ die ($content.'<center>Fehler beim starten der Mission, suche dir einen schw&auml;cheren Gegner!</center></body></html>'); }
		elseif ($target_detail['points'] < ( $points / 3 ) AND $_POST['mission'] == 3 AND $target_detail['group'] != 1000){ die ($content.'<center>Fehler beim starten der Mission, du kannst keine &Uuml;berf&uuml;hrungen zu schw&auml;cheren Spielern starten!</center></body></html>'); }
		elseif ($points < ( $target_detail['points'] / 3 ) AND $_POST['mission'] == 3 AND $target_detail){ die ($content.'<center>Fehler beim starten der Mission, du kannst keine &Uuml;berf&uuml;hrungen zu st&auml;rkeren Spielern starten!</center></body></html>'); }		
		
		if ($target_detail['points'] < ( $points / 3 ) AND $_POST['mission'] == 2 AND $_POST['gold'] != 0 AND $target_detail['points'] > 0 ){ die ($content.'<center>Fehler beim starten der Mission, du kannst kein Gold zu schw&auml;cheren Spielern transportieren!</center></body></html>'); }
		if ($points < ( $target_detail['points'] / 3 ) AND $_POST['mission'] == 2 AND $_POST['gold'] != 0 AND $target_detail['points'] > 0 ){ die ($content.'<center>Fehler beim starten der Mission, du kannst kein Gold zu st&auml;rkeren Spielern transportieren!</center></body></html>'); }
	}
		
	if ($target_detail['omni'] == $_SESSION['user']['omni'] AND $_POST['mission'] == 1){ die ($content.'<center>Fehler beim starten der Mission, du kannst dich nicht selber angreifen!</center></body></html>'); }
	if ($ressis['oel'] < $verbrauch_einh) { die ($content.'<center>Fehler beim starten der Mission, du hast nicht genug Treibstoff (Oel)</center></body></html>'); }

	
	////// keine angriffe!!!!!!
	$result = mysql_query("SELECT * FROM `angriffssperre` WHERE `end` > '".date('U')."' ORDER BY `end` ASC;");
	$row = @mysql_fetch_array($result);
	
	if ($row['end'] and $_POST['mission'] == 1) {
		die (template('head').$status.$ressis['html'].'<br /><br /><br />Es wurde eine <b>Angriffssperre f&uuml;r alle Spieler bis zum '.date("d.m.y \u\m H:i \h", $row['end']).'</b> verh&auml;ngt.<br /><br />
		Grund: <b>'.$row['grund'].'</b><br /><br />
		&Uuml;berf&uuml;hrungen und Transporte gehen selbstverst&auml;ndlich trotzdem.</body></html>');	
	}
	
	
	if ($_POST['eisen'] < 0){$_POST['eisen'] = 0;}
	if ($_POST['titan'] < 0){$_POST['titan'] = 0;}
	if ($_POST['oel'] < 0){$_POST['oel'] = 0;}
	if ($_POST['uran'] < 0){$_POST['uran'] = 0;}
	if ($_POST['gold'] < 0){$_POST['gold'] = 0;}
	if ($_POST['chanje'] < 0){$_POST['chanje'] = 0;}
	
	$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] - $_POST['eisen'])."',`titan` = '".($ressis['titan'] - $_POST['titan'])."',`oel` = '".($ressis['oel'] - ($_POST['oel']+$verbrauch_einh))."',`uran` = '".($ressis['uran'] - $_POST['uran'])."',`gold` = '".($ressis['gold'] - $_POST['gold'])."',`chanje` = '".($ressis['chanje'] - $_POST['chanje'])."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$selectResult   = mysql_query($select);

	$select = "UPDATE `hangar` SET `einh1` = '".($hangar['einh1']-$_POST['anz1'])."', `einh2` = '".($hangar['einh2']-$_POST['anz2'])."', `einh3` = '".($hangar['einh3']-$_POST['anz3'])."', `einh4` = '".($hangar['einh4']-$_POST['anz4'])."', `einh5` = '".($hangar['einh5']-$_POST['anz5'])."', `einh6` = '".($hangar['einh6']-$_POST['anz6'])."', `einh7` = '".($hangar['einh7']-$_POST['anz7'])."', `einh8` = '".($hangar['einh8']-$_POST['anz8'])."', `einh9` = '".($hangar['einh9']-$_POST['anz9'])."', `einh10` = '".($hangar['einh10']-$_POST['anz10'])."', `einh11` = '".($hangar['einh11']-$_POST['anz11'])."', `einh12` = '".($hangar['einh12']-$_POST['anz12'])."', `einh13` = '".($hangar['einh13']-$_POST['anz13'])."', `einh14` = '".($hangar['einh14']-$_POST['anz14'])."', `einh15` = '".($hangar['einh15']-$_POST['anz15'])."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);

	$select = "INSERT INTO `missionen` ( `id` , `pid` , `type` , `start` , `ziel` , `started` , `ankunft` , `return` , `speed` , `einh1` , `einh2` , `einh3` , `einh4` , `einh5` , `einh6` , `einh7` , `einh8` , `einh9` , `einh10` , `einh11` , `einh12` , `einh13` , `einh14` , `einh15` , `eisen` , `titan` , `oel` , `uran` , `gold` , `chanje` ) VALUES ( '', '".rand(100,999)."', '".$_POST['mission']."', '".$_SESSION['user']['omni']."', '".$_POST[target]."', '".date(U)."', '".(date(U)+felder2time(($entfernung*10/$maxspeed)))."', '".(date(U)+3600+felder2time(($entfernung*10/$maxspeed)*2))."', '".$maxspeed."', '".$_POST['anz1']."', '".$_POST['anz2']."', '".$_POST['anz3']."', '".$_POST['anz4']."', '".$_POST['anz5']."', '".$_POST['anz6']."', '".$_POST['anz7']."', '".$_POST['anz8']."', '".$_POST['anz9']."', '".$_POST['anz10']."', '".$_POST['anz11']."', '".$_POST['anz12']."', '".$_POST['anz13']."', '".$_POST['anz14']."', '".$_POST['anz15']."', '".$_POST['eisen']."', '".$_POST['titan']."', '".$_POST['oel']."', '".$_POST['uran']."', '".$_POST['gold']."', '".$_POST['chanje']."' );";
	$selectResult   = mysql_query($select);
	
	$eid = mysql_insert_id();
	
	$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '1', '".$eid."', '".(date(U)+felder2time(($entfernung*10/$maxspeed)))."');";
	$selectResult   = mysql_query($select);
	
	
	if ($_POST['anz1']) { $einheiten .= $_POST['anz1'].' '.$einh[1]['name'].'<br />';}
	if ($_POST['anz2']) { $einheiten .= $_POST['anz2'].' '.$einh[2]['name'].'<br />';}
	if ($_POST['anz3']) { $einheiten .= $_POST['anz3'].' '.$einh[3]['name'].'<br />';}
	if ($_POST['anz4']) { $einheiten .= $_POST['anz4'].' '.$einh[4]['name'].'<br />';}
	if ($_POST['anz5']) { $einheiten .= $_POST['anz5'].' '.$einh[5]['name'].'<br />';}
	if ($_POST['anz6']) { $einheiten .= $_POST['anz6'].' '.$einh[6]['name'].'<br />';}
	if ($_POST['anz7']) { $einheiten .= $_POST['anz7'].' '.$einh[7]['name'].'<br />';}
	if ($_POST['anz8']) { $einheiten .= $_POST['anz8'].' '.$einh[8]['name'].'<br />';}
	if ($_POST['anz9']) { $einheiten .= $_POST['anz9'].' '.$einh[9]['name'].'<br />';}
	if ($_POST['anz10']) { $einheiten .= $_POST['anz10'].' '.$einh[10]['name'].'<br />';}
	if ($_POST['anz11']) { $einheiten .= $_POST['anz11'].' '.$einh[11]['name'].'<br />';}
	if ($_POST['anz12']) { $einheiten .= $_POST['anz12'].' '.$einh[12]['name'].'<br />';}
	if ($_POST['anz13']) { $einheiten .= $_POST['anz13'].' '.$einh[13]['name'].'<br />';}
	if ($_POST['anz14']) { $einheiten .= $_POST['anz14'].' '.$einh[14]['name'].'<br />';}
	if ($_POST['anz15']) { $einheiten .= $_POST['anz15'].' '.$einh[15]['name'].'<br />';}
	
	$log = 'Mission #'.$eid.' von '.$_SESSION['user']['omni'].' gestartet nach '.$_POST['target'].' Typ: '.$_POST['mission'].' Eh.: '.$einheiten.' Ressis: E'.number_format($_POST['eisen'],0).' T:'.number_format($_POST['titan'],0).' O:'.number_format($_POST['oel'],0).' U:'.number_format($_POST['uran'],0).' G:'.number_format($_POST['gold'],0).' C:'.number_format($_POST['chanje'],0);
	mysql_query("INSERT INTO `log` ( `id` , `time` , `action` ) VALUES ( '', UNIX_TIMESTAMP() , '".$log."' );");
	
	$content .= '<center>
	<br />
	<table border="1" cellspacing="0" class="standard">
	<tr align="center"><th colspan="2">Mission gestartet:</th></tr>
	<tr align="center" class="standard"><td style="width:150px">Ziel</td><td style="width:150px">'.$_POST['target'].'</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Mission</td><td style="width:150px">'.$mission.'</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Missions ID</td><td style="width:150px">'.$eid.'</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Einheiten</td><td style="width:150px">'.$einheiten.'</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Entfernung</td><td style="width:150px">'.($entfernung*2.5).' km</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Geschwindigkeit</td><td style="width:150px">'.$maxspeed.' km/h</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Dauer</td><td style="width:150px">'.time2str(felder2time(($entfernung*10/$maxspeed))).'</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Gesamtplatz</td><td style="width:150px">'.$platz.'</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Verbrauch in Liter</td><td style="width:150px">'.$verbrauch.' Liter</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Verbrauch in Einh.</td><td style="width:150px">'.$verbrauch_einh.' Einh.</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Eisen</td><td style="width:150px">'.$_POST['eisen'].'</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Titan</td><td style="width:150px">'.$_POST['titan'].'</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Oel</td><td style="width:150px">'.$_POST['oel'].'</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Uran</td><td style="width:150px">'.$_POST['uran'].'</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Gold</td><td style="width:150px">'.$_POST['gold'].'</td></tr>
	<tr align="center" class="standard"><td style="width:150px">Chanje</td><td style="width:150px">'.$_POST['chanje'].'</td></tr>	
	<tr align="center" class="standard"><td style="width:150px">Restplatz</td><td style="width:150px;'.$red.'">'.$restplatz.'</td></tr>';
	
	$content .= '<tr align="center"><td colspan="2" style="width:300px; height:20px"><br />'.percentbar(felder2time(($entfernung*10/$maxspeed)),felder2time(($entfernung*10/$maxspeed)),290).'</td></tr>';
	
	$content .= '<tr align="center"><td colspan="2" style="width:300px"><br /><b>Mission <a href="mission.php?'.SID.'&amp;missid='.$eid.'&amp;abbrechen=1"><font class="red">ABBRECHEN</b></font></a><br /><br /></td></tr>
	</table>
	<br />';

}elseif ($_POST[action] == mission and $_POST[target]){
	$anzahl    = ($_POST['anz1']+$_POST['anz2']+$_POST['anz3']+$_POST['anz4']+$_POST['anz5']+$_POST['anz6']+$_POST['anz7']+$_POST['anz8']+$_POST['anz9']+$_POST['anz10']+$_POST['anz11']+$_POST['anz12']+$_POST['anz13']+$_POST['anz14']+$_POST['anz15']);
	$to_position  = position($_POST[target]);
	$own_position = position($_SESSION['user']['omni']);
	$own_pos = ($own_position['x']+($own_position['y']+($own_position['z']*20)));
	$to_pos  = ($to_position['x']+($to_position['y']+($to_position['z']*20)));
	
	if ( $own_position['x'] > $to_position['x'] ) { $entfernung = $own_position['x'] - $to_position['x']; }
	else { $entfernung = $to_position['x'] - $own_position['x']; }
	
	if ( ( $own_position['y'] + ( $own_position['z'] * 20 ) ) > ( $to_position['y'] + ( $to_position['z'] * 20 ) ) ) { $entfernung += ( $own_position['y'] + ( $own_position['z'] * 20 ) ) - ( $to_position['y'] + ( $to_position['z'] * 20 ) ); }
	else { $entfernung += ( $to_position['y'] + ( $to_position['z'] * 20 ) ) - ( $own_position['y'] + ( $own_position['z'] * 20 ) ); }	
		
	
	
	$verbrauch = (($_POST['anz1']*$einh[1]['verbrauch'])+($_POST['anz2']*$einh[2]['verbrauch'])+($_POST['anz3']*$einh[3]['verbrauch'])+($_POST['anz4']*$einh[4]['verbrauch'])+($_POST['anz5']*$einh[5]['verbrauch'])+($_POST['anz6']*$einh[6]['verbrauch'])+($_POST['anz7']*$einh[7]['verbrauch'])+($_POST['anz8']*$einh[8]['verbrauch'])+($_POST['anz9']*$einh[9]['verbrauch'])+($_POST['anz10']*$einh[10]['verbrauch'])+($_POST['anz11']*$einh[11]['verbrauch'])+($_POST['anz12']*$einh[12]['verbrauch'])+($_POST['anz13']*$einh[13]['verbrauch'])+($_POST['anz14']*$einh[14]['verbrauch'])+($_POST['anz15']*$einh[15]['verbrauch']))/10*$entfernung;
	$verbrauch_km = (($_POST['anz1']*$einh[1]['verbrauch'])+($_POST['anz2']*$einh[2]['verbrauch'])+($_POST['anz3']*$einh[3]['verbrauch'])+($_POST['anz4']*$einh[4]['verbrauch'])+($_POST['anz5']*$einh[5]['verbrauch'])+($_POST['anz6']*$einh[6]['verbrauch'])+($_POST['anz7']*$einh[7]['verbrauch'])+($_POST['anz8']*$einh[8]['verbrauch'])+($_POST['anz9']*$einh[9]['verbrauch'])+($_POST['anz10']*$einh[10]['verbrauch'])+($_POST['anz11']*$einh[11]['verbrauch'])+($_POST['anz12']*$einh[12]['verbrauch'])+($_POST['anz13']*$einh[13]['verbrauch'])+($_POST['anz14']*$einh[14]['verbrauch'])+($_POST['anz15']*$einh[15]['verbrauch']));
	$platz     = ($_POST['anz1']*$einh[1]['space'])+($_POST['anz2']*$einh[2]['space'])+($_POST['anz3']*$einh[3]['space'])+($_POST['anz4']*$einh[4]['space'])+($_POST['anz5']*$einh[5]['space'])+($_POST['anz6']*$einh[6]['space'])+($_POST['anz7']*$einh[7]['space'])+($_POST['anz8']*$einh[8]['space'])+($_POST['anz9']*$einh[9]['space'])+($_POST['anz10']*$einh[10]['space'])+($_POST['anz11']*$einh[11]['space'])+($_POST['anz12']*$einh[12]['space'])+($_POST['anz13']*$einh[13]['space'])+($_POST['anz14']*$einh[14]['space'])+($_POST['anz15']*$einh[15]['space']);
	$maxspeed = 1000;
	
	// soldaten in transporter packen.
	$soldaten = $_POST['anz1']+$_POST['anz2']+$_POST['anz3']+$_POST['anz4'];
	$tr_space = ($_POST['anz12']*3)+($_POST['anz13']*10);
	
	if (($tr_space - $soldaten) >= 0) {unset( $soldaten); }
		
	do {
		$i++;
		if ($soldaten){ 
			if ($_POST['anz1'] and $einh[1]['speed'] < $maxspeed){ $maxspeed = $einh[1]['speed']; }
			if ($_POST['anz2'] and $einh[2]['speed'] < $maxspeed){ $maxspeed = $einh[2]['speed']; }
			if ($_POST['anz3'] and $einh[3]['speed'] < $maxspeed){ $maxspeed = $einh[3]['speed']; }
			if ($_POST['anz4'] and $einh[4]['speed'] < $maxspeed){ $maxspeed = $einh[4]['speed']; }
		}
		if ($_POST['anz5'] and $einh[5]['speed'] < $maxspeed){ $maxspeed = $einh[5]['speed']; }
		if ($_POST['anz6'] and $einh[6]['speed'] < $maxspeed){ $maxspeed = $einh[6]['speed']; }
		if ($_POST['anz7'] and $einh[7]['speed'] < $maxspeed){ $maxspeed = $einh[7]['speed']; }
		if ($_POST['anz8'] and $einh[8]['speed'] < $maxspeed){ $maxspeed = $einh[8]['speed']; }
		if ($_POST['anz9'] and $einh[9]['speed'] < $maxspeed){ $maxspeed = $einh[9]['speed']; }
		if ($_POST['anz10'] and $einh[10]['speed'] < $maxspeed){ $maxspeed = $einh[10]['speed']; }
		if ($_POST['anz11'] and $einh[11]['speed'] < $maxspeed){ $maxspeed = $einh[11]['speed']; }
		if ($_POST['anz12'] and $einh[12]['speed'] < $maxspeed){ $maxspeed = $einh[12]['speed']; }
		if ($_POST['anz13'] and $einh[13]['speed'] < $maxspeed){ $maxspeed = $einh[13]['speed']; }
		if ($_POST['anz14'] and $einh[14]['speed'] < $maxspeed){ $maxspeed = $einh[14]['speed']; }
		if ($_POST['anz15'] and $einh[15]['speed'] < $maxspeed){ $maxspeed = $einh[15]['speed']; }
	} while ($i < 15);
	$i=0;
	
	if ($_POST['speed'] == 10){ $speedstep10 = selected; }
	elseif ($_POST['speed'] == 20){ $speedstep20 = selected; }
	elseif ($_POST['speed'] == 30){ $speedstep30 = selected; }
	elseif ($_POST['speed'] == 40){ $speedstep40 = selected; }
	elseif ($_POST['speed'] == 50){ $speedstep50 = selected; }
	elseif ($_POST['speed'] == 60){ $speedstep60 = selected; }
	elseif ($_POST['speed'] == 70){ $speedstep70 = selected; }
	elseif ($_POST['speed'] == 80){ $speedstep80 = selected; }
	elseif ($_POST['speed'] == 90){ $speedstep90 = selected; }
	else { $speedstep100 = selected; $_POST['speed'] == 100;}
	
	if ($_POST['speed']){ 
		$maxspeed  = ($maxspeed/100)*$_POST['speed'];
		$verbrauch = round(($verbrauch/100)*$_POST['speed'],0);
	}
	$verbrauch_einh = round(($verbrauch/30),0);
	if ($verbrauch_einh == 0){$verbrauch_einh = 1;}
	
	$_POST['eisen'] = number_format($_POST['eisen'],0,'','');
	$_POST['titan'] = number_format($_POST['titan'],0,'','');
	$_POST['oel'] = number_format($_POST['oel'],0,'','');
	$_POST['uran'] = number_format($_POST['uran'],0,'','');
	$_POST['gold'] = number_format($_POST['gold'],0,'','');
	$_POST['chanje'] = number_format($_POST['chanje'],0,'','');

	if ($mission == 1){ $select1 = selected;}
	elseif ($mission == 2){ $select2 = selected;}
	elseif ($mission == 3){ $select3 = selected;}
	elseif ($mission == 4){ $select4 = selected;}
	
	// $restplatz = ($platz-($verbrauch/100)-$_POST['eisen']-$_POST['titan']-$_POST['oel']-$_POST['uran']-$_POST['gold']-$_POST['chanje']);
	$verbrauch_einh = round($verbrauch/30);
	if ($verbrauch_einh == 0){ $verbrauch_einh = 1; }
	$restplatz = $platz-$verbrauch_einh;
	
	if ($restplatz < 0){ $red = 'color:red;'; }

	$content .= '
<script type="text/javascript">
<!--
function berechne() {
	var to = Math.abs(document.getElementsByName("target")[0].value);
	// if (to == 0) { to = 1; }
	var y1 = Math.floor(to / 25 + 1);
	var x1 = to - ( ( y1 - 1 ) * 25);
	if (x1 == 0) { 
		x1 = 25; 
		y1 = y1 - 1;
	}
	var zz1 = y1 / 20;
	var z1 = Math.floor(zz1);
	if (zz1 != z1){z1 = z1}
	else {z1 = zz1 - 1;}
	if (z1 != 0){ y1 = y1 - 20 * z1;}
	var x2 = '.$own_position['x'].';
	var y2 = '.$own_position['y'].';
	var z2 = '.$own_position['z'].';
	var e;
	
	if ( x2 > x1 ) { e = x2 - x1; }
	else { e = x1 - x2; }
	
	if ( ( y2 + ( z2 * 20 ) ) > ( y1 + ( z1 * 20 ) ) ) { e = e + ( y2 + ( z2 * 20 ) ) - ( y1 + ( z1 * 20 ) ); }
	else { e = e + ( y1 + ( z1 * 20 ) ) - ( y2 + ( z2 * 20 ) ); }	
	
	document.getElementById("entfernung").innerHTML=e*2.5;

	var platz     ='.$platz.';
	var maxspeed  ='.$maxspeed.';
	var verbrauch ='.$verbrauch_km.'*e/10;
	var entfernung=e*2.5;
	var eisen = document.getElementsByName("eisen");
	var titan = document.getElementsByName("titan");
	var oel   = document.getElementsByName("oel");
	var uran  = document.getElementsByName("uran");
	var gold  = document.getElementsByName("gold");
	var chanje= document.getElementsByName("chanje");
	var speed = document.getElementsByName("speed");
    var a = Math.abs(eisen[0].value);
	var b = Math.abs(titan[0].value);
	var c = Math.abs(oel[0].value);
	var d = Math.abs(uran[0].value);
	var e2 = Math.abs(gold[0].value);
	var f = Math.abs(chanje[0].value);
	var s = Math.abs(speed[0].value);
	
	speed = runde(maxspeed/100*s,2);
	var sec = Math.round(entfernung/speed*60*60);
	var hr; var min;
	hr="00"; min="00";
	
	if(sec>59){
      min=Math.floor(sec/60);
      sec=sec-min*60}
    if(min>59){
      hr=Math.floor(min/60);
      min=min-hr*60}
    if(sec<10){
      sec="0"+sec;}
    if(min<10){
      min="0"+min}
	if(hr<1){
      hr="0"}
	if(min=="000"){
      min="00"}
	
	var hr2 = Math.abs(hr)+1;
	var veh = Math.round(verbrauch/100*s/30);
	if(veh<1){
      veh=1}
	var p   = platz-veh-(a+b+c+d+e2+f);
	
	document.getElementById("d").innerHTML=hr+":"+min+":"+sec+"<br />+1:00:00<br />="+hr2+":"+min+":"+sec;
	document.getElementById("s").innerHTML=speed+" km/h";
	document.getElementById("v1").innerHTML=Math.round(verbrauch/100*s)+" Liter";
	document.getElementById("v2").innerHTML=veh+" Einh.";
	if (p>-1){
		document.getElementById("w").innerHTML=p;
	} else {
		document.getElementById("w").innerHTML="<font color=\"red\">"+p+"</font>";
	}
	
}
// END -->
</script>	
	<center>
	<br />
	<form enctype="multipart/form-data" action="mission.php?'. SID .'" method="post">
	<table border="1" cellspacing="0" class="standard">
	<tr align="center"><th colspan="2" style="width:300px"><b>Mission Planen:</b></th></tr>
	<tr align="center"><td style="width:150px">Ziel</td><td class="input"><input type="text" name="target" onFocus="berechne()" onBlur="berechne()" onKeyDown="berechne()" onKeyUp="berechne()" onChange="berechne()" value="'.$_POST[target].'" style="border:0; width:150px; height:14px;" /></td></tr>
	<tr align="center"><td style="width:150px">Mission</td><td style="width:150px"><select name="mission" size="1" style="border:0; width:150px; height:20px">
					<option value="1" '.$select1.'>angreifen</option>
					<option value="2" '.$select2.'>transportieren</option>
					<option value="3" '.$select3.'>&uuml;berfuehren</option>
					<option value="4" '.$select4.'>sammeln</option>
				</select></td></tr>
	<tr align="center"><td style="width:150px">Geschwindigkeit</td><td style="width:150px"><select name="speed" size="1" style="border:0; width:150px; height:20px" onchange="berechne()">
					<option value="100" '.$speedstep100.'>100%</option>
	 				<option value="90" '.$speedstep90.'>90%</option>
					<option value="80" '.$speedstep80.'>80%</option>
					<option value="70" '.$speedstep70.'>70%</option>	
					<option value="60" '.$speedstep60.'>60%</option>	
					<option value="50" '.$speedstep50.'>50%</option>	
					<option value="40" '.$speedstep40.'>40%</option>
					<option value="30" '.$speedstep30.'>30%</option>	
					<option value="20" '.$speedstep20.'>20%</option>	
					<option value="10" '.$speedstep10.'>10%</option>	
				</select></td></tr>
	<tr align="center"><td style="width:150px">Einheiten</td><td style="width:150px">'.$anzahl.'</td></tr>
	<tr align="center"><td style="width:150px">Entfernung</td><td style="width:150px"><a id="entfernung" name="entfernung">'.($entfernung*2.5).'</a> km</td></tr>
	<tr align="center"><td style="width:150px">Geschwindigkeit</td><td style="width:150px"><div id="s">'.$maxspeed.'.00 km/h</div></td></tr>
	<tr align="center"><td style="width:150px">Dauer</td><td style="width:150px"><div id="d">'.time2str(felder2time(($entfernung*10/$maxspeed))-3600).'<br />+1:00:00<br />='.time2str(felder2time(($entfernung*10/$maxspeed))).'</div></td></tr>
	<tr align="center"><td style="width:150px">Gesamtplatz</td><td style="width:150px">'.$platz.'</td></tr>
	<tr align="center"><td style="width:150px">Verbrauch in Liter</td><td style="width:150px"><div id="v1">'.$verbrauch.' Liter</div></td></tr>
	<tr align="center"><td style="width:150px">Verbrauch in Einh.</td><td style="width:150px"><div id="v2">'.$verbrauch_einh.' Einh.</div></td></tr>
	<tr align="center"><td style="width:150px">Eisen</td><td class="input"><input onChange="berechne();" onkeyup="berechne();" type="text" name="eisen" value="'.$_POST['eisen'].'" style="border:0; width:150px; height:14px;"></td></tr>
	<tr align="center"><td style="width:150px">Titan</td><td class="input"><input onChange="berechne();" onkeyup="berechne();" type="text" name="titan" value="'.$_POST['titan'].'" style="border:0; width:150px; height:14px"></td></tr>
	<tr align="center"><td style="width:150px">Oel</td><td class="input"><input onChange="berechne();" onkeyup="berechne();" type="text" name="oel" value="'.$_POST['oel'].'" style="border:0; width:150px; height:14px"></td></tr>
	<tr align="center"><td style="width:150px">Uran</td><td class="input"><input onChange="berechne();" onkeyup="berechne();" type="text" name="uran" value="'.$_POST['uran'].'" style="border:0; width:150px; height:14px"></td></tr>
	<tr align="center"><td style="width:150px">Gold</td><td class="input"><input onChange="berechne();" onkeyup="berechne();" type="text" name="gold" value="'.$_POST['gold'].'" style="border:0; width:150px; height:14px"></td></tr>
	<tr align="center"><td style="width:150px">Chanje</td><td class="input"><input onChange="berechne();" onkeyup="berechne();" type="text" name="chanje" value="'.$_POST['chanje'].'" style="border:0; width:150px; height:14px"></td></tr>	
	<tr align="center"><td style="width:150px">Restplatz</td><td style="width:150px;'.$red.'"><div id="w">'.round($restplatz,0).'</div></td></tr>
	</table>
	<br />
	<input type="submit" name="starten" value="Mission starten">
	<input type="hidden" name="action" value="mission" />
	<input type="hidden" name="anz1" value="'.$_POST['anz1'].'" />
	<input type="hidden" name="anz2" value="'.$_POST['anz2'].'" />
	<input type="hidden" name="anz3" value="'.$_POST['anz3'].'" />
	<input type="hidden" name="anz4" value="'.$_POST['anz4'].'" />
	<input type="hidden" name="anz5" value="'.$_POST['anz5'].'" />
	<input type="hidden" name="anz6" value="'.$_POST['anz6'].'" />
	<input type="hidden" name="anz7" value="'.$_POST['anz7'].'" />
	<input type="hidden" name="anz8" value="'.$_POST['anz8'].'" />
	<input type="hidden" name="anz9" value="'.$_POST['anz9'].'" />
	<input type="hidden" name="anz10" value="'.$_POST['anz10'].'" />
	<input type="hidden" name="anz11" value="'.$_POST['anz11'].'" />
	<input type="hidden" name="anz12" value="'.$_POST['anz12'].'" />
	<input type="hidden" name="anz13" value="'.$_POST['anz13'].'" />
	<input type="hidden" name="anz14" value="'.$_POST['anz14'].'" />
	<input type="hidden" name="anz15" value="'.$_POST['anz15'].'" />
	</form>';
}
else {
		$content .= '
		<script type="text/javascript">
<!-- Begin
function setall(type,anz) {
    document.getElementsByName("anz" + type)[0].value = anz;
}
// End -->
</script>
		<br /><b>Neue Mission planen:</b><form name="start_mission" enctype="multipart/form-data" action="mission.php?'. SID .'" method="post"><table border="1" cellspacing="0" style="width: 660px;" class="standard">
<tr align="center"><th style="width:110px">Einheit</th><th style="width:25px">Verf.</th><th style="width:50px">Geschw.</th><th style="width:40px">Gr&ouml;sse</th><th style="width:70px">Platz</th><th style="width:90px">Verbrauch</th><th style="width:70px">Anzahl</th><th width="30px">&nbsp;</th></tr>';

	$piece = '<tr class="standard" align="center"><td align="left" style="width:70px">%name%</td><td style="width:70px">%anz%</td><td style="width:40px">%speed% km/h</td><td style="width:40px">%size%</td><td style="width:70px">%space%</td><td style="width:90px">%verbrauch% l/100km</td><td  class="input" style="width:70px;">%input%</td><td>%alle%</td></tr>';

	$hangar = new_units_check($_SESSION['user']['omni']);

	do {
		$i++;
		$einheit = 'einh'.$i;
		if ($hangar[$einheit] != 0){ 
			$newpiece = str_replace('%name%', '<a href="javascript:popUp(\'details_einh.php?id='.$i.'\',400)">'.$einh[$i]['name'].'</a>', $piece);
			$newpiece = str_replace('%anz%', $hangar[$einheit], $newpiece);
			$newpiece = str_replace('%speed%', $einh[$i]['speed'], $newpiece);
			$newpiece = str_replace('%space%', $einh[$i]['space'], $newpiece);
			$newpiece = str_replace('%size%', $einh[$i]['size'], $newpiece);
			$newpiece = str_replace('%verbrauch%', $einh[$i]['verbrauch'], $newpiece);
			$newpiece = str_replace('%alle%', '<a href="#" onclick="setall('.$i.','.$hangar[$einheit].');">alle</a>', $newpiece);
			$newpiece = str_replace('%input%', '<input type="text" name="anz'.$i.'" value="0" style="border:0; width:100%; height:14px" />', $newpiece);		
			$content .= $newpiece;
		}
	} while ($i < 15);
	$content .= '<tr><td colspan="8" align="center"><input type="hidden" name="action" value="mission" /><b>Ziel UBL: </b><input type="text" name="target" value="'.$_GET[to].'" style="width:50px" /> <input type="submit" name="submit" value="weiter" /></td></tr></table></form>';
	
	// aufgedeckte missionen
	$select = "SELECT * FROM `missionen` WHERE `ziel` = '".$_SESSION['user']['omni']."' AND `ankunft` > '".date(U)."' AND `parsed` != '1' ORDER BY `ankunft` ASC;";
	$result = mysql_query($select);
	
	$content_t .= '<center><b>Aufgedeckte Missionen:</b><table border="1" cellspacing="0" style="width: 660px;"  class="standard"><tr align="center">
	<th style="width:80px">ID</th><th style="width:40px">PID</th><th style="width:80px">Mission</th><th style="width:70px">Absender</th><th style="width:70px">Anzahl</th><th style="width:60px">Geschw.</th><th style="width:90px">Ankunft</th><th style="width:170px">Einheiten</th></tr>';
	
	//$piece = '<tr class="standard" align="center"><td style="width:30px">%id%</td><td style="width:70px">%type%</td><td style="width:70px">%start%</td><td style="width:70px">%units%</td><td style="width:70px">%speed% km/h</td><td style="width:70px">%ankunft%</td><td style="width:70px">%einheiten%</td></tr>';
	// forschungen
	$select = "SELECT * FROM `forschungen` WHERE `omni` = '".($_SESSION['user']['omni'])."';";
	$result2 = mysql_query($select);
	$forschung  = mysql_fetch_array($result2);
	unset ($newpiece);
	do {
		$row = mysql_fetch_array($result);
		if ($row){
			$row['type'] == '1' ? $piece = template('detected_missions_attack') : $piece = template('detected_missions');
			if ($row['einh1']) { $einheiten .= $row['einh1'].' <a href="javascript:popUp(\'details_einh.php?id=1\',400)">'.$einh[1]['name'].'</a><br />';}
			if ($row['einh2']) { $einheiten .= $row['einh2'].' <a href="javascript:popUp(\'details_einh.php?id=2\',400)">'.$einh[2]['name'].'</a><br />';}
			if ($row['einh3']) { $einheiten .= $row['einh3'].' <a href="javascript:popUp(\'details_einh.php?id=3\',400)">'.$einh[3]['name'].'</a><br />';}
			if ($row['einh4']) { $einheiten .= $row['einh4'].' <a href="javascript:popUp(\'details_einh.php?id=4\',400)">'.$einh[4]['name'].'</a><br />';}
			if ($row['einh5']) { $einheiten .= $row['einh5'].' <a href="javascript:popUp(\'details_einh.php?id=5\',400)">'.$einh[5]['name'].'</a><br />';}
			if ($row['einh6']) { $einheiten .= $row['einh6'].' <a href="javascript:popUp(\'details_einh.php?id=6\',400)">'.$einh[6]['name'].'</a><br />';}
			if ($row['einh7']) { $einheiten .= $row['einh7'].' <a href="javascript:popUp(\'details_einh.php?id=7\',400)">'.$einh[7]['name'].'</a><br />';}
			if ($row['einh8']) { $einheiten .= $row['einh8'].' <a href="javascript:popUp(\'details_einh.php?id=8\',400)">'.$einh[8]['name'].'</a><br />';}
			if ($row['einh9']) { $einheiten .= $row['einh9'].' <a href="javascript:popUp(\'details_einh.php?id=9\',400)">'.$einh[9]['name'].'</a><br />';}
			if ($row['einh10']) { $einheiten .= $row['einh10'].' <a href="javascript:popUp(\'details_einh.php?id=10\',400)">'.$einh[10]['name'].'</a><br />';}
			if ($row['einh11']) { $einheiten .= $row['einh11'].' <a href="javascript:popUp(\'details_einh.php?id=11\',400)">'.$einh[11]['name'].'</a><br />';}
			if ($row['einh12']) { $einheiten .= $row['einh12'].' <a href="javascript:popUp(\'details_einh.php?id=12\',400)">'.$einh[12]['name'].'</a><br />';}
			if ($row['einh13']) { $einheiten .= $row['einh13'].' <a href="javascript:popUp(\'details_einh.php?id=13\',400)">'.$einh[13]['name'].'</a><br />';}
			if ($row['einh14']) { $einheiten .= $row['einh14'].' <a href="javascript:popUp(\'details_einh.php?id=14\',400)">'.$einh[14]['name'].'</a><br />';}
			if ($row['einh15']) { $einheiten .= $row['einh15'].' <a href="javascript:popUp(\'details_einh.php?id=15\',400)">'.$einh[15]['name'].'</a><br />';}
			
			if ($row['type'] == 1){ $mission = 'angreifen'; }
			elseif ($row['type'] == 2){ $mission = 'transportieren'; }
			elseif ($row['type'] == 3){ $mission = '&uuml;berf&uuml;hren'; }
			elseif ($row['type'] == 4){ $mission = 'sammeln'; }
			$units = ($row['einh1']+$row['einh2']+$row['einh3']+$row['einh4']+$row['einh5']+$row['einh6']+$row['einh7']+$row['einh8']+$row['einh9']+$row['einh10']+$row['einh11']+$row['einh12']+$row['einh13']+$row['einh14']+$row['einh15']);
			$ankunft = date('H:i - d.m',$row['ankunft']);

			$select = "SELECT * FROM `forschungen` WHERE `omni` = '".$row['start']."' LIMIT 1;";
			$enemy_forsch = mysql_query($select);
			$enemy_forsch = mysql_fetch_array($enemy_forsch);
			
			$spiodiff = ($enemy_forsch['spionage'] - $forschung['spionage']);

			if ($spiodiff >= 1) { $einheiten = 'unbekannt'; }			
			if ($spiodiff >= 2) { $row['speed'] = '?'; }			
			if ($spiodiff >= 4) { 
				$ankunft = date('H:i',$row['ankunft']);
				$ankunft = substr($ankunft, 0, 4).'X'.date(' - d.m',$row['ankunft']);
			}
			if ($spiodiff >= 6) { $units = 'unbekannt'; }
			if ($spiodiff >= 8) { $row['pid'] = '?'; }			
			
			$newpiece = str_replace('%id%', $row['id'], $piece);
			$newpiece = str_replace('%pid%', $row['pid'], $newpiece);
			$newpiece = str_replace('%type%', $mission, $newpiece);
			$newpiece = str_replace('%start%', $row['start'], $newpiece);
			$newpiece = str_replace('%units%', $units, $newpiece);
			$newpiece = str_replace('%speed%', $row['speed'], $newpiece);
			$newpiece = str_replace('%ankunft%', $ankunft, $newpiece);
			$newpiece = str_replace('%einheiten%', $einheiten, $newpiece);
			$content_t .= $newpiece;
			unset($einheiten);
		}
	} while ($row);

	$content_t .='</table><br />';	
	
	if ($newpiece){ $content .= $content_t; }
	
	
	$select = "SELECT * FROM `missionen` WHERE `start` = '".$_SESSION['user']['omni']."' AND `ankunft` > '".date(U)."' AND `parsed` != '1' ORDER BY `ankunft` ASC;";
	$result = mysql_query($select);
	
	$content .= '<center><span style="font-size: 12px";><b>Aktive Missionen: '.mysql_num_rows($result).' von '.($gebaeude[missionszentrum]*2).' m&ouml;glichen.</b></span><table border="1" cellspacing="0" style="width: 660px;"  class="standard"><tr align="center">
	<th style="width:95px">Mission</th><th style="width:70px">Ziel</th><th style="width:60px">Einheiten</th><th style="width:60px">Hangar</th><th style="width:70px">Geschw.</th><th style="width:95px">Ankunft</th><th style="width:80px">Countdown</th><th style="width:75px">&nbsp;</th></tr>%active_missions%</table>';
	
	//$piece = '<tr onclick="window.location.href=\'%link%\'" class="standard" align="center"><td>%type%</td><td>%ziel%</td><td>%units%</td><td>%speed% km/h</td><td>%ankunft%</td><td>%countdown%</td><td>%mehr%</td></tr>';

	$piece   = template('active_missions');

	$content = tag2value('missions_active', mysql_num_rows($result), $content);
	$content = tag2value('missions_max', ($gebaeude[missionszentrum]*2), $content);

	do {
		$row = mysql_fetch_array($result);
		if ($row['return'] > $row['ankunft']){
			if ($row['type'] == 1){ $mission = 'angreifen'; }
			elseif ($row['type'] == 2){ $mission = 'transportieren'; }
			elseif ($row['type'] == 3){ $mission = '&uuml;berf&uuml;hren'; }
			elseif ($row['type'] == 4){ $mission = 'sammeln'; }
			$i = 0;
			do {
				$i++;
				$size += $row['einh'.$i] * $einh[$i]['size'];			
			} while ($i < 15);			
			$newpiece = str_replace('%type%', $mission, $piece);
			$newpiece = str_replace('%ziel%', $row['ziel'], $newpiece);
			$newpiece = str_replace('%size%', $size, $newpiece);
			$newpiece = str_replace('%units%', ($row['einh1']+$row['einh2']+$row['einh3']+$row['einh4']+$row['einh5']+$row['einh6']+$row['einh7']+$row['einh8']+$row['einh9']+$row['einh10']+$row['einh11']+$row['einh12']+$row['einh13']+$row['einh14']+$row['einh15']), $newpiece);
			$newpiece = str_replace('%speed%', $row['speed'], $newpiece);
			$newpiece = str_replace('%ankunft%', date('H:i - d.m',$row['ankunft']), $newpiece);
			$newpiece = str_replace('%countdown%', countdown($row['ankunft']-date('U')), $newpiece);
			$newpiece = str_replace('%mehr%', '<a href="mission.php?'.SID.'&amp;id='.$row['id'].'">mehr</a>', $newpiece);
			$newpiece = str_replace('%link%', 'mission.php?'.SID.'&amp;id='.$row['id'], $newpiece);
			$missions .= $newpiece;
			$size = 0;
		}
	} while ($row);

	$content = tag2value('active_missions', $missions, $content);


	// rueckkehr
	$select = "SELECT * FROM `missionen` WHERE `start` = '".$_SESSION['user']['omni']."' AND `return` > '".date(U)."' ORDER BY `return` ASC;";
	$result = mysql_query($select);
	
	$content .= '<br /><center><b>Geplante R&uuml;ckkehr: </b><table border="1" cellspacing="0" style="width: 660px;"  class="standard"><tr align="center">
	<th style="width:95px">Mission</th><th style="width:70px">Ziel</th><th style="width:60px">Einheiten</th><th style="width:60px">Hangar</th><th style="width:70px">Geschw.</th><th style="width:95px">Ankunft</th><th style="width:80px">Countdown</th><th style="width:75px">&nbsp;</th></tr>%return_missions%</table>';

	$piece   = template('return_missions');

	do {
		$row = mysql_fetch_array($result);
		if ($row){
			if ($row['type'] == 1){ $mission = 'angreifen'; }
			elseif ($row['type'] == 2){ $mission = 'transportieren'; }
			elseif ($row['type'] == 3){ $mission = '&uuml;berf&uuml;hren'; }
			elseif ($row['type'] == 4){ $mission = 'sammeln'; }
						
			$i = 0;
			do {
				$i++;
				$size += $row['einh'.$i] * $einh[$i]['size'];			
			} while ($i < 15);

			if ($size > $ressis['hangar']){ $size = '<font color="red">'.$size.'</font>'; }						

			$newpiece = str_replace('%type%', $mission, $piece);
			$newpiece = str_replace('%ziel%', $row['ziel'], $newpiece);
			$newpiece = str_replace('%size%', $size, $newpiece);
			$newpiece = str_replace('%units%', ($row['einh1']+$row['einh2']+$row['einh3']+$row['einh4']+$row['einh5']+$row['einh6']+$row['einh7']+$row['einh8']+$row['einh9']+$row['einh10']+$row['einh11']+$row['einh12']+$row['einh13']+$row['einh14']+$row['einh15']), $newpiece);
			$newpiece = str_replace('%speed%', $row['speed'], $newpiece);
			$newpiece = str_replace('%ankunft%', date('H:i - d.m',$row['return']), $newpiece);
			$newpiece = str_replace('%countdown%', countdown($row['return']-date('U')), $newpiece);
			$newpiece = str_replace('%mehr%', '<a href="mission.php?'.SID.'&amp;id='.$row['id'].'">mehr</a>', $newpiece);
			$newpiece = str_replace('%link%', 'mission.php?'.SID.'&amp;id='.$row['id'], $newpiece);
			$missionr .= $newpiece;
			$size = 0;
		}
	} while ($row);

	$content = tag2value('return_missions', $missionr, $content);

}
// generierte seite ausgeben
$content = tag2value("onload",$onload,$content);
echo $content.template('footer');
?>