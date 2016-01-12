<?PHP 
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

// check session
logincheck();

// variablentyp integer setzen
$_POST['bet'] = intval($_POST['bet']);

// mit datenbank verbinden
$dbh = db_connect();

$ressis = ressistand($_SESSION['user']['omni']);

$_SESSION['credit'] = $ressis['gold'];

// if no bet is set set 25
if (!$_SESSION['bet']){$_SESSION['bet'] = 25;}

// check if game has to be restarted (eg new round)
if ($_GET['action'] == "new" and $_SESSION['game'] != "running" and $ressis['gold'] < $_SESSION['bet']) { $_SESSION['game']='<font color="red">Nicht genug Gold vorhanden.</font>'; }
elseif ($_GET['action'] == "new" and $_SESSION['game'] != "running") {
	$_SESSION['credit'] -= $_SESSION['bet'];
	$_SESSION['cards'] = NULL;
	$_SESSION['hand'] = NULL;
	$_SESSION['bank'] = NULL;
	$_SESSION['game'] = 'running';
}
if ($_SESSION['game'] == "end") {
	$_SESSION['cards'] = NULL;
	$_SESSION['hand'] = NULL;
	$_SESSION['bank'] = NULL;
	$_SESSION['game'] = 'running';
}
// shuffle cards and add them to the session
if (!$_SESSION['cards']){
	$_SESSION['cards'] = shufflecards32();
	$_SESSION['hand'][1] = $_SESSION['cards'][1];
	$_SESSION['bank'][1] = $_SESSION['cards'][2];
	$_SESSION['card'] = 2;
}

if ($_GET['action'] == "hit" and $_SESSION['game'] == 'running') {
	$i = 0;
	do {
		$i++;
	} while ($_SESSION['hand'][$i]);
	$_SESSION['card']++;
	$_SESSION['hand'][$i] = $_SESSION['cards'][$_SESSION['card']];

	if (score() >= 22){
		$_SESSION['game']='<font color="red">busted</font>'; 
		$i = 0;
		do {
			$i++;
		} while ($_SESSION['bank'][$i]);
		
		do {
			$_SESSION['card']++;
			$_SESSION['bank'][$i] = $_SESSION['cards'][$_SESSION['card']];
			$i++;
			if (bankscore() >= 17) {$stand = 1;}
			elseif (bankscore() >= 16 and rand(1,2) == 1) {$stand = 1;}
//			if (bankscore() >= score()) {$stand = 1;}
		} while (!$stand);		
		
		if (bankscore() >= 22) {$_SESSION['game']='push'; $_SESSION['credit'] += 1*$_SESSION['bet'];}
	}
}

if ($_GET['action'] == "stand" and $_SESSION['game'] == 'running') {
	if (bankscore() < 17) {
		$i = 0;
		do {
			$i++;
		} while ($_SESSION['bank'][$i]);
		
		do {
			$_SESSION['card']++;
			$_SESSION['bank'][$i] = $_SESSION['cards'][$_SESSION['card']];
			$i++;
			if (bankscore() >= 17) {$stand = 1;}
			elseif (bankscore() >= 16 and rand(1,2) == 1) {$stand = 1;}
//			if (bankscore() >= score()) {$stand = 1;}
		} while (!$stand);
	}

	if (bankscore() >= 22){$_SESSION['game']='<font color="green">gewonnen</font>'; $_SESSION['credit'] += 2*$_SESSION['bet'];}
	elseif (score() == bankscore()){$_SESSION['game']='push'; $_SESSION['credit'] += 1*$_SESSION['bet'];}
	elseif (score() <= bankscore()){
		$_SESSION['game']='<font color="red">verloren</font>'; 
		//$_SESSION['credit'] = $_SESSION['credit']-$_SESSION['bet'];
	}
	elseif (score() >= bankscore()){$_SESSION['game']='<font color="green">gewonnen</font>'; $_SESSION['credit'] += 2*$_SESSION['bet'];}
	else {die ("something is going wrong");}
}

