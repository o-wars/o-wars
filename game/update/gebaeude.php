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

// gebaudepreise:
include 'gebaeude_preise.php';

$ressis = ressistand($_SESSION[user][omni]);

// mit datenbank verbinden
$dbh = db_connect();

if ($_GET[abbrechen] == 1){
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextraketensilo` = '0', `nextnbz` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextmissionszentrum` = '0', `nextagentenzentrum` = '0', `nextraumstation` = '0', `nextrohstofflager` = '0', `nexteisenmine` = '0', `nexttitanmine` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$abbrechen = "Der aktuelle Bauvorgang wurde abgebrochen.";
}

$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION[user][omni]."';";
$selectResult   = mysql_query($select, $dbh);
$row = mysql_fetch_array($selectResult);

$row[basis]++;
$row[forschungsanlage]++;
$row[fabrik]++;
$row[raketensilo]++;
$row[nbz]++;
$row[hangar]++;
$row[fahrwege]++;
$row[missionszentrum]++;
$row[agentenzentrum]++;
$row[raumstation]++;
$row[rohstofflager]++;
$row[eisenmine]++;
$row[titanmine]++;
$row[oelpumpe]++;
$row[uranmine]++;

if ($row[nextbasis] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nextforschungsanlage] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nextfabrik] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nextraketensilo] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nextnbz] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nexthangar] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nextfahrwege] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nextmissionszentrum] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nextagentenzentrum] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nextraumstation] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nextrohstofflager] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nexteisenmine] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nexttitanpumpe] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nextoelpumpe] != 0){ $teuer = 'Du bist bereits am bauen'; }
elseif ($row[nexturanmine] != 0){ $teuer = 'Du bist bereits am bauen'; }

