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

$dbh = db_connect();

// html head setzen
$content = template('head');

$content .= '<center>
<font size="+2"><u>O-Wars Rekrutierung</u></font><br /><br />
</center>';

$select = "SELECT * FROM `user_new` WHERE `id` = '".$_GET['id']."' AND `key` = '".$_GET['key']."';";
$result = mysql_query($select);
if (@mysql_num_rows($result) == 0){ $error .= '<b>FEHLER:</b> Der angegebene Account konnte nicht gefunden werden.<br />';}

if (!$error){ 
		$user   = mysql_fetch_array($result);
		$select = "INSERT INTO `user` ( `omni` , `name` , `base` , `clan` , `tf_eisen` , `tf_titan` , `email` , `timestamp` , `password` , `browser` , `ip` ) VALUES ('', '".$user['user']."', '".$user['base']."', '', '0', '0', '".$user['email']."', '".date('U')."', '".md5($user['pass'])."', '', '');";
		$result = mysql_query($select);
		$id = mysql_insert_id();
		$select = "INSERT INTO `defense` ( `omni` , `def1` , `def2` , `def3` , `def4` , `def5` , `def6` , `def7` , `def8` , `def9` , `def10` ) VALUES ( '".$id."', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0' );";
		mysql_query($select);
		$select = "INSERT INTO `forschungen` ( `omni` , `panzerung` , `nextpanzerung` , `reaktor` , `nextreaktor` , `panzerketten` , `nextpanzerketten` , `motor` , `nextmotor` , `feuerwaffen` , `nextfeuerwaffen` , `raketen` , `nextraketen` , `sprengstoff` , `nextsprengstoff` , `spionage` , `nextspionage` , `fuehrung` , `nextfuehrung` , `minen` , `nextminen` , `cyborgtechnik` , `nextcyborgtechnik` , `rad` , `nextrad` ) VALUES ( '".$id."', '0', '0', '0', '0', '0', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0' );";
		mysql_query($select);
		$select = "INSERT INTO `gebauede` ( `omni` , `basis` , `nextbasis` , `forschungsanlage` , `nextforschungsanlage` , `fabrik` , `nextfabrik` , `raketensilo` , `nextraketensilo` , `nbz` , `nextnbz` , `hangar` , `nexthangar` , `fahrwege` , `nextfahrwege` , `missionszentrum` , `nextmissionszentrum` , `agentenzentrum` , `nextagentenzentrum` , `raumstation` , `nextraumstation` , `rohstofflager` , `nextrohstofflager` , `eisenmine` , `nexteisenmine` , `titanmine` , `nexttitanmine` , `oelpumpe` , `nextoelpumpe` , `uranmine` , `nexturanmine` ) VALUES ( '".$id."', '1', '0', '1', '0', '1', '0', '0', '0', '0', '0', '1', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0' );";
		mysql_query($select);
		$select = "INSERT INTO `hangar` ( `omni` , `einh1` , `einh2` , `einh3` , `einh4` , `einh5` , `einh6` , `einh7` , `einh8` , `einh9` , `einh10` , `einh11` , `einh12` , `einh13` , `einh14` , `einh15` ) VALUES ( '".$id."', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0' );";
		mysql_query($select);
		$select = "INSERT INTO `ressis` ( `omni` , `eisentimestamp` , `eisen` , `titantimestamp` , `titan` , `oeltimestamp` , `oel` , `urantimestamp` , `uran` , `goldtimestamp` , `gold` , `chanje` ) VALUES ( '".$id."', '".date('U')."', '500', '".date('U')."', '500', '".date('U')."', '500', '".date('U')."', '100', '".date('U')."', '20', '0' );";
		mysql_query($select);
		$select = "INSERT INTO `raketen` ( `omni` , `einh1` , `einh2` , `einh3` , `einh4` , `einh5` , `einh6` ) VALUES ( '".$id."', '0', '0', '0', '0', '0', '0' );";
		mysql_query($select);		
		$select = "INSERT INTO `raumstation` ( `omni` ) VALUES ( '".$id."' );";
		mysql_query($select);		
		$select = "INSERT INTO `stats` ( `id` , `vk1` , `vk2` , `vk3` , `vk4` , `vk5` , `vk6` , `vk7` , `vk8` , `vk9` , `vk10` , `vk11` , `vk12` , `vk13` , `vk14` , `vk15` , `vp1` , `vp2` , `vp3` , `vp4` , `vp5` , `vp6` , `vp7` , `vp8` , `vp9` , `vp10` , `vp11` , `vp12` , `vp13` , `vp14` , `vp15` , `vr1` , `vr2` , `vr3` , `vr4` , `vr5` , `vr6` , `vr7` , `vr8` , `vr9` , `vr10` , `vr11` , `vr12` , `vr13` , `vr14` , `vr15` , `dk1` , `dk2` , `dk3` , `dk4` , `dk5` , `dk6` , `dk7` , `dk8` , `dk9` , `dk10` , `dk11` , `dk12` , `dk13` , `dk14` , `dk15` , `dp1` , `dp2` , `dp3` , `dp4` , `dp5` , `dp6` , `dp7` , `dp8` , `dp9` , `dp10` , `dp11` , `dp12` , `dp13` , `dp14` , `dp15` , `dr1` , `dr2` , `dr3` , `dr4` , `dr5` , `dr6` , `dr7` , `dr8` , `dr9` , `dr10` , `dr11` , `dr12` , `dr13` , `dr14` , `dr15` , `farm_eisen` , `farm_titan` , `farm_oel` , `farm_uran` , `farm_gold` , `ripped_eisen` , `ripped_titan` , `ripped_oel` , `ripped_uran` , `ripped_gold` , `missions` ) VALUES ( '".$id."', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0' );";
		mysql_query($select);
		$select = "DELETE FROM `user_new` WHERE `id` = '".$_GET['id']."' AND `key` = '".$_GET['key']."';";
		mysql_query($select);
		$select = "INSERT INTO `nachrichten` ( `id` , `from` , `from_name` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', '1', 'TheBunman', '".$id."', UNIX_TIMESTAMP( ) , '0', 'Willkommen bei O-Wars', 'Hallo ".$user['user'].",\n\n willkommen bei O-Wars !!!\nIch hoffe du wirst viel Spass mit diesem Spiel haben.\n\nSolltest du Hilfe ben&ouml;tigen, schaue dir die Anleitung unter <a href=\"http://wiki.o-wars.de/\">http://wiki.o-wars.de/</a> an.\nSolltest du dort nicht f&uuml;ndig werden, melde dich einfach im Forum.\n\nViele erfolgreiche Bash`s w&uuml;nscht dir\nDerBunman');";
		mysql_query($select);
		$content .= nl2br($mail).'<br /><br /><b>Du bist nun erfolgreich registriert, du kannst dich sofort einloggen.<br /><br />
		<a class="red" href="http://portal.o-wars.de/"><b>zum Portal</b><br />
		<a class="red" href="http://game.o-wars.de/"><b>zum Login ohne Portal</b><br />
		</b><br />';
} else $content .= $error;

echo $content.'</body></html>';
?>