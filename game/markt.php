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
include "einheiten_preise.php";
// check session
logincheck();

// html head setzen
$content = template('head');

// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);
$content .= $status;
unset($status);
$hangar = new_units_check($_SESSION['user']['omni']);

$dbh = db_connect();
$ressis = ressistand($_SESSION['user']['omni']);

if ($_POST['ek_eisen'] < 0){$_POST['ek_eisen'] = 0; }
if ($_POST['vk_eisen'] < 0){$_POST['vk_eisen'] = 0; }
if ($_POST['ek_titan'] < 0){$_POST['ek_titan'] = 0; }
if ($_POST['vk_titan'] < 0){$_POST['vk_titan'] = 0; }
if ($_POST['ek_oel']  < 0){$_POST['ek_oel'] = 0; }
if ($_POST['vk_oel']  < 0){$_POST['vk_oel'] = 0; }
if ($_POST['ek_uran'] < 0){$_POST['ek_uran'] = 0; }
if ($_POST['vk_uran'] < 0){$_POST['vk_uran'] = 0; }

// ressourcen ver/kaufen
$result = mysql_query("SELECT * FROM `marktpreise` ORDER BY `time` DESC LIMIT 1;");
$row = mysql_fetch_array($result);

$ek['eisen'] = $row['ek_eisen'];
$vk['eisen'] = $row['vk_eisen'];
$ek['titan'] = $row['ek_titan'];
$vk['titan'] = $row['vk_titan'];
$ek['oel']   = $row['ek_oel'];
$vk['oel']   = $row['vk_oel'];
$ek['uran']  = $row['ek_uran'];
$vk['uran']  = $row['vk_uran'];
$update = $row['time']+6*3600;

$select = "SELECT * FROM `missionen` WHERE `ziel` = '".$_SESSION['user']['omni']."' AND `type` = '1' AND `ankunft` > '".date(U)."' AND `parsed` != '1' GROUP BY `ankunft` ASC;";
$result = mysql_query($select);

if (mysql_num_rows($result) > 0){ $attack = 1; }

