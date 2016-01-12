<?PHP 
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

// check session
logincheck();

// mit datenbank verbinden
$dbh = db_connect();
$ressis = ressistand($_SESSION['user']['omni']);

// 0 - offender ist drann
// 1 - defender muss annehmen oder ablehnen
// 2 - defender spielt gerade
// 3 - offender hat gewonnen
// 4 - defender hat gewonnen
// 5 - unentschieden
// 99- abgelehnt

if ($_GET['pay']) {

	$row = mysql_fetch_array(
		mysql_query("SELECT * FROM `cards` WHERE `gid` = '".intval($_GET['gid'])."' LIMIT 1;"));	
	
	switch ($_GET['status']) {
		
		case won:
			if ($row['status'] == 3 and $row['offender'] == $_SESSION['user']['omni'] and $row['payout_offender'] == 0) {
				mysql_query("UPDATE `ressis` SET gold=gold+".intval($row['bet']*2)." WHERE `omni` = ".$_SESSION['user']['omni']." LIMIT 1;");
				mysql_query("UPDATE `cards` SET `payout_offender` = '1' WHERE `gid` = '".intval($row['gid'])."' LIMIT 1;");
				$row['payout_offender'] = 1;
			} elseif ($row['status'] == 4 and $row['defender'] == $_SESSION['user']['omni'] and $row['payout_defender'] == 0) {
				mysql_query("UPDATE `ressis` SET gold=gold+".intval($row['bet']*2)." WHERE `omni` = ".$_SESSION['user']['omni']." LIMIT 1;");
				mysql_query("UPDATE `cards` SET `payout_defender` = '1' WHERE `gid` = '".intval($row['gid'])."' LIMIT 1;");
				$row['payout_defender'] = 1;
			}
			break;
			
		case draw:
			if ($row['status'] == 5 and $row['offender'] == $_SESSION['user']['omni'] and $row['payout_offender'] == 0) {
				mysql_query("UPDATE `ressis` SET gold=gold+".intval($row['bet'])." WHERE `omni` = ".$_SESSION['user']['omni']." LIMIT 1;");
				mysql_query("UPDATE `cards` SET `payout_offender` = '1' WHERE `gid` = '".intval($row['gid'])."' LIMIT 1;");
				$row['payout_offender'] = 1;
			} elseif ($row['status'] == 5 and $row['defender'] == $_SESSION['user']['omni'] and $row['payout_defender'] == 0) {
				mysql_query("UPDATE `ressis` SET gold=gold+".intval($row['bet'])." WHERE `omni` = ".$_SESSION['user']['omni']." LIMIT 1;");
				mysql_query("UPDATE `cards` SET `payout_defender` = '1' WHERE `gid` = '".intval($row['gid'])."' LIMIT 1;");
				$row['payout_defender'] = 1;
			}
			break;
			
		case lost:
			if ($row['status'] == 4 and $row['offender'] == $_SESSION['user']['omni'] and $row['payout_offender'] == 0) {
				mysql_query("UPDATE `cards` SET `payout_offender` = '1' WHERE `gid` = '".intval($row['gid'])."' LIMIT 1;");
				$row['payout_offender'] = 1;
			} elseif ($row['status'] == 3 and $row['defender'] == $_SESSION['user']['omni'] and $row['payout_defender'] == 0) {
				mysql_query("UPDATE `cards` SET `payout_defender` = '1' WHERE `gid` = '".intval($row['gid'])."' LIMIT 1;");
				$row['payout_defender'] = 1;
			}
			break;
			
		case cancel:
			if ($row['status'] == 1 and $row['offender'] == $_SESSION['user']['omni'] and $row['payout_offender'] == 0 and (time()-$row['timestamp']) > (3600*24)) {
				mysql_query("UPDATE `ressis` SET gold=gold+".intval($row['bet'])." WHERE `omni` = ".$_SESSION['user']['omni']." LIMIT 1;");
				mysql_query("UPDATE `cards` SET `payout_offender` = '1' WHERE `gid` = '".intval($row['gid'])."' LIMIT 1;");
				$row['payout_offender'] = 1;
				$row['payout_defender'] = 1;
			}
			break;
			
		case canceled:
			if ($row['status'] == 99 and $row['offender'] == $_SESSION['user']['omni'] and $row['payout_offender'] == 0) {
				mysql_query("UPDATE `ressis` SET gold=gold+".intval($row['bet'])." WHERE `omni` = ".$_SESSION['user']['omni']." LIMIT 1;");
				mysql_query("UPDATE `cards` SET `payout_offender` = '1' WHERE `gid` = '".intval($row['gid'])."' LIMIT 1;");
				$row['payout_offender'] = 1;
				$row['payout_defender'] = 1;
			}
			break;			
		
		default:
			break;
	
	}
	
	if ($row['payout_offender'] and $row['payout_defender']) {

		mysql_query("DELETE FROM `cards` WHERE `gid` = '".$row['gid']."' LIMIT 1;");

	}

}

