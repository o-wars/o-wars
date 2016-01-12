<?php
//////////////////////////////////
// berichte.php                 //
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
$content  = template('head');
$content = tag2value('onload', '', $content);

// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);
$content .= $status;

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION['user']['omni']);
$content .= $ressis['html'];

$content .= '<br />';

// mit datenbank verbinden
$dbh = db_connect();

if ($_POST['alle_gelesen'] == 1){
	$select = "UPDATE `berichte` SET `gelesen` = '1' WHERE `to` = '".$_SESSION['user']['omni']."';";
	$selectResult   = mysql_query($select);
}

if ($_POST['del'] == 1){
	delberichte($_POST);
}

$result = mysql_query("SELECT * FROM `berichte` WHERE `to` = '".$_SESSION['user']['omni']."';");

$count  = mysql_num_rows($result)/20;
$count = explode('.',$count);
$c = $count[0];
if ($count[1] != 0) { $c++; }
if (!$_GET['page']) { $_GET['page'] = 1; }

$i = 0;
do {
	$i++;
	if ($_GET['page'] == $i) { $pages .= " <b>[".$i."]</b>"; }
	else { $pages .= ' <a href="berichte.php?'.SID.'&amp;page='.$i.'">['.$i.']</a>'; }
	if ($i/15 == round($i/15)) {
		$pages .= '<br />';
	}
} while ($c > $i);

$limit = ($_GET['page'] * 20-20).','.(20);

// ungelesene nachrichten
$select = "SELECT * FROM `berichte` WHERE `to` = '".$_SESSION['user']['omni']."' ORDER BY gelesen,id DESC LIMIT ".$limit.";";
$selectResult   = mysql_query($select);

$content .= template('berichte');
$piece   =  template('berichte_piece');

do {
	$row = mysql_fetch_array($selectResult);
	if ($row) {
		$newpiece = str_replace("%date%",date("H:i d.m.y",$row['timestamp']), $piece);
		$newpiece = str_replace("%from%",$row['from'], $newpiece);
		
		$newpiece = str_replace("%onclick%",'javascript:popUp(\'bericht_lesen.php?'.SID.'&id='.$row['id'].'\',600,\'bericht\')', $newpiece);
		$row['gelesen'] ? $read = 'Ja' : $read = 'Nein';
		$row['gelesen'] ? $color = '#e2e2e2' : $color = '#faaaaa';
		if (preg_match('/^Kampfbericht.*/',$row['subject']) != 0 or 
			preg_match('/^Angriff.*/',$row['subject']) != 0 or 
			preg_match('/^Raketenbeschuss.*/',$row['subject']) != 0 or 
			preg_match('/^Plasmabeschuss.*/',$row['subject']) != 0) {
			$newpiece = str_replace("%subject%",'<a href="javascript:popUp(\'bericht_lesen.php?'.SID.'&id='.$row['id'].'\',600,\'bericht\');" class="red"><b>'.$row['subject'].'</b></a>', $newpiece);		
		} 
		elseif (preg_match('/^Rohstofflieferung.*/',$row['subject']) != 0 or 
			preg_match('/^Sammeln.*/',$row['subject']) != 0 or 
			preg_match('/^&Uuml;berf&uuml;hrung.*/',$row['subject']) != 0 or 
			preg_match('/^Transport.*/',$row['subject']) != 0) {
			$newpiece = str_replace("%subject%",'<a href="javascript:popUp(\'bericht_lesen.php?'.SID.'&id='.$row['id'].'\',600,\'bericht\');" style="color:#006600"><b>'.$row['subject'].'</b></a>', $newpiece);		
		} 
		else { 
			$newpiece = str_replace("%subject%",'<a href="javascript:popUp(\'bericht_lesen.php?'.SID.'&id='.$row['id'].'\',600,\'bericht\');">'.$row['subject'].'</a>', $newpiece);		 
		}
		$newpiece = str_replace("%color%",$color, $newpiece);		
		$newpiece = str_replace("%del%",$row['id'], $newpiece);
		$newpiece = str_replace("%gelesen%",$read, $newpiece);
		$all .= "document.getElementById('".$row['id']."').checked=1;";
		$all2.= "document.getElementById('".$row['id']."').checked=0;";
		$ungelesen .= $newpiece;
	}
} while ($row);

$content = tag2value('ungelesene_berichte', $ungelesen, $content);
$content = tag2value('js_all', $all, $content);
$content = tag2value('js_all2', $all2, $content);
$content = tag2value('pages', $pages, $content);

// generierte seite ausgeben
echo $content.template('footer');

function delberichte($array) {
	$a = array_keys($_POST);
	$i=0;
	do {
		if ($array[$a[$i]] == del) {
			mysql_query("DELETE FROM `berichte` WHERE `to` = '".$_SESSION['user']['omni']."' AND `id` = '".$a[$i]."' LIMIT 1;");
		}
		$i++;
	} while ($a[$i]);
	return $array;
}
?>