if ($_POST['ressis'] and $attack != 1){ 
	$_POST['ek_eisen'] = number_format($_POST['ek_eisen'],0,'','');
	$_POST['vk_eisen'] = number_format($_POST['vk_eisen'],0,'','');
	$_POST['ek_titan'] = number_format($_POST['ek_titan'],0,'','');
	$_POST['vk_titan'] = number_format($_POST['vk_titan'],0,'','');
	$_POST['ek_oel'] = number_format($_POST['ek_oel'],0,'','');
	$_POST['vk_oel'] = number_format($_POST['vk_oel'],0,'','');
	$_POST['ek_uran'] = number_format($_POST['ek_uran'],0,'','');
	$_POST['vk_uran'] = number_format($_POST['vk_uran'],0,'','');
	
	
	$kaufen += $_POST['ek_eisen'] * $ek['eisen']/ 100;
	$kaufen += $_POST['ek_titan'] * $ek['titan']/ 100;
	$kaufen += $_POST['ek_oel'] * $ek['oel']/ 100;
	$kaufen += $_POST['ek_uran'] * $ek['uran']/ 100;
	
	$verkaufen += $_POST['vk_eisen'] * $vk['eisen']/ 100;
	$verkaufen += $_POST['vk_titan'] * $vk['titan']/ 100;
	$verkaufen += $_POST['vk_oel'] * $vk['oel']/ 100;
	$verkaufen += $_POST['vk_uran'] * $vk['uran']/ 100;	
	
	if ($kaufen > $verkaufen) { $preis = $kaufen - $verkaufen; }
	else { $preis = $verkaufen - $kaufen; }
	
	$eisen = $ressis['eisen'] - $_POST['vk_eisen'];
	$titan = $ressis['titan'] - $_POST['vk_titan'];
	$oel   = $ressis['oel']   - $_POST['vk_oel'];
	$uran  = $ressis['uran']  - $_POST['vk_uran'];
	$gold  = $ressis['gold']  + ($verkaufen - $kaufen);
	
	if ($eisen < 0) { $status = '<b>Du hast nicht genug Eisen f&uuml;r diesen Handel.</b></body></html>'; }
	elseif ($titan < 0) { $status = '<b>Du hast nicht genug Titan f&uuml;r diesen Handel.</b></body></html>'; }
	elseif ($oel   < 0) { $status = '<b>Du hast nicht genug Oel f&uuml;r diesen Handel.</b></body></html>'; }
	elseif ($uran  < 0) { $status = '<b>Du hast nicht genug Uran f&uuml;r diesen Handel.</b></body></html>'; }
	elseif ($gold  < 0) { $status = '<b>Du hast nicht genug Gold f&uuml;r diesen Handel.</b></body></html>'; }
	else {
		$kaufen = number_format($kaufen,2,'.','');
		$verkaufen = number_format($verkaufen,2,'.','');
		$preis = number_format($preis,2,'.','');
		$status = '<b>Du hast f&uuml;r '.number_format($kaufen,2,',','.').' Gold gekauft, sowie f&uuml;r '.number_format($verkaufen,2,',','.').' Gold verkauft.<br />Das macht dann '.number_format($preis,2,',','.');	
		if ($kaufen > $verkaufen) { $status .= ' Gold zu unseren Gunsten.<br />'; }
		else { $status .= ' zu deinen Gunsten.</b>'; }
		
		$select = "UPDATE `ressis` SET `eisen` = '".$eisen."', `titan` = '".$titan."', `oel` = '".$oel."', `uran` = '".$uran."', `gold` = '".$gold."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
		mysql_query($select);

		if ($_POST['vk_eisen']) { $select = "INSERT INTO `markt` ( `einheit` , `menge` , `type` , `date` ) VALUES ( '1', '".$_POST['vk_eisen']."', '1', '".date('U')."' );"; mysql_query($select); }
		if ($_POST['ek_eisen']) { $select = "INSERT INTO `markt` ( `einheit` , `menge` , `type` , `date` ) VALUES ( '1', '".$_POST['ek_eisen']."', '0', '".date('U')."' );"; mysql_query($select); }
		if ($_POST['vk_titan']) { $select = "INSERT INTO `markt` ( `einheit` , `menge` , `type` , `date` ) VALUES ( '2', '".$_POST['vk_titan']."', '1', '".date('U')."' );"; mysql_query($select); }
		if ($_POST['ek_titan']) { $select = "INSERT INTO `markt` ( `einheit` , `menge` , `type` , `date` ) VALUES ( '2', '".$_POST['ek_titan']."', '0', '".date('U')."' );"; mysql_query($select); }
		if ($_POST['vk_oel']) { $select = "INSERT INTO `markt` ( `einheit` , `menge` , `type` , `date` ) VALUES ( '3', '".$_POST['vk_oel']."', '1', '".date('U')."' );"; mysql_query($select); }
		if ($_POST['ek_oel']) { $select = "INSERT INTO `markt` ( `einheit` , `menge` , `type` , `date` ) VALUES ( '3', '".$_POST['ek_oel']."', '0', '".date('U')."' );"; mysql_query($select); }
		if ($_POST['vk_uran']) { $select = "INSERT INTO `markt` ( `einheit` , `menge` , `type` , `date` ) VALUES ( '4', '".$_POST['vk_uran']."', '1', '".date('U')."' );"; mysql_query($select); }
		if ($_POST['ek_uran']) { $select = "INSERT INTO `markt` ( `einheit` , `menge` , `type` , `date` ) VALUES ( '4', '".$_POST['ek_uran']."', '0', '".date('U')."' );"; mysql_query($select); }
		
		if ($kaufen > 0){
			$rand = rand(20,40);
			$select = "INSERT INTO `missionen` ( `id` , `type` , `start` , `ziel` , `started` , `ankunft` , `return` , `speed` , `parsed` , `einh1` , `einh2` , `einh3` , `einh4` , `einh5` , `einh6` , `einh7` , `einh8` , `einh9` , `einh10` , `einh11` , `einh12` , `einh13` , `einh14` , `einh15` , `eisen` , `titan` , `oel` , `uran` , `gold` , `chanje` ) VALUES ( '', '2', '0', '".$_SESSION['user']['omni']."', '".date('U')."', '".(date('U')+($rand*60))."', '".(date('U')+20000)."', '666', '0', '".$eh[1]."', '".$eh[2]."', '".$eh[3]."', '".$eh[4]."', '".$eh[5]."', '".$eh[6]."', '".$eh[7]."', '".$eh[8]."', '".$eh[9]."', '100', '100', '".$eh[12]."', '".$eh[13]."', '".$eh[14]."', '".$eh[15]."', '".number_format($_POST['ek_eisen'],0,'','')."', '".number_format($_POST['ek_titan'],0,'','')."', '".number_format($_POST['ek_oel'],0,'','')."', '".number_format($_POST['ek_uran'],0,'','')."', '0', '0' );";
			mysql_query($select);
		
			$eid = mysql_insert_id($dbh);
	
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '1', '".$eid."', '".(date('U')+($rand*60))."');";
			$selectResult   = mysql_query($select);
			$status .= '<br /><b>Der Transport der Ressourcen zu deiner Basis ist nun gestartet.</b>';
		}
	}
}

