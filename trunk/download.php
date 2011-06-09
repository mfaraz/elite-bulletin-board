<?php
define('IN_EBB', true);
/**
Filename: download.php
Last Modified: 2/28/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
include "config.php";
include FULLPATH."/header.php";

#obtain download ID.
$id = $db->filterMySQL($_GET['id']);

#see if any important ids are left blank.
if(empty($id)){
	#send that user to the index page.
    redirect('index.php', false, 0);
}

#Check to see if the download script was called.
if (basename($_SERVER['PHP_SELF']) == 'download.php'){

	#see if this was accessed directly.
	if(!isset($_SERVER['HTTP_REFERER'])){
		$error = new notifySys($lang['accessdenied'], true);
		$error->genericError();
	}

	#see if guests or authorized users can download content.
	if($groupPolicy->validateAccess(1, 29) == false){
		$error = new notifySys($lang['accessdenied'], true);
		$error->genericError();
	}elseif(($boardPref->getPreferenceValue("allow_guest_downloads") == 0) and ($logged_user == "guest")){
		$error = new notifySys($lang['accessdenied'], true);
		$error->genericError();
	}
	#see if attachment is listed, if so proceed.
	$db->SQL = "SELECT Filename, encryptedFileName, Download_Count FROM ebb_attachments WHERE id='$id' LIMIT 1";
    $attachCount = $db->affectedRows();
    $attachFetch = $db->fetchResults();

	if ($attachCount == 1){
		#replace space code with an actual space.
		$file = str_replace('%20', ' ', $attachFetch['Filename']);
		$dwnloadPath = FULLPATH."/uploads/".$attachFetch['encryptedFileName'];

		#see if the user is trying to access another directory.
		if (substr($file, 0, 1) == '.' || strpos($file, '..') > 0 || substr($file, 0, 1) == '/' || strpos($file, '/') > 0){
			#Display invalid action error.
            $error = new notifySys($lang['invalidaction'], true);
			$error->genericError();
		}

		#check if the file exists, if it doesn't, fire an error message and kill the script
		if(!file_exists($dwnloadPath)){
            $error = new notifySys($lang['nofile'], true);
			$error->genericError();
		}
		$ext = strtolower(substr(strrchr($attachFetch['Filename'], "."), 1));

		#Determine correct MIME type.
		switch($ext){
			case "asf":     $type = "video/x-ms-asf";                break;
			case "avi":     $type = "video/x-msvideo";               break;
			case "exe":     $type = "application/octet-stream";      break;
			case "mov":     $type = "video/quicktime";               break;
			case "mp3":     $type = "audio/mpeg";                    break;
			case "mpg":     $type = "video/mpeg";                    break;
			case "mpeg":    $type = "video/mpeg";                    break;
			case "rar":     $type = "encoding/x-compress";           break;
			case "txt":     $type = "text/plain";                    break;
			case "wav":     $type = "audio/wav";                     break;
			case "wma":     $type = "audio/x-ms-wma";                break;
			case "wmv":     $type = "video/x-ms-wmv";                break;
			case "zip":     $type = "application/x-zip-compressed";  break;
			default:        $type = "application/force-download";    break;
        }
		#Fix IE bug.
		$headerFile = (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) ? preg_replace('/\./', '%2e', $file, substr_count($file, '.') - 1) : $file;
		header("Pragma: public"); #required.
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); #some browsers require this
		header("Content-Type: $type");

		#declares file as an attachment.
		header("Content-Disposition: attachment; filename=\"" . $headerFile . "\";");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($dwnloadPath));
	
        #Send file for download.
		$stream = fopen($dwnloadPath, 'rb');
		if ($stream){
			while(!feof($stream) && connection_status() == 0){
				#reset time limit for big files.
				set_time_limit(0);
				print(fread($stream,1024*8));
				flush();
			}
			fclose($stream);
		}
		#increase the download counter by 1.
		$newDwnloadCount = $attachFetch['Download_Count'] + 1;
		$db->SQL = "UPDATE ebb_attachments SET Download_Count='$newDwnloadCount' WHERE id='$id'";
		$db->query();

	}else{
		#display an error here.
		$error = new notifySys($lang['notfound'], true);
		$error->genericError();
	}
}else{
	#Display invalid action error.
	$error = new notifySys($lang['invalidaction'], true);
	$error->genericError();
}
ob_end_flush();
?>
