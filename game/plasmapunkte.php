<?php
//////////////////////////////////
// Einstellungen                //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////
// Kommentare:
// - Status Spieler
// - Passwort aendern
// - basisnamen aendern
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include("functions.php");
// check session
logincheck();

// get html head
$content = template('head');
// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);

// add playerinfo to html
$content .= $status;

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION[user][omni]);
$content .= $ressis['html'];

$dbh = db_connect();
$piece = template('rank_piece');

// pages
$result = mysql_query("SELECT * FROM `user` WHERE `omni` >0 ORDER BY `plasmapunkte` DESC;");

$count  = mysql_num_rows($result)/100;
$count = explode('.',$count);
$c = $count[0];
if ($count[1] != 0) { $c++; }
if ($_GET['page'] == "last") {
	$_GET['page'] = $c;
} elseif (!$_GET['page']) { $_GET['page'] = 1; }

$i = 0;
do {
	$i++;
	if ($_GET['page'] == $i) { $pages .= " <b>[".$i."]</b>"; }
	else { $pages .= ' <a href="plasmapunkte.php?'.SID.'&amp;page='.$i.'">['.$i.']</a>'; }
} while ($c > $i);
$i = $_GET['page'] * 100-100;
$limit = ($_GET['page'] * 100-100).','.(100);

$select = "SELECT * FROM `user` WHERE `omni` >0 ORDER BY `plasmapunkte` DESC LIMIT ".$limit.";";
$result = mysql_query($select);

do {
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	if ($row){
		$select = "SELECT * FROM `clans` WHERE 1 AND `userid` =".$row['omni'].";";
		$result2 = mysql_query($select);
		$clan   = mysql_fetch_array($result2);
	
		if ($clan['clanid']) { 
			$select = "SELECT * FROM `clan_info` WHERE `clanid` = '".$clan['clanid']."';";
			$result2 = mysql_query($select);
			$clan   = mysql_fetch_array($result2);	
		}
		
		$i++;
		if ($row['omni'] == $_SESSION['user']['omni']){
			$newpiece = tag2value('name','<b>'.$row['name'].'</b>', $piece);
			$newpiece = tag2value('rank','<b>'.$i.'</b>', $newpiece);
			$newpiece = tag2value('ubl','<b>'.$row['omni'].'</b>', $newpiece);
			$newpiece = tag2value('base','<b>'.$row['base'].'</b>', $newpiece);
			
			$img = "x.gif";
			if ($row['omni'] != 0 and date('U') - $row['timestamp'] > 2592000){$img = 'I.gif';}
			elseif ($row['omni'] != 0 and date('U') - $row['timestamp'] > 1209600){$img = 'i.gif';}
			$tf_ges = $row['tf_eisen'] + $row['tf_titan'];
			if ($tf_ges > 2500) { $img = 't.gif'; }			
			if ($row['kampfpunkte'] >=  2500){ $img = 'orden1.gif'; }
			if ($row['kampfpunkte'] >=  5000){ $img = 'orden2.gif'; }
			if ($row['kampfpunkte'] >= 10000){ $img = 'orden3.gif'; }
			if ($row['kampfpunkte'] >= 25000){ $img = 'orden4.gif'; }
			if ($row['gesperrt'] >= date('U')){ $img = 'g.gif'; }
			if ($row['umzug'] > ( time()-24*3600 ) ){ $img = 'u.gif'; }
			
			$newpiece = tag2value('status','<img onMouseOver=\'overlib("<b>Tr&uuml;mmerfeld:</b><br />Eisen: '.$row['tf_eisen'].'<br />Titan: '.$row['tf_titan'].'")\' onmouseout="cClick()" src="img/'.$img.'" />', $newpiece);			
			
			if ($clan['clanid']) { 
				$newpiece = tag2value('clan','<b>'.'<a href="claninfo.php?'.SID.'&amp;clan='.$clan['clanid'].'">'.$clan['tag'].'</a></b>', $newpiece);
			} else {
				$newpiece = tag2value('clan','-', $newpiece);
			}
			
			$newpiece = tag2value('points','<b>'.number_format($row['plasmapunkte'],0,'','.').'</b>', $newpiece);
			$ranking .= $newpiece;
		} else {
			$newpiece = tag2value('name',$row['name'], $piece);
			$newpiece = tag2value('rank',$i, $newpiece);
			$newpiece = tag2value('ubl',$row['omni'], $newpiece);
			$newpiece = tag2value('base',$row['base'], $newpiece);
			
			$img = "x.gif";
			if ($row['omni'] != 0 and date('U') - $row['timestamp'] > 2592000){$img = 'I.gif';}
			elseif ($row['omni'] != 0 and date('U') - $row['timestamp'] > 1209600){$img = 'i.gif';}
			$tf_ges = $row['tf_eisen'] + $row['tf_titan'];
			if ($tf_ges > 2500) { $img = 't.gif'; }			
			if ($row['kampfpunkte'] >=  2500){ $img = 'orden1.gif'; }
			if ($row['kampfpunkte'] >=  5000){ $img = 'orden2.gif'; }
			if ($row['kampfpunkte'] >= 10000){ $img = 'orden3.gif'; }
			if ($row['kampfpunkte'] >= 25000){ $img = 'orden4.gif'; }
			if ($row['gesperrt'] >= date('U')){ $img = 'g.gif'; }
			if ($row['umzug'] > ( time()-24*3600 ) ){ $img = 'u.gif'; }
			
			$newpiece = tag2value('status','<img onMouseOver=\'overlib("<b>Tr&uuml;mmerfeld:</b><br />Eisen: '.$row['tf_eisen'].'<br />Titan: '.$row['tf_titan'].'")\' onmouseout="cClick()" src="img/'.$img.'" />', $newpiece);			
			
			if ($clan['clanid']) { 
				$newpiece = tag2value('clan','<a href="claninfo.php?'.SID.'&amp;clan='.$clan['clanid'].'">'.$clan['tag'].'</a>', $newpiece);
			} else {
				$newpiece = tag2value('clan','-', $newpiece);
			}
			$newpiece = tag2value('points',number_format($row['plasmapunkte'],0,'','.'), $newpiece);
			$ranking .= $newpiece;
		}
	}
} while($row);


// get page html
$template .= template('plasmarank');
$template = tag2value('ranking', $ranking, $template);
$content .= $template;
$content = tag2value('pages', $pages, $content);
// send page to browser
$content = str_replace('%onload%', $onload, $content);
echo $content.template('footer');
?>