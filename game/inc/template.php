<?php
function template($template) {
	// gets template and replaces template_path
	$path = './templates/standard/'.$template.'.html';
	$template_path = './templates/standard/';
	if ($_SESSION['user']['graphic']) { $style_path = $_SESSION['user']['graphic']; }
	elseif (!$_SESSION['user']['style']) { $style = "standard"; }
	else { $style = $_SESSION['user']['style']; }
	if (!$style_path) {$style_path    = './style/'.$style.'/';}
	$file = @fopen($path, 'r');
	$template = fread($file, filesize($path));

	$template = str_replace('%template_path%', $template_path, $template);
	$template = str_replace('%style_path%', $style_path, $template);

	$template = str_replace('SID', SID, $template);

	return $template;
}
?>