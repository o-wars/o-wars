<?php
//////////////////////////////////
// clan.php                     //
//////////////////////////////////
// Letzte Aenderung: 01.11.2005 //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";
//include "debuglib.php";

// check session
logincheck();

// html head setzen
$content = template('head');
$content = tag2value('onload', '', $content);

// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);
$content .= $status;

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION['user']['omni']);
$content .= $ressis['html'];

$dbh = db_connect();
if ($_POST['claninfo'] == 1) {
	$result = mysql_query("SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';");
	$clans  = mysql_fetch_array($result);
	$_POST['info'] = str_replace('\'','`', $_POST['info']);
	if ($clans['leader'] != 1){ $action = 'Fehlende Berechtigung!'; }
	else {
		mysql_query("UPDATE `clan_info` SET `info` = '".htmlspecialchars($_POST['info'])."' WHERE `clanid` = '".$clans['clanid']."' LIMIT 1;");
	}
}

if ($_POST['clanlogo'] == 1) {
	$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$clans  = mysql_fetch_array($result);
	
	if ($clans['leader'] != 1){ $action = 'Fehlende Berechtigung!'; }
	else {
		$select = "UPDATE `clan_info` SET `img` = '".htmlspecialchars($_POST['url'])."' WHERE `clanid` = '".$clans['clanid']."' LIMIT 1;";
		mysql_query($select);
	}
}

if ($_GET['nopeace']) {
	$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$clans  = mysql_fetch_array($result);
	
	if ($clans['leader'] != 1){ $action = 'Fehlende Berechtigung!'; }
	else {
		$select = "SELECT * FROM `clanwars` WHERE `id` = ".number_format($_GET['nopeace'],0,'','')." and `ended` = 0 LIMIT 1;";
		$result = mysql_query($select);
		$row    = @mysql_fetch_array($result);
		if ($row){
			if ($row['clan1'] == $clans['clanid']) {
					$select = "UPDATE `clanwars` SET `frieden1` = '0' WHERE `id` = '".htmlentities($_GET['nopeace'])."' LIMIT 1;"; 
			} elseif ($row['clan2'] == $clans['clanid']) {
				$select = "UPDATE `clanwars` SET `frieden2` = '0' WHERE `id` = '".htmlentities($_GET['nopeace'])."' LIMIT 1;"; 
			}
			mysql_query($select);
		}
	}
}

if ($_GET['peace']) {
	$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$clans  = mysql_fetch_array($result);
	
	if ($clans['leader'] != 1){ $action = 'Fehlende Berechtigung!'; }
	else {
		$select = "SELECT * FROM `clanwars` WHERE `id` = ".number_format($_GET['peace'],0,'','')." and `ended` = 0 LIMIT 1;";
		$result = mysql_query($select);
		$row    = @mysql_fetch_array($result);
		if ($row){
			if ($row['clan1'] == $clans['clanid']) {
				if     ($row['frieden1'] == 0 and $row['frieden2'] == 0) { $select = "UPDATE `clanwars` SET `frieden1` = '1' WHERE `id` = '".$_GET['peace']."' LIMIT 1;"; }
				elseif ($row['frieden1'] == 0 and $row['frieden2'] == 1) { 
					$select = "UPDATE `clanwars` SET `ended` = '".date('U')."' WHERE `id` = '".htmlentities($_GET['peace'])."' LIMIT 1;"; 
					$result = @mysql_query("SELECT * FROM `clan_info` WHERE `clanid` = '".$row['clan1']."';");
					$clan1  = @mysql_fetch_array($result);
					$result = @mysql_query("SELECT * FROM `clan_info` WHERE `clanid` = '".$row['clan2']."';");
					$clan2  = @mysql_fetch_array($result);					
					$result = @mysql_query("SELECT * FROM `clans` WHERE `clanid` = '".$row['clan1']."';");
					do {
						$row1 = @mysql_fetch_array($result);
						$sel = "INSERT INTO `nachrichten` ( `id` , `from` , `from_name` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_SESSION['user']['name']."', '".$row1['userid']."', '".date(U)."', '0', '[color=&quot;red&quot;]Frieden![/color]', 'Der Krieg zwischen den Clans ".$clan1['tag']." und ".$clan2['tag']." ist nun nach langen Verhandlungen endlich beendet.');";
						if ($row1){mysql_query($sel);}
					} while ($row1);
					$result = @mysql_query("SELECT * FROM `clans` WHERE `clanid` = '".$row['clan2']."';");
					do {
						$row1 = @mysql_fetch_array($result);
						$sel = "INSERT INTO `nachrichten` ( `id` , `from` , `from_name` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_SESSION['user']['name']."', '".$row1['userid']."', '".date(U)."', '0', '[color=&quot;red&quot;]Frieden![/color]', 'Der Krieg zwischen den Clans ".$clan1['tag']." und ".$clan2['tag']." ist nun nach langen Verhandlungen endlich beendet.');";
						if ($row1){mysql_query($sel);}
					} while ($row1);
				}
			} elseif ($row['clan2'] == $clans['clanid']) {
				if     ($row['frieden1'] == 0 and $row['frieden2'] == 0) { $select = "UPDATE `clanwars` SET `frieden2` = '1' WHERE `id` = '".$_GET['peace']."' LIMIT 1;"; }
				elseif ($row['frieden1'] == 1 and $row['frieden2'] == 0) { 
					$select = "UPDATE `clanwars` SET `ended` = '".date('U')."' WHERE `id` = '".htmlentities($_GET['peace'])."' LIMIT 1;"; 
					$result = @mysql_query("SELECT * FROM `clan_info` WHERE `clanid` = '".$row['clan1']."';");
					$clan1  = @mysql_fetch_array($result);
					$result = @mysql_query("SELECT * FROM `clan_info` WHERE `clanid` = '".$row['clan2']."';");
					$clan2  = @mysql_fetch_array($result);					
					$result = @mysql_query("SELECT * FROM `clans` WHERE `clanid` = '".$row['clan1']."';");
					do {
						$row = @mysql_fetch_array($result);
						$sel = "INSERT INTO `nachrichten` ( `id` , `from` , `from_name` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_SESSION['user']['name']."', '".$row['userid']."', '".date(U)."', '0', '[color=&quot;red&quot;]Frieden![/color]', 'Der Krieg zwischen den Clans ".$clan1['tag']." und ".$clan2['tag']." ist nun nach langen verhandlungen endlich beendet.');";
						if ($row){mysql_query($sel);}
					} while ($row);
					$result = @mysql_query("SELECT * FROM `clans` WHERE `clanid` = '".$row['clan2']."';");
					do {
						$row = @mysql_fetch_array($result);
						$sel = "INSERT INTO `nachrichten` ( `id` , `from` , `from_name` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_SESSION['user']['name']."', '".$row['userid']."', '".date(U)."', '0', '[color=&quot;red&quot;]Frieden![/color]', 'Der Krieg zwischen den Clans ".$clan1['tag']." und ".$clan2['tag']." ist nun nach langen verhandlungen endlich beendet.');";
						if ($row){mysql_query($sel);}
					} while ($row);					
				}
			}
			mysql_query($select);
		}
	}
}


