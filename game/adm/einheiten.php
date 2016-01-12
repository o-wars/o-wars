<?php
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "admin.php";
include "einheiten_preise.php";

// check session
logincheck();

// mit datenbank verbinden
$dbh = db_connect();

if (!$_SESSION['ubl']) {
	 die ('error');
}

if ($_POST['aendern']) {
	mysql_query("UPDATE `hangar` SET `einh1` = '".$_POST['einh1']."',
`einh2` = '".$_POST['einh2']."',
`einh3` = '".$_POST['einh3']."',
`einh4` = '".$_POST['einh4']."',
`einh5` = '".$_POST['einh5']."',
`einh6` = '".$_POST['einh6']."',
`einh7` = '".$_POST['einh7']."',
`einh8` = '".$_POST['einh8']."',
`einh9` = '".$_POST['einh9']."',
`einh10` = '".$_POST['einh10']."',
`einh11` = '".$_POST['einh11']."',
`einh12` = '".$_POST['einh12']."',
`einh13` = '".$_POST['einh13']."',
`einh14` = '".$_POST['einh14']."',
`einh15` = '".$_POST['einh15']."' WHERE `omni` = '".$_SESSION['ubl']."' LIMIT 1;"); 
}

$hangar = mysql_fetch_array(mysql_query("SELECT * FROM `hangar` WHERE `omni` = '".$_SESSION['ubl']."';"));

$temp = template('einheiten');

$i=1;
do {
	$temp  = tag2value('einh'.$i.'_name', $einh[$i]['name'],$temp);
	$temp  = tag2value('einh'.$i, $hangar['einh'.$i],$temp);
	$i++;
} while ($i <=15);

$content .= template('index');
$content  = tag2value('ubl', $_SESSION['ubl'],$content);
$content  = tag2value('content', $_SESSION['info'].$temp,$content);

echo $content;
?>