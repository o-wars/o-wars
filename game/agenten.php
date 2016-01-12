<?php
//////////////////////////////////
// agenten.php                  //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////

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

$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION[user][omni]."';";
$selectResult   = mysql_query($select, $dbh);
$gebaeude = mysql_fetch_array($selectResult);

if ($gebaeude['agentenzentrum'] == 0){
	// html head setzen
	$top = template('head');
	$top = tag2value('onload', $onload, $top);

	// ressourcen berechnen
	$ressis = ressistand($_SESSION[user][omni]);
	$top .= $ressis['html'];

	// generierte seite ausgeben
	die ($top.$content.'<br /><br /><b>Du hast noch kein Agentenzentrum.</b></body></html>'.template('footer'));
}

if ($_GET['kaufen'] == 1){ $buy = buy_agent(); }

// markt kaufen
if ($_GET['markt'] and $_GET['markt'] == number_format($_GET['markt'],0,'','')) {
	$result = mysql_query("SELECT * FROM `forschungen` WHERE `omni` = '".$_SESSION['user']['omni']."';");
	$forschungen = mysql_fetch_array($result);
	$select = "SELECT * FROM `agenten_markt` WHERE `cyborgtechnik` <= '".$forschungen['cyborgtechnik']."' AND `id` = '".$_GET['markt']."' AND `preis` <= '".$ressis['gold']."';";
	$result = mysql_query($select);
	$row    = mysql_fetch_array($result);
	if ($row) {
		$select = "INSERT INTO `agenten_ausruestung` ( `id` , `agent` , `omni` , `name` , `abwehr` , `spionage` , `sabotage` , `diebstahl` , `tarnung` ) VALUES ( '', '0', '".$_SESSION['user']['omni']."', '".$row['name']."', '".$row['abwehr']."', '".$row['spionage']."', '".$row['sabotage']."', '".$row['diebstahl']."', '".$row['tarnung']."' );";
		mysql_query($select);
		$select = "UPDATE `ressis` SET `gold` = '".($ressis[gold]-$row['preis'])."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1;";
		mysql_query($select);		
	}
}

// ausruestung anlegen
if ($_POST['ausruesten'] == 1){
	$select = "SELECT * FROM `agenten_ausruestung` WHERE `id` = '".htmlentities($_POST['item'])."' AND `omni` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$item = mysql_fetch_array($result);
	$select = "SELECT * FROM `agenten_ausruestung` WHERE `agent` = '".htmlentities($_POST['agent'])."' AND `omni` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$item_s = mysql_num_rows($result);	
	$select = "SELECT * FROM `agenten` WHERE `id` = '".htmlentities($_POST['agent'])."' AND `omni` = '".$_SESSION['user']['omni']."';";
	$result = mysql_query($select);
	$agent = mysql_fetch_array($result);
	
	
	if ($item and $agent and $item_s < 3){
		$select = "UPDATE `agenten_ausruestung` SET `agent` = '".$agent['id']."' WHERE `id` = '".$item['id']."' LIMIT 1 ;";
		mysql_query($select);
	}
}

$content .= template('agenten');

$content = tag2value('buy', $buy, $content);

