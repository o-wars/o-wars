<?PHP
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "admin.php";

echo '<html>
<head>
<title>O-Wars --- der Online-Krieg </title>
<link rel="icon" href="favicon.ico" type="image/ico" />
<link rel="STYLESHEET" type="text/css" href="./templates/stylesheet.css" />
<meta http-equiv="Refresh" CONTENT="2;URL=index.php?'.SID.'" />
</head>
<body>
<table width="100%" height="80%" style="background-color:#efd48d;"><tr style="background-color:#efd48d;"><td width="100%" height="100%" align="center" valign="middle">
<center>
<table border="1" cellspacing="0" cellpadding="0" style="background-color:#e2e2e2;"><tr><td align="center" valign="middle" width="550px">
<p style="font-size:14px;">
<br />
<b>'.$_SESSION['admin'].'</b>,<br />
du hast dich erfolgreich <b>ausgelogged</b>, <br />du wirst nun zum Login weitergeleitet.<br /><br />
</p>
<a href="index.php?'.SID.'">Wenn du nicht weitergeleitet werden solltest klicke bitte hier.</a><br />
<br /><br /></td></tr></table>
</center>
</td></tr></table>
</body>
</html>';

session_destroy();
?>