<?php
// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";
include "inc/bbcode.php";

// check session
logincheck();

$_GET['id'] = number_format($_GET['id'],0,'','');

// html head setzen
$content = template('head');
$content = tag2value('onload', '', $content);

$content .= '<br />';
$content .= '</center>';

// mit datenbank verbinden
$dbh = db_connect();

$sql = "SELECT wmspiel FROM user WHERE omni = ".$_SESSION['user']['omni']." LIMIT 1;";
$res = mysql_query($sql);
$row = mysql_fetch_array($res);

if($_POST['store'] and !$row['wmspiel'] and time() <= mktime(16,00,00,6,30,2006))
{
  $sql = "UPDATE user SET wmspiel='".$_POST['m1'].":".$_POST['m2']."' WHERE omni = ".$_SESSION['user']['omni']." LIMIT 1;";
  $res = mysql_query($sql);

  $content .= "<b>Dein Tipp wurde erfolgreich abgegeben!</b><br /><br />";
}
elseif($_POST['store'] and $row['wmspiel'])
{
  $content .= "<b>Du hast bereits getippt!</b><br /><br />";
}
elseif(time() > mktime(16,00,00,6,30,2006))
{
  $content .= "<b>Leider ist es nun zu Sp&auml;t um eine Wette abzugeben!</b><br /><br />";
}

$sql = "SELECT wmspiel FROM user WHERE omni = ".$_SESSION['user']['omni']." LIMIT 1;";
$res = mysql_query($sql);
$row = mysql_fetch_array($res);

list($m1, $m2) = explode(':', $row['wmspiel']);

$content .= '<b>Das O-Wars WM 2006 Gewinnspiel:</b><br />
<br />
Hier hast du die m&ouml;glichkeit, einen Tipp abzugeben, wie am Freitag, den 30.06.2006, das Spiel Deutschland gegen Argentinien ausgeht. Solltest du das korrekte Ergebnis, getippt haben, erwarten dich als <b><i>Gewinn 50 Einheiten deiner wahl</b></i>.<br />
<br />
<center>
<form enctype="multipart/form-data" action="wmspiel.php?'.SID.'" method="post">
  <table cellspacing="5">
    <tr>
      <td align="center" style="width: 120px;">
        <b>Deutschland</b>
      </td>
      <td align="center" style="width: 120px;">
        <b>Argentinien</b>
      </td>
    </tr>
    <tr>
      <td align="center">
        <input type="text" name="m1" value="'.$m1.'" style="width: 50px;" />
      </td>
      <td align="center">
        <input type="text" name="m2" value="'.$m2.'" style="width: 50px;" />
      </td>
    </tr>
    <tr>
      <td colspan="2" align="center">
        <input type="hidden" name="store" value="1" />
        <input type="submit" value="Tipp abgeben!">
      </td>
    </tr>
  </table>
</form>
</center>
<br />
<i>Alle Wetten m&uuml;ssen bis Freitag, den 30.06.2006 um 16:00 Uhr abgegeben sein.</i><br /><br />
<b>Weitere Spiele werden nat&uuml;rlich folgen.</b>';

// generierte seite ausgeben
echo $content.'</body></html>';
?>