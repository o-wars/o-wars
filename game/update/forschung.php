<?php
//////////////////////////////////
// Forschungsanlage             //
//////////////////////////////////
// Letzte Aenderung: 01.11.2005 //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// panzerungfunktionen laden
include "functions.php";

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

// forschungspreise:
include 'forschung_preise.php';

$ressis = ressistand($_SESSION['user']['omni']);

// mit datenbank verbinden
$dbh = db_connect();

if ($_GET[abbrechen] == 1){
	$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextmotor` = '0', `nextfeuerwaffen` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextspionage` = '0', `nextfuehrung` = '0', `nextcyborgtechnik` = '0', `nextminen` = '0', `nextrad` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$abbrechen = "Die aktuelle Forschung wurde abgebrochen.";
}

$select = "SELECT * FROM `forschungen` WHERE `omni` = '".$_SESSION['user']['omni']."';";
$selectResult   = mysql_query($select);
$row = mysql_fetch_array($selectResult);

$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION['user']['omni']."';";
$selectResult   = mysql_query($select);
$gebaeude = mysql_fetch_array($selectResult);

$row['panzerung']++;
$row['reaktor']++;
$row['panzerketten']++;
$row['motor']++;
$row['feuerwaffen']++;
$row['raketen']++;
$row['sprengstoff']++;
$row['spionage']++;
$row['fuehrung']++;
$row['cyborgtechnik']++;
$row['minen']++;
$row['rad']++;



if ($row['nextpanzerung'] != 0){ $teuer = 'Du bist bereits am forschen'; }
elseif ($row['nextreaktor'] != 0){ $teuer = 'Du bist bereits am forschen'; }
elseif ($row['nextpanzerketten'] != 0){ $teuer = 'Du bist bereits am forschen'; }
elseif ($row['nextmotor'] != 0){ $teuer = 'Du bist bereits am forschen'; }
elseif ($row['nextfeuerwaffen'] != 0){ $teuer = 'Du bist bereits am forschen'; }
elseif ($row['nextraketen'] != 0){ $teuer = 'Du bist bereits am forschen'; }
elseif ($row['nextsprengstoff'] != 0){ $teuer = 'Du bist bereits am forschen'; }
elseif ($row['nextspionage'] != 0){ $teuer = 'Du bist bereits am forschen'; }
elseif ($row['nextfuehrung'] != 0){ $teuer = 'Du bist bereits am forschen'; }
elseif ($row['nextcyborgtechnik'] != 0){ $teuer = 'Du bist bereits am forschen'; }
elseif ($row['nextminen'] != 0){ $teuer = 'Du bist bereits am forschen'; }
elseif ($row['nextrad'] != 0){ $teuer = 'Du bist bereits am forschen'; }

