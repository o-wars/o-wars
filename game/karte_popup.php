<?php
//////////////////////////////////
// Karte                        //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////
// Kommentare:
// - Status Spieler
// - Karte
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

// check session
logincheck();
if ($_POST[id]){$_GET[id] = $_POST[id];}
$_GET[id]  = htmlspecialchars($_GET[id]);

// html head setzen
$content  = head("Uebersicht");

// spielerstatus ausgeben
$content .= '<center>'.spielerstatus().'</center>';

$content .= '<br />';

$dbh = db_connect();

//if ($_GET[id]) {
	$select = "SELECT * FROM `user` WHERE `omni` = '".htmlspecialchars($_GET[id])."' ;";
	$selectResult   = mysql_query($select, $dbh);
	$row = mysql_fetch_array($selectResult, MYSQL_ASSOC);

	if (!$row[name]){ $row[name] = "unbewohnt (Taliban-Area)";}

	$to_position = position($_GET[id]);
	$own_pos = ($own_position[x]+($own_position[y]+($own_position[z]*500)));
	$to_pos  = ($to_position[x]+($to_position[y]+($to_position[z]*20)));
	if ( $own_pos == $to_pos AND $own_position[y] != $to_position[y] ){ $entfernung = 2; }
	elseif ( $own_pos >= $to_pos ) { $entfernung = ($own_pos - $to_pos) ; }
	elseif ( $own_pos <= $to_pos ) { $entfernung = ($to_pos - $own_pos) ; }
	
	if (!$row[base]){ $row[base] = 'unbekannt'; }
	if (!$row[clan]){ $row[clan] = '-'; }
	
	if ( $row[tf_eisen] == '' ){ $row[tf_eisen] = '0'; }
	if ( $row[tf_titan] == '' ){ $row[tf_titan] = '0'; }
	
	$content .= '<span style="font-size: 12px";>Spieler: <b><font id="name">'.$row[name].'</font></b> // Clan: <b><font id="clan">'.$row[clan].'</clan></b><br />Basis: <b><font id="base">'.$row[base].'</base></b> // UBL: <b><font id="ubl">'.$_GET[id].'</font></b><br />Entfernung: <b><font id="entfernung">'.($entfernung*2.5).'</font></b> Kilometer<br /><i>Tr&uuml;mmerfeld</i> Metall: <b><font id="tf_eisen">'.$row[tf_eisen].'</font></b> Titan: <b><font id="tf_titan">'.$row[tf_titan].'</font></b><br /><br />';


$content .= '<br /><a href="mission.php?'.SID.'&to='.$_GET[id].'" target="main">Mission</a> / <a href="agenten.php?'.SID.'&to='.$_GET[id].'" target="main">Agentenmission</a> / <a href="raketen.php?'.SID.'&to='.$_GET[id].'" target="main">Beschuss</a> / <a href="nachricht_schreiben.php?'.SID.'&to='.$_GET[id].'">Nachricht</a>';

echo $content.'</body></html>';