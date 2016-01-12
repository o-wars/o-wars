<?PHP

include 'einheiten_preise.php';
include 'def_preise.php';

echo '	<form enctype="multipart/form-data" action="test.php?'.SID.'" method="post">
<table><tr><td>Elitesoldaten:</td><td> <input type="text" name="einh4" value="'.$_POST['einh4'].'" /></td></tr><tr><td>Minen</td><td><input type="text" name="def1" value="'.$_POST['def1'].'" /><input type="text" name="def2" value="'.$_POST['def2'].'" /><input type="text" name="def3" value="'.$_POST['def3'].'" /><input type="text" name="def4" value="'.$_POST['def4'].'" /></td></tr></table>
<input type="submit" />
</form>';

$defender_def['def1'] = $_POST['def1'];
$defender_def['def2'] = $_POST['def2'];
$defender_def['def3'] = $_POST['def3'];
$defender_def['def4'] = $_POST['def4'];

$offender['einh4'] = $_POST['einh4'];

if ($defender_def[def1] or $defender_def[def2] or $defender_def[def3] or $defender_def[def4]) {
	$content .=  'Elitesoldaten: '.$offender['einh4'].' <br />Minen:<br />';
	if ($defender_def[def1]) { $content .= '&nbsp;&nbsp;'.$defender_def[def1].' '.$def[1][name].'<br />';}
	if ($defender_def[def2]) { $content .= '&nbsp;&nbsp;'.$defender_def[def2].' '.$def[2][name].'<br />';}
	if ($defender_def[def3]) { $content .= '&nbsp;&nbsp;'.$defender_def[def3].' '.$def[3][name].'<br />';}
	if ($defender_def[def4]) { $content .= '&nbsp;&nbsp;'.$defender_def[def4].' '.$def[4][name].'<br />';}
	$content .= '&uuml;berrascht, dadurch entstanden folgende Verluste: <br />';

	if ($defender_def[def1] != 0 and $offender[einh4] != 0) { 
		do {
			$demont++; 
			if (rand(1,100)<=70){$defender_def[def1]--; $entschaerfung++; if (rand(1,100)<=30){ $vo[4]++; $offender[einh4]--; }} 
		} while ($offender[einh4] > $demont and $defender_def[def1] > 0);
	}
	if ($offender[einh4] > $demont and $defender_def[def2] != 0 and $offender[einh4] != 0) { 
		do {
			$demont++; 
			if (rand(1,100)<=65){$defender_def[def2]--; $entschaerfung++; if (rand(1,100)<=40){ $vo[4]++; $offender[einh4]--; }} 
		} while ($offender[einh4] > $demont and $defender_def[def2] > 0);
	}
	if ($offender[einh4] > $demont and $defender_def[def3] != 0 and $offender[einh4] != 0) { 
		do {
			$demont++; 
			if (rand(1,100)<=90){$defender_def[def3]--; $entschaerfung++; if (rand(1,100)<=15){ $vo[4]++; $offender[einh4]--; }} 
		} while ($offender[einh4] > $demont and $defender_def[def3] > 0);
	}
	if ($offender[einh4] > $demont and $defender_def[def4] != 0 and $offender[einh4] != 0) { 
		do {
			$demont++; 
			if (rand(1,100)<=80){$defender_def[def4]--; $entschaerfung++; if (rand(1,100)<=20){ $vo[4]++; $offender[einh4]--; }} 
		} while ($offender[einh4] > $demont and $defender_def[def4] > 0);
	}

	if (!$entschaerfung) {$entschaerfung=keine;}
	
	if ($defender_def[def1] != 0 and $offender[einh1] != 0) { 
		do {$vo[1]++; $offender[einh1]--; $defender_def[def1]--;} while ($offender[einh1]  > 0 and $defender_def[def1] > 0);
	}
	if ($defender_def[def1] != 0 and $offender[einh2] != 0) { 
		do {$vo[2]++; $offender[einh2]--; $defender_def[def1]--;} while ($offender[einh2]  > 0 and $defender_def[def1] > 0);
	}
	if ($defender_def[def1] != 0 and $offender[einh3] != 0) { 
		do {$vo[3]++; $offender[einh3]--; $defender_def[def1]--;} while ($offender[einh3]  > 0 and $defender_def[def1] > 0);
	}
	

	if ($defender_def[def2] != 0 and $offender[einh1] != 0) { 
		do {$vo[1]++; $offender[einh1]--; $defender_def[def2]--;} while ($offender[einh1]  > 0 and $defender_def[def2] > 0);
	}
	if ($defender_def[def2] != 0 and $offender[einh2] != 0) { 
		do {$vo[2]++; $offender[einh2]--; $defender_def[def2]--;} while ($offender[einh2]  > 0 and $defender_def[def2] > 0);
	}
	if ($defender_def[def2] != 0 and $offender[einh3] != 0) { 
		do {$vo[3]++; $offender[einh3]--; $defender_def[def2]--;} while ($offender[einh3]  > 0 and $defender_def[def2] > 0);
	}
	if ($defender_def[def2] != 0 and $offender[einh4] != 0) { 
		do {$vo[4]++; $offender[einh4]--; $defender_def[def2]--;} while ($offender[einh4]  > 0 and $defender_def[def2] > 0);
	}
	if ($defender_def[def2] != 0 and $offender[einh5] != 0) { $defender_def[def2]--; }
	if ($defender_def[def2] != 0 and $offender[einh6] != 0) { $defender_def[def2]--; }
	if ($defender_def[def2] != 0 and $offender[einh7] != 0) { $defender_def[def2]--; }
	if ($defender_def[def2] != 0 and $offender[einh8] != 0) { $defender_def[def2]--; }
	if ($defender_def[def2] != 0 and $offender[einh9] != 0) { $defender_def[def2]--; }
	if ($defender_def[def2] != 0 and $offender[einh10] != 0) { $defender_def[def2]--; }
	if ($defender_def[def2] != 0 and $offender[einh11] != 0) { $defender_def[def2]--; }
	if ($defender_def[def2] != 0 and $offender[einh12] != 0) { $defender_def[def2]--; }	
	if ($defender_def[def2] != 0 and $offender[einh13] != 0) { $defender_def[def2]--; }
	if ($defender_def[def2] != 0 and $offender[einh14] != 0) { $defender_def[def2]--; }
	if ($defender_def[def2] != 0 and $offender[einh15] != 0) { $defender_def[def2]--; }
	
	if ($defender_def[def3] != 0 and $offender[einh12] != 0) { 
		do {$vo[12]++; $offender[einh12]--; $defender_def[def3]--;} while ($offender[einh12]  > 0 and $defender_def[def3] > 0);
	}
	if ($defender_def[def3] != 0 and $offender[einh13] != 0) { 
		do {$vo[13]++; $offender[einh13]--; $defender_def[def3]--;} while ($offender[einh13]  > 0 and $defender_def[def3] > 0);
	}
	if ($defender_def[def3] != 0 and $offender[einh14] != 0) { 
		do {$vo[14]++; $offender[einh14]--; $defender_def[def3]--;} while ($offender[einh14]  > 0 and $defender_def[def3] > 0);
	}
	if ($defender_def[def3] != 0 and $offender[einh15] != 0) { 
		do {$vo[15]++; $offender[einh15]--; $defender_def[def3]--;} while ($offender[einh15]  > 0 and $defender_def[def3] > 0);
	}
	if ($defender_def[def3] != 0 and $offender[einh5] != 0) { 
		do {$vo[5]++; $offender[einh5]--; $defender_def[def3]--;} while ($offender[einh5]  > 0 and $defender_def[def3] > 0);
	}
	if ($defender_def[def3] != 0 and $offender[einh6] != 0) { 
		do {$vo[6]++; $offender[einh6]--; $defender_def[def3]--;} while ($offender[einh6]  > 0 and $defender_def[def3] > 0);
	}
	if ($defender_def[def3] != 0 and $offender[einh5] != 0) { $defender_def[def3]--; }
	if ($defender_def[def3] != 0 and $offender[einh6] != 0) { $defender_def[def3]--; }
	if ($defender_def[def3] != 0 and $offender[einh7] != 0) { $defender_def[def3]--; }
	if ($defender_def[def3] != 0 and $offender[einh8] != 0) { $defender_def[def3]--; }
	if ($defender_def[def3] != 0 and $offender[einh9] != 0) { $defender_def[def3]--; }
	if ($defender_def[def3] != 0 and $offender[einh10] != 0) { $defender_def[def3]--; }
	if ($defender_def[def3] != 0 and $offender[einh11] != 0) { $defender_def[def3]--; }
	if ($defender_def[def3] != 0 and $offender[einh12] != 0) { $defender_def[def3]--; }	
	if ($defender_def[def3] != 0 and $offender[einh13] != 0) { $defender_def[def3]--; }
	if ($defender_def[def3] != 0 and $offender[einh14] != 0) { $defender_def[def3]--; }
	if ($defender_def[def3] != 0 and $offender[einh15] != 0) { $defender_def[def3]--; }

	if ($defender_def[def4] != 0 and $offender[einh12] != 0) { 
		do {$vo[12]++; $offender[einh12]--; $defender_def[def4]--;} while ($offender[einh12] > 0 and $defender_def[def4] > 0);
	}
	if ($defender_def[def4] != 0 and $offender[einh13] != 0) { 
		do {$vo[13]++; $offender[einh13]--; $defender_def[def4]--;} while ($offender[einh13] > 0 and $defender_def[def4] > 0);
	}
	if ($defender_def[def4] != 0 and $offender[einh14] != 0) { 
		do {$vo[14]++; $offender[einh14]--; $defender_def[def4]--;} while ($offender[einh14] > 0 and $defender_def[def4] > 0);
	}
	if ($defender_def[def4] != 0 and $offender[einh15] != 0) { 
		do {$vo[15]++; $offender[einh15]--; $defender_def[def4]--;} while ($offender[einh15] > 0 and $defender_def[def4] > 0);
	}
	if ($defender_def[def4] != 0 and $offender[einh5] != 0) { 
		do {$vo[5]++; $offender[einh5]--; $defender_def[def4]--;} while ($offender[einh5] > 0 and $defender_def[def4] > 0);
	}
	if ($defender_def[def4] != 0 and $offender[einh6] != 0) { 
		do {$vo[6]++; $offender[einh6]--; $defender_def[def4]--;} while ($offender[einh6] > 0 and $defender_def[def4] > 0);
	}
	if ($defender_def[def4] != 0 and $offender[einh7] != 0) { 
		do {$vo[7]++; $offender[einh7]--; $defender_def[def4]--;} while ($offender[einh7] > 0 and $defender_def[def4] > 0);
	}
	if ($defender_def[def4] != 0 and $offender[einh9] != 0) { 
		do {$vo[9]++; $offender[einh9]--; $defender_def[def4]--;} while ($offender[einh9] > 0 and $defender_def[def4] > 0);
	}
	if ($defender_def[def4] != 0 and $offender[einh8] != 0) { 
		do {$vo[8]++; $offender[einh8]--; $defender_def[def4]--;} while ($offender[einh8] > 0 and $defender_def[def4] > 0);
	}	
	if ($defender_def[def4] != 0 and $offender[einh8]  != 0) { $defender_def[def4]--; }
	if ($defender_def[def4] != 0 and $offender[einh10] != 0) { $defender_def[def4]--; }
	if ($defender_def[def4] != 0 and $offender[einh11] != 0) { $defender_def[def4]--; }
	if ($defender_def[def4] != 0 and $offender[einh12] != 0) { $defender_def[def4]--; }	
	if ($defender_def[def4] != 0 and $offender[einh13] != 0) { $defender_def[def4]--; }
	if ($defender_def[def4] != 0 and $offender[einh14] != 0) { $defender_def[def4]--; }
	if ($defender_def[def4] != 0 and $offender[einh15] != 0) { $defender_def[def4]--; }
	

	if ($vo[1]) { $content .= '&nbsp;&nbsp;'.$vo[1].' '.$einh[1][name].'<br />';}
	if ($vo[2]) { $content .= '&nbsp;&nbsp;'.$vo[2].' '.$einh[2][name].'<br />';}
	if ($vo[3]) { $content .= '&nbsp;&nbsp;'.$vo[3].' '.$einh[3][name].'<br />';}
	if ($vo[4]) { $content .= '&nbsp;&nbsp;'.$vo[4].' '.$einh[4][name].'<br />';}
	if ($vo[5]) { $content .= '&nbsp;&nbsp;'.$vo[5].' '.$einh[5][name].'<br />';}
	if ($vo[6]) { $content .= '&nbsp;&nbsp;'.$vo[6].' '.$einh[6][name].'<br />';}
	if ($vo[7]) { $content .= '&nbsp;&nbsp;'.$vo[7].' '.$einh[7][name].'<br />';}
	if ($vo[8]) { $content .= '&nbsp;&nbsp;'.$vo[8].' '.$einh[8][name].'<br />';}
	if ($vo[9]) { $content .= '&nbsp;&nbsp;'.$vo[9].' '.$einh[9][name].'<br />';}
	if ($vo[10]) { $content .= '&nbsp;&nbsp;'.$vo[10].' '.$einh[10][name].'<br />';}
	if ($vo[11]) { $content .= '&nbsp;&nbsp;'.$vo[11].' '.$einh[11][name].'<br />';}
	if ($vo[12]) { $content .= '&nbsp;&nbsp;'.$vo[12].' '.$einh[12][name].'<br />';}
	if ($vo[13]) { $content .= '&nbsp;&nbsp;'.$vo[13].' '.$einh[13][name].'<br />';}
	if ($vo[14]) { $content .= '&nbsp;&nbsp;'.$vo[14].' '.$einh[14][name].'<br />';}
	if ($vo[15]) { $content .= '&nbsp;&nbsp;'.$vo[15].' '.$einh[15][name].'<br />';}
	
	
	$content .= '<br />Die Elitesoldaten des Angreifers haben '.$entschaerfung.' Minen entsch&auml;rft.<br /><br />';
	$content .= 'Restminen:';
	if ($defender_def[def1]) { $content .= '&nbsp;&nbsp;'.$defender_def[def1].' '.$def[1][name].'<br />';}
	if ($defender_def[def2]) { $content .= '&nbsp;&nbsp;'.$defender_def[def2].' '.$def[2][name].'<br />';}
	if ($defender_def[def3]) { $content .= '&nbsp;&nbsp;'.$defender_def[def3].' '.$def[3][name].'<br />';}
	if ($defender_def[def4]) { $content .= '&nbsp;&nbsp;'.$defender_def[def4].' '.$def[4][name].'<br />';}

}

echo $content;
?>