<?php 

include 'config.php';
$dbh = mysql_pconnect($db_host, $db_user, $db_pass)
	or die("<h1>Could not connect</h1><b>Please check your configuration. The DB settings seems to be incorrect");
mysql_select_db($db_database, $dbh);

$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION[user][omni]."';";
$selectResult   = mysql_query($select, $dbh);
$gebaeude = mysql_fetch_array($selectResult);

$einh[1][name]    = "Soldat";
$einh[1][eisen]   = 50;
$einh[1][titan]   = 30;
$einh[1][oel]     = 40;
$einh[1][uran]    = 5;
$einh[1][gold]    = 5;
$einh[1][chanje]  = 0;
$einh[1][dauer]   = 1400*pow(0.9,$gebaeude['nbz']);
$einh[1][off]     = 5;
$einh[1][def]     = 25;
$einh[1][speed]   = 7;
$einh[1][space]   = 20;
$einh[1][verbrauch]= 0;
$einh[1][ek]      = 200;
$einh[1][vk]      = 100;
$einh[1][size]    = 1;
$einh[1][info]    = 'Der gemeine Soldat kann bei o-wars direkt produziert werden, um von Anfang an das Spielgeschehen zu beleben.
Im weiteren Spielverlauf wird er dann durch bessere Einheiten ersetzt,
ist aber immer noch als "Kanonenfutter" oder "Raketenpuffer" zu empfehlen.';

$einh[2][name]    = "Grenadier";
$einh[2][eisen]   = 60;
$einh[2][titan]   = 40;
$einh[2][oel]     = 40;
$einh[2][uran]    = 7;
$einh[2][gold]    = 5;
$einh[2][chanje]  = 0;
$einh[2][dauer]   = 1600*pow(0.9,$gebaeude['nbz']);
$einh[2][off]     = 8;
$einh[2][def]     = 30;
$einh[2][speed]   = 7;
$einh[2][space]   = 25;
$einh[2][verbrauch]= 0;
$einh[2][ek] = 220;
$einh[2][vk] = 110;
$einh[2][size]    = 1;
$einh[2][info]    = 'Der Grenadier ist die Weiterentwicklung des gemeinen Soldaten und ein wahrer Soldatenschreck.';

$einh[3][name]    = "Panzerfaustsch&uuml;tze";
$einh[3][eisen]   = 75;
$einh[3][titan]   = 60;
$einh[3][oel]     = 60;
$einh[3][uran]    = 10;
$einh[3][gold]    = 5;
$einh[3][chanje]  = 0;
$einh[3][dauer]   = 1800*pow(0.9,$gebaeude['nbz']);
$einh[3][off]     = 10;
$einh[3][def]     = 35;
$einh[3][speed]   = 7;
$einh[3][space]   = 30;
$einh[3][verbrauch]= 0;
$einh[3][ek] = 240;
$einh[3][vk] = 120;
$einh[3][size]    = 1;
$einh[3][info]    = 'Der Panzerfaustsch&uuml;tze ist die erste der Infanterieeinheiten, der es m&ouml;glich ist sich mit den ersten Fahrzeugen zu messen. ';

