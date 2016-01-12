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
include "functions.php";
include "raketen_preise.php";
include "einheiten_preise.php";
include "def_preise.php";

// check session
logincheck();

// cheat protection
$i=0;
if (!$_SESSION['rak'][6]){
	do {
		$i++;
		$_SESSION['rak'][$i] = md5(rand(100000,999999));
	} while ($i < 6);
}
$i=0;
do {
	$i++;
	if ($_POST['anz'.$i] < 0) { $_POST['anz'.$i] = 0; }
	if ($_POST['anz'.$i] > 9999) { $_POST['anz'.$i] = 0; }
	if ($_POST['anz'.$i] != number_format($_POST['anz'.$i],0,'','')) { $_POST['anz'.$i] = 0; }	
	if ($_POST['anz'.$i]){
		if ($_SESSION['rak'][$i] != $_POST['x'.$i]){ die('CHEATVERSUCH!'); }
	}
} while ($i < 6);
$i=0;

// get html head
$content = template('head');

// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);

$dbh = db_connect();

// neuesten timestamp holen
$select = "SELECT * FROM `fabrik` WHERE `omni` = ".($_SESSION[user][omni])." GROUP BY fertigstellung DESC;";
$result = mysql_query($select);
$row  = mysql_fetch_array($result);
if ($row[fertigstellung] > date(U)) { $date = $row[fertigstellung]; }
else { $date = date(U); }

