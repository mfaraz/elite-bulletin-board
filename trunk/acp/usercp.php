<?php
define('IN_EBB', true);
/**
Filename: usercp.php
Last Modified: 2/22/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "../config.php";
require_once FULLPATH."/header.php";
require_once FULLPATH."/acp/acpheader.php";
require_once FULLPATH."/includes/admin_function.php";
require_once FULLPATH."/includes/phpmailer/class.phpmailer.php";

if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = ''; 
}
#get title.
switch($action){
case 'user_manage':
case 'user_process':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 14) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$usercptitle = $lang['usermenu'].' - '.$lang['manage'];
	$helpTitle = $help['usermanagetitle'];
	$helpBody = $help['usermanagebody'];
break;
case 'warnlog':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 18) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$usercptitle = $lang['usermenu'].' - '.$lang['warninglist'];
	$helpTitle = $help['warnlogtitle'];
	$helpBody = $help['warnlogbody'];
break;
case 'revoke':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 18) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$usercptitle = $lang['usermenu'].' - '.$lang['revokeaction'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'clearwarnlog':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 18) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$usercptitle = $lang['usermenu'].' - '.$lang['deletewarnlog'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'activate':
case 'activate_user':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 19) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$usercptitle = $lang['usermenu'].' - '.$lang['activateacct'];
	$helpTitle = $help['actusertitle'];
	$helpBody = $help['actuserbody'];
break;
case 'banlist':
case 'ban_add':
case 'ban_remove':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 13) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$usercptitle = $lang['usermenu'].' - '.$lang['banlist'];
	$helpTitle = $help['bantitle'];
	$helpBody = $help['banbody'];
break;
case 'blacklistuser':
case 'blacklist_add':
case 'blacklist_remove':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 16) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$usercptitle = $lang['usermenu'].' - '.$lang['blacklist'];
	$helpTitle = $help['blacklisttitle'];
	$helpBody = $help['blacklistbody'];
break;
case 'user_prune':
case 'process_user_pruning':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 15) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$usercptitle = $lang['usermenu'].' - '.$lang['userprune'];
	$helpTitle = $help['userprunetitle'];
	$helpBody = $help['userprunebody'];
break;
default:
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 14) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$usercptitle = $lang['usermenu'].' - '.$lang['manage'];
	$helpTitle = $help['usermanagetitle'];
	$helpBody = $help['usermanagebody'];
}
$tpl = new templateEngine($style, "acp_header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$usercptitle",
    "LANG-HELP-TITLE" => "$helpTitle",
    "LANG-HELP-BODY" => "$helpBody",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

//output top
$pmMsg = $userData->getNewPMCount();

$tpl = new templateEngine($style, "top-acp");
$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-WELCOME" => "$lang[welcome]",
	"LOGGEDUSER" => "$logged_user",
	"LANG-LOGOUT" => "$lang[logout]",
	"NEWPM" => "$pmMsg",
	"LANG-CP" => "$lang[admincp]",
	"LANG-NEWPOSTS" => "$lang[newposts]",
	"ADDRESS" => "$address",
	"LANG-HOME" => "$lang[home]",
	"LANG-SEARCH" => "$lang[search]",
	"LANG-CLOSE" => "$lang[close]",
	"LANG-QUICKSEARCH" => "$lang[quicksearch]",
	"LANG-ADVANCEDSEARCH" => "$lang[advsearch]",
	"LANG-HELP" => "$lang[help]",
	"LANG-MEMBERLIST" => "$lang[members]",
	"LANG-PROFILE" => "$lang[profile]"));

#update user's activity.
echo update_whosonline_reg($logged_user);

#output top template file.
echo $tpl->outputHtml();

//display admin CP
switch ( $action ){
case 'user_manage':
	#see if username was given.
	if((!isset($_POST['username'])) or (empty($_POST['username']))){
		$error = new notifySys($lang['nouser'], true);
		$error->displayError();
	}else{
		$user = $db->filterMySQL($_POST['username']);
	}	
	$db->SQL = "SELECT id FROM ebb_users WHERE Username='$user' LIMIT 1";
	$user_chk = $db->affectedRows();
	
	#get group ID.
    $db->SQL = "SELECT gid FROM ebb_group_users WHERE Username='".$user."' LIMIT 1";
	$uGID = $db->fetchResults();

	#see if the requested user exists.
	if($user_chk == 0){
		$error = new notifySys($lang['usernotexist'], true);
		$error->displayError();
	}else{
		#see if any errors were reported by auth.php.
		if(isset($_SESSION['errors'])){
		    #format error(s) for the user.
			$errors = var_cleanup($_SESSION['errors']);

			#display validation message.
   		    $displayMsg = new notifySys($errors, false);
			$displayMsg->displayValidate();

			#destroy errors session data, its no longer needed.
        	unset($_SESSION['errors']);
		}

		#call user class.
        $userMgrData = new user($user);

		#load up select boxes.
		$timezone = timezone_select($userMgrData->userSettings("Time_Zone"));
		$uStyle = style_select($userMgrData->userSettings("Style"));
		$language = lang_select($userMgrData->userSettings("Language"));
		
		#fetch user data.
		$uEml = $userMgrData->userSettings("Email");
		$uTimeFormat = $userMgrData->userSettings("Time_format");
		$uMSN = $userMgrData->userSettings("MSN");
		$uAIM = $userMgrData->userSettings("AOL");
		$uICQ = $userMgrData->userSettings("ICQ");
		$uYIM = $userMgrData->userSettings("Yahoo");
		$uWWW = $userMgrData->userSettings("WWW");
		$uLoc = $userMgrData->userSettings("Location");
		$uSig = $userMgrData->userSettings("Sig");
		$uRSS1 = $userMgrData->userSettings("rssfeed1");
		$uRSS2 = $userMgrData->userSettings("rssfeed2");

		#modify user form.
		$tpl = new templateEngine($style, "cp-usermanage2");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[admincp]",
		"LANG-MANAGEUSER" => "$lang[manageuser]",
		"LANG-YES" => "$lang[yes]",
		"LANG-NO" => "$lang[no]",
		"LANG-TICKBAN" => "$lang[tickban]",
		"LANG-TEXT" => "$lang[usertext]",
		"LANG-EMAIL" => "$lang[email]",
		"EMAIL" => "$uEml",
		"LANG-USERNAME" => "$lang[username]",
		"USERNAME" => "$user",
		"LANG-TIME" => "$lang[timezone]",
		"TIME" => "$timezone",
		"LANG-TIMEFORMAT" => "$lang[timeformat]",
		"LANG-TIMEINFO" => "$lang[timeinfo]",
		"TIMEFORMAT" => "$uTimeFormat",
		"LANG-PMNOTIFY" => "$lang[pm_notify]",
		"LANG-SHOWEMAIL" => "$lang[showemail]",
		"LANG-STYLE" => "$lang[style]",
		"STYLE" => "$uStyle",
		"LANG-LANGUAGE" => "$lang[defaultlang]",
		"LANGUAGE" => "$language",
		"LANG-MSN" => "$lang[msn]",
		"MSN" => "$uMSN",
		"LANG-AOL" => "$lang[aol]",
		"AOL" => "$uAIM",
		"LANG-YIM" => "$lang[yim]",
		"YIM" => "$uYIM",
		"LANG-ICQ" => "$lang[icq]",
		"ICQ" => "$uICQ",
		"LANG-SIG" => "$lang[sig]",
		"SIG" => "$uSig",
		"LANG-WWW" => "$lang[www]",
		"WWW" => "$uWWW",
		"LANG-LOCATION" => "$lang[location]",
		"LOCATION" => "$uLoc",
		"LANG-RSSFEED1" => "$lang[rssfeed1]",
		"RSSFEED1" => "$uRSS1",
		"LANG-RSSFEED2" => "$lang[rssfeed2]",
		"RSSFEED2" => "$uRSS2",
		"LANG-ADMINTOOLS" => "$lang[admintools]",
		"LANG-ACTIVEUSER" => "$lang[activeuser]",
		"LANG-BANUSER" => "$lang[banuser]",
		"LANG-DELUSER" => "$lang[deluser]",
		"LANG-TICKDELETE" => "$lang[tickdel]",
		"LANG-RSSBAN" => "$lang[banrss]",
		"SUBMIT" => "$lang[submit]"));

		#PM Notify Check
		if ($userMgrData->userSettings('PM_Notify') == 1){
			$tpl->removeBlock("N_PMNotify");
		}else{
			$tpl->removeBlock("Y_PMNotify");
		}
		#Email display Check
		if($userMgrData->userSettings('Hide_Email') == 0){
			$tpl->removeBlock("N_HideEml");
		}else{
			$tpl->removeBlock("Y_HideEml");
		}
		#banned status Check
		if($uGID == 4){
			$tpl->removeBlock("nbannedUsr");
		}else{
			$tpl->removeBlock("bannedUsr");
		}
		#active Check
		if($userMgrData->userSettings('active') == 1){
			$tpl->removeBlock("inactiveUsr");
		}else{
			$tpl->removeBlock("activeUsr");
		}
		#banfeeds
		if($userMgrData->userSettings('banfeeds') == 1){
			$tpl->removeBlock("N_RSSBan");
		}else{
			$tpl->removeBlock("Y_RSSBan");
		}

		#output template file.
		echo $tpl->outputHtml();
	}
break;
case 'user_process':
	//get form details.
	$username = $db->filterMySQL($_POST['user']);
	$email = $db->filterMySQL($_POST['email']);
	$time_zone = $db->filterMySQL($_POST['time_zone']);
	$time_format = $db->filterMySQL($_POST['time_format']);
	$pm_notice = $db->filterMySQL($_POST['pm_notice']);
	$show_email = $db->filterMySQL($_POST['show_email']);
	$style = $db->filterMySQL($_POST['style']);
	$default_lang = $db->filterMySQL($_POST['default_lang']);
	$msn_messenger = $db->filterMySQL($_POST['msn_messenger']);
	$aol_messenger = $db->filterMySQL($_POST['aol_messenger']);
	$yim = $db->filterMySQL($_POST['yim']);
	$icq = $db->filterMySQL($_POST['icq']);
	$location = $db->filterMySQL($_POST['location']);
	$sig = $db->filterMySQL($_POST['sig']);
	$site = $db->filterMySQL($_POST['site']);
	$rssfeed1 = $db->filterMySQL($_POST['rssfeed1']);
	$rssfeed2 = $db->filterMySQL($_POST['rssfeed2']);
	$active_user = $db->filterMySQL($_POST['active_user']);
	$banfeed = $db->filterMySQL($_POST['banfeed']);
	$banuser = $db->filterMySQL($_POST['banuser']);
	$deluser = $db->filterMySQL($_POST['deluser']);

	//do some error checking.
	if(empty($username)){
		#setup error session.
		$_SESSION['errors'] = $lang['nousernameentered'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if ($style == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nostyle'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if ($default_lang == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nolang'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if ($time_zone == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['notimezone'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if ($time_format == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['notimeformat'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if ($pm_notice == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nopmnotify'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if ($show_email == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['noshowemail'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if ($email == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['noemail'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if(strlen($email) > 255){
		#setup error session.
		$_SESSION['errors'] = $lang['longemail'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if(strlen($time_format) > 14){
		#setup error session.
		$_SESSION['errors'] = $lang['longtimeformat'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if((!preg_match('/http:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/', $rssfeed1)) or (empty($rssfeed1))){
		#setup error session.
		$_SESSION['errors'] = $lang['invalidurl'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if((!preg_match('/http:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/', $rssfeed2)) or (empty($rssfeed2))){
		#setup error session.
		$_SESSION['errors'] = $lang['invalidurl'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if(strlen($rssfeed1) > 200){
		#setup error session.
		$_SESSION['errors'] = $lang['longrss'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if(strlen($rssfeed2) > 200){
		#setup error session.
		$_SESSION['errors'] = $lang['longrss'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}
	if ((!preg_match('/http:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/', $site)) and (!empty($site))) {
		#setup error session.
		$_SESSION['errors'] = $lang['invalidurl'];

        #direct user.
		redirect('acp/usercp.php?action=user_manage', false, 0);
	}

	//see if admin wants to ban user.
	if($banuser == "yes"){
		#set user as banned.
		$banUser = new groupPolicy($username);
		$banUser->changeGroupID(4);
	}else{
		#set user as member.
		$regUser = new groupPolicy($username);
		$regUser->changeGroupID(3);
	}

	//see if admin wants to delete user.
	if($deluser == "yes"){
		$db->SQL = "DELETE FROM ebb_users WHERE Username='$username'";
		$db->query();

		//delete topics made by user.
		$db->SQL = "DELETE FROM ebb_posts WHERE re_author='$username'";
		$db->query();

		//get topic details to delete replies,then delete the topics.
		$db->SQL = "SELECT tid FROM ebb_topics WHERE author='$username'";
		$board_query = $db->query();

		#delete all replies made in the topics.
		while($replies = mysql_fetch_assoc($sql)){
			$db->SQL = "DELETE FROM ebb_posts WHERE tid='$replies[tid]'";
			$db->query();
		}

		#delete all topics made by this user.
		$db->SQL = "DELETE FROM ebb_topics WHERE author='$username'";
		$db->query();

		//delete any subscriptions to topics this user belongs to..
		$db->SQL = "DELETE FROM ebb_topic_watch WHERE username='$username'";
		$db->query();
	}
	//update user info.
	$db->SQL = "UPDATE ebb_users SET Email='$email', MSN='$msn_messenger', AOL='$aol_messenger', Yahoo='$yim', ICQ='$icq', Location='$location', Sig='$sig', WWW='$site', Time_format='$time_format', Time_Zone='$time_zone', PM_Notify='$pm_notice', Hide_Email='$show_email', Style='$style', Language='$default_lang', rssfeed1='$rssfeed1', rssfeed2='$rssfeed2', banfeeds='$banfeed', active='$active_user' WHERE Username='$username'";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Modified User Profile: ".$username, $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/usercp.php', false, 0);
break;
case 'warnlog':
	#get user warn log.
	$db->SQL = "SELECT id, Username, Authorized, Action, Message FROM ebb_warnlog";
	$warn_log_ct = $db->affectedRows();
	$warn_log_q = $db->query();

	#see if there are any entries currently.
	if($warn_log_ct == 0){
		$error = new notifySys($lang['nowarnactions'], true);
		$error->displayMessage();
	}else{
		#load warning log.
		warn_log();
	}
break;
case 'revoke':
	#make sure id was defined.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$error = new notifySys($lang['norid'], true);
		$error->displayError();
	}else{
		$id = $db->filterMySQL($_GET['rid']);
	}
	#see if id exist.
	$db->SQL = "SELECT id FROM ebb_warnlog WHERE id='$id'";
	$warnLogChk = $db->affectedRows();

	if($warnLogChk == 0){
		$error = new notifySys($lang['invalidrid'], true);
		$error->displayError();
	}else{
		$db->SQL = "SELECT Action, Username FROM ebb_warnlog WHERE id='$id'";
		$revokeRes = $db->fetchResults();

		#get user's current warning level.
		$db->SQL = "SELECT warning_level FROM ebb_users WHERE Username='$revokeRes[Username]'";
		$warnRes = $db->fetchResults();

		#see what will be revoked.
		if($revokeRes['Action'] == 1){
			$lower_warn = $warnRes['warning_level'] - 10;
			#update user's current warning level.
			$db->SQL = "UPDATE ebb_users SET warning_level='$lower_warn' WHERE Username='$revokeRes[Username]'";
			$db->query();

		}elseif($revokeRes['Action'] == 2){
			$raise_warn = $warnRes['warning_level'] + 10;
			#update user's current warning level.
			$db->SQL = "UPDATE ebb_users SET warning_level='$raise_warn' WHERE Username='$revokeRes[Username]'";
			$db->query();

		}elseif($revoke_r['Action'] == 3){
			#update user's current stat.
			$db->SQL = "UPDATE ebb_users SET Status='Member' WHERE Username='$revokeRes[Username]'";
			$db->query();

		}elseif($revoke_r['Action'] == 4){
			#remove suspension info from db.
			$db->SQL = "UPDATE ebb_users SET suspend_length='0', suspend_time='' WHERE Username='$revokeRes[Username]'";
			$db->query();

		}else{
			$error = new notifySys($lang['actionblank'], true);
			$error->displayError();
		}

		#log this into our audit system.
		$acpAudit = new auditSystem();
		$acpAudit->logAction("Revoked an action on ".$revokeRes['Username'], $acpUsr, time(), detectProxy());

		//bring user back to board section
		redirect('acp/usercp.php?action=warnlog', false, 0);
	}
break;
case 'clearwarnlog':
	#SQL sql query that will clear the warnlog db.
	$db->SQL = "TRUNCATE TABLE ebb_warnlog";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Cleared Warning Log", $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/usercp.php?action=warnlog', false, 0);
break;
case 'activate':
	//get list of inactive users.
	$db->SQL = "SELECT id, Username, Date_Joined FROM ebb_users WHERE active='0'";
	$inactive_q = $db->query();
	$user_ct = $db->affectedRows();

	//display inactive user list.
	inactive_users();
break;
case 'activate_user';
	$stat = $db->filterMySQL($_GET['stat']);
	$id = $db->filterMySQL($_GET['id']);

	//load email language file.
	require FULLPATH."/lang/".$lng.".email.php";
	
	if($stat == "accept"){
		//check for correct user id.
		$db->SQL = "SELECT id, Email FROM ebb_users WHERE id='$id'";
		$acct_chk = $db->affectedRows();
		$getEml = $db->fetchResults();

		if($acct_chk == 1){
			//set user as active.
			$db->SQL = "UPDATE ebb_users SET active='1' WHERE id='$id'";
			$db->query();

			#setup mailer.
			$mailer = new PHPMailer();
			$mailer->Subject = $lang['acceptsubject'];
			$mailer->Body = accept_user();
			$mailer->AddAddress($getEml['Email']);
			$mailer->SetFrom($boardPref->getPreferenceValue("board_email"), $title);
			#see if SMTP is used.
			if($boardPref->getPreferenceValue("mail_type") == 0){
			    $mailer->IsSMTP();
			    $mailer->SMTPAuth = true;
				$mailer->Host = $boardPref->getPreferenceValue("smtp_host");
				$mailer->Port = $boardPref->getPreferenceValue("smtp_port");
				$mailer->Username = $boardPref->getPreferenceValue("smtp_user");
				$mailer->Password = $boardPref->getPreferenceValue("smtp_pwd");
			}else{
				$mailer->IsMail();
			}
			$mailer->Send();
			$mailer->ClearAddresses();
			
			#log this into our audit system.
			$acpAudit = new auditSystem();
			$acpAudit->logAction("Activated User", $acpUsr, time(), detectProxy());
			
			$error = new notifySys($lang['useractivated'], true);
			$error->displayMessage();

			//bring user back to board section
			redirect('acp/usercp.php?action=activate', true, 5);
		}else{
			#log this into our audit system.
			$acpAudit = new auditSystem();
			$acpAudit->logAction("Failed Activating User", $acpUsr, time(), detectProxy());

			$error = new notifySys($lang['useractivateerror'], true);
			$error->displayMessage();

			//bring user back to board section
			redirect('acp/usercp.php?action=activate', true, 5);
		}
	}else{
		//check for correct user id.
		$db->SQL = "SELECT Username, Email FROM ebb_users WHERE id='$id'";
		$acct_chk = $db->affectedRows();
		$getData = $db->fetchResults();

		if($acct_chk == 1){
			//delete user from database.
			$db->SQL = "DELETE FROM ebb_users WHERE id='$id'";
			$db->query();
			
			#delete user from group.
			$db->SQL = "DELETE FROM ebb_group_users WHERE Username='$getData[Username]'";
			$db->query();

			#setup mailer.
			$mailer = new PHPMailer();
			$mailer->Subject = $lang['denysubject'];
			$mailer->Body = deny_user();
			$mailer->AddAddress($getEml['Email']);
			$mailer->SetFrom($boardPref->getPreferenceValue("board_email"), $title);
			#see if SMTP is used.
			if($boardPref->getPreferenceValue("mail_type") == 0){
			    $mailer->IsSMTP();
			    $mailer->SMTPAuth = true;
				$mailer->Host = $boardPref->getPreferenceValue("smtp_host");
				$mailer->Port = $boardPref->getPreferenceValue("smtp_port");
				$mailer->Username = $boardPref->getPreferenceValue("smtp_user");
				$mailer->Password = $boardPref->getPreferenceValue("smtp_pwd");
			}else{
				$mailer->IsMail();
			}
			$mailer->Send();
			$mailer->ClearAddresses();

			#log this into our audit system.
			$acpAudit = new auditSystem();
			$acpAudit->logAction("Denied User Activation", $acpUsr, time(), detectProxy());

			$error = new notifySys($lang['userdeny'], true);
			$error->displayMessage();

			//bring user back to board section
			redirect('acp/usercp.php?action=activate', true, 5);
		}else{
			#log this into our audit system.
			$acpAudit = new auditSystem();
			$acpAudit->logAction("Failed Dening User Activation", $acpUsr, time(), detectProxy());

			$error = new notifySys($lang['useractivateerror'], true);
			$error->displayMessage();

			//bring user back to board section
			redirect('acp/usercp.php?action=activate', true, 5);
		}
	}
break;
case 'banlist':
	#load up our select boxes.
	$admin_banlist_ip = admin_banlist_ip();
	$admin_banlist_email = admin_banlist_email();

	#see if any errors were reported by auth.php.
	if(isset($_SESSION['errors'])){
	    #format error(s) for the user.
		$errors = var_cleanup($_SESSION['errors']);

		#display validation message.
	    $displayMsg = new notifySys($errors, false);
		$displayMsg->displayValidate();

		#destroy errors session data, its no longer needed.
       	unset($_SESSION['errors']);
	}

	#banlist form.
	$tpl = new templateEngine($style, "cp-banlist");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-BANLIST" => "$lang[banlist]",
	"LANG-TEXT" => "$lang[banlisttext]",
	"LANG-EMAILBAN" => "$lang[emailban]",
	"LANG-BANEMAILTXT" => "$lang[emailbantxt]",
	"LANG-MATCHTYPETXT" => "$lang[matchtypetxt]",
	"LANG-EXACTMATCH" => "$lang[exactmatch]",
	"LANG-WILDCARDMATCH" => "$lang[wildcardmatch]",
	"LANG-UNBANEMAIL" => "$lang[emailunban]",
	"LANG-UNBANEMAILTXT" => "$lang[emailunbantxt]",
	"BANLIST-EMAIL" => "$admin_banlist_email",
	"LANG-BANIP" => "$lang[ipban]",
	"LANG-BANIPTXT" => "$lang[ipbantxt]",
	"LANG-UNBANIP" => "$lang[ipunban]",
	"LANG-UNBANIPTXT" => "$lang[ipunbantxt]",
	"BANLIST-IP" => "$admin_banlist_ip",
	"LANG-SUBMIT" => "$lang[submit]"));

	#output template file.
	echo $tpl->outputHtml();
break;
case 'ban_add':
	//form values.
	$emailbanning = $db->filterMySQL($_POST['emailbanning']);
	$ipbanning = $db->filterMySQL($_POST['ipbanning']);
	$match_type = $db->filterMySQL($_POST['match_type']);
	$type = $db->filterMySQL($_POST['type']);

	//error checking.
	if(($match_type == "") AND ($type == "Email")){
		#setup error session.
		$_SESSION['errors'] = $lang['nomatchtype'];

        #direct user.
		redirect('acp/usercp.php?action=banlist', false, 0);
	}
	//if emailbanning is blank.
	if((empty($emailbanning)) AND ($type == "Email")){
		#setup error session.
		$_SESSION['errors'] = $lang['noemailban'];

        #direct user.
		redirect('acp/usercp.php?action=banlist', false, 0);
	}
	if ((empty($ipbanning)) AND ($type == "IP")){
		#setup error session.
		$_SESSION['errors'] = $lang['noip'];

        #direct user.
		redirect('acp/usercp.php?action=banlist', false, 0);
	}
	if(strlen($emailbanning) > 255){
		#setup error session.
		$_SESSION['errors'] = $lang['longemailban'];

        #direct user.
		redirect('acp/usercp.php?action=banlist', false, 0);
	}
	if(strlen($ipbanning) > 15){
		#setup error session.
		$_SESSION['errors'] = $lang['longipban'];

        #direct user.
		redirect('acp/usercp.php?action=banlist', false, 0);
	}
	if($type == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nobantype'];

        #direct user.
		redirect('acp/usercp.php?action=banlist', false, 0);
	}

	//process query
	if($type == "IP"){
		$db->SQL = "INSERT INTO ebb_banlist (ban_item, ban_type, match_type) VALUES('$ipbanning', 'IP', 'Exact')";
		$db->query();

		#log this into our audit system.
		$acpAudit = new auditSystem();
		$acpAudit->logAction("Banned IP: ".$ipbanning, $acpUsr, time(), detectProxy());
	}
	if($type == "Email"){
		$db->SQL = "INSERT INTO ebb_banlist (ban_item, ban_type, match_type) VALUES('$emailbanning', 'Email', '$match_type')";
		$db->query();

		#log this into our audit system.
		$acpAudit = new auditSystem();
		$acpAudit->logAction("Banned E-mail Address: ".$emailbanning, $acpUsr, time(), detectProxy());
	}

	//bring user back
	redirect('acp/usercp.php?action=banlist', false, 0);
break;
case 'ban_remove':
	//get form values
	$ipsel = $db->filterMySQL($_POST['ipsel']);
	$emailsel = $db->filterMySQL($_POST['emailsel']);
	$type = $db->filterMySQL($_POST['type']);

	//error check.
	if($type == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nobantype'];

        #direct user.
		redirect('acp/usercp.php?action=banlist', false, 0);
	}
	if(($ipsel == "") AND ($type == "IP") OR ($emailsel == "") AND ($type == "Email")){
		#setup error session.
		$_SESSION['errors'] = $lang['noselectban'];

        #direct user.
		redirect('acp/usercp.php?action=banlist', false, 0);
	}else{
		//process query
		if($type == "IP"){
			$db->SQL = "DELETE FROM ebb_banlist WHERE id='$ipsel'";
			$db->query();

			#log this into our audit system.
			$acpAudit = new auditSystem();
			$acpAudit->logAction("Removed IP From Banlist", $acpUsr, time(), detectProxy());
		}
		if($type == "Email"){
			$db->SQL = "DELETE FROM ebb_banlist WHERE id='$emailsel'";
			$db->query();

			#log this into our audit system.
			$acpAudit = new auditSystem();
			$acpAudit->logAction("Removed E-mail Address from Banlist", $acpUsr, time(), detectProxy());
		}
		//bring user back
		redirect('acp/usercp.php?action=banlist', false, 0);
	}
break;
case 'blacklistuser':
	#load up blacklist list.
	$username_blacklist = admin_blacklist();

	#see if any errors were reported.
	if(isset($_SESSION['errors'])){
	    #format error(s) for the user.
		$errors = var_cleanup($_SESSION['errors']);

		#display validation message.
	    $displayMsg = new notifySys($errors, false);
		$displayMsg->displayValidate();

		#destroy errors session data, its no longer needed.
       	unset($_SESSION['errors']);
	}

	#banlist form.
	$tpl = new templateEngine($style, "cp-blacklist");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-BLACKLISTEDUSERS" => "$lang[blacklist]",
	"LANG-TEXT" => "$lang[usernameblacklisttxt]",
	"LANG-BLACKEDUSERNAME" => "$lang[blacklistusername]",
	"LANG-USERNAMETOBAN" => "$lang[blackedusername]",
	"LANG-BLACKLISTTYPE" => "$lang[blacklisttype]",
	"LANG-EXACTMATCH" => "$lang[exactmatch]",
	"LANG-WILDCARDMATCH" => "$lang[wildcardmatch]",
	"LANG-UNBLACKLISTUSER" => "$lang[whitelistingusername]",
	"LANG-UNBLACKLISTUSERTXT" => "$lang[whitelistingusernametxt]",
	"BLACKLIST" => "$username_blacklist",
	"LANG-SUBMIT" => "$lang[submit]"));

	#output template file.
	echo $tpl->outputHtml();
break;
case 'blacklist_add':
	//get form data.
	$blacklistuser = $db->filterMySQL($_POST['blacklistuser']);
	$match_type = $db->filterMySQL($_POST['match_type']);

	//error checking.
	if(empty($blacklistuser)){
		#setup error session.
		$_SESSION['errors'] = $lang['nousernameentered'];

        #direct user.
		redirect('acp/usercp.php?action=blacklistuser', false, 0);
	}
	if($match_type == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nomatchtype'];

        #direct user.
		redirect('acp/usercp.php?action=blacklistuser', false, 0);
	}
	if(strlen($blacklistuser) > 25){
		#setup error session.
		$_SESSION['errors'] = $lang['longusername'];

        #direct user.
		redirect('acp/usercp.php?action=blacklistuser', false, 0);
	}

	//process query
	$db->SQL = "INSERT INTO ebb_blacklist (blacklisted_username, match_type) VALUES('$blacklistuser', '$match_type')";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Blacklisted Username: ".$blacklistuser, $acpUsr, time(), detectProxy());

	//bring user back
	redirect('acp/usercp.php?action=blacklistuser', false, 0);
break;
case 'blacklist_remove':
	//get form data.
	$blkusersel = $db->filterMySQL($_POST['blkusersel']);

	#error check.
	if($blkusersel == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nousernameselected'];

        #direct user.
		redirect('acp/usercp.php?action=blacklistuser', false, 0);
	}else{
		//process query
		$db->SQL = "DELETE FROM ebb_blacklist WHERE id='$blkusersel'";
		$db->query();

		#log this into our audit system.
		$acpAudit = new auditSystem();
		$acpAudit->logAction("Removed Blacklisted Username from Banlist", $acpUsr, time(), detectProxy());

		//bring user back
		redirect('acp/usercp.php?action=blacklistuser', false, 0);
	}
break;
case 'user_prune':
	#user prune form.
	$tpl = new templateEngine($style, "cp-userprune");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-USERPRUNE" => "$lang[userprune]",
	"LANG-TEXT" => "$lang[userprunetext]",
	"LANG-PRUNEWARNING" => "$lang[userprunewarning]",
	"LANG-BEGINPRUNE" => "$lang[beginuserprune]"));

	#output template file.
	echo $tpl->outputHtml();
break;
case 'process_user_pruning':
	#do some math.
	$date_math = 3600*24*7;
	$time_eq = time() - $date_math;

	//process query
	$db->SQL = "DELETE FROM ebb_users WHERE Date_Joined>='$time_eq'";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Pruned Inactive Users from database", $acpUsr, time(), detectProxy());

	//bring user back
	redirect('acp/index.php', false, 0);
break;
default:
	#user profile form.
	$tpl = new templateEngine($style, "cp-usermanage");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-SELECTUSER" => "$lang[seluser]",
	"LANG-TEXT" => "$lang[usertxt]",
	"LANG-USERNAME" => "$lang[username]"));

	#output template file.
	echo $tpl->outputHtml();
break;
}
#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();
ob_end_flush();
?>