$einh[4][name]    = "Elite-Soldat";
$einh[4][eisen]   = 100;
$einh[4][titan]   = 100;
$einh[4][oel]     = 100;
$einh[4][uran]    = 15;
$einh[4][gold]    = 25;
$einh[4][chanje]  = 0;
$einh[4][dauer]   = 2000*pow(0.9,$gebaeude['nbz']);
$einh[4][off]     = 15;
$einh[4][def]     = 40;
$einh[4][speed]   = 10;
$einh[4][space]   = 50;
$einh[4][verbrauch]= 0;
$einh[4][info]    = 'Kann Minen entsch&auml;rfen.';
$einh[4][ek] = 400;
$einh[4][vk] = 200;
$einh[4][size]    = 1;
$einh[4][info]    = 'Der Elite-Soldat ist die K&ouml;nigseinheit unter den Fusstruppen.
Durch seine besondere Ausbildung, ist es ihm m&ouml;glich mit vollem Sturmgep&auml;ck verh&auml;ltnissm&auml;ssig schnell zu maschieren, auch ist er allen anderen Fusstruppen im Kampf &uuml;berlegen.
Im weiteren Spielverlauf ist er aber vor allem deshalb von Bedeutung, weil es ihm m&ouml;glich ist l&auml;stige Minenfelder zu entsch&auml;rfen.
';
$einh[5][name]    = "K&uuml;belwagen";
$einh[5][eisen]   = 150;
$einh[5][titan]   = 100;
$einh[5][oel]     = 200;
$einh[5][uran]    = 0;
$einh[5][gold]    = 20;
$einh[5][chanje]  = 0;
$einh[5][dauer]   = 2400*pow(0.9,$gebaeude['nbz']);
$einh[5][off]     = 40;
$einh[5][def]     = 100;
$einh[5][speed]   = 65;
$einh[5][space]   = 80;
$einh[5][verbrauch]= 12;
$einh[5][ek] = 600;
$einh[5][vk] = 300;
$einh[5][size]    = 2;
$einh[5][info]    = 'Ein K&uuml;belwagen war ein f&uuml;r den milit&auml;rischen Gebrauch konstruierter offener PKW der 1920er, 1930er und 1940er Jahre, dessen Namen sich von seinen k&uuml;belartige Sitzen herleitet, die er besass, um zu verhindern, dass die Soldaten bei Kurvenfahrt herausrutschten.
Ein K&uuml;belwagen ist nur mit dem Notwendigsten ausgestattet, besitzt meist abnehmbare T&uuml;ren, eine abklappbare Windschutzscheibe und nur ein Notverdeck.
Ber&uuml;hmt wurde der "K&uuml;bel" in Form des auf dem KdF-Wagen (VW K&auml;fer) aufbauenden VW Typ 82 K&uuml;belwagens, der sich im Krieg an allen Fronten bew&auml;hrte.';

$einh[6][name]    = "Marder III";
$einh[6][eisen]   = 250;
$einh[6][titan]   = 150;
$einh[6][oel]     = 150;
$einh[6][uran]    = 10;
$einh[6][gold]    = 20;
$einh[6][chanje]  = 0;
$einh[6][dauer]   = 3600*pow(0.9,$gebaeude['nbz']);
$einh[6][off]     = 90;
$einh[6][def]     = 125;
$einh[6][speed]   = 30;
$einh[6][space]   = 70;
$einh[6][verbrauch]= 100;
$einh[6][ek] = 1300;
$einh[6][vk] = 650;
$einh[6][info] = 'Wegen der st&auml;ndig zunehmenden Zahl der alliierten Panzer sah man sich gezwungen, schnell und billig neue Panzerabwehrwaffen zu produzieren. Panzer waren zu teuer und konnten nicht schnell genug hergestellt werden. So wurde auf dem Fahrgestell des Panzer II (Flamm) einfach ein Pak montiert. Geplant war eigentlich die neue 7,5 cm Pak 40/2 L/46 zu benutzen. Allerdings konnte dies nicht schnell genug produziert werden, und so entschied man sich als Notl&ouml;sung erbeutete, sowjetische 7,62 cm Pak zu installieren. Die sowjetische Pak erwies sich als &auml;usserst zuverl&auml;ssig und effektiv. Der entstandene Panzer wurde Marder II getauft. Sp&auml;ter traf endlich die deutsche Pak ein und man begann diese zu montieren auf einem ver&auml;nderten Fahrgestell. Das war nun die endg&uuml;ltige Version des Marder II.
Die Kanone des Marders war durchaus schlagkr&auml;ftig und konnte Panzer jeder Art zerst&ouml;ren, allerdings war der Marder selbst empfindlich. Die Panzerung war d&uuml;nn und er war nach oben offen, bot damit eine Angriffsfl&auml;che f&uuml;r Bomben und Handgranaten. Sein grosser Vorteil jedoch war, dass er billiger und leichter zu produzieren war als ein neuer Kampfpanzer.<br />
Der Marder III war dasselbe wie ein Marder II. Mit dem Unterschied, dass diesmal das Fahrgestell des Panther benutzt wurde. Auch beim Marder III benutzte man zun&auml;chst die zuverl&auml;ssige russische 7,62 cm Pak. Sp&auml;ter baute man jedoch auch die deutsche 7,5 cm Pak ein.';
$einh[6][size]    = 2;

