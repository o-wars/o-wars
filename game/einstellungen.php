<?php
//////////////////////////////////
// einstellungen.php            //
//////////////////////////////////
// Letzte Aenderung: 01.11.2005 //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include("functions.php");

// check session
logincheck();

$dbh = db_connect();

if ($_POST['pw1'] and $_POST['pw2'] and $_POST['pw1'] == $_POST['pw2']){ 
	if (preg_match('/^[0-9,A-Z,a-z]{4,12}$/',$_POST['pw1']) == 0){
		$action .= "Passwort: G&uuml;ltige Zeichen: [0-9,A-Z,a-z], Minimum 4, Maximum 12 Zeichen<br />";
	} else {
		$action .= 'Passwort wurde ge&auml;ndert.<br />'; 		
		$select = "UPDATE `user` SET `password` = MD5( '".htmlspecialchars($_POST['pw1'])."' ) WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
		$result = mysql_query($select);
	}
}
if ($_POST['pw1'] and $_POST['pw1'] != $_POST['pw2']){ $action .= 'Passw&ouml;rter sind nicht identisch.<br />'; }

if ($_POST['base'] and $_POST['base'] != $_SESSION['user']['base']) {
	if (preg_match('/^.{1,16}$/',$_POST['base']) == 0){
		$action .= "Basis-Name: Minimum 1, Maximum 16 Zeichen<br />";
	} else {
		$action .= 'Basis-Name wurde ge&auml;ndert.<br />'; 
		$_SESSION['user']['base'] = $_POST['base'];
		$select = "UPDATE `user` SET `base` = '".htmlspecialchars($_POST['base'])."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
		$result = mysql_query($select);
	}
}

if ($_POST['email2'] and $_POST['email2'] != $_SESSION['user']['email2']) {
	$action .= '2. eMail wurde ge&auml;ndert.<br />'; 
	$_POST['sig'] = str_replace('&quot;','"',$_POST['sig']);
	$select = "UPDATE `user` SET `email2` = '".htmlspecialchars($_POST['email2'])."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$result = mysql_query($select);
	
	$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$_SESSION['user']['email2'] = $row['email2'];
}

if ($_POST['sig'] and $_POST['sig'] != $_SESSION['user']['sig']) {
	$action .= 'Signatur wurde ge&auml;ndert.<br />'; 
	$_POST['sig'] = str_replace('&quot;','"',$_POST['sig']);
	$select = "UPDATE `user` SET `sig` = '".htmlspecialchars($_POST['sig'])."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$result = mysql_query($select);
	
	$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$_SESSION['user']['sig'] = $row['sig'];
}

if ($_POST['irc'] and $_POST['irc'] != $_SESSION['user']['irc']) {
	$action .= 'Irc Connect Script wurde ge&auml;ndert.<br />'; 
	$_POST['sig'] = str_replace('&quot;','"',$_POST['sig']);
	$select = "UPDATE `user` SET `irc` = '".htmlspecialchars($_POST['irc'])."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$result = mysql_query($select);
	
	$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$_SESSION['user']['irc'] = $row['irc'];
}

if ($_POST['pic'] and $_POST['pic'] != $_SESSION['user']['pic']) {
	$action .= 'User-Pic wurde ge&auml;ndert.<br />'; 
	$select = "UPDATE `user` SET `pic` = '".$_POST['pic']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$result = mysql_query($select);
	
	$_SESSION['user']['pic'] = $_POST['pic'] ;
}

if ($_POST and $_POST['badwords'] != $_SESSION['badwords']) {
	$action .= 'Badwords-Status wurde ge&auml;ndert.<br />'; 
	$select = "UPDATE `user` SET `badwords` = '".intval($_POST['badwords'])."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$result = mysql_query($select);
	
	$_SESSION['badwords'] = $_POST['badwords'] ;
}

if ($_POST['style'] and $_POST['style'] != $_SESSION['user']['style']) {
	if (is_dir('style/'.$_POST['style'])) {
		$action .= 'Style wurde ge&auml;ndert.<br /><script language="javascript">top.menu.location.reload();</script>'; 
		$select = "UPDATE `user` SET `style` = '".mysql_real_escape_string($_POST['style'])."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
		$result = mysql_query($select);
		$_SESSION['user']['style'] = $_POST['style'];
	} else {
		$action .= 'Style wurde nicht gefunden!!!<br />';
	}
}

if ($_POST['submit'] == 'Einstellungen Speichern !' and $_POST['graphic'] != $_SESSION['user']['graphic']) {
	$action .= 'Externer-Style wurde ge&auml;ndert.<br /><script language="javascript">top.menu.location.reload();</script>'; 
	$select = "UPDATE `user` SET `graphic` = '".mysql_real_escape_string($_POST['graphic'])."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$result = mysql_query($select);
	$_SESSION['user']['graphic'] = $_POST['graphic'];
}

if ($_POST['submit'] == 'Einstellungen Speichern !' and $_POST['karte'] == 'karte_neu') {
	$action .= 'Karte wurde ge&auml;ndert.<br /><script language="javascript">top.menu.location.reload();</script>'; 
	$select = "UPDATE `user` SET `karte_neu` = '1' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$result = mysql_query($select);
}

if ($_POST['submit'] == 'Einstellungen Speichern !' and $_POST['karte'] == 'karte') {
	$action .= 'Karte wurde ge&auml;ndert.<br /><script language="javascript">top.menu.location.reload();</script>'; 
	$select = "UPDATE `user` SET `karte_neu` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	$result = mysql_query($select);
}

$dir = opendir('style/');

for ($style = readdir($dir); $style; $style = readdir($dir)) {
	
	if ($style != '.' and $style != '..') {
		
		$_SESSION['user']['style'] == $style ? $selected = " selected" : $selected = "";
		
		$styles .= '<option value="'.$style.'"'.$selected.'>'.$style.'</option>';
		
	}
	
}

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

// get page html
$template .= template('einstellungen');
$template = tag2value('styles', $styles, $template);
$template = tag2value('graphic', $_SESSION['user']['graphic'], $template);
$template = tag2value('sig', $_SESSION['user']['sig'], $template);
$template = tag2value('base', $_SESSION['user']['base'], $template);
$template = tag2value('name', $_SESSION['user']['name'], $template);
$template = tag2value('pic', $_SESSION['user']['pic'], $template);
$template = tag2value('email', $_SESSION['user']['mail'], $template);
$template = tag2value('email2', $_SESSION['user']['email2'], $template);
$template = tag2value('supporter', date('\z\u\m <\b>d.m.Y</\b> \u\m <\b>H:i</\b>', $_SESSION['user']['supporter']), $template);
$template = tag2value('irc', $_SESSION['user']['irc'], $template);
$template = tag2value('action', $action, $template);

if ($_SESSION['badwords']) {

	$template = tag2value('checked_badwords', 'checked="checked"', $template);

} else {
	
	$template = tag2value('checked_badwords', '', $template);

}

$content .= $template;

// send page to browser
$content = tag2value("onload", $onload, $content);
echo $content.template('footer');
?>