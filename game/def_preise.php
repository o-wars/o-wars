<?php 
include_once 'einheiten_preise.php';
include 'config.php';
$dbh = mysql_pconnect($db_host, $db_user, $db_pass)
	or die("<h1>Could not connect</h1><b>Please check your configuration. The DB settings seems to be incorrect");
mysql_select_db($db_database);
	
$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION['user']['omni']."';";
$selectResult   = mysql_query($select);
$gebaeude       = mysql_fetch_array($selectResult);

$select = "SELECT * FROM `forschungen` WHERE `omni` = '".$_SESSION['user']['omni']."';";
$selectResult   = mysql_query($select);
$forschungen    = mysql_fetch_array($selectResult);

$def[1]['name']    = "Sprengfalle";
$def[1]['eisen']   = 50;
$def[1]['titan']   = 25;
$def[1]['oel']     = 25;
$def[1]['uran']    = 10;
$def[1]['gold']    = 2;
$def[1]['chanje']  = 0;
$def[1]['dauer']   = 1800*pow(0.9,$gebaeude['nbz'])*pow(0.9,($forschungen['minen']-1));
$def[1]['targets'] = $einh[1]['name'].', '.$einh[2]['name'].', '.$einh[3]['name'];
$def[1]['spot']    = 70;
$def[1]['death']   = 30;

$def[2]['name']    = "Sch&uuml;tzenmine";
$def[2]['eisen']   = 70;
$def[2]['titan']   = 35;
$def[2]['oel']     = 75;
$def[2]['uran']    = 20;
$def[2]['gold']    = 5;
$def[2]['chanje']  = 0;
$def[2]['dauer']   = 3600*pow(0.9,$gebaeude['nbz'])*pow(0.9,($forschungen['minen']-3));
$def[2]['targets'] = $einh[1]['name'].', '.$einh[2]['name'].', '.$einh[3]['name'].', '.$einh[4]['name'];
$def[2]['spot']    = 65;
$def[2]['death']   = 40;


$def[3]['name']    = "Sch&uuml;tzenpanzermine";
$def[3]['eisen']   = 85;
$def[3]['titan']   = 55;
$def[3]['oel']     = 150;
$def[3]['uran']    = 40;
$def[3]['gold']    = 10;
$def[3]['chanje']  = 0;
$def[3]['dauer']   = 5400*pow(0.9,$gebaeude['nbz'])*pow(0.9,($forschungen['minen']-5));
$def[3]['targets'] = $einh[12]['name'].', '.$einh[13]['name'].', '.$einh[14]['name'].', '.$einh[5]['name'].', '.$einh[6]['name'];
$def[3]['spot']    = 90;
$def[3]['death']   = 15;

$def[4]['name']    = "Panzerabwehrmine";
$def[4]['eisen']   = 100;
$def[4]['titan']   = 80;
$def[4]['oel']     = 200;
$def[4]['uran']    = 80;
$def[4]['gold']    = 20;
$def[4]['chanje']  = 0;
$def[4]['dauer']   = 6300*pow(0.9,$gebaeude['nbz'])*pow(0.9,($forschungen['minen']-7));
$def[4]['targets'] = $einh[12]['name'].', '.$einh[13]['name'].', '.$einh[14]['name'].', '.$einh[5]['name'].', '.$einh[6]['name'].', '.$einh[7]['name'].', '.$einh[9]['name'].', '.$einh[8]['name'];
$def[4]['spot']    = 75;
$def[4]['death']   = 20;

$def[5]['name']    = "Panzerfaust Stellung";
$def[5]['eisen']   = 250;
$def[5]['titan']   = 100;
$def[5]['oel']     = 95;
$def[5]['uran']    = 20;
$def[5]['gold']    = 10;
$def[5]['chanje']  = 0;
$def[5]['dauer']   = 2400*pow(0.9,$gebaeude['nbz']);
$def[5]['off']     = 45;
$def[5]['def']     = 40;

$def[6]['name']    = "50mm PAK Md.38";
$def[6]['eisen']   = 320;
$def[6]['titan']   = 150;
$def[6]['oel']     = 115;
$def[6]['uran']    = 30;
$def[6]['gold']    = 20;
$def[6]['chanje']  = 0;
$def[6]['dauer']   = 3600*pow(0.9,$gebaeude['nbz']);
$def[6]['off']     = 65;
$def[6]['def']     = 50;

$def[7]['name']    = "75mm PAK Md.40";
$def[7]['eisen']   = 400;
$def[7]['titan']   = 250;
$def[7]['oel']     = 250;
$def[7]['uran']    = 50;
$def[7]['gold']    = 20;
$def[7]['chanje']  = 0;
$def[7]['dauer']   = 4800*pow(0.9,$gebaeude['nbz']);
$def[7]['off']     = 80;
$def[7]['def']     = 75;

$def[8]['name']    = "88mm PAK Md.43";
$def[8]['eisen']   = 500;
$def[8]['titan']   = 300;
$def[8]['oel']     = 300;
$def[8]['uran']    = 70;
$def[8]['gold']    = 40;
$def[8]['chanje']  = 0;
$def[8]['dauer']   = 6000*pow(0.9,$gebaeude['nbz']);
$def[8]['off']     = 120;
$def[8]['def']     = 100;

$def[9]['name']    = "MG Stellung";
$def[9]['eisen']   = 150;
$def[9]['titan']   = 75;
$def[9]['oel']     = 20;
$def[9]['uran']    = 10;
$def[9]['gold']    = 5;
$def[9]['chanje']  = 0;
$def[9]['dauer']   = 1800*pow(0.9,$gebaeude['nbz']);
$def[9]['off']     = 15;
$def[9]['def']     = 25;

$def[10]['name']    = "125mm PAK Md.59";
$def[10]['eisen']   = 600;
$def[10]['titan']   = 400;
$def[10]['oel']     = 400;
$def[10]['uran']    = 100;
$def[10]['gold']    = 100;
$def[10]['chanje']  = 0;
$def[10]['dauer']   = 7200*pow(0.9,$gebaeude['nbz']);
$def[10]['off']     = 150;
$def[10]['def']     = 100;
?>