$einh[7][name]    = "Panther";
$einh[7][eisen]   = 400;
$einh[7][titan]   = 300;
$einh[7][oel]     = 200;
$einh[7][uran]    = 30;
$einh[7][gold]    = 20;
$einh[7][chanje]  = 0;
$einh[7][dauer]   = 4800*pow(0.9,$gebaeude['nbz']);
$einh[7][off]     = 120;
$einh[7][def]     = 250;
$einh[7][speed]   = 46;
$einh[7][space]   = 80;
$einh[7][verbrauch]= 120;
$einh[7][ek] = 1900;
$einh[7][vk] = 950;
$einh[7][info] = '"Feind hat einen neuen Panzer eingef&uuml;hrt! &Auml;usseres &auml;hnlich dem "Tridsatchedverka"! Der Panzer ist schwer gepanzert und sein Gewicht k&ouml;nnte zwischen 40 und 50 Tonnen betragen! Wahrscheinlich bewaffnet mit 88 mm Flakkanone! Wir haben starke Verluste auf Entfernungen von &uuml;ber 2000 Meter...!" ';
$einh[7][size]    = 3;

$einh[8][name]    = "Tiger";
$einh[8][eisen]   = 500;
$einh[8][titan]   = 300;
$einh[8][oel]     = 300;
$einh[8][uran]    = 40;
$einh[8][gold]    = 40;
$einh[8][chanje]  = 0;
$einh[8][dauer]   = 6000*pow(0.9,$gebaeude['nbz']);
$einh[8][off]     = 150;
$einh[8][def]     = 340;
$einh[8][speed]   = 38;
$einh[8][space]   = 100;
$einh[8][verbrauch]= 140;
$einh[8][ek] = 2600;
$einh[8][vk] = 1300;
$einh[8][info] = '"Ein Bataillon von Tigern ist soviel wert wie eine ganze normale Panzerdivision."<br /><i> - george23w, 2004</i>';
$einh[8][size]    = 3;

$einh[9][name]    = "Nebelwerfer";
$einh[9][eisen]   = 250;
$einh[9][titan]   = 300;
$einh[9][oel]     = 500;
$einh[9][uran]    = 50;
$einh[9][gold]    = 30;
$einh[9][chanje]  = 1;
$einh[9][dauer]   = 5400*pow(0.9,$gebaeude['nbz']);
$einh[9][off]     = 225;
$einh[9][def]     = 110;
$einh[9][speed]   = 50;
$einh[9][space]   = 140;
$einh[9][verbrauch]= 20;
$einh[9][ek] = 2400;
$einh[9][vk] = 1200;
$einh[9][info] = 'Die Raketen des 15 cm Nebelwerfers hatten eine enorme Sprengwirkung. In Mengen eingesetzt (d.h. in Werferbatterien) wurde eine enorme Fl&auml;chenwirkung erzielt und der Nebelwerfer wurde zu einer wichtigen Waffe gegen feindl. Infanterie. Dazu kam, dass die Raketen ein gr&auml;sslich kreischendes Ger&auml;usch von sich gaben - ganz wie die russ. Katysha. Da die Sprengwirkung der dt. Nebelwerfer den russ. Werfern &uuml;berlegen waren, war demzufolge die psychologische Wirkung der Geschossger&auml;usche gr&ouml;sser. Die Soldaten beteten beim Ger&auml;usch einer herannahenden Rakete. Um den Nebelwerfer mobil zu machen, modifizierte man ihn und dabei entstand der Panzerwerfer 42. Er wurde mit 7 oder 10 Rohren auf einen Opel Maultier montiert. Dieser Werfer wurde dann, wie die Katysha auch, in Batterien eingesetzt und konnte feindlichen Truppen enorme Verluste zuf&uuml;gen.';
$einh[9][size]    = 2;

