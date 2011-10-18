<?php
if (!defined('IN_EBB') ) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
 *attachmentMgr.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 6/5/2011
*/

class attachmentMgr{
	
	/**
				 *  list all attachments and alows user to manage their files.
				 * @access Public
				 * @param string $mode the type of attachment we're managing.
				 * @version 6/5/2011
			*/
	public function attachmentManager($mode){

		global $style, $title, $lang, $boardDir, $attach_q, $uploads, $db;

		#see if mode was left empty.
		if(!isset($mode)){
			$error = new notifySys($lang['noaction'], true);
			$error->genericError();
		}

		#see how to manage the attachment.
		switch ($mode){
		case 'newentry':
			#call attachment manager header file.
			$tpl = new templateEngine($style, "upload_head");

			#setup tag code.
			$tpl->parseTags(array(
			"TITLE" => "$title",
			"LANG-TITLE" => "$lang[manageattach]",
			"LANG-FILENAME" => "$lang[filename]",
			"LANG-FILESIZE" => "$lang[filesize]"));
			echo $tpl->outputHtml();

			#loop data.
			while ($newFile = mysql_fetch_assoc ($uploads)) {
		 		#get filesize in Kb.
		 		$fileSize = $this->getFileSize($newFile['File_Size']). 'Kb';

				#call attachment manager file.
				$tpl = new templateEngine($style, "upload-manager");

				#setup tag code.
				$tpl->parseTags(array(
				"ATTACHID" => "$newFile[id]",
				"LANG-DELETE" => "$lang[delattach]",
				"FILENAME" => "$newFile[Filename]",
				"FILESIZE" => "$fileSize"));

				echo $tpl->outputHtml();
			}
			
			#call attachment manager footer file.
			$tpl = new templateEngine($style, "upload_foot");
			echo $tpl->outputHtml();
		break;
		case 'profile':
			#call attachment manager header file.
			$tpl = new templateEngine($style, "attachmanager_head");

			#setup tag code.
			$tpl->parseTags(array(
			"TITLE" => "$title",
			"LANG-TITLE" => "$lang[profile]",
			"LANG-MANAGEATTACHMENTS" => "$lang[manageattach]",
			"LANG-TEXT" => "$lang[attachmenttext]",
			"LANG-FILENAME" => "$lang[filename]",
			"LANG-FILESIZE" => "$lang[filesize]",
			"LANG-POSTEDIN" => "$lang[postedin]"));
			echo $tpl->outputHtml();

			while ($attachR = mysql_fetch_assoc ($attach_q)) {
		 		#get filesize in Kb.
     			$fileSize = $this->getFileSize($attachR['File_Size']). 'Kb';

				#get topic details.
				if($attachR['pid'] == 0){
		 			$db->SQL = "SELECT Topic, bid FROM ebb_topics WHERE tid='$attachR[tid]'";
					$attachDetails = $db->fetchResults();

					#make query link.
					$link = "bid=$attachDetails[bid]&amp;tid=$attachR[tid]";
				}

				#get post details.
				if($attachR['tid'] == 0){
					$db->SQL = "SELECT tid FROM ebb_posts WHERE pid='$attachR[pid]'";
					$postTID = $db->fetchResults();

					#get topic name.
					$db->SQL = "SELECT Topic, bid FROM ebb_topics WHERE tid='$postTID[tid]'";
					$attachDetails = $db->fetchResults();

					#make query link.
					$link = "bid=$attachDetails[bid]&amp;tid=$postTID[tid]&pid=$attachR[pid]#post$attachR[pid]";
				}


				#call attachment manager file.
				$tpl = new templateEngine($style, "editattachments");

				#setup tag code.
				$tpl->parseTags(array(
				"BOARDDIR" => "$boardDir",
				"LANG-DELPROMPT" => "$lang[condel]",
				"ATTACHID" => "$attachR[id]",
				"LANG-DELETE" => "$lang[delattach]",
				"FILENAME" => "$attachR[Filename]",
				"FILESIZE" => "$fileSize",
				"LANG-POSTEDIN" => "$lang[postedin]",
				"POSTLINK" => "$link",
				"TOPICNAME" => "$attachDetails[Topic]"));

				echo $tpl->outputHtml();
			}

			#call attachment manager footer file.
			$tpl = new templateEngine($style, "attachmanager_foot");
			echo $tpl->outputHtml();
		break;
		default:
			$error = new notifySys($lang['invalidaction'], true);
			$error->genericError();
		}
	}

  /**
	*displayUploadMsgs
	*
	*Displays a list of errors occured during uploading a file.
	*
	*@modified 2/8/11
	*
	*@param string $message - message to output.
	*
	*@access public
   * @deprecated This will be replaced with  notifySys->displayAjaxError() as of v3.0.0 RC2
	*/
	public function displayUploadMsgs($message, $type){
		global $style;

		$tpl = new templateEngine($style, "upload-errors");
		$tpl->parseTags(array(
		"MESSAGE" => "$message"));

		#see if the titlebar show display.
		if ($type == "success"){
			$tpl->removeBlock("failed");
		}else{
			$tpl->removeBlock("success");
		}

		#output result
		echo $tpl->outputHtml();
	}

