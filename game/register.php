<?php
//////////////////////////////////
// Login                        //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////
// Kommentare:
// - Spielerlogin
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";
include "inc/badwords.php";

$dbh = db_connect();

// html head setzen
$content = template('head');

$_POST['user'] = htmlspecialchars($_POST['user']);
$_POST['pass'] = htmlspecialchars($_POST['pass']);
$_POST['email'] = strtolower(htmlspecialchars($_POST['email']));
$_POST['basis'] = htmlspecialchars($_POST['basis']);

$content .= '<center>
<font size="+2"><u>O-Wars Rekrutierung</u></font><br /><br />
</center>';

if (!$_POST['agb']){
	$error .= "Du musst die AGB best&auml;tigen<br />";
}

// hier kommt der badwords check
$badbase = badwords($_POST['basis']);
if ($badbase) {
	$badbase = implode('/', $badbase);
	$error .= '<b>FEHLER:</b> Dein Basisname kann so nicht aktzeptiert werden. ('.$badbase.')<br />';
}

$baduser = badwords($_POST['user']);
if ($baduser) {
	$baduser = implode('/', $baduser);
	$error .= '<b>FEHLER:</b> Dein Username kann so nicht aktzeptiert werden. ('.$baduser.')<br />';
}

if ($_POST['pass'] AND preg_match('/^[0-9,A-Z,a-z]{4,12}$/',$_POST['pass']) == 0){
	$error .= "<b>FEHLER:</b> Passwort: G&uuml;ltige Zeichen: [0-9,A-Z,a-z], Minimum 4, Maximum 12 Zeichen<br />";
}

if ($_POST['user'] AND preg_match('/^[0-9,A-Z,a-z,\ ]{1,16}$/',$_POST['user']) == 0){
	$error .= "<b>FEHLER:</b> Username: G&uuml;ltige Zeichen: [0-9,A-Z,a-z, ], Minimum 1, Maximum 16 Zeichen<br />";
} elseif ($_POST['user']) {
	$select = "SELECT * FROM `user` WHERE `name` = '".$_POST['user']."';";
	$result = mysql_query($select);
	if (@mysql_num_rows($result) != 0){ $error .= '<b>FEHLER:</b> Es gibt bereits einen Spieler mit dem Namen '.$_POST['user'].'.<br />';}
	$select = "SELECT * FROM `user_new` WHERE `user` = '".$_POST['user']."';";
	$result = mysql_query($select);
	if (@mysql_num_rows($result) != 0){ $error .= '<b>FEHLER:</b> Es gibt bereits einen Spieler mit dem Namen '.$_POST['user'].'.<br />';}	
}

if ($_POST['email']) {
	$select = "SELECT * FROM `user` WHERE `email` = '".$_POST['email']."';";
	$result = mysql_query($select);
	if (@mysql_num_rows($result) != 0){ $error .= '<b>FEHLER:</b> Es gibt bereits einen Spieler der sich mit dieser E-Mail registriert hat.<br />';}
	
	$select = "SELECT * FROM `user_new` WHERE `email` = '".$_POST['email']."';";
	$result = mysql_query($select);
	if (@mysql_num_rows($result) != 0){ $error .= '<b>FEHLER:</b> Es gibt bereits einen Spieler der sich mit dieser E-Mail registriert hat.<br />';}
	
	if (strstr($_POST['email'], 'discardmail')) { $error .= '<b>FEHLER:</b> <i>discardmail</i> E-Mail-Adressen werden nicht aktzeptiert.'; }
}

if ($_POST['pass'] AND preg_match('/^.{1,16}$/',$_POST['pass']) == 0){
	$error .= "<b>FEHLER:</b> Basisname: G&uuml;ltige Zeichen: alle, Minimum 1, Maximum 16 Zeichen<br />";
}

