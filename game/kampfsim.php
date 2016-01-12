<?php
//////////////////////////////////
// beschuss.php                 //
//////////////////////////////////
// Letzte Aenderung:            // 
//               am: 30.09.2005 //
//   in der Version: 0.26a      //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";
include "einheiten_preise.php";
include "def_preise.php";

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
$content .= $status;

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION[user][omni]);
$content .= $ressis['html'];


$i = 0; do {$i++; $oeinh += $_POST['oe'.$i];} while ($i < 15);
$i = 0; do {$i++; $deinh += $_POST['de'.$i];} while ($i < 15);
$i = 4; do {$i++; $deinh += $_POST['dd'.$i];} while ($i < 10);
$i = 0;

if ($_POST['sim'] == 1 and $oeinh > 600) { $result = "Du kannst nur Angriffe mit 600 Einheiten jeweils planen."; }
elseif ($_POST['sim'] == 1 and $deinh > 600) { $result = "Du kannst nur Angriffe mit 600 Einheiten jeweils planen."; }
elseif ($_POST['sim'] == 1) { $result = kampf(); }
else {$result = "<center><b>noch kein Kampf berechnet</b></center>";}

$content .= template('kampfsim') ;
$content = tag2value('result', $result, $content);

$content = tag2value('o_f', $_POST['o_f'], $content);
$content = tag2value('d_f', $_POST['d_f'], $content);

do {
	$i++;
	$content = tag2value('einh'.$i, $einh[$i]['name'], $content);
	$content = tag2value('oe'.$i, $_POST['oe'.$i], $content);
	$content = tag2value('de'.$i, $_POST['de'.$i], $content);
} while ($i < 15);
$i=4;
do {
	$i++;
	$content = tag2value('def'.$i, $def[$i]['name'], $content);
	$content = tag2value('dd'.$i, $_POST['dd'.$i], $content);
} while ($i < 10);
$i=0;

$content = tag2value("onload", $onload, $content);

echo $content.template('footer');

function kampf() {
include "einheiten_preise.php";
include "def_preise.php";

$o_fuehrung	= $_POST['o_f'];
$d_fuehrung	= $_POST['d_f'];
$i = 0; do {$i++; $ohangar += $_POST['oe'.$i]*$einh[$i]['size']; $offender['einh'.$i] = $_POST['oe'.$i];} while ($i < 15);
$i = 0; do {$i++; $dhangar += $_POST['de'.$i]*$einh[$i]['size']; $defender['einh'.$i] = $_POST['de'.$i];} while ($i < 15);
$i = 4; do {$i++; $defender_def['def'.$i] = $_POST['dd'.$i];} while ($i < 10);
	
$content .= '<i>Angreifer:</i><br />';
if ($offender[einh1]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh1].' '.$einh[1][name].'<br />';}
if ($offender[einh2]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh2].' '.$einh[2][name].'<br />';}
if ($offender[einh3]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh3].' '.$einh[3][name].'<br />';}
if ($offender[einh4]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh4].' '.$einh[4][name].'<br />';}
if ($offender[einh5]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh5].' '.$einh[5][name].'<br />';}
if ($offender[einh6]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh6].' '.$einh[6][name].'<br />';}
if ($offender[einh7]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh7].' '.$einh[7][name].'<br />';}
if ($offender[einh8]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh8].' '.$einh[8][name].'<br />';}
if ($offender[einh9]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh9].' '.$einh[9][name].'<br />';}
if ($offender[einh10]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh10].' '.$einh[10][name].'<br />';}
if ($offender[einh11]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh11].' '.$einh[11][name].'<br />';}
if ($offender[einh12]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh12].' '.$einh[12][name].'<br />';}
if ($offender[einh13]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh13].' '.$einh[13][name].'<br />';}
if ($offender[einh14]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh14].' '.$einh[14][name].'<br />';}
if ($offender[einh15]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh15].' '.$einh[15][name].'<br />';}

do {
	$count++;
	$type = 'einh'.$count;
	$o_anz = $o_anz+$offender[$type];
	$o_off += ($einh[$count][off]+($einh[$count][off]/10*$o_fuehrung))*$offender[$type];
	$o_def += ($einh[$count][def]+($einh[$count][def]/10*$o_fuehrung))*$offender[$type];
} while ( 15 > $count );

