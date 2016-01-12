<?php 

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

header("Content-Type: text/xml");

// connect to db
$dbh = db_connect();

$status = 0;

if ($_GET['ubl']) {
	// fetch user
	$row = mysql_fetch_array(
		mysql_query("SELECT * FROM `user` WHERE `omni`='".intval($_GET['ubl'])."' LIMIT 1;"));

	$row ? $status = 1 : $status = 2;
	
	if ($status == 1) {

		$clan = mysql_fetch_array(
			mysql_query("SELECT * FROM `clans` WHERE `userid`='".intval($_GET['ubl'])."' LIMIT 1;"));
	
		if ($clan) {
	
			$clan = mysql_fetch_array(
				mysql_query("SELECT * FROM `clan_info` WHERE `clanid`='".$clan['clanid']."' LIMIT 1;"));	
		
			$clan = $clan['name'];
			
		}	
	}

	if (!$clan) {
	
		$clan = '-';
	
	}
	/*
	$to_position  = position($_GET['ubl']);
	$own_position = position($_SESSION['user']['omni']);
	#$own_pos = ($own_position['x']+($own_position['y']+($own_position['z']*500)));
	$own_pos = ($own_position['x']+($own_position['y']+($own_position['z']*20)));
	$to_pos  = ($to_position['x']+($to_position['y']+($to_position['z']*20)));

	if ( $own_position['x'] > $to_position['x'] ) { $entfernung = $own_position['x'] - $to_position['x']; }
	else { $entfernung = $to_position['x'] - $own_position['x']; }
	
	if ( ( $own_position['y'] + ( $own_position['z'] * 20 ) ) > ( $to_position['y'] + ( $to_position['z'] * 20 ) ) ) { $entfernung += ( $own_position['y'] + ( $own_position['z'] * 20 ) ) - ( $to_position['y'] + ( $to_position['z'] * 20 ) ); }
	else { $entfernung += ( $to_position['y'] + ( $to_position['z'] * 20 ) ) - ( $own_position['y'] + ( $own_position['z'] * 20 ) ); }	
	*/
	$xml = '  
  <ubl>'.$_GET['ubl'].'</ubl>
  
  <name>'.$row['name'].'</name>
  <base>'.$row['base'].'</base>
  
  <clan>'.$clan.'</clan>
  
  <dist>'.(2.5*entfernung($_GET['ubl'],$_SESSION['user']['omni'])).'</dist>
  
  <tf_e>'.$row['tf_eisen'].'</tf_e>
  <tf_t>'.$row['tf_titan'].'</tf_t>';
  
  if ($_GET['full']) {
  	$xml .= '  <ap>'.$row['points'].'</ap>
  <kp>'.$row['kampfpunkte'].'</kp>
  <pp>'.$row['plasmapunkte'].'</pp>';
  }


} elseif ($_GET['clan']) {

	$last = $_GET['sector'] * 500 + 500;
	$result = mysql_query("SELECT userid FROM `clans` WHERE userid <= ".($last)." and userid > ".($last-500)." and clanid = ".intval($_GET['clan']).";");

	for ($row = mysql_fetch_array($result);$row;$row = mysql_fetch_array($result)) {

		$xml .= '  <ubl>'.$row['userid']."</ubl>\n";
	
	}
	
} elseif ($_GET['status']) {

	$last = $_GET['sector'] * 500 + 500;
	
	if ($_GET['status'] == 4) {
		
		list($clanid) = mysql_fetch_array(
			mysql_query("SELECT clanid FROM `clans` WHERE userid = ".$_SESSION['user']['omni'].";"));
			
		$result = mysql_query("SELECT userid FROM `clans` WHERE userid <= ".($last)." and userid > ".($last-500)." and clanid = ".$clanid.";");
		
	} elseif ($_GET['status'] == 5) {
		
		$warlist = "clanid = '0'";

		list($clanid) = mysql_fetch_array(
			mysql_query("SELECT clanid FROM `clans` WHERE userid = ".$_SESSION['user']['omni'].";"));		
		
		$result = mysql_query("SELECT clan1 as clan FROM `clanwars` WHERE clan2 = ".$clanid." AND ended = 0;");
		
		for ($wars = mysql_fetch_array($result);$wars;$wars = mysql_fetch_array($result)) {
		
			$warlist .= " OR clanid = '".$wars['clan']."'";
		
		}

		$result = mysql_query("SELECT clan2 as clan FROM `clanwars` WHERE clan1 = ".$clanid." AND ended = 0;");		
			
		for ($wars = mysql_fetch_array($result);$wars;$wars = mysql_fetch_array($result)) {
		
			$warlist .= " OR clanid = '".$wars['clan']."'";
		
		}				
		$result = mysql_query("SELECT userid FROM `clans` WHERE ".$warlist." AND userid <= ".($last)." AND userid > ".($last-500).";");
		
	} elseif ($_GET['status'] == 6) {
		
		$result = mysql_query("SELECT omni as userid FROM `user` WHERE tf_eisen+tf_titan > 2500 and omni <= ".($last)." and omni > ".($last-500).";");
			
	} elseif ($_GET['status'] == 7) {
		
		$result = mysql_query("SELECT omni as userid FROM `user` WHERE timestamp < ".(time()-1209600)." and omni <= ".($last)." and omni > ".($last-500).";");
			
	} else {
		
		$result = mysql_query("SELECT omni as userid FROM `karte` WHERE omni <= ".($last)." and omni > ".($last-500)." and id = ".$_SESSION['user']['omni']." and type=".intval($_GET['status']).";");
	
	}

	for ($row = mysql_fetch_array($result);$row;$row = mysql_fetch_array($result)) {

		$xml .= '  <ubl>'.$row['userid']."</ubl>\n";
	
	}
	
}

if ($_SESSION['user']['timeout'] < date('U')){
	$status = 99;
}
if ($_SESSION['user']['name'] == ''){
	$status = 99;
}
if ($_SESSION['user']['omni'] == ''){
	$status = 99;
}
if ($_SESSION['user']['ip']   != $_SERVER['REMOTE_ADDR']){
	$status = 99;
}
if ($_SESSION['user']['browser'] != $_SERVER['HTTP_USER_AGENT']){
	$status = 99;
}

if ($status == 99) {
	$xml = "<error>not authorized</error>";
}

echo '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n<ow>\n  <status>".$status."</status>\n".$xml."\n</ow>\n";
$_SESSION['r']++;
?>