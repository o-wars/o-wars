<?php
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "admin.php";
include "raketen_preise.php";

// check session
logincheck();

// mit datenbank verbinden
$dbh = db_connect();

if (!$_SESSION['ubl']) {
	 die ('error');
}

if ($_POST['aendern']) {
	mysql_query("UPDATE `raketen` SET `einh1` = '".$_POST['einh1']."',
`einh2` = '".$_POST['einh2']."',
`einh3` = '".$_POST['einh3']."',
`einh4` = '".$_POST['einh4']."',
`einh5` = '".$_POST['einh5']."',
`einh6` = '".$_POST['einh6']."' WHERE `omni` = '".$_SESSION['ubl']."' LIMIT 1;"); 
}

$raks = mysql_fetch_array(mysql_query("SELECT * FROM `raketen` WHERE `omni` = '".$_SESSION['ubl']."';"));

$temp = template('raks');

$i=1;
do {
	$temp  = tag2value('einh'.$i.'_name', $rak[$i]['name'],$temp);
	$temp  = tag2value('einh'.$i, $raks['einh'.$i],$temp);
	$i++;
} while ($i <=15);

$content .= template('index');
$content  = tag2value('ubl', $_SESSION['ubl'],$content);
$content  = tag2value('content', $_SESSION['info'].$temp,$content);

echo $content;
?>