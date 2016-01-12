<?php
//////////////////////////////////
// clan.php                     //
//////////////////////////////////
// Letzte Aenderung: 01.11.2005 //
//////////////////////////////////

// Basisfunktionen laden
include "functions.php";

$dbh = db_connect();

$select = mysql_query ("SELECT omni,name,base,clan,points FROM `user` WHERE 1 ORDER BY points DESC;");

echo 'place;ubl;name;base;clan;points'."\n";

for ($row=mysql_fetch_array($select);$row;$row=mysql_fetch_array($select)) {

  $i++;
  echo $i.';'.$row['omni'].';'.$row['name'].';'.$row['base'].';'.$row['clan'].';'.$row['points']."\n";

}


?>
