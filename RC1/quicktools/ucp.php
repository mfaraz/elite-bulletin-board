<?php
define('IN_EBB', true);
/**
 * ucp.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 06/28/2012
*/

#make sure the call is from an AJAX request.
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
	die("<strong>THIS PAGE CANNOT BE ACCESSED DIRECTLY!</strong>");
}

require_once "../config.php";
require_once FULLPATH."/header.php";
include_once FULLPATH."/includes/attachmentMgr.php";
require_once FULLPATH."/includes/validation.class.php";

if(isset($_GET['mode'])){
	$mode = var_cleanup($_GET['mode']);
}else{
	$mode = '';
}

switch($mode) {
	case 'editprofile':
		#setup some variables needed for this page.
		$ctitle = $userData->userSettings("Custom_Title");
		$mim = $userData->userSettings("MSN");
		$aim = $userData->userSettings("AOL");
		$icq = $userData->userSettings("ICQ");
		$yim = $userData->userSettings("Yahoo");
		$www = $userData->userSettings("WWW");
		$loc = $userData->userSettings("Location");

		//output
		$timezone = timezone_select($userData->userSettings('Time_Zone'));
		$selStyle = style_select($userData->userSettings('Style'));
		$language = lang_select($userData->userSettings('Language'));

		$tpl = new templateEngine($style, "editprofile");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[profile]",
		"LANG-EDITPROFILE" => "$lang[editprofile]",
		"LANG-TEXT" => "$lang[editprofiletxt]",
		"LANG-YES" => "$lang[yes]",
		"LANG-NO" => "$lang[no]",
		"LANG-ENTERPASS" => "$lang[enterpass]",
		"LANG-CURRPASS" => "$lang[currentpass]",
		"LANG-PMNOTIFY" => "$lang[pm_notify]",
		"LANG-SHOWEMAIL" => "$lang[showemail]",
		"LANG-CUSTOMTITLE" => "$lang[customtitle]",
		"CUSTOMTITLE" => "$ctitle",
		"LANG-MSN" => "$lang[msn]",
		"MSN" => "$mim",
		"LANG-AOL" => "$lang[aol]",
		"AOL" => "$aim",
		"LANG-ICQ" => "$lang[icq]",
		"ICQ" => "$icq",
		"LANG-YAHOO" => "$lang[yim]",
		"YAHOO" => "$yim",
		"LANG-WWW" => "$lang[www]",
		"WWW" => "$www",
		"LANG-TIME" => "$lang[timezone]",
		"TIME" => "$timezone",
		"LANG-TIMEFORMAT" => "$lang[timeformat]",
		"LANG-TIMEINFO" => "$lang[timeinfo]",
		"TIMEFORMAT" => "$timeFormat",
		"LANG-STYLE" => "$lang[style]",
		"STYLE" => "$selStyle",
		"LANG-LANGUAGE" => "$lang[defaultlang]",
		"LANGUAGE" => "$language",
		"LANG-LOCATION" => "$lang[location]",
		"LOCATION" => "$loc",
		"SUBMIT" => "$lang[saveprofile]"));

		#do some decision making.
		if($groupPolicy->validateAccess(1, 30) == false){
			$tpl->removeBlock("customtitle");
		}else{
			$tpl->removeBlock("dcustomtitle");
		}

		#pm notify detect.
		if ($userData->userSettings("PM_Notify") == 1){
			$tpl->removeBlock("npmnotice");
		}else{
   			$tpl->removeBlock("ypmnotice");
		}

		#hide email detect.
		if($userData->userSettings("Hide_Email") == 0){
   			$tpl->removeBlock("nhideemail");
		}else{
			$tpl->removeBlock("yhideemail");
		}

		echo $tpl->outputHtml();
	break;
	case 'saveProfile':
		$conpass = $db->filterMySQL(var_cleanup($_POST['conpass']));
		$pm_notice = $db->filterMySQL(var_cleanup($_POST['pm_notice']));
		$show_email = $db->filterMySQL(var_cleanup($_POST['show_email']));
		$ctitle = $db->filterMySQL(var_cleanup($_POST['ctitle']));
		$msn = $db->filterMySQL(var_cleanup($_POST['msn']));
		$aol = $db->filterMySQL(var_cleanup($_POST['aol']));
		$yim = $db->filterMySQL(var_cleanup($_POST['yim']));
		$icq = $db->filterMySQL(var_cleanup($_POST['icq']));
		$www = $db->filterMySQL(var_cleanup($_POST['www']));
		$location = $db->filterMySQL(var_cleanup($_POST['location']));
		$time_zone = $db->filterMySQL(var_cleanup($_POST['time_zone']));
		$time_format = $db->filterMySQL(var_cleanup($_POST['time_format']));
		$ustyle = $db->filterMySQL(var_cleanup($_POST['style']));
		$usrlang = $db->filterMySQL(var_cleanup($_POST['default_lang']));

		#set the columes needed for now.
        $curPwd = $userData->userSettings("Password");
        $pwdSalt = $userData->getPasswordSalt();

		#validate form values.
		if($groupPolicy->validateAccess(1, 30) == false){
			#display validation message.
			$displayMsg = new notifySys($lang['nocustomtitle'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($ctitle) > 20){
			#display validation message.
			$displayMsg = new notifySys($lang['longctitle'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ((!preg_match('/http:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/', $www)) and (!empty($www))) {
			#display validation message.
			$displayMsg = new notifySys($lang['invalidurl'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($msn) > 255){
			#display validation message.
			$displayMsg = new notifySys($lang['longmsn'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($aol) > 255){
			#display validation message.
			$displayMsg = new notifySys($lang['longaol'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($yim) > 255){
			#display validation message.
			$displayMsg = new notifySys($lang['longyim'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($icq) > 15){
			#display validation message.
			$displayMsg = new notifySys($lang['longicq'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($www) > 200){
			#display validation message.
			$displayMsg = new notifySys($lang['longwww'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($location) > 70){
			#display validation message.
			$displayMsg = new notifySys($lang['longloc'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if (empty($ustyle)){
			#display validation message.
			$displayMsg = new notifySys($lang['nostyle'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if (empty($usrlang)){
			#display validation message.
			$displayMsg = new notifySys($lang['nolang'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($time_zone == ""){
			#display validation message.
			$displayMsg = new notifySys($lang['notimezone'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if (empty($time_format)){
			#display validation message.
			$displayMsg = new notifySys($lang['notimeformat'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($pm_notice == ""){
			#display validation message.
			$displayMsg = new notifySys($lang['nopmnotify'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($show_email == ""){
			#display validation message.
			$displayMsg = new notifySys($lang['noshowemail'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if (empty($conpass)){
			#display validation message.
			$displayMsg = new notifySys($lang['novertpass'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($time_format) > 14){
			#display validation message.
			$displayMsg = new notifySys($lang['longtimeformat'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		$pass = sha1($conpass.$pwdSalt);
		//see if password matches.
		if ($userData->userSettings("Password") !== $pass){
			#display validation message.
			$displayMsg = new notifySys($lang['curpassnomatch'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}else{
			//process query
			$db->SQL = "UPDATE ebb_users SET PM_Notify='$pm_notice', Hide_Email='$show_email', Custom_Title='$ctitle', MSN='$msn', AOL='$aol', Yahoo='$yim', ICQ='$icq', WWW='$www', Location='$location', Time_Zone='$time_zone', Time_format='$time_format', Style='$ustyle', Language='$usrlang' WHERE Username='$logged_user'";
			$db->query();

			#display success message.
			$displayMsg = new notifySys($lang['usrprofilesuccess'], false);
			$displayMsg->displayAjaxError("success");
		}
	break;
	case 'edit_sig':
		if($groupPolicy->validateAccess(1, 33) == false){
	        $displayMsg = new notifySys($lang['accessdenied'], true);
			$displayMsg->displayError();
		}

		#settings.
		$allowsmile = 1;
		$allowbbcode = 1;
		$allowimg = 1;

		#format signature.
		if ($userData->userSettings("Sig") == "") {
			$displaysig = '';
		} else {
			$displaysig = nl2br(smiles(BBCode(language_filter($userData->userSettings("Sig"), 1), true)));
		}

		#call bbcode functions.
		$bName['Smiles'] = 1;
		$smile = form_smiles();

        #template output.
        $tpl = new templateEngine($style, "editsig");
		$tpl->parseTags(array(
		"LANG-DISABLERTF" => "$lang[disablertf]",
		"LANG-TEXT" => "$lang[sigtxt]",
		"LANG-SMILES" => "$lang[moresmiles]",
		"SMILES" => "$smile",
		"LANG-CURRENTSIG" => "$lang[cursig]",
		"CURRENTSIG" => "$displaysig",
		"SIGNATURE" => "$sig",
		"LANG-SAVESIG" => "$lang[savesignature]"));

		echo $tpl->outputHtml();
	break;
	case 'saveSignature':
		if($groupPolicy->validateAccess(1, 33) == false){
			#display validation message.
			$displayMsg = new notifySys($lang['accessdenied'], false);
			exit($displayMsg->displayAjaxError("error"));
		}
		$signature = $db->filterMySQL(var_cleanup($_POST['signature']));

		//error check
		if(strlen($signature) > 255){
			#display validation message.
			$displayMsg = new notifySys($lang['longsig'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		//process query
		$db->SQL = "UPDATE ebb_users SET Sig='$signature' WHERE Username='$logged_user'";
		$db->query();

		#display success message.
		$displayMsg = new notifySys($lang['sigsavedsuccessfully'], false);
		$displayMsg->displayAjaxError("success");
	break;
	case 'groupmanager':
		if($groupPolicy->validateAccess(1, 34) == false){
	        $displayMsg = new notifySys($lang['accessdenied'], true);
			$displayMsg->displayError();
		}

		//output html.
        $tpl = new templateEngine($style, "editgrouplist_head");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[profile]",
		"LANG-GROUPMANAGE" => "$lang[managegroups]",
		"LANG-TEXT" => "$lang[grouptxt]",
		"LANG-GROUPNAME" => "$lang[groupname]"));

		echo $tpl->outputHtml();

		$db->SQL = "SELECT id, Name, Description, Enrollment FROM ebb_groups WHERE Level!=0";
		$joinedQ = $db->query();

		while ($group = mysql_fetch_assoc ($joinedQ)) {
			//see if user already joined this.
			$db->SQL = "SELECT gid FROM ebb_group_users where gid='$group[id]' and Username='$logged_user'";
			$enrollmentStatus = $db->affectedRows();

			#see if a user joined a group and see if their pending still.
			$db->SQL = "SELECT Status, gid FROM ebb_group_member_request where gid='$group[id]' and username='$logged_user'";
            $pendingStatus = $db->affectedRows();
			
			if($enrollmentStatus == 1){
				if ($pendingStatus == 1){
					$groupStatus = $lang['pending'];
				}else{
					$groupStatus = '<a href="#" id="UnjoinGroup" title="'.$group['id'].'">'.$lang['unjoingroup'].'</a>';
				}
			}else{
			 	#See if a group is opened or locked or hidden.
				if ($group['Enrollment'] == 1){
					$groupStatus = '<a href="#" id="JoinGroup" title="'.$group['id'].'">'.$lang['joingroup'].'</a>';
				}elseif($group['Enrollment'] == 0){
					$groupStatus = "";
				}else{
					$groupStatus = "";
				}
			}
			#grouplist CP data.
        	$tpl = new templateEngine($style, "editgrouplist");
			$tpl->parseTags(array(
			"GROUPSTATUS" => "$groupStatus",
			"GROUPNAME" => "$group[Name]",
			"GROUPDESC" => "$group[Description]",
			"LANG-VIEWGROUPROSTER" => "$lang[viewgroup]",
			"GROUPID" => "$group[id]"));

			echo $tpl->outputHtml();
		}
		#grouplist CP footer.
	   	$tpl = new templateEngine($style, "editgrouplist_foot");
		echo $tpl->outputHtml();
	break;
	case 'joinGroup':
		if($groupPolicy->validateAccess(1, 34) == false){
			#display validation message.
			$displayMsg = new notifySys($lang['accessdenied'], false);
			exit($displayMsg->displayAjaxError("error"));
		}
		$id = $db->filterMySQL(var_cleanup($_GET['id']));
		$db->SQL = "select Enrollment from ebb_groups where id='$id'";
		$statusCheck = $db->fetchResults();
		$numChk = $db->affectedRows();

		//see if user is already a member of this group.
		$db->SQL = "select gid from ebb_group_users where Username='$logged_user' AND gid='$id'";
		$membershipChk = $db->affectedRows();

		if ($membershipChk == 1){
			#display validation message.
			$displayMsg = new notifySys($lang['alreadyjoined'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		//see if the group exist.
		if ($numChk == 1){
			if ($statusCheck['Enrollment'] == 0){
				#display validation message.
				$displayMsg = new notifySys($lang['locked'], false);
				exit($displayMsg->displayAjaxError("warning"));
			}else{
				$db->SQL = "INSERT INTO ebb_group_member_request (username, gid) VALUES('$logged_user', '$id')";
				$db->query();

				#display validation message.
				$displayMsg = new notifySys($lang['joinedgroupsuccess'], false);
				exit($displayMsg->displayAjaxError("success"));
			}
		}else{
			#display validation message.
			$displayMsg = new notifySys($lang['notexist'], false);
			exit($displayMsg->displayAjaxError("error"));
		}
	break;
	case 'unjoinGroup':
		if($groupPolicy->validateAccess(1, 34) == false){
	        #display validation message.
			$displayMsg = new notifySys($lang['accessdenied'], false);
			exit($displayMsg->displayAjaxError("error"));
		}

        #@TODO ucp-side of the new group system didn't get implemented completely. do better clean-up in RC2.
        #This field is not used at the moment and may not be needed. keep for now.
        $id = $db->filterMySQL(var_cleanup($_GET['id']));

		//change gid to regular member.
		$groupPolicy->changeGroupID(3);

		#display validation message.
		$displayMsg = new notifySys($lang['unjoinedgroupsuccess'], false);
		exit($displayMsg->displayAjaxError("success"));
	break;
	case 'avatar':
		#see if user can access this part of the page.
		if($groupPolicy->validateAccess(1, 32) == false){
	        $displayMsg = new notifySys($lang['accessdenied'], true);
			$displayMsg->displayError();
		}

		$allowed = $lang['allowed'].':&nbsp;<b>.gif .jpeg .jpg .png</b>';
		#see if user has a avatar.
        $uAvatar = $userData->userSettings("Avatar");
		if (empty($uAvatar)){
			$avatar = "images/noavatar.gif";
		}else{
			$avatar = $uAvatar;
		}
		#load template file.
       	$tpl = new templateEngine($style, "editavatar");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[profile]",
		"LANG-EDITAVATAR" => "$lang[avatarsetting]",
		"LANG-TEXT" => "$lang[avatartxt]",
		"LANG-CURRENTAVATAR" => "$lang[currentavatar]",
		"CURRENTAVATAR" => "$avatar",
		"LANG-CLEARAVATAR" => "$lang[clearavatar]",
		"ALLOWEDTYPES" => "$allowed",
		"LANG-SAVEAVATAR" => "$lang[saveavatar]",
		"BOARDDIR" => "$boardAddr",
		"LANG-GALLERY" => "$lang[avatargallery]"));

		echo $tpl->outputHtml();
	break;
	case 'gallery':
		#see if user can access this part of the page.
		if($groupPolicy->validateAccess(1, 32) == false){
	        $displayMsg = new notifySys($lang['accessdenied'], true);
			$displayMsg->displayError();
		}

		#setup gallery.
		#load template file.
       	$tpl = new templateEngine($style, "gallery-head");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[profile]",
		"LANG-GALLERY" => "$lang[avatargallery]"));

		echo $tpl->outputHtml();

        $x = 0; // we will use this to count to three later
		$gallery = '';
		$handle = opendir(FULLPATH."/images/avatar");
		while(($file = readdir($handle))){
			if (is_file(FULLPATH."/images/avatar/$file") and false !== strpos($file, '.gif') or false !== strpos($file, '.jpg') or false !== strpos($file, '.jpeg') or false !== strpos($file, '.png')){
				if (($x % 4) == 0) {
					#load template file.
			       	$tpl = new templateEngine($style, "gallery-end");
					echo $tpl->outputHtml();
					$x = 0; // $x is now 4 so we reset it here to start the next line
				}
				#load template file.
		       	$tpl = new templateEngine($style, "gallery-body");
				$tpl->parseTags(array(
				"BOARD-ADDR" => "$boardAddr",
				"FILE" => "$file"));
				echo $tpl->outputHtml();
				$x++; // increment $x by 1 so we get our 4
			}
		}
		#load template file.
       	$tpl = new templateEngine($style, "gallery-foot");
		$tpl->parseTags(array(
		"LANG-SAVEAVATAR" => "$lang[saveavatar]"));

		echo $tpl->outputHtml();
	break;
	case 'clearavatar':
		#see if user can access this part of the page.
		if($groupPolicy->validateAccess(1, 32) == false){
			#display validation message.
			$displayMsg = new notifySys($lang['accessdenied'], false);
			exit($displayMsg->displayAjaxError("error"));
		}

		#SQL Query.
        $db->SQL = "UPDATE ebb_users SET Avatar='' WHERE Username='$logged_user'";
		$db->query();

		#display validation message.
		$displayMsg = new notifySys($lang['clearavatarsuccess'], false);
		exit($displayMsg->displayAjaxError("success"));

	break;
	case 'saveAvatar':
		#see if user can access this part of the page.
		if($groupPolicy->validateAccess(1, 32) == false){
	        #display validation message.
			$displayMsg = new notifySys($lang['accessdenied'], false);
			exit($displayMsg->displayAjaxError("error"));
		}

		#get form value.
		$avatarImg = $db->filterMySQL(var_cleanup($_POST['avatar']));

		#extract information regarding this avatar and see if it meets the standards.
		// use curl if it exists
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $avatarImg);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec($ch);

			//ensure connection was successful.
			if($data !== false){
				//get image information.
				$resource = imagecreatefromstring($data);
				$width = imagesx($resource);
				$height = imagesy($resource);
				$mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
			}else {
				$displayMsg = new notifySys($lang['404'], false);
				exit($displayMsg->displayAjaxError("error"));
			}
		} else {
			//TODO: replace this with fsockopen
			$imgInfo = getimagesize($avatarImg);

			if ($imgInfo){
				$width = $imgInfo[0];
				$height = $imgInfo[1];
				$mime = $imgInfo['mime'];
			}else {
				$displayMsg = new notifySys($lang['404'], false);
				exit($displayMsg->displayAjaxError("error"));
			}
		}

		#compile a list of allowed mime types.
		$allowed = array("image/gif", "image/jpeg", "image/jpg", "image/png");

		//validate entry.
		if (!in_array($mime, $allowed)){
			#display validation message.
			$displayMsg = new notifySys($lang['wrongtype'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($avatarImg) > 255){
			#display validation message.
			$displayMsg = new notifySys($lang['longavatar'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(($width > 150) or ($height > 150)){
			#display validation message.
			$displayMsg = new notifySys($lang['lgavatar'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ((!preg_match('/http:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/', $avatarImg)) and (!empty($avatarImg))) {
			#display validation message.
			$displayMsg = new notifySys($lang['invalidurl'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(empty($avatarImg)){
			#display validation message.
			$displayMsg = new notifySys($lang['noavatarsel'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		//process query
		$db->SQL = "UPDATE ebb_users SET Avatar='$avatarImg' WHERE Username='$logged_user'";
		$db->query();

		#display validation message.
		$displayMsg = new notifySys($lang['savedavatarsuccess'], false);
		exit($displayMsg->displayAjaxError("success"));
	break;
	case 'attachments':

		$db->SQL = "SELECT id, Filename, File_Size, tid, pid FROM ebb_attachments WHERE Username='$logged_user'";
		$attach_q = $db->query();
		$num = $db->affectedRows();

		#load attachment manager.
		$attachMgr = new attachmentMgr();
		$attachMgr->attachmentManager("profile");
	break;
	case 'deleteAttachment':
		#get form values.
		if(isset($_GET['id'])){
			$id = var_cleanup($_GET['id']);
		}else{
			die($txt['noattachid']);
		}
		#get filename from db.
		$db->SQL = "select Filename from ebb_attachments where id='$id'";
		$attach_r = $db->fetchResults();

		#delete file from web space.
		$delattach = @unlink (FULLPATH.'/uploads/'. $attach_r['Filename']);
		if($delattach){
			#remove entry from db.
			$db->SQL = "delete from ebb_attachments where id='$id'";
			$db->query();

			#display validation message.
			$displayMsg = new notifySys($lang['removedattachmentsuccess'], false);
			exit($displayMsg->displayAjaxError("success"));
		}else{
			#display validation message.
			$displayMsg = new notifySys($lang['cantdelete'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
	break;
	case 'digest':
		#Figure out the total number of results in DB:
		$db->SQL = "SELECT tid FROM ebb_topic_watch WHERE username='$logged_user'";
		$sub_q = $db->query();
		$num = $db->affectedRows();

  		#subscription header.
       	$tpl = new templateEngine($style, "editsubscription_head");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[profile]",
		"LANG-EDITSUBSCRIPTION" => "$lang[subscriptionsetting]",
		"LANG-TEXT" => "$lang[digesttxt]",
		"LANG-SUBSCRIBED" => "$lang[scription]",
		"LANG-POSTEDIN" => "$lang[postedin]",
		"LANG-DELETE" => "$lang[delsubscription]",
		"LANG-NORESULT" => "$lang[nosubscription]"));

		#decision
		if($num > 0){
			$tpl->removeBlock("noresults");
		}

		echo $tpl->outputHtml();

		#subscription content.
		while ($sub = mysql_fetch_assoc ($sub_q)) {
			#get topic & board details
			$db->SQL = "SELECT t.Topic, b.id, b.Board FROM ebb_boards b LEFT JOIN ebb_topics t ON b.id=t.bid WHERE t.tid='".$sub['tid']."' ";
			$boardRes = $db->fetchResults();

	       	$tpl = new templateEngine($style, "editsubscription");
			$tpl->parseTags(array(
            "BOARDDIR" => "$boardAddr",
			"TOPICID" => "$sub[tid]",
			"LANG-DELETE" => "$lang[del]",
			"LANG-POSTEDIN" => "$lang[postedin]",
			"TOPICNAME" => "$boardRes[Topic]",
			"BOARDID" => "$boardRes[id]",
			"BOARDNAME" => "$boardRes[Board]"));

			echo $tpl->outputHtml();
		}

		#subscription footer.
       	$tpl = new templateEngine($style, "editsubscription_foot");

		echo $tpl->outputHtml();
	break;
	case 'deleteSubscription':
		if((!isset($_GET['del'])) or (empty($_GET['del']))){
	        #display validation message.
			$displayMsg = new notifySys($lang['invalidaction'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}else{
			$del = $db->filterMySQL(var_cleanup($_GET['del']));
		}
		//process query
		$db->SQL = "DELETE FROM ebb_topic_watch where username='$logged_user' and tid='$del'";
		$db->query();

		#display validation message.
		$displayMsg = new notifySys($lang['removedsubscriptionsuccess'], false);
		exit($displayMsg->displayAjaxError("success"));
	break;
	case 'new_email':
		#load template file.
       	$tpl = new templateEngine($style, "editemail");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[profile]",
		"LANG-EDITEMAIL" => "$lang[emailupdate]",
		"LANG-TEXT" => "$lang[emailtxt]",
		"LANG-CURREMAIL" => "$lang[currentemail]",
		"LANG-NEWEMAIL" => "$lang[newemail]",
		"LANG-CONFIRMEMAIL" => "$lang[confirmemail]",
		"LANG-UPDATEEMAIL" => "$lang[updateemail]"));

		echo $tpl->outputHtml();
	break;
	case 'updateEmail':
		#form values.
		$curemail = $db->filterMySQL(var_cleanup($_POST['curemail']));
		$conemail = $db->filterMySQL(var_cleanup($_POST['conemail']));
		$newemail = $db->filterMySQL(var_cleanup($_POST['newemail']));

        #call validation class.
		$validate = new validation();

		#error check.
		if ($validate->validateEmail($curemail) == false){
			#display validation message.
			$displayMsg = new notifySys($lang['invalidemail'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($validate->validateEmail($conemail) == false){
			#display validation message.
			$displayMsg = new notifySys($lang['invalidemail'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($validate->validateEmail($newemail) == false){
			#display validation message.
			$displayMsg = new notifySys($lang['invalidemail'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($newemail !== $conemail){
			#display validation message.
			$displayMsg = new notifySys($lang['nocemailmatch'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if((strlen($newemail) > 255) or (strlen($conemail) > 255) or (strlen($curemail) > 255)){
			#display validation message.
			$displayMsg = new notifySys($lang['longemail'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($curemail !== $userData->userSettings("Email")){
			#display validation message.
			$displayMsg = new notifySys($lang['noemailmatch'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		//process query
		$db->SQL = "UPDATE ebb_users SET Email='$newemail' WHERE Username='$logged_user'";
		$db->query();

		#display validation message.
		$displayMsg = new notifySys($lang['updatedemailsuccess'], false);
		exit($displayMsg->displayAjaxError("success"));
	break;
	case 'new_password':
		#load template file.
       	$tpl = new templateEngine($style, "editpassword");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[profile]",
		"LANG-EDITPASS" => "$lang[changepassword]",
		"LANG-TEXT" => "$lang[passtxt]",
		"LANG-CURRPASS" => "$lang[currentpass]",
		"LANG-NEWPASS" => "$lang[newpass]",
		"LANG-CONFIRMPASS" => "$lang[connewpass]",
		"LANG-UPDATEPASS" => "$lang[updatepass]"));

		echo $tpl->outputHtml();
	break;
	case 'updatePassword':
		#form values.
		$curpass = $db->filterMySQL(var_cleanup($_POST['curpass']));
		$newpass = $db->filterMySQL(var_cleanup($_POST['newpass']));
		$confirmpass = $db->filterMySQL(var_cleanup($_POST['confirmpass']));

		#password encryption
		$pwdSalt = $userData->getPasswordSalt();
		$curpassChk = sha1($curpass.$pwdSalt);

		#error check.
		if(empty($curpass)){
			#display validation message.
			$displayMsg = new notifySys($lang['nocpwd'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(empty($newpass)){
			#display validation message.
			$displayMsg = new notifySys($lang['nonpwd'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(empty($confirmpass)){
			#display validation message.
			$displayMsg = new notifySys($lang['novpwd'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($newpass !== $confirmpass){
			#display validation message.
			$displayMsg = new notifySys($lang['nopassmatch'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($curpassChk !== $userData->userSettings("Password")){
			#display validation message.
			$displayMsg = new notifySys($lang['curpassnomatch'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		//process query
		$pwdChg = sha1($newpass.$pwdSalt);
		$db->SQL = "UPDATE ebb_users SET Password='$pwdChg' WHERE Username='$logged_user'";
		$db->query();

		#display validation message.
		$displayMsg = new notifySys($lang['updatedpasswdsuccess'], false);
		exit($displayMsg->displayAjaxError("success"));
	break;
	default:
		$user = (empty($_GET['user'])) ? $logged_user : $db->filterMySQL(var_cleanup($_GET['user']));
		#see if user is viewing a profile.
		if((empty($user)) and ($logged_user !== "guest")){
			$user = $logged_user;
		}else{
			$db->SQL = "SELECT id FROM ebb_users WHERE Username='$user'";
			$userChk = $db->affectedRows();

			//check to see if this user exist.
			if ($userChk == 0){
            	$error = new notifySys($lang['usernotexist'], true);
				$error->displayError();
			}
		}
		#setup and obtain user settings and group status.
		$userGroupPolicy = new groupPolicy($user);
		$userInfo = new user($user);

		#setup some user-based
		$uPostCount = $userInfo->userSettings("Post_Count");
		$uEmail = $userInfo->userSettings("Email");
		$uMSN = $userInfo->userSettings("MSN");
		$uAIM = $userInfo->userSettings("AOL");
		$uICQ = $userInfo->userSettings("ICQ");
		$uYIM = $userInfo->userSettings("Yahoo");
		$uWWW = $userInfo->userSettings("WWW");
		$uLoc = $userInfo->userSettings("Location");
		$uAvatar = $userInfo->userSettings("Avatar");

		//see if the user set an avatar
		if (empty($uAvatar)){
			$avatar = "images/noavatar.gif";
		}else{
			$avatar = $uAvatar;
		}
		//get status
		if($userGroupPolicy->groupAccessLevel() == 1){
			$uGroupName = '<i><b>'.$userGroupPolicy->getGroupName().'</b></i>';
		}elseif($userGroupPolicy->groupAccessLevel() == 2){
			$uGroupName = '<b>'.$userGroupPolicy->getGroupName().'</b>';
		}elseif($userGroupPolicy->groupAccessLevel() == 3){
			$uGroupName = '<i>'.$userGroupPolicy->getGroupName().'</i>';
		}

		$joinDate = formatTime($timeFormat, $userInfo->userSettings("Date_Joined"), $gmt);
		#BEGIN Portal Data Gathering.

		#latest Topics.
		$LastTopics = $userInfo->latestTopics();
		$LastPosts = $userInfo->latestPosts();

		#END Portal Data Gathering.

		#grab PM count if their the visiting user.
		if($logged_user == $userInfo->userSettings("Username")){

			#total of new PM messages.
			$db->SQL = "SELECT Read_Status FROM ebb_pm WHERE Reciever='".$userInfo->userSettings("Username")."' AND Read_Status=''";
			$newPmCount = $db->affectedRows();
		}

		$tpl = new templateEngine($style, "ajax-ucp");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[profile]",
		"USERNAME" => "$user",
		"LANG-RANK" => "$lang[rank]",
		"RANK" => "$uGroupName",
		"LANG-POSTCOUNT" => "$lang[postcount]",
		"POSTCOUNT" => "$uPostCount",
		"LANG-EMAIL" => "$lang[email]",
		"EMAIL" => "$uEmail",
		"LANG-MSN" => "$lang[msn]",
		"MSN" => "$uMSN",
		"LANG-AOL" => "$lang[aol]",
		"AOL" => "$uAIM",
		"LANG-ICQ" => "$lang[icq]",
		"ICQ" => "$uICQ",
		"LANG-YAHOO" => "$lang[yim]",
		"YAHOO" => "$uYIM",
		"LANG-WWW" => "$lang[www]",
		"WWW" => "$uWWW",
		"LANG-LOCATION" => "$lang[location]",
		"LOCATION" => "$uLoc",
		"LANG-JOINED" => "$lang[joindate]",
		"JOINED" => "$joinDate",
		"LANG-LATEST-TOPICS" => "$lang[latesttopics]" ,
		"LATEST-TOPICS" => "$LastTopics",
		"LANG-LATEST-POSTS" => "$lang[latestreplies]",
		"LATEST-POSTS" => "$LastPosts",
		"LANG-FINDTOPICS" => "$lang[findtopics]",
		"LANG-FINDPOSTS" => "$lang[findposts]",
		"USERNAME" => "$user",
		"AVATAR" => "$avatar"));

		#see if this is the current user or not.
		if ($logged_user == $user) {
			$tpl->removeBlock("UsrMenu");
		}

		echo $tpl->outputHtml();
	break;
}

?>
