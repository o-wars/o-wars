<?php
//////////////////////////////////
// clanboard.php                //
//////////////////////////////////
// Letzte Aenderung: 15.09.2004 //
// Version:          0.0        //
//////////////////////////////////

// starten der session
session_name('SESSION');
session_start();

// Basisfunktionen laden
include "functions.php";

// check session
logincheck();

// html head setzen
$content  = template('head');
$content = tag2value('onload', '', $content);

// get playerinfo template and replace tags
$status  = template('playerinfo');
$status  = tag2value('name', $_SESSION['user']['name'], $status);
$status  = tag2value('base', $_SESSION['user']['base'], $status);
$status  = tag2value('ubl',  $_SESSION['user']['omni'], $status);
$status  = tag2value('points',$_SESSION['user']['points'], $status);
$content .= $status;

// ressourcen berechnen und ausgeben
$ressis = ressistand($_SESSION['user']['omni']);
$content .= $ressis['html'];

$content .= '<br /><b>O-Wars Foren:</b><br />';

// mit datenbank verbinden
$dbh = db_connect();

$content .= template('forum_search');
$content .= template('forum_searchresults');
$content .= '<b>Suchergebnisse:</b>';

if ($_POST['archiv']) {
		
	$archiv = 500;
	$content = tag2value('checked_archiv', 'checked="checked"', $content);
		
} else {

	$archiv = 499;

}

if ($_POST['mode'] == 'and') {
		
	$mode = 'and';
	$content = tag2value('checked_and', 'checked="checked"', $content);
		
} elseif ($_POST['mode'] == 'xor') {
		
	$mode = 'xor';
	$content = tag2value('checked_xor', 'checked="checked"', $content);
		
} elseif ($_POST['mode'] == 'exact') {
		
	$mode = 'exact';
	$content = tag2value('checked_exact', 'checked="checked"', $content);
		
} else {
	
	$mode = 'xor';
	$content = tag2value('checked_xor', 'checked="checked"', $content);
	
}

if ($_POST['search']) {
	
	if ($mode == 'exact') {
	
		$search[] = $_POST['search'];
		
	} else {
	
		$search = explode(' ', $_POST['search']);
	
	}
	
	foreach ($search as $word) {
		
		if (!$sql) 	{ 
			
			$sql  =  "SELECT * FROM `forum_posts` WHERE `fid` <= ".$archiv." AND `text` LIKE '%".$word."%'"; 
			$sql2 =  "SELECT * FROM `forum_threads` WHERE `fid` <= ".$archiv." AND `subject` LIKE '%".$word."%'";
			
		}
		else 		{ 
			
			$sql  .= " ".$mode." `text` LIKE '%".$word."%'"; 
			$sql2 .= " ".$mode." `subject` LIKE '%".$word."%'";
			
		}
	
	}
	
	$piece = template('forum_threads_piece');
	$posts = mysql_query($sql." ORDER BY `fid`;");
	
	for ($post = mysql_fetch_array($posts, MYSQL_ASSOC); $post; $post = mysql_fetch_array($posts, MYSQL_ASSOC)) {
	
		if (!$tlist[$post['tid']]) {
			
			$tlist[$post['tid']]       = 1;
			$thread      = mysql_query("SELECT `subject` FROM `forum_threads` WHERE `id` = '".$post['tid']."' LIMIT 1;");
			$thread		 = mysql_fetch_array($thread, MYSQL_ASSOC);

			$num_posts	 = mysql_num_rows(
								mysql_query("SELECT `id` FROM `forum_posts` WHERE `tid` = ".$post['tid'].";")) -1;
			
			list($text)	 = mysql_fetch_array(
								mysql_query("SELECT `text` FROM `forum_posts` WHERE `tid` = ".$post['tid']." ORDER BY `id` ASC LIMIT 1;"));
								
			$poster		 = mysql_query("SELECT user.name as name FROM forum_posts,user WHERE forum_posts.tid = ".$post['tid']." and user.omni = forum_posts.uid ORDER BY forum_posts.id ASC LIMIT 1;");
			$poster		 = mysql_fetch_array($poster, MYSQL_ASSOC);
			
			$lastpost	 = mysql_query("SELECT user.name as name, forum_posts.time as time FROM forum_posts,user WHERE forum_posts.tid = ".$post['tid']." and user.omni = forum_posts.uid ORDER BY forum_posts.id DESC LIMIT 1;");
			$lastpost	 = mysql_fetch_array($lastpost, MYSQL_ASSOC);
			
			if (!$lastpost['name']) {
				
				$lastpost['name'] = 'gel&ouml;schter Spieler';
				
			}
			
			if (!$poster['name']) {
				
				$poster['name'] = 'gel&ouml;schter Spieler';
				
			}
			
			if (!$flist[$post['fid']]) {
			
				$flist[$post['fid']] = 1;
				$forum	  = mysql_fetch_array(
								mysql_query("SELECT `name` FROM `forum_foren` WHERE `id` = ".$post['fid']." LIMIT 1;"), MYSQL_ASSOC);
				$content .= '<tr><td colspan="4">&nbsp;'.$forum['name'].':</td></tr>';
				
			}
			
			$newpiece    = tag2value('tid', 	 $post['tid'],       $piece);
			$newpiece    = tag2value('subject',  '<b><a href="forum_showthread.php?tid='.$post['tid'].'&amp;'.SID.'">'.$thread['subject'].'</a></b><br />&nbsp;'.substr($text, 0, 50).'...', $newpiece);
			$newpiece    = tag2value('replys',   $num_posts,         $newpiece);
			$newpiece    = tag2value('poster',   $poster['name'],    $newpiece);
			$newpiece    = tag2value('lastpost', $lastpost['name'].'<br /><i>'.date('d.m.y - H:i',$lastpost['time']).'</i>', $newpiece);
			
			$content .= $newpiece;
			
		}
	}
}

// generierte seite ausgeben
$content = tag2value('search', $_POST['search'], $content);
$content .= template('forum_searchresults_end');
echo $content.template('footer');
?>