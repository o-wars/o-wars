<?php 

function bbcode($text, $badwords = FALSE) {

	$regex[]   = '/  /i';
	$replace[] = ' &nbsp;';	
	
	// colors
	$regex[]   = '/\[color\=\"([a-z,A-Z,0-9,\#]*)\"\]/i';
	$replace[] = '<font color="$1">';
	$regex[]   = '/\[color\=&quot;([a-z,A-Z,0-9,\#]*)&quot;\]/i';
	$replace[] = '<font color="$1">';
	$regex[]   = '/\[\/color\]/i';
	$replace[] = '</font>';
	
	// images
	// $regex[]   = '/\[img=\"((http:\/\/)?[a-z,A-Z,\_,\-,\/,0-9,\~,\.,\:,\(,\),\ ,\&,\?]*(gif)?(jpg)?(jpeg)?(png)?)\"\]/i';
	$regex[]   = '/\[img=\"(.*)\"\]/i';
	$replace[] = '<img src="$1" />';
	$regex[]   = '/\[img=&quot;(.*)&quot;\]/i';
	$replace[] = '<img src="$1" />';

	// bold text
	$regex[]   = '/\[b\]/i';
	$replace[] = '<b>';
	$regex[]   = '/\[\/b\]/i';
	$replace[] = '</b>';
	
	// italic text
	$regex[]   = '/\[i\]/i';
	$replace[] = '<i>';
	$regex[]   = '/\[\/i\]/i';
	$replace[] = '</i>';	
	
	// underlined text
	$regex[]   = '/\[u\]/i';
	$replace[] = '<u>';
	$regex[]   = '/\[\/u\]/i';
	$replace[] = '</u>';		
	
	// centered text
	$regex[]   = '/\[center\]/i';
	$replace[] = '<center>';
	$regex[]   = '/\[\/center\]/i';
	$replace[] = '</center>';

	// right aligned
	$regex[]   = '/\[right\]/i';
	$replace[] = '<div align="right">';
	$regex[]   = '/\[\/right\]/i';
	$replace[] = '</div>';	
	
	// left aligned
	$regex[]   = '/\[left\]/i';
	$replace[] = '<div align="left">';
	$regex[]   = '/\[\/left\]/i';
	$replace[] = '</div>';		
	
	// :)
	$regex[]   = '/\:\)/i';
	$replace[] = '<img src="img/smilies/smiley.gif">';
	$regex[]   = '/\:-\)/i';
	$replace[] = '<img src="img/smilies/smiley.gif">';

	// :(
	$regex[]   = '/\:\(/i';
	$replace[] = '<img src="img/smilies/sad.gif">';
	$regex[]   = '/\:-\(/i';
	$replace[] = '<img src="img/smilies/sad.gif">';	
	
	// ;)
	$regex[]   = '/\;\)/i';
	$replace[] = '<img src="img/smilies/smiley2.gif">';
	$regex[]   = '/\;-\)/i';
	$replace[] = '<img src="img/smilies/smiley2.gif">';	
	
	// :P
	$regex[]   = '/\:p/i';
	$replace[] = '<img src="img/smilies/tounge.gif">';
	$regex[]   = '/\:\-p/i';
	$replace[] = '<img src="img/smilies/tounge.gif">';		
	
	// :D
	$regex[]   = '/\:d/i';
	$replace[] = '<img src="img/smilies/grin.gif">';
	$regex[]   = '/\:-d/i';
	$replace[] = '<img src="img/smilies/grin.gif">';		

	// :O
	$regex[]   = '/\:o/i';
	$replace[] = '<img src="img/smilies/grin.gif">';
	$regex[]   = '/\:-o/i';
	$replace[] = '<img src="img/smilies/grin.gif">';			
	
	// :8
	$regex[]   = '/\:8/i';
	$replace[] = '<img src="img/smilies/cool.gif">';
	$regex[]   = '/8\:/i';
	$replace[] = '<img src="img/smilies/cool.gif">';	
	$regex[]   = '/\:cool\:/i';
	$replace[] = '<img src="img/smilies/cool.gif">';			

	// :kaffee:
	$regex[]   = '/\:kaffee\:/i';
	$replace[] = '<img src="img/smilies/kaffee.gif">';
	
	// replace all the stuff
	$text = preg_replace($regex, $replace, $text);

	if ($badwords) {
	
		include('badwords_list.php');
		
		foreach ($badwords as $word) {
		
			$badword[] = '/'.$word.'/i';
			// $stars[]   = stars(strlen($word));
			
		}
		
		// $text = preg_replace($badword, $stars, $text);
		$text = preg_replace_callback($badword, "censor", $text);
		
	}
	
	return $text;

}

function censor($matches) {

	for ($i=1; $i <= strlen($matches); $i++) {
	
		$star .= '*';
		
	}	
	
	return $star;
	
}

?>