if ($_POST['clanwar']) {
	$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$clans  = mysql_fetch_array($result);
	
	$_POST['clanwar'] = htmlspecialchars($_POST['clanwar']);
	if ($clans['leader'] != 1){ $action = 'Fehlende Berechtigung!'; }
	else {
		$select = "SELECT * FROM `clanwars` WHERE `clan2` =".$_POST['clanwar']." AND `clan1` =".$clans['clanid']." AND `ended` = 0;";
		$result = mysql_query($select);
		$select  = "SELECT * FROM `clanwars` WHERE `clan1` =".$_POST['clanwar']." AND `clan2` =".$clans['clanid']." AND `ended` = 0;";
		$result2 = mysql_query($select);
		if (@mysql_num_rows($result) or @mysql_num_rows($result2)) {
			$action = "Du bist mit diesem Clan bereits im Krieg!";
		} else {
			$select = "SELECT * FROM `clans` WHERE `clanid` =".$_POST['clanwar'].";";
			$result = mysql_query($select);	
			$row = mysql_fetch_array($result);
			if (!$row) {
				$action = "Dieser Clan existiert nicht!";
			} elseif ($row['clanid'] == $clans['clanid']) {
				$action = "Du kannst nicht deinem eigenen Clan den Krieg erkl&auml;ren!";
			} else {
				$action = "Du befindest dich nun mit Clan Nr. ".$_POST['clanwar'].' im Krieg!';
				$select = "INSERT INTO `clanwars` ( `id` , `clan1` , `clan2` , `started` , `ended` , `kampfpunkte1` , `kampfpunkte2` , `ressis1` , `ressis2` , `offender` , `frieden1` , `frieden2` ) VALUES ( '', '".$_POST['clanwar']."', '".$clans['clanid']."', '".date('U')."', '0', '0', '0', '0', '0', '".$clans['clanid']."', '0', '0' );";
				$result = mysql_query($select);
				
				$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$_POST['clanwar']."';";
				$result = mysql_query($select);
				$offended = mysql_fetch_array($result);
				
				$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$clans['clanid']."';";
				$result = mysql_query($select);
				$offender = mysql_fetch_array($result);
								
				$select = "SELECT * FROM `clans` WHERE `clanid` = '".$_POST['clanwar']."';";
				$result = mysql_query($select);
				do {
					$row = mysql_fetch_array($result);
					$select = "INSERT INTO `nachrichten` ( `id` , `from` , `from_name` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_SESSION['user']['name']."', '".$row['userid']."', '".date(U)."', '0', '[color=&quot;red&quot;]Kriegserkl&auml;rung[/color]', 'Dein Clan hat den Krieg durch den Clan ".$offender['name']." erkl&auml;rt bekommen.');";
					if ($row){mysql_query($select);}
				} while ($row);
				
				$select = "SELECT * FROM `clans` WHERE `clanid` = '".$clans['clanid']."';";
				$result = mysql_query($select);
				do {
					$row = mysql_fetch_array($result);
					$select = "INSERT INTO `nachrichten` ( `id` , `from` , `from_name` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_SESSION['user']['name']."', '".$row['userid']."', '".date(U)."', '0', '[color=&quot;red&quot;]Kriegserkl&auml;rung[/color]', 'Dein Clan hat dem Clan ".$offended['name']." den Krieg erkl&auml;rt.');";
					if ($row){mysql_query($select);}
				} while ($row);

			}
		}
	}
}

