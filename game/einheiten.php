<?php
//////////////////////////////////
// einheiten.php                //
//////////////////////////////////
// Letzte Aenderung: 01.11.2005 //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";
include "einheiten_preise.php";
include "def_preise.php";
include "raketen_preise.php";

// check session
logincheck();
// get html head
$content = template('head');

// cheat protection
$i=0;
if (!$_SESSION['eh'][15]){
	do {
		$i++;
		$_SESSION['eh'][$i] = md5(rand(100000,999999).$i.rand(100000,999999));
	} while ($i < 15);
}
$i=0;
do {
	$i++;
	if ($_POST['anz'.$i] < 0) { $_POST['anz'.$i] = 0; }
	if ($_POST['anz'.$i] > 9999) { $_POST['anz'.$i] = 0; }	
	if ($_POST['anz'.$i] != number_format($_POST['anz'.$i],0,'','')) { $_POST['anz'.$i] = 0; }	
	if ($_POST['anz'.$i]){
		if ($_SESSION['eh'][$i] != $_POST['x'.$i]){ die('CHEATVERSUCH!'); }
	}
} while ($i < 15);
$i=0;

// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);

// add playerinfo to html
$content .= $status;

// checken ob einheiten fertig sind und dann hangar setzen
$hangar = new_units_check($_SESSION[user][omni]);

// neue nachrichten
//$content .= neue_nachrichten();

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION[user][omni]);

// mit datenbank verbinden
$dbh = db_connect();

// forschungen
$select = "SELECT * FROM `forschungen` WHERE `omni` = '".($_SESSION[user][omni])."';";
$result = mysql_query($select);
$forschung  = mysql_fetch_array($result);

// gebaeude
$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION[user][omni]."';";
$selectResult = mysql_query($select);
$gebaeude = mysql_fetch_array($selectResult);

// neuesten timestamp holen
$select = "SELECT * FROM `fabrik` WHERE `omni` = ".($_SESSION[user][omni])." GROUP BY fertigstellung DESC;";
$result = mysql_query($select);
$row  = mysql_fetch_array($result);
if ($row['fertigstellung'] > date(U)) { $date = $row['fertigstellung']; }
else { $date = date(U); }

