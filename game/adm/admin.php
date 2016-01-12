<?php 
function logincheck() {
	if ($_SESSION['admin'] == ''){
		login_failed();
	}
	if ($_SESSION['adminid'] == ''){
		login_failed();
	}
	if ($_SESSION['ip']   != $_SERVER['REMOTE_ADDR']){
		login_failed();
	}
	if ($_SESSION['browser'] != $_SERVER['HTTP_USER_AGENT']){
		login_failed();
	}
}

function login_failed() {
	session_destroy();
	echo '<html>
	<head>
	<title>O-Wars --- der Online-Krieg </title>
	<link rel="icon" href="favicon.ico" type="image/ico" />
	<link rel="STYLESHEET" type="text/css" href="./templates/standard/stylesheet.css" />
	<meta http-equiv="Refresh" CONTENT="60;URL=index.php?'.SID.'" />
	</head>
	<body>
	<script language="JavaScript" type="text/javascript">
	<!--
	function reload() {
		setTimeout("top.location.href=\'index.php\'",40000);
	}
	reload();
	//  End  -->
	</script>
	<table width="100%" height="80%"><tr><td width="100%" height="100%" align="center" valign="middle">
	<center>
	<table border="1" cellspacing="0" cellpadding="0" style="background-color:#e2e2e2;"><tr><td align="center" valign="middle" width="450px">
	<p style="font-size:14px;">
	<br />
	Session ist ungueltig / bitte neu <a href="index.php" target="_parent">einloggen.</a>
	</p>
	<br /></td></tr></table>
	</center>
	</td></tr></table>
	</body>
	</html>';
	die();
}

function db_connect() {
	include 'config.php';
	$dbh = mysql_pconnect($db_host, $db_user, $db_pass)
		or die("<h1>Could not connect</h1><b>Please check your configuration. The DB settings seems to be incorrect");
	mysql_select_db($db_database, $dbh);
	return ($dbh);
}

