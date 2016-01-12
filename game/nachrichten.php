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
$ressis = ressistand($_SESSION[user][omni]);
$content .= $ressis['html'];

if ($_POST['del'] == 1){
	delmsgs($_POST);
}

if     ($_GET['action'] == 'inbox') { $content .= inbox(); $content = tag2value('box', 'Posteingang:', $content); }
elseif ($_GET['action'] == 'outbox') { $content .= outbox(); $content = tag2value('box', 'Postausgang:', $content); }
else   { $content .= inbox(); $content = tag2value('box', 'Posteingang', $content); };

function inbox(){
	// mit datenbank verbinden
	$dbh = db_connect();
	
	$result = mysql_query("SELECT * FROM `nachrichten` WHERE `to` = '".$_SESSION['user']['omni']."';");

	$count  = mysql_num_rows($result)/20;
	$count = explode('.',$count);
	$c = $count[0];
	if ($count[1] != 0) { $c++; }
	if (!$_GET['page']) { $_GET['page'] = 1; }

	$i = 0;
	do {
		$i++;
		if ($_GET['page'] == $i) { $pages .= " <b>[".$i."]</b>"; }
		else { $pages .= ' <a href="nachrichten.php?'.SID.'&amp;page='.$i.'">['.$i.']</a>'; }
	} while ($c > $i);

	$limit = ($_GET['page'] * 20 - 20).','.(20);	

	// ungelesene nachrichten
	$select = "SELECT * FROM `nachrichten` WHERE `to` = '".$_SESSION['user']['omni']."' AND `del_to` = 0 ORDER BY gelesen,id DESC LIMIT ".$limit.";";
	$selectResult   = mysql_query($select);

	$content .= template('head');
	$content .= template('nachrichten');
	$piece = template('nachrichten_piece');

	do {
		$row = mysql_fetch_array($selectResult);
		if ($row) {
			$newpiece = str_replace("%date%",date("H:i d.m.y",$row['timestamp']), $piece);
			$row['gelesen'] ? $read = 'Ja' : $read = 'Nein';
			$row['gelesen'] ? $color = '#e2e2e2' : $color = '#faaaaa';
			$newpiece = str_replace("%read%",$read, $newpiece);			
			$newpiece = str_replace("%color%",$color, $newpiece);			
			$newpiece = str_replace("%omni%",$row['from'], $newpiece);
			$newpiece = str_replace("%from%",$row['from_name'], $newpiece);
			$newpiece = str_replace("%subject%",'<a href="javascript:popUp(\'nachricht_lesen.php?'.SID.'&id='.$row['id'].'\',600,\'nachricht\')">'.bbcode($row['subject']).'</a>', $newpiece);
			$newpiece = str_replace("%onclick%",'javascript:popUp(\'nachricht_lesen.php?'.SID.'&id='.$row['id'].'\',600,\'nachricht\')', $newpiece);
			$newpiece = str_replace("%del%",$row['id'], $newpiece);
			$all .= "document.getElementById('".$row['id']."').checked=1;";
			$all2.= "document.getElementById('".$row['id']."').checked=0;";
			$gelesen .= $newpiece;
		}
	} while ($row);

	$content = tag2value('gelesene_nachrichten', $gelesen, $content);
	$content = tag2value('pages', $pages, $content);
	$content = tag2value('js_all', $all, $content);
	$content = tag2value('js_all2', $all2, $content);	
	
	return $content;
}

function outbox(){
	// mit datenbank verbinden
	$dbh = db_connect();

	$result = mysql_query("SELECT * FROM `nachrichten` WHERE `from` = '".$_SESSION['user']['omni']."';");

	$count  = mysql_num_rows($result)/20;
	$count = explode('.',$count);
	$c = $count[0];
	if ($count[1] != 0) { $c++; }
	if (!$_GET['page']) { $_GET['page'] = 1; }

	$i = 0;
	do {
		$i++;
		if ($_GET['page'] == $i) { $pages .= " <b>[".$i."]</b>"; }
		else { $pages .= ' <a href="nachrichten.php?'.SID.'&amp;action=outbox&amp;page='.$i.'">['.$i.']</a>'; }
	} while ($c > $i);

	$limit = ($_GET['page'] * 20 - 20).','.(20);		
	
	// ungelesene nachrichten
	$select = "SELECT * FROM `nachrichten` WHERE `from` = '".$_SESSION[user][omni]."' ORDER BY id DESC LIMIT ".$limit.";";
	$selectResult   = mysql_query($select);

	$content .= template('head');
	$content .= template('nachrichten_gesendet');
	$piece = template('nachrichten_gesendet_piece');

	do {
		$row = mysql_fetch_array($selectResult);
		if ($row) {
			$select = "SELECT * FROM `user` WHERE `omni` = '".$row[to]."';";
			$selectResult2   = mysql_query($select);
			$row2 = mysql_fetch_array($selectResult2);
			$newpiece = str_replace("%date%",date("H:i d.m.y",$row[timestamp]), $piece);
			$newpiece = str_replace("%omni%",$row[to], $newpiece);
			$newpiece = str_replace("%to%",$row2[name], $newpiece);
			$row['gelesen'] ? $read = 'Ja' : $read = 'Nein';
			$newpiece = str_replace("%read%",$read, $newpiece);
			$newpiece = str_replace("%subject%",'<a href="javascript:popUp(\'nachricht_lesen.php?'.SID.'&gesendet=1&id='.$row[id].'\',600,\'nachricht\')">'.bbcode($row[subject]).'</a>', $newpiece);
			$newpiece = str_replace("%onclick%",'javascript:popUp(\'nachricht_lesen.php?'.SID.'&gesendet=1&id='.$row[id].'\',600,\'nachricht\')', $newpiece);
			$gelesen .= $newpiece;
		}
	} while ($row);

	$content = tag2value('nachrichten', $gelesen, $content);
	$content = tag2value('pages', $pages, $content);
	return $content;
}

function delmsgs($array) {
	$a = array_keys($_POST);
	$i=0;
	do {
		if ($array[$a[$i]] == 'del') {
			mysql_query("UPDATE `nachrichten` SET `del_to` = '1' AND `gelesen` = '1'  WHERE `to` = '".$_SESSION['user']['omni']."' AND `id` = '".$a[$i]."' LIMIT 1;");
		}
		$i++;
	} while ($a[$i]);
	return $array;
}

// generierte seite ausgeben
echo $content.template('footer');
?>