<?PHP
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

$_POST['user'] = htmlspecialchars($_POST['user']);
$_POST['pass'] = htmlspecialchars($_POST['pass']);

$dbh = db_connect();
$select = "SELECT * FROM `user` WHERE 1 AND `name` = '".$_POST[user]."' AND `password` = '".md5($_POST[pass])."' LIMIT 1;";
$result = mysql_query($select, $dbh);
$row = mysql_fetch_array($result);

$_SESSION['user']['name']    = $row['name'];
$_SESSION['user']['sig']     = $row['sig'];
$_SESSION['user']['omni']    = $row['omni'];
$_SESSION['user']['base']    = $row['base'];
$_SESSION['user']['clan']    = $row['clan'];
$_SESSION['user']['mail']    = $row['email'];
$_SESSION['user']['points']  = number_format($row['points'],0,'','.');
$_SESSION['user']['ip']      = $_SERVER['REMOTE_ADDR'];
$_SESSION['user']['browser'] = $_SERVER['HTTP_USER_AGENT'];

// supporter
$_SESSION['user']['supporter']    = 99999999999999;

// COOKIE
if (!strstr($_COOKIE["spacecookie"], md5($_SERVER['REMOTE_ADDR']))) {
	$value = md5($_SERVER['REMOTE_ADDR']).':'.$_COOKIE["spacecookie"];
	setcookie("spacecookie", $value, time()+7200);  /* expire in 2 hour */
} else {	
	$value = $_COOKIE["spacecookie"]; 
	setcookie("spacecookie", $value, time()+7200);  /* expire in 2 hour */
}
// END COOKIE

$select = "UPDATE `user` SET `timestamp` = '".date(U)."', `browser` = '".$_SESSION['user']['browser']."', `ip` = '".$_SESSION['user']['ip']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
$result = mysql_query($select);

//if ($_SESSION['code'] != $_POST['code']){die ('Login fehlgeschlagen, du hast einen falschen Zahlencode eingegeben. <br /><a href="index.php">zum Login ohne Portal</a><br /><a href="http://portal.o-wars.de">zum Portal</a>');}
if ($_SESSION['failed'] > 5) { 
	$_SESSION['failed']++;
	die ('Login fehlgeschlagen, mehr wie 5 falsche Loginversuche, du bist f&uuml;r 2h gesperrt. <br /><a href="index.php">zur&uuml;ck zum Login</a> / <a href="pwreminder.php">Passwort vergessen</a>'); 
}

if (!$row[name]) { 
	$_SESSION['failed']++;
	die ('Login fehlgeschlagen, falscher Benutzername oder falsches Kennwort. ('.(6-$_SESSION['failed']).' Versuche bis zur Sperrung) <br /><a href="index.php">zur&uuml;ck zum Login</a> / <a href="pwreminder.php">Passwort vergessen</a>'); 
} else {

// log login
mysql_query("INSERT INTO `logins` ( `id` , `userid` , `ip`, `browser`, `time` ) VALUES ( '', '".$_SESSION['user']['omni']."', '".$_SESSION['user']['ip']."', '".$_SERVER['HTTP_USER_AGENT']."', '".date('U')."' );");
	
$_SESSION['failed'] = 0;

echo '<html>
<head>
<title>O-Wars --- der Online-Krieg </title>
<link rel="icon" href="favicon.ico" type="image/ico" />
</head>

<frameset framespacing="0" border="0" cols="160,*" frameborder="0">
  <frame name="menu" target="main" src="menu.php?'.SID.'" noresize marginwidth="0" marginheight="0">
  <frame name="main" src="uebersicht.php?'.SID.'" target="_blank">
  <noframes>
  <body>

  <p>Diese Seite verwendet Frames. Frames werden von Ihrem Browser aber nicht
  unterst&uuml;tzt.</p>

  </noframes>
</frameset>

  </body>
</html>';
}
?>