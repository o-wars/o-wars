<?php 
include 'def_preise.php';
include 'functions.php';

session_name('SESSION');
session_start();

if (file_exists('./img/def'.$_GET[id].'.jpg')) { $img = '<img src="img/def'.$_GET[id].'.jpg" alt="PIC" />';}
if ($def[$_GET[id]]['name'] AND $_GET['id'] <= 4){
echo tag2value('onload','',template('head')).'<center>
	<table border="1" cellspacing="0" style="background-color:#e2e2e2; font-size: 12px">
		<tr align="center">
			<th style="width:440px" colspan="2">
				<b>'.$def[$_GET[id]]['name'].'</b>
			</th>
		</tr>
		<tr align="center">
			<td colspan="2">
				'.$img.'<br />
				<b>'.$def[$_GET[id]]['info'].'</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				Bek&auml;mpft:
			</td>
			<td style="width:270px">
				<b>'.$def[$_GET[id]]['targets'].'</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				Entdeckungschance:
			</td>
			<td style="width:270px">
				<b>'.$def[$_GET[id]]['spot'].'%</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				Todesrate beim entsch&auml;rfen:
			</td>
			<td style="width:270px">
				<b>'.$def[$_GET[id]]['death'].'%</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				Herstellungskosten:
			</td>
			<td style="width:270px">
				<b>'.number_format($def[$_GET['id']]['eisen']).' Eisen / '.number_format($def[$_GET['id']]['titan']).' Titan / '.number_format($def[$_GET['id']]['oel']).' Oel <br /> '.number_format($def[$_GET['id']]['uran']).' Uran / '.number_format($def[$_GET['id']]['gold']).' Gold / '.number_format($def[$_GET['id']]['chanje']).' Chanje</b>
			</td>
		</tr>			
	</table>
	<br />
	Minen k&ouml;nnen durch Elite-Soldaten entsch&auml;rft werden.
	</center>
</body>
</html>';
} elseif ($def[$_GET[id]]['name'] AND $_GET['id'] >= 5){
echo tag2value('onload','',template('head')).'<center>
	<table border="1" cellspacing="0" style="background-color:#e2e2e2; font-size: 12px">
		<tr align="center">
			<th style="width:440px" colspan="2">
				<b>'.$def[$_GET[id]]['name'].'</b>
			</th>
		</tr>
		<tr align="center">
			<td colspan="2">
				'.$img.'<br />
				<b>'.$def[$_GET[id]]['info'].'</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				Angriffswert:
			</td>
			<td style="width:270px">
				<b>'.$def[$_GET[id]]['off'].' Punkte</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				Verteidigungswert:
			</td>
			<td style="width:270px">
				<b>'.$def[$_GET[id]]['def'].' Punkte</b>
			</td>
		</tr>
		<tr align="center">
			<td style="width:170px">
				Herstellungskosten:
			</td>
			<td style="width:270px">
				<b>'.number_format($def[$_GET['id']]['eisen']).' Eisen / '.number_format($def[$_GET['id']]['titan']).' Titan / '.number_format($def[$_GET['id']]['oel']).' Oel <br /> '.number_format($def[$_GET['id']]['uran']).' Uran / '.number_format($def[$_GET['id']]['gold']).' Gold / '.number_format($def[$_GET['id']]['chanje']).' Chanje</b>
			</td>
		</tr>	
	</table>
	<br />
	</center>
</body>
</html>';
} else echo 'nich gefunden';
?>