$count = 4;
do {
	$count++;
	$type = 'def'.$count;
	$d_anz = $d_anz+$defender_def[$type];
	$d_off += ($def[$count][off]+($def[$count][off]/10*$d_fuehrung))*$defender_def[$type];
	$d_def += ($def[$count][def]+($def[$count][def]/10*$d_fuehrung))*$defender_def[$type];
} while ( 10 > $count );

$content .= '<br />F&uuml;hrungsbonus: '.($o_fuehrung*10).'%<br />';
$content .= 'Hangarplatz: '.$ohangar.'<br />';
$content .= 'Einheiten: '.$o_anz.'<br />';
$content .= 'Angriffswert: <b>'.($o_off).'</b><br />';
$content .= 'Verteidigungswert: <b>'.($o_def).'</b><br />';

$content .= '<br /><i>Verteidiger:</i><br />';
if ($defender[einh1]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh1].' '.$einh[1][name].'<br />';}
if ($defender[einh2]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh2].' '.$einh[2][name].'<br />';}
if ($defender[einh3]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh3].' '.$einh[3][name].'<br />';}
if ($defender[einh4]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh4].' '.$einh[4][name].'<br />';}
if ($defender[einh5]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh5].' '.$einh[5][name].'<br />';}
if ($defender[einh6]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh6].' '.$einh[6][name].'<br />';}
if ($defender[einh7]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh7].' '.$einh[7][name].'<br />';}
if ($defender[einh8]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh8].' '.$einh[8][name].'<br />';}
if ($defender[einh9]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh9].' '.$einh[9][name].'<br />';}
if ($defender[einh10]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh10].' '.$einh[10][name].'<br />';}
if ($defender[einh11]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh11].' '.$einh[11][name].'<br />';}
if ($defender[einh12]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh12].' '.$einh[12][name].'<br />';}
if ($defender[einh13]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh13].' '.$einh[13][name].'<br />';}
if ($defender[einh14]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh14].' '.$einh[14][name].'<br />';}
if ($defender[einh15]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh15].' '.$einh[15][name].'<br />';}
if ($defender_def[def5]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def[def5].' '.$def[5][name].'<br />';}
if ($defender_def[def6]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def[def6].' '.$def[6][name].'<br />';}
if ($defender_def[def7]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def[def7].' '.$def[7][name].'<br />';}
if ($defender_def[def8]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def[def8].' '.$def[8][name].'<br />';}
if ($defender_def[def9]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def[def9].' '.$def[9][name].'<br />';}
if ($defender_def[def10]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def[def10].' '.$def[10][name].'<br />';}

$count = 0;
do {
	$count++;
	$type = 'einh'.$count;
	$d_anz = $d_anz+$defender[$type];
	$d_off += ($einh[$count][off]+($einh[$count][off]/10*$d_fuehrung))*$defender[$type];
	$d_def += ($einh[$count][def]+($einh[$count][def]/10*$d_fuehrung))*$defender[$type];
} while ( 15 > $count );

$content .= '<br />F&uuml;hrungsbonus: '.($d_fuehrung*10).'%<br />';
$content .= 'Hangarplatz: '.$dhangar.'<br />';
$content .= 'Einheiten: '.$d_anz.'<br />';
$content .= 'Angriffswert: <b>'.($d_off).'</b><br />';
$content .= 'Verteidigungswert: <b>'.($d_def).'</b><br />';


