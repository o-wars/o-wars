<?php 
$dbh = db_connect();
$select = "SELECT * FROM `gebauede` WHERE `omni` = '".$_SESSION[user][omni]."';";
$selectResult   = mysql_query($select, $dbh);
$gebaeude = mysql_fetch_array($selectResult);

$kosten[forschungsanlage][eisen]    = 200;
$kosten[forschungsanlage][titan]    = 20;
$kosten[forschungsanlage][oel]      = 300;
$kosten[forschungsanlage][uran]     = 20;
$kosten[forschungsanlage][gold]     = 6;
$kosten[forschungsanlage][chanje] = 0;
$kosten[forschungsanlage][zeit]     = 18700*pow(0.9,$gebaeude['nbz']);
$kosten[forschungsanlage][name] = 'Forschungsanlage';
$kosten[forschungsanlage][info] = 'Die Forschungsanlage erm&ouml;glicht das gezielte Forschen nach Technologien die zum weiteren Ausbau der Basis und zur Produktion von Einheiten, Verteidigungsanlagen, Raketen etc. ben&ouml;tigt werden.
<br /><br />Maximales Forschungs-Level = Forschungsanlagen-Level mal 2
';

$kosten[basis][eisen]    = 20;
$kosten[basis][titan]    = 10;
$kosten[basis][oel]      = 200;
$kosten[basis][uran]     = 10;
$kosten[basis][gold]     = 5;
$kosten[basis][chanje] = 0;
$kosten[basis][zeit]     = 10320*pow(0.9,$gebaeude['nbz']);
$kosten[basis][name] = 'Basis';
$kosten[basis][info] = 'Die Basis bildet den Grundstock f&uuml;r den gesamten Geb&auml;ude-Komplex BASIS. Das maximale Geb&auml;ude-Level der anderen Geb&auml;ude, richtet sich nach dem aktuellen Basis-Level.';

$kosten[fabrik][eisen]    = 500;
$kosten[fabrik][titan]    = 20;
$kosten[fabrik][oel]      = 10;
$kosten[fabrik][uran]     = 0;
$kosten[fabrik][gold]     = 4;
$kosten[fabrik][chanje] = 0;
$kosten[fabrik][zeit]     = 17700*pow(0.9,$gebaeude['nbz']);
$kosten[fabrik][name] = 'Fabrik';
$kosten[fabrik][info] = 'Die Fabrik ist die zentrale Produktionsst&auml;tte deiner Basis. Hier werden deine Einheiten, Raketen und Verteidigungsanlagen gebaut. Je nach Art der zu bauenden Einheit, Rakete oder Verteidigungsanlage ist ein gewisses Fabrik-Level von N&ouml;ten.
<!--
Des weiteren dient die Fabrik als Lagerst&auml;tte f&uuml;r deine neu Produzierten Einheiten
(Pro Fabrik-Level stehen somit 10 Fabrikhanger-Pl&auml;tze zur Verf&uuml;gung).
In dieses Lager werden alle fertig produzierten Einheiten zuerst geliefert, bis die Lagerkapazit&auml;ten ersch&ouml;pft sind. Jede weitere produzierte Einheit geht dann direkt in den Hangar.
Die in der Fabrik gelagerten Einheiten, k&ouml;nnen ohne Zeitaufwand direkt in den Hanger &uuml;berf&uuml;hrt werden.-->';

