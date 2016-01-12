<?php
//////////////////////////////////
// bericht_lesen.php            //
//////////////////////////////////
// Letzte Aenderung: 01.11.2005 //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

// check session
logincheck();

// html head setzen
$content = template('head');
$content = tag2value('onload', '', $content);

$content .= '<br />';

// mit datenbank verbinden
$dbh = db_connect();

// ungelesene nachrichten
$selectResult   = mysql_query("SELECT * FROM `berichte` WHERE `id` = ".$_GET['id']." AND `to` = '".$_SESSION['user']['omni']."' GROUP BY id DESC;");

$content .= '<center><span style="font-size: 12px";><table border="1" cellspacing="0" style="background-color:#e2e2e2; font-size: 12px"><tr align="center"><th style="width:80px">Datum</th><th style="width:120px">Absender</th><th style="width:300px">Betreff</th></tr>';

$piece = '<tr align="center"><td>%date%</td><td>%omni%</td><td>%subject%</td></tr>
<tr align="left"><td colspan="4" style="padding:4px;"><br />%text%<br />'.$ad.'</td></tr>';

	$row = mysql_fetch_array($selectResult);
	if ($row['to'] == $_SESSION['user']['omni']) {
		$newpiece = str_replace("%date%",date("H:i d.m.y",$row['timestamp']), $piece);
		$newpiece = str_replace("%omni%",$row['from'], $newpiece);
		$newpiece = str_replace("%from%",$row['from_name'], $newpiece);
		$newpiece = str_replace("%subject%",$row['subject'], $newpiece);
		$newpiece = str_replace("%text%",nl2br($row['text']), $newpiece);
		$newpiece = str_replace("%ad%",template('ad'), $newpiece);
		$content .= $newpiece;
		
		$selectResult   = mysql_query("UPDATE `berichte` SET `gelesen` = '1' WHERE `id` = '".$_GET['id']."' AND `to` = '".$_SESSION['user']['omni']."' LIMIT 1;");
		
	} else {die ("es ist ein fehler aufgetreten beim anzeigen der nachricht");}

$content .= '</table></center>';

/*$content .= '
<br />
Dieser Bericht wurde pr&auml;sentiert von:<br />
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
