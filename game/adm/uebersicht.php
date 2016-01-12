<?php
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "admin.php";

// check session
logincheck();

// mit datenbank verbinden
$dbh = db_connect();

if ($_POST['ubl']) {
	$_SESSION['ubl'] = $_POST['ubl'];
} elseif ($_GET['ubl']) {
	$_SESSION['ubl'] = $_GET['ubl'];
} elseif (!$_SESSION['ubl']) {
	$_SESSION['ubl'] = 1;
}

if ($_POST['sperren']) {
	mysql_query("UPDATE `user` SET `gesperrt` = '".(date('U')+$_POST['sperren']*3600)."' WHERE `omni` = '".$_SESSION['ubl']."' LIMIT 1;");
}

$form = '<form enctype="multipart/form-data" action="uebersicht.php?'.SID.'" method="post">
			Basis: 
			<input type="text" name="ubl" value="'.$_SESSION['ubl'].'" style="width:50px" />
			<input type="submit" value="wechseln" />
		</form>';

$user = mysql_fetch_array(mysql_query("SELECT * FROM `user` WHERE `omni` = '".$_SESSION['ubl']."';"));

$_SESSION['user']['timeout']   = date(U)+2*3600;
$_SESSION['user']['name']      = $user['name'];
$_SESSION['user']['group']     = $user['group'];
$_SESSION['user']['sig']       = $user['sig'];
$_SESSION['user']['omni']      = $user['omni'];
$_SESSION['user']['base']      = $user['base'];
$_SESSION['user']['clan']      = $user['clan'];
$_SESSION['user']['mail']      = $user['email'];
$_SESSION['user']['supporter'] = $user['supporter'];
$_SESSION['user']['points']    = number_format($user['points'],0,'','.');
$_SESSION['user']['ip']        = $_SERVER['REMOTE_ADDR'];
$_SESSION['user']['buserser']  = $_SERVER['HTTP_USER_AGENT'];	
$_SESSION['user']['supporter'] = 99999999999999;

$c1 .= '<table border="1" cellspacing="0">';
$c1 .= '<tr><td width="100px">UBL:</td><td width="150px"><b>'.$user['omni'].'</b></td></tr>';
$c1 .= '<tr><td>Name:</td><td> <b>'.$user['name'].'</b></td></tr>';
$c1 .= '<tr><td>Base:</td><td> <b>'.$user['base'].'</b></td></tr>';
$c1 .= '<tr><td>Letzter Login:</td><td> <b>'.date('d.m.Y H:i',$user['timestamp']).'</b></td></tr>';
if ($user['gesperrt'] > date('U')) {
	$c1 .= '<tr><td class="red">gesperrt bis:</td><td class="red"> <b>'.date('d.m.Y H:i',$user['gesperrt']).'</b></td></tr>';
}
$c1 .= '</table>';

$_SESSION['info'] = $c1;

if ($_POST['ressis'] == 'buchen'){
	mysql_query("UPDATE `ressis` SET `eisen` = eisen+".number_format($_POST['eisen'],0,'.','').",
`titan` = titan+".number_format($_POST['titan'],0,'.','').",
`oel` = oel+".number_format($_POST['oel'],0,'.','').",
`uran` = uran+".number_format($_POST['uran'],0,'.','').",
`gold` = gold+".number_format($_POST['gold'],0,'.','').",
`chanje` = chanje+".number_format($_POST['chanje'],0,'.','')." WHERE `omni` = '".$_SESSION['ubl']."' LIMIT 1;");
}

if ($_POST['sperren']) {
	mysql_query("UPDATE `user` SET `gesperrt` = '".(date('U')+$_POST['sperren']*3600)."' WHERE `omni` = '".$_SESSION['ubl']."' LIMIT 1;");
}

$result = mysql_query("SELECT userid,ip FROM `logins` WHERE `userid` = '".$_SESSION['ubl']."' GROUP BY `userid`,`ip` DESC;");
/*	
do {
	$row = mysql_fetch_array($result);	
	if ($row) {
		$result2 = mysql_query("SELECT * FROM `logins` WHERE `ip` = '".$row['ip']."' GROUP BY `userid`,`ip` ASC;");
		do {
			$row2 = mysql_fetch_array($result2);
			if ($row2) {
				$result3 = mysql_query("SELECT * FROM `logins` WHERE `ip` = '".$row['ip']."' GROUP BY `userid`,`ip` ASC;");
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
*/	
if ($array) {
	$a = array_keys($array);
	$i=0;
	do {
		if ($array[$a[$i]]) {
			$result = mysql_query("SELECT * FROM `user` WHERE `omni` = '".$a[$i]."' LIMIT 1;");
			$row = mysql_fetch_array($result);
			if ($_SESSION['ubl'] != $a[$i]){
				if ($array[$a[$i]] > 2000) {$red='class="red"';}
				$multi .= '<a '.$red.' href="uebersicht.php?ubl='.$a[$i].'&amp;'.SID.'">UBL: '.$a[$i].'  - &nbsp;'.$row['name'].' ['.$array[$a[$i]].']</a><br />';
				unset($red);
			}
		}
		$i++;
	} while ($a[$i]);
}

$ressis = template('ressis');
$ress = ressistand($_SESSION['ubl']);

$ressis = tag2value('eisen', number_format($ress['eisen'],0,',','.'), $ressis);
$ressis = tag2value('titan', number_format($ress['titan'],0,',','.'), $ressis);
$ressis = tag2value('oel', number_format($ress['oel'],0,',','.'), $ressis);
$ressis = tag2value('gold', number_format($ress['gold'],0,',','.'), $ressis);
$ressis = tag2value('uran', number_format($ress['uran'],0,',','.'), $ressis);
$ressis = tag2value('chanje', number_format($ress['chanje'],0,',','.'), $ressis);

$sperren = template('sperren');

$content .= template('index');
$content  = tag2value('ubl', $_SESSION['ubl'],$content);
$content  = tag2value('content', $form.$c1.'<table cellspacing="0" border="1"><tr valign="top"><td width="254px"><b>Potentielle Multis:</b><br />'.$multi.'</td><td width="254px"><b>Ressis buchen:</b><br />'.$ressis.'</td></tr>
<tr><td><b>Spieler sperren:</b>'.$sperren.'</td>
<td></td>
</tr>
</table>',$content);

echo $content;
?>