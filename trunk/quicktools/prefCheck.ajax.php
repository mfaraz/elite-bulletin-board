<?php
define('IN_EBB', true);

/**
Filename: prefCheck.ajax.php
Last Modified: 3/5/2010

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


if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = '';
}

switch($action){
	case 'spelling':
		//see if  pspell exists.
		if (function_exists('pspell_check')) {
   			echo 'OK';
		} else {
			die('Spell Check has been disabled by the site administrator.');
		}
	break;
	case 'attachment':
		#see if user can add an attachment.
		if($groupPolicy->validateAccess(1, 26) == false){
	        die('Attachments hsa been disabled by the site administrator.');
	    }else{
	        echo 'OK';
	    }
	break;
	default:
	    die($lang['invalidaction']);
	break;

}
 ?>