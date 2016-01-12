<?php 
	// o-wars XML playerstats
	include 'functions.php';
	$dbh = db_connect();
	
	if ($_POST['ubl']){ 
		$_GET['ubl'] = $_POST['ubl']; 
		$_GET['pw'] = $_POST['pw']; 
	}
	if ($_GET['ubl']) {
		$_GET['ubl'] = htmlentities($_GET['ubl']);
		$_GET['pw']  = htmlentities($_GET['pw']);
		$result = mysql_query("SELECT * FROM `user` WHERE `omni` = '".number_format($_GET['ubl'],0,'','')."' and `password` = md5('".$_GET['pw']."') LIMIT 1;");
		$array  = mysql_fetch_array($result);
	
		$result   = mysql_query("SELECT * FROM `missionen` WHERE `ziel` = '".number_format($_GET['ubl'],0,'','')."';");
		$missions = mysql_num_rows($result);
		
		$result   = mysql_query("SELECT * FROM `missionen` WHERE `ziel` = '".number_format($_GET['ubl'],0,'','')."' and `type` = '1';");
		$attacks = mysql_num_rows($result);
		
		if ($array){
			echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>
<o-wars-player-stats>
<time value=\"".date('U')."\" />
<info>
<player value=\"".$array['name']."\" />
<ubl value=\"".$array['omni']."\" />
<points value=\"".$array['points']."\" />
<base value=\"".$array['base']."\" />
</info>
<attacks value=\"".$attacks."\" />
<missions value=\"".$missions."\" />
<building value=\"GEBAEUDENAME\" finished=\"TIMESTAMP_FERTIGSTELLUNG\" started=\"TIMESTAMP_STARTED\" />
<science value=\"forschungsname\" finished=\"TIMESTAMP_FERTIGSTELLUNG\" started=\"TIMESTAMP_STARTED\" />
</o-wars-player-stats>";
		} else {
			echo '503 - access denided';
		}
	} else {
		echo '503 - ubl? pw?';
	}
?>