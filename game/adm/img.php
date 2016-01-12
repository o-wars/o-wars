<?php
//////////////////////////////////
// ad.php                       //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "admin.php";

// check session
logincheck();

// mit datenbank verbinden
$dbh = db_connect();

if ($_GET['ubl']) {
	$result = mysql_query("SELECT * FROM `logins` WHERE `userid` = '".$_GET['ubl']."';");
} else {
	$result = mysql_query("SELECT * FROM `logins`;");
}
//
$rows   = mysql_num_rows($result);
do {
	$row = mysql_fetch_array($result);
	$h[date('H',$row['time'])]++;
} while ($row);

$x=$h;

sort($x);

$i=0;
do {
	if ($x[$i] > $max){$max =$x[$i];}
	$i++;	
} while ($i < 24);

$bild = imagecreatefromgif("img/graph1.gif");
$farbe = imagecolorallocate($bild, 0, 0, 0);
imagestring($bild, 10, 5,228, 'Logins nach Uhrzeiten '.date('(d.m.Y - H:i)'), $farbe);
imagestring($bild, 4, 420,3, number_format($max,0,',','.'), $farbe);
imagestring($bild, 4, 420,52, number_format($max/4*3,0,',','.'), $farbe);
imagestring($bild, 4, 420,102, number_format($max/2,0,',','.'), $farbe);
imagestring($bild, 4, 420,152, number_format($max/4,0,',','.'), $farbe);
imagestring($bild, 4, 420,203, 0, $farbe);

$faktor = 200/$max;

$i=0;
do {
	if ($i/2 != number_format($i/2,0,'','')) {$farbe = imagecolorallocate($bild, 155, 0, 0);}
	else {$farbe = imagecolorallocate($bild, 0, 0, 255);}
	if ($i < 10){
		imageline($bild, 10+$i*17, 210, 10+($i)*17, 210-$h['0'.$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+1, 210, 10+($i)*17+1, 210-$h['0'.$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+2, 210, 10+($i)*17+2, 210-$h['0'.$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+3, 210, 10+($i)*17+3, 210-$h['0'.$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+4, 210, 10+($i)*17+4, 210-$h['0'.$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+5, 210, 10+($i)*17+5, 210-$h['0'.$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+6, 210, 10+($i)*17+6, 210-$h['0'.$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+7, 210, 10+($i)*17+7, 210-$h['0'.$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+8, 210, 10+($i)*17+8, 210-$h['0'.$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+9, 210, 10+($i)*17+9, 210-$h['0'.$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+10, 210, 10+($i)*17+10, 210-$h['0'.$i]*$faktor,$farbe); 		
		imagestring($bild, 4, 10+$i*17,212, $i, $farbe);
	} else {
		imageline($bild, 10+$i*17, 210, 10+($i)*17, 210-$h[$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+1, 210, 10+($i)*17+1, 210-$h[$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+2, 210, 10+($i)*17+2, 210-$h[$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+3, 210, 10+($i)*17+3, 210-$h[$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+4, 210, 10+($i)*17+4, 210-$h[$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+5, 210, 10+($i)*17+5, 210-$h[$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+6, 210, 10+($i)*17+6, 210-$h[$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+7, 210, 10+($i)*17+7, 210-$h[$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+8, 210, 10+($i)*17+8, 210-$h[$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+9, 210, 10+($i)*17+9, 210-$h[$i]*$faktor,$farbe); 		
		imageline($bild, 10+$i*17+10, 210, 10+($i)*17+10, 210-$h[$i]*$faktor,$farbe); 		
		imagestring($bild, 4, 7+$i*17,212, $i, $farbe);
	}
	$i++;	
} while ($i < 24);

header("Content-type: image/png");
imagepng($bild);
?>