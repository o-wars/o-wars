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

if ($_SESSION['admin'] != '1'){ die('<form enctype="multipart/form-data" action="admin_panel.php?SID" method="post">
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

$content = template('admin_form');
if ($_GET['ubl']){$_POST['ubl'] = $_GET['ubl'];}
if (!$_POST['ubl']){$_POST['ubl'] = 1;}
	
if ($_POST['ubl'] != ''){
	
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
					$multi .= '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a '.$red.' href="admin.php?ubl='.$a[$i].'">UBL: '.$a[$i].'  - &nbsp;'.$row['name'].' ['.$array[$a[$i]].']</a><br />';
					unset($red);
				}
			}
			$i++;
		} while ($a[$i]);
	}
}

$content = tag2value('ubl', $_POST['ubl'], $content);
$content .= "</form>";

// generierte seite ausgeben
echo template('head').$content.template('footer');
//show_vars();
?>