// wenn gewuenscht neue einheiten in auftrag geben
do {
	$j++;
if ($_POST['anz'.$j]){ 
	if ((number_format($_POST['anz'.$j],0,'','')*$einh[$j]['eisen'])  > number_format($ressis['eisen'],0,'',''))  { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Eisen um '.number_format($_POST['anz'.$j],0,'','').' '.$einh[$j]['name'].' zu bauen.</span> <br />'; $teuer = TRUE;}
	if ((number_format($_POST['anz'.$j],0,'','')*$einh[$j]['titan'])  > number_format($ressis['titan'],0,'',''))  { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Titan um '.number_format($_POST['anz'.$j],0,'','').' '.$einh[$j]['name'].' zu bauen.</span> <br />'; $teuer = TRUE;}	
	if ((number_format($_POST['anz'.$j],0,'','')*$einh[$j]['oel'])    > number_format($ressis['oel'],0,'',''))    { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Oel um '.number_format($_POST['anz'.$j],0,'','').' '.$einh[$j]['name'].' zu bauen.</span> <br />'; $teuer = TRUE;}	
	if ((number_format($_POST['anz'.$j],0,'','')*$einh[$j]['uran'])   > number_format($ressis['uran'],0,'',''))   { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Uran um '.number_format($_POST['anz'.$j],0,'','').' '.$einh[$j]['name'].' zu bauen.</span> <br />'; $teuer = TRUE;}	
	if ((number_format($_POST['anz'.$j],0,'','')*$einh[$j]['gold'])   > number_format($ressis['gold'],0,'',''))   { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Gold um '.number_format($_POST['anz'.$j],0,'','').' '.$einh[$j]['name'].' zu bauen.</span> <br />'; $teuer = TRUE;}	
	if ((number_format($_POST['anz'.$j],0,'','')*$einh[$j]['chanje']) > number_format($ressis['chanje'],0,'','')) { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Chanje um '.number_format($_POST['anz'.$j],0,'','').' '.$einh[$j]['name'].' zu bauen.</span> <br />'; $teuer = TRUE;}	
	
	if ($teuer == FALSE) { 
		$i=0;
		do {
			$i++;
			if ($i <= $_POST['anz'.$j]) {
				$date = $date + $einh[$j]['dauer'];
				mysql_query("INSERT INTO `fabrik` ( `id` , `omni`, `type` , `fertigstellung` ) VALUES ( '', '".$_SESSION['user']['omni']."', '".$j."', '".($date)."' );");
				$insertid = mysql_insert_id($dbh);
				mysql_query("INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ( '', '2', '".$insertid."', '".$date."' );");
			}
		} while ($i < (number_format($_POST['anz'.$j],0,'','')));
		$ressis['eisen'] = $ressis['eisen']-(number_format($_POST['anz'.$j],0,'','')*$einh[$j]['eisen']);
		$ressis['titan'] = $ressis['titan']-(number_format($_POST['anz'.$j],0,'','')*$einh[$j]['titan']);
		$ressis['oel'] = $ressis['oel']-(number_format($_POST['anz'.$j],0,'','')*$einh[$j]['oel']);
		$ressis['uran'] = $ressis['uran']-(number_format($_POST['anz'.$j],0,'','')*$einh[$j]['uran']);
		$ressis['gold'] = $ressis['gold']-(number_format($_POST['anz'.$j],0,'','')*$einh[$j]['gold']);
		$ressis['chanje'] = $ressis['chanje']-(number_format($_POST['anz'.$j],0,'','')*$einh[$j]['chanje']);
		mysql_query("UPDATE `ressis` SET `eisen` = '".$ressis['eisen']."', `titan` = '".$ressis['titan']."', `oel` = '".$ressis['oel']."', `uran` = '".$ressis['uran']."', `gold` = '".$ressis['gold']."', `chanje` = '".$ressis['chanje']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
	}
	$teuer=FALSE;
}
} while ($j < 15);

$ressis = ressistand($_SESSION['user']['omni']);
$content .= $ressis['html'];

if ($zuteuer) { $content .= '<br /><b>'.$zuteuer.'</b>'; }


$content .= '<br />	
<br />
<table border="1" cellspacing="0" class="sub" style="width:720px">
	<tr>
		<th>
			<b>Einheitenbau:</b>
		</th>
	</tr>
	<tr>
		<td align="center">
<br /><b class="red">Es sind noch '.($ressis['hangar']).' Felder frei.</b><br /><br />';

$content .= '<form enctype="multipart/form-data" action="einheiten.php?'. SID .'" method="post"><span style="font-size: 12px";><b>Fusstruppen:</b></span><br /><table border="1" cellspacing="0" class="standard">
<tr align="center"><th style="width:130px">&nbsp;</th><th>Bestand</th><th style="width:55px">Eisen</th><th style="width:55px">Titan</th><th style="width:55px">Oel</th><th style="width:55px">Uran</th><th style="width:55px">Gold</th><th style="width:55px">Chanje</th><th style="width:55px">Dauer</th><th style="width:55px">&nbsp;</th></tr>';

if ($forschung['feuerwaffen'] >= 1 and $gebaeude['fabrik'] >= 1){$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=1&'.SID.'\',400)">'.$einh[1]['name'].'</a></td><td align="center">'.$hangar[einh1].'</td><td align="right">'.$einh[1]['eisen'].'</td><td align="right">'.$einh[1]['titan'].'</td><td align="right">'.$einh[1]['oel'].'</td><td align="right">'.$einh[1]['uran'].'</td><td align="right">'.$einh[1]['gold'].'</td><td align="right">'.$einh[1]['chanje'].'</td><td align="right">'.time2str($einh[1][dauer]).'</td><td class="input"><center><input type="text" name="anz1" value="" class="input"  onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x1" value="'.$_SESSION['eh'][1].'" /></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=1&'.SID.'\',400)">'.$einh[1]['name'].'</a></td><td align="center">'.$hangar[einh1].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/1 Feuerwaffen '.$forschung['feuerwaffen'].'/1 <input type="hidden" name="anz1" value="0" /></td></tr>';}

if ($forschung['feuerwaffen'] >= 1 and $gebaeude['fabrik'] >= 1 and $forschung['sprengstoff'] >= 1){$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=2&'.SID.'\',400)">'.$einh[2]['name'].'</a></td><td align="center">'.$hangar[einh2].'</td><td align="right">'.$einh[2]['eisen'].'</td><td align="right">'.$einh[2]['titan'].'</td><td align="right">'.$einh[2]['oel'].'</td><td align="right">'.$einh[2]['uran'].'</td><td align="right">'.$einh[2]['gold'].'</td><td align="right">'.$einh[2]['chanje'].'</td><td align="right">'.time2str($einh[2][dauer]).'</td><td class="input"><center><input type="text" name="anz2" value="" class="input"   onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x2" value="'.$_SESSION['eh'][2].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=2&'.SID.'\',400)">'.$einh[2]['name'].'</a></td><td align="center">'.$hangar[einh2].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/1 Feuerwaffen '.$forschung['feuerwaffen'].'/1 Sprengstoff '.$forschung['sprengstoff'].'/1 <input type="hidden" name="anz2" value="0" /></td></tr>';}

if ($gebaeude['fabrik'] >= 2 and $forschung['feuerwaffen'] >= 2 and $forschung['sprengstoff'] >= 2 and $forschung['raketen'] >= 1){$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=3&'.SID.'\',400)">'.$einh[3]['name'].'</a></td><td align="center">'.$hangar[einh3].'</td><td align="right">'.$einh[3]['eisen'].'</td><td align="right">'.$einh[3]['titan'].'</td><td align="right">'.$einh[3]['oel'].'</td><td align="right">'.$einh[3]['uran'].'</td><td align="right">'.$einh[3]['gold'].'</td><td align="right">'.$einh[3]['chanje'].'</td><td align="right">'.time2str($einh[3][dauer]).'</td><td class="input"><center><input type="text" name="anz3" value="" class="input"  onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x3" value="'.$_SESSION['eh'][3].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=3&'.SID.'\',400)">'.$einh[3]['name'].'</a></td><td align="center">'.$hangar[einh3].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/2 Feuerwaffen '.$forschung['feuerwaffen'].'/2 Sprengstoff '.$forschung['sprengstoff'].'/2 Raketen '.$forschung['raketen'].'/1 <input type="hidden" name="anz3" value="0" /></td></tr>';}

if ($gebaeude['fabrik'] >= 2 and $forschung['feuerwaffen'] >= 2 and $forschung['sprengstoff'] >= 2 and $forschung['raketen'] >= 1 and $forschung['fuehrung'] >= 2){$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=4&'.SID.'\',400)">'.$einh[4]['name'].'</a></td><td align="center">'.$hangar[einh4].'</td><td align="right">'.$einh[4]['eisen'].'</td><td align="right">'.$einh[4]['titan'].'</td><td align="right">'.$einh[4]['oel'].'</td><td align="right">'.$einh[4]['uran'].'</td><td align="right">'.$einh[4]['gold'].'</td><td align="right">'.$einh[4]['chanje'].'</td><td align="right">'.time2str($einh[4][dauer]).'</td><td class="input"><center><input type="text" name="anz4" value="" class="input"  onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x4" value="'.$_SESSION['eh'][4].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=4&'.SID.'\',400)">'.$einh[4]['name'].'</a></td><td align="center">'.$hangar[einh4].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/2 Feuerwaffen '.$forschung['feuerwaffen'].'/2 Sprengstoff '.$forschung['sprengstoff'].'/2 Raketen '.$forschung['raketen'].'/2 F&uuml;hrung '.$forschung['fuehrung'].'/2 <input type="hidden" name="anz4" value="0" /></td></tr>';}
$content .= '</table>';

$content .= '<br /><span style="font-size: 12px";><b>KFZ und Panzer:</b></span><br /><table border="1" cellspacing="0" class="standard">
<tr align="center"><th style="width:130px">&nbsp;</th><th>Bestand</th><th style="width:55px">Eisen</th><th style="width:55px">Titan</th><th style="width:55px">Oel</th><th style="width:55px">Uran</th><th style="width:55px">Gold</th><th style="width:55px">Chanje</th><th style="width:55px">Dauer</th><th style="width:55px">&nbsp;</th></tr>';

if ($gebaeude['fabrik'] >= 3 and $gebaeude['fahrwege'] >= 1 and $forschung['feuerwaffen'] >= 4 and $forschung['rad'] >= 1 and $forschung['panzerung'] >= 1 and $forschung['motor'] >= 1) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=5&'.SID.'\',400)">'.$einh[5]['name'].'</a></td><td align="center">'.$hangar[einh5].'</td><td align="right">'.$einh[5]['eisen'].'</td><td align="right">'.$einh[5]['titan'].'</td><td align="right">'.$einh[5]['oel'].'</td><td align="right">'.$einh[5]['uran'].'</td><td align="right">'.$einh[5]['gold'].'</td><td align="right">'.$einh[5]['chanje'].'</td><td align="right">'.time2str($einh[5][dauer]).'</td><td class="input"><center><input type="text" name="anz5" value="" class="input"  onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x5" value="'.$_SESSION['eh'][5].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=5&'.SID.'\',400)">'.$einh[5]['name'].'</a></td><td align="center">'.$hangar[einh5].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/3 Fahrwege '.$gebaeude['fahrwege'].'/1 Feuerwaffen '.$forschung['feuerwaffen'].'/4 Radverst&auml;rkungen '.$forschung['rad'].'/2 <br />Panzerung '.$forschung['panzerung'].'/2 Motor '.$forschung['motor'].'/1 <input type="hidden" name="anz5" value="0" /></td></tr>';}

if ($gebaeude['fabrik'] >= 4 and $gebaeude['fahrwege'] >= 3 and $forschung['feuerwaffen'] >= 6 and $forschung['panzerketten'] >= 2 and $forschung['panzerung'] >= 3 and $forschung['sprengstoff'] >= 3 and $forschung['motor'] >= 3) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=6&'.SID.'\',400)">'.$einh[6]['name'].'</a></td><td align="center">'.$hangar[einh6].'</td><td align="right">'.$einh[6]['eisen'].'</td><td align="right">'.$einh[6]['titan'].'</td><td align="right">'.$einh[6]['oel'].'</td><td align="right">'.$einh[6]['uran'].'</td><td align="right">'.$einh[6]['gold'].'</td><td align="right">'.$einh[6]['chanje'].'</td><td align="right">'.time2str($einh[6][dauer]).'</td><td class="input"><center><input type="text" name="anz6" value="" class="input"   onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x6" value="'.$_SESSION['eh'][6].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=6&'.SID.'\',400)">'.$einh[6]['name'].'</a></td><td align="center">'.$hangar[einh6].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/4 Fahrwege '.$gebaeude['fahrwege'].'/3 Feuerwaffen '.$forschung['feuerwaffen'].'/6 Sprengstoff '.$forschung['sprengstoff'].'/3 <br />Panzerketten '.$forschung['panzerketten'].'/2 Panzerung '.$forschung['panzerung'].'/3 Motor '.$forschung['motor'].'/3 <input type="hidden" name="anz6" value="0" /></td></tr>';}

if ($gebaeude['fabrik'] >= 6 and $gebaeude['fahrwege'] >= 6 and $forschung['feuerwaffen'] >= 7 and $forschung['panzerketten'] >= 5 and $forschung['panzerung'] >= 4 and $forschung['sprengstoff'] >= 4 and $forschung['motor'] >= 4) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=7&'.SID.'\',400)">'.$einh[7]['name'].'</a></td><td align="center">'.$hangar[einh7].'</td><td align="right">'.$einh[7]['eisen'].'</td><td align="right">'.$einh[7]['titan'].'</td><td align="right">'.$einh[7]['oel'].'</td><td align="right">'.$einh[7]['uran'].'</td><td align="right">'.$einh[7]['gold'].'</td><td align="right">'.$einh[7]['chanje'].'</td><td align="right">'.time2str($einh[7][dauer]).'</td><td class="input"><center><input type="text" name="anz7" value="" class="input"  onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x7" value="'.$_SESSION['eh'][7].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=7&'.SID.'\',400)">'.$einh[7]['name'].'</a></td><td align="center">'.$hangar[einh7].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/6 Fahrwege '.$gebaeude['fahrwege'].'/6 Feuerwaffen '.$forschung['feuerwaffen'].'/7 Sprengstoff '.$forschung['sprengstoff'].'/4 <br />Panzerketten '.$forschung['panzerketten'].'/5 Panzerung '.$forschung['panzerung'].'/4 Motor '.$forschung['motor'].'/4 <input type="hidden" name="anz7" value="0" /></td></tr>';}

if ($gebaeude['fabrik'] >= 8 and $gebaeude['fahrwege'] >= 8 and $forschung['feuerwaffen'] >= 9 and $forschung['panzerketten'] >= 7 and $forschung['panzerung'] >= 8 and $forschung['sprengstoff'] >= 5 and $forschung['motor'] >= 7) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=8&'.SID.'\',400)">'.$einh[8]['name'].'</a></td><td align="center">'.$hangar[einh8].'</td><td align="right">'.$einh[8]['eisen'].'</td><td align="right">'.$einh[8]['titan'].'</td><td align="right">'.$einh[8]['oel'].'</td><td align="right">'.$einh[8]['uran'].'</td><td align="right">'.$einh[8]['gold'].'</td><td align="right">'.$einh[8]['chanje'].'</td><td align="right">'.time2str($einh[8][dauer]).'</td><td class="input"><center><input type="text" name="anz8" value="" class="input"  onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x8" value="'.$_SESSION['eh'][8].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=8&'.SID.'\',400)">'.$einh[8]['name'].'</a></td><td align="center">'.$hangar[einh8].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/8 Fahrwege '.$gebaeude['fahrwege'].'/8 Feuerwaffen '.$forschung['feuerwaffen'].'/9 Sprengstoff '.$forschung['sprengstoff'].'/5 <br />Panzerketten '.$forschung['panzerketten'].'/7 Panzerung '.$forschung['panzerung'].'/8 Motor '.$forschung['motor'].'/7 <input type="hidden" name="anz8" value="0" /></td></tr>';}

if ($gebaeude['fabrik'] >= 10 and $gebaeude['fahrwege'] >= 6 and $forschung['feuerwaffen'] >= 11 and $forschung['rad'] >= 7 and $forschung['panzerung'] >= 6 and $forschung['sprengstoff'] >= 7 and $forschung['motor'] >= 6) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=9&'.SID.'\',400)">'.$einh[9]['name'].'</a></td><td align="center">'.$hangar[einh9].'</td><td align="right">'.$einh[9]['eisen'].'</td><td align="right">'.$einh[9]['titan'].'</td><td align="right">'.$einh[9]['oel'].'</td><td align="right">'.$einh[9]['uran'].'</td><td align="right">'.$einh[9]['gold'].'</td><td align="right">'.$einh[9]['chanje'].'</td><td align="right">'.time2str($einh[9][dauer]).'</td><td class="input"><center><input type="text" name="anz9" value="" class="input"  onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x9" value="'.$_SESSION['eh'][9].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=9&'.SID.'\',400)">'.$einh[9]['name'].'</a></td><td align="center">'.$hangar[einh9].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/10 Fahrwege '.$gebaeude['fahrwege'].'/6 Feuerwaffen '.$forschung['feuerwaffen'].'/11 Sprengstoff '.$forschung['sprengstoff'].'/7 <br />Radverst&auml;rkungen '.$forschung['rad'].'/7 Panzerung '.$forschung['panzerung'].'/6 Motor '.$forschung['motor'].'/6 <input type="hidden" name="anz9" value="0" /></td></tr>';}

if ($gebaeude['fabrik'] >= 12 and $gebaeude['fahrwege'] >= 10 and $forschung['feuerwaffen'] >= 12 and $forschung['panzerketten'] >= 9 and $forschung['panzerung'] >= 10 and $forschung['sprengstoff'] >= 9 and $forschung['motor'] >= 10) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=10&'.SID.'\',400)">'.$einh[10]['name'].'</a></td><td align="center">'.$hangar[einh10].'</td><td align="right">'.$einh[10]['eisen'].'</td><td align="right">'.$einh[10]['titan'].'</td><td align="right">'.$einh[10]['oel'].'</td><td align="right">'.$einh[10]['uran'].'</td><td align="right">'.$einh[10]['gold'].'</td><td align="right">'.$einh[10]['chanje'].'</td><td align="right">'.time2str($einh[10][dauer]).'</td><td class="input"><center><input type="text" name="anz10" value="" class="input"  onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x10" value="'.$_SESSION['eh'][10].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=10&'.SID.'\',400)">'.$einh[10]['name'].'</a></td><td align="center">'.$hangar[einh10].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/12 Fahrwege '.$gebaeude['fahrwege'].'/10 Feuerwaffen '.$forschung['feuerwaffen'].'/12 Sprengstoff '.$forschung['sprengstoff'].'/9 <br />Panzerketten '.$forschung['panzerketten'].'/9 Panzerung '.$forschung['panzerung'].'/10 Motor '.$forschung['motor'].'/10 <input type="hidden" name="anz10" value="0" /></td></tr>';}

if ($gebaeude['fabrik'] >= 15 and $gebaeude['fahrwege'] >= 15 and $forschung['feuerwaffen'] >= 15 and $forschung['panzerketten'] >= 15 and $forschung['panzerung'] >= 15 and $forschung['sprengstoff'] >= 10 and $forschung['motor'] >= 15) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=11&'.SID.'\',400)">'.$einh[11]['name'].'</a></td><td align="center">'.$hangar[einh11].'</td><td align="right">'.$einh[11]['eisen'].'</td><td align="right">'.$einh[11]['titan'].'</td><td align="right">'.$einh[11]['oel'].'</td><td align="right">'.$einh[11]['uran'].'</td><td align="right">'.$einh[11]['gold'].'</td><td align="right">'.$einh[11]['chanje'].'</td><td align="right">'.time2str($einh[11][dauer]).'</td><td class="input"><center><input type="text" name="anz11" value="" class="input"   onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x11" value="'.$_SESSION['eh'][11].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=11&'.SID.'\',400)">'.$einh[11]['name'].'</a></td><td align="center">'.$hangar[einh11].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/15 Fahrwege '.$gebaeude['fahrwege'].'/15 Feuerwaffen '.$forschung['feuerwaffen'].'/15 Sprengstoff '.$forschung['sprengstoff'].'/10 <br />Panzerketten '.$forschung['panzerketten'].'/15 Panzerung '.$forschung['panzerung'].'/15 Motor '.$forschung['motor'].'/15 <input type="hidden" name="anz11" value="0" /></td></tr>';}
$content .= '</table>';

$content .= '<br /><span style="font-size: 12px";><b>SdKFZ:</b></span><br /><table border="1" cellspacing="0" class="standard">
<tr align="center"><th style="width:130px">&nbsp;</th><th>Bestand</th><th style="width:55px">Eisen</th><th style="width:55px">Titan</th><th style="width:55px">Oel</th><th style="width:55px">Uran</th><th style="width:55px">Gold</th><th style="width:55px">Chanje</th><th style="width:55px">Dauer</th><th style="width:55px">&nbsp;</th></tr>';

if ($gebaeude['fabrik'] >= 2 and $gebaeude['fahrwege'] >= 1 and $forschung['rad'] >= 1 and $forschung['panzerung'] >= 1 and $forschung['motor'] >= 1) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=12&'.SID.'\',400)">'.$einh[12]['name'].'</a></td><td align="center">'.$hangar[einh12].'</td><td align="right">'.$einh[12]['eisen'].'</td><td align="right">'.$einh[12]['titan'].'</td><td align="right">'.$einh[12]['oel'].'</td><td align="right">'.$einh[12]['uran'].'</td><td align="right">'.$einh[12]['gold'].'</td><td align="right">'.$einh[12]['chanje'].'</td><td align="right">'.time2str($einh[12][dauer]).'</td><td class="input"><center><input type="text" name="anz12" value="" class="input"   onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x12" value="'.$_SESSION['eh'][12].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=12&'.SID.'\',400)">'.$einh[12]['name'].'</a></td><td align="center">'.$hangar[einh12].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/2 Fahrwege '.$gebaeude['fahrwege'].'/1 Radverst&auml;rkungen '.$forschung['rad'].'/1 Panzerung '.$forschung['panzerung'].'/1 Motor '.$forschung['motor'].'/1 <input type="hidden" name="anz12" value="0" /></td></tr>';}

if ($gebaeude['fabrik'] >= 6 and $gebaeude['fahrwege'] >= 3 and $forschung['rad'] >= 4 and $forschung['panzerung'] >= 3 and $forschung['motor'] >= 4) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=13&'.SID.'\',400)">'.$einh[13]['name'].'</a></td><td align="center">'.$hangar[einh13].'</td><td align="right">'.$einh[13]['eisen'].'</td><td align="right">'.$einh[13]['titan'].'</td><td align="right">'.$einh[13]['oel'].'</td><td align="right">'.$einh[13]['uran'].'</td><td align="right">'.$einh[13]['gold'].'</td><td align="right">'.$einh[13]['chanje'].'</td><td align="right">'.time2str($einh[13][dauer]).'</td><td class="input"><center><input type="text" name="anz13" value="" class="input"  onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x13" value="'.$_SESSION['eh'][13].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=13&'.SID.'\',400)">'.$einh[13]['name'].'</a></td><td align="center">'.$hangar[einh13].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/6 Fahrwege '.$gebaeude['fahrwege'].'/3 Radverst&auml;rkungen '.$forschung['rad'].'/4 Panzerung '.$forschung['panzerung'].'/3 Motor '.$forschung['motor'].'/4 <input type="hidden" name="anz13" value="0" /></td></tr>';}

if ($gebaeude['fabrik'] >= 3 and $gebaeude['fahrwege'] >= 1 and $forschung['rad'] >= 4 and $forschung['motor'] >= 3 and $forschung['feuerwaffen'] >= 2 ) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=14&'.SID.'\',400)">'.$einh[14]['name'].'</a></td><td align="center">'.$hangar[einh14].'</td><td align="right">'.$einh[14]['eisen'].'</td><td align="right">'.$einh[14]['titan'].'</td><td align="right">'.$einh[14]['oel'].'</td><td align="right">'.$einh[14]['uran'].'</td><td align="right">'.$einh[14]['gold'].'</td><td align="right">'.$einh[14]['chanje'].'</td><td align="right">'.time2str($einh[14][dauer]).'</td><td class="input"><center><input type="text" name="anz14" value="" class="input"   onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x14" value="'.$_SESSION['eh'][14].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=14&'.SID.'\',400)">'.$einh[14]['name'].'</a></td><td align="center">'.$hangar[einh14].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/3 Fahrwege '.$gebaeude['fahrwege'].'/1 Radverst&auml;rkungen '.$forschung['rad'].'/4 Feuerwaffen '.$forschung['feuerwaffen'].'/2 Motor '.$forschung['motor'].'/3 <input type="hidden" name="anz14" value="0" /></td></tr>';}

if ($gebaeude['fabrik'] >= 4 and $gebaeude['fahrwege'] >= 3 and $forschung['rad'] >= 3 and $forschung['motor'] >= 4 ) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=15&'.SID.'\',400)">'.$einh[15]['name'].'</a></td><td align="center">'.$hangar[einh15].'</td><td align="right">'.$einh[15]['eisen'].'</td><td align="right">'.$einh[15]['titan'].'</td><td align="right">'.$einh[15]['oel'].'</td><td align="right">'.$einh[15]['uran'].'</td><td align="right">'.$einh[15]['gold'].'</td><td align="right">'.$einh[15]['chanje'].'</td><td align="right">'.time2str($einh[15][dauer]).'</td><td class="input"><center><input type="text" name="anz15" value="" class="input"  onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /></center><input type="hidden" name="x15" value="'.$_SESSION['eh'][15].'"></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id=15&'.SID.'\',400)">'.$einh[15]['name'].'</a></td><td align="center">'.$hangar[einh15].'</td><td align="center" colspan="8">Fabrik '.$gebaeude['fabrik'].'/4 Fahrwege '.$gebaeude['fahrwege'].'/3 Radverst&auml;rkungen '.$forschung['rad'].'/3 Motor '.$forschung['motor'].'/4 <input type="hidden" name="anz15" value="0" /></td></tr>';}

$content .= '</table>';

$content .= '<script type="text/javascript"><!--
        function runde(x, n) {
        if (n < 1 || n > 14) return false;
        var e = Math.pow(10, n);
        var k = (Math.round(x * e) / e).toString();
        if (k.indexOf(".") == -1) k += ".";
        k += e.toString().substring(1);
        return k.substring(0, k.indexOf(".") + n+1);
}

function calculate_price() {
  var e = '.$ressis['eisen'].';
  var t = '.$ressis['titan'].';
  var o = '.$ressis['oel'].';
  var u = '.$ressis['uran'].';
  var g = '.$ressis['gold'].';
  var c = '.$ressis['chanje'].';

  var e1eisen = '.$einh[1]['eisen'].';
  var e2eisen = '.$einh[2]['eisen'].';
  var e3eisen = '.$einh[3]['eisen'].';
  var e4eisen = '.$einh[4]['eisen'].';
  var e5eisen = '.$einh[5]['eisen'].';
  var e6eisen = '.$einh[6]['eisen'].';
  var e7eisen = '.$einh[7]['eisen'].';
  var e8eisen = '.$einh[8]['eisen'].';
  var e9eisen = '.$einh[9]['eisen'].';
  var e10eisen = '.$einh[10]['eisen'].';
  var e11eisen = '.$einh[11]['eisen'].';
  var e12eisen = '.$einh[12]['eisen'].';
  var e13eisen = '.$einh[13]['eisen'].';
  var e14eisen = '.$einh[14]['eisen'].';
  var e15eisen = '.$einh[15]['eisen'].';
  var e1titan = '.$einh[1]['titan'].';
  var e2titan = '.$einh[2]['titan'].';
  var e3titan = '.$einh[3]['titan'].';
  var e4titan = '.$einh[4]['titan'].';
  var e5titan = '.$einh[5]['titan'].';
  var e6titan = '.$einh[6]['titan'].';
  var e7titan = '.$einh[7]['titan'].';
  var e8titan = '.$einh[8]['titan'].';
  var e9titan = '.$einh[9]['titan'].';
  var e10titan = '.$einh[10]['titan'].';
  var e11titan = '.$einh[11]['titan'].';
  var e12titan = '.$einh[12]['titan'].';
  var e13titan = '.$einh[13]['titan'].';
  var e14titan = '.$einh[14]['titan'].';
  var e15titan = '.$einh[15]['titan'].';
  var e1oel = '.$einh[1]['oel'].';
  var e2oel = '.$einh[2]['oel'].';
  var e3oel = '.$einh[3]['oel'].';
  var e4oel = '.$einh[4]['oel'].';
  var e5oel = '.$einh[5]['oel'].';
  var e6oel = '.$einh[6]['oel'].';
  var e7oel = '.$einh[7]['oel'].';
  var e8oel = '.$einh[8]['oel'].';
  var e9oel = '.$einh[9]['oel'].';
  var e10oel = '.$einh[10]['oel'].';
  var e11oel = '.$einh[11]['oel'].';
  var e12oel = '.$einh[12]['oel'].';
  var e13oel = '.$einh[13]['oel'].';
  var e14oel = '.$einh[14]['oel'].';
  var e15oel = '.$einh[15]['oel'].';
  var e1uran = '.$einh[1]['uran'].';
  var e2uran = '.$einh[2]['uran'].';
  var e3uran = '.$einh[3]['uran'].';
  var e4uran = '.$einh[4]['uran'].';
  var e5uran = '.$einh[5]['uran'].';
  var e6uran = '.$einh[6]['uran'].';
  var e7uran = '.$einh[7]['uran'].';
  var e8uran = '.$einh[8]['uran'].';
  var e9uran = '.$einh[9]['uran'].';
  var e10uran = '.$einh[10]['uran'].';
  var e11uran = '.$einh[11]['uran'].';
  var e12uran = '.$einh[12]['uran'].';
  var e13uran = '.$einh[13]['uran'].';
  var e14uran = '.$einh[14]['uran'].';
  var e15uran = '.$einh[15]['uran'].';
  var e1gold = '.$einh[1]['gold'].';
  var e2gold = '.$einh[2]['gold'].';
  var e3gold = '.$einh[3]['gold'].';
  var e4gold = '.$einh[4]['gold'].';
  var e5gold = '.$einh[5]['gold'].';
  var e6gold = '.$einh[6]['gold'].';
  var e7gold = '.$einh[7]['gold'].';
  var e8gold = '.$einh[8]['gold'].';
  var e9gold = '.$einh[9]['gold'].';
  var e10gold = '.$einh[10]['gold'].';
  var e11gold = '.$einh[11]['gold'].';
  var e12gold = '.$einh[12]['gold'].';
  var e13gold = '.$einh[13]['gold'].';
  var e14gold = '.$einh[14]['gold'].';
  var e15gold = '.$einh[15]['gold'].';
  var e1chanje = '.$einh[1]['chanje'].';
  var e2chanje = '.$einh[2]['chanje'].';
  var e3chanje = '.$einh[3]['chanje'].';
  var e4chanje = '.$einh[4]['chanje'].';
  var e5chanje = '.$einh[5]['chanje'].';
  var e6chanje = '.$einh[6]['chanje'].';
  var e7chanje = '.$einh[7]['chanje'].';
  var e8chanje = '.$einh[8]['chanje'].';
  var e9chanje = '.$einh[9]['chanje'].';
  var e10chanje = '.$einh[10]['chanje'].';
  var e11chanje = '.$einh[11]['chanje'].';
  var e12chanje = '.$einh[12]['chanje'].';
  var e13chanje = '.$einh[13]['chanje'].';
  var e14chanje = '.$einh[14]['chanje'].';
  var e15chanje = '.$einh[15]['chanje'].';
  var e1dauer = '.$einh[1]['dauer'].';
  var e2dauer = '.$einh[2]['dauer'].';
  var e3dauer = '.$einh[3]['dauer'].';
  var e4dauer = '.$einh[4]['dauer'].';
  var e5dauer = '.$einh[5]['dauer'].';
  var e6dauer = '.$einh[6]['dauer'].';
  var e7dauer = '.$einh[7]['dauer'].';
  var e8dauer = '.$einh[8]['dauer'].';
  var e9dauer = '.$einh[9]['dauer'].';
  var e10dauer = '.$einh[10]['dauer'].';
  var e11dauer = '.$einh[11]['dauer'].';
  var e12dauer = '.$einh[12]['dauer'].';
  var e13dauer = '.$einh[13]['dauer'].';
  var e14dauer = '.$einh[14]['dauer'].';
  var e15dauer = '.$einh[15]['dauer'].';

  var anz1 = Math.abs(document.getElementsByName("anz1")[0].value);
  var anz2 = Math.abs(document.getElementsByName("anz2")[0].value);
  var anz3 = Math.abs(document.getElementsByName("anz3")[0].value);
  var anz4 = Math.abs(document.getElementsByName("anz4")[0].value);
  var anz5 = Math.abs(document.getElementsByName("anz5")[0].value);
  var anz6 = Math.abs(document.getElementsByName("anz6")[0].value);
  var anz7 = Math.abs(document.getElementsByName("anz7")[0].value);
  var anz8 = Math.abs(document.getElementsByName("anz8")[0].value);
  var anz9 = Math.abs(document.getElementsByName("anz9")[0].value);
  var anz10 = Math.abs(document.getElementsByName("anz10")[0].value);
  var anz11 = Math.abs(document.getElementsByName("anz11")[0].value);
  var anz12 = Math.abs(document.getElementsByName("anz12")[0].value);
  var anz13 = Math.abs(document.getElementsByName("anz13")[0].value);
  var anz14 = Math.abs(document.getElementsByName("anz14")[0].value);
  var anz15 = Math.abs(document.getElementsByName("anz15")[0].value);

  var e1 = anz1*e1eisen; 
  var t1 = anz1*e1titan; 
  var o1 = anz1*e1oel; 
  var u1 = anz1*e1uran; 
  var g1 = anz1*e1gold; 
  var c1 = anz1*e1chanje; 
  var d1 = anz1*e1dauer;
  var e2 = anz2*e2eisen;
  var t2 = anz2*e2titan; 
  var o2 = anz2*e2oel; 
  var u2 = anz2*e2uran; 
  var g2 = anz2*e2gold; 
  var c2 = anz2*e2chanje; 
  var d2 = anz2*e2dauer;
  var e3 = anz3*e3eisen; 
  var t3 = anz3*e3titan; 
  var o3 = anz3*e3oel; 
  var u3 = anz3*e3uran; 
  var g3 = anz3*e3gold; 
  var c3 = anz3*e3chanje; 
  var d3 = anz3*e3dauer;
  var e4 = anz4*e4eisen;
  var t4 = anz4*e4titan; 
  var o4 = anz4*e4oel; 
  var u4 = anz4*e4uran; 
  var g4 = anz4*e4gold; 
  var c4 = anz4*e4chanje; 
  var d4 = anz4*e4dauer;
  var e5 = anz5*e5eisen;
  var t5 = anz5*e5titan;
  var o5 = anz5*e5oel;
  var u5 = anz5*e5uran;
  var g5 = anz5*e5gold;
  var c5 = anz5*e5chanje;
  var d5 = anz5*e5dauer;
  var e6 = anz6*e6eisen; 
  var t6 = anz6*e6titan; 
  var o6 = anz6*e6oel; 
  var u6 = anz6*e6uran;
  var g6 = anz6*e6gold;
  var c6 = anz6*e6chanje;
  var d6 = anz6*e6dauer;
  var e7 = anz7*e7eisen;
  var t7 = anz7*e7titan;
  var o7 = anz7*e7oel;
  var u7 = anz7*e7uran;
  var g7 = anz7*e7gold;
  var c7 = anz7*e7chanje;
  var d7 = anz7*e7dauer;
  var e8 = anz8*e8eisen;
  var t8 = anz8*e8titan;
  var o8 = anz8*e8oel;
  var u8 = anz8*e8uran;
  var g8 = anz8*e8gold;
  var c8 = anz8*e8chanje;
  var d8 = anz8*e8dauer;
  var e9 = anz9*e9eisen;
  var t9 = anz9*e9titan;
  var o9 = anz9*e9oel;
  var u9 = anz9*e9uran;
  var g9 = anz9*e9gold;
  var c9 = anz9*e9chanje;
  var d9 = anz9*e9dauer;
  var e10 = anz10*e10eisen;
  var t10 = anz10*e10titan;
  var o10 = anz10*e10oel;
  var u10 = anz10*e10uran;
  var g10 = anz10*e10gold;
  var c10 = anz10*e10chanje;
  var d10 = anz10*e10dauer;
  var e11 = anz11*e11eisen;
  var t11 = anz11*e11titan;
  var o11 = anz11*e11oel;
  var u11 = anz11*e11uran;
  var g11 = anz11*e11gold;
  var c11 = anz11*e11chanje;
  var d11 = anz11*e11dauer;
  var e12 = anz12*e12eisen;
  var t12 = anz12*e12titan;
  var o12 = anz12*e12oel;
  var u12 = anz12*e12uran;
  var g12 = anz12*e12gold;
  var c12 = anz12*e12chanje;
  var d12 = anz12*e12dauer;
  var e13 = anz13*e13eisen;
  var t13 = anz13*e13titan;
  var o13 = anz13*e13oel; 
  var u13 = anz13*e13uran;
  var g13 = anz13*e13gold;
  var c13 = anz13*e13chanje;
  var d13 = anz13*e13dauer;
  var e14 = anz14*e14eisen;
  var t14 = anz14*e14titan;
  var o14 = anz14*e14oel;
  var u14 = anz14*e14uran;
  var g14 = anz14*e14gold;
  var c14 = anz14*e14chanje;
  var d14 = anz14*e14dauer;
  var e15 = anz15*e15eisen;
  var t15 = anz15*e15titan;
  var o15 = anz15*e15oel;
  var u15 = anz15*e15uran;
  var g15 = anz15*e15gold;
  var c15 = anz15*e15chanje;
  var d15 = anz15*e15dauer;

  var dauer = d1+d2+d3+d4+d5+d6+d7+d8+d9+d10+d11+d12+d13+d14+d15;
  var min = Math.floor(dauer/60);
  dauer = dauer - min * 60;
  var h = Math.floor(min/60);
  min = min - h * 60;

  dauer = Math.floor(dauer);

  if(min<10) { min="0"+min; }
  if(dauer<10) { dauer="0"+dauer; }

  var g_e = e1+e2+e3+e4+e5+e6+e7+e8+e9+e10+e11+e12+e13+e14+e15;
  var g_t = t1+t2+t3+t4+t5+t6+t7+t8+t9+t10+t11+t12+t13+t14+t15;  
  var g_o = o1+o2+o3+o4+o5+o6+o7+o8+o9+o10+o11+o12+o13+o14+o15;
  var g_u = u1+u2+u3+u4+u5+u6+u7+u8+u9+u10+u11+u12+u13+u14+u15;
  var g_g = g1+g2+g3+g4+g5+g6+g7+g8+g9+g10+g11+g12+g13+g14+g15;
  var g_c = c1+c2+c3+c4+c5+c6+c7+c8+c9+c10+c11+c12+c13+c14+c15;

  if(g_e>e) { g_e = "<font class=\"red\">"+g_e+"</font>"; }
  if(g_t>t) { g_t = "<font class=\"red\">"+g_t+"</font>"; }
  if(g_o>o) { g_o = "<font class=\"red\">"+g_o+"</font>"; }
  if(g_u>u) { g_u = "<font class=\"red\">"+g_u+"</font>"; }
  if(g_g>g) { g_g = "<font class=\"red\">"+g_g+"</font>"; }
  if(g_c>c) { g_c = "<font class=\"red\">"+g_c+"</font>"; }

  document.getElementById("k_eisen").innerHTML=g_e;
  document.getElementById("k_titan").innerHTML=g_t;
  document.getElementById("k_oel").innerHTML=g_o;
  document.getElementById("k_uran").innerHTML=g_u;
  document.getElementById("k_gold").innerHTML=g_g;
  document.getElementById("k_chanje").innerHTML=g_c;
  document.getElementById("k_dauer").innerHTML=h+":"+min+":"+dauer;
}
// End -->
</script>
<br /><b>Kosten:</b><br /><table border="1" cellspacing="0" class="standard">
<tr align="center"><th style="width:55px">Eisen</th><th style="width:55px">Titan</th><th style="width:55px">Oel</th><th style="width:55px">Uran</th><th style="width:55px">Gold</th><th style="width:55px">Chanje</th><th style="width:55px">Dauer</th></tr>';

$content .= '<tr class="standard"><td align="right"><a id="k_eisen">0</a></td><td align="right"><a id="k_titan">0</a></td><td align="right"><a id="k_oel">0</a></td><td align="right"><a id="k_uran">0</a></td><td align="right"><a id="k_gold">0</a></td><td align="right"><a id="k_chanje">0</a></td><td align="right"><a id="k_dauer">0:00:00</a></td></tr>';

$content .= '</table>
<br />
<center><input type="submit" name="submit" value="bauen"></center>
</form>
<br />
<b>Achtung:</b> Einheiten, die die maximale Hangarkapazit&auml;t &uuml;berschreiten <b>verfallen sofort</b>.<br /><br /></td></tr></table>';

$result = mysql_query("SELECT * FROM `fabrik` WHERE `omni` = ".($_SESSION['user']['omni'])." GROUP BY fertigstellung ASC;");

$row  = mysql_fetch_array($result);
if ($row) {
	$content .= '<br /><table border="1" cellspacing="0" class="standard" style="width:250px">
	<tr align="center" class="standard"><th colspan="2">Aktuelle Auftr&auml;ge:</th></tr><tr>';
	$start = $row['fertigstellung'];
	do {
		$i++;
		if ($i < 4) {
			if ($row['type'] >= 1 and $row['type'] <= 15) { $content .= '<tr align="left"><td>'.$einh[$row['type']]['name'].'</td><td style="width:75px"><center>'.countdown($row['fertigstellung']-date(U)).'</center></td></tr>'; $stop=$row['fertigstellung']; }
			if ($row['type'] >= 1001 and $row['type'] <= 1011) { $row['type'] -= 1000; $content .= '<tr align="left"><td>'.$def[$row['type']]['name'].'</td><td style="width:55px"><center>'.countdown($row['fertigstellung']-date(U)).'</center></td></tr>'; $stop=$row['fertigstellung']; }
			if ($row['type'] >= 2001 and $row['type'] <= 2006) { $row['type'] -= 2000; $content .= '<tr align="left"><td>'.$rak[$row['type']]['name'].'</td><td style="width:55px"><center>'.countdown($row['fertigstellung']-date(U)).'</center></td></tr>'; $stop=$row['fertigstellung']; }
		} else {
			if ($row['type'] >= 1 and $row['type'] <= 15) { $content .= '<tr align="left"><td>'.$einh[$row['type']]['name'].'</td><td style="width:95px"><center>'.time2str($row['fertigstellung']-date(U)).'</center></td></tr>'; $stop=$row['fertigstellung']; }
			if ($row['type'] >= 1001 and $row['type'] <= 1011) { $row['type'] -= 1000; $content .= '<tr align="left"><td>'.$def[$row['type']]['name'].'</td><td style="width:55px"><center>'.time2str($row['fertigstellung']-date(U)).'</center></td></tr>'; $stop=$row['fertigstellung']; }
			if ($row['type'] >= 2001 and $row['type'] <= 2006) { $row['type'] -= 2000; $content .= '<tr align="left"><td>'.$rak[$row['type']]['name'].'</td><td style="width:55px"><center>'.time2str($row['fertigstellung']-date(U)).'</center></td></tr>'; $stop=$row['fertigstellung']; }		
		}
		$row  = mysql_fetch_array($result);
	} while ($row);
	$content .= '</table>';
}

// gebauede
for ($i=1; $i <= $gebaeude['fabrik']; $i++) {
	$content = str_replace('Fabrik '.$gebaeude['fabrik'].'/'.$i.' ', '<font class="green">Fabrik '.$gebaeude['fabrik'].'/'.$i.' </font>', $content);
}
for ($i=1; $i <= $gebaeude['fahrwege']; $i++) {
	$content = str_replace('Fahrwege '.$gebaeude['fahrwege'].'/'.$i.' ', '<font class="green">Fahrwege '.$gebaeude['fahrwege'].'/'.$i.' </font>', $content);
}

// forschungen
for ($i=$forschung['feuerwaffen']; $i > 0; $i--) {
	$content = str_replace('Feuerwaffen '.$forschung['feuerwaffen'].'/'.$i.' ', '<font class="green">Feuerwaffen '.$forschung['feuerwaffen'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['sprengstoff']; $i > 0; $i--) {
	$content = str_replace('Sprengstoff '.$forschung['sprengstoff'].'/'.$i.' ', '<font class="green">Sprengstoff '.$forschung['sprengstoff'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['raketen']; $i > 0; $i--) {
	$content = str_replace('Raketen '.$forschung['raketen'].'/'.$i.' ', '<font class="green">Raketen '.$forschung['raketen'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['fuehrung'];  $i > 0; $i--) {
	$content = str_replace('F&uuml;hrung '.$forschung['fuehrung'].'/'.$i.' ', '<font class="green">F&uuml;hrung '.$forschung['fuehrung'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['rad']; $i > 0; $i--) {
	$content = str_replace('Radverst&auml;rkungen '.$forschung['rad'].'/'.$i.' ', '<font class="green">Radverst&auml;rkungen '.$forschung['rad'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['panzerung']; $i > 0; $i--) {
	$content = str_replace('Panzerung '.$forschung['panzerung'].'/'.$i.' ', '<font class="green">Panzerung '.$forschung['panzerung'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['motor']; $i > 0; $i--) {
	$content = str_replace('Motor '.$forschung['motor'].'/'.$i.' ', '<font class="green">Motor '.$forschung['motor'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['panzerketten']; $i > 0; $i--) {
	$content = str_replace('Panzerketten '.$forschung['panzerketten'].'/'.$i.' ', '<font class="green">Panzerketten '.$forschung['panzerketten'].'/'.$i.' </font>', $content);
}

// generierte seite ausgeben
$content = tag2value('onload', $onload, $content);
echo $content.template('footer');
?>