// neue bauten starten
if ($_GET[bau] != ''){
	if ($_GET[bau] == 'basis'){ 
		if ($row['basis'] >= 26){ $teuer = "Du hast den Maximallevel bereits erreicht"; }
		if ($ressis[eisen] < number_format(($kosten[basis][eisen] * ( $row[basis] * $row[basis] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < number_format(($kosten[basis][titan] * ( $row[basis] * $row[basis] )),0)){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < number_format(($kosten[basis][oel]   * ( $row[basis] * $row[basis] )),0)){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < number_format(($kosten[basis][uran]  * ( $row[basis] * $row[basis] )),0)){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < number_format(($kosten[basis][gold]  * ( $row[basis] * $row[basis] )),0)){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < number_format(($kosten[basis][chanje] * ( $row[basis] * $row[basis] )),0)){ $teuer .= "Du hast zu wenig Chanje. "; }
				
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[basis][eisen] * ( $row[basis] * $row[basis] )))."',`titan` = '".($ressis[titan] - ($kosten[basis][titan] * ( $row[basis] * $row[basis] )))."',`oel` = '".($ressis[oel] - ($kosten[basis][oel] * ( $row[basis] * $row[basis] )))."',`uran` = '".($ressis[uran] - ($kosten[basis][uran] * ( $row[basis] * $row[basis] )))."',`gold` = '".($ressis[gold] - ($kosten[basis][gold] * ( $row[basis] * $row[basis] )))."',`chanje` = '".($ressis[chanje] - ($kosten[basis][chanje] * ( $row[basis] * $row[basis] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nextbasis] = (date(U) + ($kosten[basis][zeit] * $row[basis]));
			$select = "UPDATE `gebauede` SET `nextbasis` = '".$row[nextbasis]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[basis][zeit] * $row[basis]))."');";
			$selectResult   = mysql_query($select, $dbh);
		}
	}
	elseif ($_GET[bau] == 'forschungsanlage'){ 
		if ($row['forschungsanlage'] >= 11){ $teuer = "Du hast den Maximallevel bereits erreicht"; }
		if ($ressis[eisen] < number_format(($kosten[forschungsanlage][eisen] * ( $row[forschungsanlage] * $row[forschungsanlage] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[forschungsanlage][titan] * ( $row[forschungsanlage] * $row[forschungsanlage] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[forschungsanlage][oel]   * ( $row[forschungsanlage] * $row[forschungsanlage] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[forschungsanlage][uran]  * ( $row[forschungsanlage] * $row[forschungsanlage] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[forschungsanlage][gold]  * ( $row[forschungsanlage] * $row[forschungsanlage] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[forschungsanlage][chanje] * ( $row[forschungsanlage] * $row[forschungsanlage] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if ($row[basis] < $row[forschungsanlage]) { $teuer .= "Level &uuml;berschreitet Basis Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[forschungsanlage][eisen] * ( $row[forschungsanlage] * $row[forschungsanlage] )))."',`titan` = '".($ressis[titan] - ($kosten[forschungsanlage][titan] * ( $row[forschungsanlage] * $row[forschungsanlage] )))."',`oel` = '".($ressis[oel] - ($kosten[forschungsanlage][oel] * ( $row[forschungsanlage] * $row[forschungsanlage] )))."',`uran` = '".($ressis[uran] - ($kosten[forschungsanlage][uran] * ( $row[forschungsanlage] * $row[forschungsanlage] )))."',`gold` = '".($ressis[gold] - ($kosten[forschungsanlage][gold] * ( $row[forschungsanlage] * $row[forschungsanlage] )))."',`chanje` = '".($ressis[chanje] - ($kosten[forschungsanlage][chanje] * ( $row[forschungsanlage] * $row[forschungsanlage] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nextforschungsanlage] = (date(U) + ($kosten[forschungsanlage][zeit] * $row[forschungsanlage]));
			$select = "UPDATE `gebauede` SET `nextforschungsanlage` = '".$row[nextforschungsanlage]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[forschungsanlage][zeit] * $row[forschungsanlage]))."');";
			$selectResult   = mysql_query($select, $dbh);			
		}
	}
	elseif ($_GET[bau] == 'fabrik'){ 
		if ($ressis[eisen] < number_format(($kosten[fabrik][eisen] * ( $row[fabrik] * $row[fabrik] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[fabrik][titan] * ( $row[fabrik] * $row[fabrik] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[fabrik][oel]   * ( $row[fabrik] * $row[fabrik] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[fabrik][uran]  * ( $row[fabrik] * $row[fabrik] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[fabrik][gold]  * ( $row[fabrik] * $row[fabrik] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[fabrik][chanje] * ( $row[fabrik] * $row[fabrik] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if ($row[basis] < $row[fabrik]) { $teuer .= "Level &uuml;berschreitet Basis Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[fabrik][eisen] * ( $row[fabrik] * $row[fabrik] )))."',`titan` = '".($ressis[titan] - ($kosten[fabrik][titan] * ( $row[fabrik] * $row[fabrik] )))."',`oel` = '".($ressis[oel] - ($kosten[fabrik][oel] * ( $row[fabrik] * $row[fabrik] )))."',`uran` = '".($ressis[uran] - ($kosten[fabrik][uran] * ( $row[fabrik] * $row[fabrik] )))."',`gold` = '".($ressis[gold] - ($kosten[fabrik][gold] * ( $row[fabrik] * $row[fabrik] )))."',`chanje` = '".($ressis[chanje] - ($kosten[fabrik][chanje] * ( $row[fabrik] * $row[fabrik] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nextfabrik] = (date(U) + ($kosten[fabrik][zeit] * $row[fabrik]));
			$select = "UPDATE `gebauede` SET `nextfabrik` = '".$row[nextfabrik]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[fabrik][zeit] * $row[fabrik]))."');";
			$selectResult   = mysql_query($select, $dbh);
		}
	}
	elseif ($_GET[bau] == 'raketensilo'){ 
		if ($ressis[eisen] < number_format(($kosten[raketensilo][eisen] * ( $row[raketensilo] * $row[raketensilo] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[raketensilo][titan] * ( $row[raketensilo] * $row[raketensilo] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[raketensilo][oel]   * ( $row[raketensilo] * $row[raketensilo] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[raketensilo][uran]  * ( $row[raketensilo] * $row[raketensilo] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[raketensilo][gold]  * ( $row[raketensilo] * $row[raketensilo] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[raketensilo][chanje] * ( $row[raketensilo] * $row[raketensilo] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if ($row[basis] < $row[raketensilo]) { $teuer .= "Level &uuml;berschreitet Basis Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[raketensilo][eisen] * ( $row[raketensilo] * $row[raketensilo] )))."',`titan` = '".($ressis[titan] - ($kosten[raketensilo][titan] * ( $row[raketensilo] * $row[raketensilo] )))."',`oel` = '".($ressis[oel] - ($kosten[raketensilo][oel] * ( $row[raketensilo] * $row[raketensilo] )))."',`uran` = '".($ressis[uran] - ($kosten[raketensilo][uran] * ( $row[raketensilo] * $row[raketensilo] )))."',`gold` = '".($ressis[gold] - ($kosten[raketensilo][gold] * ( $row[raketensilo] * $row[raketensilo] )))."',`chanje` = '".($ressis[chanje] - ($kosten[raketensilo][chanje] * ( $row[raketensilo] * $row[raketensilo] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nextraketensilo] = (date(U) + ($kosten[raketensilo][zeit] * $row[raketensilo]));
			$select = "UPDATE `gebauede` SET `nextraketensilo` = '".$row[nextraketensilo]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);

			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[raketensilo][zeit] * $row[raketensilo]))."');";
			$selectResult   = mysql_query($select, $dbh);
		}
	}	
	elseif ($_GET[bau] == 'nbz'){ 
		if ($ressis[eisen] < number_format(($kosten[nbz][eisen] * ( $row[nbz] * $row[nbz] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[nbz][titan] * ( $row[nbz] * $row[nbz] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[nbz][oel]   * ( $row[nbz] * $row[nbz] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[nbz][uran]  * ( $row[nbz] * $row[nbz] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[nbz][gold]  * ( $row[nbz] * $row[nbz] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[nbz][chanje] * ( $row[nbz] * $row[nbz] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if ($row[basis] < $row[nbz]) { $teuer .= "Level &uuml;berschreitet Basis Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[nbz][eisen] * ( $row[nbz] * $row[nbz] )))."',`titan` = '".($ressis[titan] - ($kosten[nbz][titan] * ( $row[nbz] * $row[nbz] )))."',`oel` = '".($ressis[oel] - ($kosten[nbz][oel] * ( $row[nbz] * $row[nbz] )))."',`uran` = '".($ressis[uran] - ($kosten[nbz][uran] * ( $row[nbz] * $row[nbz] )))."',`gold` = '".($ressis[gold] - ($kosten[nbz][gold] * ( $row[nbz] * $row[nbz] )))."',`chanje` = '".($ressis[chanje] - ($kosten[nbz][chanje] * ( $row[nbz] * $row[nbz] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nextnbz] = (date(U) + ($kosten[nbz][zeit] * $row[nbz]));
			$select = "UPDATE `gebauede` SET `nextnbz` = '".$row[nextnbz]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[nbz][zeit] * $row[nbz]))."');";
			$selectResult   = mysql_query($select, $dbh);
		}
	}
	elseif ($_GET[bau] == 'hangar'){ 
		if ($ressis[eisen] < number_format(($kosten[hangar][eisen] * ( $row[hangar] * $row[hangar] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[hangar][titan] * ( $row[hangar] * $row[hangar] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[hangar][oel]   * ( $row[hangar] * $row[hangar] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[hangar][uran]  * ( $row[hangar] * $row[hangar] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[hangar][gold]  * ( $row[hangar] * $row[hangar] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[hangar][chanje] * ( $row[hangar] * $row[hangar] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if ($row[basis] < $row[hangar]) { $teuer .= "Level &uuml;berschreitet Basis Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[hangar][eisen] * ( $row[hangar] * $row[hangar] )))."',`titan` = '".($ressis[titan] - ($kosten[hangar][titan] * ( $row[hangar] * $row[hangar] )))."',`oel` = '".($ressis[oel] - ($kosten[hangar][oel] * ( $row[hangar] * $row[hangar] )))."',`uran` = '".($ressis[uran] - ($kosten[hangar][uran] * ( $row[hangar] * $row[hangar] )))."',`gold` = '".($ressis[gold] - ($kosten[hangar][gold] * ( $row[hangar] * $row[hangar] )))."',`chanje` = '".($ressis[chanje] - ($kosten[hangar][chanje] * ( $row[hangar] * $row[hangar] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nexthangar] = (date(U) + ($kosten[hangar][zeit] * $row[hangar]));
			$select = "UPDATE `gebauede` SET `nexthangar` = '".$row[nexthangar]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);

			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[hangar][zeit] * $row[hangar]))."');";
			$selectResult   = mysql_query($select, $dbh);		
		}
	}	
	elseif ($_GET[bau] == 'fahrwege'){ 
		if ($ressis[eisen] < number_format(($kosten[fahrwege][eisen] * ( $row[fahrwege] * $row[fahrwege] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[fahrwege][titan] * ( $row[fahrwege] * $row[fahrwege] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[fahrwege][oel]   * ( $row[fahrwege] * $row[fahrwege] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[fahrwege][uran]  * ( $row[fahrwege] * $row[fahrwege] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[fahrwege][gold]  * ( $row[fahrwege] * $row[fahrwege] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[fahrwege][chanje] * ( $row[fahrwege] * $row[fahrwege] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if ($row[basis] < $row[fahrwege]) { $teuer .= "Level &uuml;berschreitet Basis Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[fahrwege][eisen] * ( $row[fahrwege] * $row[fahrwege] )))."',`titan` = '".($ressis[titan] - ($kosten[fahrwege][titan] * ( $row[fahrwege] * $row[fahrwege] )))."',`oel` = '".($ressis[oel] - ($kosten[fahrwege][oel] * ( $row[fahrwege] * $row[fahrwege] )))."',`uran` = '".($ressis[uran] - ($kosten[fahrwege][uran] * ( $row[fahrwege] * $row[fahrwege] )))."',`gold` = '".($ressis[gold] - ($kosten[fahrwege][gold] * ( $row[fahrwege] * $row[fahrwege] )))."',`chanje` = '".($ressis[chanje] - ($kosten[fahrwege][chanje] * ( $row[fahrwege] * $row[fahrwege] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nextfahrwege] = (date(U) + ($kosten[fahrwege][zeit] * $row[fahrwege]));
			$select = "UPDATE `gebauede` SET `nextfahrwege` = '".$row[nextfahrwege]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[fahrwege][zeit] * $row[fahrwege]))."');";
			$selectResult   = mysql_query($select, $dbh);
		}
	}
	elseif ($_GET[bau] == 'missionszentrum'){ 
		if ($ressis[eisen] < number_format(($kosten[missionszentrum][eisen] * ( $row[missionszentrum] * $row[missionszentrum] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[missionszentrum][titan] * ( $row[missionszentrum] * $row[missionszentrum] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[missionszentrum][oel]   * ( $row[missionszentrum] * $row[missionszentrum] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[missionszentrum][uran]  * ( $row[missionszentrum] * $row[missionszentrum] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[missionszentrum][gold]  * ( $row[missionszentrum] * $row[missionszentrum] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[missionszentrum][chanje] * ( $row[missionszentrum] * $row[missionszentrum] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if ($row[basis] < $row[missionszentrum]) { $teuer .= "Level &uuml;berschreitet Basis Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[missionszentrum][eisen] * ( $row[missionszentrum] * $row[missionszentrum] )))."',`titan` = '".($ressis[titan] - ($kosten[missionszentrum][titan] * ( $row[missionszentrum] * $row[missionszentrum] )))."',`oel` = '".($ressis[oel] - ($kosten[missionszentrum][oel] * ( $row[missionszentrum] * $row[missionszentrum] )))."',`uran` = '".($ressis[uran] - ($kosten[missionszentrum][uran] * ( $row[missionszentrum] * $row[missionszentrum] )))."',`gold` = '".($ressis[gold] - ($kosten[missionszentrum][gold] * ( $row[missionszentrum] * $row[missionszentrum] )))."',`chanje` = '".($ressis[chanje] - ($kosten[missionszentrum][chanje] * ( $row[missionszentrum] * $row[missionszentrum] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nextmissionszentrum] = (date(U) + ($kosten[missionszentrum][zeit] * $row[missionszentrum]));
			$select = "UPDATE `gebauede` SET `nextmissionszentrum` = '".$row[nextmissionszentrum]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[missionszentrum][zeit] * $row[missionszentrum]))."');";
			$selectResult   = mysql_query($select, $dbh);
		}
	}
	elseif ($_GET[bau] == 'agentenzentrum'){ 
		if ($ressis[eisen] < number_format(($kosten[agentenzentrum][eisen] * ( $row[agentenzentrum] * $row[agentenzentrum] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[agentenzentrum][titan] * ( $row[agentenzentrum] * $row[agentenzentrum] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[agentenzentrum][oel]   * ( $row[agentenzentrum] * $row[agentenzentrum] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[agentenzentrum][uran]  * ( $row[agentenzentrum] * $row[agentenzentrum] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[agentenzentrum][gold]  * ( $row[agentenzentrum] * $row[agentenzentrum] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[agentenzentrum][chanje] * ( $row[agentenzentrum] * $row[agentenzentrum] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if ($row[basis] < $row[agentenzentrum]) { $teuer .= "Level &uuml;berschreitet Basis Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[agentenzentrum][eisen] * ( $row[agentenzentrum] * $row[agentenzentrum] )))."',`titan` = '".($ressis[titan] - ($kosten[agentenzentrum][titan] * ( $row[agentenzentrum] * $row[agentenzentrum] )))."',`oel` = '".($ressis[oel] - ($kosten[agentenzentrum][oel] * ( $row[agentenzentrum] * $row[agentenzentrum] )))."',`uran` = '".($ressis[uran] - ($kosten[agentenzentrum][uran] * ( $row[agentenzentrum] * $row[agentenzentrum] )))."',`gold` = '".($ressis[gold] - ($kosten[agentenzentrum][gold] * ( $row[agentenzentrum] * $row[agentenzentrum] )))."',`chanje` = '".($ressis[chanje] - ($kosten[agentenzentrum][chanje] * ( $row[agentenzentrum] * $row[agentenzentrum] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nextagentenzentrum] = (date(U) + ($kosten[agentenzentrum][zeit] * $row[agentenzentrum]));
			$select = "UPDATE `gebauede` SET `nextagentenzentrum` = '".$row[nextagentenzentrum]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[agentenzentrum][zeit] * $row[agentenzentrum]))."');";
			$selectResult   = mysql_query($select, $dbh);
		}
	}
	elseif ($_GET[bau] == 'raumstation'){ 
		if ($row['raumstation'] >= 11){ $teuer = "Du hast den Maximallevel bereits erreicht"; }
		if ($ressis[eisen] < number_format(($kosten[raumstation][eisen] * ( $row[raumstation] * $row[raumstation] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[raumstation][titan] * ( $row[raumstation] * $row[raumstation] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[raumstation][oel]   * ( $row[raumstation] * $row[raumstation] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[raumstation][uran]  * ( $row[raumstation] * $row[raumstation] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[raumstation][gold]  * ( $row[raumstation] * $row[raumstation] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[raumstation][chanje] * ( $row[raumstation] * $row[raumstation] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if ($row[basis] < $row[raumstation]) { $teuer .= "Level &uuml;berschreitet Basis Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[raumstation][eisen] * ( $row[raumstation] * $row[raumstation] )))."',`titan` = '".($ressis[titan] - ($kosten[raumstation][titan] * ( $row[raumstation] * $row[raumstation] )))."',`oel` = '".($ressis[oel] - ($kosten[raumstation][oel] * ( $row[raumstation] * $row[raumstation] )))."',`uran` = '".($ressis[uran] - ($kosten[raumstation][uran] * ( $row[raumstation] * $row[raumstation] )))."',`gold` = '".($ressis[gold] - ($kosten[raumstation][gold] * ( $row[raumstation] * $row[raumstation] )))."',`chanje` = '".($ressis[chanje] - ($kosten[raumstation][chanje] * ( $row[raumstation] * $row[raumstation] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nextraumstation] = (date(U) + ($kosten[raumstation][zeit] * $row[raumstation]));
			$select = "UPDATE `gebauede` SET `nextraumstation` = '".$row[nextraumstation]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[raumstation][zeit] * $row[raumstation]))."');";
			$selectResult   = mysql_query($select, $dbh);
		}
	}
	elseif ($_GET[bau] == 'rohstofflager'){ 
		if ($ressis[eisen] < number_format(($kosten[rohstofflager][eisen] * ( $row[rohstofflager] * $row[rohstofflager] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[rohstofflager][titan] * ( $row[rohstofflager] * $row[rohstofflager] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[rohstofflager][oel]   * ( $row[rohstofflager] * $row[rohstofflager] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[rohstofflager][uran]  * ( $row[rohstofflager] * $row[rohstofflager] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[rohstofflager][gold]  * ( $row[rohstofflager] * $row[rohstofflager] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[rohstofflager][chanje] * ( $row[rohstofflager] * $row[rohstofflager] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		if ($row[basis] < $row[rohstofflager]) { $teuer .= "Level &uuml;berschreitet Basis Level. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[rohstofflager][eisen] * ( $row[rohstofflager] * $row[rohstofflager] )))."',`titan` = '".($ressis[titan] - ($kosten[rohstofflager][titan] * ( $row[rohstofflager] * $row[rohstofflager] )))."',`oel` = '".($ressis[oel] - ($kosten[rohstofflager][oel] * ( $row[rohstofflager] * $row[rohstofflager] )))."',`uran` = '".($ressis[uran] - ($kosten[rohstofflager][uran] * ( $row[rohstofflager] * $row[rohstofflager] )))."',`gold` = '".($ressis[gold] - ($kosten[rohstofflager][gold] * ( $row[rohstofflager] * $row[rohstofflager] )))."',`chanje` = '".($ressis[chanje] - ($kosten[rohstofflager][chanje] * ( $row[rohstofflager] * $row[rohstofflager] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nextrohstofflager] = (date(U) + ($kosten[rohstofflager][zeit] * $row[rohstofflager]));
			$select = "UPDATE `gebauede` SET `nextrohstofflager` = '".$row[nextrohstofflager]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[rohstofflager][zeit] * $row[rohstofflager]))."');";
			$selectResult   = mysql_query($select, $dbh);
		}
	}
	elseif ($_GET[bau] == 'eisenmine'){ 
		if ($ressis[eisen] < number_format(($kosten[eisenmine][eisen] * ( $row[eisenmine] * $row[eisenmine] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[eisenmine][titan] * ( $row[eisenmine] * $row[eisenmine] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[eisenmine][oel]   * ( $row[eisenmine] * $row[eisenmine] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[eisenmine][uran]  * ( $row[eisenmine] * $row[eisenmine] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[eisenmine][gold]  * ( $row[eisenmine] * $row[eisenmine] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[eisenmine][chanje] * ( $row[eisenmine] * $row[eisenmine] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
				
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[eisenmine][eisen] * ( $row[eisenmine] * $row[eisenmine] )))."',`titan` = '".($ressis[titan] - ($kosten[eisenmine][titan] * ( $row[eisenmine] * $row[eisenmine] )))."',`oel` = '".($ressis[oel] - ($kosten[eisenmine][oel] * ( $row[eisenmine] * $row[eisenmine] )))."',`uran` = '".($ressis[uran] - ($kosten[eisenmine][uran] * ( $row[eisenmine] * $row[eisenmine] )))."',`gold` = '".($ressis[gold] - ($kosten[eisenmine][gold] * ( $row[eisenmine] * $row[eisenmine] )))."',`chanje` = '".($ressis[chanje] - ($kosten[eisenmine][chanje] * ( $row[eisenmine] * $row[eisenmine] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nexteisenmine] = (date(U) + ($kosten[eisenmine][zeit] * $row[eisenmine]));
			$select = "UPDATE `gebauede` SET `nexteisenmine` = '".$row[nexteisenmine]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[eisenmine][zeit] * $row[eisenmine]))."');";
			$selectResult   = mysql_query($select, $dbh);
		}
	}
	elseif ($_GET[bau] == 'titanmine'){ 
		if ($ressis[eisen] < number_format(($kosten[titanmine][eisen] * ( $row[titanmine] * $row[titanmine] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[titanmine][titan] * ( $row[titanmine] * $row[titanmine] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[titanmine][oel]   * ( $row[titanmine] * $row[titanmine] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[titanmine][uran]  * ( $row[titanmine] * $row[titanmine] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[titanmine][gold]  * ( $row[titanmine] * $row[titanmine] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[titanmine][chanje] * ( $row[titanmine] * $row[titanmine] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[titanmine][eisen] * ( $row[titanmine] * $row[titanmine] )))."',`titan` = '".($ressis[titan] - ($kosten[titanmine][titan] * ( $row[titanmine] * $row[titanmine] )))."',`oel` = '".($ressis[oel] - ($kosten[titanmine][oel] * ( $row[titanmine] * $row[titanmine] )))."',`uran` = '".($ressis[uran] - ($kosten[titanmine][uran] * ( $row[titanmine] * $row[titanmine] )))."',`gold` = '".($ressis[gold] - ($kosten[titanmine][gold] * ( $row[titanmine] * $row[titanmine] )))."',`chanje` = '".($ressis[chanje] - ($kosten[titanmine][chanje] * ( $row[titanmine] * $row[titanmine] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nexttitanmine] = (date(U) + ($kosten[titanmine][zeit] * $row[titanmine]));
			$select = "UPDATE `gebauede` SET `nexttitanmine` = '".$row[nexttitanmine]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[titanmine][zeit] * $row[titanmine]))."');";
			$selectResult   = mysql_query($select, $dbh);
		}
	}
	elseif ($_GET[bau] == 'oelpumpe'){ 
		if ($ressis[eisen] < number_format(($kosten[oelpumpe][eisen] * ( $row[oelpumpe] * $row[oelpumpe] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[oelpumpe][titan] * ( $row[oelpumpe] * $row[oelpumpe] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[oelpumpe][oel]   * ( $row[oelpumpe] * $row[oelpumpe] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[oelpumpe][uran]  * ( $row[oelpumpe] * $row[oelpumpe] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[oelpumpe][gold]  * ( $row[oelpumpe] * $row[oelpumpe] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[oelpumpe][chanje] * ( $row[oelpumpe] * $row[oelpumpe] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[oelpumpe][eisen] * ( $row[oelpumpe] * $row[oelpumpe] )))."',`titan` = '".($ressis[titan] - ($kosten[oelpumpe][titan] * ( $row[oelpumpe] * $row[oelpumpe] )))."',`oel` = '".($ressis[oel] - ($kosten[oelpumpe][oel] * ( $row[oelpumpe] * $row[oelpumpe] )))."',`uran` = '".($ressis[uran] - ($kosten[oelpumpe][uran] * ( $row[oelpumpe] * $row[oelpumpe] )))."',`gold` = '".($ressis[gold] - ($kosten[oelpumpe][gold] * ( $row[oelpumpe] * $row[oelpumpe] )))."',`chanje` = '".($ressis[chanje] - ($kosten[oelpumpe][chanje] * ( $row[oelpumpe] * $row[oelpumpe] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nextoelpumpe] = (date(U) + ($kosten[oelpumpe][zeit] * $row[oelpumpe]));
			$select = "UPDATE `gebauede` SET `nextoelpumpe` = '".$row[nextoelpumpe]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[oelpumpe][zeit] * $row[oelpumpe]))."');";
			$selectResult   = mysql_query($select, $dbh);
		}
	}
	elseif ($_GET[bau] == 'uranmine'){ 
		if ($ressis[eisen] < number_format(($kosten[uranmine][eisen] * ( $row[uranmine] * $row[uranmine] )),0)){ $teuer  = "Du hast zu wenig Eisen. "; }
		if ($ressis[titan] < ($kosten[uranmine][titan] * ( $row[uranmine] * $row[uranmine] ))){ $teuer .= "Du hast zu wenig Titan. "; }
		if ($ressis[oel]   < ($kosten[uranmine][oel]   * ( $row[uranmine] * $row[uranmine] ))){ $teuer .= "Du hast zu wenig Oel. "; }
		if ($ressis[uran]  < ($kosten[uranmine][uran]  * ( $row[uranmine] * $row[uranmine] ))){ $teuer .= "Du hast zu wenig Uran. "; }
		if ($ressis[gold]  < ($kosten[uranmine][gold]  * ( $row[uranmine] * $row[uranmine] ))){ $teuer .= "Du hast zu wenig Gold. "; }
		if ($ressis[chanje] < ($kosten[uranmine][chanje] * ( $row[uranmine] * $row[uranmine] ))){ $teuer .= "Du hast zu wenig Chanje. "; }
		
		if ($teuer) {$content .= '<span style="font-size: 12px";>'.$teuer.'</span><br /<br />';}
		else {
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis[eisen] - ($kosten[uranmine][eisen] * ( $row[uranmine] * $row[uranmine] )))."',`titan` = '".($ressis[titan] - ($kosten[uranmine][titan] * ( $row[uranmine] * $row[uranmine] )))."',`oel` = '".($ressis[oel] - ($kosten[uranmine][oel] * ( $row[uranmine] * $row[uranmine] )))."',`uran` = '".($ressis[uran] - ($kosten[uranmine][uran] * ( $row[uranmine] * $row[uranmine] )))."',`gold` = '".($ressis[gold] - ($kosten[uranmine][gold] * ( $row[uranmine] * $row[uranmine] )))."',`chanje` = '".($ressis[chanje] - ($kosten[uranmine][chanje] * ( $row[uranmine] * $row[uranmine] )))."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$row[nexturanmine] = (date(U) + ($kosten[uranmine][zeit] * $row[uranmine]));
			$select = "UPDATE `gebauede` SET `nexturanmine` = '".$row[nexturanmine]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
			$selectResult   = mysql_query($select, $dbh);
			
			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '3', '".$_SESSION['user']['omni']."', '".(date(U) + ($kosten[uranmine][zeit] * $row[uranmine]))."');";
			$selectResult   = mysql_query($select, $dbh);
		}
	}
}

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION[user][omni]);
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

///*
if ($row[nextbasis] <= date(U) AND $row[nextbasis] != 0){
	$select = "UPDATE `gebauede` SET `basis` = '".$row[basis]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nextbasis] = 0;
	$row[basis]++;
}
elseif ($row[nextforschungsanlage] <= date(U) AND $row[nextforschungsanlage] != 0){
	$select = "UPDATE `gebauede` SET `forschungsanlage` = '".$row[forschungsanlage]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
}
elseif ($row[nextfabrik] <= date(U) AND $row[nextfabrik] != 0){
	$select = "UPDATE `gebauede` SET `fabrik` = '".$row[fabrik]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nextfabrik] = 0;
	$row[fabrik]++;
}
elseif ($row[nextraketensilo] <= date(U) AND $row[nextraketensilo] != 0){
	$select = "UPDATE `gebauede` SET `raketensilo` = '".$row[raketensilo]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nextraketensilo] = 0;
	$row[raketensilo]++;
}
elseif ($row[nextnbz] <= date(U) AND $row[nextnbz] != 0){
	$select = "UPDATE `gebauede` SET `nbz` = '".$row[nbz]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nextnbz] = 0;
	$row[nbz]++;
}
elseif ($row[nexthangar] <= date(U) AND $row[nexthangar] != 0){
	$select = "UPDATE `gebauede` SET `hangar` = '".$row[hangar]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nexthangar] = 0;
	$row[hangar]++;
}
elseif ($row[nextfahrwege] <= date(U) AND $row[nextfahrwege] != 0){
	$select = "UPDATE `gebauede` SET `fahrwege` = '".$row[fahrwege]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nextfahrwege] = 0;
	$row[fahrwege]++;
}
elseif ($row[nextmissionszentrum] <= date(U) AND $row[nextmissionszentrum] != 0){
	$select = "UPDATE `gebauede` SET `missionszentrum` = '".$row[missionszentrum]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nextmissionszentrum] = 0;
	$row[missionszentrum]++;
}
elseif ($row[nextagentenzentrum] <= date(U) AND $row[nextagentenzentrum] != 0){
	$select = "UPDATE `gebauede` SET `agentenzentrum` = '".$row[agentenzentrum]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nextagentenzentrum] = 0;
	$row[agentenzentrum]++;
}
elseif ($row[nextraumstation] <= date(U) AND $row[nextraumstation] != 0){
	$select = "UPDATE `gebauede` SET `raumstation` = '".$row[raumstation]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nextraumstation] = 0;
	$row[raumstation]++;
}
elseif ($row[nextrohstofflager] <= date(U) AND $row[nextrohstofflager] != 0){
	$select = "UPDATE `gebauede` SET `rohstofflager` = '".$row[rohstofflager]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nextrohstofflager] = 0;
	$row[rohstofflager]++;
}
elseif ($row[nexteisenmine] <= date(U) AND $row[nexteisenmine] != 0){
	$select = "UPDATE `gebauede` SET `eisenmine` = '".$row[eisenmine]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nexteisenmine] = 0;
	$row[eisenmine]++;
}
elseif ($row[nexttitanmine] <= date(U) AND $row[nexttitanmine] != 0){
	$select = "UPDATE `gebauede` SET `titanmine` = '".$row[titanmine]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nexttitanmine] = 0;
	$row[titanmine]++;
}
elseif ($row[nextoelpumpe] <= date(U) AND $row[nextoelpumpe] != 0){
	$select = "UPDATE `gebauede` SET `oelpumpe` = '".$row[oelpumpe]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nextoelpumpe] = 0;
	$row[oelpumpe]++;
}
elseif ($row[nexturanmine] <= date(U) AND $row[nexturanmine] != 0){
	$select = "UPDATE `gebauede` SET `uranmine` = '".$row[uranmine]."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
	$selectResult   = mysql_query($select, $dbh);
	$row[nexturanmine] = 0;
	$row[uranmine]++;
}
//*/
if ($row[nextbasis] != 0){
	$running = 1; 
	$content .= str_replace("%restzeit%",$row[nextbasis]-date('U'),$script);
	$bauen[basis] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}
elseif ($row[nextforschungsanlage] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nextforschungsanlage]-date('U'),$script);
	$bauen[forschungsanlage] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[basis] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}
elseif ($row[nextfabrik] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nextfabrik]-date('U'),$script);
	$bauen[fabrik] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[basis] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}
elseif ($row[nextraketensilo] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nextraketensilo]-date('U'),$script);
	$bauen[raketensilo] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[basis] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}
elseif ($row[nextnbz] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nextnbz]-date('U'),$script);
	$bauen[nbz] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[basis] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}
elseif ($row[nexthangar] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nexthangar]-date('U'),$script);
	$bauen[hangar] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[basis] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}
elseif ($row[nextfahrwege] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nextfahrwege]-date('U'),$script);
	$bauen[fahrwege] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[basis] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}
elseif ($row[nextmissionszentrum] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nextmissionszentrum]-date('U'),$script);
	$bauen[missionszentrum] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[basis] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}
elseif ($row[nextagentenzentrum] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nextagentenzentrum]-date('U'),$script);
	$bauen[agentenzentrum] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[basis] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}
elseif ($row[nextraumstation] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nextraumstation]-date('U'),$script);
	$bauen[raumstation] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[basis] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}
elseif ($row[nextrohstofflager] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nextrohstofflager]-date('U'),$script);
	$bauen[rohstofflager] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[basis] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}
elseif ($row[nexteisenmine] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nexteisenmine]-date('U'),$script);
	$bauen[eisenmine] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[basis] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}

elseif ($row[nexttitanmine] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nexttitanmine]-date('U'),$script);
	$bauen[titanmine] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[basis] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}
elseif ($row[nextoelpumpe] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nextoelpumpe]-date('U'),$script);
	$bauen[oelpumpe] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[basis] = '<center>-</center>'; 
	$bauen[uranmine] = '<center>-</center>'; 
}
elseif ($row[nexturanmine] != 0){
	$running = 1;
	$content .= str_replace("%restzeit%",$row[nexturanmine]-date('U'),$script);
	$bauen[uranmine] = '<div align="center" id="verbleibend">loading...</div>'; 
	$bauen[forschungsanlage] = '<center>-</center>'; 
	$bauen[fabrik] = '<center>-</center>'; 
	$bauen[nbz] = '<center>-</center>'; 
	$bauen[raketensilo] = '<center>-</center>'; 
	$bauen[fahrwege] = '<center>-</center>'; 
	$bauen[hangar] = '<center>-</center>'; 
	$bauen[missionszentrum] = '<center>-</center>'; 
	$bauen[agentenzentrum] = '<center>-</center>'; 
	$bauen[raumstation] = '<center>-</center>'; 
	$bauen[rohstofflager] = '<center>-</center>'; 
	$bauen[eisenmine] = '<center>-</center>'; 
	$bauen[titanmine] = '<center>-</center>'; 
	$bauen[oelpumpe] = '<center>-</center>'; 
	$bauen[basis] = '<center>-</center>'; 
}
else {
	$zuteuer = '<font style="color: red;">zu teuer</font>';
	$bauen[basis] = '<a href="gebaeude.php?'.SID.'&bau=basis">bauen</a>';
	if ($row['basis'] >= 26){ $bauen[basis] = "Maximal"; }
	if ($kosten[basis][eisen]*($row[basis]*$row[basis]) > $ressis[eisen]){$bauen[basis] = $zuteuer;}
	if ($kosten[basis][titan]*($row[basis]*$row[basis]) > $ressis[titan]){$bauen[basis] = $zuteuer;}
	if ($kosten[basis][oel]*($row[basis]*$row[basis]) > $ressis[oel]){$bauen[basis] = $zuteuer;}
	if ($kosten[basis][uran]*($row[basis]*$row[basis]) > $ressis[uran]){$bauen[basis] = $zuteuer;}
	if ($kosten[basis][gold]*($row[basis]*$row[basis]) > $ressis[gold]){$bauen[basis] = $zuteuer;}
	if ($kosten[basis][chanje]*($row[basis]*$row[basis]) > $ressis[chanje]){$bauen[basis] = $zuteuer;}

	$bauen[forschungsanlage] = '<a href="gebaeude.php?'.SID.'&bau=forschungsanlage">bauen</a>';
	if ($row['forschungsanlage'] >= 11){ $bauen[forschungsanlage] = "Maximal"; }
	if ($kosten[forschungsanlage][eisen]*($row[forschungsanlage]*$row[forschungsanlage]) > $ressis[eisen]){$bauen[forschungsanlage] = $zuteuer;}
	if ($kosten[forschungsanlage][titan]*($row[forschungsanlage]*$row[forschungsanlage]) > $ressis[titan]){$bauen[forschungsanlage] = $zuteuer;}
	if ($kosten[forschungsanlage][oel]*($row[forschungsanlage]*$row[forschungsanlage]) > $ressis[oel]){$bauen[forschungsanlage] = $zuteuer;}
	if ($kosten[forschungsanlage][uran]*($row[forschungsanlage]*$row[forschungsanlage]) > $ressis[uran]){$bauen[forschungsanlage] = $zuteuer;}
	if ($kosten[forschungsanlage][gold]*($row[forschungsanlage]*$row[forschungsanlage]) > $ressis[gold]){$bauen[forschungsanlage] = $zuteuer;}
	if ($kosten[forschungsanlage][chanje]*($row[forschungsanlage]*$row[forschungsanlage]) > $ressis[chanje]){$bauen[forschungsanlage] = $zuteuer;}

	$bauen[fabrik] = '<a href="gebaeude.php?'.SID.'&bau=fabrik">bauen</a>';
	if ($kosten[fabrik][eisen]*($row[fabrik]*$row[fabrik]) > $ressis[eisen]){$bauen[fabrik] = $zuteuer;}
	if ($kosten[fabrik][titan]*($row[fabrik]*$row[fabrik]) > $ressis[titan]){$bauen[fabrik] = $zuteuer;}
	if ($kosten[fabrik][oel]*($row[fabrik]*$row[fabrik]) > $ressis[oel]){$bauen[fabrik] = $zuteuer;}
	if ($kosten[fabrik][uran]*($row[fabrik]*$row[fabrik]) > $ressis[uran]){$bauen[fabrik] = $zuteuer;}
	if ($kosten[fabrik][gold]*($row[fabrik]*$row[fabrik]) > $ressis[gold]){$bauen[fabrik] = $zuteuer;}
	if ($kosten[fabrik][chanje]*($row[fabrik]*$row[fabrik]) > $ressis[chanje]){$bauen[fabrik] = $zuteuer;}

	$bauen[raketensilo] = '<a href="gebaeude.php?'.SID.'&bau=raketensilo">bauen</a>';
	if ($kosten[raketensilo][eisen]*($row[raketensilo]*$row[raketensilo]) > $ressis[eisen]){$bauen[raketensilo] = $zuteuer;}
	if ($kosten[raketensilo][titan]*($row[raketensilo]*$row[raketensilo]) > $ressis[titan]){$bauen[raketensilo] = $zuteuer;}
	if ($kosten[raketensilo][oel]*($row[raketensilo]*$row[raketensilo]) > $ressis[oel]){$bauen[raketensilo] = $zuteuer;}
	if ($kosten[raketensilo][uran]*($row[raketensilo]*$row[raketensilo]) > $ressis[uran]){$bauen[raketensilo] = $zuteuer;}
	if ($kosten[raketensilo][gold]*($row[raketensilo]*$row[raketensilo]) > $ressis[gold]){$bauen[raketensilo] = $zuteuer;}
	if ($kosten[raketensilo][chanje]*($row[raketensilo]*$row[raketensilo]) > $ressis[chanje]){$bauen[raketensilo] = $zuteuer;}

	$bauen[nbz] = '<a href="gebaeude.php?'.SID.'&bau=nbz">bauen</a>';
	if ($kosten[nbz][eisen]*($row[nbz]*$row[nbz]) > $ressis[eisen]){$bauen[nbz] = $zuteuer;}
	if ($kosten[nbz][titan]*($row[nbz]*$row[nbz]) > $ressis[titan]){$bauen[nbz] = $zuteuer;}
	if ($kosten[nbz][oel]*($row[nbz]*$row[nbz]) > $ressis[oel]){$bauen[nbz] = $zuteuer;}
	if ($kosten[nbz][uran]*($row[nbz]*$row[nbz]) > $ressis[uran]){$bauen[nbz] = $zuteuer;}
	if ($kosten[nbz][gold]*($row[nbz]*$row[nbz]) > $ressis[gold]){$bauen[nbz] = $zuteuer;}
	if ($kosten[nbz][chanje]*($row[nbz]*$row[nbz]) > $ressis[chanje]){$bauen[nbz] = $zuteuer;}

	$bauen[hangar] = '<a href="gebaeude.php?'.SID.'&bau=hangar">bauen</a>';
	if ($kosten[hangar][eisen]*($row[hangar]*$row[hangar]) > $ressis[eisen]){$bauen[hangar] = $zuteuer;}
	if ($kosten[hangar][titan]*($row[hangar]*$row[hangar]) > $ressis[titan]){$bauen[hangar] = $zuteuer;}
	if ($kosten[hangar][oel]*($row[hangar]*$row[hangar]) > $ressis[oel]){$bauen[hangar] = $zuteuer;}
	if ($kosten[hangar][uran]*($row[hangar]*$row[hangar]) > $ressis[uran]){$bauen[hangar] = $zuteuer;}
	if ($kosten[hangar][gold]*($row[hangar]*$row[hangar]) > $ressis[gold]){$bauen[hangar] = $zuteuer;}
	if ($kosten[hangar][chanje]*($row[hangar]*$row[hangar]) > $ressis[chanje]){$bauen[hangar] = $zuteuer;}

	$bauen[fahrwege] = '<a href="gebaeude.php?'.SID.'&bau=fahrwege">bauen</a>';
	if ($kosten[fahrwege][eisen]*($row[fahrwege]*$row[fahrwege]) > $ressis[eisen]){$bauen[fahrwege] = $zuteuer;}
	if ($kosten[fahrwege][titan]*($row[fahrwege]*$row[fahrwege]) > $ressis[titan]){$bauen[fahrwege] = $zuteuer;}
	if ($kosten[fahrwege][oel]*($row[fahrwege]*$row[fahrwege]) > $ressis[oel]){$bauen[fahrwege] = $zuteuer;}
	if ($kosten[fahrwege][uran]*($row[fahrwege]*$row[fahrwege]) > $ressis[uran]){$bauen[fahrwege] = $zuteuer;}
	if ($kosten[fahrwege][gold]*($row[fahrwege]*$row[fahrwege]) > $ressis[gold]){$bauen[fahrwege] = $zuteuer;}
	if ($kosten[fahrwege][chanje]*($row[fahrwege]*$row[fahrwege]) > $ressis[chanje]){$bauen[fahrwege] = $zuteuer;}

	$bauen[missionszentrum] = '<a href="gebaeude.php?'.SID.'&bau=missionszentrum">bauen</a>';
	if ($kosten[missionszentrum][eisen]*($row[missionszentrum]*$row[missionszentrum]) > $ressis[eisen]){$bauen[missionszentrum] = $zuteuer;}
	if ($kosten[missionszentrum][titan]*($row[missionszentrum]*$row[missionszentrum]) > $ressis[titan]){$bauen[missionszentrum] = $zuteuer;}
	if ($kosten[missionszentrum][oel]*($row[missionszentrum]*$row[missionszentrum]) > $ressis[oel]){$bauen[missionszentrum] = $zuteuer;}
	if ($kosten[missionszentrum][uran]*($row[missionszentrum]*$row[missionszentrum]) > $ressis[uran]){$bauen[missionszentrum] = $zuteuer;}
	if ($kosten[missionszentrum][gold]*($row[missionszentrum]*$row[missionszentrum]) > $ressis[gold]){$bauen[missionszentrum] = $zuteuer;}
	if ($kosten[missionszentrum][chanje]*($row[missionszentrum]*$row[missionszentrum]) > $ressis[chanje]){$bauen[missionszentrum] = $zuteuer;}

	$bauen[agentenzentrum] = '<a href="gebaeude.php?'.SID.'&bau=agentenzentrum">bauen</a>';
	if ($kosten[agentenzentrum][eisen]*($row[agentenzentrum]*$row[agentenzentrum]) > $ressis[eisen]){$bauen[agentenzentrum] = $zuteuer;}
	if ($kosten[agentenzentrum][titan]*($row[agentenzentrum]*$row[agentenzentrum]) > $ressis[titan]){$bauen[agentenzentrum] = $zuteuer;}
	if ($kosten[agentenzentrum][oel]*($row[agentenzentrum]*$row[agentenzentrum]) > $ressis[oel]){$bauen[agentenzentrum] = $zuteuer;}
	if ($kosten[agentenzentrum][uran]*($row[agentenzentrum]*$row[agentenzentrum]) > $ressis[uran]){$bauen[agentenzentrum] = $zuteuer;}
	if ($kosten[agentenzentrum][gold]*($row[agentenzentrum]*$row[agentenzentrum]) > $ressis[gold]){$bauen[agentenzentrum] = $zuteuer;}
	if ($kosten[agentenzentrum][chanje]*($row[agentenzentrum]*$row[agentenzentrum]) > $ressis[chanje]){$bauen[agentenzentrum] = $zuteuer;}

	$bauen[raumstation] = '<a href="gebaeude.php?'.SID.'&bau=raumstation">bauen</a>';
	if ($kosten[raumstation][eisen]*($row[raumstation]*$row[raumstation]) > $ressis[eisen]){$bauen[raumstation] = $zuteuer;}
	if ($kosten[raumstation][titan]*($row[raumstation]*$row[raumstation]) > $ressis[titan]){$bauen[raumstation] = $zuteuer;}
	if ($kosten[raumstation][oel]*($row[raumstation]*$row[raumstation]) > $ressis[oel]){$bauen[raumstation] = $zuteuer;}
	if ($kosten[raumstation][uran]*($row[raumstation]*$row[raumstation]) > $ressis[uran]){$bauen[raumstation] = $zuteuer;}
	if ($kosten[raumstation][gold]*($row[raumstation]*$row[raumstation]) > $ressis[gold]){$bauen[raumstation] = $zuteuer;}
	if ($kosten[raumstation][chanje]*($row[raumstation]*$row[raumstation]) > $ressis[chanje]){$bauen[raumstation] = $zuteuer;}

	$bauen[rohstofflager] = '<a href="gebaeude.php?'.SID.'&bau=rohstofflager">bauen</a>';
	if ($kosten[rohstofflager][eisen]*($row[rohstofflager]*$row[rohstofflager]) > $ressis[eisen]){$bauen[rohstofflager] = $zuteuer;}
	if ($kosten[rohstofflager][titan]*($row[rohstofflager]*$row[rohstofflager]) > $ressis[titan]){$bauen[rohstofflager] = $zuteuer;}
	if ($kosten[rohstofflager][oel]*($row[rohstofflager]*$row[rohstofflager]) > $ressis[oel]){$bauen[rohstofflager] = $zuteuer;}
	if ($kosten[rohstofflager][uran]*($row[rohstofflager]*$row[rohstofflager]) > $ressis[uran]){$bauen[rohstofflager] = $zuteuer;}
	if ($kosten[rohstofflager][gold]*($row[rohstofflager]*$row[rohstofflager]) > $ressis[gold]){$bauen[rohstofflager] = $zuteuer;}
	if ($kosten[rohstofflager][chanje]*($row[rohstofflager]*$row[rohstofflager]) > $ressis[chanje]){$bauen[rohstofflager] = $zuteuer;}

	$bauen[eisenmine] = '<a href="gebaeude.php?'.SID.'&bau=eisenmine">bauen</a>';
	if ($kosten[eisenmine][eisen]*($row[eisenmine]*$row[eisenmine]) > $ressis[eisen]){$bauen[eisenmine] = $zuteuer;}
	if ($kosten[eisenmine][titan]*($row[eisenmine]*$row[eisenmine]) > $ressis[titan]){$bauen[eisenmine] = $zuteuer;}
	if ($kosten[eisenmine][oel]*($row[eisenmine]*$row[eisenmine]) > $ressis[oel]){$bauen[eisenmine] = $zuteuer;}
	if ($kosten[eisenmine][uran]*($row[eisenmine]*$row[eisenmine]) > $ressis[uran]){$bauen[eisenmine] = $zuteuer;}
	if ($kosten[eisenmine][gold]*($row[eisenmine]*$row[eisenmine]) > $ressis[gold]){$bauen[eisenmine] = $zuteuer;}
	if ($kosten[eisenmine][chanje]*($row[eisenmine]*$row[eisenmine]) > $ressis[chanje]){$bauen[eisenmine] = $zuteuer;}

	$bauen[titanmine] = '<a href="gebaeude.php?'.SID.'&bau=titanmine">bauen</a>';
	if ($kosten[titanmine][eisen]*($row[titanmine]*$row[titanmine]) > $ressis[eisen]){$bauen[titanmine] = $zuteuer;}
	if ($kosten[titanmine][titan]*($row[titanmine]*$row[titanmine]) > $ressis[titan]){$bauen[titanmine] = $zuteuer;}
	if ($kosten[titanmine][oel]*($row[titanmine]*$row[titanmine]) > $ressis[oel]){$bauen[titanmine] = $zuteuer;}
	if ($kosten[titanmine][uran]*($row[titanmine]*$row[titanmine]) > $ressis[uran]){$bauen[titanmine] = $zuteuer;}
	if ($kosten[titanmine][gold]*($row[titanmine]*$row[titanmine]) > $ressis[gold]){$bauen[titanmine] = $zuteuer;}
	if ($kosten[titanmine][chanje]*($row[titanmine]*$row[titanmine]) > $ressis[chanje]){$bauen[titanmine] = $zuteuer;}

	$bauen[uranmine] = '<a href="gebaeude.php?'.SID.'&bau=uranmine">bauen</a>';
	if ($kosten[uranmine][eisen]*($row[uranmine]*$row[uranmine]) > $ressis[eisen]){$bauen[uranmine] = $zuteuer;}
	if ($kosten[uranmine][titan]*($row[uranmine]*$row[uranmine]) > $ressis[titan]){$bauen[uranmine] = $zuteuer;}
	if ($kosten[uranmine][oel]*($row[uranmine]*$row[uranmine]) > $ressis[oel]){$bauen[uranmine] = $zuteuer;}
	if ($kosten[uranmine][uran]*($row[uranmine]*$row[uranmine]) > $ressis[uran]){$bauen[uranmine] = $zuteuer;}
	if ($kosten[uranmine][gold]*($row[uranmine]*$row[uranmine]) > $ressis[gold]){$bauen[uranmine] = $zuteuer;}
	if ($kosten[uranmine][chanje]*($row[uranmine]*$row[uranmine]) > $ressis[chanje]){$bauen[uranmine] = $zuteuer;}

	$bauen[oelpumpe] = '<a href="gebaeude.php?'.SID.'&bau=oelpumpe">bauen</a>';
	if ($kosten[oelpumpe][eisen]*($row[oelpumpe]*$row[oelpumpe]) > $ressis[eisen]){$bauen[oelpumpe] = $zuteuer;}
	if ($kosten[oelpumpe][titan]*($row[oelpumpe]*$row[oelpumpe]) > $ressis[titan]){$bauen[oelpumpe] = $zuteuer;}
	if ($kosten[oelpumpe][oel]*($row[oelpumpe]*$row[oelpumpe]) > $ressis[oel]){$bauen[oelpumpe] = $zuteuer;}
	if ($kosten[oelpumpe][uran]*($row[oelpumpe]*$row[oelpumpe]) > $ressis[uran]){$bauen[oelpumpe] = $zuteuer;}
	if ($kosten[oelpumpe][gold]*($row[oelpumpe]*$row[oelpumpe]) > $ressis[gold]){$bauen[oelpumpe] = $zuteuer;}
	if ($kosten[oelpumpe][chanje]*($row[oelpumpe]*$row[oelpumpe]) > $ressis[chanje]){$bauen[oelpumpe] = $zuteuer;}
}

$kick = 'clan.php?'.SID.'&kick=1&to='.$userl['omni'];
				$member .= '<td style="width: 60px;">
				<a href="#" onclick="check(\'document.location.href=\\\''.$kick.'\\\'\', \'Willst du diesen Spieler wirklich kicken?\')">kicken</a>';

$abbruch = 'gebaeude.php?'.SID.'&abbrechen=1';

$abbrechen_link = 'Derzeitigen Bauvorgang <b><a href="#" onclick="check(\'document.location.href=\\\''.$abbruch.'\\\'\', \'Willst du aktuellen Bauvorgang wirklich abbrechen?\')"><font color="#b90101">ABBRECHEN</font></a></b><br />(Alle Ressourcen f&uuml;r diesen Auftrag gehen verloren!)<br />';

if ($row[nextbasis] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nextforschungsanlage] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nextfabrik] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nextraketensilo] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nextnbz] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nexthangar] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nextfahrwege] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nextmissionszentrum] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nextagentenzentrum] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nextraumstation] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nextrohstofflager] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nexteisenmine] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nexttitanmine] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nextoelpumpe] != 0){ $abbrechen = $abbrechen_link; }
elseif ($row[nexturanmine] != 0){ $abbrechen = $abbrechen_link; }



if ($bauen['nbz'] == '<center>-</center>' or $bauen['hangar'] == '<center>-</center>'){
	 $zuhoch = '<center>-</center>';
} else {
	 $zuhoch = '<font style="color: #57ae4b;">zu hoch</font>';
}
if ($row[basis] <= $row[forschungsanlage]) {$bauen[forschungsanlage] = $zuhoch;}
if ($row[basis] <= $row[fabrik]) {$bauen[fabrik] = $zuhoch;}
if ($row[basis] <= $row[raketensilo]) {$bauen[raketensilo] = $zuhoch;}
if ($row[basis] <= $row[nbz]) {$bauen[nbz] = $zuhoch;}
if ($row[basis] <= $row[hangar]) {$bauen[hangar] = $zuhoch;}
if ($row[basis] <= $row[fahrwege]) {$bauen[fahrwege] = $zuhoch;}
if ($row[basis] <= $row[missionszentrum]) {$bauen[missionszentum] = $zuhoch;}
if ($row[basis] <= $row[agentenzentrum]) {$bauen[agentenzentrum] = $zuhoch;}
if ($row[basis] <= $row[raumstation]) {$bauen[raumstation] = $zuhoch;}
if ($row[basis] <= $row[rohstofflager]) {$bauen[rohstofflager] = $zuhoch;}

$content .= template('gebaeude');

$content = tag2value('abbrechen',$abbrechen,$content);

if ($row[basis] < 26) {
	$content = tag2value('lvl_basis',($row[basis]-1),$content);
	$content = tag2value('basis_eisen',$kosten[basis][eisen] * ($row[basis] * $row[basis]),$content);
	$content = tag2value('basis_titan',$kosten[basis][titan] * ($row[basis]* $row[basis]),$content);
	$content = tag2value('basis_oel',$kosten[basis][oel] * ($row[basis]* $row[basis]),$content);
	$content = tag2value('basis_uran',$kosten[basis][uran] * ($row[basis]* $row[basis]),$content);
	$content = tag2value('basis_gold',$kosten[basis][gold] * ($row[basis]* $row[basis]),$content);
	$content = tag2value('basis_chanje',$kosten[basis][chanje] * ($row[basis]* $row[basis]),$content);
	$content = tag2value('basis_dauer',time2str($kosten[basis][zeit] * $row[basis]),$content);
	$content = tag2value('basis_bauen',$bauen[basis],$content);
} else {
	$content = tag2value('lvl_basis',($row[basis]-1),$content);
	$content = tag2value('basis_eisen','-',$content);
	$content = tag2value('basis_titan','-',$content);
	$content = tag2value('basis_oel','-',$content);
	$content = tag2value('basis_uran','-',$content);
	$content = tag2value('basis_gold','-',$content);
	$content = tag2value('basis_chanje','-',$content);
	$content = tag2value('basis_dauer','-',$content);
	$content = tag2value('basis_bauen','max.',$content);
}

if ($row[forschungsanlage] < 11) {
	$content = tag2value('lvl_forschungsanlage',($row[forschungsanlage]-1),$content);
	$content = tag2value('forschungsanlage_eisen',$kosten[forschungsanlage][eisen] * ($row[forschungsanlage] * $row[forschungsanlage]),$content);
	$content = tag2value('forschungsanlage_titan',$kosten[forschungsanlage][titan] * ($row[forschungsanlage]* $row[forschungsanlage]),$content);
	$content = tag2value('forschungsanlage_oel',$kosten[forschungsanlage][oel] * ($row[forschungsanlage]* $row[forschungsanlage]),$content);
	$content = tag2value('forschungsanlage_uran',$kosten[forschungsanlage][uran] * ($row[forschungsanlage]* $row[forschungsanlage]),$content);
	$content = tag2value('forschungsanlage_gold',$kosten[forschungsanlage][gold] * ($row[forschungsanlage]* $row[forschungsanlage]),$content);
	$content = tag2value('forschungsanlage_chanje',$kosten[forschungsanlage][chanje] * ($row[forschungsanlage]* $row[forschungsanlage]),$content);
	$content = tag2value('forschungsanlage_dauer',time2str($kosten[forschungsanlage][zeit] * $row[forschungsanlage]),$content);
	$content = tag2value('forschungsanlage_bauen',$bauen[forschungsanlage],$content);
} else {
	$content = tag2value('lvl_forschungsanlage',($row[forschungsanlage]-1),$content);
	$content = tag2value('forschungsanlage_eisen','-',$content);
	$content = tag2value('forschungsanlage_titan','-',$content);
	$content = tag2value('forschungsanlage_oel','-',$content);
	$content = tag2value('forschungsanlage_uran','-',$content);
	$content = tag2value('forschungsanlage_gold','-',$content);
	$content = tag2value('forschungsanlage_chanje','-',$content);
	$content = tag2value('forschungsanlage_dauer','-',$content);
	$content = tag2value('forschungsanlage_bauen','max.',$content);
}

if ($row[fabrik] < 26) {
	$content = tag2value('lvl_fabrik',($row[fabrik]-1),$content);
	$content = tag2value('fabrik_eisen',$kosten[fabrik][eisen] * ($row[fabrik] * $row[fabrik]),$content);
	$content = tag2value('fabrik_titan',$kosten[fabrik][titan] * ($row[fabrik]* $row[fabrik]),$content);
	$content = tag2value('fabrik_oel',$kosten[fabrik][oel] * ($row[fabrik]* $row[fabrik]),$content);
	$content = tag2value('fabrik_uran',$kosten[fabrik][uran] * ($row[fabrik]* $row[fabrik]),$content);
	$content = tag2value('fabrik_gold',$kosten[fabrik][gold] * ($row[fabrik]* $row[fabrik]),$content);
	$content = tag2value('fabrik_chanje',$kosten[fabrik][chanje] * ($row[fabrik]* $row[fabrik]),$content);
	$content = tag2value('fabrik_dauer',time2str($kosten[fabrik][zeit] * $row[fabrik]),$content);
	$content = tag2value('fabrik_bauen',$bauen[fabrik],$content);
} else {
	$content = tag2value('lvl_fabrik',($row[fabrik]-1),$content);
	$content = tag2value('fabrik_eisen','-',$content);
	$content = tag2value('fabrik_titan','-',$content);
	$content = tag2value('fabrik_oel','-',$content);
	$content = tag2value('fabrik_uran','-',$content);
	$content = tag2value('fabrik_gold','-',$content);
	$content = tag2value('fabrik_chanje','-',$content);
	$content = tag2value('fabrik_dauer','-',$content);
	$content = tag2value('fabrik_bauen','max.',$content);
}

if ($row[raketensilo] < 26) {
	$content = tag2value('lvl_raketensilo',($row[raketensilo]-1),$content);
	$content = tag2value('raketensilo_eisen',$kosten[raketensilo][eisen] * ($row[raketensilo] * $row[raketensilo]),$content);
	$content = tag2value('raketensilo_titan',$kosten[raketensilo][titan] * ($row[raketensilo]* $row[raketensilo]),$content);
	$content = tag2value('raketensilo_oel',$kosten[raketensilo][oel] * ($row[raketensilo]* $row[raketensilo]),$content);
	$content = tag2value('raketensilo_uran',$kosten[raketensilo][uran] * ($row[raketensilo]* $row[raketensilo]),$content);
	$content = tag2value('raketensilo_gold',$kosten[raketensilo][gold] * ($row[raketensilo]* $row[raketensilo]),$content);
	$content = tag2value('raketensilo_chanje',$kosten[raketensilo][chanje] * ($row[raketensilo]* $row[raketensilo]),$content);
	$content = tag2value('raketensilo_dauer',time2str($kosten[raketensilo][zeit] * $row[raketensilo]),$content);
	$content = tag2value('raketensilo_bauen',$bauen[raketensilo],$content);
} else {
	$content = tag2value('lvl_raketensilo',($row[raketensilo]-1),$content);
	$content = tag2value('raketensilo_eisen','-',$content);
	$content = tag2value('raketensilo_titan','-',$content);
	$content = tag2value('raketensilo_oel','-',$content);
	$content = tag2value('raketensilo_uran','-',$content);
	$content = tag2value('raketensilo_gold','-',$content);
	$content = tag2value('raketensilo_chanje','-',$content);
	$content = tag2value('raketensilo_dauer','-',$content);
	$content = tag2value('raketensilo_bauen','max.',$content);
}

if ($row[nbz] < 26) {
	$content = tag2value('lvl_nbz',($row[nbz]-1),$content);
	$content = tag2value('nbz_eisen',$kosten[nbz][eisen] * ($row[nbz] * $row[nbz]),$content);
	$content = tag2value('nbz_titan',$kosten[nbz][titan] * ($row[nbz]* $row[nbz]),$content);
	$content = tag2value('nbz_oel',$kosten[nbz][oel] * ($row[nbz]* $row[nbz]),$content);
	$content = tag2value('nbz_uran',$kosten[nbz][uran] * ($row[nbz]* $row[nbz]),$content);
	$content = tag2value('nbz_gold',$kosten[nbz][gold] * ($row[nbz]* $row[nbz]),$content);
	$content = tag2value('nbz_chanje',$kosten[nbz][chanje] * ($row[nbz]* $row[nbz]),$content);
	$content = tag2value('nbz_dauer',time2str($kosten[nbz][zeit] * $row[nbz]),$content);
	$content = tag2value('nbz_bauen',$bauen[nbz],$content);
} else {
	$content = tag2value('lvl_nbz',($row[nbz]-1),$content);
	$content = tag2value('nbz_eisen','-',$content);
	$content = tag2value('nbz_titan','-',$content);
	$content = tag2value('nbz_oel','-',$content);
	$content = tag2value('nbz_uran','-',$content);
	$content = tag2value('nbz_gold','-',$content);
	$content = tag2value('nbz_chanje','-',$content);
	$content = tag2value('nbz_dauer','-',$content);
	$content = tag2value('nbz_bauen','max.',$content);
}

if ($row[hangar] < 26) {
	$content = tag2value('lvl_hangar',($row[hangar]-1),$content);
	$content = tag2value('hangar_eisen',$kosten[hangar][eisen] * ($row[hangar] * $row[hangar]),$content);
	$content = tag2value('hangar_titan',$kosten[hangar][titan] * ($row[hangar]* $row[hangar]),$content);
	$content = tag2value('hangar_oel',$kosten[hangar][oel] * ($row[hangar]* $row[hangar]),$content);
	$content = tag2value('hangar_uran',$kosten[hangar][uran] * ($row[hangar]* $row[hangar]),$content);
	$content = tag2value('hangar_gold',$kosten[hangar][gold] * ($row[hangar]* $row[hangar]),$content);
	$content = tag2value('hangar_chanje',$kosten[hangar][chanje] * ($row[hangar]* $row[hangar]),$content);
	$content = tag2value('hangar_dauer',time2str($kosten[hangar][zeit] * $row[hangar]),$content);
	$content = tag2value('hangar_bauen',$bauen[hangar],$content);
} else {
	$content = tag2value('lvl_hangar',($row[hangar]-1),$content);
	$content = tag2value('hangar_eisen','-',$content);
	$content = tag2value('hangar_titan','-',$content);
	$content = tag2value('hangar_oel','-',$content);
	$content = tag2value('hangar_uran','-',$content);
	$content = tag2value('hangar_gold','-',$content);
	$content = tag2value('hangar_chanje','-',$content);
	$content = tag2value('hangar_dauer','-',$content);
	$content = tag2value('hangar_bauen','max.',$content);
}

if ($row[fahrwege] < 26) {
	$content = tag2value('lvl_fahrwege',($row[fahrwege]-1),$content);
	$content = tag2value('fahrwege_eisen',$kosten[fahrwege][eisen] * ($row[fahrwege] * $row[fahrwege]),$content);
	$content = tag2value('fahrwege_titan',$kosten[fahrwege][titan] * ($row[fahrwege]* $row[fahrwege]),$content);
	$content = tag2value('fahrwege_oel',$kosten[fahrwege][oel] * ($row[fahrwege]* $row[fahrwege]),$content);
	$content = tag2value('fahrwege_uran',$kosten[fahrwege][uran] * ($row[fahrwege]* $row[fahrwege]),$content);
	$content = tag2value('fahrwege_gold',$kosten[fahrwege][gold] * ($row[fahrwege]* $row[fahrwege]),$content);
	$content = tag2value('fahrwege_chanje',$kosten[fahrwege][chanje] * ($row[fahrwege]* $row[fahrwege]),$content);
	$content = tag2value('fahrwege_dauer',time2str($kosten[fahrwege][zeit] * $row[fahrwege]),$content);
	$content = tag2value('fahrwege_bauen',$bauen[fahrwege],$content);
} else {
	$content = tag2value('lvl_fahrwege',($row[fahrwege]-1),$content);
	$content = tag2value('fahrwege_eisen','-',$content);
	$content = tag2value('fahrwege_titan','-',$content);
	$content = tag2value('fahrwege_oel','-',$content);
	$content = tag2value('fahrwege_uran','-',$content);
	$content = tag2value('fahrwege_gold','-',$content);
	$content = tag2value('fahrwege_chanje','-',$content);
	$content = tag2value('fahrwege_dauer','-',$content);
	$content = tag2value('fahrwege_bauen','max.',$content);
}

if ($row[missionszentrum] < 26) {
	$content = tag2value('lvl_missionszentrum',($row[missionszentrum]-1),$content);
	$content = tag2value('missionszentrum_eisen',$kosten[missionszentrum][eisen] * ($row[missionszentrum] * $row[missionszentrum]),$content);
	$content = tag2value('missionszentrum_titan',$kosten[missionszentrum][titan] * ($row[missionszentrum]* $row[missionszentrum]),$content);
	$content = tag2value('missionszentrum_oel',$kosten[missionszentrum][oel] * ($row[missionszentrum]* $row[missionszentrum]),$content);
	$content = tag2value('missionszentrum_uran',$kosten[missionszentrum][uran] * ($row[missionszentrum]* $row[missionszentrum]),$content);
	$content = tag2value('missionszentrum_gold',$kosten[missionszentrum][gold] * ($row[missionszentrum]* $row[missionszentrum]),$content);
	$content = tag2value('missionszentrum_chanje',$kosten[missionszentrum][chanje] * ($row[missionszentrum]* $row[missionszentrum]),$content);
	$content = tag2value('missionszentrum_dauer',time2str($kosten[missionszentrum][zeit] * $row[missionszentrum]),$content);
	$content = tag2value('missionszentrum_bauen',$bauen[missionszentrum],$content);
} else {
	$content = tag2value('lvl_missionszentrum',($row[missionszentrum]-1),$content);
	$content = tag2value('missionszentrum_eisen','-',$content);
	$content = tag2value('missionszentrum_titan','-',$content);
	$content = tag2value('missionszentrum_oel','-',$content);
	$content = tag2value('missionszentrum_uran','-',$content);
	$content = tag2value('missionszentrum_gold','-',$content);
	$content = tag2value('missionszentrum_chanje','-',$content);
	$content = tag2value('missionszentrum_dauer','-',$content);
	$content = tag2value('missionszentrum_bauen','max.',$content);
}

if ($row[agentenzentrum] < 26) {
	$content = tag2value('lvl_agentenzentrum',($row[agentenzentrum]-1),$content);
	$content = tag2value('agentenzentrum_eisen',$kosten[agentenzentrum][eisen] * ($row[agentenzentrum] * $row[agentenzentrum]),$content);
	$content = tag2value('agentenzentrum_titan',$kosten[agentenzentrum][titan] * ($row[agentenzentrum]* $row[agentenzentrum]),$content);
	$content = tag2value('agentenzentrum_oel',$kosten[agentenzentrum][oel] * ($row[agentenzentrum]* $row[agentenzentrum]),$content);
	$content = tag2value('agentenzentrum_uran',$kosten[agentenzentrum][uran] * ($row[agentenzentrum]* $row[agentenzentrum]),$content);
	$content = tag2value('agentenzentrum_gold',$kosten[agentenzentrum][gold] * ($row[agentenzentrum]* $row[agentenzentrum]),$content);
	$content = tag2value('agentenzentrum_chanje',$kosten[agentenzentrum][chanje] * ($row[agentenzentrum]* $row[agentenzentrum]),$content);
	$content = tag2value('agentenzentrum_dauer',time2str($kosten[agentenzentrum][zeit] * $row[agentenzentrum]),$content);
	$content = tag2value('agentenzentrum_bauen',$bauen[agentenzentrum],$content);
} else {
	$content = tag2value('lvl_agentenzentrum',($row[agentenzentrum]-1),$content);
	$content = tag2value('agentenzentrum_eisen','-',$content);
	$content = tag2value('agentenzentrum_titan','-',$content);
	$content = tag2value('agentenzentrum_oel','-',$content);
	$content = tag2value('agentenzentrum_uran','-',$content);
	$content = tag2value('agentenzentrum_gold','-',$content);
	$content = tag2value('agentenzentrum_chanje','-',$content);
	$content = tag2value('agentenzentrum_dauer','-',$content);
	$content = tag2value('agentenzentrum_bauen','max.',$content);
}

if ($row[raumstation] < 11) {
	$content = tag2value('lvl_raumstation',($row[raumstation]-1),$content);
	$content = tag2value('raumstation_eisen',$kosten[raumstation][eisen] * ($row[raumstation] * $row[raumstation]),$content);
	$content = tag2value('raumstation_titan',$kosten[raumstation][titan] * ($row[raumstation]* $row[raumstation]),$content);
	$content = tag2value('raumstation_oel',$kosten[raumstation][oel] * ($row[raumstation]* $row[raumstation]),$content);
	$content = tag2value('raumstation_uran',$kosten[raumstation][uran] * ($row[raumstation]* $row[raumstation]),$content);
	$content = tag2value('raumstation_gold',$kosten[raumstation][gold] * ($row[raumstation]* $row[raumstation]),$content);
	$content = tag2value('raumstation_chanje',$kosten[raumstation][chanje] * ($row[raumstation]* $row[raumstation]),$content);
	$content = tag2value('raumstation_dauer',time2str($kosten[raumstation][zeit] * $row[raumstation]),$content);
	$content = tag2value('raumstation_bauen',$bauen[raumstation],$content);
} else {
	$content = tag2value('lvl_raumstation',($row[raumstation]-1),$content);
	$content = tag2value('raumstation_eisen','-',$content);
	$content = tag2value('raumstation_titan','-',$content);
	$content = tag2value('raumstation_oel','-',$content);
	$content = tag2value('raumstation_uran','-',$content);
	$content = tag2value('raumstation_gold','-',$content);
	$content = tag2value('raumstation_chanje','-',$content);
	$content = tag2value('raumstation_dauer','-',$content);
	$content = tag2value('raumstation_bauen','max.',$content);
}

if ($row[rohstofflager] < 26) {
	$content = tag2value('lvl_rohstofflager',($row[rohstofflager]-1),$content);
	$content = tag2value('rohstofflager_eisen',$kosten[rohstofflager][eisen] * ($row[rohstofflager] * $row[rohstofflager]),$content);
	$content = tag2value('rohstofflager_titan',$kosten[rohstofflager][titan] * ($row[rohstofflager]* $row[rohstofflager]),$content);
	$content = tag2value('rohstofflager_oel',$kosten[rohstofflager][oel] * ($row[rohstofflager]* $row[rohstofflager]),$content);
	$content = tag2value('rohstofflager_uran',$kosten[rohstofflager][uran] * ($row[rohstofflager]* $row[rohstofflager]),$content);
	$content = tag2value('rohstofflager_gold',$kosten[rohstofflager][gold] * ($row[rohstofflager]* $row[rohstofflager]),$content);
	$content = tag2value('rohstofflager_chanje',$kosten[rohstofflager][chanje] * ($row[rohstofflager]* $row[rohstofflager]),$content);
	$content = tag2value('rohstofflager_dauer',time2str($kosten[rohstofflager][zeit] * $row[rohstofflager]),$content);
	$content = tag2value('rohstofflager_bauen',$bauen[rohstofflager],$content);
} else {
	$content = tag2value('lvl_rohstofflager',($row[rohstofflager]-1),$content);
	$content = tag2value('rohstofflager_eisen','-',$content);
	$content = tag2value('rohstofflager_titan','-',$content);
	$content = tag2value('rohstofflager_oel','-',$content);
	$content = tag2value('rohstofflager_uran','-',$content);
	$content = tag2value('rohstofflager_gold','-',$content);
	$content = tag2value('rohstofflager_chanje','-',$content);
	$content = tag2value('rohstofflager_dauer','-',$content);
	$content = tag2value('rohstofflager_bauen','max.',$content);
}

$content = tag2value('lvl_eisenmine',($row[eisenmine]-1),$content);
$content = tag2value('eisenmine_eisen',$kosten[eisenmine][eisen] * ($row[eisenmine] * $row[eisenmine]),$content);
$content = tag2value('eisenmine_titan',$kosten[eisenmine][titan] * ($row[eisenmine]* $row[eisenmine]),$content);
$content = tag2value('eisenmine_oel',$kosten[eisenmine][oel] * ($row[eisenmine]* $row[eisenmine]),$content);
$content = tag2value('eisenmine_uran',$kosten[eisenmine][uran] * ($row[eisenmine]* $row[eisenmine]),$content);
$content = tag2value('eisenmine_gold',$kosten[eisenmine][gold] * ($row[eisenmine]* $row[eisenmine]),$content);
$content = tag2value('eisenmine_chanje',$kosten[eisenmine][chanje] * ($row[eisenmine]* $row[eisenmine]),$content);
$content = tag2value('eisenmine_dauer',time2str($kosten[eisenmine][zeit] * $row[eisenmine]),$content);
$content = tag2value('eisenmine_bauen',$bauen[eisenmine],$content);

$content = tag2value('lvl_titanmine',($row[titanmine]-1),$content);
$content = tag2value('titanmine_eisen',$kosten[titanmine][eisen] * ($row[titanmine] * $row[titanmine]),$content);
$content = tag2value('titanmine_titan',$kosten[titanmine][titan] * ($row[titanmine]* $row[titanmine]),$content);
$content = tag2value('titanmine_oel',$kosten[titanmine][oel] * ($row[titanmine]* $row[titanmine]),$content);
$content = tag2value('titanmine_uran',$kosten[titanmine][uran] * ($row[titanmine]* $row[titanmine]),$content);
$content = tag2value('titanmine_gold',$kosten[titanmine][gold] * ($row[titanmine]* $row[titanmine]),$content);
$content = tag2value('titanmine_chanje',$kosten[titanmine][chanje] * ($row[titanmine]* $row[titanmine]),$content);
$content = tag2value('titanmine_dauer',time2str($kosten[titanmine][zeit] * $row[titanmine]),$content);
$content = tag2value('titanmine_bauen',$bauen[titanmine],$content);

$content = tag2value('lvl_oelpumpe',($row[oelpumpe]-1),$content);
$content = tag2value('oelpumpe_eisen',$kosten[oelpumpe][eisen] * ($row[oelpumpe] * $row[oelpumpe]),$content);
$content = tag2value('oelpumpe_titan',$kosten[oelpumpe][titan] * ($row[oelpumpe]* $row[oelpumpe]),$content);
$content = tag2value('oelpumpe_oel',$kosten[oelpumpe][oel] * ($row[oelpumpe]* $row[oelpumpe]),$content);
$content = tag2value('oelpumpe_uran',$kosten[oelpumpe][uran] * ($row[oelpumpe]* $row[oelpumpe]),$content);
$content = tag2value('oelpumpe_gold',$kosten[oelpumpe][gold] * ($row[oelpumpe]* $row[oelpumpe]),$content);
$content = tag2value('oelpumpe_chanje',$kosten[oelpumpe][chanje] * ($row[oelpumpe]* $row[oelpumpe]),$content);
$content = tag2value('oelpumpe_dauer',time2str($kosten[oelpumpe][zeit] * $row[oelpumpe]),$content);
$content = tag2value('oelpumpe_bauen',$bauen[oelpumpe],$content);

$content = tag2value('lvl_uranmine',($row[uranmine]-1),$content);
$content = tag2value('uranmine_eisen',$kosten[uranmine][eisen] * ($row[uranmine] * $row[uranmine]),$content);
$content = tag2value('uranmine_titan',$kosten[uranmine][titan] * ($row[uranmine]* $row[uranmine]),$content);
$content = tag2value('uranmine_oel',$kosten[uranmine][oel] * ($row[uranmine]* $row[uranmine]),$content);
$content = tag2value('uranmine_uran',$kosten[uranmine][uran] * ($row[uranmine]* $row[uranmine]),$content);
$content = tag2value('uranmine_gold',$kosten[uranmine][gold] * ($row[uranmine]* $row[uranmine]),$content);
$content = tag2value('uranmine_chanje',$kosten[uranmine][chanje] * ($row[uranmine]* $row[uranmine]),$content);
$content = tag2value('uranmine_dauer',time2str($kosten[uranmine][zeit] * $row[uranmine]),$content);
$content = tag2value('uranmine_bauen',$bauen[uranmine],$content);

// generierte seite ausgeben
if ($running) {$onload = 'startCountdown();';}
$content = tag2value("onload",$onload,$content);
echo $content.template('footer');
?>