<?php
//////////////////////////////////
// Nachrichtenzentrum           //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////
// Kommentare:
// - Status Spieler
// - Status Nachrichten
// - Nachrichten Lesen
// - Nachrichten Senden
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";
include "inc/bbcode.php";

// check session
logincheck();

$_GET['id'] = number_format($_GET['id'],0,'','');

// html head setzen
$content = template('head');
$content = tag2value('onload', '', $content);

$content .= '<br />';

// mit datenbank verbinden
$dbh = db_connect();

// ungelesene nachrichten
$select = "SELECT * FROM `nachrichten` WHERE `id` = ".$_GET['id']." GROUP BY id DESC;";
$selectResult   = mysql_query($select);

$content .= '<center><span style="font-size: 12px";><table border="1" cellspacing="0" style="background-color:#e2e2e2; font-size: 12px"><tr align="center"><th style="width:80px">Datum</th><th style="width:60px">UBL</th><th style="width:120px">Absender</th><th style="width:300px">Betreff</th></tr>';

$piece = '<tr align="center"><td>%date%</td><td>%omni%</td><td>%from%</td><td>%subject%</td></tr>
<tr align="left"><td colspan="4" style="padding:4px;"><br />%text%<br /><br />'.$ad.'</td></tr>';

	$row = mysql_fetch_array($selectResult);
	if ($row['from'] == $_SESSION['user']['omni']) {$show = 1;}
	if ($row[to] == $_SESSION['user']['omni'])     {$show = 1;}
	if ($show) {
		
		$row['text'] = bbcode($row['text']);
		
		$newpiece = str_replace("%date%",date("H:i d.m.y",$row['timestamp']), $piece);
		$newpiece = str_replace("%omni%",$row['from'], $newpiece);
		$newpiece = str_replace("%from%",$row['from_name'], $newpiece);
		$newpiece = str_replace("%subject%",bbcode($row['subject']), $newpiece);
		$newpiece = str_replace("%text%",bbcode(nl2br($row['text']), $_SESSION['badwords']), $newpiece);
		$newpiece = str_replace("SID",SID, $newpiece);
		$newpiece = str_replace("%ad%",template('ad'), $newpiece);		
		$content .= $newpiece;
		
		$select = "UPDATE `nachrichten` SET `gelesen` = '1' WHERE `id` = '".$_GET['id']."' AND `to` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		
	} else {die ("es ist ein fehler aufgetreten beim anzeigen der nachricht");}

$content .= '</table></center>';

// antwortbutton
if (!$_GET['gesendet']) {
	$content .= '<br /><form enctype="multipart/form-data" action="nachricht_schreiben.php?'. SID .'" method="post">
	<input type="hidden" name="action" value="reply">
	<input type="hidden" name="to" value="'.$row['from'].'">
	<input type="hidden" name="subject" value="Re: '.$row['subject'].'">
	<input type="hidden" name="id" value="'.$_GET['id'].'">
	<input type="submit" name="submit" value="Antworten"></form>';
}

/*$content2 .= '
<br />
Diese Nachricht wurde pr&auml;sentiert von:<br />
<script type="text/javascript"><!--
  amazon_ad_tag = "owars-21";
  amazon_ad_width = "468";
  amazon_ad_height = "60";
  amazon_color_link = "003399";
//--></script>
<script type="text/javascript" src="http://www.assoc-amazon.de/s/ads.js"></script>
';*/

// generierte seite ausgeben
echo $content.'</body></html>';
?>
