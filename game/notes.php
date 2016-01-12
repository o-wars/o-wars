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
include("functions.php");
include('einheiten_preise.php');
include('def_preise.php');

// check session
logincheck();

// get html head
$content = template('head');
// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);

// add playerinfo to html
$content .= $status;

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION[user][omni]);
$content .= $ressis['html'];

$dbh = db_connect();

if ($_POST['save']){
	mysql_query("UPDATE `user` SET `notes` = '".substr(str_replace("'",'`', $_POST['notiz']),0,2000)."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;");
}

$select = "SELECT notes FROM `user` WHERE `omni` = '".$_SESSION['user']['omni']."';";
$result = mysql_query($select);
$row    = mysql_fetch_array($result);

$content .= '
<script language="JavaScript">
<!--
function notecheck() {
StrLen=window.document.notes.notiz.value.length;
var maxi=2000;
if (StrLen>maxi)
{window.document.block.notiz.value=window.document.notes.notiz.value.substring(0,1000);StrLeft=0;} else {StrLeft=maxi-StrLen;}
document.notes.status.value=\'ungespeichert, Rest: \'+StrLeft;
}
// -->
</script>
<br />
<table border="1" cellspacing="0" class="sub" style="width:720px">
	<tr>
		<th>
			<b>Notizen:</b>
		</th>
	</tr>
	<tr>
		<td align="center"><br />
<form action="notes.php?'.SID.'" name="notes" method="post">
	<center>
	<textarea style="width: 600px; height: 400px;" name="notiz" onChange=notecheck(this) onFocus=notecheck(this) onBlur=notecheck(this) onKeyDown=notecheck(this) onKeyUp=notecheck(this)>'.$row['notes'].'</textarea>
	<br />
	<input name="status" value=\'gespeichert\' style="width:170px;" readonly />
	<input type="submit" value="speichern" />    
	<input type="hidden" name="save" value="1" />
    </center>
</form></td></tr></table>';

$content = tag2value("onload",$onload,$content);
echo $content.template('footer');
?>