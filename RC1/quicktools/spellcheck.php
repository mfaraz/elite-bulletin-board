<?php
define('IN_EBB', true);

/**
Filename: spellcheck.php
Last Modified: 3/5/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
#make sure the call is from an AJAX request.
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
	die("<strong>THIS PAGE CANNOT BE ACCESSED DIRECTLY!</strong>");
}

require_once "../config.php";
require_once FULLPATH."/header.php";

//setup headers to output correctly and prevent caching.
header('Content-type: application/json');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

#see is pspell is loaded.
if (!function_exists('pspell_check')) {
	die('PSPELL is required for this function to work.');
}else{
	foreach($_REQUEST as $key => $value) {
		$$key = html_entity_decode(urldecode(stripslashes(trim($value))));
	}

	// load the dictionary
	$pspell_link = pspell_new("en");

	// return suggestions
	if (isset($suggest)) {
		exit(json_encode(pspell_suggest($pspell_link, urldecode($suggest))));
	} elseif (isset($text)) {
		// return badly spelt words
		$words = array();
		foreach($text = explode(' ', urldecode($text)) as $word) {
			if (!pspell_check($pspell_link, $word) and !in_array($word, $words)) {
				$words[] = $word;
			}
		}
		exit(json_encode($words));
	}
}
?>