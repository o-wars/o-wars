<?php

highscores();

touch('../temp/timestamps/points');

function highscores() {
$dbh = db_connect();

$select = "SELECT * FROM `user`;";
$result = mysql_query($select);

include '../gebaeude_preise.php';
include '../forschung_preise.php';
include '../def_preise.php';
include '../einheiten_preise.php';

$i=0;

do {
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	if ($row) {
		$select2 = "SELECT * FROM `gebauede` WHERE `omni` = '".$row['omni']."';";
		$result2 = mysql_query($select2);
		$gebaeude = mysql_fetch_array($result2, MYSQL_ASSOC);
		
		$select3 = "SELECT * FROM `forschungen` WHERE `omni` = '".$row['omni']."';";
		$result3 = mysql_query($select3);
		$forschung = mysql_fetch_array($result3, MYSQL_ASSOC);
		
		$select4 = "SELECT * FROM `hangar` WHERE `omni` = '".$row['omni']."';";
		$result4 = mysql_query($select4);
		$hangar = mysql_fetch_array($result4, MYSQL_ASSOC);
		
		$count = 0;
		do {
			$count++;
			$type = 'einh'.$count;
			$points[$i]['points'] += $hangar[$type] * $einh[$count]['eisen'];
			$points[$i]['points'] += $hangar[$type] * $einh[$count]['titan'];
			$points[$i]['points'] += $hangar[$type] * $einh[$count]['oel'];
			$points[$i]['points'] += $hangar[$type] * $einh[$count]['uran'];
			$points[$i]['points'] += $hangar[$type] * $einh[$count]['gold'];
			$points[$i]['points'] += $hangar[$type] * $einh[$count]['chanje']*1000;
		} while ( 14 >= $count );
		
		$select5 = "SELECT * FROM `missionen` WHERE `start` = '".$row['omni']."';";
		$result5 = mysql_query($select5);
		do {
			$mission = mysql_fetch_array($result5, MYSQL_ASSOC);
			$count = 0;
			do {
				$count++;
				$type = 'einh'.$count;
				$points[$i]['points'] += $mission[$type] * $einh[$count]['eisen'];
				$points[$i]['points'] += $mission[$type] * $einh[$count]['titan'];
				$points[$i]['points'] += $mission[$type] * $einh[$count]['oel'];
				$points[$i]['points'] += $mission[$type] * $einh[$count]['uran'];
				$points[$i]['points'] += $mission[$type] * $einh[$count]['gold'];
				$points[$i]['points'] += $mission[$type] * $einh[$count]['chanje']*1000;
			} while ( 14 >= $count );
		} while ($mission);
		
		$select6 = "SELECT * FROM `defense` WHERE `omni` = '".$row['omni']."';";
		$result6 = mysql_query($select6);
		$defense = mysql_fetch_array($result6, MYSQL_ASSOC);
		
		$count = 0;
		do {
			$count++;
			$type = 'def'.$count;
			$points[$i]['points'] += $defense[$type] * $def[$count]['eisen'];
			$points[$i]['points'] += $defense[$type] * $def[$count]['titan'];
			$points[$i]['points'] += $defense[$type] * $def[$count]['oel'];
			$points[$i]['points'] += $defense[$type] * $def[$count]['uran'];
			$points[$i]['points'] += $defense[$type] * $def[$count]['gold'];
			$points[$i]['points'] += $defense[$type] * $def[$count]['chanje']*1000;
		} while ( 10 >= $count );
		
		
		$points[$i]['omni'] = $row['omni'];
		$points[$i]['kp']   = $row['kampfpunkte'];
		$points[$i]['points'] += used_ressis($gebaeude['basis'], $kosten['basis']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['basis'], $kosten['basis']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['basis'], $kosten['basis']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['basis'], $kosten['basis']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['basis'], $kosten['basis']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['basis'], $kosten['basis']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($gebaeude['forschungsanlage'], $kosten['forschungsanlage']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['forschungsanlage'], $kosten['forschungsanlage']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['forschungsanlage'], $kosten['forschungsanlage']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['forschungsanlage'], $kosten['forschungsanlage']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['forschungsanlage'], $kosten['forschungsanlage']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['forschungsanlage'], $kosten['forschungsanlage']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($gebaeude['fabrik'], $kosten['fabrik']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['fabrik'], $kosten['fabrik']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['fabrik'], $kosten['fabrik']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['fabrik'], $kosten['fabrik']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['fabrik'], $kosten['fabrik']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['fabrik'], $kosten['fabrik']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($gebaeude['raketensilo'], $kosten['raketensilo']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['raketensilo'], $kosten['raketensilo']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['raketensilo'], $kosten['raketensilo']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['raketensilo'], $kosten['raketensilo']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['raketensilo'], $kosten['raketensilo']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['raketensilo'], $kosten['raketensilo']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($gebaeude['nbz'], $kosten['nbz']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['nbz'], $kosten['nbz']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['nbz'], $kosten['nbz']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['nbz'], $kosten['nbz']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['nbz'], $kosten['nbz']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['nbz'], $kosten['nbz']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($gebaeude['hangar'], $kosten['hangar']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['hangar'], $kosten['hangar']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['hangar'], $kosten['hangar']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['hangar'], $kosten['hangar']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['hangar'], $kosten['hangar']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['hangar'], $kosten['hangar']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($gebaeude['fahrwege'], $kosten['fahrwege']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['fahrwege'], $kosten['fahrwege']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['fahrwege'], $kosten['fahrwege']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['fahrwege'], $kosten['fahrwege']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['fahrwege'], $kosten['fahrwege']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['fahrwege'], $kosten['fahrwege']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($gebaeude['missionszentrum'], $kosten['missionszentrum']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['missionszentrum'], $kosten['missionszentrum']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['missionszentrum'], $kosten['missionszentrum']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['missionszentrum'], $kosten['missionszentrum']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['missionszentrum'], $kosten['missionszentrum']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['missionszentrum'], $kosten['missionszentrum']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($gebaeude['agentenzentrum'], $kosten['agentenzentrum']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['agentenzentrum'], $kosten['agentenzentrum']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['agentenzentrum'], $kosten['agentenzentrum']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['agentenzentrum'], $kosten['agentenzentrum']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['agentenzentrum'], $kosten['agentenzentrum']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['agentenzentrum'], $kosten['agentenzentrum']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($gebaeude['raumstation'], $kosten['raumstation']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['raumstation'], $kosten['raumstation']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['raumstation'], $kosten['raumstation']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['raumstation'], $kosten['raumstation']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['raumstation'], $kosten['raumstation']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['raumstation'], $kosten['raumstation']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($gebaeude['rohstofflager'], $kosten['rohstofflager']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['rohstofflager'], $kosten['rohstofflager']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['rohstofflager'], $kosten['rohstofflager']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['rohstofflager'], $kosten['rohstofflager']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['rohstofflager'], $kosten['rohstofflager']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['rohstofflager'], $kosten['rohstofflager']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($gebaeude['eisenmine'], $kosten['eisenmine']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['eisenmine'], $kosten['eisenmine']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['eisenmine'], $kosten['eisenmine']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['eisenmine'], $kosten['eisenmine']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['eisenmine'], $kosten['eisenmine']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['eisenmine'], $kosten['eisenmine']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($gebaeude['titanmine'], $kosten['titanmine']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['titanmine'], $kosten['titanmine']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['titanmine'], $kosten['titanmine']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['titanmine'], $kosten['titanmine']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['titanmine'], $kosten['titanmine']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['titanmine'], $kosten['titanmine']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($gebaeude['oelpumpe'], $kosten['oelpumpe']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['oelpumpe'], $kosten['oelpumpe']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['oelpumpe'], $kosten['oelpumpe']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['oelpumpe'], $kosten['oelpumpe']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['oelpumpe'], $kosten['oelpumpe']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['oelpumpe'], $kosten['oelpumpe']['chanje']*1000);
				
		$points[$i]['points'] += used_ressis($gebaeude['uranmine'], $kosten['uranmine']['eisen']);
		$points[$i]['points'] += used_ressis($gebaeude['uranmine'], $kosten['uranmine']['titan']);
		$points[$i]['points'] += used_ressis($gebaeude['uranmine'], $kosten['uranmine']['oel']);
		$points[$i]['points'] += used_ressis($gebaeude['uranmine'], $kosten['uranmine']['uran']);
		$points[$i]['points'] += used_ressis($gebaeude['uranmine'], $kosten['uranmine']['gold']);
		$points[$i]['points'] += used_ressis($gebaeude['uranmine'], $kosten['uranmine']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($forschung['panzerung'], $kosten['panzerung']['eisen']);
		$points[$i]['points'] += used_ressis($forschung['panzerung'], $kosten['panzerung']['titan']);
		$points[$i]['points'] += used_ressis($forschung['panzerung'], $kosten['panzerung']['oel']);
		$points[$i]['points'] += used_ressis($forschung['panzerung'], $kosten['panzerung']['uran']);
		$points[$i]['points'] += used_ressis($forschung['panzerung'], $kosten['panzerung']['gold']);
		$points[$i]['points'] += used_ressis($forschung['panzerung'], $kosten['panzerung']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($forschung['reaktor'], $kosten['reaktor']['eisen']);
		$points[$i]['points'] += used_ressis($forschung['reaktor'], $kosten['reaktor']['titan']);
		$points[$i]['points'] += used_ressis($forschung['reaktor'], $kosten['reaktor']['oel']);
		$points[$i]['points'] += used_ressis($forschung['reaktor'], $kosten['reaktor']['uran']);
		$points[$i]['points'] += used_ressis($forschung['reaktor'], $kosten['reaktor']['gold']);
		$points[$i]['points'] += used_ressis($forschung['reaktor'], $kosten['reaktor']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($forschung['panzerketten'], $kosten['panzerketten']['eisen']);
		$points[$i]['points'] += used_ressis($forschung['panzerketten'], $kosten['panzerketten']['titan']);
		$points[$i]['points'] += used_ressis($forschung['panzerketten'], $kosten['panzerketten']['oel']);
		$points[$i]['points'] += used_ressis($forschung['panzerketten'], $kosten['panzerketten']['uran']);
		$points[$i]['points'] += used_ressis($forschung['panzerketten'], $kosten['panzerketten']['gold']);
		$points[$i]['points'] += used_ressis($forschung['panzerketten'], $kosten['panzerketten']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($forschung['motor'], $kosten['motor']['eisen']);
		$points[$i]['points'] += used_ressis($forschung['motor'], $kosten['motor']['titan']);
		$points[$i]['points'] += used_ressis($forschung['motor'], $kosten['motor']['oel']);
		$points[$i]['points'] += used_ressis($forschung['motor'], $kosten['motor']['uran']);
		$points[$i]['points'] += used_ressis($forschung['motor'], $kosten['motor']['gold']);
		$points[$i]['points'] += used_ressis($forschung['motor'], $kosten['motor']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($forschung['feuerwaffen'], $kosten['feuerwaffen']['eisen']);
		$points[$i]['points'] += used_ressis($forschung['feuerwaffen'], $kosten['feuerwaffen']['titan']);
		$points[$i]['points'] += used_ressis($forschung['feuerwaffen'], $kosten['feuerwaffen']['oel']);
		$points[$i]['points'] += used_ressis($forschung['feuerwaffen'], $kosten['feuerwaffen']['uran']);
		$points[$i]['points'] += used_ressis($forschung['feuerwaffen'], $kosten['feuerwaffen']['gold']);
		$points[$i]['points'] += used_ressis($forschung['feuerwaffen'], $kosten['feuerwaffen']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($forschung['raketen'], $kosten['raketen']['eisen']);
		$points[$i]['points'] += used_ressis($forschung['raketen'], $kosten['raketen']['titan']);
		$points[$i]['points'] += used_ressis($forschung['raketen'], $kosten['raketen']['oel']);
		$points[$i]['points'] += used_ressis($forschung['raketen'], $kosten['raketen']['uran']);
		$points[$i]['points'] += used_ressis($forschung['raketen'], $kosten['raketen']['gold']);
		$points[$i]['points'] += used_ressis($forschung['raketen'], $kosten['raketen']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($forschung['sprengstoff'], $kosten['sprengstoff']['eisen']);
		$points[$i]['points'] += used_ressis($forschung['sprengstoff'], $kosten['sprengstoff']['titan']);
		$points[$i]['points'] += used_ressis($forschung['sprengstoff'], $kosten['sprengstoff']['oel']);
		$points[$i]['points'] += used_ressis($forschung['sprengstoff'], $kosten['sprengstoff']['uran']);
		$points[$i]['points'] += used_ressis($forschung['sprengstoff'], $kosten['sprengstoff']['gold']);
		$points[$i]['points'] += used_ressis($forschung['sprengstoff'], $kosten['sprengstoff']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($forschung['spionage'], $kosten['spionage']['eisen']);
		$points[$i]['points'] += used_ressis($forschung['spionage'], $kosten['spionage']['titan']);
		$points[$i]['points'] += used_ressis($forschung['spionage'], $kosten['spionage']['oel']);
		$points[$i]['points'] += used_ressis($forschung['spionage'], $kosten['spionage']['uran']);
		$points[$i]['points'] += used_ressis($forschung['spionage'], $kosten['spionage']['gold']);
		$points[$i]['points'] += used_ressis($forschung['spionage'], $kosten['spionage']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($forschung['fuehrung'], $kosten['fuehrung']['eisen']);
		$points[$i]['points'] += used_ressis($forschung['fuehrung'], $kosten['fuehrung']['titan']);
		$points[$i]['points'] += used_ressis($forschung['fuehrung'], $kosten['fuehrung']['oel']);
		$points[$i]['points'] += used_ressis($forschung['fuehrung'], $kosten['fuehrung']['uran']);
		$points[$i]['points'] += used_ressis($forschung['fuehrung'], $kosten['fuehrung']['gold']);
		$points[$i]['points'] += used_ressis($forschung['fuehrung'], $kosten['fuehrung']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($forschung['minen'], $kosten['minen']['eisen']);
		$points[$i]['points'] += used_ressis($forschung['minen'], $kosten['minen']['titan']);
		$points[$i]['points'] += used_ressis($forschung['minen'], $kosten['minen']['oel']);
		$points[$i]['points'] += used_ressis($forschung['minen'], $kosten['minen']['uran']);
		$points[$i]['points'] += used_ressis($forschung['minen'], $kosten['minen']['gold']);
		$points[$i]['points'] += used_ressis($forschung['minen'], $kosten['minen']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($forschung['cyborgtechnik'], $kosten['cyborgtechnik']['eisen']);
		$points[$i]['points'] += used_ressis($forschung['cyborgtechnik'], $kosten['cyborgtechnik']['titan']);
		$points[$i]['points'] += used_ressis($forschung['cyborgtechnik'], $kosten['cyborgtechnik']['oel']);
		$points[$i]['points'] += used_ressis($forschung['cyborgtechnik'], $kosten['cyborgtechnik']['uran']);
		$points[$i]['points'] += used_ressis($forschung['cyborgtechnik'], $kosten['cyborgtechnik']['gold']);
		$points[$i]['points'] += used_ressis($forschung['cyborgtechnik'], $kosten['cyborgtechnik']['chanje']*1000);
		
		$points[$i]['points'] += used_ressis($forschung['rad'], $kosten['rad']['eisen']);
		$points[$i]['points'] += used_ressis($forschung['rad'], $kosten['rad']['titan']);
		$points[$i]['points'] += used_ressis($forschung['rad'], $kosten['rad']['oel']);
		$points[$i]['points'] += used_ressis($forschung['rad'], $kosten['rad']['uran']);
		$points[$i]['points'] += used_ressis($forschung['rad'], $kosten['rad']['gold']);
		$points[$i]['points'] += used_ressis($forschung['rad'], $kosten['rad']['chanje']*1000);
		
		$i++;
	}
} while ($row);

$i=0;

while ($points[$i]){
	$select = "UPDATE `user` SET `points` = '".number_format(($points[$i]['points']/100),0,'.','')."' WHERE `omni` = '".$points[$i]['omni']."' LIMIT 1 ;";
	mysql_query($select);
	$select = "UPDATE `user` SET `gesamtpunkte` = '".number_format(($points[$i]['points']/100)+($points[$i]['kp']*10),0,'.','')."' WHERE `omni` = '".$points[$i]['omni']."' LIMIT 1 ;";
	mysql_query($select);	
	$i++;
}

	// clans
	$select = "SELECT * FROM `clan_info` WHERE 1;";
	$result = mysql_query($select);
	
	do {
		$row = mysql_fetch_array($result);
		if ($row){
			$points = 0;
			$select = "SELECT * FROM `clans` WHERE `clanid` = '".$row['clanid']."';";
			$result2= mysql_query($select);
			if (mysql_num_rows($result2) == 0){
				mysql_query("UPDATE `clan_info` SET `aufgeloest` = '".date('U')."' WHERE `clanid` = '".$row['clanid']."' LIMIT 1 ;");
			}else {
				do {
					$clanm = mysql_fetch_array($result2);
					$select = "SELECT * FROM `user` WHERE `omni` = '".$clanm['userid']."';";
					$result3= mysql_query($select);
					$point  = mysql_fetch_array($result3);
					$points += $point['points'];
					$points += $point['kampfpunkte']*10;
				} while ($clanm);
				$select = "UPDATE `clan_info` SET `points` = '".($points/100)."' WHERE `clanid` = '".$row['clanid']."' LIMIT 1;";
				mysql_query($select);
			}
		}
	} while ($row);
}

function used_ressis($lvl, $kosten) {
	if ($lvl != 0) {
		while ($i < $lvl) { 
			$i++; 
			$j += $kosten*pow($i,2);
		}
	} else { $j = 0; }
	return $j;
}

function db_connect() {
	include '../config.php';
	$dbh = mysql_pconnect($db_host, $db_user, $db_pass)
		or die("<h1>Could not connect</h1><b>Please check your configuration. The DB settings seem to be incorrect");
	mysql_select_db($db_database);
	return ($dbh);
}

?>