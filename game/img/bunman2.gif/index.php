<?php

	$pics = glob('./pics/*');
	$pic  = $pics[rand(0,count($pics)-1)];
	
	$fp  = fopen($pic, r);
	$pic = fread($fp, filesize($pic));
	
	header('Content-Type: image/gif');
	echo $pic;
	
?>