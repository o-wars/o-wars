<?php 
include 'einheiten_preise.php';
include 'functions.php';

session_name('SESSION');
session_start();

if (file_exists('./img/einh'.$_GET[id].'.jpg')) { $img = '<img src="img/einh'.$_GET[id].'.jpg" alt="PIC" /><br /><br />'; }

if ($einh[$_GET[id]]['name']){
	echo tag2value('onload','',template('head')).'
	<center>
	<table border="1" cellspacing="0" style="background-color:#e2e2e2; font-size: 12px">
		<tr align="center">
			<th style="width:440px" colspan="2">
				<b>'.$einh[$_GET[id]]['name'].'</b>
			</th>
		</tr>
		<tr align="center">
			<td colspan="2">
			'.$img.'<br />
			<b>'.$einh[$_GET[id]]['info'].'</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				Angriffswert:
			</td>
			<td style="width:270px">
				<b>'.$einh[$_GET[id]]['off'].' Punkte</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				Verteidigungswert:
			</td>
			<td style="width:270px">
				<b>'.$einh[$_GET[id]]['def'].' Punkte</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				max. Gesamtzuladung:
			</td>
			<td style="width:270px">
				<b>'.$einh[$_GET[id]]['space'].' Einheiten</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				Hangarplatz:
			</td>
			<td style="width:270px">
				<b>'.$einh[$_GET[id]]['size'].' Feld(er)</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				max Geschwindigkeit:
			</td>
			<td style="width:270px">
				<b>'.$einh[$_GET[id]]['speed'].' km/h</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				Kampfpunkte:
			</td>
			<td style="width:270px">
				<b>'.round((($einh[$_GET[id]]['eisen']+$einh[$_GET[id]]['titan']+$einh[$_GET[id]]['oel']+$einh[$_GET[id]]['uran']+$einh[$_GET[id]]['gold']+$einh[$_GET[id]]['chanje'])/100),2).'</b>
			</td>
		</tr>	
		<tr align="center">
			<td style="width:170px">
				Herstellungskosten:
			</td>
			<td style="width:270px">
				<b>'.number_format($einh[$_GET['id']]['eisen']).' Eisen / '.number_format($einh[$_GET['id']]['titan']).' Titan / '.number_format($einh[$_GET['id']]['oel']).' Oel <br /> '.number_format($einh[$_GET['id']]['uran']).' Uran / '.number_format($einh[$_GET['id']]['gold']).' Gold / '.number_format($einh[$_GET['id']]['chanje']).' Chanje</b>
			</td>
		</tr>	
	</table>	
	</center>
</body>
</html>';
} else echo 'nich gefunden';
?>