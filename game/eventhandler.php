<?PHP
@set_time_limit(0);

if ($_SERVER['REMOTE_ADDR']) {die('oooops');}
$next_scores = date('U');
$dbh = db_connect();

// hole timestamps vom letzten durchlauf
if (!file_exists('temp/timestamps/points')) { touch('temp/timestamps/points'); }
else { $next_scores = date('d',filectime('temp/timestamps/points')); }

if (!file_exists('temp/timestamps/markt'))  { touch('temp/timestamps/markt'); }
else { $marktpreise = filectime('temp/timestamps/markt')+6*3600; }

// Evetid | Action
//    1   | Mission
//    2   | Fabrik
//    3   | Gebaeude
//    4   | Forschung
//    5   | Rocket
//    6   | Raumstation Gebaeude

printf("\033[01;33mO-Wars Eventhandler \033[01;32mv.1.1.0\033[00m\n");
do {
	if ( !$dbh ) { $dbh = db_connect(); }
	
	printf("\033[01;33mProcess woke up ! Checking for work.\033[00m\n");
	
	if ($next_scores != date('d')) { 
		printf("\033[01;32mCleaning Clanforum ........... \033[00m"); 
		cleanclanforum();
		printf("\033[01;33mdone\n"); 
		printf("\033[01;32mCleaning DB .................. \033[00m"); 
		mysql_query("UPDATE `user` SET `kampfpunkte` = '0' WHERE `kampfpunkte` < '0';");
		mysql_query("DELETE FROM `berichte` WHERE `timestamp` < '".(date('U')-604800)."';");
		mysql_query("DELETE FROM `user_new` WHERE `time` < '".(date('U')-172800)."';");
		mysql_query("DELETE FROM `berichte` WHERE `to` = '0';");
		mysql_query("DELETE FROM `missionen` WHERE `start` =0 AND `ziel` =0 AND `parsed` =1;");
		mysql_query("DELETE FROM `nachrichten` WHERE `timestamp` < '".(date('U')-604800)."';");
		mysql_query("DELETE FROM `clan_angebote` WHERE `timestamp` < '".(date('U')-604800)."';");
		mysql_query("DELETE FROM `markt` WHERE `date` < '".(date('U')-604800)."';");
		mysql_query("DELETE FROM `clan_info` WHERE `aufgeloest` < '".(date('U')-2419200)."' AND `aufgeloest` != '0';");
		mysql_query("DELETE FROM `clanwars` WHERE `ended` < '".(date('U')-2419200)."' AND `ended` != '0';");
		printf("\033[01;33mdone\n"); 
		printf("\033[01;32mOptimizing DB ................ \033[00m"); 
		mysql_query("OPTIMIZE TABLE `angriffssperre` , `berichte` , `beschuss` , `clan_angebote` , `clan_info` , `clans` , `clanwars` , `defense` , `events` , `fabrik` , `forschungen` , `forum_foren` , `forum_posts` , `forum_threads` , `gebauede` , `hangar` , `karte` , `log` , `logins` , `markt` , `marktpreise` , `missionen` , `munition` , `nachrichten` , `names_first` , `names_last` , `plasmalog` , `raketen` , `raumstation` , `ressis` , `scans` , `stats` , `user` , `user_new` , `werbung`;");
		printf("\033[01;33mdone\n"); 
		printf("\033[01;32mDeleting old Cards ........... \033[00m"); 
		$handle=opendir ('./temp');
		while (false !== ($file = readdir ($handle))) {
			if (preg_match('/^karte.*/',$file)) {
				if (time() - filectime('./temp/'.$file) > 4*3600) {
					unlink('./temp/'.$file);
				}
			}
		}
		printf("\033[01;33mdone\n"); 
		touch('temp/timestamps/points');
		$next_scores = date('d'); 
	}
	
	if ($marktpreise <= date('U')) {
		printf("\033[01;32mCalculating market prices .... \033[00m"); 
		marktpreise();
		printf("\033[01;33mdone\n"); 
		touch('temp/timestamps/markt');
		$marktpreise = date('U')+6*3600;
	}
	
	$select = "SELECT * FROM `events` WHERE `date` <= '".date('U')."' LIMIT 1;";
	$result = mysql_query($select);
	$row    = mysql_fetch_array($result);
	if ($row){
		printf("\033[01;36mProcessing Event #".$row['id']." [Event: ".$row['type']."/".$row['eid']."] [Date: ".date('H:i:s - d.m.y', $row['date'])."]\033[00m\n");
		// eine mission ist angekommen
		if ($row['type'] == 1){ 
			$select2 = "SELECT * FROM `missionen` WHERE `id` = '".$row['eid']."' LIMIT 1;";
			$result2 = mysql_query($select2);
			$row2    = mysql_fetch_array($result2, MYSQL_ASSOC);
			printf("mission from ".$row2['start']." to ".$row2['ziel']."\033[00m\n");
			mission_check($row2['id']);
		}
		
		// eine einheit ist fertig.
		else if ($row['type'] == 2){ 
			$select2 = "SELECT * FROM `fabrik` WHERE `id` = '".$row['eid']."' LIMIT 1;";
			$result2 = mysql_query($select2);
			$row2    = mysql_fetch_array($result2, MYSQL_ASSOC);
			printf("an unit for ".$row2['omni']." is finished\033[00m\n");
			@new_units_check($row2['omni']);
		}

		// ein gebaeude ist fertig.
		else if ($row['type'] == 3){ 
			printf("an building for ".$row['eid']." is finished\033[00m\n");
			ressistand($row['eid']);
			gebaeude($row['eid']);
			ressistand($row['eid']);
		}

		// eine forschung ist fertig.
		else if ($row['type'] == 4){ 
			printf("an science for ".$row['eid']." is finished\033[00m\n");
			forschung($row['eid']);
		}		
		
		// eine rakete ist angekommen
		else if ($row['type'] == 5){ 
			$select2 = "SELECT * FROM `beschuss` WHERE `id` = '".$row['eid']."' LIMIT 1;";
			$result2 = mysql_query($select2);
			$row2    = mysql_fetch_array($result2, MYSQL_ASSOC);
			printf("rocket from ".$row2['start']." to ".$row2['ziel']."\033[00m\n");
			rocket($row2['id']);
		}
		// ein gebaeude auf der raumstation ist fertig
		else if ($row['type'] == 6){ 
			printf("an spacestation building for ".$row['eid']." is finished\033[00m\n");
			raumstation_gebaeude($row['eid']);
		}		
		
		$select3 = "DELETE FROM `events` WHERE `id` = '".$row['id']."' LIMIT 1;";
		mysql_query($select3);
	}
	
	$select = "SELECT * FROM `events` ORDER BY `date` LIMIT 1;";
	$result = mysql_query($select);
	$row    = mysql_fetch_array($result);
	if ($row AND ($row['date'] - date(U)) <= 0){ $sleep = 0; printf("\033[01;36mDamn no sleep again .... \033[00m\n"); }
	elseif ($row AND ($row['date'] - date(U)) <= 15){ $sleep = ($row['date'] - date(U)); printf("\033[01;36mSleeping ".$sleep ." secs .... \033[00m\n"); }
	else { $sleep = 15; printf("\033[01;36mSleeping ".$sleep." secs (next event at ".date('H:i:s - d.m.y',$row['date'])." (in ".($row['date']-time())." sec)).... \033[00m\n"); }
	
	sleep($sleep);
} while(!$end);

// -------------------------------------------
function cleanclanforum(){
	// loescht beitraege die seit 1Woche keine neue Antwort haben
	$result = mysql_query("SELECT * FROM `forum_threads` WHERE `fid` >1000 AND `time` < '".(date('U')-604800*2)."';");
	do {
		$row = mysql_fetch_array($result);
		if ($row){
			mysql_query("DELETE FROM `forum_threads` WHERE `id` = '".$row['id']."';");
			mysql_query("DELETE FROM `forum_posts` WHERE `tid` = '".$row['id']."';");
		}
	} while($row);
}

function marktpreise(){
	// berechnet die marktpreise neu
	$select = "SELECT SUM( eisenmine ) AS ges FROM `gebauede`;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$eisen_gw = (($row['ges']*30)/100*($row['ges']*5)+(40+$row['ges']*30))*2;

	$select = "SELECT SUM( titanmine ) AS ges FROM `gebauede`;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$titan_gw = (($row['ges']*20)/100*($row['ges']*5)+(20+$row['ges']*20))*2;

	$select = "SELECT SUM( oelpumpe ) AS ges FROM `gebauede`;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$oel_gw = (($row['ges']*25)/100*($row['ges']*5)+(32+$row['ges']*25))*2;

	$select = "SELECT SUM( uranmine ) AS ges FROM `gebauede`;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$uran_gw = (($row['ges']*12)/100*($row['ges']*5)+($row['ges']*12))*2;

	$select = "SELECT einheit, SUM(menge) as ges FROM `markt` WHERE `type` = '0' AND `einheit` = '1' GROUP BY einheit;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$ges['eisen'] = $row['ges']+$eisen_gw;

	$select = "SELECT einheit, SUM(menge) as ges FROM `markt` WHERE `type` = '0' AND `einheit` = '2' GROUP BY einheit;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$ges['titan'] = $row['ges']+$titan_gw;

	$select = "SELECT einheit, SUM(menge) as ges FROM `markt` WHERE `type` = '0' AND `einheit` = '3' GROUP BY einheit;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$ges['oel'] = $row['ges']+$oel_gw;

	$select = "SELECT einheit, SUM(menge) as ges FROM `markt` WHERE `type` = '0' AND `einheit` = '4' GROUP BY einheit;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$ges['uran'] = $row['ges']+$uran_gw;

	$select = "SELECT einheit, SUM(menge) as ges FROM `markt` WHERE `type` = '1' AND `einheit` = '1' GROUP BY einheit;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$mges['eisen'] = $row['ges']+$eisen_gw;

	$select = "SELECT einheit, SUM(menge) as ges FROM `markt` WHERE `type` = '1' AND `einheit` = '2' GROUP BY einheit;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$mges['titan'] = $row['ges']+$titan_gw;

	$select = "SELECT einheit, SUM(menge) as ges FROM `markt` WHERE `type` = '1' AND `einheit` = '3' GROUP BY einheit;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$mges['oel'] = $row['ges']+$oel_gw;

	$select = "SELECT einheit, SUM(menge) as ges FROM `markt` WHERE `type` = '1' AND `einheit` = '4' GROUP BY einheit;";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);
	$mges['uran'] = $row['ges']+$uran_gw;

	$ek['eisen'] = number_format(300 / 100 * $ges['eisen'] / (($ges['eisen'] + $mges['eisen'])/100),2);
	$vk['eisen'] = number_format(300 / 100 * $ges['eisen'] / (($ges['eisen'] + $mges['eisen'])/100) / 2,2);
	$ek['titan'] = number_format(400 / 100 * $ges['titan'] / (($ges['titan'] + $mges['titan'])/100),2);
	$vk['titan'] = number_format(400 / 100 * $ges['titan'] / (($ges['titan'] + $mges['titan'])/100) / 2,2);
	$ek['oel']   = number_format(350 / 100 * $ges['oel'] /   (($ges['oel'] + $mges['oel'])/100),2);
	$vk['oel']   = number_format(350 / 100 * $ges['oel'] /   (($ges['oel'] + $mges['oel'])/100) / 2,2);
	$ek['uran']  = number_format(500 / 100 * $ges['uran'] /  (($ges['uran'] + $mges['uran'])/100),2);
	$vk['uran']  = number_format(500 / 100 * $ges['uran'] /  (($ges['uran'] + $mges['uran'])/100) / 2,2);
	
	mysql_query("INSERT INTO `marktpreise` ( `id` , `time` , `ek_eisen` , `ek_oel` , `ek_titan` , `ek_uran` , `vk_eisen` , `vk_oel` , `vk_titan` , `vk_uran` ) VALUES ( '', '".date('U')."', '".$ek['eisen']."', '".$ek['oel']."', '".$ek['titan']."', '".$ek['uran']."', '".$vk['eisen']."', '".$vk['oel']."', '".$vk['titan']."', '".$vk['uran']."' );");

	$bild1 = imagecreatefromgif("img/graph.gif");
	$bild2 = imagecreatefromgif("img/graph.gif");
	$bild3 = imagecreatefromgif("img/graph.gif");
	$bild4 = imagecreatefromgif("img/graph.gif");
	$farbe = imagecolorallocate($bild1, 0, 0, 0);
	$font = imageloadfont('code/addlg10.gdf');

	imagestring($bild1, 10, 30,230, 'Preisentwicklung Eisen '.date('(d.m.Y - H:i)'), $farbe);
	imagestring($bild2, 10, 30,230, 'Preisentwicklung Titan '.date('(d.m.Y - H:i)'), $farbe);
	imagestring($bild3, 10, 40,230, 'Preisentwicklung Oel '.date('(d.m.Y - H:i)'), $farbe);
	imagestring($bild4, 10, 35,230, 'Preisentwicklung Uran '.date('(d.m.Y - H:i)'), $farbe);


	$result = mysql_query("SELECT * FROM `marktpreise` GROUP BY `id` DESC LIMIT 0,42;");
	do {
		$i++;
		$row = mysql_fetch_array($result);
		$y1 = number_format($row['ek_eisen']/2,0,'','');
		$y2 = number_format($row['ek_titan']/2,0,'','');
		$y3 = number_format($row['ek_oel']/2,0,'','');
		$y4 = number_format($row['ek_uran']/2,0,'','');
		if ($oldy1 !='') { 
			imageline($bild1, 420-$i*10, 210-$y1, 420-($i-1)*10, 210-$oldy1,$farbe); 
			imageline($bild2, 420-$i*10, 210-$y2, 420-($i-1)*10, 210-$oldy2,$farbe); 
			imageline($bild3, 420-$i*10, 210-$y3, 420-($i-1)*10, 210-$oldy3,$farbe); 
			imageline($bild4, 420-$i*10, 210-$y4, 420-($i-1)*10, 210-$oldy4,$farbe); 
		}
		$oldy1=$y1;
		$oldy2=$y2;
		$oldy3=$y3;
		$oldy4=$y4;
	} while ($row AND $i < 41);

	imagepng($bild1,'eisenstats.png');
	imagepng($bild2,'titanstats.png');
	imagepng($bild3,'oelstats.png');
	imagepng($bild4,'uranstats.png');
}

function pixel($bild,$x,$y,$farbe) {
	imagesetpixel($bild,$x,$y,$farbe);
	imagesetpixel($bild,$x+1,$y+1,$farbe);
	imagesetpixel($bild,$x+1,$y,$farbe);
	imagesetpixel($bild,$x,$y+1,$farbe);
	imagesetpixel($bild,$x-1,$y-1,$farbe);
	imagesetpixel($bild,$x-1,$y,$farbe);
	imagesetpixel($bild,$x,$y-1,$farbe);	
}

function raumstation_gebaeude($omni) {
	// gebaudepreise:
	include 'raumstation_preise.php';
	
	// mit datenbank verbinden
	$dbh = db_connect();

	$select = "SELECT * FROM `raumstation` WHERE `omni` = '".$omni."';";
	$selectResult   = mysql_query($select);
	$row = mysql_fetch_array($selectResult);
	
	if ($row['nextscanner'] <= date('U') AND $row['nextscanner'] != 0){
		$row['scanner']++;
		mysql_query("UPDATE `raumstation` SET `scanner` = '".($row['scanner'])."' WHERE `omni` = '".$omni."' LIMIT 1;");
		$select = "UPDATE `raumstation` SET `nextscanner` = '0', `nextplasma` = '0' WHERE `omni` = '".$omni."' LIMIT 1;";
		$selectResult   = mysql_query($select);
	}

	if ($row['nextplasma'] <= date('U') AND $row['nextplasma'] != 0){
		$row['plasma']++;
		mysql_query("UPDATE `raumstation` SET `plasma` = '".($row['plasma'])."' WHERE `omni` = '".$omni."' LIMIT 1;");
		$select = "UPDATE `raumstation` SET `nextscanner` = '0', `nextplasma` = '0' WHERE `omni` = '".$omni."' LIMIT 1;";
		$selectResult   = mysql_query($select);
	}
}