	  /**uploadCount
				*
				*Gets a count of current pending uploads.
				*@param string $type The type of upload to count.
				*@param int $id the id to check, default is 0.
				*@modified 2/18/11
				*@access public
				* 
			*/
	public function uploadCount($type, $id=0){
		global $db, $logged_user;

		//see what type of count we're doing.
		if($type == "Topic"){
			//get count of pending files.
			$db->SQL = "SELECT id FROM ebb_attachments WHERE Username='$logged_user' AND tid='$id' AND pid='0'";
			$attachCount = $db->affectedRows();
		} elseif($type == "Post"){
			//get count of pending files.
			$db->SQL = "SELECT id FROM ebb_attachments WHERE Username='$logged_user' AND tid='0' AND pid='$id'";
			$attachCount = $db->affectedRows();
		}else{
			//get count of pending files.
			$db->SQL = "SELECT id FROM ebb_attachments WHERE Username='$logged_user' AND tid='0' AND pid='0'";
			$attachCount = $db->affectedRows();
		}

		return ($attachCount);
	}
	
	/**
	*attachmentBBCode
	*
	*Displays a list of attachments for either a topic or topic reply.
	*
	*@modified 2/21/11
	*
	*@param string $type - topic or reply to search for.
	*@param int $id - topic or post ID.
	*@access public
	*/
	public function attachmentBBCode($type, $id){

		global $db, $lang, $boardPref, $groupPolicy, $logged_user;

		if($type == "topic"){
			#see if user attached a file.
			$db->SQL = "SELECT id, Filename, File_Size, Download_Count FROM ebb_attachments WHERE tid='$id'";
			$attachCount = $db->affectedRows();
			$attach_q = $db->query();

			if($attachCount > 0){
		 		$attachment = '<br /><div class="attachheader">'.$lang['attachments'].'</div><div class="attachment">';
				while ($attachments = mysql_fetch_assoc ($attach_q)) {
		 			#get filesize in Kb.
                    $fileSize = $this->getFileSize($attachments['File_Size']). 'Kb';

			  		#see if guests can download anything.
					$guestDownloads = $boardPref->getPreferenceValue("allow_guest_downloads");

					#see if guests can download content.
					if($groupPolicy->validateAccess(1, 29) == false){
						$attachment .= $attachments['Filename'].'&nbsp;('.$fileSize.')&nbsp;'.$lang['downloadct'].':&nbsp;'.$attachments['Download_Count'].'<br />';
					}elseif(($guestDownloads == 0) and ($logged_user == "guest")){
		    			$attachment .= $attachments['Filename'].'&nbsp;('.$fileSize.')&nbsp;'.$lang['downloadct'].':&nbsp;'.$attachments['Download_Count'].'<br />';
					}else{
						$attachment .= '<a href="download.php?id='.$attachments['id'].'">'.$attachments['Filename'].'</a>&nbsp;('.$fileSize.')&nbsp;'.$lang['downloadct'].':&nbsp;'.$attachments['Download_Count'].'<br />';
					}
				}
				$attachment .= '</div>';
			}else{
				$attachment = '';
			}
		}elseif($type == "post"){
			#see if user attached a file.
			$db->SQL = "SELECT id, Filename, File_Size, Download_Count FROM ebb_attachments WHERE pid='$id'";
			$attachCount = $db->affectedRows();
			$attach_q = $db->query();

			if($attachCount > 0){
		 		$attachment = '<br /><div class="attachheader">'.$lang['attachments'].'</div><div class="attachment">';
				while ($attachments = mysql_fetch_assoc ($attach_q)) {
		 			#get filesize in Kb.
      				$fileSize = $this->getFileSize($attachments['File_Size']). 'Kb';
      
			  		#see if guests can download anything.
					$guestDownloads = $boardPref->getPreferenceValue("allow_guest_downloads");

					#see if guests can download content.
					if($groupPolicy->validateAccess(1, 29) == false){
						$attachment .= $attachments['Filename'].'&nbsp;('.$fileSize.')&nbsp;'.$lang['downloadct'].':&nbsp;'.$attachments['Download_Count'].'<br />';
					}elseif(($guestDownloads == 0) and ($logged_user == "guest")){
		    			$attachment .= $attachments['Filename'].'&nbsp;('.$fileSize.')&nbsp;'.$lang['downloadct'].':&nbsp;'.$attachments['Download_Count'].'<br />';
					}else{
						$attachment .= '<a href="download.php?id='.$attachments['id'].'">'.$attachments['Filename'].'</a>&nbsp;('.$fileSize.')&nbsp;'.$lang['downloadct'].':&nbsp;'.$attachments['Download_Count'].'<br />';
					}
				}
				$attachment .= '</div>';
			}else{
				$attachment = '';
			}
		}else{
		 	#function not called correctly.
            $error = new notifySys($lang['invalidaction'], true);
			$error->genericError();
		}
		return ($attachment);
	}

	/**
	*isAllowed
	*
	*Ensures no banned file types are uploaded.
	*
	*@modified 3/9/10
	*
	*@param string $filetype - file extension to look for in database.
	*
	*@return boolean (true|false) - results of look-up.
	*
	*@access public
	*/
	public function isAllowed($filetype){

		global $db, $lang;

		if(empty($filetype)){
			$error = new notifySys($lang['extlookup'], true);
			$error->genericError();
		}

		$db->SQL = "SELECT id FROM ebb_attachment_extlist WHERE ext='$filetype'";
		$compare = $db->affectedRows();
	
		#see if the filetype matches the one in the db.
		if($compare == 1){
		  	return true;
		}else{
			return false;
		}
	}
	
	/**
	*getFileSize
	*
	*Converts file size of bytes to Kb.
	*
	*@modified 1/7/10
	*
	*@param string $file - file size to convert.
	*
	*@return int - converted value of file size.
	*
	*@access public
	*/
	private function getFileSize($file){
		$fileSize = ceil($file / 1024);
		
		return ($fileSize);
	}

}
?>
