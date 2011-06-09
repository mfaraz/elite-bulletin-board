<?php
define('IN_EBB', true);

/**
Filename: filemanager.php
Last Modified: 2/22/2011

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

require "../config.php";
include FULLPATH."/header.php";
include_once FULLPATH."/includes/attachmentMgr.php";

if(isset($_GET['mode'])){
	$mode = var_cleanup($_GET['mode']);
}else{
	$mode = '';
}

if ($logged_user == "guest"){
	exit($lang['guesterror']);
}else{

    switch ( $mode ){
    case 'delete':
        #get form values.
    	if(isset($_GET['id'])){
    		$id = $db->filterMySQL($_GET['id']);
    	}else{
			echo $lang['noattachid'];
    	}
    	#get filename from db.
    	$db->SQL = "SELECT Filename FROM ebb_attachments WHERE id='$id'";
		$attachDel = $db->fetchResults();
		
	    #delete file from web space.
	    $delattach = @unlink (FULLPATH.'/uploads/'. $attachDel['Filename']);
	    if($delattach){
	    	#remove entry from db.
	    	$db->SQL = "DELETE FROM ebb_attachments WHERE id='$id'";
			$db->query();

			#AJAX Code to complete process.
			echo $attachDel['Filename'].'&nbsp;'.$lang['fdeleteok'];
	    }else{
			echo $lang['cantdelete'];
	    }
    break;
    default:
		$db->SQL = "SELECT id, Filename, File_Size FROM ebb_attachments WHERE Username='$logged_user' AND tid='0' AND pid='0'";
		$attachCount = $db->affectedRows();
		$uploads = $db->query();

		if($attachCount > 0){
			#load attachment manager.
			$attachMgr = new attachmentMgr();
			$attachMgr->attachmentManager("newentry");
		}
    break;
    }
}//end guest check.
?>