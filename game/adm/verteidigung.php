<?php
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "admin.php";
include "def_preise.php";

// check session
logincheck();

// mit datenbank verbinden
$dbh = db_connect();

if (!$_SESSION['ubl']) {
	 die ('error');
}

if ($_POST['aendern']) {
	mysql_query("UPDATE `defense` SET `def1` = '".$_POST['einh1']."',
`def2` = '".$_POST['einh2']."',
`def3` = '".$_POST['einh3']."',
`def4` = '".$_POST['einh4']."',
`def5` = '".$_POST['einh5']."',
`def6` = '".$_POST['einh6']."',
`def7` = '".$_POST['einh7']."',
`def8` = '".$_POST['einh8']."',
`def9` = '".$_POST['einh9']."',
`def10` = '".$_POST['einh10']."' WHERE `omni` = '".$_SESSION['ubl']."' LIMIT 1;"); 
}

$defense = mysql_fetch_array(mysql_query("SELECT * FROM `defense` WHERE `omni` = '".$_SESSION['ubl']."';"));

$temp = template('def');

$i=1;
do {
	$temp  = tag2value('einh'.$i.'_name', $def[$i]['name'],$temp);
	$temp  = tag2value('einh'.$i, $defense['def'.$i],$temp);
	$i++;
} while ($i <=15);

$content .= template('index');
$content  = tag2value('ubl', $_SESSION['ubl'],$content);
$content  = tag2value('content', $_SESSION['info'].$temp,$content);

echo $content;
?>