$kosten[raketensilo][eisen]    = 200;
$kosten[raketensilo][titan]    = 250;
$kosten[raketensilo][oel]      = 400;
$kosten[raketensilo][uran]     = 100;
$kosten[raketensilo][gold]     = 7;
$kosten[raketensilo][chanje] = 0;
$kosten[raketensilo][zeit]     = 25260*pow(0.9,$gebaeude['nbz']);
$kosten[raketensilo][name]     = 'Raketensilo';
$kosten[raketensilo][info]     = 'Im Raketensilo werden die produzierten Raketen gelagert und zudem werden alle Raketen Besch&uuml;sse von hieraus geplant und ausgef&uuml;hrt.
Dem entsprechend entscheidet die Gr&ouml;sse deines Raketensilos nicht nur &uuml;ber die Anzahl der zu lagernden Raketen (pro Raketensilo-Level 5 St&uuml;ck), sondern auch &uuml;ber die jeweilige Trefferchance
der verschiedenen Raketen:<br />
<br />
V01-fliegende Bombe 80% pro Raketensilo-Level<br />
V02-Vergeltung Raketen 40% pro Raketensilo-Level<br />
A09-Feindflug Raketen 20% pro Raketensilo-Level<br />
A10-Amerika-Rakete 10% pro Raketensilo-Level<br />
G17-P-Brecher Raketen 5% pro Raketensilo-Level<br />
D09-Spionagerakete 100% pro Raketensilo-Level';

$kosten[nbz][eisen]    = 2000;
$kosten[nbz][titan]    = 2500;
$kosten[nbz][oel]      = 3000;
$kosten[nbz][uran]     = 610;
$kosten[nbz][gold]     = 500;
$kosten[nbz][chanje]   = 0;
$kosten[nbz][zeit]     = 172800*pow(0.9,$gebaeude['nbz']);
$kosten[nbz][name]     = 'Nano Bot Zentrum';
$kosten[nbz][info]     = 'NBZ ist die Kurzform f&uuml;r Nano-Bot-Zentrum. Die sogenannte Nano-Bot-Technologie erm&ouml;glicht es mittels Zucht, Mikro-Biologische-Maschinen mit bestimmten Aufgabengebieten in den normalen Produktions-Prozess einer Basis einzubauen. Hierdurch verringert sicht der Produktionszeitaufwand. Pro Geb&auml;ude-Level sinkt die Produktionszeit um 10%. Betroffen hiervon sind Geb&auml;ude, Raketen und Einheiten.<br />
<table border="0" style="width: 100%;background-color: rgb(210, 210, 210); text-align: left; margin-left: auto; margin-right: auto;" cellspacing="2">        
  <tr >            
    <td style="text-align: center; background-color: rgb(180, 180, 180); width: 50%; font-weight: bold;">NBZ Level<br />
    </td >            
    <td style="text-align: center; background-color: rgb(180, 180, 180); width: 50%; font-weight: bold;">Dauer<br />
    </td >          
  </tr >          
  <tr >            
    <td style="vertical-align: top; text-align: center;">0<br />

    </td >
    <td style="vertical-align: top; text-align: center;">100%<br />
    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">1<br />
    </td >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">90%<br />

    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center;">2<br />
    </td >
    <td style="vertical-align: top; text-align: center;">81%<br />
    </td >
  </tr >

  <tr >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">3<br />
    </td >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">72.90%<br />
    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center;">4<br />

    </td >
    <td style="vertical-align: top; text-align: center;">65.61%<br />
    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">5<br />
    </td >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">59.05%<br />

    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center;">6<br />
    </td >
    <td style="vertical-align: top; text-align: center;">53.14%<br />
    </td >
  </tr >

  <tr >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">7<br />
    </td >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">47.83%<br />
    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center;">8<br />

    </td >
    <td style="vertical-align: top; text-align: center;">43.05%<br />
    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">9<br />
    </td >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">38.74%<br />

    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center;">10<br />
    </td >
    <td style="vertical-align: top; text-align: center;">34.87%<br />
    </td >
  </tr >

  <tr >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">11<br />
    </td >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">31.38%<br />
    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center;">12<br />

    </td >
    <td style="vertical-align: top; text-align: center;">28.24%<br />
    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">13<br />
    </td >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">25.49%<br />

    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center;">14<br />
    </td >
    <td style="vertical-align: top; text-align: center;">22.88%<br />
    </td >
  </tr >

  <tr >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">15<br />
    </td >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">20.59%<br />
    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center;">16<br />

    </td >
    <td style="vertical-align: top; text-align: center;">18.53%<br />
    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">17<br />
    </td >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">16.68%<br />

    </td >
  </tr >
  <tr >
    <td style="vertical-align: top; text-align: center;">18<br />
    </td >
    <td style="vertical-align: top; text-align: center;">15.01%<br />
    </td >
  </tr >

  <tr >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">...<br />
    </td >
    <td style="vertical-align: top; text-align: center; background-color: rgb(180, 180, 180);">...<br />
    </td >
  </tr >
