<?php
define('IN_EBB', true);
/**
Filename: upload.php
Last Modified: 2/28/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";
include_once FULLPATH."/includes/attachmentMgr.php";

if(isset($_GET['mode'])){
	$mode = var_cleanup($_GET['mode']);
}else{
	$mode = '';
}

//see if this is a guest trying to post.
if ($logged_user == "guest"){
	die($lang['accessdenied']);
}

#see if user can even upload anything.
if($groupPolicy->validateAccess(1, 26) == false){
	$error = new notifySys($lang['noattach'], true);
	$error->displayError();
}else{
    #get file values.
    $fileName = basename($_FILES['attachment']['name']);
	$encryptName = sha1($fileName);
    $fileType = $_FILES['attachment']['type'];
    $fileSize = $_FILES['attachment']['size'];
    $fileTemp = $_FILES['attachment']['tmp_name'];
    $fileExt = strtolower(substr(strrchr($fileName, "."), 1));
	$uploadFolder = FULLPATH."/uploads/";
	$uploadPath = $uploadFolder.$encryptName;

	#setup upload limit.
	if($groupPolicy->validateAccess(1, 26) == false){
		$uploadLimit = 0;
	}else{
		$uploadLimit = $boardPref->getPreferenceValue("upload_limit");
	}

	#see if an ID was set.
	if((isset($_GET['id'])) or (!empty($_GET['id']))){
		$id = $db->filterMySQL($_GET['id']);
	}else{
		$id = 0;
	}

    #load attachment manager.
	$attachMgr = new attachmentMgr();

	#validate.
	//@TODO replace attachMgr->displayUploadMsgs with notifySys->displayAjaxError() as of v3.0.0 RC2
	if ($attachMgr->uploadCount("NewUploads", $id) > $uploadLimit){
		#display error.
		exit($attachMgr->displayUploadMsgs($lang['attachlimit'], "error"));
	}
	if($fileSize > $boardPref->getPreferenceValue("attachment_quota")){
		#display error.
		exit($attachMgr->displayUploadMsgs($lang['sizelimit'], "error"));
	}
	if($fileSize == 0){
		#display error.
		exit($attachMgr->displayUploadMsgs($lang['zerofile'], "error"));
	}
	if(empty($fileName)){
		#display error.
		exit($attachMgr->displayUploadMsgs($lang['nofileentry'], "error"));
	}
	if($attachMgr->isAllowed($fileExt) == false){
		#display error.
		exit($attachMgr->displayUploadMsgs($lang['noattach'], "error"));
	}
	if(!is_writable($uploadFolder)) {
        #display error.
		exit($attachMgr->displayUploadMsgs($lang['cantwriteupload'], "error"));
	}	
	if(file_exists($uploadPath)){
		#display error.
		exit($attachMgr->displayUploadMsgs($lang['fileexist'], "error"));
	}

	#see if any errors occurred.
	if($_FILES["attachment"]["error"] == 0){
		#add attachment to upload folder.
		if ((is_uploaded_file($_FILES['attachment']['tmp_name'])) AND (move_uploaded_file($fileTemp, $uploadPath))) {
			#add attachment to db for listing purpose.
			$db->SQL = "INSERT INTO ebb_attachments (Username, Filename, encryptedFileName, File_Type, File_Size) VALUES('$logged_user', '$fileName', '$encryptName', '$fileType', '$fileSize')";
			$db->query();

			#display message.
			$attachMgr->displayUploadMsgs($file_name.'&nbsp;'.$lang['fileuploaded'], "success");
		}else{
			#display error.
			$attachMgr->displayUploadMsgs($lang['cantupload'], "error");
		}
	}
	else{
		$attachMgr->displayUploadMsgs($_FILES["attachment"]["error"], "error");
	}

}//attachment rule.
?>