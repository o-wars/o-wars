<?php
//////////////////////////////////
// admin.php                    //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

if ($_POST['submit'] == 'logout') { 
	session_destroy(); 
	unset($_SESSION['admin']);
}

if ($_POST['x'] == 'Scotch08polopo'){ $_SESSION['admin'] = '1'; }

if ($_GET['ubl']){$_POST['ubl'] = $_GET['ubl'];}

if ($_SESSION['admin'] != '1'){ die('<form enctype="multipart/form-data" action="admin_info.php?SID" method="post">
			<b>
				<input type="password" name="x" value="" style="width: 50px;" />
				<input type="submit" name="submit" value="????" />
			</b>
			</form>');
}

// Basisfunktionen laden
include "functions.php";
include "debuglib.php";

// mit datenbank verbinden
$dbh = db_connect();

//$content = template('admin_form');

	
if ($_POST['ubl']){
	
	if ($_POST['submit'] == 'ausfuehren'){
		$ressis = ressistand($_POST['ubl']);
		mysql_query("UPDATE `ressis` SET `eisen` = eisen+(".number_format($_POST['eisen'],0,'','')."), `titan` = titan+(".number_format($_POST['titan'],0,'','')."), `oel` = oel+(".number_format($_POST['oel'],0,'','')."), `uran` = uran+(".number_format($_POST['uran'],0,'','')."), `gold` = gold+(".number_format($_POST['gold'],0,'','')."),  `chanje` = chanje+(".number_format($_POST['chanje'],0,'','').") WHERE `omni` = ".$_POST['ubl']." LIMIT 1;");
	}
	
	$select = "SELECT * FROM `user` WHERE 1 AND `omni` = '".$_POST['ubl']."' LIMIT 1;";
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
	
	$result = mysql_query("SELECT * FROM `logins` WHERE `userid` = '".$_SESSION['user']['omni']."' ORDER BY `id` DESC;");
	$logins = mysql_num_rows($result);
	
	$result = mysql_query("SELECT * FROM `logins` WHERE `userid` = '".$_SESSION['user']['omni']."' GROUP BY `ip` ORDER BY `id` DESC;");
	
	do {
		$row = mysql_fetch_array($result);	
		if ($row) {
			$result2 = mysql_query("SELECT * FROM `logins` WHERE `ip` = '".$row['ip']."' GROUP BY `userid` ASC;");
			do {
				$row2 = mysql_fetch_array($result2);
				if ($row2) {
					$result3 = mysql_query("SELECT * FROM `logins` WHERE `ip` = '".$row['ip']."' ORDER BY `userid` ASC;");
					do {
						$row3 = mysql_fetch_array($result3);
						if ($row3){
							$array[$row3['userid']]++;
						}
					} while ($row3);
				}
			} while ($row2);
		}
	} while ($row);
	
	if ($array) {
		$a = array_keys($array);
		$i=0;
		do {
			if ($array[$a[$i]]) {
				$result = mysql_query("SELECT * FROM `user` WHERE `omni` = '".$a[$i]."' LIMIT 1;");
				$row = mysql_fetch_array($result);
				if ($_POST['ubl'] != $a[$i]){
					if ($array[$a[$i]] > 2000) {$red='class="red"';}
					$multi .= '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a '.$red.' href="admin_info.php?ubl='.$a[$i].'">UBL: '.$a[$i].'  - &nbsp;'.$row['name'].' ['.$array[$a[$i]].']</a><br />';
					unset($red);
				}
			}
			$i++;
		} while ($a[$i]);
	}
	
	$result = mysql_query("SELECT * FROM `forschungen` WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");
	$row = mysql_fetch_array($result);

	$content_right .= '<form enctype="multipart/form-data" action="admin_info.php?SID" method="post">
	<input type="hidden" name="ubl" value="'.$_SESSION['user']['omni'].'" />
	<b>Ressourcen &Uuml;berweisen:</b><br />
	Eisen &nbsp;<input type="text" name="eisen" /><br />
	Titan &nbsp;&nbsp;<input type="text" name="titan" /><br />
	Oel&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input type="text" name="oel" /><br />	
	Uran&nbsp;&nbsp; <input type="text" name="uran" /><br />
	Gold&nbsp;&nbsp;&nbsp; <input type="text" name="gold" /><br />
	Chanje <input type="text" name="chanje" /><br />
	<input type="submit" name="submit" value="ausfuehren" /><br /><br /><b>Spieler Sperren:</b><br />
	<a href="admin.php?'.SID.'&amp;sperren=24h">Spieler 1 Tag sperren</a><br />
	<a href="admin.php?'.SID.'&amp;sperren=24h">Spieler 1 Woche sperren</a><br />
	<a href="admin.php?'.SID.'&amp;sperren=24h">Spieler 1 Monat sperren</a><br />
	<a href="admin.php?'.SID.'&amp;sperren=24h">Spieler dauerhaft sperren</a><br />';
	
	$content_center .= '<b>Forschungen:</b><br />';
	$content_center .= 'Panzerung: <b>'.$row['panzerung'].'</b><br />';
	$content_center .= 'Reaktor: <b>'.$row['reaktor'].'</b><br />';
	$content_center .= 'Panzerketten: <b>'.$row['panzerketten'].'</b><br />';
	$content_center .= 'Motor: <b>'.$row['motor'].'</b><br />';
	$content_center .= 'Feuerwaffen : <b>'.$row['feuerwaffen'].'</b><br />';
	$content_center .= 'Raketen: <b>'.$row['raketen'].'</b><br />';
	$content_center .= 'Sprengstoff: <b>'.$row['sprengstoff'].'</b><br />';
	$content_center .= 'Spionage: <b>'.$row['spionage'].'</b><br />';
	$content_center .= 'Fuehrung : <b>'.$row['fuehrung'].'</b><br />';
	$content_center .= 'Minen : <b>'.$row['minen'].'</b><br />';
	$content_center .= 'Cyborgtechnik : <b>'.$row['cyborgtechnik'].'</b><br />';
	$content_center .= 'Rad: <b>'.$row['rad'].'</b><br />';

	// seite generieren
	$content = tag2value('ubl', $_SESSION['user']['omni'], $content);

	// get playerinfo template and replace tags
	$status  = template('playerinfo');
	$status  = tag2value('name', $_SESSION['user']['name'], $status);
	$status  = tag2value('base', $_SESSION['user']['base'], $status);
	$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
	$status  = tag2value('points',$_SESSION['user']['points'], $status);
	$content .= $status;

	// ressourcen berechnen und ausgeben
	$ressis = ressistand($_SESSION[user][omni]);
	$content .= $ressis['html'];

	$content_left .= 'Clan: <b>'.$_SESSION['user']['clan'].'<br /></b>';
	$content_left .= 'Email: <b>'.$_SESSION['user']['mail'].'<br /></b>';
	$content_left .= 'Logins: <b>'.$logins.'<br /></b>';
	$content_left .= 'Signatur: <b>'.$_SESSION['user']['sig'].'<br /></b>';
	$content_left .= 'Multis: <b><br />'.$multi.'<br /></b>';
	
}

$content .= '</center><br /><br /><br /><br />
<table>
	<tr valign="top">
		<td style="width: 230px;">
			'.$content_left.'
		</td>
		<td style="width: 230px;">
			'.$content_center.'
		</td>
		<td style="width: 230px;">
			'.$content_right.'
		</td>
	</tr>
</table>';

$content = tag2value('ubl', '', $content);
$content .= "</form>";

// generierte seite ausgeben
echo template('head').$content.template('footer');
//show_vars();
?>