</table >';

$kosten[hangar][eisen]    = 150;
$kosten[hangar][titan]    = 60;
$kosten[hangar][oel]      = 0;
$kosten[hangar][uran]     = 0;
$kosten[hangar][gold]     = 1;
$kosten[hangar][chanje] = 0;
$kosten[hangar][zeit]     = 5460*pow(0.9,$gebaeude['nbz']);
$kosten[hangar][name]     = 'Hangar';
$kosten[hangar][info]     = 'Der Hangar ist ein weiteres Kernst&uuml;ck der Basis. Hier werden die produzierten Einheiten abgestellt. Der Ausbau des Hangars entscheidet dar&uuml;ber wieviele Einheiten sich gleichzeitig in einer Basis aufhalten k&ouml;nnen. (Dies ist zu bedenken wenn sich noch Einheiten auf Mission oder noch im Bau befinden. &Uuml;berproduzierte oder zur&uuml;ckkehrende Einheiten die hier keinen Platz mehr finden gehen verloren.) <br /><br />Pro Geb&auml;ude-Level 25 Felder.<br />Wobei jede Einheit unterschiedlich viele Felder Platz ben&ouml;tigt.';

$kosten[fahrwege][eisen]    = 200;
$kosten[fahrwege][titan]    = 20;
$kosten[fahrwege][oel]      = 5;
$kosten[fahrwege][uran]     = 0;
$kosten[fahrwege][gold]     = 2;
$kosten[fahrwege][chanje] = 0;
$kosten[fahrwege][zeit]     = 5580*pow(0.9,$gebaeude['nbz']);
$kosten[fahrwege][name]     = 'Fahrwege';
$kosten[fahrwege][info]     = 'Die Fahrwege stellen den Ausbau der Strassen innerhalb der Basis dar. Eine Basis braucht um bestimmte Einheiten im Hangar lagern zu k&ouml;nnen einen bestimmten Fahrwege-Level. (Weiteres hierzu siehe unter Einheiten.) Auch bei Bestellungen am Markt muss auf ausreichenden Ausbau der Fahrwege geachtet werden.';

$kosten[missionszentrum][eisen]    = 50;
$kosten[missionszentrum][titan]    = 100;
$kosten[missionszentrum][oel]      = 200;
$kosten[missionszentrum][uran]     = 5;
$kosten[missionszentrum][gold]     = 4;
$kosten[missionszentrum][chanje] = 0;
$kosten[missionszentrum][zeit]     = 7680*pow(0.9,$gebaeude['nbz']);
$kosten[missionszentrum][name]     = 'Missionszentrum';
$kosten[missionszentrum][info]     = 'Im Missionszentrum werden die Einheiten verwaltet. Hier k&ouml;nnen Missionen aller Art gestartet und &uuml;berwacht oder auch abgebrochen werden. <br /><br />Pro Geb&auml;ude-Level 2 Missionen ';

$kosten[agentenzentrum][eisen]    = 20;
$kosten[agentenzentrum][titan]    = 200;
$kosten[agentenzentrum][oel]      = 150;
$kosten[agentenzentrum][uran]     = 40;
$kosten[agentenzentrum][gold]     = 20;
$kosten[agentenzentrum][chanje] = 0;
$kosten[agentenzentrum][zeit]     = 13800*pow(0.9,$gebaeude['nbz']);
$kosten[agentenzentrum][name]     = 'Agentenzentrum';
$kosten[agentenzentrum][info]     = 'Das Agentenzentrum verf&uuml;gt &uuml;ber zweierlei Funktionen. Zum einen erlaubt es bei weiterem Ausbau mehr Informationen &uuml;ber fremde Missionen zu Ihrer Basis. Zum anderen erm&ouml;glicht es die nach den Anf&auml;ngen relativ schnell unterqualifizierten Infanterie-Einheiten fortzubilden und Ihnen eine neue Aufgabe als AGENTEN zuzuweisen. Hier k&ouml;nnen die neuen Agenten durch Training und Cyber-Ware zu waren Kampfmaschinen herangez&uuml;chtet werden. Durch den vielf&auml;ltigen Einsatz der Agenten ergeben sich zus&auml;tzliche strategische M&ouml;glichkeiten in Kombination mit herk&ouml;mmlicher Taktik. <br /><br />Pro Geb&auml;ude-Level 2 Agenten ';


