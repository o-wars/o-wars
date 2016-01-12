<?php
if ($_GET['auth'] != 'isklaaso') die ('geh ma nach hause');
include ("functions.php");

$dbh = db_connect();

$res = mysql_query("SELECT * FROM user WHERE timestamp > ".(time()-3600*15*24)." ORDER BY timestamp ASC;");
while ($user = mysql_fetch_array($res, MYSQL_ASSOC)){

  if (($user['timestamp']+3600*14*24) > time()) {
    $u[] = '<tr><td>'.$user['omni'].'</td><td>'.$user['name']."</td><td><b>".date('d.m.y H:i:s', $user['timestamp']+3600*24*7*2)."</b></td></tr>";
  } else {
    $u[] = '<tr><td>'.$user['omni'].'</td><td>'.$user['name']."</td><td>".date('d.m.y H:i:s',
$user['timestamp']+3600*24*7*2)."</td></tr>";  }

}

echo "<table>";

foreach ($u as $user) {

  echo $user;

}

echo "</table>";

ob_flush();

?>
