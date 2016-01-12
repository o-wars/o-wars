<?php
//////////////////////////////////
// Rohstoffuebersicht           //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////
// Kommentare:
// - Ressistand
// - Rohstoffuebersicht
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

// check session
logincheck();

// html head setzen
$content  = template('head');

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

// mit datenbank verbinden
$dbh = db_connect();

$result  = mysql_query("SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';");
$clans   = mysql_fetch_array($result);
$members = mysql_num_rows(mysql_query("SELECT * FROM clans WHERE clanid = '".$clans['clanid']."';"));	
$users   = mysql_num_rows(mysql_query("SELECT * FROM user;"));
$rate    = round($members/($users / 100),2);

if ($_GET['5chanje'] == 1){
	if ($ressis['chanje'] >= 2){
		$select = "UPDATE `ressis` SET `ueberlagerbar` = '".(date('U')+600)."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
		mysql_query($select);
		$select = "UPDATE `ressis` SET `chanje` = `chanje`-2 WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
		mysql_query($select);
		$ressis = ressistand($_SESSION['user']['omni']);
	}
}

$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION[user][omni]."';";
$selectResult   = mysql_query($select);
$gebaeude = mysql_fetch_array($selectResult);

$content .= '<br />';

$ressis[eisen] = number_format($ressis[eisen],0,'','');
$ressis[titan] = number_format($ressis[titan],0,'','');
$ressis[oel] = number_format($ressis[oel],0,'','');
$ressis[uran] = number_format($ressis[uran],0,'','');
$ressis[gold] = number_format($ressis[gold],0,'','');

$space = (($gebaeude[rohstofflager] * 7500)+5000);

if ($rate < 20) {
	$eisen_bonus = ($gebaeude[eisenmine]*30)/100*($gebaeude[eisenmine]*5);
	$titan_bonus = ($gebaeude[titanmine]*20)/100*($gebaeude[titanmine]*5);
	$oel_bonus   = ($gebaeude[oelpumpe]*25)/100*($gebaeude[oelpumpe]*5);
	$uran_bonus = ($gebaeude[uranmine]*12)/100*($gebaeude[uranmine]*5);

	$e = explode('.',$eisen_bonus);
	$eisen_bonus = $e[0];
	$e = explode('.',$titan_bonus);
	$titan_bonus = $e[0];
	$e = explode('.',$oel_bonus);
	$oel_bonus = $e[0];
	$e = explode('.',$uran_bonus);
	$uran_bonus = $e[0];
} else {
	$eisen_bonus = '-';
	$titan_bonus = '-';
	$oel_bonus = '-';
	$uran_bonus = '-';
}


// rohstoffoerderung
$content .= template('rohstoffe');


$content = tag2value('lvl_eisen',$gebaeude[eisenmine],$content);
$content = tag2value('gf_eisen',($gebaeude[eisenmine]*30),$content);
$content = tag2value('lvl_titan',$gebaeude[titanmine],$content);
$content = tag2value('gf_titan',($gebaeude[titanmine]*20),$content);
$content = tag2value('lvl_oel',$gebaeude[oelpumpe],$content);
$content = tag2value('gf_oel',($gebaeude[oelpumpe]*25),$content);
$content = tag2value('lvl_uran',$gebaeude[uranmine],$content);
$content = tag2value('gf_uran',($gebaeude[uranmine]*12),$content);

$content = tag2value('eisen_bonus',$eisen_bonus,$content);
$content = tag2value('titan_bonus',$titan_bonus,$content);
$content = tag2value('oel_bonus',$oel_bonus,$content);
$content = tag2value('uran_bonus',$uran_bonus,$content);

$content = tag2value('eisen_all',($gebaeude[eisenmine]*30+40)+$eisen_bonus,$content);
$content = tag2value('titan_all',($gebaeude[titanmine]*20+20)+$titan_bonus,$content);
$content = tag2value('oel_all',(($gebaeude[oelpumpe]*25)+32)+$oel_bonus,$content);
$content = tag2value('uran_all',($gebaeude[uranmine]*12)+$uran_bonus,$content);
$content = tag2value('gold_all',($gebaeude[eisenmine]+$gebaeude[titanmine]+$gebaeude[oelpumpe]+$gebaeude[uranmine]+4),$content);
$content = tag2value('lvl_lager',$gebaeude[rohstofflager],$content);
$content = tag2value('space',($space-5000),$content);
$content = tag2value('space_all',($space),$content);
$content = tag2value('space_eisen',($space - $ressis[eisen]),$content);
$content = tag2value('space_titan',($space - $ressis[titan]),$content);
$content = tag2value('space_oel',($space - $ressis[oel]),$content);
$content = tag2value('space_uran',($space - $ressis[uran]),$content);
$content = tag2value('space_gold',($space - $ressis[gold]),$content);
$content = tag2value('nichtraubbar_lager',($gebaeude[rohstofflager]*100),$content);
$content = tag2value('nichtraubbar',($gebaeude[rohstofflager]*100+500),$content);

if ($ressis['ueberlagerbar'] > date('U')){ $content = tag2value('ueberlagerbar','<b>Countdown bis Ressourcen bei mangelndem Platz verfallen: <font class="red">'.percentbar($ressis['ueberlagerbar']-date('U'),600,400)."</font></b>",$content); }
else { $content = tag2value('ueberlagerbar','Klicke <a class="red" href="rohstoffe.php?'.SID.'&amp;5chanje=1"><b>hier</b></a> um f&uuml;r <b>2 Chanje</b> w&auml;hrend der n&auml;chsten <b>10 Minuten unbegrenzt Rohstoffe lagern</b> zu k&ouml;nnen.',$content); }

// generierte seite ausgeben
$content = tag2value("onload",$onload,$content);
echo $content.template('footer');
?>