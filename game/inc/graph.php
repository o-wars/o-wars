<?php 

/*
for ($i=0;$i<=(10);$i++){
	
	$y[$i] = (1000/5+((1000/20)*$i))/2;
	
}

$date = time();

for ($i=0;$i<=(10);$i++){
	
	$x[$i] = $date + 3600*$i;
	
}

drawgraph($x, $y, 450, 350, "Kampfpunkte fuer UBL: ", 'Kampfpunkte zur Uhrzeit');
*/

function drawgraph($filename, $datax, $datay, $w, $h, $desc, $desc_x="", $desc_y="") {
	
	//header('Content-Type: image/gif');

	$sort = $datay;
	rsort($sort, SORT_NUMERIC);
	list($max) = $sort;
	$max = round($max / 100 * 105);
	
	if ($max == 0) {
	
		$max = 0.0000001;
	
	}
	
	sort($sort, SORT_NUMERIC);
	list($min) = $sort;
	
	$image = imagecreate($w, $h);
	imagecolorallocate($image, 88,88,88);
	$fg = imagecolorallocate($image, 255, 255, 255);
	$c  = imagecolorallocate($image, 133, 133, 133);
	$line  = imagecolorallocate($image, 255, 255, 0);
	$hline = imagecolorallocate($image, 133, 133, 133);
	$font  = imagecolorallocate($image, 255, 0, 0);


	// we want to spare 25 px for the y scale
	$w -= 25;

	// description
	imagestring   ($image, 2, $w-100, $h-15, '(c) by www.O-Wars.de', $c);
	imagestring   ($image, 3, $w/100*5, $h/100*95,  $desc_x, $fg);
	imagestringup ($image, 3, $w/100*1, $h/100*90,  $desc_y, $fg);

	$h -= 85;

	imagestring   ($image, 5, $w/100*2, $h/100, $desc, $fg);
	//imagestring   ($image, 2, $w-($w/100*5), $h-($h/100*10),  'xy0', $font);

	// width per entry
	$wpe = (($w-($w/100*10))/100)*(100/(count($datay)-1));

	imagesetthickness($image, 2);
	// 9 horizontal lines
	for ($i=1; $i < 10; $i++) {
	
		imageline($image, $w/100*5, (($h/100*10))-((($h/100*80)/100))+($i*(($h/100*80)/100*10)), $w/100*95, (($h/100*10))-((($h/100*80)/100))+($i*(($h/100*80)/100*10)), $hline);
		imagestring($image, 2, $w/100*95.5, (($h/100*10))-((($h/100*80)/100))+($i*(($h/100*80)/100*10)), round($max-$max/100*(10*$i)), $font);
	
	}
	imagestring($image, 2, $w/100*95.5, 20, $max, $font);
	imagesetthickness($image, 1);

	// initial position
	$pos['x'] = $w-($w/100*95);
	$pos['y'] = $h - ($h/100*10)-(($h/100*80)/100);

	// now we draw the graph
	$j=-1;
	foreach ($datay as $value) {

		$oldpos   = $pos;
	
		if (!$first) {
	
			$first = 1;
			$pos['x'] = $oldpos['x'];
			$pos['y'] = $h - ($h/100*10)-((($h/100*80)/100)*($value/($max/100)));
		
		} else {

			$pos['x'] = $oldpos['x'] + $wpe;
			$pos['y'] = $h - ($h/100*10)-((($h/100*80)/100)*($value/($max/100)));		
		
		}

		imageline($image, $pos['x'], $h/100*90, $pos['x'], $h/100*10, $hline);
		imageline($image, $oldpos['x'], $oldpos['y'], $pos['x'], $pos['y'], $line);

		$j++;
	
		imagefilledellipse($image, $pos['x'], $pos['y'], $w/100*0.6, $w/100*0.6, $font);
		imagestringup($image, 2, $pos['x'], $h/100*90+85, date('d.m.y H:00', $datax[$j]), $font);

	}

	imagesetthickness($image, 2);

	// horizontal lines
	imageline($image, $w-($w/100*95), $h-($h/100*10), $w-($w/100*5), $h-($h/100*10), $fg);
	imageline($image, $w-($w/100*95), $h-($h/100*90), $w-($w/100*5), $h-($h/100*90), $fg);

	// vertical lines
	imageline($image, $w-($w/100*95), $h-($h/100*10), $w-($w/100*95), $h-($h/100*90), $fg);
	imageline($image, $w-($w/100*5), $h-($h/100*10), $w-($w/100*5), $h-($h/100*90), $fg);

	imagegif($image, $filename);
}
?>