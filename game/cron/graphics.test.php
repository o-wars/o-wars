<?php
include('../functions.php');
include('../inc/graph.php');

// disabe output buffering cause its enabled in functions.php
ob_end_clean();

$dbh = db_connect();

$time = microtime_float();

$users = mysql_query("SELECT omni as ubl FROM user WHERE 1;");

while ($user = mysql_fetch_array($users)) {

	echo "Processing User ".$user['ubl']."\n";
	
	$res = mysql_query("SELECT * FROM stats2 WHERE uid = ".$user['ubl']." ORDER BY time DESC LIMIT 75;");

	unset($x, $y_ap, $y_pp, $y_kp, $y_gp);
	
	$i=0;
	while ($data = @mysql_fetch_array($res)) {

		$i++;
		if ($i == 3) {
		$y_ap[] = $data['ap'];
		$y_kp[] = $data['kp'];
		$y_pp[] = $data['pp'];
		$y_gp[] = $data['gp'];
		$x[] = $data['time'];
		$i=0;
		}

	}
		
	drawgraph("../temp/img/graph_ap_".$user['ubl'].".gif", $x, $y_ap, 450, 350, "Ausbaupunkte fuer UBL: ".$user['ubl'].' (Letzte 5 Tage)', 'Stand: '.date('d.m.y H:00'), '            Ausbaupunkte zur Uhrzeit');
	usleep(200000);
	drawgraph("../temp/img/graph_kp_".$user['ubl'].".gif", $x, $y_kp, 450, 350, "Kampfpunkte fuer UBL: ".$user['ubl'].' (Letzte 5 Tage)', 'Stand: '.date('d.m.y H:00'),  '            Kampfpunkte zur Uhrzeit');
	usleep(200000);
	drawgraph("../temp/img/graph_pp_".$user['ubl'].".gif", $x, $y_pp, 450, 350, "Plasmapunkte fuer UBL: ".$user['ubl'].' (Letzte 5 Tage)', 'Stand: '.date('d.m.y H:00'), '            Plasmapunkte zur Uhrzeit');
	usleep(200000);
	drawgraph("../temp/img/graph_gp_".$user['ubl'].".gif", $x, $y_gp, 450, 350, "Gesamtpunkte fuer UBL: ".$user['ubl'].' (Letzte 5 Tage)', 'Stand: '.date('d.m.y H:00'), '            Gesamtpunkte zur Uhrzeit');
	usleep(200000);

}

echo 'Dauer: '.(microtime_float()-$time)." sec\n";

?>
