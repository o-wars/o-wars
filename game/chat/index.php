<?PHP
// starten der session
session_name('SESSION');
session_start();
?>
<html>
<head>
<title>O-Wars</title>
</head>
<body>
<center><b>irc.euirc.net - #O-Wars</b>
<applet code=IRCApplet.class archive="irc.jar,pixx.jar" width=780 height=500>
<param name="CABINETS" value="irc.cab,securedirc.cab,pixx.cab">

<param name="nick" value="<?php if ($_SESSION['user']['name']) { echo str_replace(' ', '_', $_SESSION['user']['name']); } else { echo "O-Wars-User???"; }?>">
<param name="alternatenick" value="<?php if ($_SESSION['user']['name']) { echo str_replace(' ', '_', $_SESSION['user']['name']).'????'; } else { echo "O-Wars-User???"; }?>">
<param name="fullname" value="O-Wars Spieler">
<param name="host" value="irc.euirc.net">
<param name="gui" value="pixx">
<param name="quitmessage" value="O-Wars forever!">
<param name="authorizedleavelist" value="all-#o-wars">
<param name="fingerreply" value="O-Wars rulez!">
<?php 
	$command = explode("\n",$_SESSION['user']['irc']);
	for ($i=0; $command[$i]; $i++) {
		echo '<param name="command'.($i+1).'" value="'.chop($command[$i]).'">'."\n";
	}
	echo '<param name="command'.($i+1).'" value="/join #o-wars">'."\n";
?>

</applet>

</center>
</body>
</html>