$einh[10][name]    = "K&ouml;nigstiger";
$einh[10][eisen]   = 800;
$einh[10][titan]   = 500;
$einh[10][oel]     = 800;
$einh[10][uran]    = 100;
$einh[10][gold]    = 80;
$einh[10][chanje]  = 5;
$einh[10][dauer]   = 7200*pow(0.9,$gebaeude['nbz']);
$einh[10][off]     = 490;
$einh[10][def]     = 520;
$einh[10][speed]   = 38;
$einh[10][space]   = 100;
$einh[10][verbrauch]= 400;
$einh[10][ek] = 6000;
$einh[10][vk] = 3000;
$einh[10][info] = 'Die Bezeichnung K&ouml;nigstiger fand ihren Ursprung bei den westalliierten Panzersoldaten, die diesen schweren Panzer als
erste (und das sicher nicht ohne Respekt) als <i>King Tiger</i> oder <i>Royal Tiger</i> bezeichneten.</p>
<p>Der K&ouml;nigstiger war der H&ouml;hepunkt der Panzerentwicklung w&auml;hrend des Krieges, wobei sich die Formgebung am Panther orientierte. Gegen&uuml;ber dem "Tiger"
wuchsen sowohl die Abmessungen, als auch die Panzerst&auml;rken und damit das Gewicht.  Die 8,8-cm-KwK L/71 war die beste Panzerkanone ihrer Zeit
und verlieh dem "K&ouml;nigstiger" eine gewaltige Feuerkraft. Alle feindlichen Panzer konnten frontal auf Entfernungen von 1.000 bis
3.000 m abgeschossen werden; es gibt Berichte, dass T 34 sogar auf mehr als 4.000 m "geknackt" worden sind.</p>';
$einh[10][size]    = 3;

$einh[11][name]    = "Jagdtiger";
$einh[11][eisen]   = 1000;
$einh[11][titan]   = 700;
$einh[11][oel]     = 600;
$einh[11][uran]    = 100;
$einh[11][gold]    = 150;
$einh[11][chanje]  = 6;
$einh[11][dauer]   = 9000*pow(0.9,$gebaeude['nbz']);
$einh[11][off]     = 666;
$einh[11][def]     = 500;
$einh[11][speed]   = 38;
$einh[11][space]   = 150;
$einh[11][verbrauch]= 600;
$einh[11][ek] = 12000;
$einh[11][vk] = 6000;
$einh[11][info] = 'Der Jagdpanzer Jagdtiger ist der weltweit schwerste jemals in Serie gebaute Panzer.
Wie &uuml;blich, entwickelte man ein Derivat aus dem K&ouml;nigstiger. Mit einem Gesamtgewicht von 70-75 t, einer 128 mm Kanone und einer massiven Panzerung von 250 mm war dieser Panzer ein wahrer Gigant. Entwickelt wurden zwei Versionen mit unterschiedlichem Laufwerken. Der Jagdtiger wurde aber nur mit dem Panther-Motor ausgestattet; die maximale Geschwindigkeit betrug auf der Strasse noch beachtliche 38 km/h und im Gel&auml;nde 17 km/h bei einem &uuml;berdurchschnittlich hohen Benzinverbrauch von bis zu 1100 Litern pro 100 km. Als Jagdpanzer vorgesehen, erhielt er keinen drehbaren Turm, die Kanone war um jeweils 10 Grad nach rechts und links schwenkbar, der gesamte Panzer musste daher auf das Ziel gerichtet werden. Damit waren die empfindlichen Seiten gegnerischem Feuer ausgesetzt. Wegen seiner schier unglaublichen Gr&ouml;sse war er kilometerweit zu sehen.';
$einh[11][size]    = 3;

