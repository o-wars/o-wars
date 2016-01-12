<?php 
session_name('SESSION');
session_start();

// Cache deaktivieren
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") ." GMT");
header("Pragma: no-cache");
header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate"); 

$_SESSION['code'] = rand(1000,99999);

$rand = rand(0,1);
if ($rand == 0){
	$font = imageloadfont('04b20s8.gdf');
	$x = rand(6,12); $y = rand(4,10);
} elseif ($rand == 1){
	$font = imageloadfont('addlg10.gdf');
	$x = rand(5,11); $y = rand(4,10);
}

if ($_SESSION['code'] < 10000){ $x += 10;}

//$bild = imagecreatefromgif("image".rand(1,40).".gif");
$bild = imagecreatefromgif("new".rand(1,5).".gif");
$farbe = imagecolorallocate($bild, 0, 0, 0);
imagestring($bild, $font, $x,$y, $_SESSION['code'], $farbe);
header("Content-type: image/png");
imagepng($bild);
?>
