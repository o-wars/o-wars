<?php
// starten der session
session_name('SESSION');
session_start();

include "functions.php";

echo template('head');

echo nl2br("</center><table><tr><td valign=\"top\" style=\"width:100%\"><b><font size='+1'>O-Wars CHANGELOG:</font></b>

<b>v.1.1.2:</b> (01.02.2006) 

	* Portal ueberarbeitet und das Forum dort eingebaut
	* Casino zeigt nun auch bei verlorenen Spielen die UBL des Gegners an
	* neue BBcode Funktion mit neuen Funktionen gebaut
	* Badwords-Filter fuer die sanften Gemueter
	* Session wird nun auch korrekt uebergeben, wenn der Sektor gewechselt wird auf der Karte
	* graph. Statistiken ueber die Entwicklung der Punkte (ap/kp/pp/gp)
	* Fehler, das falsche Orden oder TF in den Punktelisten angezeigt wurden behoben
	* CSV-Schnittstelle der Karte und Punktelisten (wer Interesse hat meldet sich bei mir)
	* experimentelle Karte zeigt nun die richtigen Entfernungen an
	* experimentelle Karte zeigt nun die UBL bei unbewohnten Feldern an
	* experimentelle Karte zeigt nun beendete Kriege nichtmehr bei Kriegsgegnern an
	* Profil in den Punktelisten verlinkt

<b>v.1.1.1:</b> (25.01.2006) 

	* Smilie und Tag Legende nun im Forum eingebaut
	* Archiv fuer alte Beitraege im Forum eingebaut
	* Suchfunktion im Forum integriert
	* Profil mit Userpic eingebaut (Forum und Karte)
	* im Menu wird nun angezeigt, wieviele offene Herrausforderungen im Casino warten

<b>v.1.1.0:</b> (17.01.2006) 

	* maximale Laenge fuer Betreff bei IGM's auf 50 Zeichen gesetzt
	* Clanrundmail eingebaut (unter 'mein Clan')
	* Casinogold zieht nun mit um
	* Nachrichten lassen sich nun aus dem Posteingang loeschen
	* Einheiten gehen bei Ueberfuehrungen nun auch nichtmehr unwiederbringlich verloren
	* falsche Anforderungen 125mm PAK berichtigt
	* Punktelisten werden nun alle 6h neu berechnet
	* Save-Basen zum einfacheren Saven
	* Dauer bis Threads ohne neue Posts aus dem Clanforum geloescht werden von 1ner auf 2 Wochen erweitert

<b>v.1.0.9:</b> (16.12.2005) 

	* Experimentelle AJAX Karte eingebaut
	
<b>v.1.0.8:</b> (13.12.2005) 

	* Casino nun auch multiplayerfaehig	
	
<b>v.1.0.7:</b> (09.12.2005) 

	* Onlinestatus fuer Clanmember unter *mein Clan* sichtbar
	* Orden und Statusinfo sowie Overlib mit Infos zum aktuellen TF der jeweiligen Basen in den Punktelisten

<b>v.1.0.6:</b> (07.12.2005) 

	* Karte zeigt nun auch Sektor 0 wenn man in einem anderen Sektor zuhause ist.
	* Stylesheets und HTML angepasst fuer Grafikpacks
	* Claninfo verlinkt nun auf Kriegsgegner
	* gzip-Kompression fuer schnelleren Seitenaufbau eingeschaltet
	* WiKi unter Anleitung verlinkt
	* Willkommens-IGM mit hinweis auf WiKi hinzugefuegt

<b>v.1.0.5:</b> (02.12.2005) 

	* es wird nun ueberall der abgerundete Ressi-Stand angezeigt
	* verschiedene Styles sind nun unter Einstellungen verfuegbar

<b>v.1.0.4:</b> (16.11.2005) 

	* Farbliche Kennzeichnung der bereits erreichten Anforderungen fuer Einh, Raks, Def klappt nun auch bei der Panzerung
	* Werbung liegt nun auf anderer Subdomain und sollte daher keine Probs mehr machen
	
<b>v.1.0.3:</b> (15.11.2005) 

	* Login, falls falscher Zahlencode kompfortabler gemacht
	* Gold beim Ressihandel auf dem Markt hinzugefuegt
	* PW-Reminder remidet wieder richtig :P

<b>v.1.0.2:</b> (14.11.2005) 

	* Anforderungen fuer Panzerfaustschuetzen werden nun richtig angezeigt
	* Clans mit mehr wie 20% aller Spieler bekommen keinen Rohstoff-Bonus mehr
	* Sprengminen haben nun auch Herstellungskosten in der Beschreibung
	* Angabe der aktuellen Rohstoffe wird nun immer abgerundet
	* Farbcodes koennen nun auch im Betreff von IGM's benutzt werden
	* Zahlencode wieder aktiviert (Schutz vor Bot's)
	* Multi-Login-Logger loggt wieder richtig
	* den ;) smilie hinzugefuegt
	* eigene Foren-Beitraege sind nun editierbar
	* ein bischen Werbung eingebaut (ich denke mal ist noch angenehm)
	* Techtree eingebaut
	* Farbliche Kennzeichnung der bereits erreichten Anforderungen fuer Einh, Raks, Def
	* Das IRC Applet kann nun OnConnect Scripte ausfuehren (siehe Einstellungen)
	* Beim Ressihandel auf dem Markt wird angezeigt wieviel noch vorhanden ist
	* man kann nun eine aenderbare 2. eMail angeben, an die das PW geschickt werden kann
	
<b>v.1.0.1:</b> (06.11.2005) 

	* man kann sich nicht mehr selber angreifen
	* ein paar Emoticons eingebaut
	* Missionen von 0 werden in den Stats im Portal nicht mehr angezeigt

<b>v.1.0.0:</b> (01.11.2005) 

	* Noob-Schutz bis 50.000 Punkte erweitert
	* Plasma-Noob-Schutz bis 30.000 Punkte erweitert

<b>v.1.0RC9:</b> (19.10.2005) 

	* Wichtige Berichte werden nun farblich gekennzeichnet
	* Ungelesene Berichte und Nachrichten werden nun Rot gekennzeichnet
	* Ungelesene Berichte und Nachrichten stehen nun immer als oberstes
	* Berichte koennen nun geloescht werden
	* Ranglisten zeigen nun 100 Spieler pro Seite
	* Maximale Anzahl der Runden in einem Kampf auf 100 gesetzt 
	(damit der Eventhandler nicht haengt wenn z.B. 1 Soldat auf 300 Sammler trifft;)

<b>v.1.0RC8:</b> (18.10.2005) 

	* Forum ueberarbeitet
	* Nachrichten werden nun Seitenweise angezeigt (20 pro Seite)
	* Berichte werden nun Seitenweise angezeigt (20 pro Seite)
	* Liste der Beitraege im Forum wird nun Seitenweise angezeigt (20 pro Seite)
	* Antworten auf Beitraege im Forum werden nun Seitenweise angezeigt (20 pro Seite)

<b>v.1.0RC7:</b> (15.10.2005) 

	* Umzugsfunktion unter Einstellungen hinzugefuegt
	* ein paar kleine Aenderungen hier und da ;)

