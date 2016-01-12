<?php
//////////////////////////////////
// Karte                        //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////
// Kommentare:
// - Status Spieler
// - Karte
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

// check session
logincheck();

if ($_POST['id']){$_GET['id'] = $_POST['id'];}
if (!$_GET['id']){$_GET['id'] = $_SESSION['user']['omni'];}
$_GET['id']  = htmlspecialchars($_GET['id']);

// mit datenbank verbinden
$dbh = db_connect();

if ($_GET['mark'] AND $_GET['to']) { 
	$_GET['mark'] = number_format($_GET['mark'],0,'','');
	$_GET['to']   = number_format($_GET['to'],0,'','');
	
	$select = "SELECT * FROM `karte` WHERE `id` = '".$_SESSION['user']['omni']."' AND `omni` = '".$_GET['to']."';";
	$result = mysql_query($select);
	
	if ($_GET['mark'] == 10) {	
		$select = "DELETE FROM `karte` WHERE `id` = '".$_SESSION['user']['omni']."' AND `omni` = '".$_GET['to']."';";
		mysql_query($select);
	} else {
		$select = "DELETE FROM `karte` WHERE `id` = '".$_SESSION['user']['omni']."' AND `omni` = '".$_GET['to']."';";
		mysql_query($select);
		$select = "INSERT INTO `karte` ( `id` , `type` , `omni` ) VALUES ( '".$_SESSION['user']['omni']."', '".$_GET['mark']."', '".$_GET['to']."' );";
		mysql_query($select);	
	}
}

// html head setzen
$content = template('head');

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

$select = "SELECT karte FROM `user` WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;";
$result = mysql_query($select);
$time   = mysql_fetch_array($result);

// eigene position berechnen
$own_position = position($_SESSION['user']['omni']);
if ($_POST['sector'] !='') { $own_position['z']=$_POST['sector']; }

$path = './temp/karte_'.$_SESSION['user']['omni'].'_'.$own_position['z'].'tmp';

$map['start'] = $own_position['z'] * 500;
$map['end']   = ( $own_position['z'] + 1 ) * 500;

$own_position = position($_SESSION['user']['omni']);

$time['karte'] = @filectime($path);

