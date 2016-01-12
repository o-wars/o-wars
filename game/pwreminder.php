<?
// Basisfunktionen laden
include "functions.php";

// html head setzen
$content = template('head');

$dbh = db_connect();

if ($_POST['user'] and $_POST['email']) {
	$select = "SELECT * FROM `user` WHERE `name` = '".htmlspecialchars($_POST['user'])."' AND `email` = '".htmlspecialchars($_POST['email'])."' LIMIT 1;";
	$result = mysql_query($select);
	$row = @mysql_fetch_array($result);
	if ($row) {
		$resetpw = randompass(rand(16,32));
		mail ($_POST['email'],'Passwort vergessen.','Hallo '.$_POST['user']."\n\nJemand hat einen Passwort wechsel fuer deinen Account beantragt. Solltest du das nicht gewesen sein, ignoriere diese Email bitte.\n\nSolltest du es jedoch gewesen sein, folge diesem Link: http://game.o-wars.de/pwreminder.php?resetkey=".$resetpw."&base=".$row['omni']."\n\nGueltigkeit dieses Links ist bis heute 23:59 Uhr. Danach ist dieser Link ungueltig!!!\n\nWenn du trotzdem noch Probleme haben solltest, schicke eine E-Mail an: webmaster@o-wars.de\n\n\nMfG\nDerPWReminderBot","From: no-reply@o-wars.de");
		mail ($row['email2'],'Passwort vergessen.','Hallo '.$_POST['user']."\n\nJemand hat einen Passwort wechsel fuer deinen Account beantragt. Solltest du das nicht gewesen sein, ignoriere diese Email bitte.\n\nSolltest du es jedoch gewesen sein, folge diesem Link: http://game.o-wars.de/pwreminder.php?resetkey=".$resetpw."&base=".$row['omni']."\n\nGueltigkeit dieses Links ist bis heute 23:59 Uhr. Danach ist dieser Link ungueltig!!!\n\nWenn du trotzdem noch Probleme haben solltest, schicke eine E-Mail an: webmaster@o-wars.de\n\n\nMfG\nDerPWReminderBot","From: no-reply@o-wars.de");
		mysql_query("UPDATE `user` SET `resetpw` = MD5( '".$resetpw."' ) WHERE `omni` = '".$row['omni']."' LIMIT 1;");
		$content .= "Email mit weiteren Instruktionen an ".$_POST['email'].' gesendet.';
	} else {
		$content .= "Falsche User/Email Kombination.";
	}
} elseif ($_POST['resetkey'] and $_POST['pw1'] and $_POST['pw2']){
	$select = "SELECT * FROM `user` WHERE `omni` = '".htmlspecialchars($_POST['base'])."' AND `resetpw` = '".md5(htmlspecialchars($_POST['resetkey']))."' LIMIT 1;";
	$result = mysql_query($select);
	$row = @mysql_fetch_array($result);
	if ($row) {	
		if ($_POST['pw1'] == $_POST['pw2']) {
			$select = "UPDATE `user` SET `password` = MD5( '".htmlspecialchars($_POST['pw1'])."' ) WHERE `omni` = '".$row['omni']."' LIMIT 1;";
			mysql_query($select);
			$select = "UPDATE `user` SET `resetpw` = '0' WHERE `omni` = '".$row['omni']."' LIMIT 1;";
			mysql_query($select);
			$content .= 'Passwort erfolgreich ge&auml;ndert';
		} else {$content .= 'Passw&ouml;rter sind nicht geleich.';}
	} else {$content .= 'FEHLER!!!!! Der Resetkey stimmt nicht.';}
} elseif ($_GET['resetkey']){
	$select = "SELECT * FROM `user` WHERE `omni` = '".htmlspecialchars($_GET['base'])."' AND `resetpw` = '".md5(htmlspecialchars($_GET['resetkey']))."' LIMIT 1;";
	$result = mysql_query($select);
	$row = @mysql_fetch_array($result);

	if ($row) {
		$content .= '<b>Gib dein neues Passwort 2x ein.</b>
		<br /><br />
		<form enctype="multipart/form-data" action="pwreminder.php" method="post">
		Neues Passwort: <input type="text" name="pw1" /><br />
		Neues Passwort: <input type="text" name="pw2" /><br />
		<input type="submit" value="neues Passwort speichern" /><br />
		<input type="hidden" name="resetkey" value="'.$_GET['resetkey'].'" />
		<input type="hidden" name="base" value="'.$_GET['base'].'" />
		</form>';
	} else {
		$content .= 'FEHLER!!! Der Resetkey stimmt nicht.';
	}
} else {
	$content .= '<b>Solltest du dein Passwort vergessen haben, kannst du dir hier ein neues holen.</b>
	<br /><br />
	<form enctype="multipart/form-data" action="pwreminder.php" method="post">
	<table border="0">
		<tr>
			<td>
				Username: 
			</td>
			<td>
				<input type="text" name="user" /><br />
			</td>
		</tr>
		<tr>
			<td>
				Registrierungs-E-Mail:
			</td>
			<td>
				<input type="text" name="email" /><br />
			</td>
		</tr>		
	</table><br />
	<input type="submit" value="neues Passwort holen" /><br />
	</form>';
}

echo $content;

function randompass($len = "8"){
 $pass = NULL;
 for($i=0; $i<$len; $i++) {
   $char = chr(rand(48,122));
   while (!ereg("[a-zA-Z0-9]", $char)){
     if($char == $lchar) continue;
     $char = chr(rand(48,90));
   }
   $trash .= $char;
   $lchar = $char;
 }
 return $trash;
}
?>