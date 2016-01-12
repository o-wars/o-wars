<?PHP
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

logincheck();

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
	<p>Diese Seite verwendet Frames. Frames werden von Ihrem Browser aber nicht unterst&uuml;tzt.</p>
</noframes>
</frameset>

</body>
</html>';
?>