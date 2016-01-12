<?php

	$blockmsg = '
		<h1>
			Vielen Dank für deine Bewerbung, solltest du in Frage kommen, werde ich mich bei dir melden.<br /><br />
			Bitte bedenke, falls du etwas länger auf eine Antwort warten musst, das ich das Spiel aus Zeitmangel abgeben muss.
		</h1>
		<h2>
			MfG DerBunman
		</h2>
	';

	$file = './bewerbung/ips/'. $_SERVER['REMOTE_ADDR'];

	if( file_exists( $file ) && ( time() - filectime( $file ) <= 3600*24*31 ) ) {
		die( $blockmsg );

	}

	if( isset( $_POST['vorstellung'] ) ) {
		touch( $file );
		$line = "\n-----------------------------------------------------------------\n";
		$nl   = "\n";

		$text  = $line . 'Bewerbung vom '. date('d.m.Y H:i'). $line . $nl . $nl;
		$text .= $line . 'Wer bist du eigentlich?' . $line . $_POST['vorstellung'] . $line . $nl . $nl;
		$text .= $line . 'Wie kann ich dich erreichen?' . $line . $_POST['erreichbarkeit'] . $line . $nl . $nl;
		$text .= $line . 'Ein paar schöne Referenzen wären schon nicht schlecht!' . $line . $_POST['referenzen'] . $line . $nl . $nl;
		$text .= $line . 'Und jetzt die eigentliche Bewerbung:' . $line . $_POST['bewerbung'] . $line . $nl . $nl;

		$fh = fopen( 'bewerbung/'.time().'-'.rand(0,999999).uniqid(''), "w" );
		fputs( $fh, $text, strlen($text) );
		fclose( $fh );

		mail( 'receiver@host.de', 'O-WARS.DE - BEWERBUNG !!!!!!!!!!!!', $text, 'From: bewerbung@o-wars.de' );

	}

	if( file_exists( $file ) && ( time() - filectime( $file ) <= 3600*24*31 ) ) {
		die( $blockmsg );

	}
	
?>
<html>
	<head>
		<title>O-Wars.de - Bewerbung</title>
		<style type="text/css">
			textarea { background-color: #c2c2c2; }
		</style>
	</head>
	<body style="background-color: #ffaaaa;">
		<h1>O-Wars Bewerbung</h1>
		<b>Bevor du dieses Formular ausfüllst, stelle bitte sicher, das du die Bekanntmachung gelesen hast, die Anforderungen erfüllst und auch das Spiel kennst.</b>
		<br />
		<br />
		<br />
		<form action="bewerbung.php" method="post">
			Wer bist du eigentlich?<br />
			<textarea name="vorstellung" style="width: 80%; height: 200px;"></textarea>
			<br />
			<br />
			Wie kann ich dich erreichen? (eMail, Jabber, ICQ)<br />
			<textarea name="erreichbarkeit" style="width: 80%; height: 200px;"></textarea>
			<br />
			<br />
			Ein paar schöne Referenzen wären schon nicht schlecht!<br />
			<textarea name="referenzen" style="width: 80%; height: 200px;"></textarea>
			<br />
			<br />
			Und jetzt die eigentliche Bewerbung :P<br />
			<textarea name="bewerbung" style="width: 80%; height: 200px;"></textarea>
			<br />
			<br />
			<input type="submit" value="bewerben!" />
		</form>
	</body>
</html>