$agent = template('agenten_uebersicht');
/*
if ($_POST['training']){
	$select = "SELECT * FROM `agenten` WHERE `omni` = '".$_SESSION['user']['omni']."' AND `training_dauer` > '".date('U')."';";
	$result = mysql_query($select);	
	if (mysql_num_rows($result)){ 
		die (template('head').$ressis['html'].'<br /><br />Es trainiert bereits ein Agent.'); 
	}
	
	$select = "SELECT * FROM `agenten` WHERE `omni` = '".$_SESSION['user']['omni']."' AND `id` = '".htmlentities($_POST['agent'])."';";
	$result = mysql_query($select);	
	$row = mysql_fetch_array($result);
	
	if ($_POST['dauer'] ==  1){ 
		$dauer = 1*3600; 
		if     ($_POST['training'] == 'abwehr')   { $points = 4; }
		elseif ($_POST['training'] == 'spionage') { $points = 4; }
		elseif ($_POST['training'] == 'sabotage') { $points = 2; }
		elseif ($_POST['training'] == 'diebstahl'){ $points = 1; }
		elseif ($_POST['training'] == 'tarnung')  { $points = 2; }		
	}
	elseif ($_POST['dauer'] == 12){ 
		$dauer = 12*3600; 
		if     ($_POST['training'] == 'abwehr')   { $points = 60; }
		elseif ($_POST['training'] == 'spionage') { $points = 60; }
		elseif ($_POST['training'] == 'sabotage') { $points = 30; }
		elseif ($_POST['training'] == 'diebstahl'){ $points = 15; }
		elseif ($_POST['training'] == 'tarnung')  { $points = 30; }
	}
	elseif ($_POST['dauer'] == 24){ 
		$dauer = 24*3600; 
		if     ($_POST['training'] == 'abwehr')   { $points = 140; }
		elseif ($_POST['training'] == 'spionage') { $points = 140; }
		elseif ($_POST['training'] == 'sabotage') { $points = 70; }
		elseif ($_POST['training'] == 'diebstahl'){ $points = 35; }
		elseif ($_POST['training'] == 'tarnung')  { $points = 70; }		
	}
	elseif ($_POST['dauer'] == 48){ 
		$dauer = 48*3600; 
		if     ($_POST['training'] == 'abwehr')   { $points = 320; }
		elseif ($_POST['training'] == 'spionage') { $points = 320; }
		elseif ($_POST['training'] == 'sabotage') { $points = 160; }
		elseif ($_POST['training'] == 'diebstahl'){ $points =  80; }
		elseif ($_POST['training'] == 'tarnung')  { $points = 160; }		
	}
	else   {
		die (template('head').$ressis['html'].'<br /><br />Falsche Zeitangabe.'); 
	}
	if     ($_POST['training'] == 'abwehr')   { $select = "UPDATE `agenten` SET `training_start` = '".date('U')."', `training_dauer` = '".(date('U')+$dauer)."', `abwehr` = abwehr + ".$points." WHERE `id` = '".htmlentities($_POST['agent'])."' LIMIT 1 ;"; }
	elseif ($_POST['training'] == 'spionage') { $select = "UPDATE `agenten` SET `training_start` = '".date('U')."', `training_dauer` = '".(date('U')+$dauer)."', `spionage` = spionage + ".$points." WHERE `id` = '".htmlentities($_POST['agent'])."' LIMIT 1 ;"; }
	elseif ($_POST['training'] == 'sabotage') { $select = "UPDATE `agenten` SET `training_start` = '".date('U')."', `training_dauer` = '".(date('U')+$dauer)."', `sabotage` = sabotage + ".$points." WHERE `id` = '".htmlentities($_POST['agent'])."' LIMIT 1 ;"; }
	elseif ($_POST['training'] == 'diebstahl'){ $select = "UPDATE `agenten` SET `training_start` = '".date('U')."', `training_dauer` = '".(date('U')+$dauer)."', `diebstahl` = diebstahl + ".$points." WHERE `id` = '".htmlentities($_POST['agent'])."' LIMIT 1 ;"; }
	elseif ($_POST['training'] == 'tarnung')  { $select = "UPDATE `agenten` SET `training_start` = '".date('U')."', `training_dauer` = '".(date('U')+$dauer)."', `tarnung` = tarnung + ".$points." WHERE `id` = '".htmlentities($_POST['agent'])."' LIMIT 1 ;"; }

	mysql_query($select);
}
*/
$select = "SELECT * FROM `agenten` WHERE `omni` = '".$_SESSION['user']['omni']."';";
$result = mysql_query($select);

$content = tag2value('platz', $gebaeude['agentenzentrum']*2-mysql_num_rows($result), $content);