// neue forschung starten
if ($_GET['bau'] != ''){
	if ($_GET['bau'] == 'panzerung'){ 
		if ($ressis['eisen'] < number_format(($kosten['panzerung']['eisen'] * ( $row['panzerung'] * $row['panzerung'] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis['titan'] < number_format(($kosten['panzerung']['titan'] * ( $row['panzerung'] * $row['panzerung'] )),0)){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis['oel']   < number_format(($kosten['panzerung']['oel']   * ( $row['panzerung'] * $row['panzerung'] )),0)){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis['uran']  < number_format(($kosten['panzerung']['uran']  * ( $row['panzerung'] * $row['panzerung'] )),0)){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis['gold']  < number_format(($kosten['panzerung']['gold']  * ( $row['panzerung'] * $row['panzerung'] )),0)){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis['chanje'] < number_format(($kosten['panzerung']['chanje'] * ( $row['panzerung'] * $row['panzerung'] )),0)){ $teuer .= "Du hast zu wenig Chanje. "; }
		if (($gebaeude['forschungsanlage'] * 2) < $row['panzerung']-1) { $teuer .= "Level &uuml;berschreitet Forschungsanlagen Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] - ($kosten['panzerung']['eisen'] * ( $row['panzerung'] * $row['panzerung'] )))."',`titan` = '".($ressis['titan'] - ($kosten['panzerung']['titan'] * ( $row['panzerung'] * $row['panzerung'] )))."',`oel` = '".($ressis['oel'] - ($kosten['panzerung']['oel'] * ( $row['panzerung'] * $row['panzerung'] )))."',`uran` = '".($ressis['uran'] - ($kosten['panzerung']['uran'] * ( $row['panzerung'] * $row['panzerung'] )))."',`gold` = '".($ressis['gold'] - ($kosten['panzerung']['gold'] * ( $row['panzerung'] * $row['panzerung'] )))."',`chanje` = '".($ressis['chanje'] - ($kosten['panzerung']['chanje'] * ( $row['panzerung'] * $row['panzerung'] )))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$row['nextpanzerung'] = (date(U) + ($kosten['panzerung']['zeit'] * $row['panzerung']));
			$select = "UPDATE `forschungen` SET `nextpanzerung` = '".$row['nextpanzerung']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '4', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten['panzerung']['zeit'] * $row['panzerung']))."');";
			$selectResult   = mysql_query($select);
		}
	}
	elseif ($_GET['bau'] == 'reaktor'){ 
		if ($ressis['eisen'] < number_format(($kosten['reaktor']['eisen'] * ( $row['reaktor'] * $row['reaktor'] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis['titan'] < ($kosten['reaktor']['titan'] * ( $row['reaktor'] * $row['reaktor'] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis['oel']   < ($kosten['reaktor']['oel']   * ( $row['reaktor'] * $row['reaktor'] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis['uran']  < ($kosten['reaktor']['uran']  * ( $row['reaktor'] * $row['reaktor'] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis['gold']  < ($kosten['reaktor']['gold']  * ( $row['reaktor'] * $row['reaktor'] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis['chanje'] < ($kosten['reaktor']['chanje'] * ( $row['reaktor'] * $row['reaktor'] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if (($gebaeude['forschungsanlage'] * 2) < $row['reaktor']-1) { $teuer .= "Level &uuml;berschreitet Forschungsanlagen Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] - ($kosten['reaktor']['eisen'] * ( $row['reaktor'] * $row['reaktor'] )))."',`titan` = '".($ressis['titan'] - ($kosten['reaktor']['titan'] * ( $row['reaktor'] * $row['reaktor'] )))."',`oel` = '".($ressis['oel'] - ($kosten['reaktor']['oel'] * ( $row['reaktor'] * $row['reaktor'] )))."',`uran` = '".($ressis['uran'] - ($kosten['reaktor']['uran'] * ( $row['reaktor'] * $row['reaktor'] )))."',`gold` = '".($ressis['gold'] - ($kosten['reaktor']['gold'] * ( $row['reaktor'] * $row['reaktor'] )))."',`chanje` = '".($ressis['chanje'] - ($kosten['reaktor']['chanje'] * ( $row['reaktor'] * $row['reaktor'] )))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$row['nextreaktor'] = (date(U) + ($kosten['reaktor']['zeit'] * $row['reaktor']));
			$select = "UPDATE `forschungen` SET `nextreaktor` = '".$row['nextreaktor']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '4', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten['reaktor']['zeit'] * $row['reaktor']))."');";
			$selectResult   = mysql_query($select);
		}
	}
	elseif ($_GET['bau'] == 'panzerketten'){ 
		if ($ressis['eisen'] < number_format(($kosten['panzerketten']['eisen'] * ( $row['panzerketten'] * $row['panzerketten'] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis['titan'] < ($kosten['panzerketten']['titan'] * ( $row['panzerketten'] * $row['panzerketten'] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis['oel']   < ($kosten['panzerketten']['oel']   * ( $row['panzerketten'] * $row['panzerketten'] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis['uran']  < ($kosten['panzerketten']['uran']  * ( $row['panzerketten'] * $row['panzerketten'] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis['gold']  < ($kosten['panzerketten']['gold']  * ( $row['panzerketten'] * $row['panzerketten'] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis['chanje'] < ($kosten['panzerketten']['chanje'] * ( $row['panzerketten'] * $row['panzerketten'] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if (($gebaeude['forschungsanlage'] * 2) < $row['panzerketten']-1) { $teuer .= "Level &uuml;berschreitet Forschungsanlagen Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] - ($kosten['panzerketten']['eisen'] * ( $row['panzerketten'] * $row['panzerketten'] )))."',`titan` = '".($ressis['titan'] - ($kosten['panzerketten']['titan'] * ( $row['panzerketten'] * $row['panzerketten'] )))."',`oel` = '".($ressis['oel'] - ($kosten['panzerketten']['oel'] * ( $row['panzerketten'] * $row['panzerketten'] )))."',`uran` = '".($ressis['uran'] - ($kosten['panzerketten']['uran'] * ( $row['panzerketten'] * $row['panzerketten'] )))."',`gold` = '".($ressis['gold'] - ($kosten['panzerketten']['gold'] * ( $row['panzerketten'] * $row['panzerketten'] )))."',`chanje` = '".($ressis['chanje'] - ($kosten['panzerketten']['chanje'] * ( $row['panzerketten'] * $row['panzerketten'] )))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$row['nextpanzerketten'] = (date(U) + ($kosten['panzerketten']['zeit'] * $row['panzerketten']));
			$select = "UPDATE `forschungen` SET `nextpanzerketten` = '".$row['nextpanzerketten']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);

			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '4', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten['panzerketten']['zeit'] * $row['panzerketten']))."');";
			$selectResult   = mysql_query($select);
		}
	}
	elseif ($_GET['bau'] == 'motor'){ 
		if ($ressis['eisen'] < number_format(($kosten['motor']['eisen'] * ( $row['motor'] * $row['motor'] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis['titan'] < ($kosten['motor']['titan'] * ( $row['motor'] * $row['motor'] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis['oel']   < ($kosten['motor']['oel']   * ( $row['motor'] * $row['motor'] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis['uran']  < ($kosten['motor']['uran']  * ( $row['motor'] * $row['motor'] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis['gold']  < ($kosten['motor']['gold']  * ( $row['motor'] * $row['motor'] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis['chanje'] < ($kosten['motor']['chanje'] * ( $row['motor'] * $row['motor'] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if (($gebaeude['forschungsanlage'] * 2) < $row['motor']-1) { $teuer .= "Level &uuml;berschreitet Forschungsanlagen Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] - ($kosten['motor']['eisen'] * ( $row['motor'] * $row['motor'] )))."',`titan` = '".($ressis['titan'] - ($kosten['motor']['titan'] * ( $row['motor'] * $row['motor'] )))."',`oel` = '".($ressis['oel'] - ($kosten['motor']['oel'] * ( $row['motor'] * $row['motor'] )))."',`uran` = '".($ressis['uran'] - ($kosten['motor']['uran'] * ( $row['motor'] * $row['motor'] )))."',`gold` = '".($ressis['gold'] - ($kosten['motor']['gold'] * ( $row['motor'] * $row['motor'] )))."',`chanje` = '".($ressis['chanje'] - ($kosten['motor']['chanje'] * ( $row['motor'] * $row['motor'] )))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$row['nextmotor'] = (date(U) + ($kosten['motor']['zeit'] * $row['motor']));
			$select = "UPDATE `forschungen` SET `nextmotor` = '".$row['nextmotor']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);

			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '4', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten['motor']['zeit'] * $row['motor']))."');";
			$selectResult   = mysql_query($select);			
		}
	}	
	elseif ($_GET['bau'] == 'feuerwaffen'){ 
		if ($ressis['eisen'] < number_format(($kosten['feuerwaffen']['eisen'] * ( $row['feuerwaffen'] * $row['feuerwaffen'] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis['titan'] < ($kosten['feuerwaffen']['titan'] * ( $row['feuerwaffen'] * $row['feuerwaffen'] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis['oel']   < ($kosten['feuerwaffen']['oel']   * ( $row['feuerwaffen'] * $row['feuerwaffen'] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis['uran']  < ($kosten['feuerwaffen']['uran']  * ( $row['feuerwaffen'] * $row['feuerwaffen'] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis['gold']  < ($kosten['feuerwaffen']['gold']  * ( $row['feuerwaffen'] * $row['feuerwaffen'] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis['chanje'] < ($kosten['feuerwaffen']['chanje'] * ( $row['feuerwaffen'] * $row['feuerwaffen'] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if (($gebaeude['forschungsanlage'] * 2) < $row['feuerwaffen']-1) { $teuer .= "Level &uuml;berschreitet Forschungsanlagen Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] - ($kosten['feuerwaffen']['eisen'] * ( $row['feuerwaffen'] * $row['feuerwaffen'] )))."',`titan` = '".($ressis['titan'] - ($kosten['feuerwaffen']['titan'] * ( $row['feuerwaffen'] * $row['feuerwaffen'] )))."',`oel` = '".($ressis['oel'] - ($kosten['feuerwaffen']['oel'] * ( $row['feuerwaffen'] * $row['feuerwaffen'] )))."',`uran` = '".($ressis['uran'] - ($kosten['feuerwaffen']['uran'] * ( $row['feuerwaffen'] * $row['feuerwaffen'] )))."',`gold` = '".($ressis['gold'] - ($kosten['feuerwaffen']['gold'] * ( $row['feuerwaffen'] * $row['feuerwaffen'] )))."',`chanje` = '".($ressis['chanje'] - ($kosten['feuerwaffen']['chanje'] * ( $row['feuerwaffen'] * $row['feuerwaffen'] )))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$row['nextfeuerwaffen'] = (date(U) + ($kosten['feuerwaffen']['zeit'] * $row['feuerwaffen']));
			$select = "UPDATE `forschungen` SET `nextfeuerwaffen` = '".$row['nextfeuerwaffen']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '4', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten['feuerwaffen']['zeit'] * $row['feuerwaffen']))."');";
			$selectResult   = mysql_query($select);
		}
	}
	elseif ($_GET['bau'] == 'raketen'){ 
		if ($ressis['eisen'] < number_format(($kosten['raketen']['eisen'] * ( $row['raketen'] * $row['raketen'] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis['titan'] < ($kosten['raketen']['titan'] * ( $row['raketen'] * $row['raketen'] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis['oel']   < ($kosten['raketen']['oel']   * ( $row['raketen'] * $row['raketen'] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis['uran']  < ($kosten['raketen']['uran']  * ( $row['raketen'] * $row['raketen'] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis['gold']  < ($kosten['raketen']['gold']  * ( $row['raketen'] * $row['raketen'] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis['chanje'] < ($kosten['raketen']['chanje'] * ( $row['raketen'] * $row['raketen'] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if (($gebaeude['forschungsanlage'] * 2) < $row['raketen']-1) { $teuer .= "Level &uuml;berschreitet Forschungsanlagen Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] - ($kosten['raketen']['eisen'] * ( $row['raketen'] * $row['raketen'] )))."',`titan` = '".($ressis['titan'] - ($kosten['raketen']['titan'] * ( $row['raketen'] * $row['raketen'] )))."',`oel` = '".($ressis['oel'] - ($kosten['raketen']['oel'] * ( $row['raketen'] * $row['raketen'] )))."',`uran` = '".($ressis['uran'] - ($kosten['raketen']['uran'] * ( $row['raketen'] * $row['raketen'] )))."',`gold` = '".($ressis['gold'] - ($kosten['raketen']['gold'] * ( $row['raketen'] * $row['raketen'] )))."',`chanje` = '".($ressis['chanje'] - ($kosten['raketen']['chanje'] * ( $row['raketen'] * $row['raketen'] )))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$row['nextraketen'] = (date(U) + ($kosten['raketen']['zeit'] * $row['raketen']));
			$select = "UPDATE `forschungen` SET `nextraketen` = '".$row['nextraketen']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);

			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '4', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten['raketen']['zeit'] * $row['raketen']))."');";
			$selectResult   = mysql_query($select);		
		}
	}	
	elseif ($_GET['bau'] == 'sprengstoff'){ 
		if ($ressis['eisen'] < number_format(($kosten['sprengstoff']['eisen'] * ( $row['sprengstoff'] * $row['sprengstoff'] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis['titan'] < ($kosten['sprengstoff']['titan'] * ( $row['sprengstoff'] * $row['sprengstoff'] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis['oel']   < ($kosten['sprengstoff']['oel']   * ( $row['sprengstoff'] * $row['sprengstoff'] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis['uran']  < ($kosten['sprengstoff']['uran']  * ( $row['sprengstoff'] * $row['sprengstoff'] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis['gold']  < ($kosten['sprengstoff']['gold']  * ( $row['sprengstoff'] * $row['sprengstoff'] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis['chanje'] < ($kosten['sprengstoff']['chanje'] * ( $row['sprengstoff'] * $row['sprengstoff'] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if (($gebaeude['forschungsanlage'] * 2) < $row['sprengstoff']-1) { $teuer .= "Level &uuml;berschreitet Forschungsanlagen Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] - ($kosten['sprengstoff']['eisen'] * ( $row['sprengstoff'] * $row['sprengstoff'] )))."',`titan` = '".($ressis['titan'] - ($kosten['sprengstoff']['titan'] * ( $row['sprengstoff'] * $row['sprengstoff'] )))."',`oel` = '".($ressis['oel'] - ($kosten['sprengstoff']['oel'] * ( $row['sprengstoff'] * $row['sprengstoff'] )))."',`uran` = '".($ressis['uran'] - ($kosten['sprengstoff']['uran'] * ( $row['sprengstoff'] * $row['sprengstoff'] )))."',`gold` = '".($ressis['gold'] - ($kosten['sprengstoff']['gold'] * ( $row['sprengstoff'] * $row['sprengstoff'] )))."',`chanje` = '".($ressis['chanje'] - ($kosten['sprengstoff']['chanje'] * ( $row['sprengstoff'] * $row['sprengstoff'] )))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$row['nextsprengstoff'] = (date(U) + ($kosten['sprengstoff']['zeit'] * $row['sprengstoff']));
			$select = "UPDATE `forschungen` SET `nextsprengstoff` = '".$row['nextsprengstoff']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '4', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten['sprengstoff']['zeit'] * $row['sprengstoff']))."');";
			$selectResult   = mysql_query($select);
		}
	}
	elseif ($_GET['bau'] == 'spionage'){ 
		if ($ressis['eisen'] < number_format(($kosten['spionage']['eisen'] * ( $row['spionage'] * $row['spionage'] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis['titan'] < ($kosten['spionage']['titan'] * ( $row['spionage'] * $row['spionage'] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis['oel']   < ($kosten['spionage']['oel']   * ( $row['spionage'] * $row['spionage'] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis['uran']  < ($kosten['spionage']['uran']  * ( $row['spionage'] * $row['spionage'] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis['gold']  < ($kosten['spionage']['gold']  * ( $row['spionage'] * $row['spionage'] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis['chanje'] < ($kosten['spionage']['chanje'] * ( $row['spionage'] * $row['spionage'] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if (($gebaeude['forschungsanlage'] * 2) < $row['spionage']-1) { $teuer .= "Level &uuml;berschreitet Forschungsanlagen Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] - ($kosten['spionage']['eisen'] * ( $row['spionage'] * $row['spionage'] )))."',`titan` = '".($ressis['titan'] - ($kosten['spionage']['titan'] * ( $row['spionage'] * $row['spionage'] )))."',`oel` = '".($ressis['oel'] - ($kosten['spionage']['oel'] * ( $row['spionage'] * $row['spionage'] )))."',`uran` = '".($ressis['uran'] - ($kosten['spionage']['uran'] * ( $row['spionage'] * $row['spionage'] )))."',`gold` = '".($ressis['gold'] - ($kosten['spionage']['gold'] * ( $row['spionage'] * $row['spionage'] )))."',`chanje` = '".($ressis['chanje'] - ($kosten['spionage']['chanje'] * ( $row['spionage'] * $row['spionage'] )))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$row['nextspionage'] = (date(U) + ($kosten['spionage']['zeit'] * $row['spionage']));
			$select = "UPDATE `forschungen` SET `nextspionage` = '".$row['nextspionage']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '4', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten['spionage']['zeit'] * $row['spionage']))."');";
			$selectResult   = mysql_query($select);
		}
	}
	elseif ($_GET['bau'] == 'fuehrung'){ 
		if ($ressis['eisen'] < number_format(($kosten['fuehrung']['eisen'] * ( $row['fuehrung'] * $row['fuehrung'] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis['titan'] < ($kosten['fuehrung']['titan'] * ( $row['fuehrung'] * $row['fuehrung'] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis['oel']   < ($kosten['fuehrung']['oel']   * ( $row['fuehrung'] * $row['fuehrung'] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis['uran']  < ($kosten['fuehrung']['uran']  * ( $row['fuehrung'] * $row['fuehrung'] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis['gold']  < ($kosten['fuehrung']['gold']  * ( $row['fuehrung'] * $row['fuehrung'] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis['chanje'] < ($kosten['fuehrung']['chanje'] * ( $row['fuehrung'] * $row['fuehrung'] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if (($gebaeude['forschungsanlage'] * 2) < $row['fuehrung']-1) { $teuer .= "Level &uuml;berschreitet Forschungsanlagen Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] - ($kosten['fuehrung']['eisen'] * ( $row['fuehrung'] * $row['fuehrung'] )))."',`titan` = '".($ressis['titan'] - ($kosten['fuehrung']['titan'] * ( $row['fuehrung'] * $row['fuehrung'] )))."',`oel` = '".($ressis['oel'] - ($kosten['fuehrung']['oel'] * ( $row['fuehrung'] * $row['fuehrung'] )))."',`uran` = '".($ressis['uran'] - ($kosten['fuehrung']['uran'] * ( $row['fuehrung'] * $row['fuehrung'] )))."',`gold` = '".($ressis['gold'] - ($kosten['fuehrung']['gold'] * ( $row['fuehrung'] * $row['fuehrung'] )))."',`chanje` = '".($ressis['chanje'] - ($kosten['fuehrung']['chanje'] * ( $row['fuehrung'] * $row['fuehrung'] )))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$row['nextfuehrung'] = (date(U) + ($kosten['fuehrung']['zeit'] * $row['fuehrung']));
			$select = "UPDATE `forschungen` SET `nextfuehrung` = '".$row['nextfuehrung']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '4', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten['fuehrung']['zeit'] * $row['fuehrung']))."');";
			$selectResult   = mysql_query($select);
		}
	}
	elseif ($_GET['bau'] == 'cyborgtechnik'){ 
		if ($ressis['eisen'] < number_format(($kosten['cyborgtechnik']['eisen'] * ( $row['cyborgtechnik'] * $row['cyborgtechnik'] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis['titan'] < ($kosten['cyborgtechnik']['titan'] * ( $row['cyborgtechnik'] * $row['cyborgtechnik'] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis['oel']   < ($kosten['cyborgtechnik']['oel']   * ( $row['cyborgtechnik'] * $row['cyborgtechnik'] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis['uran']  < ($kosten['cyborgtechnik']['uran']  * ( $row['cyborgtechnik'] * $row['cyborgtechnik'] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis['gold']  < ($kosten['cyborgtechnik']['gold']  * ( $row['cyborgtechnik'] * $row['cyborgtechnik'] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis['chanje'] < ($kosten['cyborgtechnik']['chanje'] * ( $row['cyborgtechnik'] * $row['cyborgtechnik'] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if (($gebaeude['forschungsanlage'] * 2) < $row['cyborgtechnik']-1) { $teuer .= "Level &uuml;berschreitet Forschungsanlagen Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] - ($kosten['cyborgtechnik']['eisen'] * ( $row['cyborgtechnik'] * $row['cyborgtechnik'] )))."',`titan` = '".($ressis['titan'] - ($kosten['cyborgtechnik']['titan'] * ( $row['cyborgtechnik'] * $row['cyborgtechnik'] )))."',`oel` = '".($ressis['oel'] - ($kosten['cyborgtechnik']['oel'] * ( $row['cyborgtechnik'] * $row['cyborgtechnik'] )))."',`uran` = '".($ressis['uran'] - ($kosten['cyborgtechnik']['uran'] * ( $row['cyborgtechnik'] * $row['cyborgtechnik'] )))."',`gold` = '".($ressis['gold'] - ($kosten['cyborgtechnik']['gold'] * ( $row['cyborgtechnik'] * $row['cyborgtechnik'] )))."',`chanje` = '".($ressis['chanje'] - ($kosten['cyborgtechnik']['chanje'] * ( $row['cyborgtechnik'] * $row['cyborgtechnik'] )))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$row['nextcyborgtechnik'] = (date(U) + ($kosten['cyborgtechnik']['zeit'] * $row['cyborgtechnik']));
			$select = "UPDATE `forschungen` SET `nextcyborgtechnik` = '".$row['nextcyborgtechnik']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '4', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten['cyborgtechnik']['zeit'] * $row['cyborgtechnik']))."');";
			$selectResult   = mysql_query($select);
		}
	}
	elseif ($_GET['bau'] == 'minen'){ 
		if ($ressis['eisen'] < number_format(($kosten['minen']['eisen'] * ( $row['minen'] * $row['minen'] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis['titan'] < ($kosten['minen']['titan'] * ( $row['minen'] * $row['minen'] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis['oel']   < ($kosten['minen']['oel']   * ( $row['minen'] * $row['minen'] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis['uran']  < ($kosten['minen']['uran']  * ( $row['minen'] * $row['minen'] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis['gold']  < ($kosten['minen']['gold']  * ( $row['minen'] * $row['minen'] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis['chanje'] < ($kosten['minen']['chanje'] * ( $row['minen'] * $row['minen'] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if (($gebaeude['forschungsanlage'] * 2) < $row['minen']-1) { $teuer .= "Level &uuml;berschreitet Forschungsanlagen Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] - ($kosten['minen']['eisen'] * ( $row['minen'] * $row['minen'] )))."',`titan` = '".($ressis['titan'] - ($kosten['minen']['titan'] * ( $row['minen'] * $row['minen'] )))."',`oel` = '".($ressis['oel'] - ($kosten['minen']['oel'] * ( $row['minen'] * $row['minen'] )))."',`uran` = '".($ressis['uran'] - ($kosten['minen']['uran'] * ( $row['minen'] * $row['minen'] )))."',`gold` = '".($ressis['gold'] - ($kosten['minen']['gold'] * ( $row['minen'] * $row['minen'] )))."',`chanje` = '".($ressis['chanje'] - ($kosten['minen']['chanje'] * ( $row['minen'] * $row['minen'] )))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$row['nextminen'] = (date(U) + ($kosten['minen']['zeit'] * $row['minen']));
			$select = "UPDATE `forschungen` SET `nextminen` = '".$row['nextminen']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '4', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten['minen']['zeit'] * $row['minen']))."');";
			$selectResult   = mysql_query($select);
		}
	}
	elseif ($_GET['bau'] == 'rad'){ 
		if ($ressis['eisen'] < number_format(($kosten['rad']['eisen'] * ( $row['rad'] * $row['rad'] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis['titan'] < ($kosten['rad']['titan'] * ( $row['rad'] * $row['rad'] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis['oel']   < ($kosten['rad']['oel']   * ( $row['rad'] * $row['rad'] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis['uran']  < ($kosten['rad']['uran']  * ( $row['rad'] * $row['rad'] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis['gold']  < ($kosten['rad']['gold']  * ( $row['rad'] * $row['rad'] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis['chanje'] < ($kosten['rad']['chanje'] * ( $row['rad'] * $row['rad'] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if (($gebaeude['forschungsanlage'] * 2) < $row['rad']-1) { $teuer .= "Level &uuml;berschreitet Forschungsanlagen Level. "; }
			
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] - ($kosten['rad']['eisen'] * ( $row['rad'] * $row['rad'] )))."',`titan` = '".($ressis['titan'] - ($kosten['rad']['titan'] * ( $row['rad'] * $row['rad'] )))."',`oel` = '".($ressis['oel'] - ($kosten['rad']['oel'] * ( $row['rad'] * $row['rad'] )))."',`uran` = '".($ressis['uran'] - ($kosten['rad']['uran'] * ( $row['rad'] * $row['rad'] )))."',`gold` = '".($ressis['gold'] - ($kosten['rad']['gold'] * ( $row['rad'] * $row['rad'] )))."',`chanje` = '".($ressis['chanje'] - ($kosten['rad']['chanje'] * ( $row['rad'] * $row['rad'] )))."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$row['nextrad'] = (date(U) + ($kosten['rad']['zeit'] * $row['rad']));
			$select = "UPDATE `forschungen` SET `nextrad` = '".$row['nextrad']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
			$selectResult   = mysql_query($select);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '4', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten['rad']['zeit'] * $row['rad']))."');";
			$selectResult   = mysql_query($select);
		}
	}
}

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION['user']['omni']);
$content .= $ressis['html'];

$content .= '<br />';

$script = "<SCRIPT LANGUAGE=\"JavaScript\">
		  <!--
          var restzeit = new Number();
          var restzeit =%restzeit%;
          function startCountdown()
           {
               if((restzeit - 1) >= 0)
                {
                    restzeit = restzeit - 1;
                    var min_count = restzeit/60;
                    min_count=Math.floor(min_count);
                    sec_count = restzeit - (min_count*60);
                     if(min_count>0)
                      {
                          var std_count = min_count/60;
                          std_count=Math.floor(std_count);
                          min_count=min_count-std_count*60;
                      }
                     else { var std_count = 0; }
                     
                     sec_count=Math.floor(sec_count);

                     if(min_count<10) { min_angabe='0'+min_count; }
                     else { min_angabe=''+min_count; }
                     if(sec_count<10) { sec_angabe='0'+sec_count; }
                     else { sec_angabe=''+sec_count; }
                      
                      document.getElementById('verbleibend').firstChild.nodeValue = std_count+':'+min_angabe+':'+sec_angabe;
                      setTimeout('startCountdown()',986);
                }
                else
                {                   
                    document.getElementById('verbleibend').firstChild.nodeValue = 'bereit';
                    setTimeout('location.reload()',500);
                }
           }
           // End --></script>";

if ($row['nextpanzerung'] <= date(U) AND $row['nextpanzerung'] != 0){
	$select = "UPDATE `forschungen` SET `panzerung` = '".$row['panzerung']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$row['nextpanzerung'] = 0;
	$row['panzerung']++;
}
elseif ($row['nextreaktor'] <= date(U) AND $row['nextreaktor'] != 0){
	$select = "UPDATE `forschungen` SET `reaktor` = '".$row['reaktor']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
}
elseif ($row['nextpanzerketten'] <= date(U) AND $row['nextpanzerketten'] != 0){
	$select = "UPDATE `forschungen` SET `panzerketten` = '".$row['panzerketten']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$row['nextpanzerketten'] = 0;
	$row['panzerketten']++;
}
elseif ($row['nextmotor'] <= date(U) AND $row['nextmotor'] != 0){
	$select = "UPDATE `forschungen` SET `motor` = '".$row['motor']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$row['nextmotor'] = 0;
	$row['motor']++;
}
elseif ($row['nextfeuerwaffen'] <= date(U) AND $row['nextfeuerwaffen'] != 0){
	$select = "UPDATE `forschungen` SET `feuerwaffen` = '".$row['feuerwaffen']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$row['nextfeuerwaffen'] = 0;
	$row['feuerwaffen']++;
}
elseif ($row['nextraketen'] <= date(U) AND $row['nextraketen'] != 0){
	$select = "UPDATE `forschungen` SET `raketen` = '".$row['raketen']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$row['nextraketen'] = 0;
	$row['raketen']++;
}
elseif ($row['nextsprengstoff'] <= date(U) AND $row['nextsprengstoff'] != 0){
	$select = "UPDATE `forschungen` SET `sprengstoff` = '".$row['sprengstoff']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$row['nextsprengstoff'] = 0;
	$row['sprengstoff']++;
}
elseif ($row['nextspionage'] <= date(U) AND $row['nextspionage'] != 0){
	$select = "UPDATE `forschungen` SET `spionage` = '".$row['spionage']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$row['nextspionage'] = 0;
	$row['spionage']++;
}
elseif ($row['nextfuehrung'] <= date(U) AND $row['nextfuehrung'] != 0){
	$select = "UPDATE `forschungen` SET `fuehrung` = '".$row['fuehrung']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$row['nextfuehrung'] = 0;
	$row['fuehrung']++;
}
elseif ($row['nextcyborgtechnik'] <= date(U) AND $row['nextcyborgtechnik'] != 0){
	$select = "UPDATE `forschungen` SET `cyborgtechnik` = '".$row['cyborgtechnik']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$row['nextcyborgtechnik'] = 0;
	$row['cyborgtechnik']++;
}
elseif ($row['nextminen'] <= date(U) AND $row['nextminen'] != 0){
	$select = "UPDATE `forschungen` SET `minen` = '".$row['minen']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$row['nextminen'] = 0;
	$row['minen']++;
}
elseif ($row['nextrad'] <= date(U) AND $row['nextrad'] != 0){
	$select = "UPDATE `forschungen` SET `rad` = '".$row['rad']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
	$selectResult   = mysql_query($select);
	$row['nextrad'] = 0;
	$row['rad']++;
}

if ($row['nextpanzerung'] != 0){
	$running = 1; 
	$content .= str_replace("%restzeit%",$row['nextpanzerung']-date('U'),$script);
	$bauen['panzerung'] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen['reaktor'] = '<center>-</center>'; 
	$bauen['panzerketten'] = '<center>-</center>'; 
	$bauen['feuerwaffen'] = '<center>-</center>'; 
	$bauen['motor'] = '<center>-</center>'; 
	$bauen['sprengstoff'] = '<center>-</center>'; 
	$bauen['raketen'] = '<center>-</center>'; 
	$bauen['spionage'] = '<center>-</center>'; 
	$bauen['fuehrung'] = '<center>-</center>'; 
	$bauen['cyborgtechnik'] = '<center>-</center>'; 
	$bauen['minen'] = '<center>-</center>'; 
	$bauen['rad'] = '<center>-</center>'; 
}
elseif ($row['nextreaktor'] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row['nextreaktor']-date('U'),$script);
	$bauen['reaktor'] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen['panzerung'] = '<center>-</center>'; 
	$bauen['panzerketten'] = '<center>-</center>'; 
	$bauen['feuerwaffen'] = '<center>-</center>'; 
	$bauen['motor'] = '<center>-</center>'; 
	$bauen['sprengstoff'] = '<center>-</center>'; 
	$bauen['raketen'] = '<center>-</center>'; 
	$bauen['spionage'] = '<center>-</center>'; 
	$bauen['fuehrung'] = '<center>-</center>'; 
	$bauen['cyborgtechnik'] = '<center>-</center>'; 
	$bauen['minen'] = '<center>-</center>'; 
	$bauen['rad'] = '<center>-</center>'; 
}
elseif ($row['nextpanzerketten'] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row['nextpanzerketten']-date('U'),$script);
	$bauen['panzerketten'] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen['reaktor'] = '<center>-</center>'; 
	$bauen['panzerung'] = '<center>-</center>'; 
	$bauen['feuerwaffen'] = '<center>-</center>'; 
	$bauen['motor'] = '<center>-</center>'; 
	$bauen['sprengstoff'] = '<center>-</center>'; 
	$bauen['raketen'] = '<center>-</center>'; 
	$bauen['spionage'] = '<center>-</center>'; 
	$bauen['fuehrung'] = '<center>-</center>'; 
	$bauen['cyborgtechnik'] = '<center>-</center>'; 
	$bauen['minen'] = '<center>-</center>'; 
	$bauen['rad'] = '<center>-</center>'; 
}
elseif ($row['nextmotor'] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row['nextmotor']-date('U'),$script);
	$bauen['motor'] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen['reaktor'] = '<center>-</center>'; 
	$bauen['panzerketten'] = '<center>-</center>'; 
	$bauen['feuerwaffen'] = '<center>-</center>'; 
	$bauen['panzerung'] = '<center>-</center>'; 
	$bauen['sprengstoff'] = '<center>-</center>'; 
	$bauen['raketen'] = '<center>-</center>'; 
	$bauen['spionage'] = '<center>-</center>'; 
	$bauen['fuehrung'] = '<center>-</center>'; 
	$bauen['cyborgtechnik'] = '<center>-</center>'; 
	$bauen['minen'] = '<center>-</center>'; 
	$bauen['rad'] = '<center>-</center>'; 
}
elseif ($row['nextfeuerwaffen'] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row['nextfeuerwaffen']-date('U'),$script);
	$bauen['feuerwaffen'] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen['reaktor'] = '<center>-</center>'; 
	$bauen['panzerketten'] = '<center>-</center>'; 
	$bauen['panzerung'] = '<center>-</center>'; 
	$bauen['motor'] = '<center>-</center>'; 
	$bauen['sprengstoff'] = '<center>-</center>'; 
	$bauen['raketen'] = '<center>-</center>'; 
	$bauen['spionage'] = '<center>-</center>'; 
	$bauen['fuehrung'] = '<center>-</center>'; 
	$bauen['cyborgtechnik'] = '<center>-</center>'; 
	$bauen['minen'] = '<center>-</center>'; 
	$bauen['rad'] = '<center>-</center>'; 
}
elseif ($row['nextraketen'] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row['nextraketen']-date('U'),$script);
	$bauen['raketen'] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen['reaktor'] = '<center>-</center>'; 
	$bauen['panzerketten'] = '<center>-</center>'; 
	$bauen['feuerwaffen'] = '<center>-</center>'; 
	$bauen['motor'] = '<center>-</center>'; 
	$bauen['sprengstoff'] = '<center>-</center>'; 
	$bauen['panzerung'] = '<center>-</center>'; 
	$bauen['spionage'] = '<center>-</center>'; 
	$bauen['fuehrung'] = '<center>-</center>'; 
	$bauen['cyborgtechnik'] = '<center>-</center>'; 
	$bauen['minen'] = '<center>-</center>'; 
	$bauen['rad'] = '<center>-</center>'; 
}
elseif ($row['nextsprengstoff'] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row['nextsprengstoff']-date('U'),$script);
	$bauen['sprengstoff'] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen['reaktor'] = '<center>-</center>'; 
	$bauen['panzerketten'] = '<center>-</center>'; 
	$bauen['feuerwaffen'] = '<center>-</center>'; 
	$bauen['motor'] = '<center>-</center>'; 
	$bauen['panzerung'] = '<center>-</center>'; 
	$bauen['raketen'] = '<center>-</center>'; 
	$bauen['spionage'] = '<center>-</center>'; 
	$bauen['fuehrung'] = '<center>-</center>'; 
	$bauen['cyborgtechnik'] = '<center>-</center>'; 
	$bauen['minen'] = '<center>-</center>'; 
	$bauen['rad'] = '<center>-</center>'; 
}
elseif ($row['nextspionage'] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row['nextspionage']-date('U'),$script);
	$bauen['spionage'] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen['reaktor'] = '<center>-</center>'; 
	$bauen['panzerketten'] = '<center>-</center>'; 
	$bauen['feuerwaffen'] = '<center>-</center>'; 
	$bauen['motor'] = '<center>-</center>'; 
	$bauen['sprengstoff'] = '<center>-</center>'; 
	$bauen['raketen'] = '<center>-</center>'; 
	$bauen['panzerung'] = '<center>-</center>'; 
	$bauen['fuehrung'] = '<center>-</center>'; 
	$bauen['cyborgtechnik'] = '<center>-</center>'; 
	$bauen['minen'] = '<center>-</center>'; 
	$bauen['rad'] = '<center>-</center>'; 
}
elseif ($row['nextfuehrung'] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row['nextfuehrung']-date('U'),$script);
	$bauen['fuehrung'] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen['reaktor'] = '<center>-</center>'; 
	$bauen['panzerketten'] = '<center>-</center>'; 
	$bauen['feuerwaffen'] = '<center>-</center>'; 
	$bauen['motor'] = '<center>-</center>'; 
	$bauen['sprengstoff'] = '<center>-</center>'; 
	$bauen['raketen'] = '<center>-</center>'; 
	$bauen['spionage'] = '<center>-</center>'; 
	$bauen['panzerung'] = '<center>-</center>'; 
	$bauen['cyborgtechnik'] = '<center>-</center>'; 
	$bauen['minen'] = '<center>-</center>'; 
	$bauen['rad'] = '<center>-</center>'; 
}
elseif ($row['nextcyborgtechnik'] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row['nextcyborgtechnik']-date('U'),$script);
	$bauen['cyborgtechnik'] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen['reaktor'] = '<center>-</center>'; 
	$bauen['panzerketten'] = '<center>-</center>'; 
	$bauen['feuerwaffen'] = '<center>-</center>'; 
	$bauen['motor'] = '<center>-</center>'; 
	$bauen['sprengstoff'] = '<center>-</center>'; 
	$bauen['raketen'] = '<center>-</center>'; 
	$bauen['spionage'] = '<center>-</center>'; 
	$bauen['fuehrung'] = '<center>-</center>'; 
	$bauen['panzerung'] = '<center>-</center>'; 
	$bauen['minen'] = '<center>-</center>'; 
	$bauen['rad'] = '<center>-</center>'; 
}
elseif ($row['nextminen'] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row['nextminen']-date('U'),$script);
	$bauen['minen'] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen['reaktor'] = '<center>-</center>'; 
	$bauen['panzerketten'] = '<center>-</center>'; 
	$bauen['feuerwaffen'] = '<center>-</center>'; 
	$bauen['motor'] = '<center>-</center>'; 
	$bauen['sprengstoff'] = '<center>-</center>'; 
	$bauen['raketen'] = '<center>-</center>'; 
	$bauen['spionage'] = '<center>-</center>'; 
	$bauen['fuehrung'] = '<center>-</center>'; 
	$bauen['cyborgtechnik'] = '<center>-</center>'; 
	$bauen['panzerung'] = '<center>-</center>'; 
	$bauen['rad'] = '<center>-</center>'; 
}
elseif ($row['nextrad'] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row['nextrad']-date('U'),$script);
	$bauen['rad'] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen['reaktor'] = '<center>-</center>'; 
	$bauen['panzerketten'] = '<center>-</center>'; 
	$bauen['feuerwaffen'] = '<center>-</center>'; 
	$bauen['motor'] = '<center>-</center>'; 
	$bauen['sprengstoff'] = '<center>-</center>'; 
	$bauen['raketen'] = '<center>-</center>'; 
	$bauen['spionage'] = '<center>-</center>'; 
	$bauen['fuehrung'] = '<center>-</center>'; 
	$bauen['cyborgtechnik'] = '<center>-</center>'; 
	$bauen['minen'] = '<center>-</center>'; 
	$bauen['panzerung'] = '<center>-</center>'; 
}
else {
	$zuteuer = '<font style="color: red;">zu teuer</font>';	
	$bauen['panzerung'] = '<a href="forschung.php?'.SID.'&bau=panzerung">forschen</a>';
	if ($kosten['panzerung']['eisen']*($row['panzerung'] *$row['panzerung']) > $ressis['eisen']){$bauen['panzerung'] = $zuteuer;}
	if ($kosten['panzerung']['titan']*($row['panzerung'] *$row['panzerung']) > $ressis['titan']){$bauen['panzerung'] = $zuteuer;}
	if ($kosten['panzerung']['oel']*($row['panzerung'] *$row['panzerung']) > $ressis['oel']){$bauen['panzerung'] = $zuteuer;}
	if ($kosten['panzerung']['uran']*($row['panzerung'] *$row['panzerung']) > $ressis['uran']){$bauen['panzerung'] = $zuteuer;}
	if ($kosten['panzerung']['gold']*($row['panzerung'] *$row['panzerung']) > $ressis['gold']){$bauen['panzerung'] = $zuteuer;}
	if ($kosten['panzerung']['chanje']*($row['panzerung'] *$row['panzerung']) > $ressis['chanje']){$bauen['panzerung'] = $zuteuer;}

	$bauen['reaktor'] = '<a href="forschung.php?'.SID.'&bau=reaktor">forschen</a>';
	if ($kosten['reaktor']['eisen']*($row['reaktor']*$row['reaktor']) > $ressis['eisen']){$bauen['reaktor'] = $zuteuer;}
	if ($kosten['reaktor']['titan']*($row['reaktor']*$row['reaktor']) > $ressis['titan']){$bauen['reaktor'] = $zuteuer;}
	if ($kosten['reaktor']['oel']*($row['reaktor']*$row['reaktor']) > $ressis['oel']){$bauen['reaktor'] = $zuteuer;}
	if ($kosten['reaktor']['uran']*($row['reaktor']*$row['reaktor']) > $ressis['uran']){$bauen['reaktor'] = $zuteuer;}
	if ($kosten['reaktor']['gold']*($row['reaktor']*$row['reaktor']) > $ressis['gold']){$bauen['reaktor'] = $zuteuer;}
	if ($kosten['reaktor']['chanje']*($row['reaktor']*$row['reaktor']) > $ressis['chanje']){$bauen['reaktor'] = $zuteuer;}

	$bauen['panzerketten'] = '<a href="forschung.php?'.SID.'&bau=panzerketten">forschen</a>';
	if ($kosten['panzerketten']['eisen']*($row['panzerketten']*$row['panzerketten']) > $ressis['eisen']){$bauen['panzerketten'] = $zuteuer;}
	if ($kosten['panzerketten']['titan']*($row['panzerketten']*$row['panzerketten']) > $ressis['titan']){$bauen['panzerketten'] = $zuteuer;}
	if ($kosten['panzerketten']['oel']*($row['panzerketten']*$row['panzerketten']) > $ressis['oel']){$bauen['panzerketten'] = $zuteuer;}
	if ($kosten['panzerketten']['uran']*($row['panzerketten']*$row['panzerketten']) > $ressis['uran']){$bauen['panzerketten'] = $zuteuer;}
	if ($kosten['panzerketten']['gold']*($row['panzerketten']*$row['panzerketten']) > $ressis['gold']){$bauen['panzerketten'] = $zuteuer;}
	if ($kosten['panzerketten']['chanje']*($row['panzerketten']*$row['panzerketten']) > $ressis['chanje']){$bauen['panzerketten'] = $zuteuer;}

	$bauen['motor'] = '<a href="forschung.php?'.SID.'&bau=motor">forschen</a>';
	if ($kosten['motor']['eisen']*($row['motor']*$row['motor']) > $ressis['eisen']){$bauen['motor'] = $zuteuer;}
	if ($kosten['motor']['titan']*($row['motor']*$row['motor']) > $ressis['titan']){$bauen['motor'] = $zuteuer;}
	if ($kosten['motor']['oel']*($row['motor']*$row['motor']) > $ressis['oel']){$bauen['motor'] = $zuteuer;}
	if ($kosten['motor']['uran']*($row['motor']*$row['motor']) > $ressis['uran']){$bauen['motor'] = $zuteuer;}
	if ($kosten['motor']['gold']*($row['motor']*$row['motor']) > $ressis['gold']){$bauen['motor'] = $zuteuer;}
	if ($kosten['motor']['chanje']*($row['motor']*$row['motor']) > $ressis['chanje']){$bauen['motor'] = $zuteuer;}

	$bauen['feuerwaffen'] = '<a href="forschung.php?'.SID.'&bau=feuerwaffen">forschen</a>';
	if ($kosten['feuerwaffen']['eisen']*($row['feuerwaffen']*$row['feuerwaffen']) > $ressis['eisen']){$bauen['feuerwaffen'] = $zuteuer;}
	if ($kosten['feuerwaffen']['titan']*($row['feuerwaffen']*$row['feuerwaffen']) > $ressis['titan']){$bauen['feuerwaffen'] = $zuteuer;}
	if ($kosten['feuerwaffen']['oel']*($row['feuerwaffen']*$row['feuerwaffen']) > $ressis['oel']){$bauen['feuerwaffen'] = $zuteuer;}
	if ($kosten['feuerwaffen']['uran']*($row['feuerwaffen']*$row['feuerwaffen']) > $ressis['uran']){$bauen['feuerwaffen'] = $zuteuer;}
	if ($kosten['feuerwaffen']['gold']*($row['feuerwaffen']*$row['feuerwaffen']) > $ressis['gold']){$bauen['feuerwaffen'] = $zuteuer;}
	if ($kosten['feuerwaffen']['chanje']*($row['feuerwaffen']*$row['feuerwaffen']) > $ressis['chanje']){$bauen['feuerwaffen'] = $zuteuer;}

	$bauen['raketen'] = '<a href="forschung.php?'.SID.'&bau=raketen">forschen</a>';
	if ($kosten['raketen']['eisen']*($row['raketen']*$row['raketen']) > $ressis['eisen']){$bauen['raketen'] = $zuteuer;}
	if ($kosten['raketen']['titan']*($row['raketen']*$row['raketen']) > $ressis['titan']){$bauen['raketen'] = $zuteuer;}
	if ($kosten['raketen']['oel']*($row['raketen']*$row['raketen']) > $ressis['oel']){$bauen['raketen'] = $zuteuer;}
	if ($kosten['raketen']['uran']*($row['raketen']*$row['raketen']) > $ressis['uran']){$bauen['raketen'] = $zuteuer;}
	if ($kosten['raketen']['gold']*($row['raketen']*$row['raketen']) > $ressis['gold']){$bauen['raketen'] = $zuteuer;}
	if ($kosten['raketen']['chanje']*($row['raketen']*$row['raketen']) > $ressis['chanje']){$bauen['raketen'] = $zuteuer;}

	$bauen['sprengstoff'] = '<a href="forschung.php?'.SID.'&bau=sprengstoff">forschen</a>';
	if ($kosten['sprengstoff']['eisen']*($row['sprengstoff']*$row['sprengstoff']) > $ressis['eisen']){$bauen['sprengstoff'] = $zuteuer;}
	if ($kosten['sprengstoff']['titan']*($row['sprengstoff']*$row['sprengstoff']) > $ressis['titan']){$bauen['sprengstoff'] = $zuteuer;}
	if ($kosten['sprengstoff']['oel']*($row['sprengstoff']*$row['sprengstoff']) > $ressis['oel']){$bauen['sprengstoff'] = $zuteuer;}
	if ($kosten['sprengstoff']['uran']*($row['sprengstoff']*$row['sprengstoff']) > $ressis['uran']){$bauen['sprengstoff'] = $zuteuer;}
	if ($kosten['sprengstoff']['gold']*($row['sprengstoff']*$row['sprengstoff']) > $ressis['gold']){$bauen['sprengstoff'] = $zuteuer;}
	if ($kosten['sprengstoff']['chanje']*($row['sprengstoff']*$row['sprengstoff']) > $ressis['chanje']){$bauen['sprengstoff'] = $zuteuer;}

	$bauen['spionage'] = '<a href="forschung.php?'.SID.'&bau=spionage">forschen</a>';
	if ($kosten['spionage']['eisen']*($row['spionage']*$row['spionage']) > $ressis['eisen']){$bauen['spionage'] = $zuteuer;}
	if ($kosten['spionage']['titan']*($row['spionage']*$row['spionage']) > $ressis['titan']){$bauen['spionage'] = $zuteuer;}
	if ($kosten['spionage']['oel']*($row['spionage']*$row['spionage']) > $ressis['oel']){$bauen['spionage'] = $zuteuer;}
	if ($kosten['spionage']['uran']*($row['spionage']*$row['spionage']) > $ressis['uran']){$bauen['spionage'] = $zuteuer;}
	if ($kosten['spionage']['gold']*($row['spionage']*$row['spionage']) > $ressis['gold']){$bauen['spionage'] = $zuteuer;}
	if ($kosten['spionage']['chanje']*($row['spionage']*$row['spionage']) > $ressis['chanje']){$bauen['spionage'] = $zuteuer;}

	$bauen['fuehrung'] = '<a href="forschung.php?'.SID.'&bau=fuehrung">forschen</a>';
	if ($kosten['fuehrung']['eisen']*($row['fuehrung']*$row['fuehrung']) > $ressis['eisen']){$bauen['fuehrung'] = $zuteuer;}
	if ($kosten['fuehrung']['titan']*($row['fuehrung']*$row['fuehrung']) > $ressis['titan']){$bauen['fuehrung'] = $zuteuer;}
	if ($kosten['fuehrung']['oel']*($row['fuehrung']*$row['fuehrung']) > $ressis['oel']){$bauen['fuehrung'] = $zuteuer;}
	if ($kosten['fuehrung']['uran']*($row['fuehrung']*$row['fuehrung']) > $ressis['uran']){$bauen['fuehrung'] = $zuteuer;}
	if ($kosten['fuehrung']['gold']*($row['fuehrung']*$row['fuehrung']) > $ressis['gold']){$bauen['fuehrung'] = $zuteuer;}
	if ($kosten['fuehrung']['chanje']*($row['fuehrung']*$row['fuehrung']) > $ressis['chanje']){$bauen['fuehrung'] = $zuteuer;}

	$bauen['cyborgtechnik'] = '<a href="forschung.php?'.SID.'&bau=cyborgtechnik">forschen</a>';
	if ($kosten['cyborgtechnik']['eisen']*($row['cyborgtechnik']*$row['cyborgtechnik']) > $ressis['eisen']){$bauen['cyborgtechnik'] = $zuteuer;}
	if ($kosten['cyborgtechnik']['titan']*($row['cyborgtechnik']*$row['cyborgtechnik']) > $ressis['titan']){$bauen['cyborgtechnik'] = $zuteuer;}
	if ($kosten['cyborgtechnik']['oel']*($row['cyborgtechnik']*$row['cyborgtechnik']) > $ressis['oel']){$bauen['cyborgtechnik'] = $zuteuer;}
	if ($kosten['cyborgtechnik']['uran']*($row['cyborgtechnik']*$row['cyborgtechnik']) > $ressis['uran']){$bauen['cyborgtechnik'] = $zuteuer;}
	if ($kosten['cyborgtechnik']['gold']*($row['cyborgtechnik']*$row['cyborgtechnik']) > $ressis['gold']){$bauen['cyborgtechnik'] = $zuteuer;}
	if ($kosten['cyborgtechnik']['chanje']*($row['cyborgtechnik']*$row['cyborgtechnik']) > $ressis['chanje']){$bauen['cyborgtechnik'] = $zuteuer;}

	$bauen['minen'] = '<a href="forschung.php?'.SID.'&bau=minen">forschen</a>';
	if ($kosten['minen']['eisen']*($row['minen']*$row['minen']) > $ressis['eisen']){$bauen['minen'] = $zuteuer;}
	if ($kosten['minen']['titan']*($row['minen']*$row['minen']) > $ressis['titan']){$bauen['minen'] = $zuteuer;}
	if ($kosten['minen']['oel']*($row['minen']*$row['minen']) > $ressis['oel']){$bauen['minen'] = $zuteuer;}
	if ($kosten['minen']['uran']*($row['minen']*$row['minen']) > $ressis['uran']){$bauen['minen'] = $zuteuer;}
	if ($kosten['minen']['gold']*($row['minen']*$row['minen']) > $ressis['gold']){$bauen['minen'] = $zuteuer;}
	if ($kosten['minen']['chanje']*($row['minen']*$row['minen']) > $ressis['chanje']){$bauen['minen'] = $zuteuer;}

	$bauen['rad'] = '<a href="forschung.php?'.SID.'&bau=rad">forschen</a>';
	if ($kosten['rad']['eisen']*($row['rad']*$row['rad']) > $ressis['eisen']){$bauen['rad'] = $zuteuer;}
	if ($kosten['rad']['titan']*($row['rad']*$row['rad']) > $ressis['titan']){$bauen['rad'] = $zuteuer;}
	if ($kosten['rad']['oel']*($row['rad']*$row['rad']) > $ressis['oel']){$bauen['rad'] = $zuteuer;}
	if ($kosten['rad']['uran']*($row['rad']*$row['rad']) > $ressis['uran']){$bauen['rad'] = $zuteuer;}
	if ($kosten['rad']['gold']*($row['rad']*$row['rad']) > $ressis['gold']){$bauen['rad'] = $zuteuer;}
	if ($kosten['rad']['chanje']*($row['rad']*$row['rad']) > $ressis['chanje']){$bauen['rad'] = $zuteuer;}

}

$abbruch = 'forschung.php?'.SID.'&abbrechen=1';
$abbrechen_link = 'Derzeitige Forschung <b><a href="#" onclick="check(\'document.location.href=\\\''.$abbruch.'\\\'\', \'Willst du aktuellen Bauvorgang wirklich abbrechen?\')"><font color="#b90101">ABBRECHEN</font></a></b><br />(Alle Ressourcen f&uuml;r diesen Auftrag gehen verloren!)<br />';

if ($row['nextpanzerung'] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row['nextreaktor'] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row['nextpanzerketten'] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row['nextmotor'] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row['nextfeuerwaffen'] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row['nextraketen'] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row['nextsprengstoff'] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row['nextspionage'] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row['nextfuehrung'] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row['nextcyborgtechnik'] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row['nextminen'] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row['nextrad'] != 0){ $abbrechen = $abbrechen_link; }

if ($bauen['reaktor'] == '<center>-</center>' or $bauen['rad'] == '<center>-</center>'){
	 $zuhoch = '<center>-</center>';
} else {
	 $zuhoch = '<font style="color: #57ae4b;">zu hoch</font>';
}
if (($gebaeude['forschungsanlage'] * 2) <= $row['panzerung']-1) {$bauen['panzerung'] = $zuhoch;}
if (($gebaeude['forschungsanlage'] * 2) <= $row['reaktor']-1) {$bauen['reaktor'] = $zuhoch;}
if (($gebaeude['forschungsanlage'] * 2) <= $row['panzerketten']-1) {$bauen['panzerketten'] = $zuhoch;}
if (($gebaeude['forschungsanlage'] * 2) <= $row['motor']-1) {$bauen['motor'] = $zuhoch;}
if (($gebaeude['forschungsanlage'] * 2) <= $row['feuerwaffen']-1) {$bauen['feuerwaffen'] = $zuhoch;}
if (($gebaeude['forschungsanlage'] * 2) <= $row['raketen']-1) {$bauen['raketen'] = $zuhoch;}
if (($gebaeude['forschungsanlage'] * 2) <= $row['sprengstoff']-1) {$bauen['sprengstoff'] = $zuhoch;}
if (($gebaeude['forschungsanlage'] * 2) <= $row['spionage']-1) {$bauen['spionage'] = $zuhoch;}
if (($gebaeude['forschungsanlage'] * 2) <= $row['fuehrung']-1) {$bauen['fuehrung'] = $zuhoch;}
if (($gebaeude['forschungsanlage'] * 2) <= $row['cyborgtechnik']-1) {$bauen['cyborgtechnik'] = $zuhoch;}
if (($gebaeude['forschungsanlage'] * 2) <= $row['minen']-1) {$bauen['minen'] = $zuhoch;}
if (($gebaeude['forschungsanlage'] * 2) <= $row['rad']-1) {$bauen['rad'] = $zuhoch;}


$content .= '<br />	
<table border="1" cellspacing="0" class="sub" style="width:720px">
	<tr>
		<th>
			<b>Forschungsanlage:</b>
		</th>
	</tr>
	<tr>
		<td align="center">
<br />
<table border="1" cellspacing="0" class="standard">
<tr align="center"><th style="width:130px">Forschung</th><th>Level</th><th style="width:55px">Eisen</th><th style="width:55px">Titan</th><th style="width:55px">Oel</th><th style="width:55px">Uran</th><th style="width:55px">Gold</th><th style="width:55px">Chanje</th><th style="width:80px">Dauer</th><th style="width:55px">&nbsp;</th></tr>';

if ($row['panzerung'] < 21) { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=panzerung&'.SID.'&'.SID.'\',400)">Panzerung</a></td><td align="center">'.($row['panzerung']-1).'</td><td align="right">'.$kosten['panzerung']['eisen'] * ($row['panzerung'] * $row['panzerung']).'</td><td align="right">'.$kosten['panzerung']['titan'] * ($row['panzerung']* $row['panzerung']).'</td><td align="right">'.$kosten['panzerung']['oel'] * ($row['panzerung']* $row['panzerung']).'</td><td align="right">'.$kosten['panzerung']['uran'] * ($row['panzerung']* $row['panzerung']).'</td><td align="right">'.$kosten['panzerung']['gold'] * ($row['panzerung']* $row['panzerung']).'</td><td align="right">'.$kosten['panzerung']['chanje'] * ($row['panzerung']* $row['panzerung']).'</td><td align="right">'.time2str($kosten['panzerung']['zeit'] * $row['panzerung']).'</td><td><center>'.$bauen['panzerung'].'</center></td></tr>';}
else { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=panzerung&'.SID.'\',400)">Panzerung</a></td><td align="center">'.($row['panzerung']-1).'</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td><center>max.</center></td></tr>'; }

if ($row['reaktor'] < 21) { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=reaktor&'.SID.'\',400)">Reaktor</a></td><td align="center">'.($row['reaktor']-1).'</td><td align="right">'.$kosten['reaktor']['eisen'] * ($row['reaktor'] * $row['reaktor']).'</td><td align="right">'.$kosten['reaktor']['titan'] * ($row['reaktor']* $row['reaktor']).'</td><td align="right">'.$kosten['reaktor']['oel'] * ($row['reaktor']* $row['reaktor']).'</td><td align="right">'.$kosten['reaktor']['uran'] * ($row['reaktor']* $row['reaktor']).'</td><td align="right">'.$kosten['reaktor']['gold'] * ($row['reaktor']* $row['reaktor']).'</td><td align="right">'.$kosten['reaktor']['chanje'] * ($row['reaktor']* $row['reaktor']).'</td><td align="right">'.time2str($kosten['reaktor']['zeit'] * $row['reaktor']).'</td><td><center>'.$bauen['reaktor'].'</center></td></tr>';}
else { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=reaktor&'.SID.'\',400)">Reaktor</a></td><td align="center">'.($row['reaktor']-1).'</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td><center>max.</center></td></tr>'; }

if ($row['panzerketten'] < 21) { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=panzerketten&'.SID.'\',400)">Panzerketten</a></td><td align="center">'.($row['panzerketten']-1).'</td><td align="right">'.$kosten['panzerketten']['eisen'] * ($row['panzerketten'] * $row['panzerketten']).'</td><td align="right">'.$kosten['panzerketten']['titan'] * ($row['panzerketten']* $row['panzerketten']).'</td><td align="right">'.$kosten['panzerketten']['oel'] * ($row['panzerketten']* $row['panzerketten']).'</td><td align="right">'.$kosten['panzerketten']['uran'] * ($row['panzerketten']* $row['panzerketten']).'</td><td align="right">'.$kosten['panzerketten']['gold'] * ($row['panzerketten']* $row['panzerketten']).'</td><td align="right">'.$kosten['panzerketten']['chanje'] * ($row['panzerketten']* $row['panzerketten']).'</td><td align="right">'.time2str($kosten['panzerketten']['zeit'] * $row['panzerketten']).'</td><td><center>'.$bauen['panzerketten'].'</center></td></tr>';}
else { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=panzerketten&'.SID.'\',400)">Panzerketten</a></td><td align="center">'.($row['panzerketten']-1).'</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td><center>max.</center></td></tr>'; }

if ($row['rad'] < 21) { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=rad&'.SID.'\',400)">Radverst&auml;rkungen</a></td><td align="center">'.($row['rad']-1).'</td><td align="right">'.$kosten['rad']['eisen'] * ($row['rad'] * $row['rad']).'</td><td align="right">'.$kosten['rad']['titan'] * ($row['rad']* $row['rad']).'</td><td align="right">'.$kosten['rad']['oel'] * ($row['rad']* $row['rad']).'</td><td align="right">'.$kosten['rad']['uran'] * ($row['rad']* $row['rad']).'</td><td align="right">'.$kosten['rad']['gold'] * ($row['rad']* $row['rad']).'</td><td align="right">'.$kosten['rad']['chanje'] * ($row['rad']* $row['rad']).'</td><td align="right">'.time2str($kosten['rad']['zeit'] * $row['rad']).'</td><td><center>'.$bauen['rad'].'</center></td></tr>';}
else { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=rad&'.SID.'\',400)">Radverst&auml;rkungen</a></td><td align="center">'.($row['rad']-1).'</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td><center>max.</center></td></tr>'; }

if ($row['motor'] < 21) { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=motor&'.SID.'\',400)">Motor</a></td><td align="center">'.($row['motor']-1).'</td><td align="right">'.$kosten['motor']['eisen'] * ($row['motor'] * $row['motor']).'</td><td align="right">'.$kosten['motor']['titan'] * ($row['motor']* $row['motor']).'</td><td align="right">'.$kosten['motor']['oel'] * ($row['motor']* $row['motor']).'</td><td align="right">'.$kosten['motor']['uran'] * ($row['motor']* $row['motor']).'</td><td align="right">'.$kosten['motor']['gold'] * ($row['motor']* $row['motor']).'</td><td align="right">'.$kosten['motor']['chanje'] * ($row['motor']* $row['motor']).'</td><td align="right">'.time2str($kosten['motor']['zeit'] * $row['motor']).'</td><td><center>'.$bauen['motor'].'</center></td></tr>';}
else { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=motor&'.SID.'\',400)">Motor</a></td><td align="center">'.($row['motor']-1).'</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td><center>max.</center></td></tr>'; }

if ($row['feuerwaffen'] < 21) { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=feuerwaffen&'.SID.'\',400)">Feuerwaffen</a></td><td align="center">'.($row['feuerwaffen']-1).'</td><td align="right">'.$kosten['feuerwaffen']['eisen'] * ($row['feuerwaffen'] * $row['feuerwaffen']).'</td><td align="right">'.$kosten['feuerwaffen']['titan'] * ($row['feuerwaffen']* $row['feuerwaffen']).'</td><td align="right">'.$kosten['feuerwaffen']['oel'] * ($row['feuerwaffen']* $row['feuerwaffen']).'</td><td align="right">'.$kosten['feuerwaffen']['uran'] * ($row['feuerwaffen']* $row['feuerwaffen']).'</td><td align="right">'.$kosten['feuerwaffen']['gold'] * ($row['feuerwaffen']* $row['feuerwaffen']).'</td><td align="right">'.$kosten['feuerwaffen']['chanje'] * ($row['feuerwaffen']* $row['feuerwaffen']).'</td><td align="right">'.time2str($kosten['feuerwaffen']['zeit'] * $row['feuerwaffen']).'</td><td><center>'.$bauen['feuerwaffen'].'</center></td></tr>';}
else { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=feuerwaffen&'.SID.'\',400)">Feuerwaffen</a></td><td align="center">'.($row['feuerwaffen']-1).'</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td><center>max.</center></td></tr>'; }

if ($row['raketen'] < 21) { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=raketen&'.SID.'\',400)">Raketen</a></td><td align="center">'.($row['raketen']-1).'</td><td align="right">'.$kosten['raketen']['eisen'] * ($row['raketen'] * $row['raketen']).'</td><td align="right">'.$kosten['raketen']['titan'] * ($row['raketen']* $row['raketen']).'</td><td align="right">'.$kosten['raketen']['oel'] * ($row['raketen']* $row['raketen']).'</td><td align="right">'.$kosten['raketen']['uran'] * ($row['raketen']* $row['raketen']).'</td><td align="right">'.$kosten['raketen']['gold'] * ($row['raketen']* $row['raketen']).'</td><td align="right">'.$kosten['raketen']['chanje'] * ($row['raketen']* $row['raketen']).'</td><td align="right">'.time2str($kosten['raketen']['zeit'] * $row['raketen']).'</td><td><center>'.$bauen['raketen'].'</center></td></tr>';}
else { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=raketen&'.SID.'\',400)">Raketen</a></td><td align="center">'.($row['raketen']-1).'</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td><center>max.</center></td></tr>'; }

if ($row['sprengstoff'] < 21) { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=sprengstoff&'.SID.'\',400)">Sprengstoff</a></td><td align="center">'.($row['sprengstoff']-1).'</td><td align="right">'.$kosten['sprengstoff']['eisen'] * ($row['sprengstoff'] * $row['sprengstoff']).'</td><td align="right">'.$kosten['sprengstoff']['titan'] * ($row['sprengstoff']* $row['sprengstoff']).'</td><td align="right">'.$kosten['sprengstoff']['oel'] * ($row['sprengstoff']* $row['sprengstoff']).'</td><td align="right">'.$kosten['sprengstoff']['uran'] * ($row['sprengstoff']* $row['sprengstoff']).'</td><td align="right">'.$kosten['sprengstoff']['gold'] * ($row['sprengstoff']* $row['sprengstoff']).'</td><td align="right">'.$kosten['sprengstoff']['chanje'] * ($row['sprengstoff']* $row['sprengstoff']).'</td><td align="right">'.time2str($kosten['sprengstoff']['zeit'] * $row['sprengstoff']).'</td><td><center>'.$bauen['sprengstoff'].'</center></td></tr>';}
else { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=sprengstoff&'.SID.'\',400)">Sprengstoff</a></td><td align="center">'.($row['sprengstoff']-1).'</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td><center>max.</center></td></tr>'; }

if ($row['spionage'] < 21) { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=spionage&'.SID.'\',400)">Spionage</a></td><td align="center">'.($row['spionage']-1).'</td><td align="right">'.$kosten['spionage']['eisen'] * ($row['spionage'] * $row['spionage']).'</td><td align="right">'.$kosten['spionage']['titan'] * ($row['spionage']* $row['spionage']).'</td><td align="right">'.$kosten['spionage']['oel'] * ($row['spionage']* $row['spionage']).'</td><td align="right">'.$kosten['spionage']['uran'] * ($row['spionage']* $row['spionage']).'</td><td align="right">'.$kosten['spionage']['gold'] * ($row['spionage']* $row['spionage']).'</td><td align="right">'.$kosten['spionage']['chanje'] * ($row['spionage']* $row['spionage']).'</td><td align="right">'.time2str($kosten['spionage']['zeit'] * $row['spionage']).'</td><td><center>'.$bauen['spionage'].'</center></td></tr>';}
else { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=spionage&'.SID.'\',400)">Spionage</a></td><td align="center">'.($row['spionage']-1).'</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td><center>max.</center></td></tr>'; }

if ($row['fuehrung'] < 21) { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=fuehrung&'.SID.'\',400)">F&uuml;hrung</a></td><td align="center">'.($row['fuehrung']-1).'</td><td align="right">'.$kosten['fuehrung']['eisen'] * ($row['fuehrung'] * $row['fuehrung']).'</td><td align="right">'.$kosten['fuehrung']['titan'] * ($row['fuehrung']* $row['fuehrung']).'</td><td align="right">'.$kosten['fuehrung']['oel'] * ($row['fuehrung']* $row['fuehrung']).'</td><td align="right">'.$kosten['fuehrung']['uran'] * ($row['fuehrung']* $row['fuehrung']).'</td><td align="right">'.$kosten['fuehrung']['gold'] * ($row['fuehrung']* $row['fuehrung']).'</td><td align="right">'.$kosten['fuehrung']['chanje'] * ($row['fuehrung']* $row['fuehrung']).'</td><td align="right">'.time2str($kosten['fuehrung']['zeit'] * $row['fuehrung']).'</td><td><center>'.$bauen['fuehrung'].'</center></td></tr>';}
else { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=fuehrung&'.SID.'\',400)">F&uuml;hrung</a></td><td align="center">'.($row['fuehrung']-1).'</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td><center>max.</center></td></tr>'; }

// if ($row['cyborgtechnik'] < 21) { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=cyborgtechnik&'.SID.'\',400)">Cyborgtechnik</a></td><td align="center">'.($row['cyborgtechnik']-1).'</td><td align="right">'.$kosten['cyborgtechnik']['eisen'] * ($row['cyborgtechnik'] * $row['cyborgtechnik']).'</td><td align="right">'.$kosten['cyborgtechnik']['titan'] * ($row['cyborgtechnik']* $row['cyborgtechnik']).'</td><td align="right">'.$kosten['cyborgtechnik']['oel'] * ($row['cyborgtechnik']* $row['cyborgtechnik']).'</td><td align="right">'.$kosten['cyborgtechnik']['uran'] * ($row['cyborgtechnik']* $row['cyborgtechnik']).'</td><td align="right">'.$kosten['cyborgtechnik']['gold'] * ($row['cyborgtechnik']* $row['cyborgtechnik']).'</td><td align="right">'.$kosten['cyborgtechnik']['chanje'] * ($row['cyborgtechnik']* $row['cyborgtechnik']).'</td><td align="right">'.time2str($kosten['cyborgtechnik']['zeit'] * $row['cyborgtechnik']).'</td><td><center>'.$bauen['cyborgtechnik'].'</center></td></tr>';}
// else { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=cyborgtechnik&'.SID.'\',400)">Cyborgtechnik</a></td><td align="center">'.($row['cyborgtechnik']-1).'</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td><center>max.</center></td></tr>'; }

if ($row['minen'] < 21) { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=minen&'.SID.'\',400)">Minentechnik</a></td><td align="center">'.($row['minen']-1).'</td><td align="right">'.$kosten['minen']['eisen'] * ($row['minen'] * $row['minen']).'</td><td align="right">'.$kosten['minen']['titan'] * ($row['minen']* $row['minen']).'</td><td align="right">'.$kosten['minen']['oel'] * ($row['minen']* $row['minen']).'</td><td align="right">'.$kosten['minen']['uran'] * ($row['minen']* $row['minen']).'</td><td align="right">'.$kosten['minen']['gold'] * ($row['minen']* $row['minen']).'</td><td align="right">'.$kosten['minen']['chanje'] * ($row['minen']* $row['minen']).'</td><td align="right">'.time2str($kosten['minen']['zeit'] * $row['minen']).'</td><td><center>'.$bauen['minen'].'</center></td></tr>';}
else { $content .= '<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=minen&'.SID.'\',400)">Minentechnik</a></td><td align="center">'.($row['minen']-1).'</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td align="right">-</td><td><center>max.</center></td></tr>'; }

/*
'<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=reaktor&'.SID.'\',400)">Reaktor</a></td><td align="center">'.($row['reaktor']-1).'</td><td align="right">'.$kosten['reaktor']['eisen'] * ($row['reaktor']* $row['reaktor']).'</td><td align="right">'.$kosten['reaktor']['titan'] * ($row['reaktor']* $row['reaktor']).'</td><td align="right">'.$kosten['reaktor']['oel'] * ($row['reaktor']* $row['reaktor']).'</td><td align="right">'.$kosten['reaktor']['uran'] * ($row['reaktor']* $row['reaktor']).'</td><td align="right">'.$kosten['reaktor']['gold'] * ($row['reaktor']* $row['reaktor']).'</td><td align="right">'.$kosten['reaktor']['chanje'] * ($row['reaktor']* $row['reaktor']).'</td><td align="right">'.time2str($kosten['reaktor']['zeit'] * $row['reaktor']).'</td><td><center>'.$bauen['reaktor'].'</center></td></tr>
<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=panzerketten&'.SID.'\',400)">Panzerketten</a></td><td align="center">'.($row['panzerketten']-1).'</td><td align="right">'.$kosten['panzerketten']['eisen'] * ($row['panzerketten']* $row['panzerketten']).'</td><td align="right">'.$kosten['panzerketten']['titan'] * ($row['panzerketten']* $row['panzerketten']).'</td><td align="right">'.$kosten['panzerketten']['oel'] * ($row['panzerketten']* $row['panzerketten']).'</td><td align="right">'.$kosten['panzerketten']['uran'] * ($row['panzerketten']* $row['panzerketten']).'</td><td align="right">'.$kosten['panzerketten']['gold'] * ($row['panzerketten']* $row['panzerketten']).'</td><td align="right">'.$kosten['panzerketten']['chanje'] * ($row['panzerketten']* $row['panzerketten']).'</td><td align="right">'.time2str($kosten['panzerketten']['zeit'] * $row['panzerketten']).'</td><td><center>'.$bauen['panzerketten'].'</center></td></tr>
<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=rad&'.SID.'\',400)">Radverst&auml;rkungen</a></td><td align="center">'.($row['rad']-1).'</td><td align="right">'.$kosten['rad']['eisen'] * ($row['rad']* $row['rad']).'</td><td align="right">'.$kosten['rad']['titan'] * ($row['rad']* $row['rad']).'</td><td align="right">'.$kosten['rad']['oel'] * ($row['rad']* $row['rad']).'</td><td align="right">'.$kosten['rad']['uran'] * ($row['rad']* $row['rad']).'</td><td align="right">'.$kosten['rad']['gold'] * ($row['rad']* $row['rad']).'</td><td align="right">'.$kosten['rad']['chanje'] * ($row['rad']* $row['rad']).'</td><td align="right">'.time2str($kosten['rad']['zeit'] * $row['rad']).'</td><td><center>'.$bauen['rad'].'</center></td></tr>
<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=motor&'.SID.'\',400)">Motor</a></td><td align="center">'.($row['motor']-1).'</td><td align="right">'.$kosten['motor']['eisen'] * ($row['motor']* $row['motor']).'</td><td align="right">'.$kosten['motor']['titan'] * ($row['motor']* $row['motor']).'</td><td align="right">'.$kosten['motor']['oel'] * ($row['motor']* $row['motor']).'</td><td align="right">'.$kosten['motor']['uran'] * ($row['motor']* $row['motor']).'</td><td align="right">'.$kosten['motor']['gold'] * ($row['motor']* $row['motor']).'</td><td align="right">'.$kosten['motor']['chanje'] * ($row['motor']* $row['motor']).'</td><td align="right">'.time2str($kosten['motor']['zeit'] * $row['motor']).'</td><td><center>'.$bauen['motor'].'</center></td></tr>
<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=feuerwaffen&'.SID.'\',400)">Feuerwaffen</a></td><td align="center">'.($row['feuerwaffen']-1).'</td><td align="right">'.$kosten['feuerwaffen']['eisen'] * ($row['feuerwaffen']* $row['feuerwaffen']).'</td><td align="right">'.$kosten['feuerwaffen']['titan'] * ($row['feuerwaffen']* $row['feuerwaffen']).'</td><td align="right">'.$kosten['feuerwaffen']['oel'] * ($row['feuerwaffen']* $row['feuerwaffen']).'</td><td align="right">'.$kosten['feuerwaffen']['uran'] * ($row['feuerwaffen']* $row['feuerwaffen']).'</td><td align="right">'.$kosten['feuerwaffen']['gold'] * ($row['feuerwaffen']* $row['feuerwaffen']).'</td><td align="right">'.$kosten['feuerwaffen']['chanje'] * ($row['feuerwaffen']* $row['feuerwaffen']).'</td><td align="right">'.time2str($kosten['feuerwaffen']['zeit'] * $row['feuerwaffen']).'</td><td><center>'.$bauen['feuerwaffen'].'</center></td></tr>
<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=raketen&'.SID.'\',400)">Raketen</a></td><td align="center">'.($row['raketen']-1).'</td><td align="right">'.$kosten['raketen']['eisen'] * ($row['raketen']* $row['raketen']).'</td><td align="right">'.$kosten['raketen']['titan'] * ($row['raketen']* $row['raketen']).'</td><td align="right">'.$kosten['raketen']['oel'] * ($row['raketen']* $row['raketen']).'</td><td align="right">'.$kosten['raketen']['uran'] * ($row['raketen']* $row['raketen']).'</td><td align="right">'.$kosten['raketen']['gold'] * ($row['raketen']* $row['raketen']).'</td><td align="right">'.$kosten['raketen']['chanje'] * ($row['raketen']* $row['raketen']).'</td><td align="right">'.time2str($kosten['raketen']['zeit'] * $row['raketen']).'</td><td><center>'.$bauen['raketen'].'</center></td></tr>
<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=sprengstoff&'.SID.'\',400)">Sprengstoff</a></td><td align="center">'.($row['sprengstoff']-1).'</td><td align="right">'.$kosten['sprengstoff']['eisen'] * ($row['sprengstoff']* $row['sprengstoff']).'</td><td align="right">'.$kosten['sprengstoff']['titan'] * ($row['sprengstoff']* $row['sprengstoff']).'</td><td align="right">'.$kosten['sprengstoff']['oel'] * ($row['sprengstoff']* $row['sprengstoff']).'</td><td align="right">'.$kosten['sprengstoff']['uran'] * ($row['sprengstoff']* $row['sprengstoff']).'</td><td align="right">'.$kosten['sprengstoff']['gold'] * ($row['sprengstoff']* $row['sprengstoff']).'</td><td align="right">'.$kosten['sprengstoff']['chanje'] * ($row['sprengstoff']* $row['sprengstoff']).'</td><td align="right">'.time2str($kosten['sprengstoff']['zeit'] * $row['sprengstoff']).'</td><td><center>'.$bauen['sprengstoff'].'</center></td></tr>
<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=spionage&'.SID.'\',400)">Spionage</a></td><td align="center">'.($row['spionage']-1).'</td><td align="right">'.$kosten['spionage']['eisen'] * ($row['spionage']* $row['spionage']).'</td><td align="right">'.$kosten['spionage']['titan'] * ($row['spionage']* $row['spionage']).'</td><td align="right">'.$kosten['spionage']['oel'] * ($row['spionage']* $row['spionage']).'</td><td align="right">'.$kosten['spionage']['uran'] * ($row['spionage']* $row['spionage']).'</td><td align="right">'.$kosten['spionage']['gold'] * ($row['spionage']* $row['spionage']).'</td><td align="right">'.$kosten['spionage']['chanje'] * ($row['spionage']* $row['spionage']).'</td><td align="right">'.time2str($kosten['spionage']['zeit'] * $row['spionage']).'</td><td><center>'.$bauen['spionage'].'</center></td></tr>
<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=fuehrung&'.SID.'\',400)">F&uuml;hrungsf&auml;higkeiten</a></td><td align="center">'.($row['fuehrung']-1).'</td><td align="right">'.$kosten['fuehrung']['eisen'] * ($row['fuehrung']* $row['fuehrung']).'</td><td align="right">'.$kosten['fuehrung']['titan'] * ($row['fuehrung']* $row['fuehrung']).'</td><td align="right">'.$kosten['fuehrung']['oel'] * ($row['fuehrung']* $row['fuehrung']).'</td><td align="right">'.$kosten['fuehrung']['uran'] * ($row['fuehrung']* $row['fuehrung']).'</td><td align="right">'.$kosten['fuehrung']['gold'] * ($row['fuehrung']* $row['fuehrung']).'</td><td align="right">'.$kosten['fuehrung']['chanje'] * ($row['fuehrung']* $row['fuehrung']).'</td><td align="right">'.time2str($kosten['fuehrung']['zeit'] * $row['fuehrung']).'</td><td><center>'.$bauen['fuehrung'].'</center></td></tr>
<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=cyborgtechnik&'.SID.'\',400)">Cyborgtechnik</a></td><td align="center">'.($row['cyborgtechnik']-1).'</td><td align="right">'.$kosten['cyborgtechnik']['eisen'] * ($row['cyborgtechnik']* $row['cyborgtechnik']).'</td><td align="right">'.$kosten['cyborgtechnik']['titan'] * ($row['cyborgtechnik']* $row['cyborgtechnik']).'</td><td align="right">'.$kosten['cyborgtechnik']['oel'] * ($row['cyborgtechnik']* $row['cyborgtechnik']).'</td><td align="right">'.$kosten['cyborgtechnik']['uran'] * ($row['cyborgtechnik']* $row['cyborgtechnik']).'</td><td align="right">'.$kosten['cyborgtechnik']['gold'] * ($row['cyborgtechnik']* $row['cyborgtechnik']).'</td><td align="right">'.$kosten['cyborgtechnik']['chanje'] * ($row['cyborgtechnik']* $row['cyborgtechnik']).'</td><td align="right">'.time2str($kosten['cyborgtechnik']['zeit'] * $row['cyborgtechnik']).'</td><td><center>'.$bauen['cyborgtechnik'].'</center></td></tr>
<tr class="standard"><td><a href="javascript:popUp(\'details_forschung.php?id=minen&'.SID.'\',400)">Minentechnik</a></td><td align="center">'.($row['minen']-1).'</td><td align="right">'.$kosten['minen']['eisen'] * ($row['minen']* $row['minen']).'</td><td align="right">'.$kosten['minen']['titan'] * ($row['minen']* $row['minen']).'</td><td align="right">'.$kosten['minen']['oel'] * ($row['minen']* $row['minen']).'</td><td align="right">'.$kosten['minen']['uran'] * ($row['minen']* $row['minen']).'</td><td align="right">'.$kosten['minen']['gold'] * ($row['minen']* $row['minen']).'</td><td align="right">'.$kosten['minen']['chanje'] * ($row['minen']* $row['minen']).'</td><td align="right">'.time2str($kosten['minen']['zeit'] * $row['minen']).'</td><td><center>'.$bauen['minen'].'</center></td></tr>
*/
$content .= '</table>';
$content .= '<br /><br /><span align="center">'.$abbrechen.'</span></center><br /><br /></td></tr></table>';

// generierte seite ausgeben
if ($running) {$onload = 'startCountdown();';}
$content = tag2value("onload",$onload,$content);
echo $content.template('footer');
?>