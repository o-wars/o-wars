<?php 

include 'config.php';
$dbh = mysql_pconnect($db_host, $db_user, $db_pass)
	or die("<h1>Could not connect</h1><b>Please check your configuration. The DB settings seems to be incorrect");
mysql_select_db($db_database, $dbh);

$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION[user][omni]."';";
$selectResult   = mysql_query($select, $dbh);
$gebaeude = mysql_fetch_array($selectResult);

$rak[1][name]    = "V01-fliegende Bombe";
$rak[1][eisen]   = 50;
$rak[1][titan]   = 120;
$rak[1][oel]     = 100;
$rak[1][uran]    = 10;
$rak[1][gold]    = 1;
$rak[1][chanje]  = 0;
$rak[1][dauer]   = 1200*pow(0.9,$gebaeude['nbz']);
$rak[1][treffer] = 60;
$rak[1][speed]   = 666;
$rak[1][targets] = 'Alle Soldaten, ausser Elitesoldaten<br />MG-Nest<br />Panzerfaust Stellung';

$rak[2][name]    = "V02-Vergeltung";
$rak[2][eisen]   = 120;
$rak[2][titan]   = 150;
$rak[2][oel]     = 300;
$rak[2][uran]    = 10;
$rak[2][gold]    = 3;
$rak[2][chanje]  = 0;
$rak[2][dauer]   = 1800*pow(0.9,$gebaeude['nbz']);
$rak[2][treffer] = 40;
$rak[2][speed]   = 666;
$rak[2][targets] = 'Alle Soldaten<br />MG-Nest<br />Panzerfaust Stellung<br />50mm PAK Md.38<br />Kleiner Transporter<br />K&uuml;belwagen<br />Motorrad';

$rak[3][name]    = "A09-Feindflug";
$rak[3][eisen]   = 200;
$rak[3][titan]   = 220;
$rak[3][oel]     = 300;
$rak[3][uran]    = 15;
$rak[3][gold]    = 15;
$rak[3][chanje]  = 0;
$rak[3][dauer]   = 2700*pow(0.9,$gebaeude['nbz']);
$rak[3][treffer] = 20;
$rak[3][speed]   = 666;
$rak[3][targets] = 'Alle Soldaten<br />MG-Nest<br />Panzerfaust Stellung<br />50mm PAK Md.38<br />75mm PAK Md.40<br />Kleiner Transporter<br />Grosser Transporter<br />Sammler<br />K&uuml;belwagen<br />Marder III<br />Nebelwerfer<br />Motorrad<br />88mm PAK Md.43<br />Panther';

$rak[4][name]    = "A10-Amerika-Rakete";
$rak[4][eisen]   = 300;
$rak[4][titan]   = 300;
$rak[4][oel]     = 400;
$rak[4][uran]    = 200;
$rak[4][gold]    = 20;
$rak[4][chanje]  = 1;
$rak[4][dauer]   = 4500*pow(0.9,$gebaeude['nbz']);
$rak[4][treffer] = 10;
$rak[4][speed]   = 666;
$rak[4][targets] = 'Alle Soldaten<br />MG-Nest<br />Panzerfaust Stellung<br />50mm PAK Md.38<br />Kleiner Transporter<br />Grosser Transporter<br />Sammler<br />K&uuml;belwagen<br />75mm PAK Md.40<br />88mm PAK Md.43<br />Marder III<br />Nebelwerfer<br />Panther<br />125mm PAK Md.59<br />Tiger<br />Motorrad';

$rak[5][name]    = "G17-P-Brecher";
$rak[5][eisen]   = 500;
$rak[5][titan]   = 400;
$rak[5][oel]     = 600;
$rak[5][uran]    = 150;
$rak[5][gold]    = 50;
$rak[5][chanje]  = 3;
$rak[5][dauer]   = 5400*pow(0.9,$gebaeude['nbz']);
$rak[5][treffer] = 5;
$rak[5][speed]   = 666;
$rak[5][targets] = 'Jagdtiger und K&ouml;nigstiger<br />sonst keine m&ouml;glichen Ziele.';

$rak[6][name]    = "D09-Spionagerakete";
$rak[6][eisen]   = 50;
$rak[6][titan]   = 50;
$rak[6][oel]     = 50;
$rak[6][uran]    = 10;
$rak[6][gold]    = 20;
$rak[6][chanje]  = 0;
$rak[6][dauer]   = 600*pow(0.9,$gebaeude['nbz']);
$rak[6][treffer] = 100;
$rak[6][speed]   = 666;
$rak[6][targets] = 'keine';
?>