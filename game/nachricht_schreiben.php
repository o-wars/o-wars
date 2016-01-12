<?php
//////////////////////////////////
// Nachrichtenzentrum           //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////
// Kommentare:
// - Status Spieler
// - Status Nachrichten
// - Nachrichten Lesen
// - Nachrichten Senden
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

// check session
logincheck();
$_GET['id'] = str_replace('\'','`',htmlspecialchars($_GET['id']));
$_GET['to'] = str_replace('\'','`',htmlspecialchars($_GET['to']));
$_POST['to'] = str_replace('\'','`',htmlspecialchars($_POST['to']));
$_POST['subject'] = str_replace('\'','`',htmlspecialchars($_POST['subject']));
$_POST['text'] =  str_replace('\'','`',htmlspecialchars($_POST['text']));

// html head setzen
$content = template('head');
$content = tag2value('onload', '', $content);

$ressis = ressistand($_SESSION['user']['omni']);
$content .= $ressis['html'];

// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);
$content .= $status;
unset($status);

$content .= '<br /><br />';

// mit datenbank verbinden
$dbh = db_connect();

if ($_POST['send'] == 1 AND $_POST['to']){
	
	$row = mysql_fetch_array(
		mysql_query("SELECT * FROM `user` WHERE `omni` = '".$_POST['to'].$_GET['to']."' ;"));
		
	if (!$row['omni']) { $content .= "<br />Der Empf&auml;nger ".$_POST['to']." existiert nicht.<br />"; }
	else {
		if (!$_POST['subject']){ $_POST['subject'] = 'kein Betreff'; }
		elseif ("" == str_replace(" ", "", $_POST['subject'])){ $_POST['subject'] = 'kein Betreff'; }
		$select = "INSERT INTO `nachrichten` ( `id` , `from` , `from_name` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_SESSION[user][name]."', '".$_POST['to']."', '".date(U)."', '0', '".substr($_POST['subject'],0,50)."', '".$_POST['text']."');";
		$selectResult   = mysql_query($select);
		$content .= "<br />Nachricht erfolgreich an ".$_POST['to']." versendet.<br />";
		$_GET['id'] = htmlspecialchars($_GET['id']);
	}
}

// ungelesene nachrichten
$select = "SELECT * FROM `nachrichten` WHERE `id` = ".$_GET['id']." AND `to` = '".$_SESSION['user']['omni']."' GROUP BY id DESC;";
$selectResult   = mysql_query($select);

$content .= '<script language="JavaScript">
<!--
function notecheck() {
StrLen=window.document.notes.text.value.length;
var maxi=2000;
if (StrLen>maxi) {window.document.block.text.value=window.document.notes.text.value.substring(0,1000);StrLeft=0;} else {StrLeft=maxi-StrLen;}
document.notes.status.value=\'Rest: \'+StrLeft;
}
// -->
</script><center><span style="font-size: 12px";><b>Nachricht schreiben:</b><form name="notes" enctype="multipart/form-data" action="nachricht_schreiben.php?'. SID .'" method="post"><table border="1" cellspacing="0" style="background-color:#e2e2e2; font-size: 12px"><tr style="background-image:url(templates/standard/table_head.gif);" align="center"><td style="width:80px">Datum</td><td style="width:60px">An:</td><td style="width:400px">Betreff</td></tr>';
$content .= '<tr align="center"><td>'.date("H:i d.m.y").'</td><td><input type="text" name="to" value="'.$_POST['to'].$_GET['to'].'" style="width:60px" /></td><td><input type="text" name="subject" value="'.stripslashes($_POST['subject']).'" maxlength="50" style="width:400px" /></td></tr>
<tr align="left"><td colspan="4" align="center"><br /><textarea name="text" style="width:570px; height:400px" onChange=notecheck(this) onFocus=notecheck(this) onBlur=notecheck(this) onKeyDown=notecheck(this) onKeyUp=notecheck(this)>'.html_entity_decode("\n\n\n\n".$_SESSION['user']['sig']).'</textarea><br /><input name="status" value=\'Rest: 2000\' style="width:170px;" readonly /></td></tr>';
$content .= '</table></center>';
$content .= '<input type="hidden" name="send" value="1">
<input type="submit" name="submit" value="Senden"></form>
<br /><br />
<table border="1" cellspacing="0">
<tr>
<td style="width:150px" rowspan="3" valign="top">%bbcode%</td>
</tr>
</table>
	<b>Es k&ouml;nnen folgende Tags verwendet werden:</b><br /></b>
	[b] fette Schrift <br />
	[/b] ende fette Schrift<br />
	[i] geschwungene Schrift <br />
	[/i] ende geschwungene Schrift<br />
	[color="farbe"] anfang farbige Schrift (HEX-Farbcode #123456 oder Farbe auf englisch)<br />
	[/color] ende farbige Schrift<br />

	[center] anfang zentrierte Schrift<br />
	[/center] ende zentrierte Schrift';

$content = tag2value('bbcode', template('bbcode'), $content);

// generierte seite ausgeben
echo $content.'</body></html>';
?>