$i=0;
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
// neuer kampfmod
do {
	$kampf .= '<br /><br /><i>Runde '.++$round.':</i><br /><br />';
	$count_offender = count( $soldiers['offender'] );
	$count_defender = count( $soldiers['defender'] );
	
	$trooper_offender = 0;
	$trooper_offended = 0;
	do {
		if ($trooper_offended > $count_defender){$trooper_offended = 0;}
			
		$soldiers['defender'][$trooper_offended]['def'] -= $soldiers['offender'][$trooper_offender]['off'];
		if ($soldiers['defender'][$trooper_offended]['name']) {$kampf .= 'Die angreifende Eh. '.$soldiers['offender'][$trooper_offender]['name'].' ('.$soldiers['offender'][$trooper_offender]['off'].'/'.$soldiers['offender'][$trooper_offender]['def'].') schiesst auf Eh. '.$soldiers['defender'][$trooper_offended]['name'].' ('.$soldiers['defender'][$trooper_offended]['off'].'/'.$soldiers['defender'][$trooper_offended]['def'].')<br />';}
		
		$trooper_offender++;
		$trooper_offended = rand(0, $count_defender);

	} while ( $trooper_offender < count( $soldiers['offender'] ) );

	$trooper_offender = 0;
	$trooper_offended = 0;
	do {
		if ($trooper_offended > $count_offender){$trooper_offended = 0;}
			
		$soldiers['offender'][$trooper_offended]['def'] -= $soldiers['defender'][$trooper_offender]['off'];
		if ($soldiers['offender'][$trooper_offended]['name']) {$kampf .= 'Die verteidigende Eh. '.$soldiers['defender'][$trooper_offender]['name'].' ('.$soldiers['defender'][$trooper_offender]['off'].'/'.$soldiers['defender'][$trooper_offender]['def'].') schiesst auf Eh '.$soldiers['offender'][$trooper_offended]['name'].' ('.$soldiers['offender'][$trooper_offended]['off'].'/'.$soldiers['offender'][$trooper_offended]['def'].')<br />';}
		
		$trooper_offender++;
		$trooper_offended = rand(0, $count_offender);

	} while ( $trooper_offender < count( $soldiers['defender'] ) );
	
	
	// unset kaputte defender 
	$trooper_offended = 0;
	$count = (count( $soldiers['defender'] )+1);
	do {
		if( $soldiers['defender'][$trooper_offended]['def'] <= 0 ) {
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
		if( $soldiers['offender'][$trooper_offended]['def'] <= 0 ) {
			// $v[$soldiers['offender'][$trooper_offended]['type']]++;
			$vo[$soldiers['offender'][$trooper_offended]['type']]++;
			unset( $soldiers['offender'][$trooper_offended] );
		}
		$trooper_offended++;
	} while ( $trooper_offended <= $count );
	sort( $soldiers['offender'] );
	
	//echo "---".count( $soldiers['offender'] )."/".count( $soldiers['defender'] )."\n";
	
} while ( count( $soldiers['offender'] ) > 0 and count( $soldiers['defender'] ) > 0 and $round < 100);
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
	if ($soldiers['offender'][$i]['type'] == 1){$offender[einh1]++;}
	elseif ($soldiers['offender'][$i]['type'] == 2){$offender[einh2]++;}
	elseif ($soldiers['offender'][$i]['type'] == 3){$offender[einh3]++;}
	elseif ($soldiers['offender'][$i]['type'] == 4){$offender[einh4]++;}
	elseif ($soldiers['offender'][$i]['type'] == 5){$offender[einh5]++;}
	elseif ($soldiers['offender'][$i]['type'] == 6){$offender[einh6]++;}
	elseif ($soldiers['offender'][$i]['type'] == 7){$offender[einh7]++;}
	elseif ($soldiers['offender'][$i]['type'] == 8){$offender[einh8]++;}
	elseif ($soldiers['offender'][$i]['type'] == 9){$offender[einh9]++;}
	elseif ($soldiers['offender'][$i]['type'] == 10){$offender[einh10]++;}
	elseif ($soldiers['offender'][$i]['type'] == 11){$offender[einh11]++;}
	elseif ($soldiers['offender'][$i]['type'] == 12){$offender[einh12]++;}
	elseif ($soldiers['offender'][$i]['type'] == 13){$offender[einh13]++;}
	elseif ($soldiers['offender'][$i]['type'] == 14){$offender[einh14]++;}
	elseif ($soldiers['offender'][$i]['type'] == 15){$offender[einh15]++;}
	$i++;
} while ($soldiers['offender'][$i]);

