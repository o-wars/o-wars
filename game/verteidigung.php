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

$exec_start = microtime();

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";
include "def_preise.php";
include "einheiten_preise.php";
include "raketen_preise.php";

// check session
logincheck();

// mit datenbank verbinden
$dbh = db_connect();

// cheat protection
$i=0;
if (!$_SESSION['d'][10]){
	do {
		$i++;
		$_SESSION['d'][$i] = md5(rand(100000,999999).$i.rand(100000,999999));
	} while ($i < 10);
}
$i=0;
do {
	$i++;
	if ($_POST['anz'.$i] < 0) { $_POST['anz'.$i] = 0; }
	if ($_POST['anz'.$i] > 9999) { $_POST['anz'.$i] = 0; }
	if ($_POST['anz'.$i] != number_format($_POST['anz'.$i],0,'','')) { $_POST['anz'.$i] = 0; }	
	if ($_POST['anz'.$i]){
		if ($_SESSION['d'][$i] != $_POST['x'.$i]){ die('CHEATVERSUCH!'); }
	}
} while ($i < 10);
$i=0;

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

// checken ob einheiten oder def fertig sind und dann hangar setzen
$hangar = new_units_check($_SESSION[user][omni]);

// bestehende def anlagen
$select2 = "SELECT * FROM `defense` WHERE `omni` = ".($_SESSION[user][omni]).";";
$result = mysql_query($select2);
$defense  = mysql_fetch_array($result);

if ($_GET['destroy']){
	if ($defense['def'.$_GET['destroy']] > 0) {
		$defense['def'.$_GET['destroy']]--;
		$select = "UPDATE `defense` SET `def1` = '".$defense['def1']."', `def2` = '".$defense['def2']."', `def3` = '".$defense['def3']."', `def4` = '".$defense['def4']."', `def5` = '".$defense['def5']."', `def6` = '".$defense['def6']."', `def7` = '".$defense['def7']."', `def8` = '".$defense['def8']."', `def9` = '".$defense['def9']."', `def10` = '".$defense['def10']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
		mysql_query($select);
 	}
}

// neue nachrichten
//$content .= neue_nachrichten();

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION[user][omni]);

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
if ($row[fertigstellung] > date(U)) { $date = $row[fertigstellung]; }
else { $date = date(U); }

