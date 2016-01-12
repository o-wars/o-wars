<?PHP
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";
include "../version.php";

// check session
logincheck();

$menu = template('menu');
$menu = tag2value('date', date('M, d Y H:i:s'), $menu);
$menu = tag2value('version', $version, $menu);

$refresh=600;

$dbh = db_connect();

$select = "UPDATE `user` SET `timestamp` = '".date(U)."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;";
$result = mysql_query($select);

$select = "SELECT * FROM `missionen` WHERE `ziel` = '".$_SESSION[user][omni]."' AND `type` = '1' AND `ankunft` > '".date(U)."' AND `parsed` != '1' ORDER BY `ankunft` DESC;";
$result = mysql_query($select);

if (mysql_num_rows($result) > 0){ 
	$menu = str_replace('logo.gif', 'attack.gif', $menu); 
	$row = mysql_fetch_array($result);
	if ($row['ankunft']-time() < 600) {
		$refresh=$row['ankunft']-time();
	}
}

$select = "SELECT * FROM `berichte` WHERE `gelesen` = 0 AND `to` = '".$_SESSION[user][omni]."' ORDER BY id DESC;";
$selectResult   = mysql_query($select);

$menu = tag2value('berichte', mysql_num_rows($selectResult), $menu);

$select = "SELECT * FROM `nachrichten` WHERE `gelesen` = 0 AND `to` = '".$_SESSION[user][omni]."' ORDER BY id DESC;";
$selectResult   = mysql_query($select);

$menu = tag2value('nachrichten', mysql_num_rows($selectResult), $menu);

$menu = tag2value('refresh', $refresh, $menu);

$select = "SELECT karte_neu FROM `user` WHERE `omni` = ".$_SESSION['user']['omni']." LIMIT 1;";
$result   = mysql_query($select);
$row = mysql_fetch_array($result);

if ($row['karte_neu'] == 1) {

	$karte = 'karte_neu.php';
	
} else {

	$karte = 'karte.php';

}

$menu = tag2value('karte', $karte, $menu);

$casino = mysql_num_rows(
	mysql_query("SELECT * FROM `cards` WHERE `defender` = '".$_SESSION['user']['omni']."' and `status` = '1';"));
	
$menu = tag2value('casino', $casino, $menu);	

echo $menu;
?>