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
include "inc/bbcode.php";

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
$ressis = ressistand($_SESSION[user][omni]);
$content .= $ressis['html'];

$dbh = db_connect();

$row = mysql_fetch_array(
	mysql_query("SELECT * FROM `user` WHERE `omni` = '".$_GET['ubl']."' LIMIT 1;"));

list($plasma['recieved']) = mysql_fetch_array(
	mysql_query("SELECT SUM(damage) as recieved FROM `plasmalog` WHERE `target` = '".$_GET['ubl']."' LIMIT 1;"));

list($plasma['send']) = mysql_fetch_array(
	mysql_query("SELECT SUM(damage) as send FROM `plasmalog` WHERE `omni` = '".$_GET['ubl']."' LIMIT 1;"));	
	
if ($row['pic']) { $pic = $row['pic']; }
else { $pic = "img/notfound.gif"; }
	
$content .= '<br /><br /><table style="width:400px;" border="1" cellspacing="0" cellpadding="0" class="standard">
<tr>
<th colspan="2">
Profil von '.$row['name'].'
</th>
</tr>
<tr class="standard">
<td width="50%">
&nbsp;<b>Basis:</b>
</td>
<td align="right">
'.$row['base'].'&nbsp;
</td>
</tr>
<tr class="standard">
<td>
&nbsp;<b>Ausbaupunkte:</b>
</td>
<td align="right">
'.number_format($row['points'],2,',','.').'&nbsp;
</td>
</tr>
<tr class="standard">
<td>
&nbsp;<b>Kampfpunkte:</b>
</td>
<td align="right">
'.number_format($row['kampfpunkte'],2,',','.').'&nbsp;
</td>
</tr>
<tr class="standard">
<td>
&nbsp;<b>Gesamtpunkte:</b>
</td>
<td align="right">
'.number_format($row['gesamtpunkte'],2,',','.').'&nbsp;
</td>
</tr>
<tr class="standard">
<td>
&nbsp;<b>Plasmapunkte:</b>
</td>
<td align="right">
'.number_format($row['plasmapunkte'],2,',','.').'&nbsp;
</td>
</tr>
<tr class="standard">
<td>
&nbsp;<b>Plasmaschaden:</b> (verteilt)
</td>
<td align="right">
'.number_format($plasma['send'],0,',','.').'&nbsp;
</td>
</tr>
<tr class="standard">
<td>
&nbsp;<b>Plasmaschaden:</b> (erhalten)
</td>
<td align="right">
'.number_format($plasma['recieved'],0,',','.').'&nbsp;
</td>
</tr>
<tr class="standard">
<td align="left" colspan="2" align="center">
<div align="center"><img src="'.$pic.'" /></div>
</td>
</tr>

<tr class="standard">
<td align="left" colspan="2">
'.nl2br(bbcode($row['sig'])).'
</td>
</tr>

</table>';


$content = tag2value("onload",$onload,$content);
echo $content.template('footer');
?>