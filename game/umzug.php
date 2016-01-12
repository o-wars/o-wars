<?php
//////////////////////////////////
// einstellungen.php            //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include("functions.php");

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
$ressis = ressistand($_SESSION['user']['omni']);
$content .= $ressis['html'];

$dbh = db_connect();

$content .= template('umzug');

$result = mysql_query("SELECT umzug FROM `user` WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
$timestamp = mysql_fetch_array($result);

$result = mysql_query("SELECT omni FROM `user` ORDER BY `omni` DESC LIMIT 1;");
$last = mysql_fetch_array($result);

if ($_POST['ubl'] < $last['omni'] AND $_POST['ubl'] > 0 AND $_POST['ubl'] == number_format($_POST['ubl'],0,'','') AND $timestamp['umzug'] <= time()-24*60*60*14) {
	$entfernung = entfernung($_SESSION['user']['omni'],$_POST['ubl'])*2.5;
	$result = mysql_query("SELECT * FROM `user` WHERE `omni` = ".$_POST['ubl']." LIMIT 1;");
	$row = mysql_fetch_array($result);
	if ($row) { 	
		$status = '<b>Basis ist bereits bewohnt.</b><br /><form enctype="multipart/form-data" action="umzug.php?'.SID.'" method="post">
		<b>Zielbasis: <input type="text" style="width:50px" name="ubl" onChange="berechne();" onkeyup="berechne();"  />
		 - Entfernung: <a id="entfernung">-</a> km<br />
		<input type="submit" name="submit" value="umziehen" /></b>
		</form>'; 
	} elseif ($entfernung > 50) {
		$status = '<b>Basis ist zu weit entfernt.</b><br /><form enctype="multipart/form-data" action="umzug.php?'.SID.'" method="post">
		<b>Zielbasis: <input type="text" style="width:50px" name="ubl" onChange="berechne();" onkeyup="berechne();"  />
		 - Entfernung: <a id="entfernung">-</a> km<br />
		<input type="submit" name="submit" value="umziehen" /></b>
		</form>'; 	
	} else {
		deluser($_POST['ubl']);
		mysql_query("UPDATE `user` SET `omni` = '".$_POST['ubl']."', `umzug` = '".time()."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
		mysql_query("DELETE FROM `berichte` WHERE `to` = '".$_SESSION['user']['omni']."';");
		mysql_query("DELETE FROM `fabrik` WHERE `omni` = '".$_SESSION['user']['omni']."';");
		mysql_query("UPDATE `forschungen` SET `nextpanzerung` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextmotor` = '0', `nextfeuerwaffen` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextspionage` = '0', `nextfuehrung` = '0', `nextcyborgtechnik` = '0', `nextminen` = '0', `nextrad` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
		mysql_query("UPDATE `gebauede` SET `nextbasis` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextraketensilo` = '0', `nextnbz` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextmissionszentrum` = '0', `nextagentenzentrum` = '0', `nextraumstation` = '0', `nextrohstofflager` = '0', `nexteisenmine` = '0', `nexttitanmine` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
		mysql_query("UPDATE `nachrichten` SET `to` = '".$_POST['ubl']."' WHERE `to` = '".$_SESSION['user']['omni']."';");
		mysql_query("UPDATE `nachrichten` SET `from` = '".$_POST['ubl']."' WHERE `from` = '".$_SESSION['user']['omni']."';");		
		mysql_query("UPDATE `defense` SET `omni` = '".$_POST['ubl']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
		mysql_query("UPDATE `munition` SET `omni` = '".$_POST['ubl']."' WHERE `omni` = '".$_SESSION['user']['omni']."';");
		mysql_query("UPDATE `forschungen` SET `omni` = '".$_POST['ubl']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
		mysql_query("UPDATE `gebauede` SET `omni` = '".$_POST['ubl']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
		mysql_query("UPDATE `hangar` SET `omni` = '".$_POST['ubl']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
		mysql_query("UPDATE `ressis` SET `omni` = '".$_POST['ubl']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
		mysql_query("UPDATE `raketen` SET `omni` = '".$_POST['ubl']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
		mysql_query("UPDATE `raumstation` SET `omni` = '".$_POST['ubl']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
		mysql_query("UPDATE `id` SET `omni` = '".$_POST['ubl']."' WHERE `id` = '".$_SESSION['user']['omni']."' LIMIT 1;");
		mysql_query("UPDATE `scans` SET `userid` = '".$_POST['ubl']."' WHERE `userid` = '".$_SESSION['user']['omni']."';");
		mysql_query("UPDATE `clans` SET `userid` = '".$_POST['ubl']."' WHERE `userid` = '".$_SESSION['user']['omni']."';");
		mysql_query("UPDATE `karte` SET `omni` = '".$_POST['ubl']."' WHERE `omni` = '".$_SESSION['user']['omni']."';");
		mysql_query("UPDATE `karte` SET `id` = '".$_POST['ubl']."' WHERE `id` = '".$_SESSION['user']['omni']."';");
		mysql_query("UPDATE `stats` SET `id` = '".$_POST['ubl']."' WHERE `id` = '".$_SESSION['user']['omni']."';");
		mysql_query("UPDATE `cards` SET `offender` = '".$_POST['ubl']."' WHERE `offender` = '".$_SESSION['user']['omni']."';");
		mysql_query("UPDATE `cards` SET `defender` = '".$_POST['ubl']."' WHERE `defender` = '".$_SESSION['user']['omni']."';");
		mysql_query("UPDATE `logins` SET `userid` = '".$_POST['ubl']."' WHERE `userid` = '".$_SESSION['user']['omni']."';");
		mysql_query("UPDATE `forum_threads` SET `uid` = '".$_POST['ubl']."' WHERE `uid` = '".$_SESSION['user']['omni']."';");
		mysql_query("UPDATE `forum_posts` SET `uid` = '".$_POST['ubl']."' WHERE `uid` = '".$_SESSION['user']['omni']."';");		

		$result = mysql_query("SELECT * FROM `missionen` WHERE `start` = '".$_SESSION['user']['omni']."';");
		
		do {
			$row = mysql_fetch_array($result);
			if ($row) {
				$return = rand(3600,4*3600);
				mysql_query("INSERT INTO `missionen` ( `id` , `pid` , `parsed` , `type` , `start` , `ziel` , `started` , `ankunft` , `return` , `speed` , `einh1` , `einh2` , `einh3` , `einh4` , `einh5` , `einh6` , `einh7` , `einh8` , `einh9` , `einh10` , `einh11` , `einh12` , `einh13` , `einh14` , `einh15` , `eisen` , `titan` , `oel` , `uran` , `gold` , `chanje` ) VALUES ( '', '".rand(100,999)."', '1', '2', '".$_POST['ubl']."', '0', '".(date(U)-$return)."', '".(date(U))."', '".(date(U)+$return)."', '10', '".$row['einh1']."', '".$row['einh2']."', '".$row['einh3']."', '".$row['einh4']."', '".$row['einh5']."', '".$row['einh6']."', '".$row['einh7']."', '".$row['einh8']."', '".$row['einh9']."', '".$row['einh10']."', '".$row['einh11']."', '".$row['einh12']."', '".$row['einh13']."', '".$row['einh14']."', '".$row['einh15']."', '".$row['eisen']."', '".$row['titan']."', '".$row['oel']."', '".$row['uran']."', '".$row['gold']."', '".$row['chanje']."' );");
				$eid = mysql_insert_id();
				mysql_query("INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '1', '".$eid."', '".(date(U)+$return)."');");
				mysql_query("DELETE FROM `events` WHERE `eid` = '".$row['id']."' AND `type` = '1';");
			}
		} while ($row);
		
		mysql_query("DELETE FROM `missionen` WHERE `start` = '".$_SESSION['user']['omni']."';");
		
		$status = '<b>Du bist erfolgreich umgezogen.<br /><br />Bitte logge dich neu ein.<br /><br /></b>';
		session_destroy();
	}
} elseif ($timestamp['umzug'] <= time()-24*60*60*14) {
	$status = '<form enctype="multipart/form-data" action="umzug.php?'.SID.'" method="post">
	<b>Zielbasis: <input type="text" style="width:50px" name="ubl" onChange="berechne();" onkeyup="berechne();"  />
	 - Entfernung: <a id="entfernung">-</a> km<br />
	<input type="submit" name="submit" value="umziehen" /></b>
	</form>';
} else {
	$status = '<b>Du hast die Umzugsfunktion in den letzten 14 Tagen bereits verwendet.<br />Ein Umzug ist erst wieder m&ouml;glich in:</b>';
	$status .= percentbar($timestamp['umzug']-(time()-24*60*60*14),24*60*60*14,400);
}

$content = tag2value('status', $status, $content);
$pos = position($_SESSION['user']['omni']);
$content = tag2value('x', $pos['x'], $content);
$content = tag2value('y', $pos['y'], $content);
$content = tag2value('z', $pos['z'], $content);
	
// send page to browser
$content = tag2value("onload", $onload, $content);
echo $content.template('footer');
?>