$game = mysql_fetch_array(
	mysql_query("SELECT * FROM `cards` WHERE `offender` = '".$_SESSION['user']['omni']."' and `status` = '0' LIMIT 1;"));
	
if (!$game) {
	$game = mysql_fetch_array(
		mysql_query("SELECT * FROM `cards` WHERE `defender` = '".$_SESSION['user']['omni']."' and `status` = '2' LIMIT 1;"));
}

// neues game starten
if ($_POST['ubl'] and $_POST['bet'] < $ressis['gold'] and $_POST['bet'] > 0 and !$game) {

	$stack   = shufflecards32();
	$card    = rand(1,32);
	$hand[0] = $stack[$card];
	
	unset($stack[$card]);
	sort($stack);
	
	mysql_query("INSERT INTO `cards` ( `gid` , `offender` , `defender` , `timestamp` , `bet` , `status` , `stack` , `hand_offender` , `hand_defender` ) VALUES ('', '".$_SESSION['user']['omni']."', '".intval($_POST['ubl'])."', '".time()."', '".intval($_POST['bet'])."', '0', '".implode(':', $stack)."', '".implode(':', $hand)."', '');");
	mysql_query("UPDATE `ressis` SET gold=gold-".intval($_POST['bet'])." WHERE `omni` = ".$_SESSION['user']['omni']." LIMIT 1;");
	
	$game = mysql_fetch_array(mysql_query("SELECT * FROM `cards` WHERE `offender` = '".$_SESSION['user']['omni']."' and `status` = '0' LIMIT 1;"));
	
}

// herausforderung annehmen
if ($_GET['gid'] and $_GET['accept'] == 1 and !$game) {
	
	$game = mysql_fetch_array(mysql_query("SELECT * FROM `cards` WHERE `defender` = '".$_SESSION['user']['omni']."' and `status` = '1' and `gid` = '".intval($_GET['gid'])."' LIMIT 1;"));
	
	if ($game and $ressis['gold'] >= $game['bet']) {
		
		mysql_query("UPDATE `ressis` SET gold=gold-".intval($game['bet'])." WHERE `omni` = ".$_SESSION['user']['omni']." LIMIT 1;");
		mysql_query("UPDATE `cards` SET `status` = '2' WHERE `defender` = '".$_SESSION['user']['omni']."' and `gid` = '".intval($_GET['gid'])."' LIMIT 1;");
		$game['status'] = 2;
		
	} else {
		
		unset($game);
		
	}
}

// herausforderung ablehnen
if ($_GET['gid'] and $_GET['accept'] == 2 and !$game) {
	
	mysql_query("UPDATE `cards` SET `status` = '99' WHERE `gid` = '".intval($_GET['gid'])."' AND `defender` = '".$_SESSION['user']['omni']."' LIMIT 1;");

}

