<?php 
include 'raketen_preise.php';
include 'functions.php';

session_name('SESSION');
session_start();

if ($rak[$_GET[id]]['name']){
	if (file_exists('./img/rak'.$_GET[id].'.jpg')) { $img = '<img src="img/rak'.$_GET[id].'.jpg" alt="PIC" /><br /><br />'; }

	echo tag2value('onload','',template('head')).'<center>
	<table border="1" cellspacing="0" style="background-color:#e2e2e2; font-size: 12px">
		<tr align="center">
			<th style="width:440px" colspan="2">
				<b>'.$rak[$_GET[id]]['name'].'</b>
			</th>
		</tr>
		<tr align="center">
			<td colspan="2">
				'.$img.'
				<b>'.$rak[$_GET[id]]['info'].'</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				Zielreihenfolge:
			</td>
			<td style="width:270px">
				<b>'.$rak[$_GET[id]]['targets'].' </b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				max Geschwindigkeit:
			</td>
			<td style="width:270px">
				<b>'.$rak[$_GET[id]]['speed'].' km/h</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				Herstellungskosten:
			</td>
			<td style="width:270px">
				<b>'.number_format($rak[$_GET['id']]['eisen']).' Eisen / '.number_format($rak[$_GET['id']]['titan']).' Titan / '.number_format($rak[$_GET['id']]['oel']).' Oel <br /> '.number_format($rak[$_GET['id']]['uran']).' Uran / '.number_format($rak[$_GET['id']]['gold']).' Gold / '.number_format($rak[$_GET['id']]['chanje']).' Chanje</b>
			</td>
		</tr>	
	</table>
	</center>
</body>
</html>';
} else echo 'nich gefunden';
?>