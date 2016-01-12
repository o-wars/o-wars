<?PHP
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

echo '<html>
<head>
<title>O-Wars --- der Online-Krieg </title>
<link rel="icon" href="favicon.ico" type="image/ico" />
<link rel="STYLESHEET" type="text/css" href="./templates/standard/stylesheet.css" />
<meta http-equiv="Refresh" CONTENT="30;URL=index.php?'.SID.'" />
<script src="http://layer-ads.de/la-15590-subid:owlogout.js" type="text/javascript"></script>
</head>
<body>
<img src="templates/standard/logo.gif">
<table width="100%" height="80%"><tr><td width="100%" height="100%" align="center" valign="middle">
<center>
<table border="1" cellspacing="0" cellpadding="0" style="background-color:#e2e2e2;"><tr><td align="center" valign="middle" width="550px">
<p style="font-size:14px;">
<br />
<b>'.$_SESSION['user']['name'].'</b>,<br />
du hast dich erfolgreich <b>ausgelogged</b>, <br />du wirst nun zum Login ohne Portal weitergeleitet.<br />
'.template('ad').'
</p>
<a href="index.php?'.SID.'">Wenn du zum Login ohne Portal m&ouml;chtest klicke bitte hier.</a><br />
<a href="http://portal.o-wars.de">Wenn du zum O-Wars.de Portal m&ouml;chtest klicke bitte hier.</a>
<br /><br /></td></tr></table>
</center>
</td></tr></table>
</body>
</html>';

session_destroy();
?>