// einheiten verkaufen
do {
	$count++;
	$einh[$count]['ek'] = number_format((($einh[$count]['eisen']/100*$ek['eisen']+$einh[$count]['titan']/100*$ek['titan']+$einh[$count]['oel']/100*$ek['oel']+$einh[$count]['uran']/100*$ek['uran']+$einh[$count]['chanje']*1000+$einh[$count]['gold']*2)/100*150),0,'','');
	$einh[$count]['vk'] = number_format($einh[$count]['ek'] / 2,0,'','');
} while ($count < 15);

if ($_GET['id'] and $_GET['action'] == 'sell' and $hangar['einh'.$_GET['id']] > 0){ 
	$hangar['einh'.$_GET['id']]--;
	$select = "UPDATE `ressis` SET `gold` = '".($ressis[gold]+$einh[$_GET['id']]['vk'])."' WHERE `omni` = '".$_SESSION['user']['omni']."';";
	mysql_query($select);
	$select = "UPDATE `hangar` SET `einh".$_GET['id']."` = '".$hangar['einh'.$_GET['id']]."' WHERE `omni` = '".$_SESSION['user']['omni']."';";
	mysql_query($select);
}

// einheiten kaufen
if ($_POST['action'] == 'buy'){ 

	$i=0;
	do {
		$i++;
		if ($_POST['anz'.$i] > 0){ 
			$kosten += $_POST['anz'.$i]*$einh[$i]['ek'];
			$einheit[$i] = $_POST['anz'.$i];
			$anz += $_POST['anz'.$i];
		}
	} while ($i<15);
	$i=0;
	
	if ($ressis[gold] >= $kosten and $anz > 0) {
		mysql_query("UPDATE `ressis` SET `gold` = '".($ressis[gold]-$kosten)."' WHERE `omni` = '".$_SESSION['user']['omni']."';");
		$einheit[$_POST['id']]=$einheit[$_POST['id']]*$_POST['anz'];
		$rand = rand(20,40);
		$select = "INSERT INTO `missionen` ( `id` , `type` , `start` , `ziel` , `started` , `ankunft` , `return` , `speed` , `parsed` , `einh1` , `einh2` , `einh3` , `einh4` , `einh5` , `einh6` , `einh7` , `einh8` , `einh9` , `einh10` , `einh11` , `einh12` , `einh13` , `einh14` , `einh15` , `eisen` , `titan` , `oel` , `uran` , `gold` , `chanje` ) VALUES ( '', '3', '0', '".$_SESSION['user']['omni']."', '".date('U')."', '".(date('U')+($rand*60))."', '".(date('U')+20000)."', '666', '0', '".$einheit[1]."', '".$einheit[2]."', '".$einheit[3]."', '".$einheit[4]."', '".$einheit[5]."', '".$einheit[6]."', '".$einheit[7]."', '".$einheit[8]."', '".$einheit[9]."', '".$einheit[10]."', '".$einheit[11]."', '".$einheit[12]."', '".$einheit[13]."', '".$einheit[14]."', '".$einheit[15]."', '0', '0', '0', '0', '0', '0' );";
		mysql_query($select);
	
		$eid = mysql_insert_id($dbh);
	
		$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '1', '".$eid."', '".(date('U')+($rand*60))."');";
		$selectResult   = mysql_query($select);
	
		$status .= '<center>Die &Uuml;berf&uuml;hrung wurde gestartet. Die Einheit(en) trifft/treffen in '.$rand.' Minuten ein.</center>';
	}
}
unset($einheit);

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION['user']['omni']);
$content .= $ressis['html'];