$kosten[raumstation][eisen]    = 200;
$kosten[raumstation][titan]    = 100;
$kosten[raumstation][oel]      = 60;
$kosten[raumstation][uran]     = 250;
$kosten[raumstation][gold]     = 90;
$kosten[raumstation][chanje]   = 0;
$kosten[raumstation][zeit]     = 38400*pow(0.9,$gebaeude['nbz']);
$kosten[raumstation][name]  = 'Raumstation';
$kosten[raumstation][info]     = 'Die Raumstation erf&uuml;llt zwei Funktionen.
Sie beherbergt den Scanner und die Plasmakanone.
Mit Hilfe des Scanners ist es ihnen m&ouml;glich, andere Basen zu scannen, und die dort laufenden Missionen aufzudecken.
Die Plasmakanone bietet dir die M&ouml;glichkeit andere Missionen mit Hilfe deren Missions-ID zu beschiessen und ist f&uuml;r ein erfolgreiches Zusammenspiel mit Clanmembern und B&uuml;ndnisspartnern, aber nat&uuml;rlich auch zur Verteidigung deiner eigenen Basis von grosser Bedeutung.';

$kosten[rohstofflager][eisen]    = 200;
$kosten[rohstofflager][titan]    = 10;
$kosten[rohstofflager][oel]      = 0;
$kosten[rohstofflager][uran]     = 0;
$kosten[rohstofflager][gold]     = 1;
$kosten[rohstofflager][chanje] = 0;
$kosten[rohstofflager][zeit]     = 10440*pow(0.9,$gebaeude['nbz']);
$kosten[rohstofflager][name]     = 'Rohstofflager';
$kosten[rohstofflager][info]     = 'Das Rohstofflager dient als Speicher f&uuml;r deine Ressis.
Pro Rohstofflager-Level steigt die Lagerkapazit&auml;t deiner Basis um 7500 pro Rohstoff.
Des weiteren bietet dir das Rohstofflager die M&ouml;glichkeit deine Rohstoffe vor Angriffen zu sch&uuml;tzen.
So sch&uuml;tzt jedes Rohstofflager-Level 100 eines jeden Rohstoffes.';

$kosten[eisenmine][eisen]    = 400;
$kosten[eisenmine][titan]    = 100;
$kosten[eisenmine][oel]      = 20;
$kosten[eisenmine][uran]     = 0;
$kosten[eisenmine][gold]     = 4;
$kosten[eisenmine][chanje] = 0;
$kosten[eisenmine][zeit]     = 8160*pow(0.9,$gebaeude['nbz']);
$kosten[eisenmine][name]     = 'Eisenmine';
$kosten[eisenmine][info]     = 'Pro Level deiner Eisenmine steigt dein Ertrag um 30 Eisen.
Desweiteren bekommst du f&uuml;r jedes Level noch einen Bonus von 5% welcher allerdings nur auf die Ressis berechnet wird, die aus deiner Eisenmine gewonnen werden.';

$kosten[titanmine][eisen]    = 300;
$kosten[titanmine][titan]    = 200;
$kosten[titanmine][oel]      = 40;
$kosten[titanmine][uran]     = 0;
$kosten[titanmine][gold]     = 3;
$kosten[titanmine][chanje] = 0;
$kosten[titanmine][zeit]     = 12720*pow(0.9,$gebaeude['nbz']);
$kosten[titanmine][name] = 'Titanmine';
$kosten[titanmine][info]     = 'Pro Level deiner Titanmine steigt dein Ertrag um 20 Titan.
Desweiteren bekommst du f&uuml;r jedes Level noch einen Bonus von 5% welcher allerdings nur auf die Ressis berechnet wird, die aus deiner Titanmine gewonnen werden.
';