if ($_POST['action'] == "bet" and $_POST['bet'] >= 1 and $_POST['bet'] <= 250 and $_SESSION['game'] != 'running'){ 
	$_SESSION['bet'] = $_POST['bet']; 
}


if ($_SESSION['game'] != 'running'){$content = "<b>Deine Karten (".score()."):</b><br />".showcards()."<br /><b>Der Bank ihre Karten (".bankscore()."):</b><br />".showbankcards().'<br /><a href="?action=new&amp;'.SID.'">[Runde starten!]</a><br /><br /><form method="post" action="?'. SID .'" enctype="multipart/form-data">
	Der Einsatz betr&auml;gt: <input type="text" name="bet" value="'.$_SESSION['bet'].'" size="4" />
  	<input type="hidden" name="action" value="bet" />
	<input type="hidden" name="ok" value="1" />
	<input type="submit" name="submit" value="&auml;ndern" /></form>Minimum: 1 / Maximum: 250';} 
elseif ($_SESSION['card'] == 2) {$content = showcards()."<br /><b>Derzeitige Punkte: ".score().'</b><br /><a href="?action=hit&amp;'.SID.'">hit</a> / <a href="?action=stand&amp;'.SID.'">stand</a><br />';}
else {$content = showcards()."<br /><b>Derzeitige Punkte: ".score().'</b><br /><a href="?action=hit&amp;'.SID.'">hit</a> / <a href="?action=stand&amp;'.SID.'">stand</a>';}
$content = str_replace("%content%", $content ,template1());
$content = str_replace("%status%", $_SESSION['game'],$content);
$content = str_replace("%credits%", number_format($ressis['display_gold'],0,'','.'),$content);
$content = str_replace("%bet%", $_SESSION['bet'],$content);


mysql_query("UPDATE `ressis` SET `gold` = '".$_SESSION['credit']."' WHERE `omni` = '".$_SESSION['user']['omni']."' LIMIT 1;");		
$ressis = ressistand($_SESSION['user']['omni']);
	
// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);

echo tag2value('onload',$onload,template('head')).$status.$ressis['html'].$content.'</table>'.template('footer');


// debug
// show_vars();
function template1(){
	// standard xhtml template
	$template = '<br /><br />
	<table width="700px" border="1" cellspacing="0" cellpadding="0" class="standard">
	<tr align="center">
		<th><b>Das O-Wars Offiziers Casino</b></th>
	</tr>
		<tr style="height: 250px;">
			<td valign="top" align="center">
				<table width="99%" border="0" cellspacing="0" cellpadding="0">
					<td width="200px" valign="top">
						Einsatz: <b>%bet% Gold</b><br />
						Konto: <b>%credits% Gold</b><br />
						Status: <b>%status%</b><br />
						<a href="rules.html" target="_new">17+4 Regeln</a><br /><br />
						<b>Singleplayer</b> [<a href="cards_multi.php?'.SID.'">&auml;ndern</a>]
					</td>
					<td>
						<br />
						%content%
					</td>
			</td>
		</tr>
	</table>';
	return $template;
}

function showcards() {
	// shows the current hand
	$i = 0;
	do {
		$i++;
		if ($_SESSION['hand'][$i]) {
			$cards['hand'] .= '<img src="cards/'.$_SESSION['hand'][$i].'.gif">';
		}
	} while ($_SESSION['hand'][$i]);
	
	return $cards['hand'];
}

function showbankcards() {
	// shows the current bank hand
	$i = 0;
	do {
		$i++;
		if ($_SESSION['bank'][$i]) {
			$cards['bank'] .= '<img src="cards/'.$_SESSION['bank'][$i].'.gif">';
		}
	} while ($_SESSION['bank'][$i]);
	
	return $cards['bank'];
}

function score() {
	// shows the current hand
	$i = 0;
	do {
		$i++;
		if ($_SESSION['hand'][$i]) {
			$card   = explode("-", $_SESSION['hand'][$i]);
			if ($card[0] == 1) {
				if ($card[1] <= 10)		{$cards['score'] = $cards['score'] + $card[1];}
				elseif ($card[1] == 11) {$cards['score'] = $cards['score'] + 2;}
				elseif ($card[1] == 12) {$cards['score'] = $cards['score'] + 3;}
				elseif ($card[1] == 13) {$cards['score'] = $cards['score'] + 4;}
				elseif ($card[1] == 14) {$cards['score'] = $cards['score'] + 11;}
			}
			elseif ($card[0] == 2) {
				if ($card[1] <= 10)		{$cards['score'] = $cards['score'] + $card[1];}
				elseif ($card[1] == 11) {$cards['score'] = $cards['score'] + 2;}
				elseif ($card[1] == 12) {$cards['score'] = $cards['score'] + 3;}
				elseif ($card[1] == 13) {$cards['score'] = $cards['score'] + 4;}
				elseif ($card[1] == 14) {$cards['score'] = $cards['score'] + 11;}
			}
			elseif ($card[0] == 3) {
				if ($card[1] <= 10)		{$cards['score'] = $cards['score'] + $card[1];}
				elseif ($card[1] == 11) {$cards['score'] = $cards['score'] + 2;}
				elseif ($card[1] == 12) {$cards['score'] = $cards['score'] + 3;}
				elseif ($card[1] == 13) {$cards['score'] = $cards['score'] + 4;}
				elseif ($card[1] == 14) {$cards['score'] = $cards['score'] + 11;}
			}
			elseif ($card[0] == 4) {
				if ($card[1] <= 10)		{$cards['score'] = $cards['score'] + $card[1];}
				elseif ($card[1] == 11) {$cards['score'] = $cards['score'] + 2;}
				elseif ($card[1] == 12) {$cards['score'] = $cards['score'] + 3;}
				elseif ($card[1] == 13) {$cards['score'] = $cards['score'] + 4;}
				elseif ($card[1] == 14) {$cards['score'] = $cards['score'] + 11;}
			}
		}
	} while ($_SESSION['hand'][$i]);
	
	return $cards['score'];
}

function bankscore() {
	// shows the current hand
	$i = 0;
	do {
		$i++;
		if ($_SESSION['bank'][$i]) {
			$card   = explode("-", $_SESSION['bank'][$i]);
			if ($card[0] == 1) {
				if ($card[1] <= 10)		{$cards['score'] = $cards['score'] + $card[1];}
				elseif ($card[1] == 11) {$cards['score'] = $cards['score'] + 2;}
				elseif ($card[1] == 12) {$cards['score'] = $cards['score'] + 3;}
				elseif ($card[1] == 13) {$cards['score'] = $cards['score'] + 4;}
				elseif ($card[1] == 14) {$cards['score'] = $cards['score'] + 11;}
			}
			elseif ($card[0] == 2) {
				if ($card[1] <= 10)		{$cards['score'] = $cards['score'] + $card[1];}
				elseif ($card[1] == 11) {$cards['score'] = $cards['score'] + 2;}
				elseif ($card[1] == 12) {$cards['score'] = $cards['score'] + 3;}
				elseif ($card[1] == 13) {$cards['score'] = $cards['score'] + 4;}
				elseif ($card[1] == 14) {$cards['score'] = $cards['score'] + 11;}
			}
			elseif ($card[0] == 3) {
				if ($card[1] <= 10)		{$cards['score'] = $cards['score'] + $card[1];}
				elseif ($card[1] == 11) {$cards['score'] = $cards['score'] + 2;}
				elseif ($card[1] == 12) {$cards['score'] = $cards['score'] + 3;}
				elseif ($card[1] == 13) {$cards['score'] = $cards['score'] + 4;}
				elseif ($card[1] == 14) {$cards['score'] = $cards['score'] + 11;}
			}
			elseif ($card[0] == 4) {
				if ($card[1] <= 10)		{$cards['score'] = $cards['score'] + $card[1];}
				elseif ($card[1] == 11) {$cards['score'] = $cards['score'] + 2;}
				elseif ($card[1] == 12) {$cards['score'] = $cards['score'] + 3;}
				elseif ($card[1] == 13) {$cards['score'] = $cards['score'] + 4;}
				elseif ($card[1] == 14) {$cards['score'] = $cards['score'] + 11;}
			}
		}
	} while ($_SESSION['bank'][$i]);
	
	return $cards['score'];
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



function shufflecards52(){
	// shuffles the cards and stores them in the session
	$cards = 1;
	do {
		$number = rand(1,52);
		if ($number == 1 AND $a[$number] != 1)		{$deck[$cards]= "1-2" ;$cards++; $a[$number] = 1;}
		elseif ($number == 2 AND $a[$number] != 1)	{$deck[$cards]= "2-2" ;$cards++; $a[$number] = 1;}
		elseif ($number == 3 AND $a[$number] != 1)	{$deck[$cards]= "3-2" ;$cards++; $a[$number] = 1;}
		elseif ($number == 4 AND $a[$number] != 1)	{$deck[$cards]= "4-2" ;$cards++; $a[$number] = 1;}
		elseif ($number == 5 AND $a[$number] != 1)	{$deck[$cards]= "1-3" ;$cards++; $a[$number] = 1;}
		elseif ($number == 6 AND $a[$number] != 1)	{$deck[$cards]= "2-3" ;$cards++; $a[$number] = 1;}
		elseif ($number == 7 AND $a[$number] != 1)	{$deck[$cards]= "3-3" ;$cards++; $a[$number] = 1;}
		elseif ($number == 8 AND $a[$number] != 1)	{$deck[$cards]= "4-3" ;$cards++; $a[$number] = 1;}
		elseif ($number == 9 AND $a[$number] != 1)	{$deck[$cards]= "1-4" ;$cards++; $a[$number] = 1;}
		elseif ($number == 10 AND $a[$number] != 1)	{$deck[$cards]= "2-4" ;$cards++; $a[$number] = 1;}
		elseif ($number == 11 AND $a[$number] != 1)	{$deck[$cards]= "3-4" ;$cards++; $a[$number] = 1;}
		elseif ($number == 12 AND $a[$number] != 1)	{$deck[$cards]= "4-4" ;$cards++; $a[$number] = 1;}
		elseif ($number == 13 AND $a[$number] != 1)	{$deck[$cards]= "1-5" ;$cards++; $a[$number] = 1;}
		elseif ($number == 14 AND $a[$number] != 1)	{$deck[$cards]= "2-5" ;$cards++; $a[$number] = 1;}
		elseif ($number == 15 AND $a[$number] != 1)	{$deck[$cards]= "3-5" ;$cards++; $a[$number] = 1;}
		elseif ($number == 16 AND $a[$number] != 1)	{$deck[$cards]= "4-5" ;$cards++; $a[$number] = 1;}
		elseif ($number == 17 AND $a[$number] != 1)	{$deck[$cards]= "1-6" ;$cards++; $a[$number] = 1;}
		elseif ($number == 18 AND $a[$number] != 1)	{$deck[$cards]= "2-6" ;$cards++; $a[$number] = 1;}
		elseif ($number == 19 AND $a[$number] != 1)	{$deck[$cards]= "3-6" ;$cards++; $a[$number] = 1;}
		elseif ($number == 20 AND $a[$number] != 1)	{$deck[$cards]= "4-6" ;$cards++; $a[$number] = 1;}
		elseif ($number == 21 AND $a[$number] != 1)	{$deck[$cards]= "1-7" ;$cards++; $a[$number] = 1;}
		elseif ($number == 22 AND $a[$number] != 1)	{$deck[$cards]= "2-7" ;$cards++; $a[$number] = 1;}
		elseif ($number == 23 AND $a[$number] != 1)	{$deck[$cards]= "3-7" ;$cards++; $a[$number] = 1;}
		elseif ($number == 24 AND $a[$number] != 1)	{$deck[$cards]= "4-7" ;$cards++; $a[$number] = 1;}
		elseif ($number == 25 AND $a[$number] != 1)	{$deck[$cards]= "1-8" ;$cards++; $a[$number] = 1;}
		elseif ($number == 26 AND $a[$number] != 1)	{$deck[$cards]= "2-8" ;$cards++; $a[$number] = 1;}
		elseif ($number == 27 AND $a[$number] != 1)	{$deck[$cards]= "3-8" ;$cards++; $a[$number] = 1;}
		elseif ($number == 28 AND $a[$number] != 1)	{$deck[$cards]= "4-8" ;$cards++; $a[$number] = 1;}
		elseif ($number == 29 AND $a[$number] != 1)	{$deck[$cards]= "1-9" ;$cards++; $a[$number] = 1;}
		elseif ($number == 30 AND $a[$number] != 1)	{$deck[$cards]= "2-9" ;$cards++; $a[$number] = 1;}
		elseif ($number == 31 AND $a[$number] != 1)	{$deck[$cards]= "3-9" ;$cards++; $a[$number] = 1;}
		elseif ($number == 32 AND $a[$number] != 1)	{$deck[$cards]= "4-9" ;$cards++; $a[$number] = 1;}
		elseif ($number == 33 AND $a[$number] != 1)	{$deck[$cards]= "1-10" ;$cards++; $a[$number] = 1;}
		elseif ($number == 34 AND $a[$number] != 1)	{$deck[$cards]= "2-10" ;$cards++; $a[$number] = 1;}
		elseif ($number == 35 AND $a[$number] != 1)	{$deck[$cards]= "3-10" ;$cards++; $a[$number] = 1;}
		elseif ($number == 36 AND $a[$number] != 1)	{$deck[$cards]= "4-10" ;$cards++; $a[$number] = 1;}
		elseif ($number == 37 AND $a[$number] != 1)	{$deck[$cards]= "1-11" ;$cards++; $a[$number] = 1;}
		elseif ($number == 38 AND $a[$number] != 1)	{$deck[$cards]= "2-11" ;$cards++; $a[$number] = 1;}
		elseif ($number == 39 AND $a[$number] != 1)	{$deck[$cards]= "3-11" ;$cards++; $a[$number] = 1;}
		elseif ($number == 40 AND $a[$number] != 1)	{$deck[$cards]= "4-11" ;$cards++; $a[$number] = 1;}
		elseif ($number == 41 AND $a[$number] != 1)	{$deck[$cards]= "1-12" ;$cards++; $a[$number] = 1;}
		elseif ($number == 42 AND $a[$number] != 1)	{$deck[$cards]= "2-12" ;$cards++; $a[$number] = 1;}
		elseif ($number == 43 AND $a[$number] != 1)	{$deck[$cards]= "3-12" ;$cards++; $a[$number] = 1;}
		elseif ($number == 44 AND $a[$number] != 1)	{$deck[$cards]= "4-12" ;$cards++; $a[$number] = 1;}
		elseif ($number == 45 AND $a[$number] != 1)	{$deck[$cards]= "1-13" ;$cards++; $a[$number] = 1;}
		elseif ($number == 46 AND $a[$number] != 1)	{$deck[$cards]= "2-13" ;$cards++; $a[$number] = 1;}
		elseif ($number == 47 AND $a[$number] != 1)	{$deck[$cards]= "3-13" ;$cards++; $a[$number] = 1;}
		elseif ($number == 48 AND $a[$number] != 1)	{$deck[$cards]= "4-13" ;$cards++; $a[$number] = 1;}
		elseif ($number == 49 AND $a[$number] != 1)	{$deck[$cards]= "1-14" ;$cards++; $a[$number] = 1;}
		elseif ($number == 50 AND $a[$number] != 1)	{$deck[$cards]= "2-14" ;$cards++; $a[$number] = 1;}
		elseif ($number == 51 AND $a[$number] != 1)	{$deck[$cards]= "3-14" ;$cards++; $a[$number] = 1;}
		elseif ($number == 52 AND $a[$number] != 1)	{$deck[$cards]= "4-14" ;$cards++; $a[$number] = 1;}
	} while ($cards <= 52);
	
	return $deck;
}

?>