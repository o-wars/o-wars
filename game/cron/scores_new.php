<?php
set_time_limit(0);

touch('../temp/timestamps/points');

include('../functions.php');
include('../forschung_preise.php');
include('../gebaeude_preise.php');
include('../def_preise.php');
include('../einheiten_preise.php');

$time = microtime_float();

$file = "UBL,NAME,BASIS,AP,GP,KP,PP,".date('d.m.y_H:i')."\n";

$keys  = array_keys($kosten);

foreach ($keys as $key) {

	$gk[$key] = $kosten[$key]['eisen'] + $kosten[$key]['titan'] +
		  $kosten[$key]['oel']   + $kosten[$key]['uran']  +
		  $kosten[$key]['gold']	 + ($kosten[$key]['chanje']*1000);
		  
}

$i = 1;
foreach ($einh as $key) {

	$ek[$i] = $einh[$i]['eisen'] + $einh[$i]['titan'] +
		  $einh[$i]['oel']   + $einh[$i]['uran']  +
		  $einh[$i]['gold']	 + ($einh[$i]['chanje']*1000);
	
	$i++;
		  
}

$i = 1;
foreach ($def as $key) {

	$dk[$i] = $def[$i]['eisen'] + $def[$i]['titan'] +
		  $def[$i]['oel']   + $def[$i]['uran']  +
		  $def[$i]['gold']	 + ($def[$i]['chanje']*1000);
	
	$i++;
		  
}

$users = mysql_query("SELECT omni as ubl FROM user WHERE 1;");

while ($user = mysql_fetch_array($users)) {

	$geb = mysql_fetch_array(
		mysql_query("SELECT * FROM gebauede,forschungen WHERE gebauede.omni = ".$user['ubl']." AND forschungen.omni = ".$user['ubl']." LIMIT 1;"), MYSQL_ASSOC);
		
	foreach ($keys as $key) {

		$points[$user['ubl']]['ap'] += used_ressis($geb[$key], $gk[$key])/100;

	}
	
	$einheiten = mysql_fetch_array(
		mysql_query("SELECT * FROM `hangar` WHERE `omni` = '".$user['ubl']."';"), MYSQL_ASSOC);

	$i = 1;
	while (@array_key_exists('einh'.$i, $einheiten)) {
	
		$points[$user['ubl']]['ap'] += $einheiten['einh'.$i] * $ek[$i] / 100;#
		if ($sum) {$sum .= ",";}
		$sum .= "SUM(einh".$i.") as einh".$i;
		$i++;
		
	}

	$einheiten = mysql_fetch_array(
		mysql_query("SELECT ".$sum." FROM `missionen` WHERE `start` = '".$user['ubl']."';"), MYSQL_ASSOC);

	$i = 1;
	while (@array_key_exists('einh'.$i, $einheiten)) {
	
		$points[$user['ubl']]['ap'] += $einheiten['einh'.$i] * $ek[$i] / 100;
		$i++;
		
	}	
	
	$defense = mysql_fetch_array(
		mysql_query("SELECT * FROM `hangar` WHERE `omni` = '".$user['ubl']."';"), MYSQL_ASSOC);
	
	$i = 1;
	while (@array_key_exists('def'.$i, $defense)) {
	
		$points[$user['ubl']]['ap'] += $defense['def'.$i] * $dk[$i] / 100;
		$i++;
		
	}	
	
	$userinfo = mysql_fetch_array(
		mysql_query("SELECT * FROM user WHERE omni = ".$user['ubl']." LIMIT 1;"), MYSQL_ASSOC);
	
	mysql_query("UPDATE `user` SET `points` = '".$points[$user['ubl']]['ap']."', `gesamtpunkte` = '".($points[$user['ubl']]['ap']+($userinfo['kampfpunkte']*10))."' WHERE `omni` =".$user['ubl']." LIMIT 1;");		
	
	$file .= $userinfo['omni'].",".str_replace(',','.',$userinfo['name']).",".str_replace(',','.',$userinfo['base']).",".number_format($points[$user['ubl']]['ap'],0,'','').",".number_format(($points[$user['ubl']]['ap']+($userinfo['kampfpunkte']*10)),0,'','').",".$userinfo['kampfpunkte'].",".$userinfo['plasmapunkte']."\n";
		
	mysql_query("INSERT INTO `stats2` ( `id` , `uid` , `time` , `kp` , `pp` , `ap` , `gp` , `einh1` , `einh2` , `einh3` , `einh4` , `einh5` , `einh6` , `einh7` , `einh8` , `einh9` , `einh10` , `einh11` , `einh12` , `einh13` , `einh14` , `einh15` , `einh16` , `einh17` , `einh18` , `einh19` , `einh20` , `def1` , `def2` , `def3` , `def4` , `def5` , `def6` , `def7` , `def8` , `def9` , `def10` , `def11` , `def12` , `def13` , `def14` , `def15` )
VALUES (
NULL , '".$user['ubl']."', UNIX_TIMESTAMP( ) , '".$userinfo['kampfpunkte']."', '".$userinfo['plasmapunkte']."', '".$points[$user['ubl']]['ap']."', '".($points[$user['ubl']]['ap']+($userinfo['kampfpunkte']*10))."', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'
);");
	
}

$fp = fopen('../csv/'.date('y-m-d_H-i').".csv", 'w');
fputs($fp, $file, strlen($file));

$fp = fopen('../csv/current.csv', 'w');
fputs($fp, $file, strlen($file));


echo 'Dauer: '.(microtime_float()-$time).'<br>
	  User: '.count($points).'<br>
	  Dauer/User: '.((microtime_float()-$time)/count($points));

function used_ressis($lvl, $kosten) {
	if ($lvl != 0) {
		while ($i < $lvl) { 
			$i++; 
			$j += $kosten*pow($i,2);
		}
	} else { $j = 0; }
	return $j;
}
?>