////// activate to debug ///////////
//$time['karte'] = 0;
////////////////////////////////////
if ($time['karte'] < (date('U')-14400)) {
	
$karte .= '<br />';

$karte .= '
<script type="text/javascript">
<!-- Begin
function u(ubl, name, clan, base, entfernung, tf_eisen, tf_titan) { 
	document.getElementById("ubl").firstChild.nodeValue  = ubl;
	document.getElementById("name").firstChild.nodeValue = name;
	document.getElementById("clan").firstChild.nodeValue = clan;
	document.getElementById("base").firstChild.nodeValue = base;
	document.getElementById("entfernung").firstChild.nodeValue = entfernung*2.5;
	document.getElementById("tf_eisen").firstChild.nodeValue = tf_eisen;
	document.getElementById("tf_titan").firstChild.nodeValue = tf_titan;
}
// End -->
</script>
<script type="text/javascript">
<!-- Begin
function t(ubl, name, clan, id, base, entfernung, tf_eisen, tf_titan) { 
	document.getElementById("t_ubl").innerHTML = ubl;
	document.getElementById("t_name").innerHTML = name;
	document.getElementById("t_clan").innerHTML = clan;
	document.getElementById("t_base").innerHTML = base;
	document.getElementById("t_entfernung").innerHTML = entfernung*2.5;
	document.getElementById("t_tf_eisen").innerHTML = tf_eisen;
	document.getElementById("t_tf_titan").innerHTML = tf_titan;
	document.getElementById("t_clanlink").href = "claninfo.php?" + "SID" + "&clan=" + id;
	document.getElementById("t_mission").href = "mission.php?" + "SID" + "&to=" + ubl;
	document.getElementById("t_beschuss").href = "beschuss.php?" + "SID" + "&to=" + ubl;
	document.getElementById("t_nachricht").href = "nachricht_schreiben.php?" + "SID" + "&to=" + ubl;
	document.getElementById("t_profil").href = "profil.php?" + "SID" + "&ubl=" + ubl
	document.getElementById("t_neutral").href = "karte.php?" + "SID" + "&mark=10&to=" + ubl;
	document.getElementById("t_farm").href = "karte.php?" + "SID" + "&mark=1&to=" + ubl;
	document.getElementById("t_enemy").href = "karte.php?" + "SID" + "&mark=2&to=" + ubl;
	document.getElementById("t_nap").href = "karte.php?" + "SID" + "&mark=3&to=" + ubl;
}
// End -->
</script>
';

	$select = "SELECT * FROM `user` WHERE `omni` = '".htmlspecialchars($_GET[id])."' ;";
	$selectResult   = mysql_query($select);
	$row = mysql_fetch_array($selectResult);

	if (!$row['name']){ $row['name'] = "unbewohnt (Taliban-Area)";}

	$to_position = position($_GET['id']);
	$own_pos = ($own_position['x']+($own_position['y']+($own_position['z']*500)));
	$to_pos  = ($to_position['x']+($to_position['y']+($to_position['z']*20)));
	
	if ( $own_pos == $to_pos AND $own_position['y'] != $to_position['y'] ){ $entfernung = 2; }
	elseif ( $own_pos >= $to_pos ) { $entfernung = ($own_pos - $to_pos) ; }
	elseif ( $own_pos <= $to_pos ) { $entfernung = ($to_pos - $own_pos) ; }

	if (!$row['base']){ $row['base'] = 'unbekannt'; }
	if (!$row['clan']){ $row['clan'] = '-'; }
	
	if ( $row['tf_eisen'] == '' ){ $row['tf_eisen'] = '0'; }
	if ( $row['tf_titan'] == '' ){ $row['tf_titan'] = '0'; }
	
	$maxomni = mysql_fetch_array(mysql_query("SELECT `omni` FROM `user` ORDER BY `omni` DESC;"));
	$maxomni = $maxomni['omni'];

	$sectors = explode('.',$maxomni / 500);
	if   ($sectors[1] != 0) {$sectors = $sectors[0]+1;}
	else {$sectors = $sectors[0];}
	
	$i=0;
	do {
		if ($_POST['sector'] == $i) { $sektorselect .= '<option value="'.$i.'" selected>'.$i.'  ('.(($i)*500+1).'-'.(($i+1)*500).')</option>'; }
		else {$sektorselect .= '<option value="'.$i.'">'.$i.'  ('.(($i)*500+1).'-'.(($i+1)*500).')</option>';}
		$i++;
	} while ($i < $sectors);
	$i=0;
	
	$karte.='</table><br />
<table cellspacing="0" style="width:450px;" border="1" class="standard">
	<tr>
		<th align="center">
			<b>Info:</b>
		</th>
		<th align="center">
			<b>Sektor:</b>
		</th>
	</tr>
	<tr align="center">
		<td align="center" style="width:330px;">
			Spieler: <b><font id="name">'.$row['name'].'</font></b> // Clan: <b><font id="clan">'.$row['clan'].'</font></b><br />Basis: <b><font id="base">'.$row['base'].'</font></b> // UBL: <b><font id="ubl">'.$_GET['id'].'</font></b><br />Entfernung: <b><font id="entfernung">'.($entfernung*2.5).'</font></b> Kilometer<br /><i>Tr&uuml;mmerfeld</i> Metall: <b><font id="tf_eisen">'.$row['tf_eisen'].'</font></b> Titan: <b><font id="tf_titan">'.$row['tf_titan'].'</font></b>
		</td>
		<td align="center" style="width:120px;">
			<form enctype="multipart/form-data" action="karte.php?'. SID .'" method="post">
				<select name="sector" style="width:%">
					'.$sektorselect.'
				</select>
				<br />
				<input type="submit" value="wechseln" name="wechseln" style="width:80px" /><br />
				<input type="hidden" value="'.session_id().'" name="'.session_name().'" />
			</form>
			<a onClick="popUp(\'sectorrank.php?sector='.($map['end']/500-1).'&amp;'.SID.'\', 600)"><b>Sektor Top 10</b>
		</td>	
	</tr>
</table>';
	
$karte .= '<br /><table border="1" cellspacing="0" class="karte">
	<tr>
		<th align="center" colspan="25">
			<b>Karte Sektor '.($map['end']/500-1).':</b> (stand: '.date('H:i').' update: %update%'.')
		</th>
	</tr>';
//date('H:i',(date('U')+14400)).
$select = "SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."' LIMIT 1;";
$result = mysql_query($select);
$myclan = mysql_fetch_array($result);
if ($myclan == 0) {$myclan = 'keiner';}
do {
	$map['start']++;
	$col++;

	
	$to_position = position($map['start']);
	$own_pos = ($own_position['x']+($own_position['y']+($own_position['z']*20)));
	$to_pos  = ($to_position['x']+($to_position['y']+($to_position['z']*20)));

	if ( $own_position['x'] > $to_position['x'] ) { $entfernung = $own_position['x'] - $to_position['x']; }
	else { $entfernung = $to_position['x'] - $own_position['x']; }
	
	if ( ( $own_position['y'] + ( $own_position['z'] * 20 ) ) > ( $to_position['y'] + ( $to_position['z'] * 20 ) ) ) { $entfernung += ( $own_position['y'] + ( $own_position['z'] * 20 ) ) - ( $to_position['y'] + ( $to_position['z'] * 20 ) ); }
	else { $entfernung += ( $to_position['y'] + ( $to_position['z'] * 20 ) ) - ( $own_position['y'] + ( $own_position['z'] * 20 ) ); }	
	
	$select = "SELECT * FROM `user` WHERE 1 AND `omni` =".$map['start'].";";
	$result = mysql_query($select);
	$row    = mysql_fetch_array($result);
	
	$select = "SELECT * FROM `clans` WHERE 1 AND `userid` =".$map['start'].";";
	$result = mysql_query($select);
	$clan   = mysql_fetch_array($result);
	
	if ($clan['clanid']) { 
		$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$clan['clanid']."';";
		$result = mysql_query($select);
		$clan   = mysql_fetch_array($result);	
	}
		
	if ($col == 1){$karte .= '<tr>';}
	if ($row['omni'] == $_SESSION['user']['omni']){$status = 'kb';}
	elseif ($row['gesperrt'] > date('U')){$status = 'kr';}
	elseif ($_GET['id'] == $map['start']){$status = 'kr';}
	elseif ($row['omni'] != 0){$status = 'kg';}
	else {$status = 'kg2';}

	$select = "SELECT * FROM `karte` WHERE `id` = '".$_SESSION['user']['omni']."' AND `omni` = '".$map['start']."';";
	$result = mysql_query($select);
	$info   = mysql_fetch_array($result);
	if ($row['omni'] == $_SESSION['user']['omni']) {$status = 'kb';}
	elseif ($myclan['clanid'] == $clan['clanid']) { $status = 'ky'; }
	elseif ($info['type'] == 1) { $status = 'kf'; }
	elseif ($info['type'] == 2) { $status = 'ke'; }
	elseif ($info['type'] == 3) { $status = 'kn'; }
		
	$img = "x.gif";
	
	$points = str_replace('.','',$_SESSION['user']['points']);
	
	if ($row['omni'] != 0 and date('U') - $row['timestamp'] > 2592000){$img = 'I.gif';}
	elseif ($row['omni'] != 0 and date('U') - $row['timestamp'] > 1209600){$img = 'II.gif';}
	
	$tf_ges = $row['tf_eisen'] + $row['tf_titan'];
	if ($tf_ges > 2500) { $img = 't.gif'; }
	
	$check = 1;
	
	if ($row['points'] > 50000 and $points > 50000) {
		$check = 0;
	}

	if ($row['kampfpunkte'] >=  2500){ $img = 'orden1.gif'; }
	if ($row['kampfpunkte'] >=  5000){ $img = 'orden2.gif'; }
	if ($row['kampfpunkte'] >= 10000){ $img = 'orden3.gif'; }
	if ($row['kampfpunkte'] >= 25000){ $img = 'orden4.gif'; }	
	
	if ($row['gesperrt'] >= date('U')){ $img = 'g.gif'; }
	
	if ($row['umzug'] > ( time()-24*3600 ) ){ $img = 'u.gif'; }
	
	if (date('U') - $row['timestamp'] < 1209600 and $check == 1) {
		if ($row['points'] < ( $points / 3 ) ){ $img = 's.gif'; }
		elseif ($points < ( $row['points'] / 3 ) ){ $img = 'S.gif'; }
	}
	
	if ($row['group'] == 1000 ){ $img = 'A.gif'; }
	
	if ($row['name'] == ""){ $row['name'] = '-';}

	if ($map['start'] == $row['omni']){$onclick = 'onClick="t(\''.$map['start'].'\',\''.$row['name'].'\',\''.$clan['tag'].'\',\''.$clan['clanid'].'\',\''.$row['base'].'\',\''.$entfernung.'\',\''.$row['tf_eisen'].'\',\''.$row['tf_titan'].'\');"';}
	else {$onclick = '';}
	// $karte .= '<td class="'.$status.'"><a '.$onclick.' onMouseOver="u(\''.$map['start'].'\',\''.$row['name'].'\',\''.$clan['tag'].'\',\''.$row['base'].'\',\''.$entfernung.'\',\''.$row['tf_eisen'].'\',\''.$row['tf_titan'].'\');"><img src="img/'.$img.'" /></a></td>';
	$karte .= '<td class="'.$status.'" '.$onclick.' onMouseOver="u(\''.$map['start'].'\',\''.$row['name'].'\',\''.$clan['tag'].'\',\''.$row['base'].'\',\''.$entfernung.'\',\''.$row['tf_eisen'].'\',\''.$row['tf_titan'].'\');"><img src="img/'.$img.'"></td>';
	$row = 0;
	if ($col == 25){$karte .= "</tr>"; $col = 0;}
} while ($map['start'] != ($map['end']));


// generierte seite ausgeben
$karte.='</table><br />
<table cellspacing="0" style="width:450px;" border="1" class="standard">
	<tr>
		<th align="center" colspan="2">
			<b>Target:</b>
		</th>
	</tr>
	<tr align="center">
		<td align="center">
<table style="width:450px;" border="0">
	<tr>
		<td align="center">
			Spieler: <b><font id="t_name"> </font></b> // Clan: <b><a id="t_clanlink" href="#"><font id="t_clan"> </font></a></b><br />Basis: <b><font id="t_base"> </font></b> // UBL: <b><font id="t_ubl"> </font></b><br />Entfernung: <b><font id="t_entfernung"> </font></b> Kilometer<br /><i>Tr&uuml;mmerfeld</i> Metall: <b><font id="t_tf_eisen">0</font></b> Titan: <b><font id="t_tf_titan">0</font></b>
		</td>
		<td align="center">
			<a id="t_mission" href="#">Mission</a><br />
			<a id="t_beschuss" href="#">Beschuss</a><br />
			<a id="t_nachricht" href="#">Nachricht</a><br />
			<a id="t_profil" href="#">Profil</a><br />
		</td>	
	</tr>
	<tr align="center">
		<td colspan="2">
			<b>Status: </b><a id="t_neutral" href="#">Neutral</a> / <a id="t_farm" href="#">Farmland</a> / <a id="t_enemy" href="#">Feindlich</a> / <a id="t_nap" href="#">NAP/BND</a>
		</td>
	</tr>
</table>
		</td>
	</tr>
</table>

<br />
<table cellspacing="0" style="width:450px;" border="1" class="standard">
	<tr>
		<th align="center">
			<b>Legende</b>
		</th>
	</tr>
	<tr align="center">
		<td align="center">
			<table border="0">
				<tr align="center" class="standard">
					<td align="center" style="width:15px;background-image:url(img/kb.gif);">
						<img src="img/x.gif" />
					</td>
					<td align="left" >
						&nbsp;Deine Basis&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
					<td align="center" style="width:15px;background-image:url(img/kg.gif);">
						<img src="img/x.gif" />
					</td>
					<td align="left" >
						&nbsp;Fremde Basis
					</td>
				</tr>
				<tr align="center" class="standard">
					<td align="center" style="width:15px;background-image:url(img/kr.gif);">
						<img src="img/g.gif" />
					</td>
					<td align="left" >
						&nbsp;gesperrter Spieler&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
					<td align="center" style="width:15px;background-image:url(img/ky.gif);">
						<img src="img/x.gif" />
					</td>
					<td align="left" >
						&nbsp;Clanmember
					</td>
				</tr>
				<tr align="center" class="standard">
					<td align="center" style="width:15px;background-image:url(img/kg.gif);">
						<img src="img/i.gif" />
					</td>
					<td align="left" >
						&nbsp;2 Wochen inaktiv&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
					<td align="center" style="width:15px;background-image:url(img/kg.gif);">
						<img src="img/I.gif" />
					</td>
					<td align="left" >
						&nbsp;4 Wochen inaktiv
					</td>
				</tr>
				<tr align="center" class="standard">
					<td align="center" style="width:15px;background-image:url(img/karte_farm.gif);">
						<img src="img/x.gif" />
					</td>
					<td align="left" >
						&nbsp;Farmland&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
					<td align="center" style="width:15px;background-image:url(img/karte_nap.gif);">
						<img src="img/x.gif" />
					</td>
					<td align="left" >
						&nbsp;NAP/BND
					</td>
				</tr>
				<tr align="center" class="standard">
					<td align="center" style="width:15px;background-image:url(img/kg.gif);">
						<img src="img/s.gif" />
					</td>
					<td align="left" >
						&nbsp;schwacher Spieler&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
					<td align="center" style="width:15px;background-image:url(img/kg.gif);">
						<img src="img/S.gif" />
					</td>
					<td align="left" >
						&nbsp;starker Spieler
					</td>
				</tr>
<!--
				<tr align="center" class="standard">
					<td align="center" style="width:15px;background-image:url(img/kg.gif);">
						<img src="img/x.gif" />
					</td>
					<td align="left" >
						&nbsp;gesperrter Spieler
					</td>
					<td align="center" style="width:15px;background-image:url(img/kg.gif);">
						<img src="img/u.gif" />
					</td>
					<td align="left" >
						&nbsp;Spieler im Urlaub
					</td>
				</tr>
-->
				<tr align="center" class="standard">
					<td align="center" style="width:15px;background-image:url(img/karte_enemy.gif);">
						<img src="img/x.gif" />
					</td>
					<td align="left" >
						&nbsp;feindlicher Spieler&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					</td>
					<td align="center" style="width:15px;background-image:url(img/kg.gif);">
						<img src="img/t.gif" />
					</td>
					<td align="left" >
						&nbsp;grosses Tr&uuml;mmerfeld
					</td>
				</tr>
				<tr align="center" class="standard">
					<td align="center" style="width:15px;background-image:url(img/kg.gif);">
						<img src="img/u.gif" />
					</td>
					<td align="left"  colspan="3">
						&nbsp;Spieler, der in den letzten 24h umgezogen ist
					</td>
				</tr>
				<tr align="center" class="standard">
					<td align="center" style="width:15px;background-image:url(img/kg.gif);">
						<img src="img/orden1.gif" />
					</td>
					<td align="left" >
						&nbsp;2500+ Kampfpunkte
					</td>
					<td align="center" style="width:15px;background-image:url(img/kg.gif);">
						<img src="img/orden2.gif" />
					</td>
					<td align="left" >
						&nbsp;5000+ Kampfpunkte
					</td>
				</tr>
				<tr align="center" class="standard">
					<td align="center" style="width:15px;background-image:url(img/kg.gif);">
						<img src="img/orden3.gif" />
					</td>
					<td align="left" >
						&nbsp;10000+ Kampfpunkte
					</td>
					<td align="center" style="width:15px;background-image:url(img/kg.gif);">
						<img src="img/orden4.gif" />
					</td>
					<td align="left" >
						&nbsp;25000+ Kampfpunkte
					</td>
				</tr>
				<tr align="center" class="standard">
					<td colspan="4" align="center" >
						<br />(Tr&uuml;mmerfelder k&ouml;nnen erst ab einer<br />gr&ouml;sse von 2500 vom Scanner entdeckt werden.)
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>';
	$file = fopen($path,'w');
	fputs($file, $karte);
	fclose($file);
	$karte = str_replace('SID',SID,$karte);
	$karte = str_replace('%update%',countdown(4*3600),$karte);
	$content .= $karte;
	mysql_query("UPDATE `user` SET `karte` = '".date('U')."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1 ;");
} else {
	$file = @fopen($path, 'r');
	$size = filesize($path);
	$karte = fread($file, $size);
	$karte = str_replace('SID',SID,$karte);
	$karte = str_replace('%update%',countdown(($time['karte']+(4*3600))-date('U')),$karte);
	$content .= $karte;
}

$content   = tag2value("onload",'startCountdown1();',$content);
echo $content.template('footer');
?>
