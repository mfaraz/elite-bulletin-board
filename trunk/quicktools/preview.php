<?php
define('IN_EBB', true);

/**
Filename: preview.php
Last Modified: 12/12/2010

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

include "../config.php";
include FULLPATH."/header.php";

if ($logged_user == "guest"){
	exit($lang['guesterror']);
}else{
	//get our variable needed to grab the data from our editor.
	$previewPost = var_cleanup($_POST['data']);

	//see if the user added anytihng to preview.
	if($previewPost == ""){
		exit($lang['notopicbody']);
	}else{
		#format string.
		$formatMsg = nl2br(smiles(BBCode(language_filter($previewPost, 1), true)));

		#output formatted data.
		echo $formatMsg;
	}
}

?>
