<?PHP
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

$_POST['user'] = htmlspecialchars($_POST['user']);
$_POST['pass'] = htmlspecialchars($_POST['pass']);

$dbh = db_connect();
$select = "SELECT * FROM `user` WHERE 1 AND `name` = '".$_POST['user']."' AND `password` = '".md5($_POST['pass'])."' LIMIT 1;";
$result = mysql_query($select);
$row = mysql_fetch_array($result);

if ($row['gesperrt'] >= date('U')) { 
	echo '<html>
	<head>
	<title>O-Wars --- der Online-Krieg </title>
	<link rel="icon" href="favicon.ico" type="image/ico" />
	<link rel="STYLESHEET" type="text/css" href="./templates/standard/stylesheet.css" />
	<meta http-equiv="Refresh" CONTENT="60;URL=index.php?'.SID.'" />
	</head>
	<body>
	<img src="templates/standard/logo.gif">
	<table width="100%" height="80%"><tr><td width="100%" height="100%" align="center" valign="middle">
	<center>
	<table border="1" cellspacing="0" cellpadding="0" style="background-color:#ff0000;"><tr><td align="center" valign="middle" width="450px">
	<br />
	<font size="+2"><u>ACCOUNT GESPERRT</u></font><font size="+1"><br /><br />Dein Account ist bis zum '.date('d.m.Y \u\m H:i',$row['gesperrt']).' gesperrt.<br />Solltest du der Meinung sein, das dies nicht richtig ist kontaktiere einen Admin.<br /><br />E-Mail: <i>sperrung@o-wars.de</i></font>
	<br /><br />
	<a href="index.php">zur&uuml;ck zum Login</a>
	<br /><br /></td></tr></table>
	</center>
	</td></tr></table>
	</body>
	</html>';
	die();
}

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
	<img src="templates/standard/logo.gif">
	<table width="100%" height="80%"><tr><td width="100%" height="100%" align="center" valign="middle">
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

if (!$row['name']) { 
	$_SESSION['failed']++;
	echo '<html>
	<head>
	<title>O-Wars --- der Online-Krieg </title>
	<link rel="icon" href="favicon.ico" type="image/ico" />
	<link rel="STYLESHEET" type="text/css" href="./templates/standard/stylesheet.css" />
	<meta http-equiv="Refresh" CONTENT="60;URL=index.php?'.SID.'" />
	</head>
	<body>
	<img src="templates/standard/logo.gif">
	<table width="100%" height="80%"><tr><td width="100%" height="100%" align="center" valign="middle">
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
} 

if ($_SESSION['code'] != $_POST['code'] and $row['group'] <= 1){
	echo '<html>
	<head>
	<title>O-Wars --- der Online-Krieg </title>
	<link rel="icon" href="favicon.ico" type="image/ico" />
	<link rel="STYLESHEET" type="text/css" href="./templates/standard/stylesheet.css" />
	<meta http-equiv="Refresh" CONTENT="120;URL=index.php?'.SID.'" />
	</head>
	<body>
	<img src="templates/standard/logo.gif">
	<table width="100%" height="80%"><tr><td width="100%" height="100%" align="center" valign="middle">
	<center>
	<table border="1" cellspacing="0" cellpadding="0" style="background-color:#e2e2e2;"><tr><td align="center" valign="middle" width="450px">
	<p style="font-size:14px;">
	<br />
	<b>Falscher Zahlencode !!!</b><br /><br />
	Login fehlgeschlagen,<br /> du hast einen falschen Zahlencode eingegeben.<br /><br />
	Versuche es einfach nocheinmal:<br />
	<form name="owars" enctype="multipart/form-data" action="login.php?'. SID .'" method="post">	
	<img src="code/" alt="code" /><br />
	<input type="hidden" name="user" value="'.$_POST['user'].'" />
	<input type="hidden" name="pass" value="'.$_POST['pass'].'" />
	<input type="text"   name="code" value="" style="width:95px; height:20px" /><br />
		<script language="javascript" type="text/javascript">
		  	document.owars.code.focus();
  			document.owars.code.select();
		</script>
	<input type="submit" name="submit" value="Login" />
	</form>
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
	$_SESSION['code'] = rand(1000000,9999999);
	$_SESSION['user']['timeout']   = date(U)+2*3600;
	$_SESSION['user']['name']      = $row['name'];
	$_SESSION['user']['group']     = $row['group'];
	$_SESSION['user']['sig']       = $row['sig'];
	$_SESSION['user']['irc']       = $row['irc'];
	$_SESSION['user']['omni']      = $row['omni'];
	$_SESSION['user']['base']      = $row['base'];
	$_SESSION['user']['clan']      = $row['clan'];
	$_SESSION['user']['mail']      = $row['email'];
	$_SESSION['user']['email2']    = $row['email2'];
	$_SESSION['user']['pic']       = $row['pic'];
	$_SESSION['user']['supporter'] = $row['supporter'];
	$_SESSION['user']['points']    = number_format($row['points'],0,'','.');
	$_SESSION['user']['ip']        = $_SERVER['REMOTE_ADDR'];
	$_SESSION['user']['browser']   = $_SERVER['HTTP_USER_AGENT'];
	$_SESSION['badwords']		   = $row['badwords'];

	if ($row['graphic']) { 
		$_SESSION['user']['graphic'] = $row['graphic']; 
	} elseif ($row['style'] and is_dir('style/'.$row['style'])) { 
		$_SESSION['user']['style']     = $row['style']; 
	} else {
		$_SESSION['user']['style']     = 'standard'; 
	}
	
	// log login
	mysql_query("INSERT INTO `logins` ( `id` , `userid` , `ip`, `browser`, `time` ) VALUES ( '', '".$_SESSION['user']['omni']."', '".$_SESSION['user']['ip']."', '".$_SERVER['HTTP_USER_AGENT']."', '".date('U')."' );");
	mysql_query("UPDATE `user` SET `timestamp` = '".date('U')."', `browser` = '".$_SESSION['user']['browser']."', `ip` = '".$_SESSION['user']['ip']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");	
	
	// supporter
	$_SESSION['user']['supporter']    = $row['supporter'];
	
	echo '<html>
	<head>
	<title>O-Wars --- der Online-Krieg </title>
	<link rel="icon" href="favicon.ico" type="image/ico" />
	<link rel="STYLESHEET" type="text/css" href="./templates/standard/stylesheet.css" />
	<meta http-equiv="Refresh" CONTENT="2;URL=frame.php?'.SID.'" />
	</head>
	<body>
	<img src="templates/standard/logo.gif">
	<table width="100%" height="70%"><tr><td width="100%" height="100%" align="center" valign="middle">
	<center>
	<table border="1" cellspacing="0" cellpadding="0" style="background-color:#e2e2e2;">
	<tr><td align="center" valign="middle" width="550px">
	<p style="font-size:14px;">
	<br />
	Hallo <b>'.$_SESSION['user']['name'].',</b><br />
	willkommen in deiner Basis <b>'.$_SESSION['user']['base'].'</b>,<br />
	du hast dich erfolgreich eingelogged.<br />
	Du wirst sofort weitergeleitet.<br /><br />
	<a href="http://www.all-net-solutions.de" target="_new"><img src="img/ansad.gif" alt="ad" /></a>
	</p>
	<a href="frame.php?'.SID.'">Wenn du nicht automatisch weitergeleitet werden solltest klicke bitte hier.</a>
	<br /><br /></td></tr></table>
	</center>
	</td></tr></table>
	</body>
	</html>';

}
?>