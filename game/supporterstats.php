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

//if ($_SESSION['user']['supporter'] <= date('U')){
//	die ("hier kommt dann das template fuer noch nicht supporter :P");
//}

$dbh = db_connect();

$select = "SELECT * FROM `stats` WHERE `id` = ".$_SESSION['user']['omni']." LIMIT 1;";
$result = mysql_query($select);
$row    = @mysql_fetch_array($result);

$content .= template('supporterstats');

$i = 0;
do {
	$i++;
	$content = tag2value("einh".$i, "<a href=\"javascript:popUp('details_einh.php?id=".$i."',400)\">".$einh[$i]['name']."</a>", $content);
	$content = tag2value("vkeinh".$i, number_format($row["vk".$i],0,'',','), $content);
	$content = tag2value("vreinh".$i, number_format($row["vr".$i],0,'',','), $content);
	$content = tag2value("vpeinh".$i, number_format($row["vp".$i],0,'',','), $content);
	$content = tag2value("vgeinh".$i, number_format($row["vp".$i]+$row["vr".$i]+$row["vk".$i],0,'',','), $content);
	$content = tag2value("dkeinh".$i, number_format($row["dk".$i],0,'',','), $content);
	$content = tag2value("dreinh".$i, number_format($row["dr".$i],0,'',','), $content);
	$content = tag2value("dpeinh".$i, number_format($row["dp".$i],0,'',','), $content);	
	$content = tag2value("dgeinh".$i, number_format($row["dp".$i]+$row["dr".$i]+$row["dk".$i],0,'',','), $content);
	$vp += $row["vp".$i];
	$vr += $row["vr".$i];
	$vk += $row["vk".$i];
	$vg += $row["vp".$i]+$row["vr".$i]+$row["vk".$i];
	$dp += $row["dp".$i];
	$dr += $row["dr".$i];
	$dk += $row["dk".$i];
	$dg += $row["dp".$i]+$row["dr".$i]+$row["dk".$i];
} while($i<15);

$content = tag2value("dkg", number_format($dk,0,'',','), $content);
$content = tag2value("drg", number_format($dr,0,'',','), $content);
$content = tag2value("dpg", number_format($dp,0,'',','), $content);	
$content = tag2value("dg", number_format($dg,0,'',','), $content);	

$content = tag2value("vkg", number_format($vk,0,'',','), $content);
$content = tag2value("vrg", number_format($vr,0,'',','), $content);
$content = tag2value("vpg", number_format($vp,0,'',','), $content);	
$content = tag2value("vg", number_format($vg,0,'',','), $content);	

$content = tag2value("f_eisen", number_format($row["farm_eisen"],0,'',','), $content);	
$content = tag2value("f_titan", number_format($row["farm_titan"],0,'',','), $content);	
$content = tag2value("f_oel", number_format($row["farm_oel"],0,'',','), $content);	
$content = tag2value("f_uran", number_format($row["farm_uran"],0,'',','), $content);	
$content = tag2value("f_gold", number_format($row["farm_gold"],0,'',','), $content);	

$content = tag2value("r_eisen", number_format($row["ripped_eisen"],0,'',','), $content);	
$content = tag2value("r_titan", number_format($row["ripped_titan"],0,'',','), $content);	
$content = tag2value("r_oel", number_format($row["ripped_oel"],0,'',','), $content);	
$content = tag2value("r_uran", number_format($row["ripped_uran"],0,'',','), $content);	
$content = tag2value("r_gold", number_format($row["ripped_gold"],0,'',','), $content);	

// generierte seite ausgeben
$content = str_replace('%onload%', $onload, $content);
echo $content.template('footer');;
?>