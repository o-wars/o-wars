<?php
//////////////////////////////////
// Basisfunktionen              //
//////////////////////////////////
// Letzte Aenderung: 01.11.2005 //
//////////////////////////////////

ob_start('ob_gzhandler');

$_GET  = cleanarray($_GET);
$_POST = cleanarray($_POST);

function cleanarray ($array) {
	// filters evil quotes and replaces em with good ones
	// all for the sake of nice SQL querys :)
	$a = array_keys($array);
	$i=0;
	do {
		if ($array[$a[$i]]) {
			//$array[$a[$i]] = str_replace('"', "``",  $array[$a[$i]]);
			$array[$a[$i]] = str_replace("'", "`",   $array[$a[$i]]);
			// $array[$a[$i]] = str_replace(";", "\\;", $array[$a[$i]]);
			// $array[$a[$i]] = mysql_real_escape_string($array[$a[$i]]);
		}
		$i++;
	} while ($a[$i]);
	return $array;
}

function logincheck() {
	if ($_SESSION['user']['timeout'] < date('U')){
		login_failed();
	}
	if ($_SESSION['user']['name'] == ''){
		login_failed();
	}
	if ($_SESSION['user']['omni'] == ''){
		login_failed();
	}
	if ($_SESSION['user']['ip']   != $_SERVER['REMOTE_ADDR']){
		login_failed();
	}
	if ($_SESSION['user']['browser'] != $_SERVER['HTTP_USER_AGENT']){
		login_failed();
	}
}

function showpage($content, $omni, $onload) {
	$status  = template('playerinfo');
	$status  = tag2value('name', $_SESSION['user']['name'], $status);
	$status  = tag2value('base', $_SESSION['user']['base'], $status);
	$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
	$status  = tag2value('points',$_SESSION['user']['points'], $status);
	$ressis = ressistand($omni);
	$page  = template('head');
	$page  = tag2value("onload",$onload,$page);
	$page .= $status;
	$page .= $ressis['html'];
	$page .= $content;
	$page .= '</body></html>';
	
	echo $page;
}