do {
	$row = mysql_fetch_array($result);
	if ($row){
		$table = $agent;
		$table = tag2value('name',      $row['name'], $table);

		$ausruestung = mysql_query("SELECT * FROM `agenten_ausruestung` WHERE `agent` = '".$row['id']."';");
		do {
			$item = mysql_fetch_array($ausruestung);
			if ($item){
				
				if ($item['abwehr'] > 0){ $bonus .= '<font color="green">'.$item['abwehr'].' Abw. </font>'; $row['abwehr'] += $item['abwehr'];}
				if ($item['abwehr'] < 0){ $bonus .= '<font color="red">'.$item['abwehr'].' Abw. </font>'; $row['abwehr'] += $item['abwehr'];}
				if ($item['spionage'] > 0){ $bonus .= '<font color="green">'.$item['spionage'].' Spio. </font>'; $row['spionage'] += $item['spionage'];}
				if ($item['spionage'] < 0){ $bonus .= '<font color="red">'.$item['spionage'].' Spio. </font>'; $row['spionage'] += $item['spionage'];}
				if ($item['sabotage'] > 0){ $bonus .= '<font color="green">'.$item['sabotage'].' Sabo. </font>'; $row['sabotage'] += $item['sabotage'];}
				if ($item['sabotage'] < 0){ $bonus .= '<font color="red">'.$item['sabotage'].' Sabo. </font>'; $row['sabotage'] += $item['sabotage'];}				
				if ($item['diebstahl'] > 0){ $bonus .= '<font color="green">'.$item['diebstahl'].' Diebst. </font>'; $row['diebstahl'] += $item['diebstahl'];}
				if ($item['diebstahl'] < 0){ $bonus .= '<font color="red">'.$item['diebstahl'].' Diebst. </font>'; $row['diebstahl'] += $item['diebstahl'];}				
				if ($item['tarnung'] > 0){ $bonus .= '<font color="green">'.$item['tarnung'].' Tarn. </font>'; $row['tarnung'] += $item['tarnung'];}
				if ($item['tarnung'] < 0){ $bonus .= '<font color="red">'.$item['tarnung'].' Tarn. </font>'; $row['tarnung'] += $item['tarnung'];}				
				
				$stuff .= $item['name'].' '.$bonus.'<br />';
				unset($bonus);
			}
		} while ($item);
		
		if (!$stuff) {$stuff = "keine";}
		
		$table = tag2value('abwehr',    $row['abwehr'], $table);
		$table = tag2value('spionage',  $row['spionage'], $table);
		$table = tag2value('sabotage',  $row['sabotage'], $table);
		$table = tag2value('diebstahl', $row['diebstahl'], $table);
		$table = tag2value('tarnung',   $row['tarnung'], $table);		
		
		if ($row['mission'] == 0){
			$status = 'Der Agent befindet sich in deiner Basis.';
			$status .= ' <a href="agentenmission.php?'.SID.'&amp;id='.$row['id'].'"><b>Mission starten</b></a>'; 
		} else {
			$status = 'Der Agent befindet sich auf einer Mission nach '.$row['missionsziel'].'<br />
			Ankunft: '.date('H:i d.m.y', $row['mission_ankunft']).'<br />
			R&uuml;ckkehr: '.date('H:i d.m.y', $row['mission_return']).'<br />';
		}
		
		$table = tag2value('ausruestung', $stuff, $table);
		$table = tag2value('status',      $status, $table);
		unset($status);
		unset($stuff);
		$agenten .= $table;
	}
} while ($row);

$select = "SELECT * FROM `agenten` WHERE `omni` = '".$_SESSION['user']['omni']."' AND `mission` = '0';";
$result = mysql_query($select);
unset($select);
do {
	$row = mysql_fetch_array($result);
	if ($row){ $select_html .= '<option value="'.$row['id'].'">'.$row['name'].'</option>'; }
} while ($row);

$content = tag2value('agenten',$agenten,$content);


$select = "SELECT * FROM `agenten` WHERE `omni` = '".$_SESSION['user']['omni']."' AND `training_dauer` > '".date('U')."';";
$result = mysql_query($select);	
$row    = mysql_fetch_array($result);
/*
if ($row) {
	$trainiert = template('agenten_trainiert');
	
	$trainiert = tag2value('agent',     $row['name'], $trainiert);
	$trainiert = tag2value('countdown', percentbar( ( $row['training_dauer'] - date('U') ), $row['training_dauer']-$row['training_start'], 205 ), $trainiert);
	
	$content = tag2value('training',$trainiert,$content); 
} else { 
	$content = tag2value('training',template('agenten_training'),$content); 
}
*/
$content = tag2value('select', $select_html, $content);

// ausruestung
$result = mysql_query("SELECT * FROM `forschungen` WHERE `omni` = '".$_SESSION['user']['omni']."';");
$forschungen = mysql_fetch_array($result);

$select = "SELECT * FROM `agenten_ausruestung` WHERE `omni` = '".$_SESSION['user']['omni']."' AND `agent` = '0';";
$result = mysql_query($select);