if ($_GET['giveleader'] == 1) {
	$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$clans  = mysql_fetch_array($result);
	
	$select = "SELECT * FROM `clans` WHERE `userid` = '".number_format($_GET['to'],0,'','')."';";
	$result = mysql_query($select);
	$toleader= mysql_fetch_array($result);
	
	if ($toleader['founder'] == 1){ $action = 'Du kannst den Founder nicht befummeln!'; }
	elseif ($toleader['clanid'] != $clans['clanid']){ $action = 'Fehler!'; }
	elseif ($clans['leader'] != 1){ $action = 'Fehlende Berechtigung!'; }
	else {
		if ($toleader['leader'] == 1){ 
			$select = "UPDATE `clans` SET `leader` = '0' WHERE `userid` = '".number_format($_GET['to'],0,'','')."' LIMIT 1;"; 
			$action = 'Leaderrechte genommen!';
		} else { 
			$select = "UPDATE `clans` SET `leader` = '1' WHERE `userid` = '".number_format($_GET['to'],0,'','')."' LIMIT 1;";
			$action = 'Leaderrechte gegeben!';
		}
		mysql_query($select);
	}
}
if ($_GET['kick'] == 1) {
	$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$clans  = mysql_fetch_array($result);
	
	$select = "SELECT * FROM `clans` WHERE `userid` = '".number_format($_GET['to'],0,'','')."';";
	$result = mysql_query($select);
	$todel  = mysql_fetch_array($result);

	if ($todel['founder'] == 1){ $action = 'Du kannst den Founder nicht kicken!'; }
	elseif ($todel['clanid'] != $clans['clanid']){ $action = 'Fehler!'; }
	elseif ($clans['leader'] != 1){ $action = 'Fehlende Berechtigung!'; }
	else {
		$select = "SELECT * FROM `clans` WHERE `userid` = '".number_format($_GET['to'],0,'','')."';";
		$result = mysql_query($select);
		$clans  = mysql_fetch_array($result);

		$select = "SELECT * FROM `clans` WHERE `clanid` = '".$clans['clanid']."';";
		$result = mysql_query($select);
	
		if (mysql_num_rows($result) < 2){ 
			// $select = "DELETE FROM `clan_info` WHERE `clanid` = '".$clans['clanid']."';";	 
			$select = "UPDATE `clan_info` SET `aufgeloest` = '".date('U')."' WHERE `clanid` = '".$clans['clanid']."' LIMIT 1 ;";
			$result = mysql_query($select);
		}

		$select = "UPDATE `user` SET `clan` = '' WHERE `omni` = '".number_format($_GET['to'],0,'','')."' LIMIT 1;";
		mysql_query($select);
		
		$select = "INSERT INTO `nachrichten` ( `id` , `from` , `from_name` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_SESSION['user']['name']."', '".number_format($_GET['to'],0,'','')."', '".date(U)."', '0', 'Du wurdest aus dem Clan gekickt', 'Du wurdest von ".$_SESSION['user']['name']." aus dem Clan gekickt');";
		$selectResult   = mysql_query($select);
		
		$select = "DELETE FROM `clans` WHERE `userid` = '".number_format($_GET['to'],0,'','')."';";	
		mysql_query($select);
	}
}

