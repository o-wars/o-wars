<?php

function badlist() {
	
	return array(
						'jude',
						'hitler',
						'nazi',
						'npd',
						'nsdap',
						'dvu',
						'fuehrer',
						'3. reich',
						'88',
						'skinhead',
						'konzentrationslager',
						'wehrmacht'
					);

}

function badwords($text) {

	/*
	$cleaned = preg_replace(badlist(), '', strtolower($text));
	
	if ($text != $cleaned) { $r = 1; }
	*/
	$badwords = badlist();

	foreach ($badwords as $word) {
		
		if (strstr(strtolower($text), $word)){

			$r[count($r)] = $word;
			
		}
	
	}
	
	return $r;
	
}


?>