$content .= '<br />';

$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION['user']['omni']."';";
$selectResult   = mysql_query($select);
$gebaeude = mysql_fetch_array($selectResult);

// forschungen
$select = "SELECT * FROM `forschungen` WHERE `omni` = '".($_SESSION['user']['omni'])."';";
$result = mysql_query($select);
$forschung  = mysql_fetch_array($result);

// einheiten verkaufen
unset($einheit);
do {
$einheit++;
if ($hangar['einh'.$einheit]){ $units .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id='.$einheit.'\',400)">'.$einh[$einheit]['name'].'</a></td><td align="center">'.$hangar['einh'.$einheit].'</td><td align="center">'.$einh[$einheit]['ek'].' G</td><td align="center">'.$einh[$einheit]['vk'].' G</td><td align="center"><a href="markt.php?'.SID.'&amp;action=sell&amp;id='.$einheit.'">einen verkaufen</td</tr>'; }
} while ($einheit < 15);

$content .= '<br />
<table border="1" cellspacing="0" class="sub" style="width:640px">
	<tr>
		<th>
			<b>Markt:</b>
		</th>
	</tr>
	<tr>
		<td align="center">
		<br /><b>N&auml;chstes Marktupdate:</b><br />'.percentbar($update-date('U')+15,6*3600+15,420);

if ($units) { 
	$content .= '<b>Einheiten verkaufen:</b><table border="1" cellspacing="0" class="standard"><tr align="center"><th style="width:140px"><b>Einheit</b></th><th style="width:70px"><b>Anzahl<b></th><th style="width:70px"><b>EK</b></th><th style="width:70px"><b>VK</b></th><th style="width:150px;">&nbsp;</th></tr>'.$units.'</table><br />'; 
}

// einheiten kaufen
unset($units);
$einheit = 0;
do {
$einheit++;
$link = '<input type="hidden" name="action" value="buy" /><input type="text" name="anz'.$einheit.'" style="border:0; width:100%;" onFocus="calculate_price_eh()" onBlur="calculate_price_eh()" onKeyDown="calculate_price_eh()" onKeyUp="calculate_price_eh()" onChange="calculate_price_eh()" />';
if ($einheit ==  5 and $gebaeude['fahrwege'] < 1)   	{$link = '<input type="hidden" name="anz5" value="0" /><font color="red">zu niedrige Fahrwege</font>';}
elseif ($einheit ==  6 and $gebaeude['fahrwege'] < 3)   {$link = '<input type="hidden" name="anz6" value="0" /><font color="red">zu niedrige Fahrwege</font>';}
elseif ($einheit ==  7 and $gebaeude['fahrwege'] < 6)   {$link = '<input type="hidden" name="anz7" value="0" /><font color="red">zu niedrige Fahrwege</font>';}
elseif ($einheit ==  8 and $gebaeude['fahrwege'] < 8)   {$link = '<input type="hidden" name="anz8" value="0" /><font color="red">zu niedrige Fahrwege</font>';}
elseif ($einheit ==  9 and $gebaeude['fahrwege'] < 6)   {$link = '<input type="hidden" name="anz9" value="0" /><font color="red">zu niedrige Fahrwege</font>';}
elseif ($einheit == 10 and $gebaeude['fahrwege'] < 10)  {$link = '<input type="hidden" name="anz10" value="0" /><font color="red">zu niedrige Fahrwege</font>';}
elseif ($einheit == 11 and $gebaeude['fahrwege'] < 15)  {$link = '<input type="hidden" name="anz11" value="0" /><font color="red">zu niedrige Fahrwege</font>';}
elseif ($einheit == 12 and $gebaeude['fahrwege'] < 1)   {$link = '<input type="hidden" name="anz12" value="0" /><font color="red">zu niedrige Fahrwege</font>';}
elseif ($einheit == 13 and $gebaeude['fahrwege'] < 3)   {$link = '<input type="hidden" name="anz13" value="0" /><font color="red">zu niedrige Fahrwege</font>';}
elseif ($einheit == 14 and $gebaeude['fahrwege'] < 1)   {$link = '<input type="hidden" name="anz14" value="0" /><font color="red">zu niedrige Fahrwege</font>';}
elseif ($einheit == 15 and $gebaeude['fahrwege'] < 3)   {$link = '<input type="hidden" name="anz15" value="0" /><font color="red">zu niedrige Fahrwege</font>';}

$units .= '<tr class="standard"><td><a href="javascript:popUp(\'details_einh.php?id='.$einheit.'\',400)">'.$einh[$einheit]['name'].'</a></td><td align="center">'.$hangar['einh'.$einheit].'</td><td align="center">'.$einh[$einheit]['ek'].' G</td><td align="center">'.$einh[$einheit]['vk'].' G</td><td align="center" class="input">'.$link.'</td</tr>';
} while ($einheit < 15);