function rocket($id) {
	$dbh = db_connect();
	include 'einheiten_preise.php';
	include 'def_preise.php';
	include 'raketen_preise.php';

	$select = "SELECT * FROM `beschuss` WHERE `id` = '".$id."';";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result);	
	$started = $row;
	
	$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$row['start']."';";
	$result = mysql_query($select);
	$gebaeude = mysql_fetch_array($result);	
	
	if ($row['einh1']) { 
		$i = 0; $j = $row['einh1'];
		$chance = $gebaeude['raketensilo']*$rak[1]['treffer'];
		if ($chance < 100) {
			do {
				$i++;
				if (rand(0,100) > $chance) { $row['einh1']--; }
			} while( $i < $j );
		}
	}
	if ($row['einh2']) { 
		$i = 0; $j = $row['einh2'];
		$chance = $gebaeude['raketensilo']*$rak[2]['treffer'];
		if ($chance < 100) {
			do {
				$i++;
				if (rand(0,100) > $chance) { $row['einh2']--; }
			} while( $i < $j );
		}
	}
	if ($row['einh3']) { 
		$i = 0; $j = $row['einh3'];
		$chance = $gebaeude['raketensilo']*$rak[3]['treffer'];
		if ($chance < 100) {
			do {
				$i++;
				if (rand(0,100) > $chance) { $row['einh3']--; }
			} while( $i < $j );
		}
	}
	if ($row['einh4']) { 
		$i = 0; $j = $row['einh4'];
		$chance = $gebaeude['raketensilo']*$rak[4]['treffer'];
		if ($chance < 100) {
			do {
				$i++;
				if (rand(0,100) > $chance) { $row['einh4']--; }
			} while( $i < $j );
		}
	}
	if ($row['einh5']) { 
		$i = 0; $j = $row['einh5'];
		$chance = $gebaeude['raketensilo']*$rak[5]['treffer'];
		if ($chance < 100) {
			do {
				$i++;
				if (rand(0,100) > $chance) { $row['einh5']--; }
			} while( $i < $j );
		}
	}
	
	$angekommen = $row;
	
	if ($row['type'] == 1){
		$select = "SELECT * FROM `hangar` WHERE `omni` = '".$row['ziel']."' LIMIT 1;";
		$result = mysql_query($select);
		$hangar = mysql_fetch_array($result);
		
		$select = "SELECT * FROM `defense` WHERE `omni` = '".$row['ziel']."' LIMIT 1;";
		$result = mysql_query($select);
		$defense = mysql_fetch_array($result);
	} 
	elseif ($row['type'] == 2) {
		$select = "SELECT * FROM `missionen` WHERE `id` = '".$row['ziel']."' LIMIT 1;";
		$result = mysql_query($select);
		$hangar = mysql_fetch_array($result);		
	}
	
		if ($row['einh1'] > 0){
			if ($hangar['einh1'] > 0) {
				if ($hangar['einh1'] > $row['einh1']) {
					$hangar['einh1'] = $hangar['einh1'] - $row['einh1'];
					$treffer .= $row['einh1'].' x '.$einh[1]['name'].'<br />';
					$v[1] += $row['einh1'];
					$row['einh1'] = 0;
				} else {
					$treffer .= $hangar['einh1'].' x '.$einh[1]['name'].'<br />';
					$v[1] += $hangar['einh1'];
					$row['einh1'] = $row['einh1'] - $hangar['einh1'];
					$hangar['einh1'] = 0;
				}
			}
			if ($hangar['einh2'] > 0 and $row['einh1'] > 0) {
				if ($hangar['einh2'] > $row['einh1']) {
					$hangar['einh2'] = $hangar['einh2'] - $row['einh1'];
					$treffer .= $row['einh1'].' x '.$einh[2]['name'].'<br />';
					$v[2] += $row['einh1'];
					$row['einh1'] = 0;
				} else {
					$treffer .= $hangar['einh2'].' x '.$einh[2]['name'].'<br />';
					$row['einh1'] = $row['einh1'] - $hangar['einh2'];
					$v[2] += $hangar['einh2'];
					$hangar['einh2'] = 0;
				}
			}
			if ($hangar['einh3'] > 0 and $row['einh1'] > 0) {
				if ($hangar['einh3'] > $row['einh1']) {
					$hangar['einh3'] = $hangar['einh3'] - $row['einh1'];
					$treffer .= $row['einh1'].' x '.$einh[3]['name'].'<br />';
					$v[3] += $row['einh1'];
					$row['einh1'] = 0;
				} else {
					$treffer .= $hangar['einh3'].' x '.$einh[3]['name'].'<br />';
					$row['einh1'] = $row['einh1'] - $hangar['einh3'];
					$v[3] += $hangar['einh3'];
					$hangar['einh3'] = 0;
				}
			}
/*			if ($hangar['einh4'] > 0 and $row['einh1'] > 0) {
				if ($hangar['einh4'] > $row['einh1']) {
					$hangar['einh4'] = $hangar['einh4'] - $row['einh1'];
					$treffer .= $row['einh1'].' x '.$einh[4]['name'].'<br />';
					$v[4] += $row['einh1'];
					$row['einh1'] = 0;
				} else {
					$treffer .= $hangar['einh4'].' x '.$einh[4]['name'].'<br />';
					$row['einh1'] = $row['einh1'] - $hangar['einh4'];
					$v[4] += $hangar['einh4'];
					$hangar['einh4'] = 0;
				}
			}
*/
			if ($defense['def5'] > 0 and $row['einh1'] > 0) {
				if ($defense['def5'] > $row['einh1']) {
					$defense['def5'] = $defense['def5'] - $row['einh1'];
					$treffer .= $row['einh1'].' x '.$def[5]['name'].'<br />';
					$row['einh1'] = 0;
				} else {
					$treffer .= $defense['def5'].' x '.$def[5]['name'].'<br />';
					$row['einh1'] = $row['einh1'] - $defense['def5'];
					$defense['def5'] = 0;
				}
			}			
			if ($defense['def9'] > 0 and $row['einh1'] > 0) {
				if ($defense['def9'] > $row['einh1']) {
					$defense['def9'] = $defense['def9'] - $row['einh1'];
					$treffer .= $row['einh1'].' x '.$def[9]['name'].'<br />';
					$row['einh1'] = 0;
				} else {
					$treffer .= $defense['def9'].' x '.$def[9]['name'].'<br />';
					$row['einh1'] = $row['einh1'] - $defense['def9'];
					$defense['def9'] = 0;
				}
			}
		}
		
		
		if ($row['einh2'] > 0){
			if ($hangar['einh1'] > 0) {
				if ($hangar['einh1'] > $row['einh2']) {
					$hangar['einh1'] = $hangar['einh1'] - $row['einh2'];
					$treffer .= $row['einh2'].' x '.$einh[1]['name'].'<br />';
					$v[1] += $row['einh2'];
					$row['einh2'] = 0;
				} else {
					$treffer .= $hangar['einh1'].' x '.$einh[1]['name'].'<br />';
					$row['einh2'] = $row['einh2'] - $hangar['einh1'];
					$v[1] += $hangar['einh1'];
					$hangar['einh1'] = 0;
				}
			}
			if ($hangar['einh2'] > 0 and $row['einh2'] > 0) {
				if ($hangar['einh2'] > $row['einh2']) {
					$hangar['einh2'] = $hangar['einh2'] - $row['einh2'];
					$treffer .= $row['einh2'].' x '.$einh[2]['name'].'<br />';
					$v[2] += $row['einh2'];
					$row['einh2'] = 0;
				} else {
					$treffer .= $hangar['einh2'].' x '.$einh[2]['name'].'<br />';
					$row['einh2'] = $row['einh2'] - $hangar['einh2'];
					$v[2] += $hangar['einh2'];
					$hangar['einh2'] = 0;
				}
			}
			if ($hangar['einh3'] > 0 and $row['einh2'] > 0) {
				if ($hangar['einh3'] > $row['einh2']) {
					$hangar['einh3'] = $hangar['einh3'] - $row['einh2'];
					$treffer .= $row['einh2'].' x '.$einh[3]['name'].'<br />';
					$v[3] += $row['einh2'];
					$row['einh2'] = 0;
				} else {
					$treffer .= $hangar['einh3'].' x '.$einh[3]['name'].'<br />';
					$row['einh2'] = $row['einh2'] - $hangar['einh3'];
					$v[3] += $hangar['einh3'];
					$hangar['einh3'] = 0;
				}
			}
			if ($hangar['einh4'] > 0 and $row['einh2'] > 0) {
				if ($hangar['einh4'] > $row['einh2']) {
					$hangar['einh4'] = $hangar['einh4'] - $row['einh2'];
					$treffer .= $row['einh2'].' x '.$einh[4]['name'].'<br />';
					$v[4] += $row['einh2'];
					$row['einh2'] = 0;
				} else {
					$treffer .= $hangar['einh4'].' x '.$einh[4]['name'].'<br />';
					$row['einh2'] = $row['einh2'] - $hangar['einh4'];
					$v[4] += $hangar['einh4'];
					$hangar['einh4'] = 0;
				}
			}
			if ($hangar['einh12'] > 0 and $row['einh2'] > 0) {
				if ($hangar['einh12'] > $row['einh2']) {
					$hangar['einh12'] = $hangar['einh12'] - $row['einh2'];
					$treffer .= $row['einh2'].' x '.$einh[12]['name'].'<br />';
					$v[12] += $row['einh2'];
					$row['einh2'] = 0;
				} else {
					$treffer .= $hangar['einh12'].' x '.$einh[12]['name'].'<br />';
					$row['einh2'] = $row['einh2'] - $hangar['einh12'];
					$v[12] += $hangar['einh12'];
					$hangar['einh12'] = 0;
				}
			}
			if ($defense['def5'] > 0 and $row['einh2'] > 0) {
				if ($defense['def5'] > $row['einh2']) {
					$defense['def5'] = $defense['def5'] - $row['einh2'];
					$treffer .= $row['einh2'].' x '.$def[5]['name'].'<br />';
					$row['einh2'] = 0;
				} else {
					$row['einh2'] - $defense['def5'];
					$treffer .= $defense['def5'].' x '.$def[5]['name'].'<br />';
					$row['einh2'] = $row['einh2'] - $defense['def5'];
					$defense['def5'] = 0;
				}
			}			
			if ($defense['def9'] > 0 and $row['einh2'] > 0) {
				if ($defense['def9'] > $row['einh2']) {
					$defense['def9'] = $defense['def9'] - $row['einh2'];
					$treffer .= $row['einh2'].' x '.$def[9]['name'].'<br />';
					$row['einh2'] = 0;
				} else {
					$row['einh2'] - $defense['def9'];
					$treffer .= $defense['def9'].' x '.$def[9]['name'].'<br />';
					$row['einh2'] = $row['einh2'] - $defense['def9'];
					$defense['def9'] = 0;
				}
			}
			if ($hangar['einh5'] > 0 and $row['einh2'] > 0) {
				if ($hangar['einh5'] > $row['einh2']) {
					$hangar['einh5'] = $hangar['einh5'] - $row['einh2'];
					$treffer .= $row['einh2'].' x '.$einh[5]['name'].'<br />';
					$v[5] += $row['einh2'];
					$row['einh2'] = 0;
				} else {
					$treffer .= $hangar['einh5'].' x '.$einh[5]['name'].'<br />';
					$row['einh2'] = $row['einh2'] - $hangar['einh5'];
					$v[5] += $hangar['einh5'];
					$hangar['einh5'] = 0;
				}
			}
			if ($defense['def6'] > 0 and $row['einh2'] > 0) {
				if ($defense['def6'] > $row['einh2']) {
					$defense['def6'] = $defense['def6'] - $row['einh2'];
					$treffer .= $row['einh2'].' x '.$def[6]['name'].'<br />';
					$row['einh2'] = 0;
				} else {
					$row['einh2'] - $defense['def6'];
					$treffer .= $defense['def6'].' x '.$def[6]['name'].'<br />';
					$row['einh2'] = $row['einh2'] - $defense['def6'];
					$defense['def6'] = 0;
				}
			}
			if ($hangar['einh14'] > 0 and $row['einh2'] > 0) {
				if ($hangar['einh14'] > $row['einh2']) {
					$hangar['einh14'] = $hangar['einh14'] - $row['einh2'];
					$treffer .= $row['einh2'].' x '.$einh[14]['name'].'<br />';
					$v[14] += $row['einh2'];
					$row['einh2'] = 0;
				} else {
					$treffer .= $hangar['einh14'].' x '.$einh[14]['name'].'<br />';
					$row['einh2'] = $row['einh2'] - $hangar['einh14'];
					$v[14] += $hangar['einh14'];
					$hangar['einh14'] = 0;
				}
			}			
		}
		
		
		if ($row['einh3'] > 0){
			if ($hangar['einh1'] > 0) {
				if ($hangar['einh1'] > $row['einh3']) {
					$hangar['einh1'] = $hangar['einh1'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$einh[1]['name'].'<br />';
					$v[1] += $row['einh3'];
					$row['einh3'] = 0;
				} else {
					$treffer .= $hangar['einh1'].' x '.$einh[1]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $hangar['einh1'];
					$v[1] += $hangar['einh1'];
					$hangar['einh1'] = 0;
				}
			}
			if ($hangar['einh2'] > 0 and $row['einh3'] > 0) {
				if ($hangar['einh2'] > $row['einh3']) {
					$hangar['einh2'] = $hangar['einh2'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$einh[2]['name'].'<br />';
					$v[2] += $row['einh3'];
					$row['einh3'] = 0;
				} else {
					$treffer .= $hangar['einh2'].' x '.$einh[2]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $hangar['einh2'];
					$v[2] += $hangar['einh2'];
					$hangar['einh2'] = 0;
				}
			}
			if ($hangar['einh3'] > 0 and $row['einh3'] > 0) {
				if ($hangar['einh3'] > $row['einh3']) {
					$hangar['einh3'] = $hangar['einh3'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$einh[3]['name'].'<br />';
					$v[3] += $row['einh3'];
					$row['einh3'] = 0;
				} else {
					$treffer .= $hangar['einh3'].' x '.$einh[3]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $hangar['einh3'];
					$v[3] += $hangar['einh3'];
					$hangar['einh3'] = 0;
				}
			}
			if ($hangar['einh4'] > 0 and $row['einh3'] > 0) {
				if ($hangar['einh4'] > $row['einh3']) {
					$hangar['einh4'] = $hangar['einh4'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$einh[4]['name'].'<br />';
					$v[4] += $row['einh3'];
					$row['einh3'] = 0;
				} else {
					$treffer .= $hangar['einh4'].' x '.$einh[4]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $hangar['einh4'];
					$v[4] += $hangar['einh4'];
					$hangar['einh4'] = 0;
				}
			}
			if ($hangar['einh12'] > 0 and $row['einh3'] > 0) {
				if ($hangar['einh12'] > $row['einh3']) {
					$hangar['einh12'] = $hangar['einh12'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$einh[12]['name'].'<br />';
					$v[12] += $row['einh3'];
					$row['einh3'] = 0;
				} else {
					$treffer .= $hangar['einh12'].' x '.$einh[12]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $hangar['einh12'];
					$v[12] += $hangar['einh12'];
					$hangar['einh12'] = 0;
				}
			}
			if ($hangar['einh13'] > 0 and $row['einh3'] > 0) {
				if ($hangar['einh13'] > $row['einh3']) {
					$hangar['einh13'] = $hangar['einh13'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$einh[13]['name'].'<br />';
					$v[13] += $row['einh3'];
					$row['einh3'] = 0;
				} else {
					$treffer .= $hangar['einh13'].' x '.$einh[13]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $hangar['einh13'];
					$v[13] += $hangar['einh13'];
					$hangar['einh13'] = 0;
				}
			}
			if ($hangar['einh15'] > 0 and $row['einh3'] > 0) {
				if ($hangar['einh15'] > $row['einh3']) {
					$hangar['einh15'] = $hangar['einh15'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$einh[15]['name'].'<br />';
					$v[15] += $row['einh3'];
					$row['einh3'] = 0;
				} else {
					$treffer .= $hangar['einh15'].' x '.$einh[15]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $hangar['einh15'];
					$v[15] += $hangar['einh15'];
					$hangar['einh15'] = 0;
				}
			}			
			if ($defense['def5'] > 0 and $row['einh3'] > 0) {
				if ($defense['def5'] > $row['einh3']) {
					$defense['def5'] = $defense['def5'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$def[5]['name'].'<br />';
					$row['einh3'] = 0;
				} else {
					$treffer .= $defense['def5'].' x '.$def[5]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $defense['def5'];
					$defense['def5'] = 0;
				}
			}			
			if ($defense['def9'] > 0 and $row['einh3'] > 0) {
				if ($defense['def9'] > $row['einh3']) {
					$defense['def9'] = $defense['def9'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$def[9]['name'].'<br />';
					$row['einh3'] = 0;
				} else {
					$treffer .= $defense['def9'].' x '.$def[9]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $defense['def9'];
					$defense['def9'] = 0;
				}
			}
			if ($defense['def6'] > 0 and $row['einh3'] > 0) {
				if ($defense['def6'] > $row['einh3']) {
					$defense['def6'] = $defense['def6'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$def[6]['name'].'<br />';
					$row['einh3'] = 0;
				} else {
					$treffer .= $defense['def6'].' x '.$def[6]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $defense['def6'];
					$defense['def6'] = 0;
				}
			}
			if ($hangar['einh5'] > 0 and $row['einh3'] > 0) {
				if ($hangar['einh5'] > $row['einh3']) {
					$hangar['einh5'] = $hangar['einh5'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$einh[5]['name'].'<br />';
					$v[5] += $row['einh3'];
					$row['einh3'] = 0;
				} else {
					$treffer .= $hangar['einh5'].' x '.$einh[5]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $hangar['einh5'];
					$v[5] += $hangar['einh5'];
					$hangar['einh5'] = 0;
				}
			}
			if ($hangar['einh6'] > 0 and $row['einh3'] > 0) {
				if ($hangar['einh6'] > $row['einh3']) {
					$hangar['einh6'] = $hangar['einh6'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$einh[6]['name'].'<br />';
					$v[6] += $row['einh3'];
					$row['einh3'] = 0;
				} else {
					$treffer .= $hangar['einh6'].' x '.$einh[6]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $hangar['einh6'];
					$v[6] += $hangar['einh6'];
					$hangar['einh6'] = 0;
				}
			}
			if ($hangar['einh9'] > 0 and $row['einh3'] > 0) {
				if ($hangar['einh9'] > $row['einh3']) {
					$hangar['einh9'] = $hangar['einh9'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$einh[9]['name'].'<br />';
					$v[9] += $row['einh3'];
					$row['einh3'] = 0;
				} else {
					$treffer .= $hangar['einh9'].' x '.$einh[9]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $hangar['einh9'];
					$v[9] += $hangar['einh9'];
					$hangar['einh9'] = 0;
				}
			}
			if ($hangar['einh14'] > 0 and $row['einh3'] > 0) {
				if ($hangar['einh14'] > $row['einh3']) {
					$hangar['einh14'] = $hangar['einh14'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$einh[14]['name'].'<br />';
					$v[14] += $row['einh3'];
					$row['einh3'] = 0;
				} else {
					$treffer .= $hangar['einh14'].' x '.$einh[14]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $hangar['einh14'];
					$v[14] += $hangar['einh14'];
					$hangar['einh14'] = 0;
				}
			}
			if ($defense['def7'] > 0 and $row['einh3'] > 0) {
				if ($defense['def7'] > $row['einh3']) {
					$defense['def7'] = $defense['def7'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$def[7]['name'].'<br />';
					$row['einh3'] = 0;
				} else {
					$treffer .= $defense['def7'].' x '.$def[7]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $defense['def7'];
					$defense['def7'] = 0;
				}
			}
			if ($defense['def8'] > 0 and $row['einh3'] > 0) {
				if ($defense['def8'] > $row['einh3']) {
					$defense['def8'] = $defense['def8'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$def[8]['name'].'<br />';
					$row['einh3'] = 0;
				} else {
					$treffer .= $defense['def8'].' x '.$def[8]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $defense['def8'];
					$defense['def8'] = 0;
				}
			}
			if ($hangar['einh7'] > 0 and $row['einh3'] > 0) {
				if ($hangar['einh7'] > $row['einh3']) {
					$hangar['einh7'] = $hangar['einh7'] - $row['einh3'];
					$treffer .= $row['einh3'].' x '.$einh[7]['name'].'<br />';
					$v[7] += $row['einh3'];
					$row['einh3'] = 0;
				} else {
					$treffer .= $hangar['einh7'].' x '.$einh[7]['name'].'<br />';
					$row['einh3'] = $row['einh3'] - $hangar['einh7'];
					$v[7] += $hangar['einh7'];
					$hangar['einh7'] = 0;
				}
			}			
		}


		if ($row['einh4'] > 0 and $row['einh4'] > 0){
			if ($hangar['einh1'] > 0) {
				if ($hangar['einh1'] > $row['einh4']) {
					$hangar['einh1'] = $hangar['einh1'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$einh[1]['name'].'<br />';
					$v[1] += $row['einh4'];
					$row['einh4'] = 0;
				} else {
					$treffer .= $hangar['einh1'].' x '.$einh[1]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $hangar['einh1'];
					$v[1] += $hangar['einh1'];
					$hangar['einh1'] = 0;
				}
			}
			if ($hangar['einh2'] > 0 and $row['einh4'] > 0) {
				if ($hangar['einh2'] > $row['einh4']) {
					$hangar['einh2'] = $hangar['einh2'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$einh[2]['name'].'<br />';
					$v[2] += $row['einh4'];
					$row['einh4'] = 0;
				} else {
					$treffer .= $hangar['einh2'].' x '.$einh[2]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $hangar['einh2'];
					$v[2] += $hangar['einh2'];
					$hangar['einh2'] = 0;
				}
			}
			if ($hangar['einh3'] > 0 and $row['einh4'] > 0) {
				if ($hangar['einh3'] > $row['einh4']) {
					$hangar['einh3'] = $hangar['einh3'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$einh[3]['name'].'<br />';
					$v[3] += $row['einh4'];
					$row['einh4'] = 0;
				} else {
					$treffer .= $hangar['einh3'].' x '.$einh[3]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $hangar['einh3'];
					$v[3] += $hangar['einh3'];
					$hangar['einh3'] = 0;
				}
			}
			if ($hangar['einh4'] > 0 and $row['einh4'] > 0) {
				if ($hangar['einh4'] > $row['einh4']) {
					$hangar['einh4'] = $hangar['einh4'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$einh[4]['name'].'<br />';
					$v[4] += $row['einh4'];
					$row['einh4'] = 0;
				} else {
					$treffer .= $hangar['einh4'].' x '.$einh[4]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $hangar['einh4'];
					$v[4] += $hangar['einh4'];
					$hangar['einh4'] = 0;
				}
			}
			if ($hangar['einh12'] > 0 and $row['einh4'] > 0) {
				if ($hangar['einh12'] > $row['einh4']) {
					$hangar['einh12'] = $hangar['einh12'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$einh[12]['name'].'<br />';
					$v[12] += $row['einh4'];
					$row['einh4'] = 0;
				} else {
					$treffer .= $hangar['einh12'].' x '.$einh[12]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $hangar['einh12'];
					$v[12] += $hangar['einh12'];
					$hangar['einh12'] = 0;
				}
			}
			if ($hangar['einh13'] > 0 and $row['einh4'] > 0) {
				if ($hangar['einh13'] > $row['einh4']) {
					$hangar['einh13'] = $hangar['einh13'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$einh[13]['name'].'<br />';
					$v[13] += $row['einh4'];
					$row['einh4'] = 0;
				} else {
					$treffer .= $hangar['einh13'].' x '.$einh[13]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $hangar['einh13'];
					$v[13] += $hangar['einh13'];
					$hangar['einh13'] = 0;
				}
			}
			if ($hangar['einh15'] > 0 and $row['einh4'] > 0) {
				if ($hangar['einh15'] > $row['einh4']) {
					$hangar['einh15'] = $hangar['einh15'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$einh[15]['name'].'<br />';
					$v[15] += $row['einh15'];
					$row['einh4'] = 0;
				} else {
					$treffer .= $hangar['einh15'].' x '.$einh[15]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $hangar['einh15'];
					$v[15] += $hangar['einh15'];
					$hangar['einh15'] = 0;
				}
			}
			if ($defense['def5'] > 0 and $row['einh4'] > 0) {
				if ($defense['def5'] > $row['einh4']) {
					$defense['def5'] = $defense['def5'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$def[5]['name'].'<br />';
					$row['einh4'] = 0;
				} else {
					$treffer .= $defense['def5'].' x '.$def[5]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $defense['def5'];
					$defense['def5'] = 0;
				}
			}			
			if ($defense['def9'] > 0 and $row['einh4'] > 0) {
				if ($defense['def9'] > $row['einh4']) {
					$defense['def9'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$def[9]['name'].'<br />';
					$row['einh4'] = 0;
				} else {
					$treffer .= $defense['def9'].' x '.$def[9]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $defense['def9'];
					$defense['def9'] = 0;
				}
			}
			if ($defense['def6'] > 0 and $row['einh4'] > 0) {
				if ($defense['def6'] > $row['einh4']) {
					$defense['def6'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$def[6]['name'].'<br />';
					$row['einh4'] = 0;
				} else {
					$treffer .= $defense['def6'].' x '.$def[6]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $defense['def6'];
					$defense['def6'] = 0;
				}
			}
			if ($hangar['einh5'] > 0 and $row['einh4'] > 0) {
				if ($hangar['einh5'] > $row['einh4']) {
					$hangar['einh5'] = $hangar['einh5'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$einh[5]['name'].'<br />';
					$v[5] += $row['einh4'];
					$row['einh4'] = 0;
				} else {
					$treffer .= $hangar['einh5'].' x '.$einh[5]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $hangar['einh5'];
					$v[5] += $hangar['einh5'];
					$hangar['einh5'] = 0;
				}
			}
			if ($hangar['einh6'] > 0 and $row['einh4'] > 0) {
				if ($hangar['einh6'] > $row['einh4']) {
					$hangar['einh6'] = $hangar['einh6'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$einh[6]['name'].'<br />';
					$v[6] += $row['einh4'];
					$row['einh4'] = 0;
				} else {
					$treffer .= $hangar['einh6'].' x '.$einh[6]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $hangar['einh6'];
					$v[6] += $hangar['einh6'];
					$hangar['einh6'] = 0;
				}
			}
			if ($hangar['einh9'] > 0 and $row['einh4'] > 0) {
				if ($hangar['einh9'] > $row['einh4']) {
					$hangar['einh9'] = $hangar['einh9'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$einh[9]['name'].'<br />';
					$v[9] += $row['einh4'];
					$row['einh4'] = 0;
				} else {
					$treffer .= $hangar['einh9'].' x '.$einh[9]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $hangar['einh9'];
					$v[9] += $hangar['einh9'];
					$hangar['einh9'] = 0;
				}
			}
			if ($hangar['einh14'] > 0 and $row['einh4'] > 0) {
				if ($hangar['einh14'] > $row['einh4']) {
					$hangar['einh14'] = $hangar['einh14'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$einh[14]['name'].'<br />';
					$v[14] += $row['einh4'];
					$row['einh4'] = 0;
				} else {
					$treffer .= $hangar['einh14'].' x '.$einh[14]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $hangar['einh14'];
					$v[14] += $hangar['einh14'];
					$hangar['einh14'] = 0;
				}
			}
			if ($defense['def7'] > 0 and $row['einh4'] > 0) {
				if ($defense['def7'] > $row['einh4']) {
					$defense['def7'] = $defense['def7'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$def[7]['name'].'<br />';
					$row['einh4'] = 0;
				} else {
					$treffer .= $defense['def7'].' x '.$def[7]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $defense['def7'];
					$defense['def7'] = 0;
				}
			}
			if ($defense['def8'] > 0 and $row['einh4'] > 0) {
				if ($defense['def8'] > $row['einh4']) {
					$defense['def8'] = $defense['def8'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$def[8]['name'].'<br />';
					$row['einh4'] = 0;
				} else {
					$treffer .= $defense['def8'].' x '.$def[8]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $defense['def8'];
					$defense['def8'] = 0;
				}
			}
			if ($hangar['einh7'] > 0 and $row['einh4'] > 0) {
				if ($hangar['einh7'] > $row['einh4']) {
					$hangar['einh7'] = $hangar['einh7'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$einh[7]['name'].'<br />';
					$v[7] += $row['einh4'];
					$row['einh4'] = 0;
				} else {
					$treffer .= $hangar['einh7'].' x '.$einh[7]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $hangar['einh7'];
					$v[7] += $hangar['einh7'];
					$hangar['einh7'] = 0;
				}
			}
			if ($defense['def10'] > 0 and $row['einh4'] > 0) {
				if ($defense['def10'] > $row['einh4']) {
					$defense['def10'] = $defense['def10'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$def[10]['name'].'<br />';
					$row['einh4'] = 0;
				} else {
					$treffer .= $defense['def10'].' x '.$def[10]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $defense['def10'];
					$defense['def10'] = 0;
				}
			}			
			if ($hangar['einh8'] > 0 and $row['einh4'] > 0) {
				if ($hangar['einh8'] > $row['einh4']) {
					$hangar['einh8'] = $hangar['einh8'] - $row['einh4'];
					$treffer .= $row['einh4'].' x '.$einh[8]['name'].'<br />';
					$v[8] += $row['einh4'];
					$row['einh4'] = 0;
				} else {
					$treffer .= $hangar['einh8'].' x '.$einh[8]['name'].'<br />';
					$row['einh4'] = $row['einh4'] - $hangar['einh8'];
					$v[8] += $hangar['einh8'];
					$hangar['einh8'] = 0;
				}
			}
		}
			
		if ($row['einh5'] > 0){
			if ($hangar['einh10'] > 0 and $row['einh5'] > 0) {
				if ($hangar['einh10'] > $row['einh5']) {
					$hangar['einh10'] = $hangar['einh10'] - $row['einh5'];
					$treffer .= $row['einh5'].' x '.$einh[10]['name'].'<br />';
					$v[10] += $row['einh5'];
					$row['einh5'] = 0;
				} else {
					$treffer .= $hangar['einh10'].' x '.$einh[10]['name'].'<br />';
					$row['einh5'] = $row['einh5'] - $hangar['einh10'];
					$v[10] += $hangar['einh10'];
					$hangar['einh10'] = 0;
				}
			}
			if ($hangar['einh11'] > 0 and $row['einh5'] > 0) {
				if ($hangar['einh11'] > $row['einh5']) {
					$hangar['einh11'] = $hangar['einh11'] - $row['einh5'];
					$treffer .= $row['einh5'].' x '.$einh[11]['name'].'<br />';
					$v[11] += $row['einh5'];
					$row['einh5'] = 0;
				} else {
					$treffer .= $hangar['einh11'].' x '.$einh[11]['name'].'<br />';
					$row['einh5'] = $row['einh5'] - $hangar['einh11'];
					$v[11] += $hangar['einh11'];
					$hangar['einh11'] = 0;
				}
			}
		}

		if ($row['einh6'] > 0 and $row['type'] == 1){
			$ressis = ressistand($row['ziel']);
			do {
				$einheit++;
				if ($hangar['einh'.$einheit]){ $units .= $hangar['einh'.$einheit].' x '.$einh[$einheit]['name'].'<br />'; }
			} while ($einheit < 15);
			
			$einheit = 4;
			do {
				$einheit++;
				if ($defense['def'.$einheit]){ $units .= $defense['def'.$einheit].' x '.$def[$einheit]['name'].'<br />'; }
			} while ($einheit < 10);
			if (!$hangar){ $spio = "<br /><b>Spionage:</b><br />Die Rakete(n) konnte(n) kein Ziel entdecken."; }
			else { $spio = '<br /><b>Spionage:</b><br />Eisen: '.number_format($ressis['eisen'],0,',','.').' <br />Titan: '.number_format($ressis['titan'],0,',','.').'<br />Oel: '.number_format($ressis['oel'],0,',','.').'<br />Uran: '.number_format($ressis['uran'],0,',','.').' <br />Gold: '.number_format($ressis['gold'],0,',','.').'<br />'.$units; }
		}
		
		if ($row['einh6'] > 0 and $row['type'] == 2){
			do {
				$einheit++;
				if ($hangar['einh'.$einheit]){ $units .= $hangar['einh'.$einheit].' x '.$einh[$einheit]['name'].'<br />'; }
			} while ($einheit < 15);
			$spio = '<br /><b>Spionage:</b><br />'.$units;
		}
		
		
		
		
		if ($row['type'] == 1){
				$select = "UPDATE `hangar` SET `einh1` = '".$hangar['einh1']."', `einh2` = '".$hangar['einh2']."', `einh3` = '".$hangar['einh3']."', `einh4` = '".$hangar['einh4']."', `einh5` = '".$hangar['einh5']."', `einh6` = '".$hangar['einh6']."', `einh7` = '".$hangar['einh7']."',  `einh8` = '".$hangar['einh8']."', `einh9` = '".$hangar['einh9']."', `einh10` = '".$hangar['einh10']."',  `einh11` = '".$hangar['einh11']."', `einh12` = '".$hangar['einh12']."', `einh13` = '".$hangar['einh13']."', `einh14` = '".$hangar['einh14']."', `einh15` = '".$hangar['einh15']."' WHERE `omni` = '".$row['ziel']."' LIMIT 1;";
				mysql_query($select);
				$select = "UPDATE `defense` SET `def1` = '".$defense['def1']."', `def2` = '".$defense['def2']."', `def3` = '".$defense['def3']."', `def4` = '".$defense['def4']."', `def5` = '".$defense['def5']."', `def6` = '".$defense['def6']."', `def7` = '".$defense['def7']."', `def8` = '".$defense['def8']."', `def9` = '".$defense['def9']."', `def10` = '".$defense['def10']."' WHERE `omni` = '".$row['ziel']."' LIMIT 1;";
				mysql_query($select);
				$type = 'Basis '.$row['ziel'];
		} elseif ($row['type'] == 2) {
				$anzahl = $hangar['einh1']+$hangar['einh2']+$hangar['einh3']+$hangar['einh4']+$hangar['einh5']+$hangar['einh6']+$hangar['einh7']+$hangar['einh8']+$hangar['einh9']+$hangar['einh10']+$hangar['einh11']+$hangar['einh12']+$hangar['einh13']+$hangar['einh14']+$hangar['einh15'];
				if ($anzahl == 0){
					$select = "DELETE FROM `missionen` WHERE `id` = '".$row['ziel']."' LIMIT 1;";
				}
				else {
					$select = "UPDATE `missionen` SET `einh1` = '".$hangar['einh1']."', `einh2` = '".$hangar['einh2']."', `einh3` = '".$hangar['einh3']."', `einh4` = '".$hangar['einh4']."', `einh5` = '".$hangar['einh5']."', `einh6` = '".$hangar['einh6']."', `einh7` = '".$hangar['einh7']."',  `einh8` = '".$hangar['einh8']."', `einh9` = '".$hangar['einh9']."', `einh10` = '".$hangar['einh10']."',  `einh11` = '".$hangar['einh11']."', `einh12` = '".$hangar['einh12']."', `einh13` = '".$hangar['einh13']."', `einh14` = '".$hangar['einh14']."', `einh15` = '".$hangar['einh15']."' WHERE `id` = '".$row['ziel']."' LIMIT 1;";
				}
				mysql_query($select);
				$type = 'Mission '.$row['ziel'];
				$row['ziel'] = $hangar['start'];
		}
		
		if ($started['einh1']) { $raketen .= $angekommen['einh1'].'/'.$started['einh1'].' '.$rak[1]['name'].'<br />'; }
		if ($started['einh2']) { $raketen .= $angekommen['einh2'].'/'.$started['einh2'].' '.$rak[2]['name'].'<br />'; }
		if ($started['einh3']) { $raketen .= $angekommen['einh3'].'/'.$started['einh3'].' '.$rak[3]['name'].'<br />'; }
		if ($started['einh4']) { $raketen .= $angekommen['einh4'].'/'.$started['einh4'].' '.$rak[4]['name'].'<br />'; }
		if ($started['einh5']) { $raketen .= $angekommen['einh5'].'/'.$started['einh5'].' '.$rak[5]['name'].'<br />'; }
		if ($started['einh6']) { $raketen .= $angekommen['einh6'].'/'.$started['einh6'].' '.$rak[6]['name'].'<br />'; }
		
		$select = "DELETE FROM `beschuss` WHERE `id` = '".$id."' LIMIT 1;";
		mysql_query($select);	

		if (!$treffer) { $treffer = "Es wurde nichts zerst&ouml;rt.<br />"; }
		
		$message =  '<b><i>Es wurde die '.$type.' beschossen:</i></b><br /><br /><b>Raketen:</b> (angekommen/gestartet)<br />'.$raketen.'<br /><b>Verluste:</b><br />'.$treffer;
		$message = str_replace('\'',"\\\"", $message);
		
		$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Raketensilo', '".$row['ziel']."', '".$row['ankunft']."', '0', 'Raketenbeschuss von ".$row['start']."', '".$message."' );";
		mysql_query($select);	
		$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Raketensilo', '".$row['start']."', '".$row['ankunft']."', '0', 'Raketenbeschuss auf ".$row['ziel']."', '".$message.$spio."' );";
		mysql_query($select);	

		// supporterstats
		$i=0;
		do {
			$i++;
			if (!$v[$i]){$v[$i]=0;}
		} while ($i<15);

		$select = "UPDATE `stats` SET `dr1` = dr1 + ".$v[1].", `dr2` = dr2 + ".$v[2].", `dr3` = dr3 + ".$v[3].", `dr4` = dr4 + ".$v[4].", `dr5` = dr5 + ".$v[5].", `dr6` = dr6 + ".$v[6].", `dr7` = dr7 + ".$v[7].", `dr8` = dr8 + ".$v[8].", `dr9` = dr9 + ".$v[9].", `dr10` = dr10 + ".$v[10].", `dr11` = dr11 + ".$v[11].", `dr12` = dr12 + ".$v[12].", `dr13` = dr13 + ".$v[13].", `dr14` = dr14 + ".$v[14].", `dr15` = dr15 + ".$v[15]." WHERE `id` = ".$row['start'].";";
		mysql_query($select);	

		$select = "UPDATE `stats` SET `vr1` = vr1 + ".$v[1].", `vr2` = vr2 + ".$v[2].", `vr3` = vr3 + ".$v[3].", `vr4` = vr4 + ".$v[4].", `vr5` = vr5 + ".$v[5].", `vr6` = vr6 + ".$v[6].", `vr7` = vr7 + ".$v[7].", `vr8` = vr8 + ".$v[8].", `vr9` = vr9 + ".$v[9].", `vr10` = vr10 + ".$v[10].", `vr11` = vr11 + ".$v[11].", `vr12` = vr12 + ".$v[12].", `vr13` = vr13 + ".$v[13].", `vr14` = vr14 + ".$v[14].", `vr15` = vr15 + ".$v[15]." WHERE `id` = ".$row['ziel'].";";
		mysql_query($select);	
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
	
	if ($rate < 15) {
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
	if (number_format($eisen,0,'','') != number_format($row['eisen'],0,'','')){
		$select = "UPDATE `ressis` SET `eisentimestamp` = '".date("U")."', `eisen` = '".$eisen."' WHERE `omni` = '".$omni."' ;";
		$selectResult   = mysql_query($select);	
	}

	if (number_format($titan,0,'','') != number_format($row['titan'],0,'','')){
		$select = "UPDATE `ressis` SET `titantimestamp` = '".date("U")."', `titan` = '".$titan."' WHERE `omni` = '".$omni."' ;";
		$selectResult   = mysql_query($select);	
	}	

	if (number_format($oel,0,'','') != number_format($row['oel'],0,'','')){
		$select = "UPDATE `ressis` SET `oeltimestamp` = '".date("U")."', `oel` = '".$oel."' WHERE `omni` = '".$omni."' ;";
		$selectResult   = mysql_query($select);	
	}		

	if (number_format($uran,0,'','') != number_format($row['uran'],0,'','')){
		$select = "UPDATE `ressis` SET `urantimestamp` = '".date("U")."', `uran` = '".$uran."' WHERE `omni` = '".$omni."' ;";
		$selectResult   = mysql_query($select);	
	}		

	if (number_format($gold,0,'','') != number_format($row['gold'],0,'','')){
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
	
/*	$ressis_template = template(ressis);
	$ressis_template = tag2value('_eisen', number_format($ressis['eisen'],0), $ressis_template);
	$ressis_template = tag2value('_titan', number_format($ressis['titan'],0), $ressis_template);
	$ressis_template = tag2value('_oel', number_format($ressis['oel'],0), $ressis_template);
	$ressis_template = tag2value('_uran', number_format($ressis['uran'],0), $ressis_template);
	$ressis_template = tag2value('_gold', number_format($ressis['gold'],0), $ressis_template);
	$ressis_template = tag2value('_chanje', number_format($ressis['chanje'],0), $ressis_template);
	$ressis_template = tag2value('_hangar', number_format($ressis['hangar'],0), $ressis_template);
*/	
	$ressis['html']    = $ressis_template;
	return $ressis;
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

function mission_check($id){
	$dbh = db_connect();
	include 'einheiten_preise.php';
	// eigene missionen
	$select = "SELECT * FROM `missionen` WHERE `id` = '".$id."';";
	$result = mysql_query($select);
	$row = mysql_fetch_array($result, MYSQL_ASSOC);	
	
	if ($row['parsed'] == 0) {
		if ($row){
			if ($row['type'] == 1) { 
				// angriff
				kampf($row['id']); 
			}
			elseif ($row['type'] == 2) { 
				// transport
				$select = "SELECT * FROM `user` WHERE `omni` = '".$row['ziel']."';";
				$rows = mysql_query($select);
				$target = mysql_fetch_array($rows);
				if ($target and $target['group'] != 1000){
					$ressis = ressistand($row['ziel']);
					$select = "UPDATE `ressis` SET `eisen` = eisen+".$row['eisen'].",`titan` = titan+".$row['titan'].",`oel` = oel+".$row['oel'].",`uran` = uran+".$row['uran'].",`gold` = gold+".$row['gold'].",`chanje` = chanje+".$row['chanje']." WHERE `omni` = '".$row['ziel']."' LIMIT 1;";
					$ressiupdate = mysql_query($select);
					$select = "UPDATE `missionen` SET `eisen` = '0',`titan` = '0',`oel` = '0',`uran` = '0',`gold` = '0',`chanje` = '0' WHERE `id` = '".$row['id']."' LIMIT 1;";
					mysql_query($select);
					$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Missionszentrum', '".$row['ziel']."', '".$row['ankunft']."', '0', 'Rohstofflieferung von ".$row['start']."', '".$row['start']." hat dir<br />".$row['eisen']." Eisen<br />".$row['titan']." Titan<br />  ".$row['oel']." Oel<br />".$row['uran']." Uran<br />".$row['gold']." Gold<br />".$row['chanje']." Chanje<br />geliefert.' );";
					mysql_query($select);	
					$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Missionszentrum', '".$row['start']."', '".$row['ankunft']."', '0', 'Transport zu ".$row['ziel']."', 'Du hast an die Basis bei ".$row['ziel']."<br />".$row['eisen']." Eisen<br />".$row['titan']." Titan<br />  ".$row['oel']." Oel<br />".$row['uran']." Uran<br />".$row['gold']." Gold<br />".$row['chanje']." Chanje<br />geliefert.' );";
					mysql_query($select);	
					
					$handel = mysql_query("SELECT * FROM `handel` WHERE `p1` = '".$row['start']."' AND `p2` = '".$row['ziel']."' LIMIT 1;");
					$handel = @mysql_fetch_array($handel);
					if ($handel) {
						mysql_query("UPDATE `handel` SET `s1` = s1-(".($row['eisen']+$row['titan']+$row['oel']+$row['uran']+$row['gold']+$row['chanje']*1000)."), `s2` = s2+(".($row['eisen']+$row['titan']+$row['oel']+$row['uran']+$row['gold']+$row['chanje']*1000).") WHERE `id` = '".$handel['id']."' LIMIT 1;");
					} else {
						$handel = mysql_query("SELECT * FROM `handel` WHERE `p2` = '".$row['start']."' AND `p1` = '".$row['ziel']."' LIMIT 1;");
						$handel = @mysql_fetch_array($handel);						
						if ($handel) {
							mysql_query("UPDATE `handel` SET `s1` = s1+(".($row['eisen']+$row['titan']+$row['oel']+$row['uran']+$row['gold']+$row['chanje']*1000)."), `s2` = s2-(".($row['eisen']+$row['titan']+$row['oel']+$row['uran']+$row['gold']+$row['chanje']*1000).") WHERE `id` = '".$handel['id']."' LIMIT 1;");
						} else {
							mysql_query("INSERT INTO `handel` ( `id` , `p1` , `p2` , `s1` , `s2` ) VALUES ( '', '".$row['start']."', '".$row['ziel']."', '".($row['eisen']+$row['titan']+$row['oel']+$row['uran']+$row['gold']+$row['chanje']*1000)."', '-".($row['eisen']+$row['titan']+$row['oel']+$row['uran']+$row['gold']+$row['chanje']*1000)."' );");
						}
					}
					
				} else {
					$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Missionszentrum', '".$row['start']."', '".$row['ankunft']."', '0', 'Transport zu ".$row['ziel']."', 'Du hast keine Basis bei ".$row['ziel']." vorgefunden.<br />Deine Einheiten kehren incl. Ladung zur&uuml;ck.' );";
					mysql_query($select);	
				} 
			} elseif ($row['type'] == 3) { 
				// ueberfuehrung
				$select = "SELECT * FROM `user` WHERE `omni` = '".$row['ziel']."';";
				$rows = mysql_query($select);
				$target = mysql_fetch_array($rows);
				if ($target and $target['group'] != 1000){
					$ressis = ressistand($row['ziel']);
					$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] + $row['eisen'])."',`titan` = '".($ressis['titan'] + $row['titan'])."',`oel` = '".($ressis['oel'] + $row['oel'])."',`uran` = '".($ressis['uran'] + $row['uran'])."',`gold` = '".($ressis['gold'] + $row['gold'])."',`chanje` = '".($ressis['chanje'] + $row['chanje'])."' WHERE `omni` = '".$row['ziel']."' LIMIT 1;";
					$ressiupdate = mysql_query($select);
					
					$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$row['ziel']."';";
					$selectResult   = mysql_query($select);
					$gebaeude       = mysql_fetch_array($selectResult);	
	
					// checken ob einheiten fertig sind und dann hangar setzen
					$hangar = new_units_check($row['ziel']);

					$count = 0;
					do {
						$count++;
						$type = 'einh'.$count;
						$kosten += einh2ress($count, $row[$type]);
					} while ( $count < 15 );	
									
					if ($row['einh1']) { $units .= $row['einh1'].' '.$einh[1]['name'].'<br />';}
					if ($row['einh2']) { $units .= $row['einh2'].' '.$einh[2]['name'].'<br />';}
					if ($row['einh3']) { $units .= $row['einh3'].' '.$einh[3]['name'].'<br />';}
					if ($row['einh4']) { $units .= $row['einh4'].' '.$einh[4]['name'].'<br />';}
					if ($row['einh5']) { $units .= $row['einh5'].' '.$einh[5]['name'].'<br />';}
					if ($row['einh6']) { $units .= $row['einh6'].' '.$einh[6]['name'].'<br />';}
					if ($row['einh7']) { $units .= $row['einh7'].' '.$einh[7]['name'].'<br />';}
					if ($row['einh8']) { $units .= $row['einh8'].' '.$einh[8]['name'].'<br />';}
					if ($row['einh9']) { $units .= $row['einh9'].' '.$einh[9]['name'].'<br />';}
					if ($row['einh10']) { $units .= $row['einh10'].' '.$einh[10]['name'].'<br />';}
					if ($row['einh11']) { $units .= $row['einh11'].' '.$einh[11]['name'].'<br />';}
					if ($row['einh12']) { $units .= $row['einh12'].' '.$einh[12]['name'].'<br />';}
					if ($row['einh13']) { $units .= $row['einh13'].' '.$einh[13]['name'].'<br />';}
					if ($row['einh14']) { $units .= $row['einh14'].' '.$einh[14]['name'].'<br />';}
					if ($row['einh15']) { $units .= $row['einh15'].' '.$einh[15]['name'].'<br />';}

					// fahrwege 
					if ($gebaeude['fahrwege'] < 1)   {$klatsch_fw['einh5'] = $row['einh5']; $row['einh5'] = 0;}
					if ($gebaeude['fahrwege'] < 3)   {$klatsch_fw['einh6'] = $row['einh6']; $row['einh6'] = 0;}
					if ($gebaeude['fahrwege'] < 6)   {$klatsch_fw['einh7'] = $row['einh7']; $row['einh7'] = 0;}
					if ($gebaeude['fahrwege'] < 8)   {$klatsch_fw['einh8'] = $row['einh8']; $row['einh8'] = 0;}
					if ($gebaeude['fahrwege'] < 6)   {$klatsch_fw['einh9'] = $row['einh9']; $row['einh9'] = 0;}
					if ($gebaeude['fahrwege'] < 10)  {$klatsch_fw['einh10'] = $row['einh10']; $row['einh10'] = 0;}
					if ($gebaeude['fahrwege'] < 15)  {$klatsch_fw['einh11'] = $row['einh11']; $row['einh11'] = 0;}
					if ($gebaeude['fahrwege'] < 1)   {$klatsch_fw['einh12'] = $row['einh12']; $row['einh12'] = 0;}
					if ($gebaeude['fahrwege'] < 3)   {$klatsch_fw['einh13'] = $row['einh13']; $row['einh13'] = 0;}
					if ($gebaeude['fahrwege'] < 1)   {$klatsch_fw['einh14'] = $row['einh14']; $row['einh14'] = 0;}
					if ($gebaeude['fahrwege'] < 3)   {$klatsch_fw['einh15'] = $row['einh15']; $row['einh15'] = 0;}
					
					
					for ($count =1; $count <= 15; $count++) {
						
						$type = 'einh'.$count;
						
						for (;$row[$type];) { 

							if (($ressis['hangar']-$einh[$count]['size']) >= 0 and $row[$type] > 0) {
								
								$hangar[$type]++; $row[$type]--; $ressis['hangar'] -= $einh[$count]['size'];
								
							} else { 
								
								$klatsch[$count] = $row[$type]; 
								$row[$type] = 0;
								
							}
							
						} 
						
					}

					if ($klatsch) {
						
						$count = 0;
						do {
							$count++;
							if ($klatsch[$count]) { $klatscher .= $klatsch[$count].' x '.$einh[$count]['name'].'<br />';}
						} while ($count < 15);
						
						if ($klatscher) {$geklatscht .= '<br /><br />Leider konnten aufgrund mangelnden Hangarplatzes <br />'.$klatscher.' nicht eingelagert werden.';}
					}
									
					if ($klatsch_fw) {						
						unset($klatscher);
						$count = 0;
						do {
							$count++;
							if ($klatsch_fw[$count]) { $klatscher .= $klatsch[$count].' x '.$einh[$count]['name'].'<br />';}
						} while ($count < 15);
						
						if ($klatscher) {$geklatscht .= '<br /><br />Leider konnten aufgrund mangelnden Fahrwegeausbaus <br />'.$klatscher.'  nicht eingelagert werden.';}
						
					} 

					if (!$klatsch and !$klatsch_fw) {
						
						$geklatscht = '<br /><br />Alle Einheiten wurden ordnungsgem&auml;ss eingelagert.';		
						
					}
					
					$select = "UPDATE `hangar` SET `einh1` = '".$hangar['einh1']."', `einh2` = '".$hangar['einh2']."', `einh3` = '".$hangar['einh3']."', `einh4` = '".$hangar['einh4']."', `einh5` = '".$hangar['einh5']."', `einh6` = '".$hangar['einh6']."', `einh7` = '".$hangar['einh7']."', `einh8` = '".$hangar['einh8']."', `einh9` = '".$hangar['einh9']."', `einh10` = '".$hangar['einh10']."', `einh11` = '".$hangar['einh11']."', `einh12` = '".$hangar['einh12']."', `einh13` = '".$hangar['einh13']."', `einh14` = '".$hangar['einh14']."', `einh15` = '".$hangar['einh15']."' WHERE `omni` = '".$row['ziel']."' LIMIT 1;";
					mysql_query($select);
					
					for ($i=1;$i<=15;$i++) {
					
						$klatsch[$i] += $klatsch_fw[$i];
						$k += $klatsch[$i];
					
					}
					
					if ($k <= 0) {

						mysql_query("DELETE FROM `missionen` WHERE `id` = '".$row['id']."' LIMIT 1;");
						
					} else {
					
						mysql_query("UPDATE `missionen` SET `einh1` = '".$klatsch[1]."',
`einh2` = '".$klatsch[2]."',
`einh3` = '".$klatsch[3]."',
`einh4` = '".$klatsch[4]."',
`einh5` = '".$klatsch[5]."',
`einh6` = '".$klatsch[6]."',
`einh7` = '".$klatsch[7]."',
`einh8` = '".$klatsch[8]."',
`einh9` = '".$klatsch[9]."',
`einh10` = '".$klatsch[10]."',
`einh11` = '".$klatsch[11]."',
`einh12` = '".$klatsch[12]."',
`einh13` = '".$klatsch[13]."',
`einh14` = '".$klatsch[14]."',
`einh15` = '".$klatsch[15]."',
`eisen` = '0',
`titan` = '0',
`oel` = '0',
`uran` = '0',
`gold` = '0',
`chanje` = '0' WHERE `id` ='".$row['id']."' LIMIT 1;");
					
					}
					
					$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Missionszentrum', '".$row['ziel']."', '".$row['ankunft']."', '0', '&Uuml;berf&uuml;hrung von ".$row['start']."', '".$row['start']." hat dir<br />".$row['eisen']." Eisen<br />".$row['titan']." Titan<br />  ".$row['oel']." Oel<br />".$row['uran']." Uran<br />".$row['gold']." Gold<br />".$row['chanje']." Chanje<br /><br />".$units."geliefert. ".$geklatscht."' );";
					mysql_query($select);	
					$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Missionszentrum', '".$row['start']."', '".$row['ankunft']."', '0', '&Uuml;berf&uuml;hrung zu ".$row['ziel']."', 'Du hast an die Basis bei ".$row['ziel']."<br />".$row['eisen']." Eisen<br />".$row['titan']." Titan<br />  ".$row['oel']." Oel<br />".$row['uran']." Uran<br />".$row['gold']." Gold<br />".$row['chanje']." Chanje<br /><br />".$units."geliefert. ".$geklatscht."' );";
					mysql_query($select);

					$handel = mysql_query("SELECT * FROM `handel` WHERE `p1` = '".$row['start']."' AND `p2` = '".$row['ziel']."' LIMIT 1;");
					$handel = @mysql_fetch_array($handel);
					if ($handel) {
						mysql_query("UPDATE `handel` SET `s1` = s1-(".($kosten+$row['eisen']+$row['titan']+$row['oel']+$row['uran']+$row['gold']+$row['chanje']*1000)."), `s2` = s2+(".($kosten+$row['eisen']+$row['titan']+$row['oel']+$row['uran']+$row['gold']+$row['chanje']*1000).") WHERE `id` = '".$handel['id']."' LIMIT 1;");
					} else {
						$handel = mysql_query("SELECT * FROM `handel` WHERE `p2` = '".$row['start']."' AND `p1` = '".$row['ziel']."' LIMIT 1;");
						$handel = @mysql_fetch_array($handel);						
						if ($handel) {
							mysql_query("UPDATE `handel` SET `s1` = s1+(".($kosten+$row['eisen']+$row['titan']+$row['oel']+$row['uran']+$row['gold']+$row['chanje']*1000)."), `s2` = s2-(".($kosten+$row['eisen']+$row['titan']+$row['oel']+$row['uran']+$row['gold']+$row['chanje']*1000).") WHERE `id` = '".$handel['id']."' LIMIT 1;");
						} else {
							mysql_query("INSERT INTO `handel` ( `id` , `p1` , `p2` , `s1` , `s2` ) VALUES ( '', '".$row['start']."', '".$row['ziel']."', '".($kosten+$row['eisen']+$row['titan']+$row['oel']+$row['uran']+$row['gold']+$row['chanje']*1000)."', '-".($kosten+$row['eisen']+$row['titan']+$row['oel']+$row['uran']+$row['gold']+$row['chanje']*1000)."' );");
						}
					}					
					
				} else {
					$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Missionszentrum', '".$row['start']."', '".$row['ankunft']."', '0', '&Uuml;berf&uuml;hrung zu ".$row['ziel']."', 'Du hast keine Basis bei ".$row['ziel']." vorgefunden.<br />Deine Einheiten kehren incl. Ladung zur&uuml;ck.' );";
					mysql_query($select);	
				} 
			} elseif ($row['type'] == 4) {
				// sammeln
				$select = "SELECT * FROM `user` WHERE `omni` = '".$row['ziel']."';";
				$res = mysql_query($select);	
				$tf  = mysql_fetch_array($res, MYSQL_ASSOC);
				$space = $row['einh15'] * $einh[15]['space'];
				
				if ($space >= $tf['tf_eisen']) { $sammeln['eisen'] = $tf['tf_eisen']; $space -= $tf['tf_eisen']; }
				else { $sammeln['eisen'] = $space; $space = 0; }
				
				if ($space >= $tf['tf_titan']) { $sammeln['titan'] = $tf['tf_titan']; $space -= $tf['tf_titan']; }
				else { $sammeln['titan'] = $space; }
							
				$select = "UPDATE `missionen` SET `eisen` = '".$sammeln['eisen']."',`titan` = '".$sammeln['titan']."',`oel` = '0',`uran` = '0',`gold` = '0',`chanje` = '0' WHERE `id` = '".$row['id']."' LIMIT 1;";
				mysql_query($select);
				
				$select = "UPDATE `user` SET `tf_eisen` = '".($tf['tf_eisen']-$sammeln['eisen'])."', `tf_titan` = '".($tf['tf_titan']-$sammeln['titan'])."' WHERE `omni` = '".$row['ziel']."' LIMIT 1 ;";
				mysql_query($select);
				
				$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Missionszentrum', '".$row['start']."', '".$row['ankunft']."', '0', 'Sammeln bei ".$row['ziel']."', 'Deine ".$row['einh15']." Sammler haben einen Gesamtladeplatz von ".($row['einh15'] * $einh[15]['space']).".<br />Bei der Zielbasis lagen ".$tf['tf_eisen']." Eisen ".$tf['tf_titan']." Titan<br />Du hast davon ".$sammeln['eisen']." Eisen ".$sammeln['titan']." Titan gesammelt.' );";
				mysql_query($select);					
			}
			$select = "UPDATE `missionen` SET `parsed` = '1' WHERE `id` = '".$row['id']."' LIMIT 1;";
			mysql_query($select);

			$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '1', '".$row['id']."', '".($row['return'])."');";
			$selectResult   = mysql_query($select);
		}
	} else {
		if ($row){
			$ressis = ressistand($row['start']);
			$select = "UPDATE `ressis` SET `eisen` = '".($ressis['eisen'] + $row['eisen'])."',`titan` = '".($ressis['titan'] + $row['titan'])."',`oel` = '".($ressis['oel'] + $row['oel'])."',`uran` = '".($ressis['uran'] + $row['uran'])."',`gold` = '".($ressis['gold'] + $row['gold'])."',`chanje` = '".($ressis['chanje'] + $row['chanje'])."' WHERE `omni` = '".$row['start']."' LIMIT 1;";
			mysql_query($select);
			$ressiupdate = mysql_errno($dbh);
			
			$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$omni."' LIMIT 1;";
			$selectResult = mysql_query($select);
			$gebaeude     = mysql_fetch_array($selectResult);	
	
			// checken ob einheiten fertig sind und dann hangar setzen
			$hangar = new_units_check($row['start']);

			if ($row['einh1']) { $einheiten .= $row['einh1'].' x '.$einh[1]['name'].'<br />';}
			if ($row['einh2']) { $einheiten .= $row['einh2'].' x '.$einh[2]['name'].'<br />';}
			if ($row['einh3']) { $einheiten .= $row['einh3'].' x '.$einh[3]['name'].'<br />';}
			if ($row['einh4']) { $einheiten .= $row['einh4'].' x '.$einh[4]['name'].'<br />';}
			if ($row['einh5']) { $einheiten .= $row['einh5'].' x '.$einh[5]['name'].'<br />';}
			if ($row['einh6']) { $einheiten .= $row['einh6'].' x '.$einh[6]['name'].'<br />';}
			if ($row['einh7']) { $einheiten .= $row['einh7'].' x '.$einh[7]['name'].'<br />';}
			if ($row['einh8']) { $einheiten .= $row['einh8'].' x '.$einh[8]['name'].'<br />';}
			if ($row['einh9']) { $einheiten .= $row['einh9'].' x '.$einh[9]['name'].'<br />';}
			if ($row['einh10']) { $einheiten .= $row['einh10'].' x '.$einh[10]['name'].'<br />';}
			if ($row['einh11']) { $einheiten .= $row['einh11'].' x '.$einh[11]['name'].'<br />';}
			if ($row['einh12']) { $einheiten .= $row['einh12'].' x '.$einh[12]['name'].'<br />';}
			if ($row['einh13']) { $einheiten .= $row['einh13'].' x '.$einh[13]['name'].'<br />';}
			if ($row['einh14']) { $einheiten .= $row['einh14'].' x '.$einh[14]['name'].'<br />';}
			if ($row['einh15']) { $einheiten .= $row['einh15'].' x '.$einh[15]['name'].'<br />';}
			
			$count = 0;
			do {
				$count++;
				$type = 'einh'.$count;
				do { 
					if ($ressis['hangar'] >= $einh[$count]['size'] and $row[$type] > 0) {$hangar[$type]++; $row[$type]--; $ressis['hangar'] -= $einh[$count]['size'];} 
					else { $klatsch[$count] = $row[$type]; $row[$type] = 0; }
				} while ($row[$type] > 0);
			} while ( $count < 15 );
			
			$count = 0;
			do {
				$count++;
				if ($klatsch[$count]) { $geklatscht .= $klatsch[$count].' x '.$einh[$count]['name'].'<br />';}
			} while ($count < 15);
			
			if ($geklatscht) {
				$rand = rand((2*3600),(6*3600));
				
				$restsekunden = $rand;
				$stunden = floor($restsekunden/60/60);
				$restsekunden = $restsekunden-$stunden*60*60;
				$minuten = $restsekunden/60; // Umrechnung in Minuten
				$ganzzahl = floor($minuten); // Abrunden auf Ganzzahl
				$sekunden2 = $ganzzahl*60; // Rest errechnen
				$restsek = $restsekunden - $sekunden2; // Restsekunden 
	
				$restsek = number_format($restsek, 0, '', '');
				$ganzzahl = str_pad( $ganzzahl, 2, "0", STR_PAD_LEFT);
				$restsek = str_pad( $restsek, 2, "0", STR_PAD_LEFT);
							
				$geklatscht = '<br /><br />Leider sind aufgrund mangelnden Hangarplatzes: <br />'.$geklatscht.'f&uuml;r '.$stunden.' Stunden und '.$ganzzahl.' Minuten'.' verloren gegangen.<br />';
				
				$select = "INSERT INTO `missionen` ( `id` , `type` , `start` , `ziel` , `started` , `ankunft` , `return` , `parsed` , `speed` , `einh1` , `einh2` , `einh3` , `einh4` , `einh5` , `einh6` , `einh7` , `einh8` , `einh9` , `einh10` , `einh11` , `einh12` , `einh13` , `einh14` , `einh15` , `eisen` , `titan` , `oel` , `uran` , `gold` , `chanje` ) VALUES ( '', '2', '".$row['start']."', '".$row['start']."', '".(date(U)-$rand)."', '".(date(U))."', '".(date(U)+$rand)."', '1', '10', '".$klatsch[1]."', '".$klatsch[2]."', '".$klatsch[3]."', '".$klatsch[4]."', '".$klatsch[5]."', '".$klatsch[6]."', '".$klatsch[7]."', '".$klatsch[8]."', '".$klatsch[9]."', '".$klatsch[10]."', '".$klatsch[11]."', '".$klatsch[12]."', '".$klatsch[13]."', '".$klatsch[14]."', '".$klatsch[15]."', '0', '0', '0', '0', '0', '0' );";
				$selectResult   = mysql_query($select);
		
				$eid = mysql_insert_id($dbh);
	
				$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '1', '".$eid."', '".(date(U)+$rand)."');";
				$selectResult   = mysql_query($select);
			}
			
			$einheiten = str_replace("'",'`',$einheiten);
			
			$select = "UPDATE `hangar` SET `einh1` = '".$hangar['einh1']."', `einh2` = '".$hangar['einh2']."', `einh3` = '".$hangar['einh3']."', `einh4` = '".$hangar['einh4']."', `einh5` = '".$hangar['einh5']."', `einh6` = '".$hangar['einh6']."', `einh7` = '".$hangar['einh7']."', `einh8` = '".$hangar['einh8']."', `einh9` = '".$hangar['einh9']."', `einh10` = '".$hangar['einh10']."', `einh11` = '".$hangar['einh11']."', `einh12` = '".$hangar['einh12']."', `einh13` = '".$hangar['einh13']."', `einh14` = '".$hangar['einh14']."', `einh15` = '".$hangar['einh15']."' WHERE `omni` = '".$row['start']."' LIMIT 1;";
			mysql_query($select);

			if   ($ressiupdate != 0) { $eingelagert = "Die Ressourcen wurden <b>NICHT</b>erfolgreich eingelagert.<br />DAS IST EIN BUG, BITTE MELDEN!!!!!!!!!"; }

			if     ($row['type'] == 1) { $from = "vom Angriff auf ".$row['ziel']; }
			elseif ($row['type'] == 2) { $from = "vom Transport nach ".$row['ziel']; }
			elseif ($row['type'] == 3) { $from = "von der &Uuml;berf&uuml;hrung zu ".$row['ziel']; }
			elseif ($row['type'] == 4) { $from = "vom Sammeln bei ".$row['ziel']; }
			
			$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Missionszentrum', '".$row['start']."', '".$row['return']."', '0', 'R&uuml;ckkehr ".$from."', 'Einheiten sind zur&uuml;ckgekehrt:<br />".$einheiten."<br /> und haben <br />".$row['eisen']." Eisen<br />".$row['titan']." Titan<br />  ".$row['oel']." Oel<br />".$row['uran']." Uran<br />".$row['gold']." Gold<br />".$row['chanje']." Chanje<br />geliefert. <br />".$eingelagert."<br /> ".$geklatscht."' );";
			mysql_query($select);	
			$select = "DELETE FROM `missionen` WHERE `parsed` = '1' AND `id` = '".$row['id']."' LIMIT 1;";
			mysql_query($select);
		}
	}
}

function kampf($id) {
include('einheiten_preise.php');
include('def_preise.php');

// db verbindung
$dbh = db_connect();

$select = "SELECT * FROM `missionen` WHERE `id` = '".$id."';";
$result = mysql_query($select);
$offender = mysql_fetch_array($result);

$o_omni = $offender['start'];
$d_omni = $offender['ziel'];

$select = "SELECT * FROM `user` WHERE `omni` = '".$o_omni."';";
$result = mysql_query($select);
$offender_user = mysql_fetch_array($result);

$select = "SELECT * FROM `user` WHERE `omni` = '".$d_omni."';";
$result = mysql_query($select);
if (mysql_num_rows($result) == 0){ $content = 'Es wurde keine Basis bei '.$d_omni.' gefunden.'; }
else {
$defender_user = mysql_fetch_array($result);
$date = date(U);

$defender = new_units_check($d_omni);

$select = "SELECT * FROM `defense` WHERE `omni` = '".$d_omni."';";
$result = mysql_query($select);
$defender_def = mysql_fetch_array($result);

$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$d_omni."';";
$result = mysql_query($select);
$gebaeude = mysql_fetch_array($result);

$select = "SELECT * FROM `forschungen` WHERE `omni` = '".$o_omni."';";
$result = mysql_query($select);
$row = mysql_fetch_array($result);
$o_fuehrung = $row['fuehrung'];
if (!$o_fuehrung){$o_fuehrung=0;}

$select = "SELECT * FROM `forschungen` WHERE `omni` = '".$d_omni."';";
$result = mysql_query($select);
$row = mysql_fetch_array($result);
$d_fuehrung = $row['fuehrung'];
if (!$d_fuehrung){$d_fuehrung=0;}

if ($defender_def['def1'] or $defender_def['def2'] or $defender_def['def3'] or $defender_def['def4']) {
	$content .=  'Auf dem Weg zur Basis bei '.$d_omni.' wurden die Einheiten des Angreifers durch <br />';
	if ($defender_def['def1']) { $content .= '&nbsp;&nbsp;'.$defender_def['def1'].' '.$def[1]['name'].'<br />';}
	if ($defender_def['def2']) { $content .= '&nbsp;&nbsp;'.$defender_def['def2'].' '.$def[2]['name'].'<br />';}
	if ($defender_def['def3']) { $content .= '&nbsp;&nbsp;'.$defender_def['def3'].' '.$def[3]['name'].'<br />';}
	if ($defender_def['def4']) { $content .= '&nbsp;&nbsp;'.$defender_def['def4'].' '.$def[4]['name'].'<br />';}
	$content .= '&uuml;berrascht, dadurch entstanden folgende Verluste: <br />';

	if ($defender_def['def1'] != 0 and $offender['einh4'] != 0) { 
		do {
			$demont++; 
			if (rand(1,100)<=70){$defender_def['def1']--; $entschaerfung++; if (rand(1,100)<=30){ $vo[4]++; $offender['einh4']--; }} 
		} while ($offender['einh4'] > $demont and $defender_def['def1'] > 0);
	}
	if ($offender['einh4'] > $demont and $defender_def['def2'] != 0 and $offender['einh4'] != 0) { 
		do {
			$demont++; 
			if (rand(1,100)<=65){$defender_def['def2']--; $entschaerfung++; if (rand(1,100)<=40){ $vo[4]++; $offender['einh4']--; }} 
		} while ($offender['einh4'] > $demont and $defender_def['def2'] > 0);
	}
	if ($offender['einh4'] > $demont and $defender_def['def3'] != 0 and $offender['einh4'] != 0) { 
		do {
			$demont++; 
			if (rand(1,100)<=90){$defender_def['def3']--; $entschaerfung++; if (rand(1,100)<=15){ $vo[4]++; $offender['einh4']--; }} 
		} while ($offender['einh4'] > $demont and $defender_def['def3'] > 0);
	}
	if ($offender['einh4'] > $demont and $defender_def['def4'] != 0 and $offender['einh4'] != 0) { 
		do {
			$demont++; 
			if (rand(1,100)<=80){$defender_def['def4']--; $entschaerfung++; if (rand(1,100)<=20){ $vo[4]++; $offender['einh4']--; }} 
		} while ($offender['einh4'] > $demont and $defender_def['def4'] > 0);
	}

	if (!$entschaerfung) {$entschaerfung=keine;}
	
	if ($defender_def['def1'] != 0 and $offender['einh1'] != 0) { 
		do {$vo[1]++; $offender['einh1']--; $defender_def['def1']--;} while ($offender['einh1']  > 0 and $defender_def['def1'] > 0);
	}
	if ($defender_def['def1'] != 0 and $offender['einh2'] != 0) { 
		do {$vo[2]++; $offender['einh2']--; $defender_def['def1']--;} while ($offender['einh2']  > 0 and $defender_def['def1'] > 0);
	}
	if ($defender_def['def1'] != 0 and $offender['einh3'] != 0) { 
		do {$vo[3]++; $offender['einh3']--; $defender_def['def1']--;} while ($offender['einh3']  > 0 and $defender_def['def1'] > 0);
	}
	

	if ($defender_def['def2'] != 0 and $offender['einh1'] != 0) { 
		do {$vo[1]++; $offender['einh1']--; $defender_def['def2']--;} while ($offender['einh1']  > 0 and $defender_def['def2'] > 0);
	}
	if ($defender_def['def2'] != 0 and $offender['einh2'] != 0) { 
		do {$vo[2]++; $offender['einh2']--; $defender_def['def2']--;} while ($offender['einh2']  > 0 and $defender_def['def2'] > 0);
	}
	if ($defender_def['def2'] != 0 and $offender['einh3'] != 0) { 
		do {$vo[3]++; $offender['einh3']--; $defender_def['def2']--;} while ($offender['einh3']  > 0 and $defender_def['def2'] > 0);
	}
	if ($defender_def['def2'] != 0 and $offender['einh4'] != 0) { 
		do {$vo[4]++; $offender['einh4']--; $defender_def['def2']--;} while ($offender['einh4']  > 0 and $defender_def['def2'] > 0);
	}

	
	if ($defender_def['def3'] != 0 and $offender['einh12'] != 0) { 
		do {$vo[12]++; $offender['einh12']--; $defender_def['def3']--;} while ($offender['einh12']  > 0 and $defender_def['def3'] > 0);
	}
	if ($defender_def['def3'] != 0 and $offender['einh13'] != 0) { 
		do {$vo[13]++; $offender['einh13']--; $defender_def['def3']--;} while ($offender['einh13']  > 0 and $defender_def['def3'] > 0);
	}
	if ($defender_def['def3'] != 0 and $offender['einh14'] != 0) { 
		do {$vo[14]++; $offender['einh14']--; $defender_def['def3']--;} while ($offender['einh14']  > 0 and $defender_def['def3'] > 0);
	}
	if ($defender_def['def3'] != 0 and $offender['einh15'] != 0) { 
		do {$vo[15]++; $offender['einh15']--; $defender_def['def3']--;} while ($offender['einh15']  > 0 and $defender_def['def3'] > 0);
	}
	if ($defender_def['def3'] != 0 and $offender['einh5'] != 0) { 
		do {$vo[5]++; $offender['einh5']--; $defender_def['def3']--;} while ($offender['einh5']  > 0 and $defender_def['def3'] > 0);
	}
	if ($defender_def['def3'] != 0 and $offender['einh6'] != 0) { 
		do {$vo[6]++; $offender['einh6']--; $defender_def['def3']--;} while ($offender['einh6']  > 0 and $defender_def['def3'] > 0);
	}

	
	if ($defender_def['def4'] != 0 and $offender['einh12'] != 0) { 
		do {$vo[12]++; $offender['einh12']--; $defender_def['def4']--;} while ($offender['einh12'] > 0 and $defender_def['def4'] > 0);
	}
	if ($defender_def['def4'] != 0 and $offender['einh13'] != 0) { 
		do {$vo[13]++; $offender['einh13']--; $defender_def['def4']--;} while ($offender['einh13'] > 0 and $defender_def['def4'] > 0);
	}
	if ($defender_def['def4'] != 0 and $offender['einh14'] != 0) { 
		do {$vo[14]++; $offender['einh14']--; $defender_def['def4']--;} while ($offender['einh14'] > 0 and $defender_def['def4'] > 0);
	}
	if ($defender_def['def4'] != 0 and $offender['einh15'] != 0) { 
		do {$vo[15]++; $offender['einh15']--; $defender_def['def4']--;} while ($offender['einh15'] > 0 and $defender_def['def4'] > 0);
	}
	if ($defender_def['def4'] != 0 and $offender['einh5'] != 0) { 
		do {$vo[5]++; $offender['einh5']--; $defender_def['def4']--;} while ($offender['einh5'] > 0 and $defender_def['def4'] > 0);
	}
	if ($defender_def['def4'] != 0 and $offender['einh6'] != 0) { 
		do {$vo[6]++; $offender['einh6']--; $defender_def['def4']--;} while ($offender['einh6'] > 0 and $defender_def['def4'] > 0);
	}
	if ($defender_def['def4'] != 0 and $offender['einh7'] != 0) { 
		do {$vo[7]++; $offender['einh7']--; $defender_def['def4']--;} while ($offender['einh7'] > 0 and $defender_def['def4'] > 0);
	}
	if ($defender_def['def4'] != 0 and $offender['einh9'] != 0) { 
		do {$vo[9]++; $offender['einh9']--; $defender_def['def4']--;} while ($offender['einh9'] > 0 and $defender_def['def4'] > 0);
	}
	if ($defender_def['def4'] != 0 and $offender['einh8'] != 0) { 
		do {$vo[8]++; $offender['einh8']--; $defender_def['def4']--;} while ($offender['einh8'] > 0 and $defender_def['def4'] > 0);
	}	
	

	if ($vo[1]) { $content .= '&nbsp;&nbsp;'.$vo[1].' '.$einh[1]['name'].'<br />';}
	if ($vo[2]) { $content .= '&nbsp;&nbsp;'.$vo[2].' '.$einh[2]['name'].'<br />';}
	if ($vo[3]) { $content .= '&nbsp;&nbsp;'.$vo[3].' '.$einh[3]['name'].'<br />';}
	if ($vo[4]) { $content .= '&nbsp;&nbsp;'.$vo[4].' '.$einh[4]['name'].'<br />';}
	if ($vo[5]) { $content .= '&nbsp;&nbsp;'.$vo[5].' '.$einh[5]['name'].'<br />';}
	if ($vo[6]) { $content .= '&nbsp;&nbsp;'.$vo[6].' '.$einh[6]['name'].'<br />';}
	if ($vo[7]) { $content .= '&nbsp;&nbsp;'.$vo[7].' '.$einh[7]['name'].'<br />';}
	if ($vo[8]) { $content .= '&nbsp;&nbsp;'.$vo[8].' '.$einh[8]['name'].'<br />';}
	if ($vo[9]) { $content .= '&nbsp;&nbsp;'.$vo[9].' '.$einh[9]['name'].'<br />';}
	if ($vo[10]) { $content .= '&nbsp;&nbsp;'.$vo[10].' '.$einh[10]['name'].'<br />';}
	if ($vo[11]) { $content .= '&nbsp;&nbsp;'.$vo[11].' '.$einh[11]['name'].'<br />';}
	if ($vo[12]) { $content .= '&nbsp;&nbsp;'.$vo[12].' '.$einh[12]['name'].'<br />';}
	if ($vo[13]) { $content .= '&nbsp;&nbsp;'.$vo[13].' '.$einh[13]['name'].'<br />';}
	if ($vo[14]) { $content .= '&nbsp;&nbsp;'.$vo[14].' '.$einh[14]['name'].'<br />';}
	if ($vo[15]) { $content .= '&nbsp;&nbsp;'.$vo[15].' '.$einh[15]['name'].'<br />';}
	
	$content .= '<br />Die Elitesoldaten des Angreifers haben '.$entschaerfung.' Minen entsch&auml;rft.<br /><br />';
}

$content .= 'Folgende Truppen standen sich am '.date("d.m.Y \u\m H:i",$date).' gegen&uuml;ber: <br /><br /><i>Angreifer ('.$offender_user['name'].'):</i><br />';
if ($offender['einh1']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh1'].' '.$einh[1]['name'].'<br />';}
if ($offender['einh2']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh2'].' '.$einh[2]['name'].'<br />';}
if ($offender['einh3']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh3'].' '.$einh[3]['name'].'<br />';}
if ($offender['einh4']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh4'].' '.$einh[4]['name'].'<br />';}
if ($offender['einh5']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh5'].' '.$einh[5]['name'].'<br />';}
if ($offender['einh6']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh6'].' '.$einh[6]['name'].'<br />';}
if ($offender['einh7']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh7'].' '.$einh[7]['name'].'<br />';}
if ($offender['einh8']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh8'].' '.$einh[8]['name'].'<br />';}
if ($offender['einh9']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh9'].' '.$einh[9]['name'].'<br />';}
if ($offender['einh10']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh10'].' '.$einh[10]['name'].'<br />';}
if ($offender['einh11']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh11'].' '.$einh[11]['name'].'<br />';}
if ($offender['einh12']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh12'].' '.$einh[12]['name'].'<br />';}
if ($offender['einh13']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh13'].' '.$einh[13]['name'].'<br />';}
if ($offender['einh14']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh14'].' '.$einh[14]['name'].'<br />';}
if ($offender['einh15']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh15'].' '.$einh[15]['name'].'<br />';}

do {
	$count++;
	$type = 'einh'.$count;
	$o_anz = $o_anz+$offender[$type];
	$o_off += ($einh[$count]['off']+($einh[$count]['off']/10*$o_fuehrung))*$offender[$type];
	$o_def += ($einh[$count]['def']+($einh[$count]['def']/10*$o_fuehrung))*$offender[$type];
} while ( 15 > $count );

$count = 4;
do {
	$count++;
	$type = 'def'.$count;
	$d_anz = $d_anz+$defender_def[$type];
	$d_off += ($def[$count]['off']+($def[$count]['off']/10*$d_fuehrung))*$defender_def[$type];
	$d_def += ($def[$count]['def']+($def[$count]['def']/10*$d_fuehrung))*$defender_def[$type];
} while ( 10 > $count );

$content .= '<br />F&uuml;hrungsbonus: '.($o_fuehrung*10).'%<br />';
$content .= 'Einheiten: '.$o_anz.'<br />';
$content .= 'Angriffswert: <b>'.($o_off).'</b><br />';
$content .= 'Verteidigungswert: <b>'.($o_def).'</b><br />';

$content .= '<br /><i>Verteidiger ('.$defender_user['name'].'):</i><br />';
if ($defender['einh1']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh1'].' '.$einh[1]['name'].'<br />';}
if ($defender['einh2']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh2'].' '.$einh[2]['name'].'<br />';}
if ($defender['einh3']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh3'].' '.$einh[3]['name'].'<br />';}
if ($defender['einh4']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh4'].' '.$einh[4]['name'].'<br />';}
if ($defender['einh5']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh5'].' '.$einh[5]['name'].'<br />';}
if ($defender['einh6']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh6'].' '.$einh[6]['name'].'<br />';}
if ($defender['einh7']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh7'].' '.$einh[7]['name'].'<br />';}
if ($defender['einh8']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh8'].' '.$einh[8]['name'].'<br />';}
if ($defender['einh9']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh9'].' '.$einh[9]['name'].'<br />';}
if ($defender['einh10']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh10'].' '.$einh[10]['name'].'<br />';}
if ($defender['einh11']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh11'].' '.$einh[11]['name'].'<br />';}
if ($defender['einh12']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh12'].' '.$einh[12]['name'].'<br />';}
if ($defender['einh13']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh13'].' '.$einh[13]['name'].'<br />';}
if ($defender['einh14']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh14'].' '.$einh[14]['name'].'<br />';}
if ($defender['einh15']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh15'].' '.$einh[15]['name'].'<br />';}
if ($defender_def['def5']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def['def5'].' '.$def[5]['name'].'<br />';}
if ($defender_def['def6']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def['def6'].' '.$def[6]['name'].'<br />';}
if ($defender_def['def7']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def['def7'].' '.$def[7]['name'].'<br />';}
if ($defender_def['def8']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def['def8'].' '.$def[8]['name'].'<br />';}
if ($defender_def['def9']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def['def9'].' '.$def[9]['name'].'<br />';}
if ($defender_def['def10']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def['def10'].' '.$def[10]['name'].'<br />';}

$count = 0;
do {
	$count++;
	$type = 'einh'.$count;
	$d_anz = $d_anz+$defender[$type];
	$d_off += ($einh[$count]['off']+($einh[$count]['off']/10*$d_fuehrung))*$defender[$type];
	$d_def += ($einh[$count]['def']+($einh[$count]['def']/10*$d_fuehrung))*$defender[$type];
} while ( 15 > $count );

$content .= '<br />F&uuml;hrungsbonus: '.($d_fuehrung*10).'%<br />';
$content .= 'Einheiten: '.$d_anz.'<br />';
$content .= 'Angriffswert: <b>'.($d_off).'</b><br />';
$content .= 'Verteidigungswert: <b>'.($d_def).'</b><br />';


$bonus = rand (0,10);
if     ($bonus == 1) { $o_off_bonus = 10; $bonus = "<br /><b>Der Angreifer konnte den Verteidiger &uuml;berraschen. <br />(+10% Angriff)</b><br />"; }
elseif ($bonus == 2) { $o_def_bonus = 10; $bonus = "<br /><b>Die Soldaten des Angreifers haben tapfer und mutig gek&auml;mpft. <br />(+10% Verteidigung)</b><br />"; }
elseif ($bonus == 3) { $o_off_bonus = 10; $bonus = "<br /><b>Der Verteidiger konnte den Angreifer &uuml;berraschen. <br />(+10% Angriff)</b><br />"; }
elseif ($bonus == 4) { $d_def_bonus = 10; $bonus = "<br /><b>Die Soldaten des Verteidigers haben tapfer und mutig gek&auml;mpft. <br />(+10% Verteidigung)</b><br />"; }
else   {$bonus = "<br />";}
$j=0;
do {
	$i++;
	$einheit = 'einh'.$i;
	$k=0;
	do {
		$k++;
		if ($k <= $offender[$einheit]){
		$soldiers['offender'][$j]['type'] = $i;
		$soldiers['offender'][$j]['id']   = $k;
		$soldiers['offender'][$j]['name'] = $einh[$i]['name'];
		$soldiers['offender'][$j]['off']  = $einh[$i]['off']+($einh[$i]['off']/10 * $o_fuehrung )+($einh[$i]['off']/100 * $o_off_bonus );
		$soldiers['offender'][$j]['def']  = $einh[$i]['def']+($einh[$i]['def']/10 * $o_fuehrung )+($einh[$i]['def']/100 * $o_def_bonus );
		$j++;
		}
	} while ($k < $offender[$einheit]);
} while ($i < 15);

$i=0;
$j=0;

do {
	$i++;
	$einheit = 'einh'.$i;
	$k=0;
	do {
		$k++;
		if ($k <= $defender[$einheit]){
		$soldiers['defender'][$j]['type'] = $i;
		$soldiers['defender'][$j]['id']   = $k;
		$soldiers['defender'][$j]['name'] = $einh[$i]['name'];
		$soldiers['defender'][$j]['off']  = $einh[$i]['off']+($einh[$i]['off']/10 * $d_fuehrung )+($einh[$i]['off']/100 * $d_off_bonus );
		$soldiers['defender'][$j]['def']  = $einh[$i]['def']+($einh[$i]['def']/10 * $d_fuehrung )+($einh[$i]['off']/100 * $d_def_bonus );
		$j++;
		}
	} while ($k < $defender[$einheit]);
} while ($i < 15);

$i=1004;
do {
	$i++;
	$l = $i-1000;
	$einheit = 'def'.($l);
	$k=0;
	do {
		$k++;
		if ($k <= $defender_def[$einheit]){
		$d_anz++;
		$soldiers['defender'][$j]['type'] = $i;
		$soldiers['defender'][$j]['id']   = $k;
		$soldiers['defender'][$j]['name'] = $def[$l]['name'];
		$soldiers['defender'][$j]['off']  = $def[$l]['off']+($def[$l]['off']/10 * $d_fuehrung );
		$soldiers['defender'][$j]['def']  = $def[$l]['def']+($def[$l]['def']/10 * $d_fuehrung );
		$j++;
		}
	} while ($k < $defender_def[$einheit]);
} while ($i < 1015);


if ($d_anz > 0 and $o_anz > 0){
	$content .= $bonus;

// neuer kampfmod
do {
	$kampf .= '<br /><i>Runde '.++$round.':</i><br />';
	$count_offender = count( $soldiers['offender'] )-1;
	$count_defender = count( $soldiers['defender'] )-1;
	
	$trooper_offender = 0;
	$trooper_offended = 0;
	do {
		if ($trooper_offended > $count_defender){$trooper_offended = 0;}
			
		$soldiers['defender'][$trooper_offended]['def'] -= $soldiers['offender'][$trooper_offender]['off'];
		if ($soldiers['defender'][$trooper_offended]['name']) {
			// $kampf .= 'Die angreifende Eh. '.$soldiers['offender'][$trooper_offender]['name'].' ('.$soldiers['offender'][$trooper_offender]['off'].'/'.$soldiers['offender'][$trooper_offender]['def'].') schiesst auf Eh. '.$soldiers['defender'][$trooper_offended]['name'].' ('.$soldiers['defender'][$trooper_offended]['off'].'/'.$soldiers['defender'][$trooper_offended]['def'].')<br />';
		}
		
		$trooper_offender++;
		$trooper_offended = rand(0, $count_defender);

	} while ( $trooper_offender < count( $soldiers['offender'] ) );

	$trooper_offender = 0;
	$trooper_offended = 0;
	do {
		if ($trooper_offended > $count_offender){$trooper_offended = 0;}
			
		$soldiers['offender'][$trooper_offended]['def'] -= $soldiers['defender'][$trooper_offender]['off'];
		if ($soldiers['offender'][$trooper_offended]['name']) {
			// $kampf .= 'Die verteidigende Eh. '.$soldiers['defender'][$trooper_offender]['name'].' ('.$soldiers['defender'][$trooper_offender]['off'].'/'.$soldiers['defender'][$trooper_offender]['def'].') schiesst auf Eh '.$soldiers['offender'][$trooper_offended]['name'].' ('.$soldiers['offender'][$trooper_offended]['off'].'/'.$soldiers['offender'][$trooper_offended]['def'].')<br />';
		}
		
		$trooper_offender++;
		$trooper_offended = rand(0, $count_offender);

	} while ( $trooper_offender < count( $soldiers['defender'] ) );
	
	
	// unset kaputte defender 
	$trooper_offended = 0;
	$count = (count( $soldiers['defender'] )+1);
	do {
		if( $soldiers['defender'][$trooper_offended]['def'] <= 0 and $soldiers['defender'][$trooper_offended]['type'] != 0 ) {
			// $v[$soldiers['defender'][$trooper_offended]['type']]++;
			$vd[$soldiers['defender'][$trooper_offended]['type']]++;			
			unset( $soldiers['defender'][$trooper_offended] );
		}
		$trooper_offended++;
	} while ( $trooper_offended <= $count );
	sort( $soldiers['defender'] );
	
	// unset kaputte offender 
	$trooper_offended = 0;
	$count = (count( $soldiers['offender'] )+1);
	do {		
		if( $soldiers['offender'][$trooper_offended]['def'] <= 0 and $soldiers['offender'][$trooper_offended]['type'] != 0 ) {
			// $v[$soldiers['offender'][$trooper_offended]['type']]++;
			$vo[$soldiers['offender'][$trooper_offended]['type']]++;
			unset( $soldiers['offender'][$trooper_offended] );
		}
		$trooper_offended++;
	} while ( $trooper_offended <= $count );
	sort( $soldiers['offender'] );
	
	// echo "---".count( $soldiers['offender'] )."/".count( $soldiers['defender'] )."\n";
	$kampf .= "&nbsp;&nbsp;&nbsp;Angreifer: ".count( $soldiers['offender'] )." Einheiten<br />&nbsp;&nbsp;&nbsp;Verteidiger ".count( $soldiers['defender'] )." Einheiten<br />";
} while ( count( $soldiers['offender'] ) > 0 and count( $soldiers['defender'] ) > 0 and $round < 100 );
// ende neuer kampfmod

}

$i=0;
do {
	$i++;
	$tf_eisen += number_format(($vd[$i]+$vo[$i]) * $einh[$i]['eisen'] / 1.5,0,'','');
	$tf_titan += number_format(($vd[$i]+$vo[$i]) * $einh[$i]['titan'] / 1.5,0,'','');
} while ($i < 15);

$i=1004;
do {
	$i++;
	$l = $i-1000;
	$tf_eisen += number_format(($vd[$i]+$vo[$i]) * $def[$l]['eisen'] / 1.5,0,'','');
	$tf_titan += number_format(($vd[$i]+$vo[$i]) * $def[$l]['titan'] / 1.5,0,'','');
	if ($vd[$i] > 0){
		$inst[$i] = rand (0,$vd[$i]);
		$inst['text'] .= "&nbsp;&nbsp;".$inst[$i]." x ".$def[$l]['name']."<br />";
	}	
} while ($i < 1010);


// verluste in ressis umrechnen
$i=0;
do {
	$i++;
	$vo['eisen'] += number_format(($vd[$i]-$vo[$i]) * $einh[$i]['eisen'],0,'','');
	$vo['titan'] += number_format(($vd[$i]-$vo[$i]) * $einh[$i]['titan'],0,'','');
	$vo['oel'] += number_format(($vd[$i]-$vo[$i]) * $einh[$i]['oel'],0,'','');
	$vo['uran'] += number_format(($vd[$i]-$vo[$i]) * $einh[$i]['uran'],0,'','');
	$vo['gold'] += number_format(($vd[$i]-$vo[$i]) * $einh[$i]['gold'],0,'','');
	$vo['chanje'] += number_format(($vd[$i]-$vo[$i]) * $einh[$i]['chanje'],0,'','');

	$vd['eisen'] += number_format(($vo[$i]-$vd[$i]) * $einh[$i]['eisen'],0,'','');
	$vd['titan'] += number_format(($vo[$i]-$vd[$i]) * $einh[$i]['titan'],0,'','');
	$vd['oel'] += number_format(($vo[$i]-$vd[$i]) * $einh[$i]['oel'],0,'','');
	$vd['uran'] += number_format(($vo[$i]-$vd[$i]) * $einh[$i]['uran'],0,'','');
	$vd['gold'] += number_format(($vo[$i]-$vd[$i]) * $einh[$i]['gold'],0,'','');
	$vd['chanje'] += number_format(($vo[$i]-$vd[$i]) * $einh[$i]['chanje'],0,'','');	
} while ($i < 15);


$content .= '<br /><b>Endstand:</b><br /><br />';

$count = 0;
do {
	$count++;
	$type = 'einh'.$count;
	$offender[$type] = 0;
} while ( 15 > $count );

$i=0;
do { 
	if ($soldiers['offender'][$i]['type'] == 1){$offender['einh1']++;}
	elseif ($soldiers['offender'][$i]['type'] == 2){$offender['einh2']++;}
	elseif ($soldiers['offender'][$i]['type'] == 3){$offender['einh3']++;}
	elseif ($soldiers['offender'][$i]['type'] == 4){$offender['einh4']++;}
	elseif ($soldiers['offender'][$i]['type'] == 5){$offender['einh5']++;}
	elseif ($soldiers['offender'][$i]['type'] == 6){$offender['einh6']++;}
	elseif ($soldiers['offender'][$i]['type'] == 7){$offender['einh7']++;}
	elseif ($soldiers['offender'][$i]['type'] == 8){$offender['einh8']++;}
	elseif ($soldiers['offender'][$i]['type'] == 9){$offender['einh9']++;}
	elseif ($soldiers['offender'][$i]['type'] == 10){$offender['einh10']++;}
	elseif ($soldiers['offender'][$i]['type'] == 11){$offender['einh11']++;}
	elseif ($soldiers['offender'][$i]['type'] == 12){$offender['einh12']++;}
	elseif ($soldiers['offender'][$i]['type'] == 13){$offender['einh13']++;}
	elseif ($soldiers['offender'][$i]['type'] == 14){$offender['einh14']++;}
	elseif ($soldiers['offender'][$i]['type'] == 15){$offender['einh15']++;}
	$i++;
} while ($soldiers['offender'][$i]);

$content .= '<i>Angreifer ('.$offender_user['name'].'):</i><br />';
if ($offender['einh1']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh1'].' '.$einh[1]['name'].'<br />';}
if ($offender['einh2']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh2'].' '.$einh[2]['name'].'<br />';}
if ($offender['einh3']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh3'].' '.$einh[3]['name'].'<br />';}
if ($offender['einh4']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh4'].' '.$einh[4]['name'].'<br />';}
if ($offender['einh5']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh5'].' '.$einh[5]['name'].'<br />';}
if ($offender['einh6']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh6'].' '.$einh[6]['name'].'<br />';}
if ($offender['einh7']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh7'].' '.$einh[7]['name'].'<br />';}
if ($offender['einh8']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh8'].' '.$einh[8]['name'].'<br />';}
if ($offender['einh9']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh9'].' '.$einh[9]['name'].'<br />';}
if ($offender['einh10']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh10'].' '.$einh[10]['name'].'<br />';}
if ($offender['einh11']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh11'].' '.$einh[11]['name'].'<br />';}
if ($offender['einh12']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh12'].' '.$einh[12]['name'].'<br />';}
if ($offender['einh13']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh13'].' '.$einh[13]['name'].'<br />';}
if ($offender['einh14']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh14'].' '.$einh[14]['name'].'<br />';}
if ($offender['einh15']) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender['einh15'].' '.$einh[15]['name'].'<br />';}

$count = 0;
$o_anz = 0;
$o_off = 0;
$o_def = 0;
do {
	$count++;
	$type = 'einh'.$count;
	$o_anz = $o_anz+$offender[$type];
	$o_off += ($einh[$count]['off']+($einh[$count]['off']/10*$o_fuehrung))*$offender[$type];
	$o_def += ($einh[$count]['def']+($einh[$count]['def']/10*$o_fuehrung))*$offender[$type];
} while ( 15 > $count );

if ($o_anz == 0){ $content .= '&nbsp;&nbsp;&nbsp;<b>vernichtet</b><br />'; $vernichtet = 'o';}

$count = 0;
do {
	$count++;
	$type = 'einh'.$count;
	$defender[$type] = 0;
} while ( 15 > $count );

$defender_def['def5'] = 0 ;
$defender_def['def6'] = 0 ;
$defender_def['def7'] = 0 ;
$defender_def['def8'] = 0 ;
$defender_def['def9'] = 0 ;
$defender_def['def10'] = 0 ;

$i=0;
do { 
	if ($soldiers['defender'][$i]['type'] == 1){$defender['einh1']++;}
	elseif ($soldiers['defender'][$i]['type'] == 2){$defender['einh2']++;}
	elseif ($soldiers['defender'][$i]['type'] == 3){$defender['einh3']++;}
	elseif ($soldiers['defender'][$i]['type'] == 4){$defender['einh4']++;}
	elseif ($soldiers['defender'][$i]['type'] == 5){$defender['einh5']++;}
	elseif ($soldiers['defender'][$i]['type'] == 6){$defender['einh6']++;}
	elseif ($soldiers['defender'][$i]['type'] == 7){$defender['einh7']++;}
	elseif ($soldiers['defender'][$i]['type'] == 8){$defender['einh8']++;}
	elseif ($soldiers['defender'][$i]['type'] == 9){$defender['einh9']++;}
	elseif ($soldiers['defender'][$i]['type'] == 10){$defender['einh10']++;}
	elseif ($soldiers['defender'][$i]['type'] == 11){$defender['einh11']++;}
	elseif ($soldiers['defender'][$i]['type'] == 12){$defender['einh12']++;}
	elseif ($soldiers['defender'][$i]['type'] == 13){$defender['einh13']++;}
	elseif ($soldiers['defender'][$i]['type'] == 14){$defender['einh14']++;}
	elseif ($soldiers['defender'][$i]['type'] == 15){$defender['einh15']++;}
	elseif ($soldiers['defender'][$i]['type'] == 1005){$defender_def['def5']++;}
	elseif ($soldiers['defender'][$i]['type'] == 1006){$defender_def['def6']++;}
	elseif ($soldiers['defender'][$i]['type'] == 1007){$defender_def['def7']++;}
	elseif ($soldiers['defender'][$i]['type'] == 1008){$defender_def['def8']++;}
	elseif ($soldiers['defender'][$i]['type'] == 1009){$defender_def['def9']++;}
	elseif ($soldiers['defender'][$i]['type'] == 1010){$defender_def['def10']++;}
	$i++;
} while ($soldiers['defender'][$i]);



$content .= '<br /><i>Verteidiger ('.$defender_user['name'].'):</i><br />';
if ($defender['einh1']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh1'].' '.$einh[1]['name'].'<br />';}
if ($defender['einh2']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh2'].' '.$einh[2]['name'].'<br />';}
if ($defender['einh3']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh3'].' '.$einh[3]['name'].'<br />';}
if ($defender['einh4']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh4'].' '.$einh[4]['name'].'<br />';}
if ($defender['einh5']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh5'].' '.$einh[5]['name'].'<br />';}
if ($defender['einh6']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh6'].' '.$einh[6]['name'].'<br />';}
if ($defender['einh7']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh7'].' '.$einh[7]['name'].'<br />';}
if ($defender['einh8']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh8'].' '.$einh[8]['name'].'<br />';}
if ($defender['einh9']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh9'].' '.$einh[9]['name'].'<br />';}
if ($defender['einh10']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh10'].' '.$einh[10]['name'].'<br />';}
if ($defender['einh11']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh11'].' '.$einh[11]['name'].'<br />';}
if ($defender['einh12']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh12'].' '.$einh[12]['name'].'<br />';}
if ($defender['einh13']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh13'].' '.$einh[13]['name'].'<br />';}
if ($defender['einh14']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh14'].' '.$einh[14]['name'].'<br />';}
if ($defender['einh15']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender['einh15'].' '.$einh[15]['name'].'<br />';}
if ($defender_def['def5']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def['def5'].' '.$def[5]['name'].'<br />';}
if ($defender_def['def6']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def['def6'].' '.$def[6]['name'].'<br />';}
if ($defender_def['def7']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def['def7'].' '.$def[7]['name'].'<br />';}
if ($defender_def['def8']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def['def8'].' '.$def[8]['name'].'<br />';}
if ($defender_def['def9']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def['def9'].' '.$def[9]['name'].'<br />';}
if ($defender_def['def10']) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def['def10'].' '.$def[10]['name'].'<br />';}

$count = 0;
$d_anz = 0;
$d_off = 0;
$d_def = 0;
do {
	$count++;
	$type = 'einh'.$count;
	$d_anz = $d_anz+$defender[$type];
	$d_off += ($einh[$count]['off']+($einh[$count]['off']/10*$d_fuehrung))*$defender[$type];
	$d_def += ($einh[$count]['def']+($einh[$count]['def']/10*$d_fuehrung))*$defender[$type];
} while ( 15 > $count );

$count = 4;
do {
	$count++;
	$type = 'def'.$count;
	$d_anz = $d_anz+$defender_def[$type];
	$d_off += ($def[$count]['off']+($def[$count]['off']/10*$d_fuehrung))*$defender_def[$type];
	$d_def += ($def[$count]['def']+($def[$count]['def']/10*$d_fuehrung))*$defender_def[$type];
} while ( 10 > $count );


if ($d_anz == 0) { $content .= '&nbsp;&nbsp;&nbsp;<b>vernichtet</b><br />'; $vernichtet = 'd';}

if ($inst['text']){
	$defender_def['def5'] += $inst[1005];
	$defender_def['def6'] += $inst[1006];
	$defender_def['def7'] += $inst[1007];
	$defender_def['def8'] += $inst[1008];
	$defender_def['def9'] += $inst[1009];
	$defender_def['def10'] += $inst[1010];
	$content .= "<br /><b>Folgende Verteidigungsanlagen liessen sich reparieren:</b><br />";
	$content .= $inst['text']."<br />";
}

$count = 0;
if ($o_anz > 0)  { 
	do {
		$count++;
		$type = 'einh'.$count;
		if ($offender[$type] > 0) {$space += ($offender[$type]*$einh[$count]['space']);}
	} while ( 14 > $count );	
	
	$ressis        = ressistand($d_omni);
	$ressis_old    = $ressis;
	$ausbeute      = rand ( 40, 90 );
	$ressisgesammt = $ressis['eisen']+$ressis['titan']+$ressis['oel']+$ressis['uran']+$ressis['gold']+$ressis['chanje'];
	
	$ressis['eisen']  - ($gebaeude['rohstofflager']*100+500) >= 0 ? $ressis['eisen']  -= ($gebaeude['rohstofflager']*100+500) : $ressis['eisen'] = 0;
	$ressis['titan']  - ($gebaeude['rohstofflager']*100+500) >= 0 ? $ressis['titan']  -= ($gebaeude['rohstofflager']*100+500) : $ressis['titan'] = 0;
	$ressis['oel']    - ($gebaeude['rohstofflager']*100+500) >= 0 ? $ressis['oel']    -= ($gebaeude['rohstofflager']*100+500) : $ressis['oel'] = 0;
	$ressis['uran']   - ($gebaeude['rohstofflager']*100+500) >= 0 ? $ressis['uran']   -= ($gebaeude['rohstofflager']*100+500) : $ressis['uran'] = 0;
	$ressis['gold']   - ($gebaeude['rohstofflager']*100+500) >= 0 ? $ressis['gold']   -= ($gebaeude['rohstofflager']*100+500) : $ressis['gold'] = 0;
	$ressis['chanje'] - ($gebaeude['rohstofflager']*100+500) >= 0 ? $ressis['chanje'] -= ($gebaeude['rohstofflager']*100+500) : $ressis['chanje'] = 0;
	
	$pluenderung['eisen']  = number_format(($ressis['eisen']/100*$ausbeute),0,'','');
	$pluenderung['titan']  = number_format(($ressis['titan']/100*$ausbeute),0,'','');
	$pluenderung['oel']    = number_format(($ressis['oel']/100*$ausbeute),0,'','');
	$pluenderung['uran']   = number_format(($ressis['uran']/100*$ausbeute),0,'','');
	$pluenderung['gold']   = number_format(($ressis['gold']/100*$ausbeute),0,'','');
	$pluenderung['chanje'] = number_format(($ressis['chanje']/100*$ausbeute),0,'','');
	
	$free = $space;
	
	$pluendern[0]['name'] = 'eisen';
	$pluendern[1]['name'] = 'titan';
	$pluendern[2]['name'] = 'oel';
	$pluendern[3]['name'] = 'uran';
	$pluendern[4]['name'] = 'gold';
	$pluendern[5]['name'] = 'chanje';
	
	$max                 = number_format(rand ( 25, 90 ),0,'','');
	$max                 = $space / 100 * $max;
	
	do {
		$rand = rand(0,2);
		if ( $pluenderung[$pluendern[$rand]['name']] > $max) { $pluenderung[$pluendern[$rand]['name']] = $max; };
		if ( count( $pluendern ) > 3 ) { $name = $pluendern[$rand]['name']; unset ( $pluendern[$rand] ); sort( $pluendern ); }
		else { $rand = rand( 0, ( count( $pluendern )-1 ) ); $name = $pluendern[$rand]['name']; unset ( $pluendern[$rand] ); sort( $pluendern ); } 
		if (($free-$pluenderung[$name]) >= 0) { $free -= $pluenderung[$name]; } else { $pluenderung[$name] = $free; $free = 0; }
	} while (count( $pluendern ) > 0);
	
	$content .= '<br /><b><u>Pl&uuml;nderung:</u></b><br /><br />Maximale Gesamtzuladung: '.$space.'<br />Maximale Ausbeute: '.$ausbeute.'%<br />';
	$content .= 'Eisen: '.number_format($pluenderung['eisen'],0).'<br />';
	$content .= 'Titan: '.number_format($pluenderung['titan'],0).'<br />';
	$content .= 'Oel: '.number_format($pluenderung['oel'],0).'<br />';
	$content .= 'Uran: '.number_format($pluenderung['uran'],0).'<br />';
	$content .= 'Gold: '.number_format($pluenderung['gold'],0).'<br />';
	$content .= 'Chanje: '.number_format($pluenderung['chanje'],0).'<br />';
	$content .= 'Restplatz: '.number_format($free,0).'<br />';
	
	$ressis_old['eisen']  -= $pluenderung['eisen'];
	$ressis_old['titan']  -= $pluenderung['titan'];
	$ressis_old['oel']    -= $pluenderung['oel'];
	$ressis_old['uran']   -= $pluenderung['uran'];
	$ressis_old['gold']   -= $pluenderung['gold'];
	$ressis_old['chanje'] -= $pluenderung['chanje'];


	$select = "UPDATE `ressis` SET `eisen` = '".$ressis_old['eisen']."', `titan` = '".$ressis_old['titan']."', `oel` = '".$ressis_old['oel']."', `uran` = '".$ressis_old['uran']."', `gold` = '".$ressis_old['gold']."', `chanje` = '".$ressis_old['chanje']."' WHERE `omni` = '".$d_omni."' LIMIT 1;"; 
	mysql_query($select);
		
	$select = "UPDATE `missionen` SET `einh1` = '".$offender['einh1']."', `einh2` = '".$offender['einh2']."', `einh3` = '".$offender['einh3']."', `einh4` = '".$offender['einh4']."', `einh5` = '".$offender['einh5']."', `einh6` = '".$offender['einh6']."', `einh7` = '".$offender['einh7']."',  `einh8` = '".$offender['einh8']."', `einh9` = '".$offender['einh9']."', `einh10` = '".$offender['einh10']."',  `einh11` = '".$offender['einh11']."', `einh12` = '".$offender['einh12']."', `einh13` = '".$offender['einh13']."', `einh14` = '".$offender['einh14']."', `einh15` = '".$offender['einh15']."', `eisen` = '".$pluenderung['eisen']."', `titan` = '".$pluenderung['titan']."', `oel` = '".$pluenderung['oel']."', `uran` = '".$pluenderung['uran']."', `gold` = '".$pluenderung['gold']."', `chanje` = '".$pluenderung['chanje']."' WHERE `id` = '".$id."' LIMIT 1;";
} else { $select = "DELETE FROM `missionen` WHERE `id` = '".$id."' LIMIT 1 ;"; }

mysql_query($select);

$content .= '<br />Durch den Kampf entstandene Tr&uuml;mmer: '.$tf_eisen.' Eisen '.$tf_titan.' Titan. <br />';

// supporterstats
$i=0;
do {
	$i++;
	if (!$vo[$i]){$vo[$i]=0;}
	if (!$vd[$i]){$vd[$i]=0;}
	// printf ($vo[$i]." / ".$vd[$i]."\n");
} while ($i<15);

mysql_query("UPDATE `stats` SET `dk1` = dk1 + ".$vo[1].", `dk2` = dk2 + ".$vo[2].", `dk3` = dk3 + ".$vo[3].", `dk4` = dk4 + ".$vo[4].", `dk5` = dk5 + ".$vo[5].", `dk6` = dk6 + ".$vo[6].", `dk7` = dk7 + ".$vo[7].", `dk8` = dk8 + ".$vo[8].", `dk9` = dk9 + ".$vo[9].", `dk10` = dk10 + ".$vo[10].", `dk11` = dk11 + ".$vo[11].", `dk12` = dk12 + ".$vo[12].", `dk13` = dk13 + ".$vo[13].", `dk14` = dk14 + ".$vo[14].", `dk15` = dk15 + ".$vo[15]." WHERE `id` = ".$d_omni.";");
mysql_query("UPDATE `stats` SET `vk1` = vk1 + ".$vd[1].", `vk2` = vk2 + ".$vd[2].", `vk3` = vk3 + ".$vd[3].", `vk4` = vk4 + ".$vd[4].", `vk5` = vk5 + ".$vd[5].", `vk6` = vk6 + ".$vd[6].", `vk7` = vk7 + ".$vd[7].", `vk8` = vk8 + ".$vd[8].", `vk9` = vk9 + ".$vd[9].", `vk10` = vk10 + ".$vd[10].", `vk11` = vk11 + ".$vd[11].", `vk12` = vk12 + ".$vd[12].", `vk13` = vk13 + ".$vd[13].", `vk14` = vk14 + ".$vd[14].", `vk15` = vk15 + ".$vd[15]." WHERE `id` = ".$d_omni.";");
mysql_query("UPDATE `stats` SET `dk1` = dk1 + ".$vd[1].", `dk2` = dk2 + ".$vd[2].", `dk3` = dk3 + ".$vd[3].", `dk4` = dk4 + ".$vd[4].", `dk5` = dk5 + ".$vd[5].", `dk6` = dk6 + ".$vd[6].", `dk7` = dk7 + ".$vd[7].", `dk8` = dk8 + ".$vd[8].", `dk9` = dk9 + ".$vd[9].", `dk10` = dk10 + ".$vd[10].", `dk11` = dk11 + ".$vd[11].", `dk12` = dk12 + ".$vd[12].", `dk13` = dk13 + ".$vd[13].", `dk14` = dk14 + ".$vd[14].", `dk15` = dk15 + ".$vd[15]." WHERE `id` = ".$o_omni.";");
mysql_query("UPDATE `stats` SET `vk1` = vk1 + ".$vo[1].", `vk2` = vk2 + ".$vo[2].", `vk3` = vk3 + ".$vo[3].", `vk4` = vk4 + ".$vo[4].", `vk5` = vk5 + ".$vo[5].", `vk6` = vk6 + ".$vo[6].", `vk7` = vk7 + ".$vo[7].", `vk8` = vk8 + ".$vo[8].", `vk9` = vk9 + ".$vo[9].", `vk10` = vk10 + ".$vo[10].", `vk11` = vk11 + ".$vo[11].", `vk12` = vk12 + ".$vo[12].", `vk13` = vk13 + ".$vo[13].", `vk14` = vk14 + ".$vo[14].", `vk15` = vk15 + ".$vo[15]." WHERE `id` = ".$o_omni.";");
mysql_query("UPDATE `stats` SET `farm_eisen` = farm_eisen + ".$pluenderung['eisen'].", `farm_titan` = farm_titan + ".$pluenderung['titan'].", `farm_oel` = farm_oel + ".$pluenderung['oel'].", `farm_uran` = farm_uran + ".$pluenderung['uran'].", `farm_gold` = farm_gold + ".$pluenderung['gold']." WHERE `id` = '".$o_omni."' LIMIT 1 ;");
mysql_query("UPDATE `stats` SET `ripped_eisen` = ripped_eisen + ".$pluenderung['eisen'].", `ripped_titan` = ripped_titan + ".$pluenderung['titan'].", `ripped_oel` = ripped_oel + ".$pluenderung['oel'].", `ripped_uran` = ripped_uran + ".$pluenderung['uran'].", `ripped_gold` = ripped_gold + ".$pluenderung['gold']." WHERE `id` = '".$d_omni."' LIMIT 1 ;");

// kampfpunkte
$kp_o = (($vo['eisen']+$vo['titan']+$vo['oel']+$vo['uran']+$vo['gold']+($vo['chanje']*25))/100);
$kp_d = (($vd['eisen']+$vd['titan']+$vd['oel']+$vd['uran']+$vd['gold']+($vd['chanje']*25))/100);

if ($vernichtet == 'o' and $kp_d < 0){ $kp_d = 0; }
if ($vernichtet == 'd' and $kp_o < 0){ $kp_o = 0; }

$content .= 'Kampfpunkte Angreifer: '.$kp_o.' <br />';
$content .= 'Kampfpunkte Verteidiger: '.$kp_d.' <br />';

if ($vernichtet == 'd'){ 
	$user = mysql_query("SELECT timestamp FROM `user` WHERE `omni` = '".$d_omni."' LIMIT 1;");
	$user = mysql_fetch_array($user);
	if ($user['timestamp'] < date('U')-3628800){
		$content .= '<br /><font class="red">Die Basis bei '.$d_omni.' wurde durch diesen Angriff komplett zerst&ouml;rt.</font><br />';
		deluser($d_omni);
	}
}

// clanwars
$r1 = mysql_query("SELECT * FROM `clans` WHERE `userid` =".$o_omni.";");
$r1 = @mysql_fetch_array($r1);
$r2 = mysql_query("SELECT * FROM `clans` WHERE `userid` =".$d_omni.";");
$r2 = @mysql_fetch_array($r2);

if ($r1 and $r2) {
	$r3 = mysql_query("SELECT * FROM `clanwars` WHERE `clan1` =".$r1['clanid']." AND `clan2` =".$r2['clanid']." AND `ended` = 0;");
	$r3 = @mysql_fetch_array($r3);
	$r4 = mysql_query("SELECT * FROM `clanwars` WHERE `clan2` =".$r1['clanid']." AND `clan1` =".$r2['clanid']." AND `ended` = 0;");
	$r4 = @mysql_fetch_array($r4);	
	if ($r3) {
		$select = "UPDATE `clanwars` SET `kampfpunkte1` =`kampfpunkte1`+".$kp_o.", `kampfpunkte2` =`kampfpunkte2`+".$kp_d.", `ressis1` =`ressis1`+".($pluenderung['eisen']+$pluenderung['titan']+$pluenderung['oel']+$pluenderung['uran']+$pluenderung['gold']+($pluenderung['chanje']*1000)).", `ressis2` =`ressis2`-".($pluenderung['eisen']+$pluenderung['titan']+$pluenderung['oel']+$pluenderung['uran']+$pluenderung['gold']+($pluenderung['chanje']*1000))." WHERE `id` = '".$r3['id']."' LIMIT 1;";
		mysql_query($select);
	} elseif ($r4) {
		$select = "UPDATE `clanwars` SET `kampfpunkte1` =`kampfpunkte1`+".$kp_d.", `kampfpunkte2` =`kampfpunkte2`+".$kp_o.", `ressis1` =`ressis1`-".($pluenderung['eisen']+$pluenderung['titan']+$pluenderung['oel']+$pluenderung['uran']+$pluenderung['gold']+($pluenderung['chanje']*1000)).", `ressis2` =`ressis2`+".($pluenderung['eisen']+$pluenderung['titan']+$pluenderung['oel']+$pluenderung['uran']+$pluenderung['gold']+($pluenderung['chanje']*1000))." WHERE `id` = '".$r4['id']."' LIMIT 1;";
		mysql_query($select);
	}
}


$select = "UPDATE `user` SET `kampfpunkte` = kampfpunkte + ".$kp_o." WHERE `omni` = '".$o_omni."' LIMIT 1;";
mysql_query($select);
$select = "UPDATE `user` SET `kampfpunkte` = kampfpunkte + ".$kp_d." WHERE `omni` = '".$d_omni."' LIMIT 1;";
mysql_query($select);

	if (($tf_eisen + $tf_titan) > 7000){ 
		$chanje = rand(number_format((($tf_eisen + $tf_titan)/2000)),number_format((($tf_eisen + $tf_titan)/500),0,'',''));
		if ($vernichtet == 'o'){
			$content .= '<br /><b>F&uuml;r diesen Kampf, bekommt der Kommandant der Basis '.$d_omni.' nun '.$chanje.' Chanje als Anerkennung.<br /></b>';
			$target   = $d_omni;
		} 
		if ($vernichtet == 'd') {
			$content .= '<br /><b>F&uuml;r diesen Kampf, bekommt der Kommandant der Basis '.$o_omni.' nun '.$chanje.' Chanje als Anerkennung.<br /></b>';
			$target   = $o_omni;
		}
		
		$rand = rand(20,40);
		$select = "INSERT INTO `missionen` ( `id` , `type` , `start` , `ziel` , `started` , `ankunft` , `return` , `speed` , `parsed` , `einh1` , `einh2` , `einh3` , `einh4` , `einh5` , `einh6` , `einh7` , `einh8` , `einh9` , `einh10` , `einh11` , `einh12` , `einh13` , `einh14` , `einh15` , `eisen` , `titan` , `oel` , `uran` , `gold` , `chanje` ) VALUES ( '', '2', '0', '".$target."', '".date('U')."', '".(date('U')+($rand*60))."', '".(date('U')+20000)."', '666', '0', '', '', '', '', '', '', '', '', '', '', '', '', '', '10', '', '0', '0', '0', '0', '0', '".$chanje."' );";
		mysql_query($select);
	
		$eid = mysql_insert_id($dbh);
	
		$select = "INSERT INTO `events` ( `id` , `type` , `eid` , `date` ) VALUES ('', '1', '".$eid."', '".(date('U')+($rand*60))."');";
		$selectResult   = mysql_query($select);
	}

$select = "UPDATE `hangar` SET `einh1` = '".$defender['einh1']."', `einh2` = '".$defender['einh2']."', `einh3` = '".$defender['einh3']."', `einh4` = '".$defender['einh4']."', `einh5` = '".$defender['einh5']."', `einh6` = '".$defender['einh6']."', `einh7` = '".$defender['einh7']."',  `einh8` = '".$defender['einh8']."', `einh9` = '".$defender['einh9']."', `einh10` = '".$defender['einh10']."',  `einh11` = '".$defender['einh11']."', `einh12` = '".$defender['einh12']."', `einh13` = '".$defender['einh13']."', `einh14` = '".$defender['einh14']."', `einh15` = '".$defender['einh15']."' WHERE `omni` = '".$d_omni."' LIMIT 1;";
mysql_query($select);

$select = "UPDATE `defense` SET `def1` = '".$defender_def['def1']."', `def2` = '".$defender_def['def2']."', `def3` = '".$defender_def['def3']."', `def4` = '".$defender_def['def4']."', `def5` = '".$defender_def['def5']."', `def6` = '".$defender_def['def6']."', `def7` = '".$defender_def['def7']."',  `def8` = '".$defender_def['def8']."', `def9` = '".$defender_def['def9']."', `def10` = '".$defender_def['def10']."' WHERE `omni` = '".$d_omni."' LIMIT 1;";
mysql_query($select);


$select = "UPDATE `user` SET `tf_eisen` = '".($defender_user['tf_eisen']+$tf_eisen)."', `tf_titan` = '".($defender_user['tf_titan']+$tf_titan)."' WHERE `omni` = '".$defender_user['omni']."' LIMIT 1;";
mysql_query($select);
$content .= '<br /><br />';


$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Missionszentrum', '".$d_omni."', '".$offender['ankunft']."', '0', 'Angriff von ".$o_omni."', '".$content.$kampf."' );";
$result = mysql_query($select);
}
$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ( '', 'Missionszentrum', '".$o_omni."', '".$offender['ankunft']."', '0', 'Kampfbericht bei ".$d_omni."', '".$content.$kampf."' );";
$result = mysql_query($select);
}

function new_units_check($omni){
	$dbh = db_connect();

	include('einheiten_preise.php');
	include('def_preise.php');
	include('raketen_preise.php');
	
	// gebaeude
	$select = "SELECT * FROM `gebauede` WHERE `omni` = ".($omni).";";
	$result = mysql_query($select);
	$gebaeude  = mysql_fetch_array($result);
	
	// hangar inhalt
	$select = "SELECT * FROM `hangar` WHERE `omni` = ".($omni).";";
	$result = mysql_query($select);
	$hangar  = mysql_fetch_array($result);
	
	// defense inhalt
	$select = "SELECT * FROM `defense` WHERE `omni` = ".($omni).";";
	$result = mysql_query($select);
	$defense  = mysql_fetch_array($result);

	// raketensilo inhalt
	$select = "SELECT * FROM `raketen` WHERE `omni` = ".($omni).";";
	$result = mysql_query($select);
	$raketen = mysql_fetch_array($result);
	
	$select = "SELECT * FROM `fabrik` WHERE `omni` = '".$omni."' AND `fertigstellung` <=".date(U).";";
	$result = mysql_query($select);

	$row  = mysql_fetch_array($result);
	if ($row) {
		do {
			if ($row) { 
				if ($row['type'] == 1)      { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh1']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 2)  { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh2']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 3)  { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh3']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 4)  { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh4']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 5)  { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh5']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 6)  { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh6']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 7)  { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh7']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 8)  { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh8']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 9)  { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh9']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 10) { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh10']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 11) { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh11']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 12) { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh12']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 13) { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh13']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 14) { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh14']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 15) { $select = "UPDATE `hangar` SET `einh".$row['type']."` = '".($hangar['einh15']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }

				elseif ($row['type'] == 1001)  { $select = "UPDATE `defense` SET `def1` = '".($defense['def1']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 1002)  { $select = "UPDATE `defense` SET `def2` = '".($defense['def2']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 1003)  { $select = "UPDATE `defense` SET `def3` = '".($defense['def3']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 1004)  { $select = "UPDATE `defense` SET `def4` = '".($defense['def4']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 1005)  { $select = "UPDATE `defense` SET `def5` = '".($defense['def5']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 1006)  { $select = "UPDATE `defense` SET `def6` = '".($defense['def6']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 1007)  { $select = "UPDATE `defense` SET `def7` = '".($defense['def7']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 1008)  { $select = "UPDATE `defense` SET `def8` = '".($defense['def8']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 1009)  { $select = "UPDATE `defense` SET `def9` = '".($defense['def9']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 1010)  { $select = "UPDATE `defense` SET `def10` = '".($defense['def10']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }

				elseif ($row['type'] == 2001)  { $select = "UPDATE `raketen` SET `einh1` = '".($raketen['einh1']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 2002)  { $select = "UPDATE `raketen` SET `einh2` = '".($raketen['einh2']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 2003)  { $select = "UPDATE `raketen` SET `einh3` = '".($raketen['einh3']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 2004)  { $select = "UPDATE `raketen` SET `einh4` = '".($raketen['einh4']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 2005)  { $select = "UPDATE `raketen` SET `einh5` = '".($raketen['einh5']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				elseif ($row['type'] == 2006)  { $select = "UPDATE `raketen` SET `einh6` = '".($raketen['einh6']+1)."' WHERE `omni` = '".$omni."' LIMIT 1 ;"; }
				
				
				if ($row['type'] >= 1 and $row['type'] <= 15) {
					$count = 0;
					do {
						$count++;
						$type = 'einh'.$count;
						$used = $used+($hangar[$type]*$einh[$count]['size']);
					} while ( 14 >= $count );
				
					if (($used+($einh[$row['type']]['size'])) <= ($gebaeude['hangar'] * 25)){
						$query = mysql_query($select);
					} else { 
						$type = $row['type'];
						$msg = 'Durch mangelnde Hangarkapazit&auml;t hast du eine Einheit vom Typ '.$einh[$type]['name'].' verloren.';
						$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', 'Fabrik', '".$omni."', '".$row['fertigstellung']."', '0', 'mangelnde Hangarkapazit&auml;t', '".$msg."');";
						$query = mysql_query($select);
					}
				}
				
				if ($row['type'] >= 1001 and $row['type'] <= 1011) { 
					// bestehende def anlagen
					$select2 = "SELECT * FROM `defense` WHERE `omni` = ".($omni).";";
					$query = mysql_query($select2);
					$defense  = mysql_fetch_array($query);
					$used = 0;
					$count = 0;
					do {
						$count++;
						$type = 'def'.$count;
						if ($count <= 4) {$used = $used+$defense[$type]*2;}
						else {$used = $used+$defense[$type];}
					} while ( 10 > $count );
				
					if ($used < ($gebaeude['basis']*4)){
						$query = mysql_query($select);
					} else { 
						$type = $row['type'];
						$msg = 'Durch mangelnden Basisausbau hast du eine Verteidigungsanlage vom Typ '.($def[$type-1000]['name']).' verloren.';
						$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', 'Fabrik', '".$omni."', '".$row['fertigstellung']."', '0', 'mangelnder Basisausbau', '".$msg."');";
						$query = mysql_query($select);
					}
					
				}

				if ($row['type'] >= 2001 and $row['type'] <= 2006) {
					$count = 0;
					do {
						$count++;
						$type = 'einh'.$count;
						$used = $used+$raketen[$type];
					} while ( 6 >= $count );
				
					if ($used < ($gebaeude['raketensilo'] * 5)){
						$query = mysql_query($select);
					} else { 
						$type = $row['type']-2000;
						$msg = 'Durch mangelnde Raketensilokapazit&auml;t hast du eine Rakete vom Typ '.$rak[$type]['name'].' verloren.';
						$select = "INSERT INTO `berichte` ( `id` , `from` , `to` , `timestamp` , `gelesen` , `subject` , `text` ) VALUES ('', 'Fabrik', '".$omni."', '".$row['fertigstellung']."', '0', 'mangelnde Raketensilokapazit&auml;t', '".$msg."');";
						$query = mysql_query($select);
					}
				}
				
	
				$used = 0; $count = 0;
				$select = "DELETE FROM `fabrik` WHERE `id` = '".$row['id']."' AND `omni` = '".$omni."' AND `fertigstellung` =".$row['fertigstellung']." LIMIT 1;";
				mysql_query($select);
			}
			$row  = mysql_fetch_array($result);
			$select = "SELECT * FROM `hangar` WHERE `omni` = ".($omni).";";
			$result2 = mysql_query($select);
			$hangar  = mysql_fetch_array($result2);

			// bestehende def anlagen
 			$select2 = "SELECT * FROM `defense` WHERE `omni` = ".($omni).";";
			$query = mysql_query($select2);
			$defense  = mysql_fetch_array($query);
					
			// raketensilo inhalt
			$select = "SELECT * FROM `raketen` WHERE `omni` = ".($omni).";";
//			$result = mysql_query($select);
//			$raketen = mysql_fetch_array($result);
						
		} while ($row);
	}
	return $hangar;
}

function gebaeude($omni){
	// gebaudepreise:
	include 'gebaeude_preise.php';
	
	// mit datenbank verbinden
	$dbh = db_connect();

	$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$omni."';";
	$selectResult   = mysql_query($select);
	$row = mysql_fetch_array($selectResult);

	$row['basis']++;
	$row['forschungsanlage']++;
	$row['fabrik']++;
	$row['raketensilo']++;
	$row['nbz']++;
	$row['hangar']++;
	$row['fahrwege']++;
	$row['missionszentrum']++;
	$row['agentenzentrum']++;
	$row['raumstation']++;
	$row['rohstofflager']++;
	$row['eisenmine']++;
	$row['titanmine']++;
	$row['oelpumpe']++;
	$row['uranmine']++;

	if ($row['nextbasis'] <= date(U) AND $row['nextbasis'] != 0){
		$select = "UPDATE `gebauede` SET `basis` = '".$row['basis']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextbasis'] = 0;
		$row['basis']++;
	}
	elseif ($row['nextforschungsanlage'] <= date(U) AND $row['nextforschungsanlage'] != 0){
		$select = "UPDATE `gebauede` SET `forschungsanlage` = '".$row['forschungsanlage']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
	}
	elseif ($row['nextfabrik'] <= date(U) AND $row['nextfabrik'] != 0){
		$select = "UPDATE `gebauede` SET `fabrik` = '".$row['fabrik']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextfabrik'] = 0;
		$row['fabrik']++;
	}
	elseif ($row['nextraketensilo'] <= date(U) AND $row['nextraketensilo'] != 0){
		$select = "UPDATE `gebauede` SET `raketensilo` = '".$row['raketensilo']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextraketensilo'] = 0;
		$row['raketensilo']++;
	}
	elseif ($row['nextnbz'] <= date(U) AND $row['nextnbz'] != 0){
		$select = "UPDATE `gebauede` SET `nbz` = '".$row['nbz']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextnbz'] = 0;
		$row['nbz']++;
	}
	elseif ($row['nexthangar'] <= date(U) AND $row['nexthangar'] != 0){
		$select = "UPDATE `gebauede` SET `hangar` = '".$row['hangar']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nexthangar'] = 0;
		$row['hangar']++;
	}
	elseif ($row['nextfahrwege'] <= date(U) AND $row['nextfahrwege'] != 0){
		$select = "UPDATE `gebauede` SET `fahrwege` = '".$row['fahrwege']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextfahrwege'] = 0;
		$row['fahrwege']++;
	}
	elseif ($row['nextmissionszentrum'] <= date(U) AND $row['nextmissionszentrum'] != 0){
		$select = "UPDATE `gebauede` SET `missionszentrum` = '".$row['missionszentrum']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextmissionszentrum'] = 0;
		$row['missionszentrum']++;
	}
	elseif ($row['nextagentenzentrum'] <= date(U) AND $row['nextagentenzentrum'] != 0){
		$select = "UPDATE `gebauede` SET `agentenzentrum` = '".$row['agentenzentrum']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextagentenzentrum'] = 0;
		$row['agentenzentrum']++;
	}
	elseif ($row['nextraumstation'] <= date(U) AND $row['nextraumstation'] != 0){
		$select = "UPDATE `gebauede` SET `raumstation` = '".$row['raumstation']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextraumstation'] = 0;
		$row['raumstation']++;
	}
	elseif ($row['nextrohstofflager'] <= date(U) AND $row['nextrohstofflager'] != 0){
		$select = "UPDATE `gebauede` SET `rohstofflager` = '".$row['rohstofflager']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextrohstofflager'] = 0;
		$row['rohstofflager']++;
	}
	elseif ($row['nexteisenmine'] <= date(U) AND $row['nexteisenmine'] != 0){
		$select = "UPDATE `gebauede` SET `eisenmine` = '".$row['eisenmine']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nexteisenmine'] = 0;
		$row['eisenmine']++;
	}
	elseif ($row['nexttitanmine'] <= date(U) AND $row['nexttitanmine'] != 0){
		$select = "UPDATE `gebauede` SET `titanmine` = '".$row['titanmine']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nexttitanmine'] = 0;
		$row['titanmine']++;
	}
	elseif ($row['nextoelpumpe'] <= date(U) AND $row['nextoelpumpe'] != 0){
		$select = "UPDATE `gebauede` SET `oelpumpe` = '".$row['oelpumpe']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextoelpumpe'] = 0;
		$row['oelpumpe']++;
	}
	elseif ($row['nexturanmine'] <= date(U) AND $row['nexturanmine'] != 0){
		$select = "UPDATE `gebauede` SET `uranmine` = '".$row['uranmine']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `gebauede` SET `nextbasis` = '0', `nexturanmine` = '0', `nextoelpumpe` = '0', `nexttitanmine` = '0', `nexteisenmine` = '0', `nextrohstofflager` = '0', `nextraumstation` = '0', `nextagentenzentrum` = '0', `nextmissionszentrum` = '0', `nexthangar` = '0', `nextfahrwege` = '0', `nextraketensilo` = '0', `nextforschungsanlage` = '0', `nextfabrik` = '0', `nextnbz` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nexturanmine'] = 0;
		$row['uranmine']++;
	}
}

function forschung($omni){
	$dbh = db_connect();
	
	$select = "SELECT * FROM `forschungen` WHERE `omni` = '".$omni."';";
	$selectResult   = mysql_query($select);
	$row = mysql_fetch_array($selectResult);
	
	$row['panzerung']++;
	$row['reaktor']++;
	$row['panzerketten']++;
	$row['motor']++;
	$row['feuerwaffen']++;
	$row['raketen']++;
	$row['sprengstoff']++;
	$row['spionage']++;
	$row['fuehrung']++;
	$row['cyborgtechnik']++;
	$row['minen']++;
	$row['rad']++;
	
	if ($row['nextpanzerung'] <= date(U) AND $row['nextpanzerung'] != 0){
		$select = "UPDATE `forschungen` SET `panzerung` = '".$row['panzerung']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextpanzerung'] = 0;
		$row['panzerung']++;
	}
	elseif ($row['nextreaktor'] <= date(U) AND $row['nextreaktor'] != 0){
		$select = "UPDATE `forschungen` SET `reaktor` = '".$row['reaktor']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
	}
	elseif ($row['nextpanzerketten'] <= date(U) AND $row['nextpanzerketten'] != 0){
		$select = "UPDATE `forschungen` SET `panzerketten` = '".$row['panzerketten']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextpanzerketten'] = 0;
		$row['panzerketten']++;
	}
	elseif ($row['nextmotor'] <= date(U) AND $row['nextmotor'] != 0){
		$select = "UPDATE `forschungen` SET `motor` = '".$row['motor']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextmotor'] = 0;
		$row['motor']++;
	}
	elseif ($row['nextfeuerwaffen'] <= date(U) AND $row['nextfeuerwaffen'] != 0){
		$select = "UPDATE `forschungen` SET `feuerwaffen` = '".$row['feuerwaffen']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextfeuerwaffen'] = 0;
		$row['feuerwaffen']++;
	}
	elseif ($row['nextraketen'] <= date(U) AND $row['nextraketen'] != 0){
		$select = "UPDATE `forschungen` SET `raketen` = '".$row['raketen']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextraketen'] = 0;
		$row['raketen']++;
	}
	elseif ($row['nextsprengstoff'] <= date(U) AND $row['nextsprengstoff'] != 0){
		$select = "UPDATE `forschungen` SET `sprengstoff` = '".$row['sprengstoff']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextsprengstoff'] = 0;
		$row['sprengstoff']++;
	}
	elseif ($row['nextspionage'] <= date(U) AND $row['nextspionage'] != 0){
		$select = "UPDATE `forschungen` SET `spionage` = '".$row['spionage']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextspionage'] = 0;
		$row['spionage']++;
	}
	elseif ($row['nextfuehrung'] <= date(U) AND $row['nextfuehrung'] != 0){
		$select = "UPDATE `forschungen` SET `fuehrung` = '".$row['fuehrung']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextfuehrung'] = 0;
		$row['fuehrung']++;
	}
	elseif ($row['nextcyborgtechnik'] <= date(U) AND $row['nextcyborgtechnik'] != 0){
		$select = "UPDATE `forschungen` SET `cyborgtechnik` = '".$row['cyborgtechnik']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextcyborgtechnik'] = 0;
		$row['cyborgtechnik']++;
	}
	elseif ($row['nextminen'] <= date(U) AND $row['nextminen'] != 0){
		$select = "UPDATE `forschungen` SET `minen` = '".$row['minen']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextminen'] = 0;
		$row['minen']++;
	}
	elseif ($row['nextrad'] <= date(U) AND $row['nextrad'] != 0){
		$select = "UPDATE `forschungen` SET `rad` = '".$row['rad']."' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$select = "UPDATE `forschungen` SET `nextpanzerung` = '0', `nextrad` = '0', `nextminen` = '0', `nextcyborgtechnik` = '0', `nextfuehrung` = '0', `nextspionage` = '0', `nextraketen` = '0', `nextsprengstoff` = '0', `nextmotor` = '0', `nextreaktor` = '0', `nextpanzerketten` = '0', `nextfeuerwaffen` = '0' WHERE `omni` = '".$omni."' LIMIT 1 ;";
		$selectResult   = mysql_query($select);
		$row['nextrad'] = 0;
		$row['rad']++;
	}
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

function db_connect() {
	include 'config.php';
	$dbh = mysql_pconnect($db_host, $db_user, $db_pass)
		or die("<h1>Could not connect</h1><b>Please check your configuration. The DB settings seem to be incorrect");
	mysql_select_db($db_database);
	return ($dbh);
}
?>