<b>v.1.0RC6:</b> (11.10.2005) 

	* Formel zur Berechnung der Dauer beim Missionsbeschuss mit Raketen berichtigt
	* Countdown beim Raketenbeschuss hinzugefuegt

<b>v.1.0RC5:</b> (07.10.2005) 

	* Beim Plasmabeschuss muss nun zusaetzlich eine PID (Plasma-ID) mit angegeben werden, 
	  damit man nichtmehr per zufall Missionen beschiessen kann. 
	  Falsche MissionsID oder PID = der Schuss ist weg
	* Man sieht die Missions ID immer, egal welches Spio Level, 
	  dafuer verschwindet ab einer Spionagedifferenz von 8 Leveln stattdessen die PID.

<b>v.1.0RC4:</b> (04.10.2005) 

	* Markierungen auf der Karte und Clanzugehoerigkeit werden nun auch geloescht wenn ein Spieler geloescht wird
	* Fehler, das Anforderungen fuer Panther nicht richtig angezeigt werden behoben
	* Fehler, das Anforderungen fuer 75mm PAK Md.40 nicht richtig angezeigt werden behoben

<b>v.1.0RC3:</b> (03.10.2005) 

	* Spieler die laenger wie 6 Wochen inaktiv sind werden nach dem naechsten erfolgreichen Angriff geloescht

<b>v.1.0RC2:</b> (02.10.2005) 

	* Statistiken zu den einzelnen Rohstoffen auf dem Markt
	* Clans koennen sich nichtmehr selber den Krieg erklaeren
	* Clantags/Clannamen koennen nichtmehr doppelt vorkommen