if ($units){ 
	$content .= '<script type="text/javascript"><!--
function calculate_price_eh() {
  var e = '.$ressis['eisen'].';
  var t = '.$ressis['titan'].';
  var o = '.$ressis['oel'].';
  var u = '.$ressis['uran'].';
  var g = '.$ressis['gold'].';
  var c = '.$ressis['chanje'].';

  var e1gold = '.$einh[1]['ek'].';
  var e2gold = '.$einh[2]['ek'].';
  var e3gold = '.$einh[3]['ek'].';
  var e4gold = '.$einh[4]['ek'].';
  var e5gold = '.$einh[5]['ek'].';
  var e6gold = '.$einh[6]['ek'].';
  var e7gold = '.$einh[7]['ek'].';
  var e8gold = '.$einh[8]['ek'].';
  var e9gold = '.$einh[9]['ek'].';
  var e10gold = '.$einh[10]['ek'].';
  var e11gold = '.$einh[11]['ek'].';
  var e12gold = '.$einh[12]['ek'].';
  var e13gold = '.$einh[13]['ek'].';
  var e14gold = '.$einh[14]['ek'].';
  var e15gold = '.$einh[15]['ek'].';

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

  var g1 = anz1*e1gold; 
  var g2 = anz2*e2gold; 
  var g3 = anz3*e3gold; 
  var g4 = anz4*e4gold; 
  var g5 = anz5*e5gold;
  var g6 = anz6*e6gold;
  var g7 = anz7*e7gold;
  var g8 = anz8*e8gold;
  var g9 = anz9*e9gold;
  var g10 = anz10*e10gold;
  var g11 = anz11*e11gold;
  var g12 = anz12*e12gold;
  var g13 = anz13*e13gold;
  var g14 = anz14*e14gold;
  var g15 = anz15*e15gold;

  var g_g = g1+g2+g3+g4+g5+g6+g7+g8+g9+g10+g11+g12+g13+g14+g15;

  if(g_g>g) { g_g = "<font class=\"red\">"+g_g+"</font>"; }

  document.getElementById("vk_kosten").innerHTML=g_g;
}
// End -->
</script>';

	$units .= '<tr class="standard"><td><b>Kosten</b></td><td align="right" colspan="4"><b><a id="vk_kosten">0</a> Gold</b></td</tr>';
	$content .= '<form enctype="multipart/form-data" action="markt.php?'. SID .'" method="post"><b>Einheiten kaufen:</b><table border="1" cellspacing="0" class="standard"><tr align="center"><th style="width:140px"><b>Einheit</b></th><th style="width:70px"><b>Anzahl<b></th><th style="width:70px"><b>EK</b></th><th style="width:70px"><b>VK</b></th><th style="width:145px;">&nbsp;</th></tr>'.$units.'</table><input type="submit" name="kaufen" value="kaufen" style="width:60px" /></form><br />'; 
}