$content .= '<i>Angreifer:</i><br />';
if ($offender[einh1]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh1].' '.$einh[1][name].'<br />';}
if ($offender[einh2]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh2].' '.$einh[2][name].'<br />';}
if ($offender[einh3]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh3].' '.$einh[3][name].'<br />';}
if ($offender[einh4]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh4].' '.$einh[4][name].'<br />';}
if ($offender[einh5]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh5].' '.$einh[5][name].'<br />';}
if ($offender[einh6]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh6].' '.$einh[6][name].'<br />';}
if ($offender[einh7]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh7].' '.$einh[7][name].'<br />';}
if ($offender[einh8]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh8].' '.$einh[8][name].'<br />';}
if ($offender[einh9]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh9].' '.$einh[9][name].'<br />';}
if ($offender[einh10]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh10].' '.$einh[10][name].'<br />';}
if ($offender[einh11]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh11].' '.$einh[11][name].'<br />';}
if ($offender[einh12]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh12].' '.$einh[12][name].'<br />';}
if ($offender[einh13]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh13].' '.$einh[13][name].'<br />';}
if ($offender[einh14]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh14].' '.$einh[14][name].'<br />';}
if ($offender[einh15]) { $content .= '&nbsp;&nbsp;&nbsp;'.$offender[einh15].' '.$einh[15][name].'<br />';}

$count = 0;
$o_anz = 0;
$o_off = 0;
$o_def = 0;
do {
	$count++;
	$type = 'einh'.$count;
	$o_anz = $o_anz+$offender[$type];
	$o_off += ($einh[$count][off]+($einh[$count][off]/10*$o_fuehrung))*$offender[$type];
	$o_def += ($einh[$count][def]+($einh[$count][def]/10*$o_fuehrung))*$offender[$type];
} while ( 15 > $count );

if ($o_anz == 0){ $content .= '&nbsp;&nbsp;&nbsp;<b>vernichtet</b><br />'; $vernichtet = 'o';}

$count = 0;
do {
	$count++;
	$type = 'einh'.$count;
	$defender[$type] = 0;
} while ( 15 > $count );

$defender_def[def5] = 0 ;
$defender_def[def6] = 0 ;
$defender_def[def7] = 0 ;
$defender_def[def8] = 0 ;
$defender_def[def9] = 0 ;
$defender_def[def10] = 0 ;

$i=0;
do { 
	if ($soldiers['defender'][$i]['type'] == 1){$defender[einh1]++;}
	elseif ($soldiers['defender'][$i]['type'] == 2){$defender[einh2]++;}
	elseif ($soldiers['defender'][$i]['type'] == 3){$defender[einh3]++;}
	elseif ($soldiers['defender'][$i]['type'] == 4){$defender[einh4]++;}
	elseif ($soldiers['defender'][$i]['type'] == 5){$defender[einh5]++;}
	elseif ($soldiers['defender'][$i]['type'] == 6){$defender[einh6]++;}
	elseif ($soldiers['defender'][$i]['type'] == 7){$defender[einh7]++;}
	elseif ($soldiers['defender'][$i]['type'] == 8){$defender[einh8]++;}
	elseif ($soldiers['defender'][$i]['type'] == 9){$defender[einh9]++;}
	elseif ($soldiers['defender'][$i]['type'] == 10){$defender[einh10]++;}
	elseif ($soldiers['defender'][$i]['type'] == 11){$defender[einh11]++;}
	elseif ($soldiers['defender'][$i]['type'] == 12){$defender[einh12]++;}
	elseif ($soldiers['defender'][$i]['type'] == 13){$defender[einh13]++;}
	elseif ($soldiers['defender'][$i]['type'] == 14){$defender[einh14]++;}
	elseif ($soldiers['defender'][$i]['type'] == 15){$defender[einh15]++;}
	elseif ($soldiers['defender'][$i]['type'] == 1005){$defender_def[def5]++;}
	elseif ($soldiers['defender'][$i]['type'] == 1006){$defender_def[def6]++;}
	elseif ($soldiers['defender'][$i]['type'] == 1007){$defender_def[def7]++;}
	elseif ($soldiers['defender'][$i]['type'] == 1008){$defender_def[def8]++;}
	elseif ($soldiers['defender'][$i]['type'] == 1009){$defender_def[def9]++;}
	elseif ($soldiers['defender'][$i]['type'] == 1010){$defender_def[def10]++;}
	$i++;
} while ($soldiers['defender'][$i]);