$einh[12][name]    = "Kleiner Transporter";
$einh[12][eisen]   = 100;
$einh[12][titan]   = 50;
$einh[12][oel]     = 75;
$einh[12][uran]    = 0;
$einh[12][gold]    = 5;
$einh[12][chanje]  = 0;
$einh[12][dauer]   = 1800*pow(0.9,$gebaeude['nbz']);
$einh[12][off]     = 1;
$einh[12][def]     = 20;
$einh[12][speed]   = 35;
$einh[12][space]   = 100;
$einh[12][verbrauch]= 8;
$einh[12][ek] = 400;
$einh[12][vk] = 200;
$einh[12][size]    = 1;
$einh[12][info]    = 'Kann biszu 3 Soldaten transportieren.';

$einh[13][name]    = "Grosser Transporter";
$einh[13][eisen]   = 200;
$einh[13][titan]   = 100;
$einh[13][oel]     = 150;
$einh[13][uran]    = 0;
$einh[13][gold]    = 10;
$einh[13][chanje]  = 0;
$einh[13][dauer]   = 2400*pow(0.9,$gebaeude['nbz']);
$einh[13][off]     = 2;
$einh[13][def]     = 100;
$einh[13][speed]   = 50;
$einh[13][space]   = 350;
$einh[13][verbrauch]= 9;
$einh[13][ek] = 800;
$einh[13][vk] = 400;
$einh[13][size]    = 2;
$einh[13][info]    = 'Kann biszu 10 Soldaten transportieren.';

$einh[14][name]    = "Motorrad";
$einh[14][eisen]   = 100;
$einh[14][titan]   = 20;
$einh[14][oel]     = 80;
$einh[14][uran]    = 10;
$einh[14][gold]    = 40;
$einh[14][chanje]  = 0;
$einh[14][dauer]   = 3600*pow(0.9,$gebaeude['nbz']);
$einh[14][off]     = 80;
$einh[14][def]     = 20;
$einh[14][speed]   = 80;
$einh[14][space]   = 50;
$einh[14][verbrauch]= 6;
$einh[14][ek] = 400;
$einh[14][vk] = 200;
$einh[14]['info'] = 'Die BMW R 75  mit Beiwagen war für drei Soldaten ausgelegt. Der BMW R 75-Boxer OHV-746 ccm-Zweizylinder-Motor brachte das 670 kg schwere Gefährt auf maximal 80 km/h.Das Motorrad hat eine Länge von 2,40 m und eine Höhe von 1 Meter. Die normale Bewaffnung bestand aus einem MG 34.<br />
Des weiteren verleitete die Kiste den Fahrer oft zu waghalsigen Lenkman&ouml;vern, sehr zur Erheiterung der Beifahrer.<br />
Das Motorrad ist Plasmaimmun.';
$einh[14][size]    = 2;

$einh[15][name]    = "Sammler";
$einh[15][eisen]   = 300;
$einh[15][titan]   = 230;
$einh[15][oel]     = 150;
$einh[15][uran]    = 10;
$einh[15][gold]    = 30;
$einh[15][chanje]  = 0;
$einh[15][dauer]   = 4800*pow(0.9,$gebaeude['nbz']);
$einh[15][off]     = 0;
$einh[15][def]     = 100;
$einh[15][speed]   = 15;
$einh[15][space]   = 200;
$einh[15][verbrauch]= 14;
$einh[15][ek] = 1000;
$einh[15][vk] = 500;
$einh[15][info] = 'Zu den O-Wars anf&auml;ngen, entstand das Problem, das durch die immer gr&ouml;sser werdenden Tr&uuml;mmerfelder kein vern&uuml;nftiger Krieg mehr gef&uuml;hrt werden konnte.
Dies war die Geburtsstunde des Sammlers. Dank guter Tarnung und eines besonders starken Magneten, kann der Sammler ohne Feindkontakt die begehrten Tr&uuml;mmerfelder abbauen.';
$einh[15][size]    = 1;
?>