if ($_GET['annehmen'] == 1){
	$select = "SELECT * FROM `clan_angebote` WHERE `id` = '".number_format($_GET['angebot'],0,'','')."' AND `user` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$angebot= mysql_fetch_array($result);
	if ($angebot){
		$select = "DELETE FROM `clan_angebote` WHERE `id` = '".number_format($_GET['angebot'],0,'','')."' LIMIT 1;";
		$result = mysql_query($select);
		
		$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
		$result = mysql_query($select);
		$clans  = mysql_fetch_array($result);

		$select = "SELECT * FROM `clans` WHERE `clanid` = '".$clans['clanid']."';";
		$result = mysql_query($select);
	
		if (mysql_num_rows($result) < 2){ 
			// $select = "DELETE FROM `clan_info` WHERE `clanid` = '".$clans['clanid']."';";	 
			$select = "UPDATE `clan_info` SET `aufgeloest` = '".date('U')."' WHERE `clanid` = '".$clans['clanid']."' LIMIT 1 ;";
			$result = mysql_query($select);
		}

		$select = "DELETE FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";	
		mysql_query($select);
		
		$select = "INSERT INTO `clans` ( `id` , `userid` , `clanid` , `founder` , `leader` ) VALUES ( '', '".$_SESSION['user']['omni']."', '".$angebot['clan']."', '0', '0' );";
		mysql_query($select);
		
		$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$angebot['clan']."';";
		$result = mysql_query($select);
		$clan   = mysql_fetch_array($result);
		
		$select = "UPDATE `user` SET `clan` = '".$clan['name']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
		mysql_query($select);
		
		$action = '<b>Du bist dem Clan '.$clan['name'].' beigetreten</b>';
	} else { $action = 'Fehler beim joinen des Clans.'; }
}

if ($_GET['austreten'] == 1){ 
	$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$clans  = mysql_fetch_array($result);

	$select = "SELECT * FROM `clans` WHERE `clanid` = '".$clans['clanid']."';";
	$result = mysql_query($select);
	
	if (mysql_num_rows($result) < 2){ 
		// $select = "DELETE FROM `clan_info` WHERE `clanid` = '".$clans['clanid']."';";	 
		$select = "UPDATE `clan_info` SET `aufgeloest` = '".date('U')."' WHERE `clanid` = '".$clans['clanid']."' LIMIT 1 ;";
		$result = mysql_query($select);
	}
	
	$select = "UPDATE `user` SET `clan` = '' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
	mysql_query($select);
	
	$select = "DELETE FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";	
	mysql_query($select);
}

if ($_POST['clanangebot'] == 1){
	$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$clans  = mysql_fetch_array($result);

	$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$clans['clanid']."';";
	$result = mysql_query($select);
	$clan   = mysql_fetch_array($result);	
	$select = "INSERT INTO `clan_angebote` ( `id` , `user` , `clan` , `timestamp` ) VALUES ( '', '".number_format($_POST['ubl'],0,'','')."', '".$clan['clanid']."', '".date('U')."' );";
	$result = mysql_query($select);
	$id     = mysql_insert_id();
	$einladung = '<center>Du bist in den Clan <b>'.$clan['name'].'</b> eingeladen worden.<br /><br />Willst du das Angebot annehmen?<br /><br /><b><i><a href="clan.php?SID&amp;angebot='.$id.'&amp;annehmen=1">JA</a> / <a href="clan.php?SID&amp;angebot='.$id.'&amp;annehmen=2">NEIN</a></i></b></center>';
	
	$select = "INSERT INTO `nachrichten` ( `id` , `from` , `from_name` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', '".$_SESSION['user']['omni']."', '".$_SESSION['user']['name']."', '".number_format($_POST['ubl'],0,'','')."', '".date(U)."', '0', 'Clanangebot von ".$clan['name']."', '".$einladung."');";
	$selectResult   = mysql_query($select);
	
	$action = 'Du hast '.number_format($_POST['ubl'],0,'','').' in den Clan eingeladen';
}

if ($_POST['newclan'] == 1){
	if (preg_match('/^.{1,15}$/',$_POST['name']) == 0){
		$action = "Clan-Name: Minimum 1, Maximum 15 Zeichen";
	} 
	elseif (preg_match('/^.{1,6}$/',$_POST['tag']) == 0){
		$action = "Clan-Tag: Minimum 1, Maximum 6 Zeichen";
	} 
	else {
		$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
		$result = mysql_query($select);
		$clans  = mysql_fetch_array($result);

		$select = "SELECT * FROM `clans` WHERE `clanid` = '".$clans['clanid']."';";
		$result = mysql_query($select);
	
		$select = "SELECT * FROM `clan_info` WHERE `name` = '".$_POST['name']."';";
		$result1 = mysql_query($select);
		
		$select = "SELECT * FROM `clan_info` WHERE `tag` = '".$_POST['tag']."';";
		$result2 = mysql_query($select);	
	
		if (mysql_num_rows($result) < 2){ 
			// $select = "DELETE FROM `clan_info` WHERE `clanid` = '".$clans['clanid']."';";	 
			$select = "UPDATE `clan_info` SET `aufgeloest` = '".date('U')."' WHERE `clanid` = '".$clans['clanid']."' LIMIT 1 ;";
			$result = mysql_query($select);
		}
	
		if (mysql_num_rows($result1) != 0){ 
			$action = "Es gibt bereits einen Clan mit diesem Namen.";
		} elseif (mysql_num_rows($result2) != 0){ 
			$action = "Es gibt bereits einen Clan mit diesem Tag.";
		} else {
			$select = "DELETE FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";	
			mysql_query($select);
			$select = "INSERT INTO `clan_info` ( `clanid` , `name` , `tag` , `img` ) VALUES ( '', '".htmlspecialchars($_POST['name'])."', '".htmlspecialchars($_POST['tag'])."', '' );";
			mysql_query($select);
			$id = mysql_insert_id();
			$select = "INSERT INTO `clans` ( `id` , `userid` , `clanid` , `founder` , `leader` ) VALUES ( '', '".$_SESSION['user']['omni']."', '".$id."', '1', '1' );";
			mysql_query($select);
			$select = "UPDATE `user` SET `clan` = '".htmlspecialchars($_POST['name'])."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
			mysql_query($select);
		}
	}
}