if (!$game) {

	$content .= '<br /><br />
<table border="1" cellspacing="0" cellpadding="0" class="sub">
  <tr>
   <th colspan="2">
    Das O-Wars Offiziers Casino - Multiplayer Edition
   </th>
  </tr>
  <tr valign="top">
   <td style="width:370px" align="center">
    <br />
	<form method="post" action="?'. SID .'" enctype="multipart/form-data">
	<table border="1" cellpadding="0" cellspacing="0" class="standard">
		<tr>
			<th style="width:290px;" colspan="2">
				Spieler herausfordern:
			</th>
		</tr>
		<tr>
			<td style="width:90px">
				&nbsp;<b>UBL:</b>
			</td>
			<td class="input">
				<input class="input" name="ubl" type="text" style="width:100%">
			</td>			
		</tr>
		<tr>
			<td>
				&nbsp;<b>Einsatz:</b>
			</td>
			<td class="input">
				<input class="input" name="bet" value="'.$ressis['display_gold'].'" type="text" style="width:100%">
			</td>			
		</tr>		
		<tr>
			<td colspan="2" class="input">
				<input class="input" name="submit" type="submit" value="Herausforderung senden!" style="width:100%">
			</td>			
		</tr>				
	</table>
	</form>
	<br />
	<div align="left" width="100%" style="padding-left:5px;padding-right:5px;text-align:justify">
	<b>Anleitung:</b><br /><br />
	Hier hast du die M&ouml;glichkeit, andere Spieler zu einem 17+4 Duell herauszufordern und um einen Einsatz beliebiger h&ouml;he zu Spielen. (Das Limit setzt dein Goldvorrat.)<br /><br />
	Gib einfach die UBL des Spielers, den du herausfordern willst, sowie die Menge Gold, die du einsetzen willst in das Formular ein und klicke auf "Herausforderung senden!".<br /><br />
	Dein Einsatz wird dir sofort abgezogen und du kannst ihn nur wiederbekommen, wenn du das Spiel gewinnst, oder der Gegner das Spiel ablehnt, oder, damit dein Gold nicht weg ist, falls dein Gegner weder annimmt noch ablehnt kannst du nach 24h diese Herausforderung zur&uuml;cknehmen.<br /><br />
	Bei unentschiedenen, gewonnenen oder abgelehnten Spielen, kannst du dir danach das Gold direkt in die Basis transferieren, indem du auf auszahlen klickst.<br /><br />
	Bedenke bei deiner Herausforderung, das dein Gegner auch soviel Gold einzahlen muss, wie du. Daher w&auml;hre es sinnlos, z.B. einen ganz neuen Spieler herauszufordern und direkt um 1000 Gold spielen zu wollen.<br /><br />
	Ausserdem, muss dein Rohstofflager die DOPPELTE Menge Gold lagern k&ouml;nnen, damit du, wenn du gewinnst, das Gold auszahlen lassen kannst, ohne das du welches verlierst.<br /><br />
	</div>
   </td>
   <td align="center" style="width:340px">
     <br />
	';
	
	
	$content .= '<b>Empfangene Herausforderungen:</b><br />';
	
	$content .= '<table border="1" cellpadding="0" cellspacing="0" class="standard"><tr><th style="width:65px">UBL</th><th style="width:80px">Einsatz</th></th><th style="width:100px">Zeitpunkt</th></th><th style="width:65px">Annehmen</th></tr>';
	
	$result = mysql_query("SELECT * FROM `cards` WHERE `defender` = '".$_SESSION['user']['omni']."' and `status` = '1';");
	
	for ($row = mysql_fetch_array($result); $row ;$row = mysql_fetch_array($result)) {
		
		$content .= '<tr><td align="center">'.$row['offender'].'</td><td align="right">'.number_format($row['bet'],0).' Gold&nbsp;</td></td><td align="center">'.date('d.m.y - H:i',$row['timestamp']).'</td></td><td align="center"><a href="cards_multi.php?'.SID.'&amp;gid='.$row['gid'].'&accept=1">Ja</a> / <a href="cards_multi.php?'.SID.'&amp;gid='.$row['gid'].'&accept=2">Nein</a></td></tr>';
		
	}
	
	$content .= '</table><br />';
	
	$content .= '<b>Gesendete Herausforderungen:</b><br />';
	
	$content .= '<table border="1" cellpadding="0" cellspacing="0" class="standard"><tr><th style="width:65px">UBL</th><th style="width:80px">Einsatz</th></th><th style="width:100px">Zeitpunkt</th></th><th style="width:65px">&nbsp;</th></tr>';
	
	$result = mysql_query("SELECT * FROM `cards` WHERE `offender` = '".$_SESSION['user']['omni']."' and `status` = '1';");
	
	for ($row = mysql_fetch_array($result); $row ;$row = mysql_fetch_array($result)) {

		((time()-$row['timestamp']) > (3600*24)) ? $scrub = '<a href="cards_multi.php?'.SID.'&amp;gid='.$row['gid'].'&pay=1&status=cancel">auszahlen</a>' : $scrub = '-';
		$content .= '<tr><td align="center">'.$row['defender'].'</td><td align="right">'.number_format($row['bet'],0).' Gold&nbsp;</td></td><td align="center">'.date('d.m.y - H:i',$row['timestamp']).'</td></td><td align="center">'.$scrub.'</td></tr>';
		
	}
	
	$content .= '</table><br />';
	
	$content .= '<b>Abgelehnte Herausforderungen:</b><br />';
	
	$content .= '<table border="1" cellpadding="0" cellspacing="0" class="standard"><tr><th style="width:65px">UBL</th><th style="width:80px">Einsatz</th></th><th style="width:100px">Zeitpunkt</th></th><th style="width:65px">&nbsp;</th></tr>';
	
	$result = mysql_query("SELECT * FROM `cards` WHERE `offender` = '".$_SESSION['user']['omni']."' and `status` = '99';");
	
	for ($row = mysql_fetch_array($result); $row ;$row = mysql_fetch_array($result)) {

		$content .= '<tr><td align="center">'.$row['defender'].'</td><td align="right">'.number_format($row['bet'],0).' Gold&nbsp;</td></td><td align="center">'.date('d.m.y - H:i',$row['timestamp']).'</td></td><td align="center"><a href="cards_multi.php?'.SID.'&amp;gid='.$row['gid'].'&pay=1&status=canceled">auszahlen</a></td></tr>';
		
	}
	
	$content .= '</table>(Es wird der Einsatz ausgezahlt)<br /><br />';			
	
	$content .= '<b>Gewonnene Spiele:</b><br />';
	
	$content .= '<table border="1" cellpadding="0" cellspacing="0" class="standard"><tr><th style="width:65px">UBL</th><th style="width:80px">Einsatz</th></th><th style="width:100px">Ausgang</th></th><th style="width:65px">&nbsp;</th></tr>';
	
	$result = mysql_query("SELECT * FROM `cards` WHERE `defender` = '".$_SESSION['user']['omni']."' and `status` = '4' and `payout_defender` = '0';");
	
	for ($row = mysql_fetch_array($result); $row ;$row = mysql_fetch_array($result)) {

		$hand_o = explode(':', $row['hand_offender']);
		$hand_d = explode(':', $row['hand_defender']);
		
		$content .= '<tr><td align="center">'.$row['offender'].'</td><td align="right">'.number_format($row['bet'],0).' Gold&nbsp;</td></td><td align="center">'.score($hand_d).' - '.score($hand_o).'</td></td><td align="center"><a href="cards_multi.php?'.SID.'&amp;gid='.$row['gid'].'&pay=1&status=won">auszahlen</a></td></tr>';
		
	}

	$result = mysql_query("SELECT * FROM `cards` WHERE `offender` = '".$_SESSION['user']['omni']."' and `status` = '3' and `payout_offender` = '0';");
	
	for ($row = mysql_fetch_array($result); $row ;$row = mysql_fetch_array($result)) {

		$hand_o = explode(':', $row['hand_offender']);
		$hand_d = explode(':', $row['hand_defender']);
		
		$content .= '<tr><td align="center">'.$row['defender'].'</td><td align="right">'.number_format($row['bet'],0).' Gold&nbsp;</td></td><td align="center">'.score($hand_o).' - '.score($hand_d).'</td></td><td align="center"><a href="cards_multi.php?'.SID.'&amp;gid='.$row['gid'].'&pay=1&status=won">auszahlen</a></td></tr>';
		
	}	
	
	$content .= '</table>(Es wird der <font color="red">doppelte</font> Einsatz ausgezahlt)<br /><br />';				

	$content .= '<b>Unentschiedene Spiele:</b><br />';
	
	$content .= '<table border="1" cellpadding="0" cellspacing="0" class="standard"><tr><th style="width:65px">UBL</th><th style="width:80px">Einsatz</th></th><th style="width:100px">Ausgang</th></th><th style="width:65px">&nbsp;</th></tr>';
	
	$result = mysql_query("SELECT * FROM `cards` WHERE `offender` = '".$_SESSION['user']['omni']."' and `status` = '5' and `payout_offender` = '0';");
	
	for ($row = mysql_fetch_array($result); $row ;$row = mysql_fetch_array($result)) {

		$hand_o = explode(':', $row['hand_offender']);
		$hand_d = explode(':', $row['hand_defender']);

		$content .= '<tr><td align="center">'.$row['defender'].'</td><td align="right">'.number_format($row['bet'],0).' Gold&nbsp;</td></td><td align="center">'.score($hand_o).' - '.score($hand_d).'</td></td><td align="center"><a href="cards_multi.php?'.SID.'&amp;gid='.$row['gid'].'&pay=1&status=draw">auszahlen</a></td></tr>';
		
	}
	
	$result = mysql_query("SELECT * FROM `cards` WHERE `defender` = '".$_SESSION['user']['omni']."' and `status` = '5' and `payout_defender` = '0';");
	
	for ($row = mysql_fetch_array($result); $row ;$row = mysql_fetch_array($result)) {

		$hand_o = explode(':', $row['hand_offender']);
		$hand_d = explode(':', $row['hand_defender']);

		$content .= '<tr><td align="center">'.$row['offender'].'</td><td align="right">'.number_format($row['bet'],0).' Gold&nbsp;</td></td><td align="center">'.score($hand_d).' - '.score($hand_o).'</td></td><td align="center"><a href="cards_multi.php?'.SID.'&amp;gid='.$row['gid'].'&pay=1&status=draw">auszahlen</a></td></tr>';

		
	}	
	
	$content .= '</table>(Es wird der Einsatz ausgezahlt)<br /><br />';			

	$content .= '<b>Verlorene Spiele:</b><br />';
	
	$content .= '<table border="1" cellpadding="0" cellspacing="0" class="standard"><tr><th style="width:65px">UBL</th><th style="width:80px">Einsatz</th></th><th style="width:100px">Ausgang</th></th><th style="width:65px">&nbsp;</th></tr>';
	
	$result = mysql_query("SELECT * FROM `cards` WHERE `offender` = '".$_SESSION['user']['omni']."' and `status` = '4' and `payout_offender` = '0';");
	
	for ($row = mysql_fetch_array($result); $row ;$row = mysql_fetch_array($result)) {

		$hand_o = explode(':', $row['hand_offender']);
		$hand_d = explode(':', $row['hand_defender']);
		
		if ($row['offender'] == $_SESSION['user']['omni']) {
			
			$ubl = $row['defender'];
			
		} else {
			
			$ubl = $row['offender'];
		
		}

		$content .= '<tr><td align="center">'.$ubl.'</td><td align="right">'.number_format($row['bet'],0).' Gold&nbsp;</td></td><td align="center">'.score($hand_o).' - '.score($hand_d).'</td></td><td align="center"><a href="cards_multi.php?'.SID.'&amp;gid='.$row['gid'].'&pay=1&status=lost">l&ouml;schen</a></td></tr>';
		
	}

	$result = mysql_query("SELECT * FROM `cards` WHERE `defender` = '".$_SESSION['user']['omni']."' and `status` = '3' and `payout_defender` = '0';");
	
	for ($row = mysql_fetch_array($result); $row ;$row = mysql_fetch_array($result)) {

		$hand_o = explode(':', $row['hand_offender']);
		$hand_d = explode(':', $row['hand_defender']);

		$content .= '<tr><td align="center">'.$row['defender'].'</td><td align="right">'.number_format($row['bet'],0).' Gold&nbsp;</td></td><td align="center">'.score($hand_d).' - '.score($hand_o).'</td></td><td align="center"><a href="cards_multi.php?'.SID.'&amp;gid='.$row['gid'].'&pay=1&status=lost">l&ouml;schen</a></td></tr>';
		
	}
	
	$content .= '</table><br /></td></tr></table><br />';

} elseif ($game['status'] == 0) {

	$content .= '<br /><br /><table border="1" cellspacing="0" cellpadding="0" class="standard" style="width:600px">
  <tr>
   <th>
    Das O-Wars Offiziers Casino - Multiplayer Edition
   </th>
  </tr>
  <tr valign="top">
   <td align="left" style="padding-left:5px">
     <b>Gegner: UBL '.$game['defender'].'</b><br />
     <b>Einsatz: '.$game['bet'].' Gold</b><br />';
	
	$stack = explode(':', $game['stack']);
	$hand  = explode(':', $game['hand_offender']);

	if ($_GET['action'] ==  'hit') {
		
		$card    = rand(0,count($stack)-1);
		$hand[count($hand)] = $stack[$card];
	
		unset($stack[$card]);
		sort($stack);
		
		if (score($hand) <= 21) {
			
			mysql_query("UPDATE `cards` SET `stack` = '".implode(':', $stack)."', `hand_offender` = '".implode(':', $hand)."' WHERE `gid` = '".$game['gid']."' LIMIT 1;");	
			
		} else {
			
			$_GET['action'] = 'stand';
			$text = 'Du hast dich &uuml;berzogen, hoffentlich macht das dein Gegner auch.<br />';
			mysql_query("UPDATE `cards` SET `status` = '1' WHERE `gid` = '".$game['gid']."' LIMIT 1;");
			mysql_query("UPDATE `cards` SET `stack` = '".implode(':', $stack)."', `hand_offender` = '".implode(':', $hand)."' WHERE `gid` = '".$game['gid']."' LIMIT 1;");
			
		}
		
	} elseif ($_GET['action'] == 'stand') { 
		
		mysql_query("UPDATE `cards` SET `stack` = '".implode(':', $stack)."', `hand_offender` = '".implode(':', $hand)."' WHERE `gid` = '".$game['gid']."' LIMIT 1;");
		mysql_query("UPDATE `cards` SET `status` = '1' WHERE `gid` = '".$game['gid']."' LIMIT 1;");
		
	}
	
	if ($_GET['action'] != 'stand') {
		
		$content .= '<br /><b>Deine Punkte: '.score($hand).'</b><br /><b>Deine Karten:<br />'.showcards($hand).'<br />
		<a href="cards_multi.php?'.SID.'&amp;action=hit" />hit</a> / <a href="cards_multi.php?'.SID.'&amp;action=stand" />stand</a>';
		
	} else {
		$content .= '<br /><b>Deine Punkte: '.score($hand).'</b><br /><b>Deine Karten:<br />'.showcards($hand).'<br />
		'.$text.'Diese Herausforderung wurde gespeichert.<br />';
		$content .= '<a href="cards_multi.php?'.SID.'" />klicke hier um zur uebersicht zu kommen</a>';
		
	}
	
	$content .= '</td></tr></table>';
	
} elseif ($game['status'] == 2) {
	
	$content .= '<br /><br /><table border="1" cellspacing="0" cellpadding="0" class="standard" style="width:600px">
  <tr>
   <th>
    Das O-Wars Offiziers Casino - Multiplayer Edition
   </th>
  </tr>
  <tr valign="top">
   <td align="left" style="padding-left:5px">
     <b>Gegner: UBL '.$game['offender'].'</b><br />
     <b>Einsatz: '.$game['bet'].' Gold</b><br />';

	$stack = explode(':', $game['stack']);
	$hand  = explode(':', $game['hand_defender']);
	$hand_o= explode(':', $game['hand_offender']);
	
	if (!$_GET['action']) {

		if ($hand[0] == "") {
			$card    = rand(0,count($stack)-1);
			$hand[0] = $stack[$card];
	
			unset($stack[$card]);
			sort($stack);
		
			mysql_query("UPDATE `cards` SET `stack` = '".implode(':', $stack)."', `hand_defender` = '".implode(':', $hand)."' WHERE `gid` = '".$game['gid']."' LIMIT 1;");		
			
		}	
	
	} elseif ($_GET['action'] ==  'hit') {
		
		$card    = rand(0,count($stack)-1);
		$hand[count($hand)] = $stack[$card];
		
		unset($stack[$card]);
		sort($stack);
		
		if (score($hand) <= 21) {
			
			mysql_query("UPDATE `cards` SET `stack` = '".implode(':', $stack)."', `hand_defender` = '".implode(':', $hand)."' WHERE `gid` = '".$game['gid']."' LIMIT 1;");		
			
		} else {
			
			$_GET['action'] = 'stand';
			if (score($hand_o) > 21) {
				$text = 'Dieses Spiel endet unentschieden<br />';
				mysql_query("UPDATE `cards` SET `status` = '5' WHERE `gid` = '".$game['gid']."' LIMIT 1;");
			} else {
				$text = '<font class="red">Du hast leider verloren</font><br />';
				mysql_query("UPDATE `cards` SET `status` = '3' WHERE `gid` = '".$game['gid']."' LIMIT 1;");
			}
			
			mysql_query("UPDATE `cards` SET `stack` = '".implode(':', $stack)."', `hand_defender` = '".implode(':', $hand)."' WHERE `gid` = '".$game['gid']."' LIMIT 1;");
			
		}
		
	} elseif ($_GET['action'] == 'stand') { 
		
			if (score($hand_o) > 21) {
				$text = '<font class="green">Du hast gewonnen</font><br />';
				mysql_query("UPDATE `cards` SET `status` = '4' WHERE `gid` = '".$game['gid']."' LIMIT 1;");
			} elseif (score($hand_o) < score($hand)) {
				$text = '<font class="green">Du hast gewonnen</font><br />';
				mysql_query("UPDATE `cards` SET `status` = '4' WHERE `gid` = '".$game['gid']."' LIMIT 1;");
			} elseif (score($hand_o) > score($hand)) {
				$text = '<font class="red">Du hast leider verloren</font><br />';
				mysql_query("UPDATE `cards` SET `status` = '3' WHERE `gid` = '".$game['gid']."' LIMIT 1;");
			} else {
				$text = 'Dieses Spiel endet unentschieden<br />';
				mysql_query("UPDATE `cards` SET `status` = '5' WHERE `gid` = '".$game['gid']."' LIMIT 1;");
			}
			mysql_query("UPDATE `cards` SET `stack` = '".implode(':', $stack)."', `hand_defender` = '".implode(':', $hand)."' WHERE `gid` = '".$game['gid']."' LIMIT 1;");		
		
	}
	
	if ($_GET['action'] != 'stand') {
		
		$content .= '<br /><b>Deine Punkte: '.score($hand).'</b><br /><b>Deine Karten:<br />'.showcards($hand).'<br />
		<a href="cards_multi.php?'.SID.'&amp;action=hit" />hit</a> / <a href="cards_multi.php?'.SID.'&amp;action=stand" />stand</a>';
		
	} else {
	
		$content .= '<br /><b>Deine Punkte: '.score($hand).'</b><br /><b>Deine Karten:<br />'.showcards($hand).'<br /><br />
		<b>gegnerische Punkte: '.score($hand_o).'</b><br /><b>gegnerische Karten:<br />'.showcards($hand_o).'<br /><br />
		'.$text.'<a href="cards_multi.php?'.SID.'" />klicke hier um zur uebersicht zu kommen</a>';
		
	}
	
	$content .= '</td></tr></table>';
}

