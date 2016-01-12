<?php
//////////////////////////////////
// beschuss.php                 //
//////////////////////////////////
// Letzte Aenderung: 01.11.2005 //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";
include "raketen_preise.php";

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
$content .= $status;

// neue nachrichten
//$content .= neue_nachrichten();

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION['user']['omni']);
$content .= $ressis['html'];

////// keine angriffe !!!!!!
$result = mysql_query("SELECT * FROM `angriffssperre` WHERE `end` > '".date('U')."' ORDER BY `end` ASC;");
$row = @mysql_fetch_array($result);

if ($row['end']) {
		die (template('head').$status.$ressis['html'].'<br /><br /><br />Es wurde eine <b>Angriffssperre f&uuml;r alle Spieler bis zum '.date("d.m.y \u\m H:i \h", $row['end']).'</b> verh&auml;ngt.<br /><br />
		Grund: <b>'.$row['grund'].'</b><br /><br />
		&Uuml;berf&uuml;hrungen und Transporte gehen selbstverst&auml;ndlich trotzdem.</body></html>');	
}

$content .= '<br />';

$dbh = db_connect();

$selectResult   = mysql_query("SELECT * FROM `raketen` WHERE `omni` = '".$_SESSION['user']['omni']."';");
$raksilo = mysql_fetch_array($selectResult);

$selectResult   = mysql_query("SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION['user']['omni']."';");
$gebaeude = mysql_fetch_array($selectResult);


$content .= '<br />	
<table border="1" cellspacing="0" class="sub" style="width:720px">
	<tr>
		<th>
			<b>Beschuss:</b>
		</th>
	</tr>
	<tr>
		<td align="center">
<br />';

if ($_POST['submit'] == 'Raketen abfeuern!'){
	
	do {
		$i++;
		if ($_POST['anz'.$i] < 0) { $_POST['anz'.$i] = 0; }
	} while ($i < 6);
	$i=0;	
	
	$_POST['ziel'] = number_format($_POST['ziel'],0,'','');
	$_POST['anz1'] = number_format($_POST['anz1'],0,'','');
	$_POST['anz2'] = number_format($_POST['anz2'],0,'','');
	$_POST['anz3'] = number_format($_POST['anz3'],0,'','');
	$_POST['anz4'] = number_format($_POST['anz4'],0,'','');
	$_POST['anz5'] = number_format($_POST['anz5'],0,'','');
	$_POST['anz6'] = number_format($_POST['anz6'],0,'','');
	
	if ($raksilo['einh1'] < $_POST['anz1']) { $content .= '<b>Du hast nicht genug Raketen!</b>'; $error = 1; }
	if ($raksilo['einh2'] < $_POST['anz2']) { $content .= '<b>Du hast nicht genug Raketen!</b>'; $error = 1; }
	if ($raksilo['einh3'] < $_POST['anz3']) { $content .= '<b>Du hast nicht genug Raketen!</b>'; $error = 1; }
	if ($raksilo['einh4'] < $_POST['anz4']) { $content .= '<b>Du hast nicht genug Raketen!</b>'; $error = 1; }
	if ($raksilo['einh5'] < $_POST['anz5']) { $content .= '<b>Du hast nicht genug Raketen!</b>'; $error = 1; }
	if ($raksilo['einh6'] < $_POST['anz6']) { $content .= '<b>Du hast nicht genug Raketen!</b>'; $error = 1; }
	
	if (!$_POST['ziel']){ $content .= '<b>Du musst ein Ziel angeben!</b><br />'; $error = 1;}
	if (!$_POST['anz1'] and !$_POST['anz2'] and !$_POST['anz3'] and !$_POST['anz4'] and !$_POST['anz5'] and !$_POST['anz6'])
		{ $content .= '<b>Du hast keine Raketen ausgew&auml;hlt.!</b><br />'; $error = 1;}
	if (!$_POST['type']){ $content .= '<b>Du musst einen Beschusstyp angeben!</b><br />'; $error = 1;}		
	
	if ($_POST['type'] == 1) { 
			$select = "SELECT * FROM `user` WHERE `omni` = '".$_POST['ziel']."';";
			$selectResult   = mysql_query($select);
			$target_detail  = mysql_fetch_array($selectResult, MYSQL_ASSOC);
	
			$points = str_replace('.','',$_SESSION['user']['points']);
	
			$check = 1;
			if ($target_detail['points'] > 50000 and $points > 50000) { $check = 0; }
			if ((date('U') - $target_detail['timestamp']) > 1209600)  { $check = 0; }

			// n00b schutz
			if ($check) { 	
				if ($target_detail['points'] < ( $points / 3 )){ die ($content.'<center>Fehler beim starten der Rakete/n, suche dir einen st&auml;rkeren Gegner!</center></body></html>'); }
				elseif ($points < ( $target_detail['points'] / 3 )){ die ($content.'<center>Fehler beim starten der Rakete/n, suche dir einen schw&auml;cheren Gegner!</center></body></html>'); }
			}
	}
	
	if (!$error){
		if ($_POST['type'] == 1) { 
			$to_position  = position($_POST['ziel']);
			$own_position = position($_SESSION['user']['omni']);
			$own_pos = ($own_position['x']+($own_position['y']+($own_position['z']*500)));
			$to_pos  = ($to_position['x']+($to_position['y']+($to_position['z']*20)));

			if ( $own_position['x'] > $to_position['x'] ) { $entfernung = $own_position['x'] - $to_position['x']; }
			else { $entfernung = $to_position['x'] - $own_position['x']; }
	
			if ( ( $own_position['y'] + ( $own_position['z'] * 20 ) ) > ( $to_position['y'] + ( $to_position['z'] * 20 ) ) ) { $entfernung += ( $own_position['y'] + ( $own_position['z'] * 20 ) ) - ( $to_position['y'] + ( $to_position['z'] * 20 ) ); }
			else { $entfernung += ( $to_position['y'] + ( $to_position['z'] * 20 ) ) - ( $own_position['y'] + ( $own_position['z'] * 20 ) ); }	
			
			$ankunft = (date('U')+felder2time(($entfernung*10/666)))-3600; 
			$type    = 1;
			$content .= '<span style="font-size: 12px";><b>Beschuss gestartet, Einschlag in '.countdown($ankunft-date('U')).'.</b></span><br />';
			mysql_query("INSERT INTO `beschuss` ( `id` , `start` , `ziel` , `type` , `einh1` , `einh2` , `einh3` , `einh4` , `einh5` , `einh6` , `ankunft` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_POST['ziel']."', '".$type."', '".$_POST['anz1']."', '".$_POST['anz2']."', '".$_POST['anz3']."', '".$_POST['anz4']."', '".$_POST['anz5']."', '".$_POST['anz6']."', '".$ankunft."');");					
			
			$eid = mysql_insert_id($dbh);

			$selectResult   = mysql_query("INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '5', '".$eid."', '".$ankunft."');");
			mysql_query("UPDATE `raketen` SET `einh1` = '".($raksilo['einh1']-$_POST['anz1'])."', `einh2` = '".($raksilo['einh2']-$_POST['anz2'])."', `einh3` = '".($raksilo['einh3']-$_POST['anz3'])."', `einh4` = '".($raksilo['einh4']-$_POST['anz4'])."', `einh5` = '".($raksilo['einh5']-$_POST['anz5'])."', `einh6` = '".($raksilo['einh6']-$_POST['anz6'])."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
			$i = 1;
			do {
				$raksilo['einh'.$i] -= $_POST['anz'.$i];
				$i++;
			} while ($i <= 6);
		} elseif ($_POST['type'] == 2) {
			$type   = 2;
			$result = mysql_query("SELECT * FROM `missionen` WHERE `ziel` = '".$_SESSION['user']['omni']."' AND `id` = '".$_POST['ziel']."';");
			$row    = mysql_fetch_array($result);

			$to_position  = position($row['start']);
			$own_position = position($_SESSION['user']['omni']);
			$own_pos = ($own_position['x']+($own_position['y']+($own_position['z']*500)));
			$to_pos  = ($to_position['x']+($to_position['y']+($to_position['z']*20)));

			if ( $own_position['x'] > $to_position['x'] ) { $entfernung = $own_position['x'] - $to_position['x']; }
			else { $entfernung = $to_position['x'] - $own_position['x']; }
	
			if ( ( $own_position['y'] + ( $own_position['z'] * 20 ) ) > ( $to_position['y'] + ( $to_position['z'] * 20 ) ) ) { $entfernung += ( $own_position['y'] + ( $own_position['z'] * 20 ) ) - ( $to_position['y'] + ( $to_position['z'] * 20 ) ); }
			else { $entfernung += ( $to_position['y'] + ( $to_position['z'] * 20 ) ) - ( $own_position['y'] + ( $own_position['z'] * 20 ) ); }			
			
			if (!$row) { 
				$content .= '<span style="font-size: 12px";><b>Du kannst diese Mission nicht beschiessen!</b></span><br />';
			} else {
				if ($row['ankunft'] < date('U')) { $content .= '<span style="font-size: 12px";><b>Du kannst diese Mission nicht beschiessen da sie bereits auf dem R&uuml;ckweg ist!</b></span><br />'; }
				elseif ($row['ankunft'] < date('U')+300) { $content .= '<span style="font-size: 12px";><b>Du kannst diese Mission nicht beschiessen da sie bereits weniger wie 5 minuten von deiner Basis entfernt ist!</b></span><br />'; }
				else {
					mysql_query("UPDATE `raketen` SET `einh1` = '".($raksilo['einh1']-$_POST['anz1'])."', `einh2` = '".($raksilo['einh2']-$_POST['anz2'])."', `einh3` = '".($raksilo['einh3']-$_POST['anz3'])."', `einh4` = '".($raksilo['einh4']-$_POST['anz4'])."', `einh5` = '".($raksilo['einh5']-$_POST['anz5'])."', `einh6` = '".($raksilo['einh6']-$_POST['anz6'])."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");

					$ankunftK = ($row['ankunft'] - date('U'));
					//$ankunft  = (date('U')) + ($ankunftK * $row['speed'] / 666);
					$entfernung = $entfernung / 100 * ($row['ankunft'] - date('U')) / (($row['ankunft'] - $row['started']) / 100);
					$ankunft    = (date(U)+felder2time(($entfernung*10/666)))-3600;	
					
					$content .= '<span style="font-size: 12px";><b>Beschuss gestartet, Einschlag in '.countdown($ankunft-date('U')).'.</b></span><br />';
					
					mysql_query("INSERT INTO `beschuss` ( `id` , `start` , `ziel` , `type` , `einh1` , `einh2` , `einh3` , `einh4` , `einh5` , `einh6` , `ankunft` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_POST['ziel']."', '".$type."', '".$_POST['anz1']."', '".$_POST['anz2']."', '".$_POST['anz3']."', '".$_POST['anz4']."', '".$_POST['anz5']."', '".$_POST['anz6']."', '".$ankunft."');");
					
					$eid = mysql_insert_id($dbh);
					
					$selectResult   = mysql_query("INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '5', '".$eid."', '".$ankunft."');");
					do {
						$raksilo['einh'.$i] -= $_POST['anz'.$i];
						$i++;
					} while ($i <= 6);					
				}
			}
		}
	}
}


do {
	$count++;
	$treffer[$count] = ($rak[$count]['treffer']*$gebaeude['raketensilo']);
	if ($treffer[$count] > 100) { $treffer[$count] = 100; }
} while ($count < 6);
$content .= '<form enctype="multipart/form-data" action="beschuss.php?'. SID .'" method="post"><br /><table border="1" cellspacing="0" class="standard">
<tr align="center"><th style="width:155px">Raketentyp</th><th>Bestand</th><th style="width:65px">Treffsicherheit</th><th style="width:55px">Geschwindigkeit</th><th style="width:55px">Anzahl</th></tr>';
$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=1\',400)">'.$rak[1]['name'].'</a></td><td align="center">'.$raksilo['einh1'].'</td><td align="right">'.$treffer[1].'%</td><td align="right">'.$rak[1]['speed'].'</td><td class="input"><center><input type="text" name="anz1" value="" style=" border:0; width:55px; height:14px" /></center></td></tr>';
$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=2\',400)">'.$rak[2]['name'].'</a></td><td align="center">'.$raksilo['einh2'].'</td><td align="right">'.$treffer[2].'%</td><td align="right">'.$rak[2]['speed'].'</td><td class="input"><center><input type="text" name="anz2" value="" style=" border:0; width:55px; height:14px" /></center></td></tr>';
$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=3\',400)">'.$rak[3]['name'].'</a></td><td align="center">'.$raksilo['einh3'].'</td><td align="right">'.$treffer[3].'%</td><td align="right">'.$rak[3]['speed'].'</td><td class="input"><center><input type="text" name="anz3" value="" style=" border:0; width:55px; height:14px" /></center></td></tr>';
$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=4\',400)">'.$rak[4]['name'].'</a></td><td align="center">'.$raksilo['einh4'].'</td><td align="right">'.$treffer[4].'%</td><td align="right">'.$rak[4]['speed'].'</td><td class="input"><center><input type="text" name="anz4" value="" style=" border:0; width:55px; height:14px" /></center></td></tr>';
$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=5\',400)">'.$rak[5]['name'].'</a></td><td align="center">'.$raksilo['einh5'].'</td><td align="right">'.$treffer[5].'%</td><td align="right">'.$rak[5]['speed'].'</td><td class="input"><center><input type="text" name="anz5" value="" style=" border:0; width:55px; height:14px" /></center></td></tr>';
$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=6\',400)">'.$rak[6]['name'].'</a></td><td align="center">'.$raksilo['einh6'].'</td><td align="right">'.$treffer[6].'%</td><td align="right">'.$rak[6]['speed'].'</td><td class="input"><center><input type="text" name="anz6" value="" style=" border:0; width:55px; height:14px" /></center></td></tr>';

$content .= '</table><br />
<center>
<span style="font-size: 12px";>
<input type="radio" name="type" value="1" checked /> <b>Basisbeschuss</b>
<input type="radio" name="type" value="2" /> <b>Missionsbeschuss</b><br /><br />
<b>Ziel:</b> <input type="text" name="ziel" value="'.$_GET['to'].'" style="width:55px;" /> (Missions-ID oder UBL) 
</span>
<br /><br />
<input type="submit" name="submit" value="Raketen abfeuern!" />
</center>
</form>
<br /></td></tr></table>';


// generierte seite ausgeben
$content = tag2value('onload', $onload, $content);
echo $content.template('footer');
?>