$kosten[oelpumpe][eisen]    = 100;
$kosten[oelpumpe][titan]    = 200;
$kosten[oelpumpe][oel]      = 10;
$kosten[oelpumpe][uran]     = 0;
$kosten[oelpumpe][gold]     = 5;
$kosten[oelpumpe][chanje] = 0;
$kosten[oelpumpe][zeit]     = 16020*pow(0.9,$gebaeude['nbz']);
$kosten[oelpumpe][name] = 'Oelpumpe';
$kosten[oelpumpe][info]     = 'Pro Level deiner Oelmine steigt dein Ertrag um 25 Oel.
Desweiteren bekommst du f&uuml;r jedes Level noch einen Bonus von 5% welcher allerdings nur auf die Ressis berechnet wird, die aus deiner Oelmine gewonnen werden.';

$kosten[uranmine][eisen]    = 100;
$kosten[uranmine][titan]    = 250;
$kosten[uranmine][oel]      = 15;
$kosten[uranmine][uran]     = 0;
$kosten[uranmine][gold]     = 10;
$kosten[uranmine][chanje] = 0;
$kosten[uranmine][zeit]     = 18720*pow(0.9,$gebaeude['nbz']);
$kosten[uranmine][name] = 'Uranmine';
$kosten[uranmine][info]     = 'Pro Level deiner Uranmine steigt dein Ertrag um 12 Uran.
Desweiteren bekommst du f&uuml;r jedes Level noch einen Bonus von 5% welcher allerdings nur auf die Ressis berechnet wird, die aus deiner Uranmine gewonnen werden.';

$kosten[scanner][name]     = 'Scanner';
$kosten[scanner][info]     = '
* Liefert R&uuml;ckkehrzeiten der Missionen <br /><br />
* Grundkosten pro Scan: 250 Uran <br />
* Kosten pro Kilometer:&nbsp; 30*Scannerlevel Uran <br /><br />
* &Uuml;berf&uuml;hrungen k&ouml;nnen nicht entdeckt werden <br /><br />
* Entschl&uuml;sselung: <br />
&nbsp;&nbsp;&nbsp;x = gescannte Basis Spionage Level <br />
&nbsp;&nbsp;&nbsp;y = dein Spionage Level <br />
&nbsp;&nbsp;&nbsp;z = dein Scanner Level <br />
&nbsp;&nbsp;&nbsp;Entschl&uuml;sselungsdauer in Minuten = (x/y)*(40-z)';

$kosten[plasma][name]     = 'Plasmakanone';
$kosten[plasma][info]     = 'Die Plasmakanone bietet dir die Möglichkeit andere Missionen mit Hilfe deren Missions-ID zu beschiessen und ist für ein erfolgreiches Zusammenspiel mit Clanmembern und Bündnisspartnern, aber natürlich auch zur Verteidigung deiner eigenen Basis von grosser Bedeutung.<br />
<br />
Wirkungsweise:<br />
<br />
*Schaden = (Uran/5+((Uran/20)*Lvl_Plasmakan.))/2 <br />
*Missionen von 0(Markt) können nicht beschossen werden<br />
*Missionen auf dem Rückweg können nicht beschossen werden<br />
<br />
* Ablauf: <br />
<br />
Plasma und Missions-Id ins entsprechende Feld eingeben
<br />Schuss auswählen
<br />"Feuerbutton" betätigen
<br />Die zerstörrten Einheiten werden angezeigt
<br />
Wichtig:<br />
<br />
Pro Level der Plasmakanone können 2 Schüsse gelagert werden.<br />
Motorräder, Panther und Nebelwerfer sind Plasmaimmun. ';
?>