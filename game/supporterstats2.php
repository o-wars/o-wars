<?php
//////////////////////////////////
// Komplettuebersicht           //
//////////////////////////////////
// Letzte Aenderung: 20.02.2005 //
// Version:          0.10a      //
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
//$content = tag2value('onload', '', $content);

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

//if ($_SESSION['user']['supporter'] <= date('U')){
//	die ("hier kommt dann das template fuer noch nicht supporter :P");
//}

// $dbh = db_connect();

$content .= '<br /><br />';

$files = glob('temp/img/graph_*_'.$_SESSION['user']['omni'].'.gif');

foreach ($files as $file) {

	$content .= '<img src="'.$file.'" alt="'.$file.'" /><br /><br />';

}

// generierte seite ausgeben
$content = str_replace('%onload%', $onload, $content);
echo $content.template('footer');;
?>