do {
	$row = mysql_fetch_array($result);
	if ($row) {$select_items .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';}
} while ($row);

if (!$row) {$select_items .= '<option value="0">nichts</option>';}

$content = tag2value('select_stuff', $select_items, $content);

// markt
$result = mysql_query("SELECT * FROM `forschungen` WHERE `omni` = '".$_SESSION['user']['omni']."';");
$forschungen = mysql_fetch_array($result);
$select = "SELECT * FROM `agenten_markt` WHERE `cyborgtechnik` <= '".$forschungen['cyborgtechnik']."' ORDER BY `cyborgtechnik`;";
$result = mysql_query($select);

$items_temp = template('agenten_items');
do {
	$row  = mysql_fetch_array($result);
	if ($row){
		$item = $items_temp;
		$item = tag2value('name', $row['name'], $item);
		$item = tag2value('cyborg', $row['cyborgtechnik'], $item);
		if ($row['abwehr'] < 0){ $row['abwehr'] = '<font color="red">'.$row['abwehr'].'</font>';}
		$item = tag2value('abwehr', $row['abwehr'], $item);
		if ($row['spionage'] < 0){ $row['spionage'] = '<font color="red">'.$row['spionage'].'</font>';}
		$item = tag2value('spionage', $row['spionage'], $item);
		if ($row['sabotage'] < 0){ $row['sabotage'] = '<font color="red">'.$row['sabotage'].'</font>';}
		$item = tag2value('sabotage', $row['sabotage'], $item);
		if ($row['diebstahl'] < 0){ $row['diebstahl'] = '<font color="red">'.$row['diebstahl'].'</font>';}
		$item = tag2value('diebstahl', $row['diebstahl'], $item);
		if ($row['tarnung'] < 0){ $row['tarnung'] = '<font color="red">'.$row['tarnung'].'</font>';}
		$item = tag2value('tarnung', $row['tarnung'], $item);

		$item = tag2value('preis', $row['preis'], $item);
		$item = tag2value('kaufen', '<a href="agenten.php?'.SID.'&amp;markt='.$row['id'].'">kaufen</a>', $item);
		
		$items .= $item;
	}
} while ($row);

$content = tag2value('items',$items,$content);

// html head setzen
$top = template('head');
$top = tag2value('onload', $onload, $top);

// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);
$top .= $status;

// ressourcen berechnen
$ressis = ressistand($_SESSION[user][omni]);
$top .= $ressis['html'];

// generierte seite ausgeben
echo $top.$content.template('footer');

// ---------- - - - -  -  -    - 
function buy_agent(){
	$ressis = ressistand($_SESSION['user']['omni']);
	$dbh = db_connect();
	$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION[user][omni]."';";
	$selectResult   = mysql_query($select, $dbh);
	$gebaeude = mysql_fetch_array($selectResult);
	$select = "SELECT * FROM `agenten` WHERE `omni` = '".$_SESSION['user']['omni']."';";
	$agenten = mysql_query($select);
	$agenten = mysql_num_rows($agenten);
	
	if ( $ressis['gold'] < 100 ) { 
		$result = 'Du hast nicht genug Gold um einen Agenten zu kaufen.'; 
	} elseif ( $gebaeude['agentenzentrum'] * 2 <= $agenten ) {
		$result = 'Du hast nicht genug Platz um einen weiteren Agenten unterzubringen.'; 
	} else {
		$result = 'Du haste einen Agenten f&uuml;r 100 Gold gekauft.';
		$select = "INSERT INTO `agenten` ( `id` , `name` , `omni` , `alive` , `abwehr` , `spionage` , `sabotage` , `diebstahl` , `tarnung` ) VALUES ( '0', '".name()."', '".$_SESSION['user']['omni']."', '1', '0', '0', '0', '0', '0' );";
		mysql_query($select);
		$select = "UPDATE `ressis` SET `gold` = '".($ressis[gold]-100)."' WHERE `omni` = '".$_SESSION[user][omni]."' LIMIT 1 ;";
		mysql_query($select);
	}
	
	return $result;
}

function name() {
	$select = "SELECT * FROM `names_first` WHERE 1;";
	$result = mysql_query($select);
	$row = rand ( 0, (mysql_num_rows($result)-1) );
	mysql_data_seek($result, $row);
	$first = mysql_fetch_array($result);

	$select = "SELECT * FROM `names_last` WHERE 1;";
	$result = mysql_query($select);
	$row = rand ( 0, (mysql_num_rows($result)-1) );
	mysql_data_seek($result, $row);
	$last = mysql_fetch_array($result);

	return $first['first'].' '.$last['last'];
}
?>