function ressistand($omni) {
	// datenbank verbindung herstellen
	$dbh = db_connect();
	include '../einheiten_preise.php';

	$select = "SELECT * FROM `ressis` WHERE `omni` = '".$omni."' ;";
	$selectResult   = mysql_query($select);
	$row            = mysql_fetch_array($selectResult);
	
	$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$omni."' ;";
	$selectResult   = mysql_query($select);
	$gebaeude       = mysql_fetch_array($selectResult);	
	
	// checken ob einheiten fertig sind und dann hangar setzen
	//$hangar = new_units_check($omni);
	
	do {
		$count++;
		$type = 'einh'.$count;
		$used = $used+($hangar[$type]*$einh[$count]['size']);
	} while ( 15 > $count );
	$free = $gebaeude['hangar'] * 25 - $used;
	
	$eisen_bonus = ($gebaeude[eisenmine]*30)/100*($gebaeude['eisenmine']*5);
	$titan_bonus = ($gebaeude[titanmine]*20)/100*($gebaeude['titanmine']*5);
	$oel_bonus   = ($gebaeude[oelpumpe] *25)/100*($gebaeude['oelpumpe'] *5);
	$uran_bonus  = ($gebaeude[uranmine] *12)/100*($gebaeude['uranmine'] *5);

	$e = explode('.',$eisen_bonus);
	$eisen_bonus = $e[0];
	$e = explode('.',$titan_bonus);
	$titan_bonus = $e[0];
	$e = explode('.',$oel_bonus);
	$oel_bonus = $e[0];
	$e = explode('.',$uran_bonus);
	$uran_bonus = $e[0];
	
	// aktuellen ressi stand berechnen
	$eisen = (date('U')-$row['eisentimestamp'])/60/60*(40+($gebaeude['eisenmine']*30)+$eisen_bonus) + $row['eisen'];
	$titan = (date('U')-$row['titantimestamp'])/60/60*(20+($gebaeude['titanmine']*20)+$titan_bonus) + $row['titan'];
	$oel   = (date('U')-$row['oeltimestamp'])/60/60*(32+($gebaeude['oelpumpe']*25)+$oel_bonus)  + $row['oel'];
	$uran  = (date('U')-$row['urantimestamp'])/60/60*($gebaeude['uranmine']*12+$uran_bonus)  + $row['uran'];
	$gold  = (date('U')-$row['goldtimestamp'])/60/60*(4+($gebaeude['eisenmine']+$gebaeude['titanmine']+$gebaeude['oelpumpe']+$gebaeude['uranmine']))  + $row['gold'];
	
	if ($row['ueberlagerbar'] <= date('U')) {
		if (number_format($eisen,0,'','') >= (($gebaeude['rohstofflager'] * 7500)+5000)){ 
			$eisen = (($gebaeude['rohstofflager'] * 7500)+5000); 
			$select = "UPDATE `ressis` SET `eisentimestamp` = '".date("U")."', `eisen` = '".$eisen."' WHERE `omni` = '".$omni."' ;";
			$selectResult   = mysql_query($select);	
		}
		if (number_format($titan,0,'','') >= (($gebaeude['rohstofflager'] * 7500)+5000)){ 
			$titan = (($gebaeude['rohstofflager'] * 7500)+5000); 
			$select = "UPDATE `ressis` SET `titantimestamp` = '".date("U")."', `titan` = '".$titan."' WHERE `omni` = '".$omni."' ;";
			$selectResult   = mysql_query($select);	
		}
		if (number_format($oel,0,'','') >= (($gebaeude['rohstofflager'] * 7500)+5000)){ 
			$oel = (($gebaeude['rohstofflager'] * 7500)+5000); 
			$select = "UPDATE `ressis` SET `oeltimestamp` = '".date("U")."', `oel` = '".$oel."' WHERE `omni` = '".$omni."' ;";
			$selectResult   = mysql_query($select);	
		}
		if (number_format($uran,0,'','') >= (($gebaeude['rohstofflager'] * 7500)+5000)){ 
			$uran = (($gebaeude['rohstofflager'] * 7500)+5000); 
			$select = "UPDATE `ressis` SET `urantimestamp` = '".date("U")."', `uran` = '".$uran."' WHERE `omni` = '".$omni."' ;";
			$selectResult   = mysql_query($select);	
		}
		if (number_format($gold,0,'','') >= (($gebaeude['rohstofflager'] * 7500)+5000)){ 
			$gold = (($gebaeude['rohstofflager'] * 7500)+5000); 
			$select = "UPDATE `ressis` SET `goldtimestamp` = '".date("U")."', `gold` = '".$gold."' WHERE `omni` = '".$omni."' ;";
			$selectResult   = mysql_query($select);	
		}
	}
	// die neuen ressis speichern
	if (number_format($eisen,0,'','') > number_format($row['eisen'],0,'','')){
		$select = "UPDATE `ressis` SET `eisentimestamp` = '".date("U")."', `eisen` = '".$eisen."' WHERE `omni` = '".$omni."' ;";
		$selectResult   = mysql_query($select);	
	}

	if (number_format($titan,0,'','') > number_format($row['titan'],0,'','')){
		$select = "UPDATE `ressis` SET `titantimestamp` = '".date("U")."', `titan` = '".$titan."' WHERE `omni` = '".$omni."' ;";
		$selectResult   = mysql_query($select);	
	}	

	if (number_format($oel,0,'','') > number_format($row['oel'],0,'','')){
		$select = "UPDATE `ressis` SET `oeltimestamp` = '".date("U")."', `oel` = '".$oel."' WHERE `omni` = '".$omni."' ;";
		$selectResult   = mysql_query($select);	
	}		

	if (number_format($uran,0,'','') > number_format($row['uran'],0,'','')){
		$select = "UPDATE `ressis` SET `urantimestamp` = '".date("U")."', `uran` = '".$uran."' WHERE `omni` = '".$omni."' ;";
		$selectResult   = mysql_query($select);	
	}		

	if (number_format($gold,0,'','') > number_format($row['gold'],0,'','')){
		$select = "UPDATE `ressis` SET `goldtimestamp` = '".date("U")."', `gold` = '".$gold."' WHERE `omni` = '".$omni."' ;";
		$selectResult   = mysql_query($select);	
	}	

	// ausgabe
	$ressis['eisen']    = $eisen;
	$ressis['titan']    = $titan;
	$ressis['oel']      = $oel;
	$ressis['uran']     = $uran;
	$ressis['gold']     = $gold;
	$ressis['chanje']   = $row['chanje'];
	$ressis['hangar']   = $free;
	$ressis['ueberlagerbar'] = $row['ueberlagerbar'];
	
	/*
	$ressis_template = template(ressis);
	
	$ressis_template = tag2value('_eisen', number_format($ressis['eisen'],0), $ressis_template);
	$ressis_template = tag2value('_titan', number_format($ressis['titan'],0), $ressis_template);
	$ressis_template = tag2value('_oel', number_format($ressis['oel'],0), $ressis_template);
	$ressis_template = tag2value('_uran', number_format($ressis['uran'],0), $ressis_template);
	$ressis_template = tag2value('_gold', number_format($ressis['gold'],0), $ressis_template);
	$ressis_template = tag2value('_chanje', number_format($ressis['chanje'],0), $ressis_template);
	$ressis_template = tag2value('_hangar', number_format($ressis['hangar'],0), $ressis_template);
	
	$ressis['html']  = $ressis_template;		
	*/
	return $ressis;
}

function template($template) {
	// gets template and replaces template_path
	$path = './templates/'.$template.'.html';
	$template_path = './templates/';
	$file = @fopen($path, 'r');
	$template = fread($file, filesize($path));
	while (strstr($template, '%template_path%')) {
		$template = str_replace('%template_path%', $template_path, $template);
	} 
	while (strstr($template, 'SID')) {
		$template = str_replace('SID', SID, $template);
	} 
	$template = tag2value('date', date('M, d Y H:i:s'), $template);
	return $template;
}

function tag2value($tag, $value, $text) {
		return str_replace('%'.$tag.'%', $value, $text);
}
?>