$ressis = ressistand($_SESSION['user']['omni']);

// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);

echo tag2value('onload',$onload,template('head')).$status.$ressis['html'].$content.'</table>'.template('footer');

function showcards($hand) {
	// shows the current hand
	for ($i=0; $hand[$i]; $i++) {
			$cards .= '<img src="cards/'.$hand[$i].'.gif" alt="card">';
	}
	
	return $cards;
}

function score($data) {
	// shows the current hand
	$i = 0;
	do {
		if ($data[$i]) {
			$card   = explode("-", $data[$i]);
			if ($card[0] == 1) {
				if ($card[1] <= 10)		{$score = $score + $card[1];}
				elseif ($card[1] == 11) {$score = $score + 2;}
				elseif ($card[1] == 12) {$score = $score + 3;}
				elseif ($card[1] == 13) {$score = $score + 4;}
				elseif ($card[1] == 14) {$score = $score + 11;}
			}
			elseif ($card[0] == 2) {
				if ($card[1] <= 10)		{$score = $score + $card[1];}
				elseif ($card[1] == 11) {$score = $score + 2;}
				elseif ($card[1] == 12) {$score = $score + 3;}
				elseif ($card[1] == 13) {$score = $score + 4;}
				elseif ($card[1] == 14) {$score = $score + 11;}
			}
			elseif ($card[0] == 3) {
				if ($card[1] <= 10)		{$score = $score + $card[1];}
				elseif ($card[1] == 11) {$score = $score + 2;}
				elseif ($card[1] == 12) {$score = $score + 3;}
				elseif ($card[1] == 13) {$score = $score + 4;}
				elseif ($card[1] == 14) {$score = $score + 11;}
			}
			elseif ($card[0] == 4) {
				if ($card[1] <= 10)		{$score = $score + $card[1];}
				elseif ($card[1] == 11) {$score = $score + 2;}
				elseif ($card[1] == 12) {$score = $score + 3;}
				elseif ($card[1] == 13) {$score = $score + 4;}
				elseif ($card[1] == 14) {$score = $score + 11;}
			}
		}
		$i++;
	} while ($data[$i]);
	
	return $score;
}

