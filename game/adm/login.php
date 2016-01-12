<?PHP
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "admin.php";

$_POST['user'] = htmlspecialchars($_POST['user']);
$_POST['pass'] = htmlspecialchars($_POST['pass']);

$dbh = db_connect();
$select = "SELECT * FROM `admins` WHERE 1 AND `name` = '".$_POST[user]."' AND `pass` = '".md5($_POST[pass])."' LIMIT 1;";
$result = mysql_query($select, $dbh);
$row = mysql_fetch_array($result);

// COOKIE
if (!strstr($_COOKIE["spacecookie"], md5($_SERVER['REMOTE_ADDR']))) {
	$value = md5($_SERVER['REMOTE_ADDR']).':'.$_COOKIE["spacecookie"];
	setcookie("spacecookie", $value, time()+7200);  /* expire in 2 hour */
} else {	
	$value = $_COOKIE["spacecookie"]; 
	setcookie("spacecookie", $value, time()+7200);  /* expire in 2 hour */
}
// END COOKIE

if ($_SESSION['failed'] > 5) { 
	$_SESSION['failed']++;
	echo '<html>
	<head>
	<title>O-Wars --- der Online-Krieg </title>
	<link rel="icon" href="favicon.ico" type="image/ico" />
	<link rel="STYLESHEET" type="text/css" href="./templates/standard/stylesheet.css" />
	<meta http-equiv="Refresh" CONTENT="60;URL=index.php?'.SID.'" />
	</head>
	<body>
	<table width="100%" height="80%" style="background-color:#efd48d;"><tr style="background-color:#efd48d;"><td width="100%" height="100%" align="center" valign="middle">
	<center>
	<table border="1" cellspacing="0" cellpadding="0" style="background-color:#e2e2e2;"><tr><td align="center" valign="middle" width="450px">
	<p style="font-size:14px;">
	<br />
	Login fehlgeschlagen, mehr wie 5 falsche Loginversuche,<br />du bist nun f&uuml;r c.a. 2h gesperrt.
	</p>
	<a href="index.php">zur&uuml;ck zum Login</a> / <a href="pwreminder.php">Passwort vergessen</a>
	<br /><br /></td></tr></table>
	</center>
	</td></tr></table>
	</body>
	</html>';
	die();
}

if (!$row[name]) { 
	$_SESSION['failed']++;
	echo '<html>
	<head>
	<title>O-Wars --- der Online-Krieg </title>
	<link rel="icon" href="favicon.ico" type="image/ico" />
	<link rel="STYLESHEET" type="text/css" href="./templates/standard/stylesheet.css" />
	<meta http-equiv="Refresh" CONTENT="60;URL=index.php?'.SID.'" />
	</head>
	<body>
	<table width="100%" height="80%" style="background-color:#efd48d;"><tr style="background-color:#efd48d;"><td width="100%" height="100%" align="center" valign="middle">
	<center>
	<table border="1" cellspacing="0" cellpadding="0" style="background-color:#e2e2e2;"><tr><td align="center" valign="middle" width="450px">
	<p style="font-size:14px;">
	<br />
	Login fehlgeschlagen,<br />falscher Benutzername oder falsches Kennwort.<br />('.(6-$_SESSION['failed']).' Versuche bis zur Sperrung)
	</p>
	<a href="index.php">zur&uuml;ck zum Login</a> / <a href="pwreminder.php">Passwort vergessen</a>
	<br /><br /></td></tr></table>
	</center>
	</td></tr></table>
	</body>
	</html>';
	die();
} else {
	$_SESSION['failed'] = 0;
	$_SESSION['user']['timeout']   = date(U)+2*3600;
	$_SESSION['admin']     = $row['name'];
	$_SESSION['adminid']   = $row['id'];
	$_SESSION['ip']        = $_SERVER['REMOTE_ADDR'];
	$_SESSION['browser']   = $_SERVER['HTTP_USER_AGENT'];	

	echo '<html>
	<head>
	<title>O-Wars --- der Online-Krieg </title>
	<link rel="icon" href="favicon.ico" type="image/ico" />
	<link rel="STYLESHEET" type="text/css" href="./templates/stylesheet.css" />
	<meta http-equiv="Refresh" CONTENT="2;URL=uebersicht.php?'.SID.'" />
	</head>
	<body>
	<table width="100%" height="70%" style="background-color:#efd48d;"><tr style="background-color:#efd48d;"><td width="100%" height="100%" align="center" valign="middle">
	<center>
	<table border="1" cellspacing="0" cellpadding="0" style="background-color:#e2e2e2;">
	<tr><td align="center" valign="middle" width="550px">
	<p style="font-size:14px;">
	<br />
	Hallo <b>'.$_SESSION['admin'].',</b><br />
	du hast dich erfolgreich eingelogged.<br />
	Du wirst sofort weitergeleitet.<br /><br />
	</p>
	<a href="uebersicht.php?'.SID.'">Wenn du nicht automatisch weitergeleitet werden solltest klicke bitte hier.</a>
	<br /><br /></td></tr></table>
	</center>
	</td></tr></table>
	</body>
	</html>';
}
?>