// wenn gewuenscht neue def in auftrag geben
do {
	$j++;
if ($_POST['anz'.$j]){ 
	if ((number_format($_POST['anz'.$j],0,'','')*$def[$j][eisen])  > number_format($ressis[eisen],0,'',''))  { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Eisen um '.number_format($_POST['anz'.$j],0,'','').' '.$def[$j][name].' zu bauen.</span> <br />'; $teuer = TRUE;}
	if ((number_format($_POST['anz'.$j],0,'','')*$def[$j][titan])  > number_format($ressis[titan],0,'',''))  { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Titan um '.number_format($_POST['anz'.$j],0,'','').' '.$def[$j][name].' zu bauen.</span> <br />'; $teuer = TRUE;}	
	if ((number_format($_POST['anz'.$j],0,'','')*$def[$j][oel])    > number_format($ressis[oel],0,'',''))    { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Oel um '.number_format($_POST['anz'.$j],0,'','').' '.$def[$j][name].' zu bauen.</span> <br />'; $teuer = TRUE;}	
	if ((number_format($_POST['anz'.$j],0,'','')*$def[$j][uran])   > number_format($ressis[uran],0,'',''))   { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Uran um '.number_format($_POST['anz'.$j],0,'','').' '.$def[$j][name].' zu bauen.</span> <br />'; $teuer = TRUE;}	
	if ((number_format($_POST['anz'.$j],0,'','')*$def[$j][gold])   > number_format($ressis[gold],0,'',''))   { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Gold um '.number_format($_POST['anz'.$j],0,'','').' '.$def[$j][name].' zu bauen.</span> <br />'; $teuer = TRUE;}	
	if ((number_format($_POST['anz'.$j],0,'','')*$def[$j][chanje]) > number_format($ressis[chanje],0,'','')) { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Chanje um '.number_format($_POST['anz'.$j],0,'','').' '.$def[$j][name].' zu bauen.</span> <br />'; $teuer = TRUE;}	
	
	if ($teuer == FALSE) { 
		$i=0;
		do {
			$i++;
			if ($i <= $_POST['anz'.$j]) {
				$date = $date + $def[$j][dauer];
				$select = "INSERT INTO `fabrik` ( `id` , `omni`, `type` , `fertigstellung` ) VALUES ( '', '".$_SESSION[user][omni]."', '".($j+1000)."', '".($date)."' );";
				mysql_query($select);
				$insertid = mysql_insert_id($dbh);
				$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ( '', '2', '".$insertid."', '".$date."' );";
				mysql_query($select);
			}
		} while ($i < (number_format($_POST['anz'.$j],0,'','')));
		$ressis[eisen] = $ressis[eisen]-(number_format($_POST['anz'.$j],0,'','')*$def[$j][eisen]);
		$ressis[titan] = $ressis[titan]-(number_format($_POST['anz'.$j],0,'','')*$def[$j][titan]);
		$ressis[oel] = $ressis[oel]-(number_format($_POST['anz'.$j],0,'','')*$def[$j][oel]);
		$ressis[uran] = $ressis[uran]-(number_format($_POST['anz'.$j],0,'','')*$def[$j][uran]);
		$ressis[gold] = $ressis[gold]-(number_format($_POST['anz'.$j],0,'','')*$def[$j][gold]);
		$ressis[chanje] = $ressis[chanje]-(number_format($_POST['anz'.$j],0,'','')*$def[$j][chanje]);
		$select = "UPDATE `ressis` SET `eisen` = '".$ressis[eisen]."', `titan` = '".$ressis[titan]."', `oel` = '".$ressis[oel]."', `uran` = '".$ressis[uran]."', `gold` = '".$ressis[gold]."', `chanje` = '".$ressis[chanje]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
		mysql_query($select);
	}
	$teuer=FALSE;
}
} while ($j < 15);

$ressis = ressistand($_SESSION[user][omni]);

$content .= $ressis['html'];

if ($zuteuer) { $content .= '<br /><b>'.$zuteuer.'</b>'; }

$content .= '<br />';

do {
	$count++;
	$type = 'def'.$count;
	if ($count <= 4) {$used = $used+$defense[$type]*2;}
	else {$used = $used+$defense[$type];}
} while ( 10 > $count );

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

  var e1eisen = '.$def[1]['eisen'].';
  var e2eisen = '.$def[2]['eisen'].';
  var e3eisen = '.$def[3]['eisen'].';
  var e4eisen = '.$def[4]['eisen'].';
  var e5eisen = '.$def[5]['eisen'].';
  var e6eisen = '.$def[6]['eisen'].';
  var e7eisen = '.$def[7]['eisen'].';
  var e8eisen = '.$def[8]['eisen'].';
  var e9eisen = '.$def[9]['eisen'].';
  var e10eisen = '.$def[10]['eisen'].';
  var e1titan = '.$def[1]['titan'].';
  var e2titan = '.$def[2]['titan'].';
  var e3titan = '.$def[3]['titan'].';
  var e4titan = '.$def[4]['titan'].';
  var e5titan = '.$def[5]['titan'].';
  var e6titan = '.$def[6]['titan'].';
  var e7titan = '.$def[7]['titan'].';
  var e8titan = '.$def[8]['titan'].';
  var e9titan = '.$def[9]['titan'].';
  var e10titan = '.$def[10]['titan'].';
  var e1oel = '.$def[1]['oel'].';
  var e2oel = '.$def[2]['oel'].';
  var e3oel = '.$def[3]['oel'].';
  var e4oel = '.$def[4]['oel'].';
  var e5oel = '.$def[5]['oel'].';
  var e6oel = '.$def[6]['oel'].';
  var e7oel = '.$def[7]['oel'].';
  var e8oel = '.$def[8]['oel'].';
  var e9oel = '.$def[9]['oel'].';
  var e10oel = '.$def[10]['oel'].';
  var e1uran = '.$def[1]['uran'].';
  var e2uran = '.$def[2]['uran'].';
  var e3uran = '.$def[3]['uran'].';
  var e4uran = '.$def[4]['uran'].';
  var e5uran = '.$def[5]['uran'].';
  var e6uran = '.$def[6]['uran'].';
  var e7uran = '.$def[7]['uran'].';
  var e8uran = '.$def[8]['uran'].';
  var e9uran = '.$def[9]['uran'].';
  var e10uran = '.$def[10]['uran'].';
  var e1gold = '.$def[1]['gold'].';
  var e2gold = '.$def[2]['gold'].';
  var e3gold = '.$def[3]['gold'].';
  var e4gold = '.$def[4]['gold'].';
  var e5gold = '.$def[5]['gold'].';
  var e6gold = '.$def[6]['gold'].';
  var e7gold = '.$def[7]['gold'].';
  var e8gold = '.$def[8]['gold'].';
  var e9gold = '.$def[9]['gold'].';
  var e10gold = '.$def[10]['gold'].';
  var e1chanje = '.$def[1]['chanje'].';
  var e2chanje = '.$def[2]['chanje'].';
  var e3chanje = '.$def[3]['chanje'].';
  var e4chanje = '.$def[4]['chanje'].';
  var e5chanje = '.$def[5]['chanje'].';
  var e6chanje = '.$def[6]['chanje'].';
  var e7chanje = '.$def[7]['chanje'].';
  var e8chanje = '.$def[8]['chanje'].';
  var e9chanje = '.$def[9]['chanje'].';
  var e10chanje = '.$def[10]['chanje'].';
  var e1dauer = '.$def[1]['dauer'].';
  var e2dauer = '.$def[2]['dauer'].';
  var e3dauer = '.$def[3]['dauer'].';
  var e4dauer = '.$def[4]['dauer'].';
  var e5dauer = '.$def[5]['dauer'].';
  var e6dauer = '.$def[6]['dauer'].';
  var e7dauer = '.$def[7]['dauer'].';
  var e8dauer = '.$def[8]['dauer'].';
  var e9dauer = '.$def[9]['dauer'].';
  var e10dauer = '.$def[10]['dauer'].';

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

  var dauer = d1+d2+d3+d4+d5+d6+d7+d8+d9+d10;
  var min = Math.floor(dauer/60);
  dauer = dauer - min * 60;
  var h = Math.floor(min/60);
  min = min - h * 60;

  dauer = Math.floor(dauer);

  if(min<10) { min="0"+min; }
  if(dauer<10) { dauer="0"+dauer; }

  var g_e = e1+e2+e3+e4+e5+e6+e7+e8+e9+e10;
  var g_t = t1+t2+t3+t4+t5+t6+t7+t8+t9+t10;  
  var g_o = o1+o2+o3+o4+o5+o6+o7+o8+o9+o10;
  var g_u = u1+u2+u3+u4+u5+u6+u7+u8+u9+u10;
  var g_g = g1+g2+g3+g4+g5+g6+g7+g8+g9+g10;
  var g_c = c1+c2+c3+c4+c5+c6+c7+c8+c9+c10;

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
</script>';

$content .= '<br />	
<table border="1" cellspacing="0" class="sub" style="width:720px">
	<tr>
		<th>
			<b>Verteidigungsanlagen:</b>
		</th>
	</tr>
	<tr>
		<td align="center">
<br /><b class="red">Es sind noch '.(($gebaeude[basis]*4)-$used).' Felder verf&uuml;gbar f&uuml;r Verteidigungsanlagen (jeweils 1 Feld) oder Minen (jeweils 2 Felder).</b><br /><br />';

$content .= '<form enctype="multipart/form-data" action="verteidigung.php?'. SID .'" method="post"><span style="font-size: 12px";><b>Minen:</b></span><br /><table border="1" cellspacing="0">
<tr align="center"><th style="width:130px">&nbsp;</th><th>Bestand</th><th style="width:55px">Eisen</th><th style="width:55px">Titan</th><th style="width:55px">Oel</th><th style="width:55px">Uran</th><th style="width:55px">Gold</th><th style="width:55px">Chanje</th><th style="width:55px">Dauer</th><th style="width:55px">&nbsp;</th></tr>';

if ($forschung[minen] >= 1 and $forschung[sprengstoff] >= 1 and $gebaeude[fabrik] >= 1){$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=1&'.SID.'\',400)">'.$def[1][name].'</a></td><td align="center">'.$defense[def1].'</td><td align="right">'.$def[1][eisen].'</td><td align="right">'.$def[1][titan].'</td><td align="right">'.$def[1][oel].'</td><td align="right">'.$def[1][uran].'</td><td align="right">'.$def[1][gold].'</td><td align="right">'.$def[1][chanje].'</td><td align="right">'.time2str($def[1][dauer]).'</td><td class="input"><center><input type="text" name="anz1" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x1" value="'.$_SESSION['d'][1].'"></center></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=1&'.SID.'\',400)">'.$def[1][name].'</a></td><td align="center" colspan="9">Fabrik '.$gebaeude[fabrik].'/1 Minentechnik '.$forschung[minen].'/1 Sprengstoff '.$forschung[sprengstoff].'/1 <input type="hidden" name="anz1" value="0" /></td></tr>';}

if ($forschung[minen] >= 3 and $gebaeude[fabrik] >= 1 and $forschung[sprengstoff] >= 2){$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=2&'.SID.'\',400)">'.$def[2][name].'</a></td><td align="center">'.$defense[def2].'</td><td align="right">'.$def[2][eisen].'</td><td align="right">'.$def[2][titan].'</td><td align="right">'.$def[2][oel].'</td><td align="right">'.$def[2][uran].'</td><td align="right">'.$def[2][gold].'</td><td align="right">'.$def[2][chanje].'</td><td align="right">'.time2str($def[2][dauer]).'</td><td class="input"><center><input type="text" name="anz2" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x2" value="'.$_SESSION['d'][2].'"></center></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=2&'.SID.'\',400)">'.$def[2][name].'</a></td><td align="center" colspan="9">Fabrik '.$gebaeude[fabrik].'/1 Minentechnik '.$forschung[minen].'/3 Sprengstoff '.$forschung[sprengstoff].'/2 <input type="hidden" name="anz2" value="0" /></td></tr>';}

if ($gebaeude[fabrik] >= 5 and $forschung[minen] >= 5 and $forschung[sprengstoff] >= 4){$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=3&'.SID.'\',400)">'.$def[3][name].'</a></td><td align="center">'.$defense[def3].'</td><td align="right">'.$def[3][eisen].'</td><td align="right">'.$def[3][titan].'</td><td align="right">'.$def[3][oel].'</td><td align="right">'.$def[3][uran].'</td><td align="right">'.$def[3][gold].'</td><td align="right">'.$def[3][chanje].'</td><td align="right">'.time2str($def[3][dauer]).'</td><td class="input"><center><input type="text" name="anz3" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x3" value="'.$_SESSION['d'][3].'"></center></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=3&'.SID.'\',400)">'.$def[3][name].'</a></td><td align="center" colspan="9">Fabrik '.$gebaeude[fabrik].'/5 Minentechnik '.$forschung[minen].'/5 Sprengstoff '.$forschung[sprengstoff].'/4 <input type="hidden" name="anz3" value="0" /></td></tr>';}

if ($gebaeude[fabrik] >= 7 and $forschung[minen] >= 7 and $forschung[sprengstoff] >= 6){$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=4&'.SID.'\',400)">'.$def[4][name].'</a></td><td align="center">'.$defense[def4].'</td><td align="right">'.$def[4][eisen].'</td><td align="right">'.$def[4][titan].'</td><td align="right">'.$def[4][oel].'</td><td align="right">'.$def[4][uran].'</td><td align="right">'.$def[4][gold].'</td><td align="right">'.$def[4][chanje].'</td><td align="right">'.time2str($def[4][dauer]).'</td><td class="input"><center><input type="text" name="anz4" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x4" value="'.$_SESSION['d'][4].'"></center></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=4&'.SID.'\',400)">'.$def[4][name].'</a></td><td align="center" colspan="9">Fabrik '.$gebaeude[fabrik].'/7 Minentechnik '.$forschung[minen].'/7 Sprengstoff '.$forschung[sprengstoff].'/6 <input type="hidden" name="anz4" value="0" /></td></tr>';}
$content .= '</table>';

$content .= '<br /><b>Verteidigung:</b><br /><table border="1" cellspacing="0">
<tr align="center"><th style="width:130px">&nbsp;</th><th>Bestand</th><th style="width:55px">Eisen</th><th style="width:55px">Titan</th><th style="width:55px">Oel</th><th style="width:55px">Uran</th><th style="width:55px">Gold</th><th style="width:55px">Chanje</th><th style="width:55px">Dauer</th><th style="width:55px">&nbsp;</th></tr>';

if ($gebaeude[fabrik] >= 2 and $forschung[panzerung] >= 3) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=9&'.SID.'\',400)">'.$def[9][name].'</a></td><td align="center">'.$defense[def9].'</td><td align="right">'.$def[9][eisen].'</a></td><td align="right">'.$def[9][titan].'</td><td align="right">'.$def[9][oel].'</td><td align="right">'.$def[9][uran].'</td><td align="right">'.$def[9][gold].'</td><td align="right">'.$def[9][chanje].'</td><td align="right">'.time2str($def[9][dauer]).'</td><td class="input"><center><input type="text" name="anz9" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x9" value="'.$_SESSION['d'][9].'"></center></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=9&'.SID.'\',400)">'.$def[9][name].'</a></td><td align="center" colspan="9">Fabrik '.$gebaeude[fabrik].'/2 Panzerung '.$forschung[panzerung].'/3<input type="hidden" name="anz9" value="0" /></td></tr>';}

if ($gebaeude[fabrik] >= 3 and $forschung[feuerwaffen] >= 4 and $forschung[panzerung] >= 1) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=5&'.SID.'\',400)">'.$def[5][name].'</a></td><td align="center">'.$defense[def5].'</td><td align="right">'.$def[5][eisen].'</td><td align="right">'.$def[5][titan].'</td><td align="right">'.$def[5][oel].'</td><td align="right">'.$def[5][uran].'</td><td align="right">'.$def[5][gold].'</td><td align="right">'.$def[5][chanje].'</td><td align="right">'.time2str($def[5][dauer]).'</td><td class="input"><center><input type="text" name="anz5" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x5" value="'.$_SESSION['d'][5].'"></center></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=5&'.SID.'\',400)">'.$def[5][name].'</a></td><td align="center" colspan="9">Fabrik '.$gebaeude[fabrik].'/3 Feuerwaffen '.$forschung[feuerwaffen].'/4 Panzerung '.$forschung[panzerung].'/2 Motor '.$forschung[motor].'/1 <input type="hidden" name="anz5" value="0" /></td></tr>';}

if ($gebaeude[fabrik] >= 4 and $forschung[feuerwaffen] >= 6 and $forschung[panzerung] >= 3 and $forschung[sprengstoff] >= 3) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=6&'.SID.'\',400)">'.$def[6][name].'</a></td><td align="center">'.$defense[def6].'</td><td align="right">'.$def[6][eisen].'</td><td align="right">'.$def[6][titan].'</td><td align="right">'.$def[6][oel].'</td><td align="right">'.$def[6][uran].'</td><td align="right">'.$def[6][gold].'</td><td align="right">'.$def[6][chanje].'</td><td align="right">'.time2str($def[6][dauer]).'</td><td class="input"><center><input type="text" name="anz6" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x6" value="'.$_SESSION['d'][6].'"></center></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=6&'.SID.'\',400)">'.$def[6][name].'</a></td><td align="center" colspan="9">Fabrik '.$gebaeude[fabrik].'/4 Feuerwaffen '.$forschung[feuerwaffen].'/6 Sprengstoff '.$forschung[sprengstoff].'/3 Panzerung '.$forschung[panzerung].'/4 <input type="hidden" name="anz6" value="0" /></td></tr>';}

if ($gebaeude[fabrik] >= 6 and $forschung[feuerwaffen] >= 7 and $forschung[panzerung] >= 4 and $forschung[sprengstoff] >= 4) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=7&'.SID.'\',400)">'.$def[7][name].'</a></td><td align="center">'.$defense[def7].'</td><td align="right">'.$def[7][eisen].'</td><td align="right">'.$def[7][titan].'</td><td align="right">'.$def[7][oel].'</td><td align="right">'.$def[7][uran].'</td><td align="right">'.$def[7][gold].'</td><td align="right">'.$def[7][chanje].'</td><td align="right">'.time2str($def[7][dauer]).'</td><td class="input"><center><input type="text" name="anz7" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x7" value="'.$_SESSION['d'][7].'"></center></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=7&'.SID.'\',400)">'.$def[7][name].'</a></td><td align="center" colspan="9">Fabrik '.$gebaeude[fabrik].'/6 Feuerwaffen '.$forschung[feuerwaffen].'/7 Sprengstoff '.$forschung[sprengstoff].'/4 Panzerung '.$forschung[panzerung].'/4 <input type="hidden" name="anz7" value="0" /></td></tr>';}

if ($gebaeude[fabrik] >= 8 and $forschung[feuerwaffen] >= 9 and $forschung[panzerung] >= 8 and $forschung[sprengstoff] >= 5) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=8&'.SID.'\',400)">'.$def[8][name].'</a></td><td align="center">'.$defense[def8].'</td><td align="right">'.$def[8][eisen].'</td><td align="right">'.$def[8][titan].'</td><td align="right">'.$def[8][oel].'</td><td align="right">'.$def[8][uran].'</td><td align="right">'.$def[8][gold].'</td><td align="right">'.$def[8][chanje].'</td><td align="right">'.time2str($def[8][dauer]).'</td><td class="input"><center><input type="text" name="anz8" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x8" value="'.$_SESSION['d'][8].'"></center></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=8&'.SID.'\',400)">'.$def[8][name].'</a></td><td align="center" colspan="9">Fabrik '.$gebaeude[fabrik].'/8 Feuerwaffen '.$forschung[feuerwaffen].'/9 Sprengstoff '.$forschung[sprengstoff].'/5 Panzerung '.$forschung[panzerung].'/8 <input type="hidden" name="anz8" value="0" /></td></tr>';}

if ($gebaeude[fabrik] >= 12 and $forschung[panzerung] >= 10 and $forschung[feuerwaffen] >= 15) {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=10&'.SID.'\',400)">'.$def[10][name].'</a></td><td align="center">'.$defense[def10].'</td><td align="right">'.$def[10][eisen].'</a></td><td align="right">'.$def[10][titan].'</td><td align="right">'.$def[10][oel].'</td><td align="right">'.$def[10][uran].'</td><td align="right">'.$def[10][gold].'</td><td align="right">'.$def[10][chanje].'</td><td align="right">'.time2str($def[10][dauer]).'</td><td class="input"><center><input type="text" name="anz10" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x10" value="'.$_SESSION['d'][10].'"></center></td></tr>';}
else {$content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_def.php?id=10&'.SID.'\',400)">'.$def[10][name].'</a></td><td align="center" colspan="9">Fabrik '.$gebaeude[fabrik].'/12 Panzerung '.$forschung[panzerung].'/10 Feuerwaffen '.$forschung[feuerwaffen].'/15 <input type="hidden" name="anz10" value="0" /></td></tr>';}

$content .= '</table>
<br /><b>Kosten:</b><br /><table border="1" cellspacing="0" class="standard">
<tr align="center"><th style="width:55px">Eisen</th><th style="width:55px">Titan</th><th style="width:55px">Oel</th><th style="width:55px">Uran</th><th style="width:55px">Gold</th><th style="width:55px">Chanje</th><th style="width:55px">Dauer</th></tr>
<tr class="standard"><td align="right"><a id="k_eisen">0</a></td><td align="right"><a id="k_titan">0</a></td><td align="right"><a id="k_oel">0</a></td><td align="right"><a id="k_uran">0</a></td><td align="right"><a id="k_gold">0</a></td><td align="right"><a id="k_chanje">0</a></td><td align="right"><a id="k_dauer">0:00:00</a></td></tr>
</table><br />
<center><input type="submit" name="submit" value="bauen"></center>
</form>
<br />
<span style="font-size: 12px";><b>Achtung:</b> Verteidigungsanlagen und Minen, die das aktuelle Basislevel &uuml;berschreiten <b>verfallen sofort</b>.</span><br /><br />
</td></tr></table>';

unset($i);
do {
	$i++;
	if ($defense['def'.$i]){ $content .= '<a href="verteidigung.php?destroy='.$i.'&amp;'.SID.'"><font color="#b90101"><b><i>Eine(/n) '.$def[$i][name].' vernichten. ('.$defense['def'.$i].' vorhanden)</i></b></font></a><br />'; }
} while ($i < 10);

$select = "SELECT * FROM `fabrik` WHERE `omni` = ".($_SESSION[user][omni])." GROUP BY fertigstellung ASC;";
$result = mysql_query($select);

$row  = mysql_fetch_array($result);
if ($row) {
	$content .= '<br /><table border="1" cellspacing="0" class="standard" style="width:250px">
	<tr align="center" class="standard"><th colspan="2">Aktuelle Auftr&auml;ge:</th></tr><tr>';
	$start = $row[fertigstellung];
	$i=0;
	do {
		$i++;
		if ($i < 4) {
			if ($row[type] >= 1 and $row[type] <= 15) { $content .= '<tr align="left"><td>'.$einh[$row[type]][name].'</td><td style="width:100px"><center>'.countdown($row[fertigstellung]-date(U)).'</center></td></tr>'; $stop=$row[fertigstellung]; }
			if ($row[type] >= 1001 and $row[type] <= 1011) { $row[type] -= 1000; $content .= '<tr align="left"><td>'.$def[$row[type]][name].'</td><td style="width:100px"><center>'.countdown($row[fertigstellung]-date(U)).'</center></td></tr>'; $stop=$row[fertigstellung]; }
			if ($row[type] >= 2001 and $row[type] <= 2006) { $row[type] -= 2000; $content .= '<tr align="left"><td>'.$rak[$row[type]][name].'</td><td style="width:100px"><center>'.countdown($row[fertigstellung]-date(U)).'</center></td></tr>'; $stop=$row[fertigstellung]; }
		} else {
			if ($row[type] >= 1 and $row[type] <= 15) { $content .= '<tr align="left"><td>'.$einh[$row[type]][name].'</td><td style="width:100px"><center>'.time2str($row[fertigstellung]-date(U)).'</center></td></tr>'; $stop=$row[fertigstellung]; }
			if ($row[type] >= 1001 and $row[type] <= 1011) { $row[type] -= 1000; $content .= '<tr align="left"><td>'.$def[$row[type]][name].'</td><td style="width:100px"><center>'.time2str($row[fertigstellung]-date(U)).'</center></td></tr>'; $stop=$row[fertigstellung]; }
			if ($row[type] >= 2001 and $row[type] <= 2006) { $row[type] -= 2000; $content .= '<tr align="left"><td>'.$rak[$row[type]][name].'</td><td style="width:100px"><center>'.time2str($row[fertigstellung]-date(U)).'</center></td></tr>'; $stop=$row[fertigstellung]; }		
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
for ($i=$forschung['minen']; $i > 0; $i--) {
	$content = str_replace('Minentechnik '.$forschung['minen'].'/'.$i.' ', '<font class="green">Minentechnik '.$forschung['minen'].'/'.$i.' </font>', $content);
}

// generierte seite ausgeben
$content = tag2value('onload', $onload, $content);
echo $content.template('footer');
?>