function shufflecards32(){
	// shuffles the cards and stores them in the session
	$cards = 1;
	do {
		$number = rand(1,32);
		if ($number == 1 AND $a[$number] != 1)		{$deck[$cards]= "1-7" ;$cards++; $a[$number] = 1;}
		elseif ($number == 2 AND $a[$number] != 1)	{$deck[$cards]= "2-7" ;$cards++; $a[$number] = 1;}
		elseif ($number == 3 AND $a[$number] != 1)	{$deck[$cards]= "3-7" ;$cards++; $a[$number] = 1;}
		elseif ($number == 4 AND $a[$number] != 1)	{$deck[$cards]= "4-7" ;$cards++; $a[$number] = 1;}
		elseif ($number == 5 AND $a[$number] != 1)	{$deck[$cards]= "1-8" ;$cards++; $a[$number] = 1;}
		elseif ($number == 6 AND $a[$number] != 1)	{$deck[$cards]= "2-8" ;$cards++; $a[$number] = 1;}
		elseif ($number == 7 AND $a[$number] != 1)	{$deck[$cards]= "3-8" ;$cards++; $a[$number] = 1;}
		elseif ($number == 8 AND $a[$number] != 1)	{$deck[$cards]= "4-8" ;$cards++; $a[$number] = 1;}
		elseif ($number == 9 AND $a[$number] != 1)	{$deck[$cards]= "1-9" ;$cards++; $a[$number] = 1;}
		elseif ($number == 10 AND $a[$number] != 1)	{$deck[$cards]= "2-9" ;$cards++; $a[$number] = 1;}
		elseif ($number == 11 AND $a[$number] != 1)	{$deck[$cards]= "3-9" ;$cards++; $a[$number] = 1;}
		elseif ($number == 12 AND $a[$number] != 1)	{$deck[$cards]= "4-9" ;$cards++; $a[$number] = 1;}
		elseif ($number == 13 AND $a[$number] != 1)	{$deck[$cards]= "1-10" ;$cards++; $a[$number] = 1;}
		elseif ($number == 14 AND $a[$number] != 1)	{$deck[$cards]= "2-10" ;$cards++; $a[$number] = 1;}
		elseif ($number == 15 AND $a[$number] != 1)	{$deck[$cards]= "3-10" ;$cards++; $a[$number] = 1;}
		elseif ($number == 16 AND $a[$number] != 1)	{$deck[$cards]= "4-10" ;$cards++; $a[$number] = 1;}
		elseif ($number == 17 AND $a[$number] != 1)	{$deck[$cards]= "1-11" ;$cards++; $a[$number] = 1;}
		elseif ($number == 18 AND $a[$number] != 1)	{$deck[$cards]= "2-11" ;$cards++; $a[$number] = 1;}
		elseif ($number == 19 AND $a[$number] != 1)	{$deck[$cards]= "3-11" ;$cards++; $a[$number] = 1;}
		elseif ($number == 20 AND $a[$number] != 1)	{$deck[$cards]= "4-11" ;$cards++; $a[$number] = 1;}
		elseif ($number == 21 AND $a[$number] != 1)	{$deck[$cards]= "1-12" ;$cards++; $a[$number] = 1;}
		elseif ($number == 22 AND $a[$number] != 1)	{$deck[$cards]= "2-12" ;$cards++; $a[$number] = 1;}
		elseif ($number == 23 AND $a[$number] != 1)	{$deck[$cards]= "3-12" ;$cards++; $a[$number] = 1;}
		elseif ($number == 24 AND $a[$number] != 1)	{$deck[$cards]= "4-12" ;$cards++; $a[$number] = 1;}
		elseif ($number == 25 AND $a[$number] != 1)	{$deck[$cards]= "1-13" ;$cards++; $a[$number] = 1;}
		elseif ($number == 26 AND $a[$number] != 1)	{$deck[$cards]= "2-13" ;$cards++; $a[$number] = 1;}
		elseif ($number == 27 AND $a[$number] != 1)	{$deck[$cards]= "3-13" ;$cards++; $a[$number] = 1;}
		elseif ($number == 28 AND $a[$number] != 1)	{$deck[$cards]= "4-13" ;$cards++; $a[$number] = 1;}
		elseif ($number == 29 AND $a[$number] != 1)	{$deck[$cards]= "1-14" ;$cards++; $a[$number] = 1;}
		elseif ($number == 30 AND $a[$number] != 1)	{$deck[$cards]= "2-14" ;$cards++; $a[$number] = 1;}
		elseif ($number == 31 AND $a[$number] != 1)	{$deck[$cards]= "3-14" ;$cards++; $a[$number] = 1;}
		elseif ($number == 32 AND $a[$number] != 1)	{$deck[$cards]= "4-14" ;$cards++; $a[$number] = 1;}
	} while ($cards <= 32);
	
	return $deck;
}
?>