if ($_POST['user'] AND $_POST['pass'] AND $_POST['email'] AND $_POST['basis'] AND !$error){ 
		$rand = rand(1000000,9999999);
		$select = "INSERT INTO `user_new` ( `id` , `user` , `pass` , `email` , `base` , `time` , `key` ) VALUES ( '', '".$_POST['user']."', '".$_POST['pass']."', '".$_POST['email']."', '".$_POST['basis']."', '".date('U')."', '".$rand."' );";
		$result = mysql_query($select);
		$id = mysql_insert_id();

		$mail = 'Hallo '.$_POST['user'].',
		
		willkommen bei O-Wars.de, um deinen Account zu aktivieren klicke bitte:
		http://game.o-wars.de/validate.php?id='.$id.'&key='.$rand.'
		
		danach kannst du dich mit folgenden Daten anmelden:
		Username: '.$_POST['user'].'
		Passwort: '.$_POST['pass'].'
		
		Diese E-Mail verliert ihre Gueltigkeit in 48h, Sollte die Registrierung bis dahin nicht abgeschlossen sein musst du dich neu registrieren.
		
		Viel Spass wuenscht
		Das O-Wars.de Team';
		
		mail ($_POST['email'],'O-Wars Registrierung',$mail,"From: no-reply@o-wars.de");
		
		$content .= '<br /><br /><b>Du bist nun erfolgreich registriert,<br />
		bitte checke deine E-Mails und klicke den Link in der Best&auml;tigungsmail,<br />
		erst dann kannst du dich einloggen.</b><br />
		<br />
		Es kann sein, das manche E-Mail Provider diese Mail im Spam Ordner ablegen.<br />
		also wenn die Mail nicht auftaucht einfach mal im Spam Ordner gucken.';
} else { $content .= '<table border="0"><tr><td width="90%">
	<p style="text-align:justify">
	<b>O-Wars ist ein spannendes browserbasierendes Echtzeit-Strategie Spiel. Es spielt in der Endzeit des 3. Weltkrieges, in einer Zeit in der verschiedene Kommandanten mehr oder weniger friedlich in ihren Basen leben. Doch der scheinbare Friede w&auml;hrt selten lange und meist reicht schon eine kleine Provokation, um mehrere Basen in einen privaten Krieg untereinander zu reissen.<br /><br />
	Registriere dich noch heute und erkunde die Welt von O-Wars, tritt mit anderen O-Wars-Kommandanten in Kontakt, verb&uuml;nde dich mit ihnen, handle mit ihnen oder f&uuml;hre erbarmungslosen Krieg gegen sie und ihre Verb&uuml;ndeten.<br /><br />
	<i>Damit das ganze Fair bleibt, darf jeder Spieler nat&uuml;rlich nur einen Account haben.</i><br /><br />
	Also los, du musst nurnoch alle Felder ausfuellen und auf registrieren Klicken.<br /><br /><i>Die Mitgliedschaft bei O-Wars ist komplett kostenfrei.</i>
	</b></p>
	<form enctype="multipart/form-data" action="register.php?'. SID .'" method="post">
	&nbsp;&nbsp;&nbsp;<b>Username:</b> G&uuml;ltige Zeichen: [0-9,A-Z,a-z, ], 1-16 Zeichen<br />
	&nbsp;&nbsp;&nbsp;<input type="text" name="user" value="'.$_POST['user'].'"><br />
	&nbsp;&nbsp;&nbsp;<b>Passwort:</b> G&uuml;ltige Zeichen: [0-9,A-Z,a-z], 4-12 Zeichen<br />
	&nbsp;&nbsp;&nbsp;<input type="password" name="pass" value="'.$_POST['pass'].'"><br />
	&nbsp;&nbsp;&nbsp;<b>E-Mail:</b><br />
	&nbsp;&nbsp;&nbsp;<input type="text" name="email" value="'.$_POST['email'].'"><br />
	&nbsp;&nbsp;&nbsp;<b>Basisname:</b> G&uuml;ltige Zeichen: alle, 1-16 Zeichen<br />
	&nbsp;&nbsp;&nbsp;<input type="text" name="basis" value="'.$_POST['basis'].'"><br />
	<br />
	<input type="checkbox" name="agb" value="accepted" />
	Ich bin mit den <a href="http://portal.o-wars.de/index.php?action=regeln" target="_new">AGB</a> einverstanden.
	<br /><br /><input type="hidden" name="login" value="1">
	<input type="submit" name="submit" value="Registrieren"></span><br /><p class="red">'.$error.'</p></td></tr></table>';
}

echo $content.'</body></html>';
?>