$content .= '<br /><i>Verteidiger:</i><br />';
if ($defender[einh1]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh1].' '.$einh[1][name].'<br />';}
if ($defender[einh2]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh2].' '.$einh[2][name].'<br />';}
if ($defender[einh3]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh3].' '.$einh[3][name].'<br />';}
if ($defender[einh4]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh4].' '.$einh[4][name].'<br />';}
if ($defender[einh5]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh5].' '.$einh[5][name].'<br />';}
if ($defender[einh6]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh6].' '.$einh[6][name].'<br />';}
if ($defender[einh7]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh7].' '.$einh[7][name].'<br />';}
if ($defender[einh8]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh8].' '.$einh[8][name].'<br />';}
if ($defender[einh9]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh9].' '.$einh[9][name].'<br />';}
if ($defender[einh10]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh10].' '.$einh[10][name].'<br />';}
if ($defender[einh11]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh11].' '.$einh[11][name].'<br />';}
if ($defender[einh12]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh12].' '.$einh[12][name].'<br />';}
if ($defender[einh13]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh13].' '.$einh[13][name].'<br />';}
if ($defender[einh14]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh14].' '.$einh[14][name].'<br />';}
if ($defender[einh15]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender[einh15].' '.$einh[15][name].'<br />';}
if ($defender_def[def5]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def[def5].' '.$def[5][name].'<br />';}
if ($defender_def[def6]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def[def6].' '.$def[6][name].'<br />';}
if ($defender_def[def7]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def[def7].' '.$def[7][name].'<br />';}
if ($defender_def[def8]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def[def8].' '.$def[8][name].'<br />';}
if ($defender_def[def9]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def[def9].' '.$def[9][name].'<br />';}
if ($defender_def[def10]) { $content .= '&nbsp;&nbsp;&nbsp;'.$defender_def[def10].' '.$def[10][name].'<br />';}

$count = 0;
$d_anz = 0;
$d_off = 0;
$d_def = 0;
do {
	$count++;
	$type = 'einh'.$count;
	$d_anz = $d_anz+$defender[$type];
	$d_off += ($einh[$count][off]+($einh[$count][off]/10*$d_fuehrung))*$defender[$type];
	$d_def += ($einh[$count][def]+($einh[$count][def]/10*$d_fuehrung))*$defender[$type];
} while ( 15 > $count );

$count = 0;
do {
	$count++;
	$type = 'def'.$count;
	$d_anz = $d_anz+$defender_def[$type];
	$d_off += ($def[$count][off]+($def[$count][off]/10*$d_fuehrung))*$defender_def[$type];
	$d_def += ($def[$count][def]+($def[$count][def]/10*$d_fuehrung))*$defender_def[$type];
} while ( 10 > $count );


if ($d_anz == 0) { $content .= '&nbsp;&nbsp;&nbsp;<b>vernichtet</b><br />'; $vernichtet = 'd';}

$count = 0;

$kp_o = (($vo['eisen']+$vo['titan']+$vo['oel']+$vo['uran']+$vo['gold']+($vo['chanje']*25))/100);
$kp_d = (($vd['eisen']+$vd['titan']+$vd['oel']+$vd['uran']+$vd['gold']+($vd['chanje']*25))/100);

if ($vernichtet == 'o' and $kp_d < 0){ $kp_d = 0; }
if ($vernichtet == 'd' and $kp_o < 0){ $kp_o = 0; }

$content .= '<br />Tr&uuml;mmerfeld: '.$tf_eisen.' Eisen '.$tf_titan.' Titan. <br />';
$content .= 'Kampfpunkte Angreifer: '.$kp_o.' <br />';
$content .= 'Kampfpunkte Verteidiger: '.$kp_d.' <br />';
$content .= 'Dauer: '.$round.' Runden<br />';

	if (($tf_eisen + $tf_titan) > 7000){ 
		$chanje = rand(number_format((($tf_eisen + $tf_titan)/2000)),number_format((($tf_eisen + $tf_titan)/500),0,'',''));
		if ($o_anz == 0){
			$content .= '<br /><b>Verteidiger bekommt '.$chanje.' Chanje.<br /></b>';
			$target   = $d_omni;
		} 
		
		if ($d_anz == 0) {
			$content .= '<br /><b>Angreifer bekommt '.$chanje.' Chanje.<br /></b>';
			$target   = $o_omni;
		}
	}

$content .= '<br /><br />';
$content = str_replace('%onload%', $onload, $content);
return $content;
} 
?>