$ressis = ressistand($_SESSION[user][omni]);
// wenn gewuenscht neue einheiten in auftrag geben
do {
	$count++;
	if ($_POST['anz'.$count]){ 
		if ((number_format($_POST['anz'.$count],0,'','')*$rak[$count][eisen])  > number_format($ressis[eisen],0,'',''))  { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Eisen um '.number_format($_POST['anz'.$count],0,'','').' '.$rak[$count][name].' zu bauen.</span> <br />'; $teuer = TRUE;}
		if ((number_format($_POST['anz'.$count],0,'','')*$rak[$count][titan])  > number_format($ressis[titan],0,'',''))  { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Titan um '.number_format($_POST['anz'.$count],0,'','').' '.$rak[$count][name].' zu bauen.</span> <br />'; $teuer = TRUE;}	
		if ((number_format($_POST['anz'.$count],0,'','')*$rak[$count][oel])    > number_format($ressis[oel],0,'',''))    { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Oel um '.number_format($_POST['anz'.$count],0,'','').' '.$rak[$count][name].' zu bauen.</span> <br />'; $teuer = TRUE;}	
		if ((number_format($_POST['anz'.$count],0,'','')*$rak[$count][uran])   > number_format($ressis[uran],0,'',''))   { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Uran um '.number_format($_POST['anz'.$count],0,'','').' '.$rak[$count][name].' zu bauen.</span> <br />'; $teuer = TRUE;}	
		if ((number_format($_POST['anz'.$count],0,'','')*$rak[$count][gold])   > number_format($ressis[gold],0,'',''))   { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Gold um '.number_format($_POST['anz'.$count],0,'','').' '.$rak[$count][name].' zu bauen.</span> <br />'; $teuer = TRUE;}	
		if ((number_format($_POST['anz'.$count],0,'','')*$rak[$count][chanje]) > number_format($ressis[chanje],0,'','')) { $zuteuer .= '<span style="font-size: 12px";>Du hast nicht genug Chanje um '.number_format($_POST['anz'.$count],0,'','').' '.$rak[$count][name].' zu bauen.</span> <br />'; $teuer = TRUE;}	
	
		if ($teuer == FALSE) { 
			$i=0;
			do {
				$i++;
				if ($i <= $_POST['anz'.$count]) {
					$date = $date + $rak[$count][dauer];
					$select = "INSERT INTO `fabrik` ( `id` , `omni`, `type` , `fertigstellung` ) VALUES ( '', '".$_SESSION[user][omni]."', '".($count+2000)."', '".($date)."' );";
					mysql_query($select);
					$insertid = mysql_insert_id($dbh);
					$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ( '', '2', '".$insertid."', '".$date."' );";
					mysql_query($select);
				}
				} while ($i < (number_format($_POST['anz'.$count],0,'','')));
			$ressis[eisen] = $ressis[eisen]-(number_format($_POST['anz'.$count],0,'','')*$rak[$count][eisen]);

			$ressis[titan] = $ressis[titan]-(number_format($_POST['anz'.$count],0,'','')*$rak[$count][titan]);
			$ressis[oel] = $ressis[oel]-(number_format($_POST['anz'.$count],0,'','')*$rak[$count][oel]);
			$ressis[uran] = $ressis[uran]-(number_format($_POST['anz'.$count],0,'','')*$rak[$count][uran]);
			$ressis[gold] = $ressis[gold]-(number_format($_POST['anz'.$count],0,'','')*$rak[$count][gold]);
			$ressis[chanje] = $ressis[chanje]-(number_format($_POST['anz'.$count],0,'','')*$rak[$count][chanje]);
			$select = "UPDATE `ressis` SET `eisen` = '".$ressis[eisen]."', `titan` = '".$ressis[titan]."', `oel` = '".$ressis[oel]."', `uran` = '".$ressis[uran]."', `gold` = '".$ressis[gold]."', `chanje` = '".$ressis[chanje]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			mysql_query($select);
		}
		$teuer=FALSE;
	}
} while ($count < 6);

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
$ressis = ressistand($_SESSION[user][omni]);
$content .= $ressis['html'];

$content .= '<br />';

$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION[user][omni]."';";
$selectResult   = mysql_query($select);
$gebaeude = mysql_fetch_array($selectResult);

$select = "SELECT * FROM `raketen` WHERE `omni` = '".$_SESSION[user][omni]."';";
$selectResult   = mysql_query($select);
$raksilo = mysql_fetch_array($selectResult);

// forschungen
$select = "SELECT * FROM `forschungen` WHERE `omni` = '".($_SESSION[user][omni])."';";
$result = mysql_query($select);
$forschung  = mysql_fetch_array($result);

$content .= $zuteuer;
$count = 0;
do {
	$count++;
	$type = 'einh'.$count;
	$used = $used+$raksilo[$type];
} while ( 6 >= $count );

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

  var e1eisen = '.$rak[1]['eisen'].';
  var e2eisen = '.$rak[2]['eisen'].';
  var e3eisen = '.$rak[3]['eisen'].';
  var e4eisen = '.$rak[4]['eisen'].';
  var e5eisen = '.$rak[5]['eisen'].';
  var e6eisen = '.$rak[6]['eisen'].';
  var e1titan = '.$rak[1]['titan'].';
  var e2titan = '.$rak[2]['titan'].';
  var e3titan = '.$rak[3]['titan'].';
  var e4titan = '.$rak[4]['titan'].';
  var e5titan = '.$rak[5]['titan'].';
  var e6titan = '.$rak[6]['titan'].';
  var e1oel = '.$rak[1]['oel'].';
  var e2oel = '.$rak[2]['oel'].';
  var e3oel = '.$rak[3]['oel'].';
  var e4oel = '.$rak[4]['oel'].';
  var e5oel = '.$rak[5]['oel'].';
  var e6oel = '.$rak[6]['oel'].';
  var e1uran = '.$rak[1]['uran'].';
  var e2uran = '.$rak[2]['uran'].';
  var e3uran = '.$rak[3]['uran'].';
  var e4uran = '.$rak[4]['uran'].';
  var e5uran = '.$rak[5]['uran'].';
  var e6uran = '.$rak[6]['uran'].';
  var e1gold = '.$rak[1]['gold'].';
  var e2gold = '.$rak[2]['gold'].';
  var e3gold = '.$rak[3]['gold'].';
  var e4gold = '.$rak[4]['gold'].';
  var e5gold = '.$rak[5]['gold'].';
  var e6gold = '.$rak[6]['gold'].';
  var e1chanje = '.$rak[1]['chanje'].';
  var e2chanje = '.$rak[2]['chanje'].';
  var e3chanje = '.$rak[3]['chanje'].';
  var e4chanje = '.$rak[4]['chanje'].';
  var e5chanje = '.$rak[5]['chanje'].';
  var e6chanje = '.$rak[6]['chanje'].';
  var e1dauer = '.$rak[1]['dauer'].';
  var e2dauer = '.$rak[2]['dauer'].';
  var e3dauer = '.$rak[3]['dauer'].';
  var e4dauer = '.$rak[4]['dauer'].';
  var e5dauer = '.$rak[5]['dauer'].';
  var e6dauer = '.$rak[6]['dauer'].';

  var anz1 = Math.abs(document.getElementsByName("anz1")[0].value);
  var anz2 = Math.abs(document.getElementsByName("anz2")[0].value);
  var anz3 = Math.abs(document.getElementsByName("anz3")[0].value);
  var anz4 = Math.abs(document.getElementsByName("anz4")[0].value);
  var anz5 = Math.abs(document.getElementsByName("anz5")[0].value);
  var anz6 = Math.abs(document.getElementsByName("anz6")[0].value);

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

  var dauer = d1+d2+d3+d4+d5+d6;
  var min = Math.floor(dauer/60);
  dauer = dauer - min * 60;
  var h = Math.floor(min/60);
  min = min - h * 60;

  dauer = Math.floor(dauer);

  if(min<10) { min="0"+min; }
  if(dauer<10) { dauer="0"+dauer; }

  var g_e = e1+e2+e3+e4+e5+e6;
  var g_t = t1+t2+t3+t4+t5+t6;  
  var g_o = o1+o2+o3+o4+o5+o6;
  var g_u = u1+u2+u3+u4+u5+u6;
  var g_g = g1+g2+g3+g4+g5+g6;
  var g_c = c1+c2+c3+c4+c5+c6;

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
			<b>Raketensilo:</b>
		</th>
	</tr>
	<tr>
		<td align="center">
<br /><b class="red">Es ist noch Platz f&uuml;r '.($gebaeude['raketensilo']*5-$used).' Raketen.</b><br /><br />';

$content .= '<form enctype="multipart/form-data" action="raketen.php?'. SID .'" method="post"><b>Bauen:</b><br /><table border="1" cellspacing="0" class="standard">
<tr align="center"><th style="width:155px">&nbsp;</th><th>Bestand</th><th style="width:50px">Eisen</th><th style="width:50px">Titan</th><th style="width:50px">Oel</th><th style="width:50px">Uran</th><th style="width:50px">Gold</th><th style="width:50px">Chanje</th><th style="width:50px">Dauer</th><th style="width:55px">&nbsp;</th></tr>';

if ($forschung['raketen'] >= 2 and $forschung['sprengstoff'] >= 2){$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=1&'.SID.'\',400)">'.$rak[1][name].'</a></td><td align="center">'.$raksilo[einh1].'</td><td align="right">'.$rak[1][eisen].'</td><td align="right">'.$rak[1][titan].'</td><td align="right">'.$rak[1][oel].'</td><td align="right">'.$rak[1][uran].'</td><td align="right">'.$rak[1][gold].'</td><td align="right">'.$rak[1][chanje].'</td><td align="right">'.time2str($rak[1][dauer]).'</td><td class="input"><center><input type="text" name="anz1" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x1" value="'.$_SESSION['rak'][1].'"></center></td></tr>';}
else {$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=1&'.SID.'\',400)">'.$rak[1][name].'</a></td><td align="center" colspan="9">Raketen '.$forschung['raketen'].'/2 Sprengstoff '.$forschung['sprengstoff'].'/2 <input type="hidden" name="anz1" value="0" /></td></tr>';}

if ($forschung['raketen'] >= 4 and $forschung['sprengstoff'] >= 5){$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=2&'.SID.'\',400)">'.$rak[2][name].'</a></td><td align="center">'.$raksilo[einh2].'</td><td align="right">'.$rak[2][eisen].'</td><td align="right">'.$rak[2][titan].'</td><td align="right">'.$rak[2][oel].'</td><td align="right">'.$rak[2][uran].'</td><td align="right">'.$rak[2][gold].'</td><td align="right">'.$rak[2][chanje].'</td><td align="right">'.time2str($rak[2][dauer]).'</td><td class="input"><center><input type="text" name="anz2" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x2" value="'.$_SESSION['rak'][2].'"></center></td></tr>';}
else {$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=2&'.SID.'\',400)">'.$rak[2][name].'</a></td><td align="center" colspan="9">Raketen '.$forschung['raketen'].'/4 Sprengstoff '.$forschung['sprengstoff'].'/5 <input type="hidden" name="anz2" value="0" /></td></tr>';}

if ($forschung['raketen'] >= 7 and $forschung['sprengstoff'] >= 7){$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=3&'.SID.'\',400)">'.$rak[3][name].'</a></td><td align="center">'.$raksilo[einh3].'</td><td align="right">'.$rak[3][eisen].'</td><td align="right">'.$rak[3][titan].'</td><td align="right">'.$rak[3][oel].'</td><td align="right">'.$rak[3][uran].'</td><td align="right">'.$rak[3][gold].'</td><td align="right">'.$rak[3][chanje].'</td><td align="right">'.time2str($rak[3][dauer]).'</td><td class="input"><center><input type="text" name="anz3" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x3" value="'.$_SESSION['rak'][3].'"></center></td></tr>';}
else {$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=3&'.SID.'\',400)">'.$rak[3][name].'</a></td><td align="center" colspan="9">Raketen '.$forschung['raketen'].'/7 Sprengstoff '.$forschung['sprengstoff'].'/7 <input type="hidden" name="anz3" value="0" /></td></tr>';}

if ($forschung['raketen'] >= 9 and $forschung['sprengstoff'] >= 8 and $forschung['reaktor'] >= 5){$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=4&'.SID.'\',400)">'.$rak[4][name].'</a></td><td align="center">'.$raksilo[einh4].'</td><td align="right">'.$rak[4][eisen].'</td><td align="right">'.$rak[4][titan].'</td><td align="right">'.$rak[4][oel].'</td><td align="right">'.$rak[4][uran].'</td><td align="right">'.$rak[4][gold].'</td><td align="right">'.$rak[4][chanje].'</td><td align="right">'.time2str($rak[4][dauer]).'</td><td class="input"><center><input type="text" name="anz4" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x4" value="'.$_SESSION['rak'][4].'"></center></td></tr>';}
else {$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=4&'.SID.'\',400)">'.$rak[4][name].'</a></td><td align="center" colspan="9">Raketen '.$forschung['raketen'].'/9 Sprengstoff '.$forschung['sprengstoff'].'/8 Reaktor '.$forschung['reaktor'].'/5 <input type="hidden" name="anz4" value="0" /></td></tr>';}

if ($forschung['raketen'] >= 15 and $forschung['sprengstoff'] >= 15 and $forschung['reaktor'] >= 10){$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=5&'.SID.'\',400)">'.$rak[5][name].'</a></td><td align="center">'.$raksilo[einh5].'</td><td align="right">'.$rak[5][eisen].'</td><td align="right">'.$rak[5][titan].'</td><td align="right">'.$rak[5][oel].'</td><td align="right">'.$rak[5][uran].'</td><td align="right">'.$rak[5][gold].'</td><td align="right">'.$rak[5][chanje].'</td><td align="right">'.time2str($rak[5][dauer]).'</td><td class="input"><center><input type="text" name="anz5" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x5" value="'.$_SESSION['rak'][5].'"></center></td></tr>';}
else {$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=5&'.SID.'\',400)">'.$rak[5][name].'</a></td><td align="center" colspan="9">Raketen '.$forschung['raketen'].'/15 Sprengstoff '.$forschung['sprengstoff'].'/15 Reaktor '.$forschung['reaktor'].'/10 <input type="hidden" name="anz5" value="0" /></td></tr>';}

if ($forschung['raketen'] >= 3 and $forschung['spionage'] >= 3){$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=6&'.SID.'\',400)">'.$rak[6][name].'</a></td><td align="center">'.$raksilo[einh6].'</td><td align="right">'.$rak[6][eisen].'</td><td align="right">'.$rak[6][titan].'</td><td align="right">'.$rak[6][oel].'</td><td align="right">'.$rak[6][uran].'</td><td align="right">'.$rak[6][gold].'</td><td align="right">'.$rak[6][chanje].'</td><td align="right">'.time2str($rak[6][dauer]).'</td><td class="input"><center><input type="text" name="anz6" value="" class="input" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" /><input type="hidden" name="x6" value="'.$_SESSION['rak'][6].'"></center></td></tr>';}
else {$content .= '<tr><td><a href="javascript:popUp(\'details_rak.php?id=6&'.SID.'\',400)">'.$rak[6][name].'</a></td><td align="center" colspan="9">Raketen '.$forschung[raketen].'/3 Spionage '.$forschung['spionage'].'/3 <input type="hidden" name="anz6" value="0" /></td></tr>';}

$content .= '</table><br />
<span style="font-size: 12px";><b>Kosten:</b></span><br /><table border="1" cellspacing="0" class="standard">
<tr align="center"><th style="width:55px">Eisen</th><th style="width:55px">Titan</th><th style="width:55px">Oel</th><th style="width:55px">Uran</th><th style="width:55px">Gold</th><th style="width:55px">Chanje</th><th style="width:55px">Dauer</th></tr>
<tr class="standard"><td align="right"><a id="k_eisen">0</a></td><td align="right"><a id="k_titan">0</a></td><td align="right"><a id="k_oel">0</a></td><td align="right"><a id="k_uran">0</a></td><td align="right"><a id="k_gold">0</a></td><td align="right"><a id="k_chanje">0</a></td><td align="right"><a id="k_dauer">0:00:00</a></td></tr>
</table><br />
<center><input type="submit" name="submit" value="bauen"></center>
</form>
<a href="beschuss.php?'.SID.'" class="red" alt="beschuss"><b><i>Raketenbeschuss starten.</i></b></a><br />
<br />
<b>Achtung:</b> Raketen, die die maximale Raketensilokapazit&auml;t &uuml;berschreiten <b>verfallen sofort</b>.<br /><br /></td></tr></table><br />';

$select = "SELECT * FROM `fabrik` WHERE `omni` = ".($_SESSION[user][omni])." GROUP BY fertigstellung ASC;";
$result = mysql_query($select);

$row  = mysql_fetch_array($result);
if ($row) {
	$content .= '<br /><table border="1" cellspacing="0" class="standard" style="width:250px">
	<tr align="center" class="standard"><th colspan="2">Aktuelle Auftr&auml;ge:</th></tr><tr>';
	$start = $row[fertigstellung];
	do {
		$i++;
		if ($i < 4) {
			if ($row[type] >= 1 and $row[type] <= 15) { $content .= '<tr align="left"><td>'.$einh[$row[type]][name].'</td><td style="width:55px"><center>'.countdown($row[fertigstellung]-date(U)).'</center></td></tr>'; $stop=$row[fertigstellung]; }
			if ($row[type] >= 1001 and $row[type] <= 1011) { $row[type] -= 1000; $content .= '<tr align="left"><td>'.$def[$row[type]][name].'</td><td style="width:55px"><center>'.countdown($row[fertigstellung]-date(U)).'</center></td></tr>'; $stop=$row[fertigstellung]; }
			if ($row[type] >= 2001 and $row[type] <= 2006) { $row[type] -= 2000; $content .= '<tr align="left"><td>'.$rak[$row[type]][name].'</td><td style="width:55px"><center>'.countdown($row[fertigstellung]-date(U)).'</center></td></tr>'; $stop=$row[fertigstellung]; }
		} else {
			if ($row[type] >= 1 and $row[type] <= 15) { $content .= '<tr align="left"><td>'.$einh[$row[type]][name].'</td><td style="width:55px"><center>'.time2str($row[fertigstellung]-date(U)).'</center></td></tr>'; $stop=$row[fertigstellung]; }
			if ($row[type] >= 1001 and $row[type] <= 1011) { $row[type] -= 1000; $content .= '<tr align="left"><td>'.$def[$row[type]][name].'</td><td style="width:55px"><center>'.time2str($row[fertigstellung]-date(U)).'</center></td></tr>'; $stop=$row[fertigstellung]; }
			if ($row[type] >= 2001 and $row[type] <= 2006) { $row[type] -= 2000; $content .= '<tr align="left"><td>'.$rak[$row[type]][name].'</td><td style="width:55px"><center>'.time2str($row[fertigstellung]-date(U)).'</center></td></tr>'; $stop=$row[fertigstellung]; }		
		}
		$row  = mysql_fetch_array($result);
	} while ($row);
	$content .= '</table>';
}

// forschungen
for ($i=$forschung['feuerwaffen']; $i > 0; $i--) {
	$content = str_replace('Feuerwaffen '.$forschung['feuerwaffen'].'/'.$i.' ', '<font color="green">Feuerwaffen '.$forschung['feuerwaffen'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['sprengstoff']; $i > 0; $i--) {
	$content = str_replace('Sprengstoff '.$forschung['sprengstoff'].'/'.$i.' ', '<font color="green">Sprengstoff '.$forschung['sprengstoff'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['raketen']; $i > 0; $i--) {
	$content = str_replace('Raketen '.$forschung['raketen'].'/'.$i.' ', '<font color="green">Raketen '.$forschung['raketen'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['fuehrung'];  $i > 0; $i--) {
	$content = str_replace('F&uuml;hrung '.$forschung['fuehrung'].'/'.$i.' ', '<font color="green">F&uuml;hrung '.$forschung['fuehrung'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['rad']; $i > 0; $i--) {
	$content = str_replace('Radverst&auml;rkungen '.$forschung['rad'].'/'.$i.' ', '<font color="green">Radverst&auml;rkungen '.$forschung['rad'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['panzerung']; $i > 0; $i--) {
	$content = str_replace('Panzerung '.$forschung['fuehrung'].'/'.$i.' ', '<font color="green">Panzerung '.$forschung['fuehrung'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['motor']; $i > 0; $i--) {
	$content = str_replace('Motor '.$forschung['motor'].'/'.$i.' ', '<font color="green">Motor '.$forschung['motor'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['panzerketten']; $i > 0; $i--) {
	$content = str_replace('Panzerketten '.$forschung['panzerketten'].'/'.$i.' ', '<font color="green">Panzerketten '.$forschung['panzerketten'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['minen']; $i > 0; $i--) {
	$content = str_replace('Minentechnik '.$forschung['minen'].'/'.$i.' ', '<font color="green">Minentechnik '.$forschung['minen'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['reaktor']; $i > 0; $i--) {
	$content = str_replace('Reaktor '.$forschung['reaktor'].'/'.$i.' ', '<font color="green">Reaktor '.$forschung['reaktor'].'/'.$i.' </font>', $content);
}
for ($i=$forschung['spionage']; $i > 0; $i--) {
	$content = str_replace('Spionage '.$forschung['spionage'].'/'.$i.' ', '<font color="green">Spionage '.$forschung['spionage'].'/'.$i.' </font>', $content);
}

// generierte seite ausgeben
$content = tag2value("onload", $onload, $content);
echo $content.template('footer');
?>