if ($action){$content .= '<b>'.$action.'</b><br /><br />';}

$content .= '<br /><br /><table border="1" cellspacing="0" class="sub">
	<tbody>
		<tr align="center">
			<th style="width: 670px;">
				Clanmitgliedschaft:
			</th>
		</tr>
		<tr align="center">
			<td>';

$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
$result = mysql_query($select);
$clans  = mysql_fetch_array($result);

if ($clans) { 
	$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$clans['clanid']."';";
	$result = mysql_query($select);
	$clan   = mysql_fetch_array($result);
	if ($clans['founder'] == 1) { $status = 'Founder'; }
	elseif ($clans['leader'] == 1) { $status = 'Leader'; }
	else { $status = 'Member'; }
	if ($clan['img']){ $img = '<img src="'.$clan['img'].'" alt="clan" />'; }
	$content .= 'Dein Clan: <b>'.$clan['name'].'</b> // Tag:<b> '.$clan['tag'].'</b><br />'.$img.'<br />';
	
	$select = "SELECT * FROM `clans` WHERE `clanid` = '".$clans['clanid']."' ORDER BY `founder` DESC,`leader` DESC,`id` ASC;";
	$result = mysql_query($select);
	
	do {
		$clanm  = mysql_fetch_array($result);
		if ($clanm) { 
			
			$select  = "SELECT * FROM `user` WHERE `omni` = '".$clanm['userid']."';";
			$result2 = mysql_query($select);
			$userl   = mysql_fetch_array($result2);

			if ($clanm['founder'] == 1) { $status = 'Founder'; }
			elseif ($clanm['leader'] == 1) { $status = 'Leader'; }
			else { $status = 'Member'; }			
			
			$member .= '		<tr align="center" class="standard">
			<td style="width: 170px;">
				'.$userl['name'].'
			</td>
			<td style="width: 70px;">
				'.$userl['omni'].'
			</td>
			<td style="width: 80px;">
				'.$status.'
			</td>
			<td style="width: 70px;">
				'.number_format($userl['points'],0,',','.').'
			</td>';
			$last = time() - $userl['timestamp'];
			if ($last < 600) {
				$online = '<font class="green">online</font>';
			} elseif ($last < 3600) {
				list ($m) = explode('.', ($last/60));
				$online = '<font class="red">'.$m.' Min.</font>';
			} elseif ($last < 24*3600) {
				list ($h) = explode('.', ($last/60/60));
				$online = '<font class="red">'.$h.' Std.</font>';
			} else {
				list ($d) = explode('.', $last/60/60/24);
				if ($d == 1) {
					$online = '<font class="red">'.$d.' Tag</font>';
				} else {
					$online = '<font class="red">'.$d.' Tage</font>';
				}
			}
			$member .= '<td style="width: 60px;">
			'.$online.'
			</td>';
				
			$member .= '<td style="width: 60px;">
				<a href="nachricht_schreiben.php?'.SID.'&amp;to='.$userl['omni'].'">nachricht</a>
			</td>';

			if ($clans['leader'] == 1) { 
			 $kick = 'clan.php?'.SID.'&kick=1&to='.$userl['omni'];
				$member .= '<td style="width: 60px;">
				<a href="#" onclick="check(\'document.location.href=\\\''.$kick.'\\\'\', \'Willst du diesen Spieler wirklich kicken?\')">kicken</a>
			</td>';}
			if ($clans['founder'] == 1) { 
				$member .= '<td style="width: 60px;">
				<a href="clan.php?'.SID.'&amp;giveleader=1&amp;to='.$userl['omni'].'">leader</a>
			</td>';}		
			
			$member .= '</tr>'; }
	} while ($clanm);
	
	
	$content .= '<table border="1" cellspacing="0" class="standard">
	<tbody>
		<tr align="center">
			<th style="width: 170px;">
				Name
			</th>
			<th style="width: 70px;">
				UBL
			</th>
			<th style="width: 80px;">
				Status
			</th>
			<th style="width: 70px;">
				Punkte
			</th>';
	
			$content .= '<th style="width: 60px;">
			Online
			</th>';
			
			$content .='<th style="width: 60px;">
				&nbsp;
			</th>';

		if ($clans['founder'] == 1) { 
			$content .= '<th style="width: 60px;">
			&nbsp;
		</th>';}
		if ($clans['founder'] == 1) { 
			$content .= '<th style="width: 60px;">
			&nbsp;
		</th>';}			
		$content .= '</tr>
		'.$member.'
	</tbody>
</table>
	<b><a href="#" onclick="check(\'document.location.href=\\\'clan.php?'.SID.'&amp;austreten=1\\\'\', \'Willst du aus diesem Clan wirklich austreten?\')">Aus diesem Clan austreten.</a></b>';
} else {
	$content .= '<b>Du bist in keinem Clan.<br /><br />
	<form enctype="multipart/form-data" action="clan.php?'.SID.'" method="post">
	Clan Name:<br />
	<input type="text" name="name" /><br />
	Clan Tag:<br />
	<input type="text" name="tag" /><br />
	<input type="hidden" name="newclan" value="1" />
	<input type="submit" name="submit" value="Clan gr&uuml;nden!" /><br />
	</form></b>';
}
if ($clans['leader'] == 1) { 
	$result = mysql_query("SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';");
	$clans  = mysql_fetch_array($result);
	$members = mysql_num_rows(mysql_query("SELECT * FROM clans WHERE clanid = '".$clans['clanid']."';"));	
	$users   = mysql_num_rows(mysql_query("SELECT * FROM user;"));
	
	$rate = round($members/($users / 100),2);
	
	if ($rate >= 20) {
		$steuern = '<b><div align="center" class="red">
			Dein Clan hat '.$rate.'% aller Spieler aufgenommen.<br />
			Daher entf&auml;llt der Rohstoff Bonus f&uuml;r alle Clanmember.<br />
		</div></b>';		
	} else {
		$steuern = '<b><div align="center">
			Dein Clan hat '.$rate.'% aller Spieler aufgenommen.<br />
			Sollte dein Clan mehr wie 20% aller Spieler ('.number_format(($users/100*20),1).') aufgenommen haben,
			entf&auml;llt der Rohstoff Bonus.<br />
		</div></b>';			
	}
	
	$content .= '<br /><br />'; 
	$content .= '<b><a href="rundmail.php?'.SID.'"><img src="img/envelope.gif" /> Klicke hier um eine claninterne Rundmail zu versenden. <img src="img/envelope.gif" /></a></b>';
	
	$content .= '<br /><br />'; 
	$content .= '<b>
	<form enctype="multipart/form-data" action="clan.php?'.SID.'" method="post">
	<input type="text" style="width: 60px" name="ubl" />
	in den Clan 
	<input type="hidden" name="clanangebot" value="1" />
	<input type="submit" name="submit" value="einladen" /><br />
	'.$steuern.'
	</form></b>';
	
	$content .= '<br />'; 
	$content .= '<b>
	<form enctype="multipart/form-data" action="clan.php?'.SID.'" method="post">
	Clanlogo: </b>
	<input type="text" style="width: 400px" name="url" value="'.$clan['img'].'" />
	<input type="hidden" name="clanlogo" value="1" />
	<input type="submit" name="submit" value="Speichern" /><br />
	</form>';

	$content .= '<b>
	<form enctype="multipart/form-data" action="clan.php?'.SID.'" method="post">
	<font class="red">Dem Clan mit der ClanID <input type="text" style="width: 60px" name="clanwar" />
	den 
	<input type="submit" name="submit" value=" Krieg erkl&auml;ren." /><br /></font>
	</form></b>';	
	
	$content .= '<br />
	<b>Kriege:</b><br />';
	
	$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$clans  = mysql_fetch_array($result);	
	
	$piece = template('clanwars_piece').'<tr><td colspan="8">%frieden%</td></tr>';
	$select = "SELECT * FROM `clanwars` WHERE `clan1` = ".$clans['clanid']." AND `ended` = 0;";
	$result = mysql_query($select);
	
	do {
		$row = @mysql_fetch_array($result, MYSQL_ASSOC);
		if ($row){
			$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$row['clan1']."';";
			$result2 = mysql_query($select);
			$clan1   = mysql_fetch_array($result2);	

			$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$row['clan2']."';";
			$result2 = mysql_query($select);
			$clan2   = mysql_fetch_array($result2);			

			if ($row['kampfpunkte1'] < 0){ $row['kampfpunkte1'] = '<font class="red">'.$row['kampfpunkte1'].'</font>'; }
			if ($row['kampfpunkte2'] < 0){ $row['kampfpunkte2'] = '<font class="red">'.$row['kampfpunkte2'].'</font>'; }
			if ($row['ressis1'] < 0){ $row['ressis1'] = '<font class="red">'.$row['ressis1'].'</font>'; }
			if ($row['ressis2'] < 0){ $row['ressis2'] = '<font class="red">'.$row['ressis2'].'</font>'; }
			if (!$clan1){ $clan1['tag'] = '<font class="red">aufgel&ouml;st</font>';}
			if (!$clan2){ $clan2['tag'] = '<font class="red">aufgel&ouml;st</font>';}

			if ($row['frieden1'] == 1 and $row['frieden2'] == 0) { $frieden = "Du hast dem Clan ".$clan2['tag']." ein Friedensangebot gemacht. Klicke <a href=\"clan.php?nopeace=".$row['id']."\"><b>hier</b></a> um das Friedensangebot zur&uuml;ck zunehmen."; }
			elseif ($row['frieden1'] == 0 and $row['frieden2'] == 0) { $frieden = "Wenn du dem Clan ".$clan2['tag']." ein Friedensangebot machen m&ouml;chtest klicke <a href=\"clan.php?peace=".$row['id']."&amp;".SID."\"><b>hier</b></a>."; }
			elseif ($row['frieden1'] == 0 and $row['frieden2'] == 1) { $frieden = "Du hast ein Friedensangebot von dem Clan ".$clan2['tag']." bekommen wenn du es annehmen m&ouml;chtest klicke <a href=\"clan.php?peace=".$row['id']."&amp;".SID."\"><b>hier</b></a>."; }
			
			$i++;
			$newpiece = tag2value('id', $row['id'], $piece);
			$newpiece = tag2value('start',date('d.m.y',$row['started']), $newpiece);
			$newpiece = tag2value('clan1','<a href="claninfo.php?'.SID.'&clan='.$clan1['clanid'].'">'.$clan1['tag'].'</a>', $newpiece);
			$newpiece = tag2value('kp1',$row['kampfpunkte1'], $newpiece);
			$newpiece = tag2value('pluenderung1',$row['ressis1'], $newpiece);
			$newpiece = tag2value('clan2','<a href="claninfo.php?'.SID.'&clan='.$clan2['clanid'].'">'.$clan2['tag'].'</a>', $newpiece);
			$newpiece = tag2value('kp2',$row['kampfpunkte2'], $newpiece);
			$newpiece = tag2value('pluenderung2',$row['ressis2'], $newpiece);
			$newpiece = tag2value('frieden',$frieden, $newpiece);
			$ranking .= $newpiece;
		}
	} while($row);

	$select = "SELECT * FROM `clanwars` WHERE `clan2` = ".$clans['clanid']." AND `ended` = 0;";
	$result = mysql_query($select);
	
	do {
		$row = @mysql_fetch_array($result, MYSQL_ASSOC);
		if ($row){
			$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$row['clan1']."';";
			$result2 = mysql_query($select);
			$clan1   = mysql_fetch_array($result2);	

			$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$row['clan2']."';";
			$result2 = mysql_query($select);
			$clan2   = mysql_fetch_array($result2);			

			if ($row['kampfpunkte1'] < 0){ $row['kampfpunkte1'] = '<font class="red">'.$row['kampfpunkte1'].'</font>'; }
			if ($row['kampfpunkte2'] < 0){ $row['kampfpunkte2'] = '<font class="red">'.$row['kampfpunkte2'].'</font>'; }
			if ($row['ressis1'] < 0){ $row['ressis1'] = '<font class="red">'.$row['ressis1'].'</font>'; }
			if ($row['ressis2'] < 0){ $row['ressis2'] = '<font class="red">'.$row['ressis2'].'</font>'; }
			if (!$clan1){ $clan1['tag'] = '<font class="red">aufgel&ouml;st</font>';}
			if (!$clan2){ $clan2['tag'] = '<font class="red">aufgel&ouml;st</font>';}

			if ($row['frieden1'] == 0 and $row['frieden2'] == 1) { $frieden = "Du hast dem Clan ".$clan1['tag']." ein Friedensangebot gemacht. Klicke <a href=\"clan.php?nopeace=".$row['id']."\"><b>hier</b></a> um das Friedensangebot zur&uuml;ck zunehmen."; }
			elseif ($row['frieden1'] == 0 and $row['frieden2'] == 0) { $frieden = "Wenn du dem Clan ".$clan1['tag']." ein Friedensangebot machen m&ouml;chtest klicke <a href=\"clan.php?peace=".$row['id']."&amp;".SID."\"><b>hier</b></a>."; }
			elseif ($row['frieden1'] == 1 and $row['frieden2'] == 0) { $frieden = "Du hast ein Friedensangebot von dem Clan ".$clan1['tag']." bekommen wenn du es annehmen m&ouml;chtest klicke <a href=\"clan.php?peace=".$row['id']."&amp;".SID."\"><b>hier</b></a>."; }
			
			$i++;
			$newpiece = tag2value('id', $row['id'], $piece);
			$newpiece = tag2value('start',date('d.m.y',$row['started']), $newpiece);
			$newpiece = tag2value('clan1','<a href="claninfo.php?'.SID.'&clan='.$clan1['clanid'].'">'.$clan1['tag'].'</a>', $newpiece);
			$newpiece = tag2value('kp1',$row['kampfpunkte1'], $newpiece);
			$newpiece = tag2value('pluenderung1',$row['ressis1'], $newpiece);
			$newpiece = tag2value('clan2','<a href="claninfo.php?'.SID.'&clan='.$clan2['clanid'].'">'.$clan2['tag'].'</a>', $newpiece);
			$newpiece = tag2value('kp2',$row['kampfpunkte2'], $newpiece);
			$newpiece = tag2value('pluenderung2',$row['ressis2'], $newpiece);
			$newpiece = tag2value('frieden',$frieden, $newpiece);
			$ranking .= $newpiece;
		}
	} while($row);
	
	
	$content .= '<table class="standard" border="1" cellspacing="0">
	<tbody>
		<tr align="center">
			<th style="width: 50px;">
				WarID
			</th>
			<th style="width: 90px;">
				Beginn
			</th>			
			<th style="width: 80px;">
				Clan
			</th>			
			<th style="width: 80px;">
				Kampfpunkte
			</th>
			<th style="width: 80px;">
				Pl&uuml;nderung
			</th>
			<th style="width: 80px;">
				Clan
			</th>			
			<th style="width: 80px;">
				Kampfpunkte
			</th>
			<th style="width: 80px;">
				Pl&uuml;nderung
			</th>
		</tr>'.$ranking.'
		</tbody>
		</table>';

	
	$content .= '<br />'; 
	$content .= '<b>Claninfo:<br />
	<form enctype="multipart/form-data" action="clan.php?'.SID.'" method="post">
	<textarea style="background-color:#b2b2b2; width: 600px; height: 200px" name="info" />'.$clan['info'].'</textarea>
	<input type="hidden" name="claninfo" value="1" />
	<br /><br />
	<input type="submit" name="submit" value="Claninfo Speichern" /><br />
	<br />
	<b>Es k&ouml;nnen folgende Tags verwendet werden:</b><br /></b>
	[b] fette Schrift <br />
	[/b] ende fette Schrift<br />
	[i] geschwungene Schrift <br />
	[/i] ende geschwungene Schrift<br />
	[color="farbe"] anfang farbige Schrift (HEX-Farbcode #123456 oder Farbe auf englisch)<br />
	[/color] ende farbige Schrift<br />
	[center] anfang zentrierte Schrift<br />
	[/center] ende zentrierte Schrift
	
		
	<br />
	</form></b>';
} else { 
	if (!$clan['info']){ $clan['info'] = 'keine';}
	
	$clan['info'] = str_replace('[b]','<b>',$clan['info']);
	$clan['info'] = str_replace('[/b]','</b>',$clan['info']);
	$clan['info'] = str_replace('[i]','<i>',$clan['info']);
	$clan['info'] = str_replace('[/i]','</i>',$clan['info']);
	$clan['info'] = str_replace('[color=&quot;','<font color="',$clan['info']);
	$clan['info'] = str_replace('[/color]','</font>',$clan['info']);
	$clan['info'] = str_replace('&quot;]','">',$clan['info']);
	$clan['info'] = str_replace('[center]','<center>',$clan['info']);
	$clan['info'] = str_replace('[/center]','</center>',$clan['info']);
	
	$select = "SELECT * FROM `clanwars` WHERE `clan1` =".$clan['clanid'].";";
	$result = mysql_query($select);
	
	$clan['info'] .= "<br /><br /><b>Kriege:</b> ";
	do {
		$clan1  = @mysql_fetch_array($result);
		if ($clan1){
			$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$clan1['clan2']."';";
			$result2 = mysql_query($select);
			$row    = mysql_fetch_array($result2);
			$clan['info'] .= $row['tag'].' ';
		}
	} while ($clan1);
	
	$select = "SELECT * FROM `clanwars` WHERE `clan2` =".$clan['clanid'].";";
	$result = mysql_query($select);
	
	do {
		$clan2 = @mysql_fetch_array($result);
		if ($clan2){
			$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$clan2['clan1']."';";
			$result2 = mysql_query($select);
			$row    = mysql_fetch_array($result2);
			$clan['info'] .= $row['tag'].' ';
		}
	} while ($clan2);	

	$content .= '<br /><br />'; 
	$content .= '<table style="background-color: rgb(226, 226, 226); font-size: 12px;" border="1" cellspacing="0">
	<tbody>
		<tr align="center" style="background-image:url(templates/standard/table_head.gif);">
			<td style="width: 500px;">
				Claninfo:
			</td>
		</tr>
		<tr align="left">
			<td>
				'.nl2br($clan['info']).'
			</td>
		</tr>
	</tbody>
</table>';
}
$content .= '			</td>
		</tr>
	</tbody>
</table>';

echo $content.template('footer');
//show_vars();
?>
