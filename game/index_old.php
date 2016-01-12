<?php
//////////////////////////////////
// Login                        //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////
// Kommentare:
// - Spielerlogin
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";
include "../version.php";

if ($_GET['logout'] == 1){ session_destroy(); }

$dbh    = db_connect();
$select = "SELECT * FROM `user` WHERE `timestamp` > '".(date('U')-1800)."';";
$result = mysql_query($select);
$users  = mysql_num_rows($result);


$i = rand(1,3);

$content .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<meta name="expires" content="0" />
<meta name="author" content="TheBunman" />
<meta name="date" content="2004-09-11" />
<meta name="robots" content="index" />
<meta name="expires" content="0" />
<meta name="description" content="owars" />
<meta name="keywords" content="blah" />
<link rel="icon" href="favicon.ico" type="image/ico" />
<title>game.O-Wars.de - Der Online Krieg</title>
<script language="JavaScript" type="text/javascript">
<!-- Begin
function popUp(url, height, id) { 
var new_win = window.open(url,id+"popUp"+id,"resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width=600,height="+height+",top=0,left=0"); 
new_win.focus(); }
// End -->
</script>
<link rel="STYLESHEET" type="text/css" href="templates/standard/stylesheet.css" />
</head>
<body>
<center>
<br />
<br />
<table border="1" cellspacing="0" style="font-size: 12px">
<tr style="background-image:url(templates/standard/table_head.gif);" align="center">
<td><b>- = // O-Wars --- Der Online Krieg --- '.$version.' \\\\ = -</b></td>
</tr>
<tr>
<td style="width:658px;height:387px;background-image:url(img/panzer'.$i.'.jpg);" align="center" valign="middle">
<br />
<table style="background-image: url(img/login_bg.gif); color: #000000; font-size: 12px; width:250px;" border="1">
	<tr>
		<td align="center">
		<b style="color: red;">O-Wars.de ist auf einen neuen Server umgezogen. Sollten Probleme auftauchen, einfach eine IGM an die UBL 1.</b><br />
		<br />
		<b>- \ ::: Login ::: / -</b><br />
		<form name="owars" enctype="multipart/form-data" action="login.php?'. SID .'" method="post">
		username<br />
		<input type="text" name="user" value="" onFocus="setClear();" style="width: 155px; height: 17px;" /><br />
		<script language="javascript" type="text/javascript">
		  	document.owars.user.focus();
  			document.owars.user.select();
		</script>
		passwort<br />
		<input type="password" name="pass" value="" style="width: 155px; height: 17px;" /><br />
		Zahlencode:<br />
		<img src="code/?'.SID.'" alt="code" /><br />
		<input type="text" name="code" value="" style="width: 92px; height: 20px;" /><br />
		<input type="hidden" name="login" value="1" /><br />
		<input type="submit" name="submit" value="Login" /><br />
		</form>
		<font class="red">
		<a class="red" href="javascript:popUp(\'register.php\',600)"><b>registrieren</b></a> / 
		<a class="red" href="http://portal.o-wars.de/"><b>Zur&uuml;ck zum Portal</b><br />
		<a class="red" href="pwreminder.php"><b>Passwort vergessen</b></a>
		</font>
		</td>
	</tr>
</table>
<br />
'.template('ad').'
</td>
</tr>
</table>
'.$users.' Spieler sind im Moment online.<br />
<a href="http://www.browserwelten.net/?ac=vote&gameid=452" target="_blank"><img src="http://www.browserwelten.net/img/bw_votebutton.gif" border="0" alt="www.browserwelten.net" /></a>
<a href="http://www.galaxy-news.de/?page=charts&op=vote&game_id=888" target="_blank"><img src="gn_vote.gif" border=0 /></a>
<a href="http://www.rawnews.de/index.php?pg=charts&at=vote&game_id=181" target="_blank"><img src="vote_raw.gif" border="0"></a>
'.template('footer');

echo $content;
?>