<b>v.1.0RC1:</b> (31.09.2005) 

	* Fehler, das bei der Registrierung der Basisname nicht uebernommen wurde behoben
	* Missionsziel kann nun beim Planen der Mission geaendert werden
	* Scanner bringt nun pro Level 2 Min weniger Ungenauigkeit statt 1 Min
	* Scannkosten steigen pro Scanner Level um 30 Uran pro Kilometer

<b>v.0.26a:</b> (30.09.2005) 

	* maximaler Level wird nun bei allen Forschungen angezeigt
	* Login & Session handling neu gemacht
	* neues Forum (kein extra Foren Account & Login mehr noetig)
	* Clanforum ist nun im normalen Forum integriert (die anderen Clanforen verschwinden in der Offiz. Version)
	* Textformatierung in Nachrichten ist nun auch moeglich
	* Kampfpunkte werden nun auf 2 Kommastellen genau gerechnet
	* Karte etwas ueberarbeitet

<b>v.0.25a:</b> (28.09.2005) 

	* Javascript beim Raketenbau rechnet nun richtig
	* Registrierung neu gemacht
	* archivierte Berichte/Nachrichten/Clanwars sollten nun ueberall richtig angezeigt werden
	* maximaler Level wird nun bei allen Gebaeuden angezeigt

<b>v.0.24a:</b> (27.09.2005) 

	* Fehler bei der Anzeige der archivierten Nachrichten behoben, das statt Posteingang da %box% stand
	* Fehler bei Amerika Raketen behoben, das getroffene 125mm PAKs als 88mm PAKs im Trefferbericht standen
	* Spionageraketen die auf unbewohnten Feldern einschlagen, zeigen nichtmehr von jeder Ressi-Sorte 5.000 an
	* Panther sind nun auch Plasmaimmun
	* Neues, erweitertes Clanforum eingefuehrt
	* Zuletzt beendete Clanwars werden nun als oberstes angezeigt

<b>v.0.23a:</b> (26.09.2005) 
	
	* Javascript zur Berechnung der Kosten nun auch beim Verteidigungsbau
	* Javascript zur Berechnung der Kosten nun auch beim Raketenbau
	* Javascript zur Berechnung der Kosten nun auch beim kaufen von Einheiten auf dem Markt
	* Basisstats erweitert
	* Ausgabe beim starten der Missionen erweitert
	* Ausgabe der einzelnen Runden bei Kaempfen geaendert
	* Neue Spieler die sich noch nie eingelogged haben werden nichtmehr als inaktiv angezeigt
	* Archivierte Berichte/Nachrichten werden nurnoch nach Anforderung angezeigt
	* Beendete Clanwars werden nurnoch nach Anforderung angezeigt
	* Maximalen Level der Raumstation auf 10 gesetzt
	* Portalseite ueberarbeitet
	* Markt ueberarbeitet
	* Menu ueberarbeitet
	* Kampfsimulator ueberarbeitet
	* Fehler bei der Verarbeitung zu grosser Zahlen in der Fabrik behoben
	* 10 Minuten Ressis ueberlagern kostet nun 2 statt 5 Chanje
	* Fehler, das Clanwars nicht richtig beendet wurden wenn sich ein Clan aufgeloest hatte behoben
	* Marktpreise werden nun alle 6h neu berechnet

<b>v.0.22a:</b> (19.09.2005) 
	
	* 17+4: Bank muss nun auch noch ziehen, wenn Spieler sich ueberzogen hat
	* 17+4: Man kann nichtmehr ins Minus kommen, da bei verlorenem Spiel nicht mehr der doppelte Einsatz abgezogen wird
	* Javascripte gehen nun auch alle im Internet Explorer
	* Raumstation wird nun auch im Internet Explorer richtig dargestellt
	* Inaktive Spieler koennen nun auch mit Raketen beschossen werden
	* beendete Clanwars werden nun auch wenn sich ein Clan aufgeloest hat mit richtigem Tag angezeigt
	* Neuer Javascript zur Berechnung der Kosten wenn man mehrere Einheiten baut

<b>v.0.21a:</b> (04.09.2005) 
	
	* Kommastellen werden nun bei den Kampfpunkten in der Einheitenbeschreibung angezeigt
	* Casino mit 17+4 eingebaut