function einh2ress($type, $anz) {
	include('einheiten_preise.php');
	return ($einh[$type]['eisen'] * $anz)
	+ ($einh[$type]['titan'] * $anz)
	+ ($einh[$type]['oel'] * $anz)
	+ ($einh[$type]['uran'] * $anz)
	+ ($einh[$type]['gold'] * $anz)
	+ ($einh[$type]['chanje'] * $anz * 1000);
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

function ressistand($omni) {
	// datenbank verbindung herstellen
	$dbh = db_connect();
	include 'einheiten_preise.php';

	$select = "SELECT * FROM `ressis` WHERE `omni` = '".$omni."' ;";
	$selectResult   = mysql_query($select);
	$row            = mysql_fetch_array($selectResult);
	
	$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$omni."' ;";
	$selectResult   = mysql_query($select);
	$gebaeude       = mysql_fetch_array($selectResult);	
	
	// checken ob einheiten fertig sind und dann hangar setzen
	$hangar = new_units_check($omni);
	
	do {
		$count++;
		$type = 'einh'.$count;
		$used = $used+($hangar[$type]*$einh[$count]['size']);
	} while ( 15 > $count );
	$free = $gebaeude['hangar'] * 25 - $used;
	
	$result = mysql_query("SELECT * FROM `clans` WHERE `userid` = '".$_SESSION['user']['omni']."';");
	$clans  = mysql_fetch_array($result);
	$members = mysql_num_rows(mysql_query("SELECT * FROM clans WHERE clanid = '".$clans['clanid']."';"));	
	$users   = mysql_num_rows(mysql_query("SELECT * FROM user;"));
	
	$rate = round($members/($users / 100),2);

	if ($rate < 20) {
		
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
	}
	
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
	
	$ressis_template = template(ressis);
	
	$eisen_html = explode('.', $ressis['eisen']);
	$eisen_html = $eisen_html[0];
	$titan_html = explode('.', $ressis['titan']);
	$titan_html = $titan_html[0];
	$oel_html = explode('.', $ressis['oel']);
	$oel_html = $oel_html[0];
	$uran_html = explode('.', $ressis['uran']);
	$uran_html = $uran_html[0];
	$gold_html = explode('.', $ressis['gold']);
	$gold_html = $gold_html[0];
	
	list ($ressis['display_eisen']) = explode('.', $ressis['eisen']);
	list ($ressis['display_titan']) = explode('.', $ressis['titan']);
	list ($ressis['display_oel']) = explode('.', $ressis['oel']);
	list ($ressis['display_uran']) = explode('.', $ressis['uran']);
	list ($ressis['display_gold']) = explode('.', $ressis['gold']);

	
	$ressis_template = tag2value('_eisen', number_format($eisen_html,0), $ressis_template);
	$ressis_template = tag2value('_titan', number_format($titan_html,0), $ressis_template);
	$ressis_template = tag2value('_oel', number_format($oel_html,0), $ressis_template);
	$ressis_template = tag2value('_uran', number_format($uran_html,0), $ressis_template);
	$ressis_template = tag2value('_gold', number_format($gold_html,0), $ressis_template);
	$ressis_template = tag2value('_chanje', number_format($ressis['chanje'],0), $ressis_template);
	$ressis_template = tag2value('_hangar', number_format($ressis['hangar'],0), $ressis_template);
	
	$ressis['html']  = $ressis_template;		
	return $ressis;
}

function spielerstatus() {
	return "<span style='font-size: 12px'>Commander: <b>".$_SESSION[user][name]."</b> // Basis: <b>".$_SESSION[user][base]."</b> // UBL: <b>".$_SESSION[user][omni]."</b> // Punkte: <b>1000</b></span>";
}

function new_units_check($omni){
	$dbh = db_connect();

	include('einheiten_preise.php');
	include('def_preise.php');
	include('raketen_preise.php');
	
	// hangar inhalt
	$select = "SELECT * FROM `hangar` WHERE `omni` = ".($omni).";";
	$result = mysql_query($select);
	$hangar  = mysql_fetch_array($result);

	return $hangar;
}

function db_connect() {
	include 'config.php';
	$dbh = mysql_pconnect($db_host, $db_user, $db_pass)
		or die("<h1>Could not connect</h1><b>Please check your configuration. The DB settings seems to be incorrect");
	mysql_select_db($db_database);
	return ($dbh);
}

function position($omni) {
	$y = $omni / 25 + 1;
	$y = explode(".",$y);
	$y = $y[0];
	$x = $omni - ( ( $y - 1 ) * 25 );

	if ($x == 0){ $x = 25; $y = $y - 1;}

	$zz = $y / 20;

	$z = explode(".",$zz);
	if ($zz != $z[0]){$z = $z[0];}
	else {$z = $zz - 1;}
	if ($z != 0){ $y = $y - 20 * $z;}
	
	$position['x'] = $x;
	$position['y'] = $y;
	$position['z'] = $z;
		
	return $position;
}

function time2str($restsekunden) {
	$tage    = floor($restsekunden/60/60/24);
	$restsekunden = $restsekunden-$tage*60*60*24;
	if ($tage > 0) {
		$tage    = $tage.'T ';
	} else { $tage =''; }
	
	$stunden = floor($restsekunden/60/60);
	$restsekunden = $restsekunden-$stunden*60*60;
	$minuten = $restsekunden/60; // Umrechnung in Minuten
	$ganzzahl = floor($minuten); // Abrunden auf Ganzzahl
	$sekunden2 = $ganzzahl*60; // Rest errechnen
	$restsek = $restsekunden - $sekunden2; // Restsekunden 
	
	$restsek = number_format($restsek, 0, '', '');
	$ganzzahl = str_pad( $ganzzahl, 2, "0", STR_PAD_LEFT);
	$restsek = str_pad( $restsek, 2, "0", STR_PAD_LEFT);
	return $tage.$stunden.':'.$ganzzahl.':'.$restsek;
}

function felder2time($felder) {
	//if ($felder != 0) $time = ($felder * 900) + 3600;
	//else {$time = 0;}
	$time = ($felder * 900) + 3600;
	return number_format($time,0,'','');
}

function mission_position($mission) {
	$dbh = db_connect;
	$select = "SELECT * FROM `missionen` WHERE 1 AND `id` =".$mission.";";
	$result = mysql_query($select);
	$row    = mysql_fetch_array($result);

	if ($row) {
		$restzeit    = $row['ankunft'] - date('U');
		$verstrichen = date('U') - $row['started'];
		$zurueck     = $verstrichen / ( ( $row['ankunft'] - $row['started'] ) / 100 ); 
		if ($zurueck > 100) { $zurueck -= 100; }
	}
	
	return $zurueck;
}

function percentbar ($restzeit, $gesamtzeit, $width=150, $template='gold', $height=20) {
	global $percentbar_number;
	$percentbar_number++;
	//$bar = '<img src="bartemplates/'.$template.'/percentborder_l.gif" height="'.$height.'px" alt="pe" /><img src="bartemplates/'.$template.'/percentdone.gif" height="'.$height.'px" name="percentdone'.$percentbar_number.'" alt="xxx" /><img src="bartemplates/'.$template.'/percenttodo.gif" height="'.$height.'px" name="percenttodo'.$percentbar_number.'" alt="xxx" /><img src="bartemplates/'.$template.'/percentborder_r.gif" height="'.$height.'px" alt="pe" /><b><font style="position:relative;left:-'.($width/2).'px;top:-5px;" class="countdown" id="countdown'.$percentbar_number.'">loading...</font></b>';
	$bar = '<center><img src="bartemplates/'.$template.'/percentborder_l.gif" height="'.$height.'px" alt="pe" /><img src="bartemplates/'.$template.'/percentdone.gif" height="'.$height.'px" name="percentdone'.$percentbar_number.'" alt="xxx" /><img src="bartemplates/'.$template.'/percenttodo.gif" height="'.$height.'px" name="percenttodo'.$percentbar_number.'" alt="xxx" /><img src="bartemplates/'.$template.'/percentborder_r.gif" height="'.$height.'px" alt="pe" /><b><br /><font style="position:relative;left:0px;top:-17px;" class="countdown" id="countdown'.$percentbar_number.'">loading...</font></b></center>';
	$script = "<script type=\"text/javascript\"><!--
          var todozeit".$percentbar_number." = new Number();
          var todozeit".$percentbar_number." =%todozeit%;
		  var restzeit".$percentbar_number." = new Number();
          var restzeit".$percentbar_number." =%restzeit%;
          function startCountdown".$percentbar_number."()
           {
               if((restzeit".$percentbar_number." - 1) >= 0)
                {
                    restzeit".$percentbar_number." = restzeit".$percentbar_number." - 1;
                    var min_count".$percentbar_number." = restzeit".$percentbar_number."/60;
					var pixeldone".$percentbar_number.";
				    pixeldone".$percentbar_number." = ".$width."/100*(100-restzeit".$percentbar_number."/(todozeit".$percentbar_number."/100));
                    min_count".$percentbar_number."=Math.floor(min_count".$percentbar_number.");
                    sec_count".$percentbar_number." = restzeit".$percentbar_number." - (min_count".$percentbar_number."*60);
                     if(min_count".$percentbar_number.">0)
                      {
                          var std_count".$percentbar_number." = min_count".$percentbar_number."/60;
                          std_count".$percentbar_number."=Math.floor(std_count".$percentbar_number.");
                          min_count".$percentbar_number."=min_count".$percentbar_number."-std_count".$percentbar_number."*60;
                      }
                     else { var std_count".$percentbar_number." = 0; }
                     
                     if(min_count".$percentbar_number."<10) { min_angabe".$percentbar_number."='0'+min_count".$percentbar_number."; }
                     else { min_angabe".$percentbar_number."=''+min_count".$percentbar_number."; }
                     if(sec_count".$percentbar_number."<10) { sec_angabe".$percentbar_number."='0'+sec_count".$percentbar_number."; }
                     else { sec_angabe".$percentbar_number."=''+sec_count".$percentbar_number."; }
					  document.getElementById('countdown".$percentbar_number."').firstChild.nodeValue = std_count".$percentbar_number."+':'+min_angabe".$percentbar_number."+':'+sec_angabe".$percentbar_number."+' - '+runde((100-restzeit".$percentbar_number."/(todozeit".$percentbar_number."/100)),2)+'%';
					  document.percentdone".$percentbar_number.".width = Math.round(pixeldone".$percentbar_number.");
					  document.percenttodo".$percentbar_number.".width = ".$width."-Math.round(pixeldone".$percentbar_number.");
                      setTimeout('startCountdown".$percentbar_number."()',986);
                }
                else
                {                   
                    document.getElementById('countdown".$percentbar_number."').firstChild.nodeValue = '0:00:00 - 100.00%';
                    setTimeout('location.reload()',500);
                }
           }
           // End --></script>";

	$script = str_replace("%restzeit%",$restzeit,$script);
	$bar = str_replace("%todozeit%",$gesamtzeit,$script).$bar;
	global $onload;
	$onload .= 'startCountdown'.$percentbar_number.'();';
	return $bar;
}

function template($template) {
	// gets template and replaces template_path
	$path = './templates/standard/'.$template.'.html';
	$template_path = './templates/standard/';
	if ($_SESSION['user']['graphic']) { $style_path = $_SESSION['user']['graphic']; }
	elseif (!$_SESSION['user']['style']) { $style = "standard"; }
	else { $style = $_SESSION['user']['style']; }
	if (!$style_path) {$style_path    = './style/'.$style.'/';}
	$file = @fopen($path, 'r');
	$template = fread($file, filesize($path));

	$template = str_replace('%template_path%', $template_path, $template);
	$template = str_replace('%style_path%', $style_path, $template);

	$template = str_replace('SID', SID, $template);

	return $template;
}

function tag2value($tag, $value, $text) {
		return str_replace('%'.$tag.'%', $value, $text);
}

function countdown ($restzeit) {
	global $percentbar_number;
	$percentbar_number++;
	$bar = '<font id="countdown'.$percentbar_number.'">loading...</font>';
	$script = "<script type=\"text/javascript\"><!--
		  var restzeit".$percentbar_number." = new Number();
          var restzeit".$percentbar_number." =%restzeit%;
          function startCountdown".$percentbar_number."()
           {
               if((restzeit".$percentbar_number." - 1) >= 0)
                {
                    restzeit".$percentbar_number." = restzeit".$percentbar_number." - 1;
                    var min_count".$percentbar_number." = restzeit".$percentbar_number."/60;
                    min_count".$percentbar_number."=Math.floor(min_count".$percentbar_number.");
                    sec_count".$percentbar_number." = restzeit".$percentbar_number." - (min_count".$percentbar_number."*60);
                     if(min_count".$percentbar_number.">0)
                      {
                          var std_count".$percentbar_number." = min_count".$percentbar_number."/60;
                          std_count".$percentbar_number."=Math.floor(std_count".$percentbar_number.");
                          min_count".$percentbar_number."=min_count".$percentbar_number."-std_count".$percentbar_number."*60;
                      }
                     else { var std_count".$percentbar_number." = 0; }
                     
                     if(min_count".$percentbar_number."<10) { min_angabe".$percentbar_number."='0'+min_count".$percentbar_number."; }
                     else { min_angabe".$percentbar_number."=''+min_count".$percentbar_number."; }
                     if(sec_count".$percentbar_number."<10) { sec_angabe".$percentbar_number."='0'+sec_count".$percentbar_number."; }
                     else { sec_angabe".$percentbar_number."=''+sec_count".$percentbar_number."; }
					  document.getElementById('countdown".$percentbar_number."').firstChild.nodeValue = std_count".$percentbar_number."+':'+min_angabe".$percentbar_number."+':'+sec_angabe".$percentbar_number.";
                      setTimeout('startCountdown".$percentbar_number."()',986);
                }
                else
                {                   
                    document.getElementById('countdown".$percentbar_number."').firstChild.nodeValue = '0:00:00';
                }
           }
           // End --></script>";

	$script = str_replace("%restzeit%",$restzeit,$script);
	global $onload;
	$onload .= 'startCountdown'.$percentbar_number.'();';
	return $script.$bar;
}

function countup ($restzeit) {
	global $percentbar_number;
	$percentbar_number++;
	$bar = '<font id="countdown'.$percentbar_number.'">loading...</font>';
	$script = "<script type=\"text/javascript\"><!--
		  var restzeit".$percentbar_number." = new Number();
          var restzeit".$percentbar_number." =%restzeit%;
          function startCountdown".$percentbar_number."()
           {
               if((restzeit".$percentbar_number." - 1) >= 0)
                {
                    restzeit".$percentbar_number." = restzeit".$percentbar_number." + 1;
                    var min_count".$percentbar_number." = restzeit".$percentbar_number."/60;
                    min_count".$percentbar_number."=Math.floor(min_count".$percentbar_number.");
                    sec_count".$percentbar_number." = restzeit".$percentbar_number." - (min_count".$percentbar_number."*60);
                     if(min_count".$percentbar_number.">0)
                      {
                          var std_count".$percentbar_number." = min_count".$percentbar_number."/60;
                          std_count".$percentbar_number."=Math.floor(std_count".$percentbar_number.");
                          min_count".$percentbar_number."=min_count".$percentbar_number."-std_count".$percentbar_number."*60;
                      }
                     else { var std_count".$percentbar_number." = 0; }
                     
                     if(min_count".$percentbar_number."<10) { min_angabe".$percentbar_number."='0'+min_count".$percentbar_number."; }
                     else { min_angabe".$percentbar_number."=''+min_count".$percentbar_number."; }
                     if(sec_count".$percentbar_number."<10) { sec_angabe".$percentbar_number."='0'+sec_count".$percentbar_number."; }
                     else { sec_angabe".$percentbar_number."=''+sec_count".$percentbar_number."; }
					  document.getElementById('countdown".$percentbar_number."').firstChild.nodeValue = std_count".$percentbar_number."+':'+min_angabe".$percentbar_number."+':'+sec_angabe".$percentbar_number.";
                      setTimeout('startCountdown".$percentbar_number."()',986);
                }
                else
                {                   
                    document.getElementById('countdown".$percentbar_number."').firstChild.nodeValue = '0:00:00';
                    setTimeout('location.reload()',500);
                }
           }
           // End --></script>";

	$script = str_replace("%restzeit%",$restzeit,$script);
	global $onload;
	$onload .= 'startCountdown'.$percentbar_number.'();';
	return $script.$bar;
}

function entfernung($from, $to) {
	$to_position  = position($to);
	$own_position = position($from);
	$own_pos = ($own_position['x']+($own_position['y']+($own_position['z']*20)));
	$to_pos  = ($to_position['x']+($to_position['y']+($to_position['z']*20)));

	if ( $own_position['x'] > $to_position['x'] ) { $entfernung = $own_position['x'] - $to_position['x']; }
	else { $entfernung = $to_position['x'] - $own_position['x']; }
	
	if ( ( $own_position['y'] + ( $own_position['z'] * 20 ) ) > ( $to_position['y'] + ( $to_position['z'] * 20 ) ) ) { $entfernung += ( $own_position['y'] + ( $own_position['z'] * 20 ) ) - ( $to_position['y'] + ( $to_position['z'] * 20 ) ); }
	else { $entfernung += ( $to_position['y'] + ( $to_position['z'] * 20 ) ) - ( $own_position['y'] + ( $own_position['z'] * 20 ) ); }	
		
	/* buggy !!!!
	$to_position  = position($to);
	$own_position = position($from);
	$own_pos = ($own_position['x']+($own_position['y']+($own_position['z']*500)));
	$to_pos  = ($to_position['x']+($to_position['y']+($to_position['z']*20)));
	
	if ( $own_pos == $to_pos AND $own_position['y'] != $to_position['y'] ){ $entfernung = 2; }
	elseif ( $own_pos >= $to_pos ) { $entfernung = ($own_pos - $to_pos) ; }
	elseif ( $own_pos <= $to_pos ) { $entfernung = ($to_pos - $own_pos) ; }
	*/
	
	return $entfernung;
}

function deluser($omni) {
	mysql_query("DELETE FROM `user` WHERE `omni` = '".$omni."' LIMIT 1;");
	mysql_query("DELETE FROM `defense` WHERE `omni` = '".$omni."' LIMIT 1;");
	mysql_query("DELETE FROM `forschungen` WHERE `omni` = '".$omni."' LIMIT 1;");
	mysql_query("DELETE FROM `gebauede` WHERE `omni` = '".$omni."' LIMIT 1;");
	mysql_query("DELETE FROM `hangar` WHERE `omni` = '".$omni."' LIMIT 1;");
	mysql_query("DELETE FROM `ressis` WHERE `omni` = '".$omni."' LIMIT 1;");
	mysql_query("DELETE FROM `raketen` WHERE `omni` = '".$omni."' LIMIT 1;");
	mysql_query("DELETE FROM `raumstation` WHERE `omni` = '".$omni."' LIMIT 1;");
	mysql_query("DELETE FROM `stats` WHERE `id` = '".$omni."' LIMIT 1;");
	mysql_query("DELETE FROM `munition` WHERE `id` = '".$omni."';");
	mysql_query("DELETE FROM `berichte` WHERE `to` = '".$omni."';");
	mysql_query("DELETE FROM `nachrichten` WHERE `to` = '".$omni."';");
	mysql_query("DELETE FROM `nachrichten` WHERE `from` = '".$omni."';");
	mysql_query("DELETE FROM `scans` WHERE `userid` = '".$omni."';");
	mysql_query("DELETE FROM `missionen` WHERE `start` = '".$omni."';");
	mysql_query("DELETE FROM `clans` WHERE `userid` = '".$omni."';");
	mysql_query("DELETE FROM `karte` WHERE `id` = '".$omni."' OR `omni` = '".$omni."';");
	mysql_query("DELETE FROM `logins` WHERE `userid` = '".$omni."';");
	mysql_query("DELETE FROM `fabrik` WHERE `omni` = '".$omni."';");
	mysql_query("UPDATE `forum_threads` SET `uid` = '-1' WHERE `uid` = '".$omni."';");
	mysql_query("UPDATE `forum_posts` SET `uid` = '-1' WHERE `uid` = '".$omni."';");
}

/**
* Simple function to replicate PHP 5 behaviour
*/
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
} 
?>