<?php 
$dbh = db_connect();
$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION[user][omni]."';";
$selectResult   = mysql_query($select, $dbh);
$gebaeude = mysql_fetch_array($selectResult);

$rst[scanner][eisen]    = 300;
$rst[scanner][titan]    = 500;
$rst[scanner][oel]      = 850;
$rst[scanner][uran]     = 80;
$rst[scanner][gold]     = 600;
$rst[scanner][chanje]   = 0;
$rst[scanner][zeit]     = 18700*pow(0.9,$gebaeude['nbz']);
$rst[scanner][name] = 'scanner';
$rst[scanner][info] = 'Die scanner erm&ouml;glicht das gezielte Forschen nach Technologien die zum weiteren Ausbau der plasma und zur Produktion von Einheiten, Verteidigungsanlagen, Raketen etc. ben&ouml;tigt werden.
<br /><br />Maximales Forschungs-Level = scannern-Level mal 2
';

$rst[plasma][eisen]    = 50;
$rst[plasma][titan]    = 85;
$rst[plasma][oel]      = 40;
$rst[plasma][uran]     = 180;
$rst[plasma][gold]     = 20;
$rst[plasma][chanje] = 0;
$rst[plasma][zeit]     = 10320*pow(0.9,$gebaeude['nbz']);
$rst[plasma][name] = 'Plasmakanone';
$rst[plasma][info] = 'Die plasma bildet den Grundstock f&uuml;r den gesamten Geb&auml;ude-Komplex plasma. Das maximale Geb&auml;ude-Level der anderen Geb&auml;ude, richtet sich nach dem aktuellen plasma-Level.';
?>