<b>v.0.20a:</b> (08.08.2005) 

	* Preis fuer P-Brecher Raketen runtergesetzt
	* Fehler bei Feindflug Raketen behoben (Treffer wurden falsch angezeigt)
	* Karte angepasst fuer mehr als einen Sektor
	* Karten HTML Quelltext verkleinert, da die Karten extrem gross werden
	* Fehler bei der Anzeige der Kosten der Abrechnung beim Ressi-Kauf behoben (nur die Bestaetigung war Falsch, gespeichert wurde alles richtig)
	* In der Beschreibung von Einheiten, Raketen und Def-Anlagen den Baupreis hinzugefuegt
	* Maximales Chanje was man im Kampf bekommen kann verdoppelt
	* einige Tippfehler behoben
	* Markt funktionierte nicht richtig im Opera, Problem behoben
	* wenn der Verteidiger noch Minen hatte, galt der Verteidiger nicht als vernichtet, und dann gab es auch kein Chanje fuer diesen Kampf, Fehler behoben
	
<b>v.0.19a:</b> (15.07.2005) 

	* Auf dem Markt koennen nun auch mehrere Einheiten gleichzeitig gekauft werden
	* Ziehlreihenfolge bei 'V01 - Fliegende Bombe' Raketen geaendert, so das Elitesoldaten nun nicht mehr getroffen werden
	* wenn Fehler beim starten der Mission wird nun immer der Grund angegeben
	* genaue Angabe von welcher Mission Einheiten zurueckkommen im Betreff der Berichte
	* Ressourcen fuer 5 Chanje 10 Minuten ueberlagerbar
	* neue 'SQL-Injection-Protection'
	* 'kein Betreff' als Betreff gesetzt falls Betreff nur ein oder mehrere Leerzeichen ist
	* Noobschutz bei Raketen auch auf 30.000 Punkte gesetzt 

<b>v.0.18a:</b> (27.06.2005) 

	* Signatur fuer Nachrichten unter Einstellungen hinzugefuegt
	* Formel in der Beschreibung der Plasmakanone berichtigt

<b>v.0.17a:</b> (25.06.2005) 

	* Neue Basisspezifische Statistiken
	* Neues Ingame Logo

<b>v.0.16a:</b> (23.06.2005) 

	* Zerstoerte Verteidigungsanlangen werden nun nach dem Kampf repariert sofern dies noch moeglich ist (zufall)

<b>v.0.15a:</b> (22.06.2005) 

	* Gesamtpunkte hinzugefuegt (Ausbaupunkte + (Kampfpunkte * 10))
	* Minentechnik erhoeht nun pro Level das man ueber dem zum Bau benoetigten Level hat die Baugeschwindigkeit um 10%
	* Defensiv Anlagen staerker gemacht	
	* Koenigs/Jagdtiger etwas staerker gemacht
	* Fehler bei Feindflug Raketen behoben, das wenn 75mm PAK`s getroffen wurden stattdessen 88mm PAK`s angezeigt werden
	* Leerzeichen koennen nun auch zur Formatierung der Clanbeschreibung genutzt werden

<b>v.0.14a:</b> (21.06.2005) 

	* Ziel wird angezeigt wenn Mission gestartet wurde
	* Ueberfuehrungen zu inaktiven gesperrt
	* Es wird nichtmehr eine Mine abgezogen, wenn Einheiten angreifen, die keinen Schaden durch die Mine bekommen wuerden
	* Bug mit nichteingelagerten Einheiten bei Ueberfuehrung behoben wenn nurnoch 2 Restplatz sind
	* Plasma auf nicht vorhandene Missions ID's geht verloren
	* Bei Beendigung eines Clanwars durch Friedensangebot bekommen nun alle Beteiligten eine IGM
	* Friedensangebote koennen nun auch zurueckgezogen werden
	* Maximales Basis Level: 25 gesetzt
	* Maximales Forschungslabor Level: 10 gesetzt
	* Entschluesselungsgeschwindigkeitsbonus beim Scanner pro Level von 60 auf 90 Sec erhoeht
	* Scannerkosten pro Kilometer sinkt pro Level um 2 Uran
	* Fehler bei der Chanjevergabe wenn noch Minen beim Verteidiger sind behoben
	* CHANGELOG eingefuehrt

</td></tr></table></body></html>
");
?>