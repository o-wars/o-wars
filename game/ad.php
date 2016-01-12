<?php
//////////////////////////////////
// ad.php                       //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

// mit datenbank verbinden
$dbh = db_connect();

$result = mysql_query("SELECT * FROM `werbung` WHERE 1;");
$rows   = mysql_num_rows($result);

$rand   = rand(0,$rows-1);

$result = mysql_query("SELECT * FROM `werbung` WHERE 1 LIMIT ".$rand.",".($rand+1).";");
$row    = mysql_fetch_array($result);

$content .= '<script language="JavaScript" type="text/javascript">
			<!-- 
			function refresh() {
			setTimeout("location.reload();",300000);
			}
			refresh();
			-->
			</script>';

$content .= '<a href="'.$row['link'].'" target="_new"><img src="'.$row['img'].'" name="ad" alt="ad" /></a>';

// html head setzen
$top = template('head');
$top = tag2value('onload', $onload, $top);

// generierte seite ausgeben
echo $top.$content.template('footer');
?>