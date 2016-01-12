<?php 
include 'functions.php';
include 'gebaeude_preise.php';

session_name('SESSION');
session_start();

echo tag2value('onload','',template('head')).'<center>
	<table border="1" cellspacing="0" style="background-color:#e2e2e2; font-size: 12px">
		<tr align="center">
			<th style="width:570px">
				<b>'.$kosten[$_GET[id]]['name'].'</b>
			</th>
		</tr>
		<tr align="center">
			<td style="width:270px" align="left">
				<b>'.$kosten[$_GET[id]]['info'].'</b>
			</td>
		</tr>
	</table>
	<br />
	</center>
</body>
</html>';
?>