if ($attack == 1){
	$content .= '<b>'.$status.'</b><br /><br /><b>Du kannst im Moment nicht mit Rohstoffen handeln, da du Angegriffen wirst.</b><br />';
} else {

$content .= '<b>'.$status.'</b><br /><br />
<script type="text/javascript"><!--
        function runde(x, n) {
        if (n < 1 || n > 14) return false;
        var e = Math.pow(10, n);
        var k = (Math.round(x * e) / e).toString();
        if (k.indexOf(".") == -1) k += ".";
        k += e.toString().substring(1);
        return k.substring(0, k.indexOf(".") + n+1);
}

function calculate_price() {
  var eisen_ek = Math.abs(document.getElementsByName("ek_eisen")[0].value);
  var eisen_vk = Math.abs(document.getElementsByName("vk_eisen")[0].value);
  var titan_ek = Math.abs(document.getElementsByName("ek_titan")[0].value);
  var titan_vk = Math.abs(document.getElementsByName("vk_titan")[0].value);
  var oel_ek   = Math.abs(document.getElementsByName("ek_oel")[0].value);
  var oel_vk   = Math.abs(document.getElementsByName("vk_oel")[0].value);
  var uran_ek  = Math.abs(document.getElementsByName("ek_uran")[0].value);
  var uran_vk  = Math.abs(document.getElementsByName("vk_uran")[0].value);

  var gold_minus = runde(eisen_ek/100*'.$ek['eisen'].'+titan_ek/100*'.$ek['titan'].'+oel_ek/100*'.$ek['oel'].'+uran_ek/100*'.$ek['uran'].',2);
  var gold_plus = runde(eisen_vk/100*'.$vk['eisen'].'+titan_vk/100*'.$vk['titan'].'+oel_vk/100*'.$vk['oel'].'+uran_vk/100*'.$vk['uran'].',2);
  var endsumme  = runde(gold_plus - gold_minus,2)

  document.getElementById("gold_plus").innerHTML="<font color=\"yellow\">"+gold_plus+"</font> Gold";
  document.getElementById("gold_minus").innerHTML="<font color=\"red\">"+gold_minus+"</font> Gold";
  document.getElementById("endsumme").innerHTML="<font color=\"red\">"+endsumme+"</font> Gold";

  if (endsumme > 0) {
    document.getElementById("endsumme").innerHTML="<font color=\"yellow\">"+endsumme+"</font> Gold";
  } else {
    document.getElementById("endsumme").innerHTML="<font color=\"red\">"+endsumme+"</font> Gold";
  }
}
// End -->
</script>
<form enctype="multipart/form-data" action="markt.php?'.SID.'" method="post">
<input type="hidden" name="ressis" value="1">
<b>Rohstoffhandel:</b><br />
(alle Preise verstehen sich in Gold je 100 Einheiten, 
<br />
sollten Kommastellen entstehen wird entsprechend gerundet.)
<table border="1" cellspacing="0" class="standard">
	<tr align="center">
		<th style="width:140px">
			<b>Rohstoff</b>
		</th>
		<th style="width:70px">
			<b>kaufen</b>
		</th>
		<th style="width:70px">
			<b>verkaufen</b>
		</th>
		<th style="width:95px">
			<b>kaufen</b>
		</th>
		<th style="width:95px;">
			<b>verkaufen</b>
		</th>
		<th style="width:70px">
			<b>vorhanden</b>
		</th>		
	</tr>
	<tr class="standard">
		<td style="width:140px">
			Eisen
		</td>
		<td align="right" style="width:70px">
			'.$ek['eisen'].' G&nbsp;
		</td>
		<td align="right" style="width:70px">
			'.$vk['eisen'].' G&nbsp;
		</td>
		<td class="input" style="width:95px;">
			<input type="text" name="ek_eisen" style="border:0; width:100%" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" />
		</td>
		<td class="input" style="width:95px;">
			<input type="text" name="vk_eisen" style="border:0; width:100%" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" />
		</td>
		<td align="right" style="width:70px">
			'.number_format($ressis['display_eisen'],0).'&nbsp;
		</td>				
	</tr>
	<tr class="standard">
		<td style="width:140px">
			Titan
		</td>
		<td align="right" style="width:70px">
			'.$ek['titan'].' G&nbsp;
		</td>
		<td align="right" style="width:70px">
			'.$vk['titan'].' G&nbsp;
		</td>
		<td class="input" style="width:95px;">
			<input type="text" name="ek_titan" style="border:0; width:100%" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" />
		</td>
		<td class="input" style="width:95px;">
			<input type="text" name="vk_titan" style="border:0; width:100%" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" />
		</td>
		<td align="right" style="width:70px">
			'.number_format($ressis['display_titan'],0).'&nbsp;
		</td>						
	</tr>
	<tr class="standard">
		<td style="width:140px">
			Oel 
		</td>
		<td align="right" style="width:70px">
			'.$ek['oel'].' G&nbsp;
		</td>
		<td align="right" style="width:70px">
			'.$vk['oel'].' G&nbsp;
		</td>
		<td class="input" style="width:95px;">
			<input type="text" name="ek_oel" style="border:0; width:100%" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" />
		</td>
		<td class="input" style="width:95px;">
			<input type="text" name="vk_oel" style="border:0; width:100%" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" />
		</td>
		<td align="right" style="width:70px">
			'.number_format($ressis['display_oel'],0).'&nbsp;
		</td>						
	</tr>
	<tr class="standard">
		<td style="width:140px">
			Uran
		</td>
		<td align="right" style="width:70px">
			'.$ek['uran'].' G&nbsp;
		</td>
		<td align="right" style="width:70px">
			'.$vk['uran'].' G&nbsp;
		</td>
		<td class="input" style="width:95px;">
			<input type="text" name="ek_uran" style="border:0; width:100%" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" />
		</td>
		<td class="input" style="width:95px;">
			<input type="text" name="vk_uran" style="border:0; width:100%" onFocus="calculate_price()" onBlur="calculate_price()" onKeyDown="calculate_price()" onKeyUp="calculate_price()" onChange="calculate_price()" />
		</td>
		<td align="right" style="width:70px">
			'.number_format($ressis['display_uran'],0).'&nbsp;
		</td>						
	</tr>
	<tr class="standard">
		<td style="width:140px">
			Gold
		</td>
		<td align="right" style="width:70px">
			-&nbsp;
		</td>
		<td align="right" style="width:70px">
			-&nbsp;
		</td>
		<td align="right" style="width:95px;">
			-&nbsp;
		</td>
		<td align="right" style="width:95px;">
			-&nbsp;
		</td>
		<td align="right" style="width:70px">
			'.number_format($ressis['display_gold'],0).'&nbsp;
		</td>						
	</tr>	
</table>
<table border="0">
<tr>
	<td align="right" width="130px">
		Gewinn durch Verkauf: 
	</td>
	<td align="right" width="100px">
		<div id="gold_plus">0.00 Gold</div>
	</td>
</tr>
<tr>
	<td align="right">
Kosten durch Ankauf 
	</td>
	<td align="right">
<div id="gold_minus">0.00 Gold</div>
	</td>
</tr>
<tr>
	<td align="right">
<b>Endsumme</b> 
	</td>
	<td align="right">
<b><div id="endsumme">0.00 Gold</div></b>
	</td>
	<td>
</tr>
</table>
<br />
<input type="submit" name="bestellen" value="bestellen" />
</form>
<br />
</td></tr></table><br />';
}

$content .= '<img src="eisenstats.png" alt="stats" /><br /><br />
<img src="titanstats.png" alt="stats" /><br /><br />
<img src="oelstats.png" alt="stats" /><br /><br />
<img src="uranstats.png" alt="stats" /><br />';

// generierte seite ausgeben
$content = tag2value('onload', $onload, $content);
echo $content.'<br />'.template('footer');
?>