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
include "admin.php";

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
<title>o-wars --- Der Online Krieg</title>
<script language="JavaScript" type="text/javascript">
<!-- Begin
function popUp(url, height, id) { 
var new_win = window.open(url,id+"popUp"+id,"resizable=yes,scrollbars=yes,menubar=no,toolbar=no,width=600,height="+height+",top=0,left=0"); 
new_win.focus(); }
// End -->
</script>
<link rel="STYLESHEET" type="text/css" href="templates/stylesheet.css" />
</head>
<body>
<center>
<br />
<br />
<table cellspacing="0" style="font-size: 12px" width="100%" height="80%" border="0">
<tr style="background-color:#efd48d;">
<td style="width:658px;height:387px;background-image:url(img/panzer'.$i.'.jpg);" align="center" valign="middle">
<br />
<table style="background-image: url(img/login_bg.gif); color: #ffffff; font-size: 12px; width:250px;" border="1">
	<tr>
		<td align="center">
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
		<input type="hidden" name="login" value="1" /><br />
		<input type="submit" name="submit" value="Login" /><br />
		</form>
		</td>
	</tr>
</table>
</td>
</tr>
</table>
</body></html>';

echo $content;
?>