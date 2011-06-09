<?php
define('IN_EBB', true);
/**
Filename: PM.php
Last Modified: 3/15/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";
require_once FULLPATH."/includes/swift/swift_required.php";

if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = ''; 
}
#get title information.
switch($action){
case 'write':
case 'write_process':
	$pmtitle = $lang['PostPM'];
	$helpTitle = $help['pmcreatetitle'];
	$helpBody = $help['pmcreatebody'];
break;
case 'read':
	$pmtitle = $lang['readpm'];
	$helpTitle = $help['pmreadtitle'];
	$helpBody = $help['pmreadbody'];
break;
case 'reply':
case 'reply_process':
	$pmtitle = $lang['replypm'];
	$helpTitle = $help['pmcreatetitle'];
	$helpBody = $help['pmcreatebody'];
break;
case 'delete':
	$pmtitle = $lang['delpm'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'ban':
case 'ban_process':
	$pmtitle = $lang['banusertitle'];
	$helpTitle = $help['pmbantitle'];
	$helpBody = $help['pmbanbody'];
break;
case 'banlist':
	$pmtitle = $lang['banlist'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'del_ban':
	$pmtitle = $lang['delbanuser'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
default:
	$pmtitle = $lang['pm'];
	$helpTitle = $help['pmtitle'];
	$helpBody = $help['pmbody'];
}
#output header.
$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
	"TITLE" => "$title",
    "PAGETITLE" => "$pmtitle",
    "LANG-HELP-TITLE" => "$helpTitle",
    "LANG-HELP-BODY" => "$helpBody",
    "LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();


#see if user can access PM.
if($groupPolicy->validateAccess(1, 27) == false){
    $displayMsg = new notifySys($lang['accessdenied'], true);
	$displayMsg->displayError();
}

//output top
if($logged_user == "guest"){
	redirect('login.php', false, 0);
}

$pmMsg = $userData->getNewPMCount();

$tpl = new templateEngine($style, "top");
$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-WELCOME" => "$lang[welcome]",
	"LANG-WELCOMEGUEST" => "$lang[welcomeguest]",
	"LOGGEDUSER" => "$logged_user",
	"LANG-LOGIN" => "$lang[login]",
	"LANG-REGISTER" => "$lang[register]",
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
	"LANG-PROFILE" => "$lang[profile]",
	"LANG-USERNAME" => "$lang[username]",
	"LANG-PASSWORD" => "$lang[pass]",
	"LANG-FORGOT" => "$lang[forgot]",
	"LANG-REMEMBERTXT" => "$lang[remembertxt]",
	"LANG-LOGIN" => "$lang[login]"));

#do some decision making.
if($groupAccess == 1){
	$tpl->removeBlock("user");
	$tpl->removeBlock("guest");
	$tpl->removeBlock("guestMenu");
	$tpl->removeBlock("login");

	#update user's activity.
	echo update_whosonline_reg($logged_user);
}elseif(($groupAccess == 2) or ($groupAccess == 3)){
	$tpl->removeBlock("admin");
	$tpl->removeBlock("guest");
	$tpl->removeBlock("guestMenu");
	$tpl->removeBlock("login");

	#update user's activity.
	echo update_whosonline_reg($logged_user);
}
#output top template file.
echo $tpl->outputHtml();

#set some posting rules.
$allowsmile = 1;
$allowbbcode = 1;
$allowimg = 0;

switch ($action){
case 'write':
	if(isset($_GET['user'])){
		$user = $db->filterMySQL($_GET['user']);
	}else{
		$user = '';
	}
	//@TODO: add new editor to PM.php
	$bbcode = '';
	$smile = form_smiles('body');
	
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
	
	#load template file.
	$tpl = new templateEngine($style, "pm-postpm");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[pm]",
	"LANG-POSTPM" => "$lang[PostPM]",
	"BBCODE" => "$bbcode",
	"LANG-SMILES" => "$lang[moresmiles]",
	"SMILES" => "$smile",
	"LANG-USERNAME" => "$lang[username]",
	"USERNAME" => "$logged_user",
	"LANG-TO" => "$lang[send]",
	"TO" => "$user",
	"LANG-SUBJECT" => "$lang[subject]",
	"LANG-SENDPM" => "$lang[sendpm]"));
	echo $tpl->outputHtml();
  break;
  case 'write_process':
	//get the values from the form.
	$send = $db->filterMySQL($_POST['send']);
	$subject = $db->filterMySQL($_POST['subject']);
	$message = $db->filterMySQL(var_cleanup($_POST['message']));

	#error check.
	if (empty($send)){
		#setup error session.
		$_SESSION['errors'] = $lang['nosend'];

		#direct user.
		redirect('PM.php?action=write', false, 0);
	}
	if (empty($subject)){
		#setup error session.
		$_SESSION['errors'] = $lang['nosubject'];

		#direct user.
		redirect('PM.php?action=write', false, 0);
	}
	if (empty($message)){
		#setup error session.
		$_SESSION['errors'] = $lang['nomessage'];

		#direct user.
		redirect('PM.php?action=write', false, 0);
	}
	if(strlen($send) > 25){
		#setup error session.
		$_SESSION['errors'] = $lang['longuser'];

		#direct user.
		redirect('PM.php?action=write', false, 0);
	}
	if(strlen($subject) > 25){
		#setup error session.
		$_SESSION['errors'] = $lang['longsubject'];

		#direct user.
		redirect('PM.php?action=write', false, 0);
	}

	#setup and obtain user group status & information.
	$userGroupPolicy = new groupPolicy($send);
	$userInfo = new user($send);

	if($userGroupPolicy->validateAccess(1, 27) == false){
		$displayMsg = new notifySys($lang['pm_access_user'], true);
		$displayMsg->displayError();
	}

	//check to see if the from user's inbox is full.
	$db->SQL = "SELECT id FROM ebb_pm WHERE Reciever='$send' AND Folder='Inbox'";
	$check_inbox = $db->affectedRows();

	if ($check_inbox == $boardPref->getPreferenceValue("pm_quota")){
		$displayMsg = new notifySys($lang['overquota'], true);
		$displayMsg->displayError();
	}

	//check to see if this user is on the ban list.
	$db->SQL = "SELECT id FROM ebb_pm_banlist WHERE Banned_User='$logged_user' AND Ban_Creator='$send'";
	$check_ban_r = $db->affectedRows();

	if ($check_ban_r == 1){
		$displayMsg = new notifySys($lang['blocked'], true);
		$displayMsg->displayError();
	}else{
		$time = time();
		//process query
		$db->SQL = "INSERT INTO ebb_pm (Sender, Reciever, Subject, Folder, Message, Date) VALUES('$logged_user', '$send', '$subject', 'Inbox', '$message', '$time')";
		$db->query();

		#see if user wishes to recieve an email about new PMs.
		if ($userInfo->userSettings("PM_Notify") == 1){
			//get pm id.
			$db->SQL = "SELECT id FROM ebb_pm ORDER BY id DESC limit 1";
			$pm_id_result = $db->fetchResults();

			//grab values from PM message.
			$db->SQL = "SELECT Reciever, Sender, Subject, id FROM ebb_pm WHERE id='$pm_id_result[id]'";
			$pm_data = $db->fetchResults();

			#load mail language file.
			require_once FULLPATH."/lang/".$lng.".email.php";

			#see what kind of transport to use.
			if($boardPref->getPreferenceValue("mail_type") == 0){
				#see if we're using some form of encryption.
				if ($boardPref->getPreferenceValue("smtp_encryption") == ""){
					//Create the Transport
					$transport = Swift_SmtpTransport::newInstance($boardPref->getPreferenceValue("smtp_host"), $boardPref->getPreferenceValue("smtp_port"))
					  ->setUsername($boardPref->getPreferenceValue("smtp_user"))
					  ->setPassword($boardPref->getPreferenceValue("smtp_pwd"));
				} else {
					//Create the Transport
					$transport = Swift_SmtpTransport::newInstance($boardPref->getPreferenceValue("smtp_host"), $boardPref->getPreferenceValue("smtp_port"), $boardPref->getPreferenceValue("smtp_encryption"))
					  ->setUsername($boardPref->getPreferenceValue("smtp_user"))
					  ->setPassword($boardPref->getPreferenceValue("smtp_pwd"));
				}

				//Create the Mailer using your created Transport
				$mailer = Swift_Mailer::newInstance($transport);
			} else if ($boardPref->getPreferenceValue("mail_type") == 2){
				//TODO add sendmail option to administation panel
				//Create the Transport
				$transport = Swift_SendmailTransport::newInstance($boardPref->getPreferenceValue("sendmail_path").' -bs');

				//Create the Mailer using your created Transport
				$mailer = Swift_Mailer::newInstance($transport);
			} else {
				//Create the Transport
				$transport = Swift_MailTransport::newInstance();

				//Create the Mailer using your created Transport
				$mailer = Swift_Mailer::newInstance($transport);
			}

			#build email.
			$message = Swift_Message::newInstance($lang['pmsubject'])
				->setFrom(array($boardPref->getPreferenceValue("board_email") => $title)) //Set the From address
				->setTo(array($userInfo->userSettings("Email") => $send))
				->setBody(pm_notify()); //set email body

			#create array for replacements.
			$replacements[$userInfo->userSettings("Email")] = array(
				'{pm-receiver}'=>$pm_data['Reciever'],
				'{pm-sender}'=>$pm_data['Sender'],
				'{pm-subject}' =>$pm_data['Sender'],
				'{boardAddr}'=>$boardAddr,
				'{pm-id}'=>$pm_data['id']
			);

			#setup mailer template.
			$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
			$mailer->registerPlugin($decorator);

			#send message out.
			//TODO: Add a failure list to this method to help administrators weed out "dead" accounts.
			$mailer->Send($message);
		}
		//bring user back
		redirect('PM.php', false, 0);
	}
break;
case 'read':
	#validate PM ID.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$displayMsg = new notifySys($lang['nopmid'], true);
		$displayMsg->displayError();
	}else{
		$id = $db->filterMySQL($_GET['id']);
	}

	#get PM data.
	$db->SQL = "SELECT id, Read_Status, Sender, Reciever, Folder, Subject, Message, Date FROM ebb_pm WHERE id='$id'";
	$pm_r = $db->fetchResults();
	$chk_pm = $db->affectedRows();

	#see if pm message exist.
	if($chk_pm == 0){
		$displayMsg = new notifySys($lang['pm404'], true);
		$displayMsg->displayError();
	}

	//see if pm message belong to the right user.
	if ($pm_r['Reciever'] !== $logged_user){
		$displayMsg = new notifySys($lang['accessdenied'], true);
		$displayMsg->displayError();
	}

	//mark as read
	if (empty($pm_r['Read_status'])){
		$db->SQL = "UPDATE ebb_pm SET Read_Status='old' WHERE id='$id'";
		$db->query();
	}

	//bbcode & other formating processes.
	$pmMsg = nl2br(smiles(BBCode(language_filter($pm_r['Message'], 1), true)));
	
	//get the date
    $pmDate = formatTime($timeFormat, $pm_r['Date'], $gmt);

	#get information regarding the sender.
    $userInfo = new user($pm_r['Sender']);

	//get sig.
	$uSig = $userInfo->userSettings("Sig");
	if(empty($uSig)){
		$sig = "";
	}else{
		$pmsig = nl2br(smiles(BBCode(language_filter($userInfo->userSettings("Sig"), 1), true)));
		$sig = "_____________<br />".$pmsig;
	}

	#load template file.
	$tpl = new templateEngine($style, "pm-read");
	$tpl->parseTags(array(
	"BOARDDIR" => "$boardDir",
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[pm]",
	"LANG-READPM" => "$lang[readpm]",
	"LANG-DELPROMPT" => "$lang[confirmdelete]",
	"LANG-MOVEPROMPT" => "$lang[moveconfirm]",
	"LANG-REPLY" => "$lang[replyalt]",
	"ID" => "$pm_r[id]",
	"LANG-FROM" => "$lang[from]",
	"FROM" => "$pm_r[Sender]",
	"LANG-BANUSER" => "$lang[banuser]",
	"LANG-TO" => "$lang[to]",
	"TO" => "$pm_r[Reciever]",
	"LANG-DATE" => "$lang[date]",
	"DATE" => "$pmDate",
	"LANG-SUBJECT" => "$lang[subject]",
	"SUBJECT" => "$pm_r[Subject]",
	"PM-MESSAGE" => "$pmMsg",
	"SIGNATURE" => "$sig",
	"LANG-DELETEPM" => "$lang[delpm]",
	"LANG-MOVEPM" => "$lang[movemsg]"));

	#do some decision making.
 	if($pm_r['Folder'] == "Archive"){
		$tpl->removeBlock("reply");
		$tpl->removeBlock("inbox");
	}else{
		$tpl->removeBlock("archive");
	}
    echo $tpl->outputHtml();
break;
case 'reply':
	#validate PM ID.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$displayMsg = new notifySys($lang['nopmid'], true);
		$displayMsg->displayError();
	}else{
		$id = $db->filterMySQL($_GET['id']);
	}

	$db->SQL = "SELECT Sender, Subject FROM ebb_pm WHERE id='$id'";
	$reply = $db->fetchResults();

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

	$bbcode = bbcode_form('body');
	$smile = form_smiles('body');

	#load template file.
	$tpl = new templateEngine($style, "pm-replypm");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[pm]",
	"LANG-REPLYPM" => "$lang[replypm]",
	"BBCODE" => "$bbcode",
	"LANG-SMILES" => "$lang[moresmiles]",
	"SMILES" => "$smile",
	"LANG-USERNAME" => "$lang[username]",
	"USERNAME" => "$logged_user",
	"LANG-TO" => "$lang[send]",
	"TO" => "$reply[Sender]",
	"LANG-SUBJECT" => "$lang[subject]",
	"SUBJECT" => "$reply[Subject]",
	"LANG-SENDPM" => "$lang[reply]"));
    echo $tpl->outputHtml();
break;
case 'reply_process':
	//get the value from the form.
	$reply_send = $db->filterMySQL($_POST['send']);
	$reply_message = $db->filterMySQL(var_cleanup($_POST['message']));
	$re_subject = $db->filterMySQL($_POST['subject']);

	//error-check.
	if (empty($reply_send)){
		#setup error session.
		$_SESSION['errors'] = $lang['nosend'];

		#direct user.
		redirect('PM.php?action=reply', false, 0);
	}
	if (empty($reply_message)){
		#setup error session.
		$_SESSION['errors'] = $lang['nomessage'];

		#direct user.
		redirect('PM.php?action=reply', false, 0);
	}
	if(strlen($reply_send) > 25){
		#setup error session.
		$_SESSION['errors'] = $lang['longuser'];

		#direct user.
		redirect('PM.php?action=reply', false, 0);
	}

	#setup and obtain user group status & information.
	$userGroupPolicy = new groupPolicy($reply_send);
	$userInfo = new user($reply_send);

	if($userGroupPolicy->validateAccess(1, 27) == false){
		$displayMsg = new notifySys($lang['pm_access_user'], true);
		$displayMsg->displayError();
	}
	
	//check to see if the from user's inbox is full.
	$db->SQL = "SELECT id FROM ebb_pm WHERE Reciever='$reply_send' AND Folder='Inbox'";
	$check_inbox = $db->affectedRows();

	if ($check_inbox == $boardPref->getPreferenceValue("pm_quota")){
		$displayMsg = new notifySys($lang['overquota'], true);
		$displayMsg->displayError();
	}
	
	//check to see if this user is on the ban list.
	$db->SQL = "SELECT id FROM ebb_pm_banlist WHERE Banned_User='$logged_user' AND Ban_Creator='$reply_send'";
	$check_ban_r = $db->affectedRows();

	if ($check_ban_r == 1){
		$displayMsg = new notifySys($lang['blocked'], true);
		$displayMsg->displayError();
	}else{
		//process query
		$time = time();
		$db->SQL = "INSERT INTO ebb_pm (Sender, Reciever, Subject, Folder, Message, Date) VALUES('$logged_user', '$reply_send', '$re_subject', 'Inbox', '$reply_message', '$time')";
		$db->query();

		#see if user wishes to recieve an email about new PMs.
		if ($userInfo->userSettings("PM_Notify") == 1){
			//get pm id.
			$db->SQL = "SELECT id FROM ebb_pm ORDER BY id DESC limit 1";
			$pm_id_result = $db->fetchResults();

			//grab values from PM message.
			$db->SQL = "SELECT Reciever, Sender, Subject, id FROM ebb_pm WHERE id='$pm_id_result[id]'";
			$pm_data = $db->fetchResults();

			#load mail language file.
			require_once FULLPATH."/lang/".$lng.".email.php";

			#see what kind of transport to use.
			if($boardPref->getPreferenceValue("mail_type") == 0){
				#see if we're using some form of encryption.
				if ($boardPref->getPreferenceValue("smtp_encryption") == ""){
					//Create the Transport
					$transport = Swift_SmtpTransport::newInstance($boardPref->getPreferenceValue("smtp_host"), $boardPref->getPreferenceValue("smtp_port"))
					  ->setUsername($boardPref->getPreferenceValue("smtp_user"))
					  ->setPassword($boardPref->getPreferenceValue("smtp_pwd"));
				} else {
					//Create the Transport
					$transport = Swift_SmtpTransport::newInstance($boardPref->getPreferenceValue("smtp_host"), $boardPref->getPreferenceValue("smtp_port"), $boardPref->getPreferenceValue("smtp_encryption"))
					  ->setUsername($boardPref->getPreferenceValue("smtp_user"))
					  ->setPassword($boardPref->getPreferenceValue("smtp_pwd"));
				}

				//Create the Mailer using your created Transport
				$mailer = Swift_Mailer::newInstance($transport);
			} else if ($boardPref->getPreferenceValue("mail_type") == 2){
				//TODO add sendmail option to administation panel
				//Create the Transport
				$transport = Swift_SendmailTransport::newInstance($boardPref->getPreferenceValue("sendmail_path").' -bs');

				//Create the Mailer using your created Transport
				$mailer = Swift_Mailer::newInstance($transport);
			} else {
				//Create the Transport
				$transport = Swift_MailTransport::newInstance();

				//Create the Mailer using your created Transport
				$mailer = Swift_Mailer::newInstance($transport);
			}

			#build email.
			$message = Swift_Message::newInstance($lang['pmsubject'])
				->setFrom(array($boardPref->getPreferenceValue("board_email") => $title)) //Set the From address
				->setTo(array($userInfo->userSettings("Email") => $send))
				->setBody(pm_notify()); //set email body

			#create array for replacements.
			$replacements[$userInfo->userSettings("Email")] = array(
				'{pm-receiver}'=>$pm_data['Reciever'],
				'{pm-sender}'=>$pm_data['Sender'],
				'{pm-subject}' =>$pm_data['Sender'],
				'{boardAddr}'=>$boardAddr,
				'{pm-id}'=>$pm_data['id']
			);

			#setup mailer template.
			$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
			$mailer->registerPlugin($decorator);

			#send message out.
			//TODO: Add a failure list to this method to help administrators weed out "dead" accounts.
			$mailer->Send($message);
		}
		//bring user back
		redirect('PM.php', false, 0);
	}
break;
case 'movemsg':
	#validate PM ID.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$displayMsg = new notifySys($lang['nopmid'], true);
		$displayMsg->displayError();
	}else{
		$id = $db->filterMySQL($_GET['id']);
	}

	$db->SQL = "SELECT Reciever FROM ebb_pm WHERE id='$id'";
	$pm_r = $db->fetchResults();

	//see if pm message belong to the right user.
	if ($pm_r['Reciever'] !== $logged_user){
		$displayMsg = new notifySys($lang['accessdenied'], true);
		$displayMsg->displayError();
	}
	
	#see if user has enough space to save message.
	$db->SQL = "SELECT id FROM ebb_pm WHERE Reciever='$logged_user' AND Folder='Archive'";
	$check_archive = $db->affectedRows();

	if ($check_archive == $boardPref->getPreferenceValue("archive_quota")){
		$displayMsg = new notifySys($lang['overquota'], true);
		$displayMsg->displayError();
	}else{
		//process query
		$db->SQL = "UPDATE ebb_pm SET Folder='Archive' WHERE id='$id'";
		$db->query();

		//bring user back
		redirect('PM.php', false, 0);
	}
break;
case 'delete':
	#validate PM ID.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$displayMsg = new notifySys($lang['nopmid'], true);
		$displayMsg->displayError();
	}else{
		$id = $db->filterMySQL($_GET['id']);
	}

	$db->SQL = "SELECT Reciever FROM ebb_pm WHERE id='$id'";
	$pm_r = $db->fetchResults();

	//see if pm message belong to the right user.
	if ($pm_r['Reciever'] !== $logged_user){
		$displayMsg = new notifySys($lang['accessdenied'], true);
		$displayMsg->displayError();
	}
	//process query
	$db->SQL = "DELETE FROM ebb_pm WHERE id='$id'";
	$db->query();

	//bring user back
	redirect('PM.php', false, 0);
break;
case 'ban':
	if(!isset($_GET['ban_user'])){
		$ban_user = ''; 
	}else{
		$ban_user = $db->filterMySQL($_GET['ban_user']);
	}

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
	
	#load template file.
	$tpl = new templateEngine($style, "pm-banuser");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[pm]",
	"LANG-BANUSER" => "$lang[banusertitle]",
	"LANG-NOUSERNAME" => "$lang[blankfield]",
	"LANG-LONGUSERNAME" => "$lang[longbanuser]",
	"TEXT" => "$lang[pmbantxt]",
	"LANG-USERNAME" => "$lang[username]",
	"USERNAME" => "$logged_user",
	"LANG-BAN" => "$lang[usertoban]",
	"BAN" => "$ban_user",
	"LANG-SUBMIT" => "$lang[banuser]"));
    echo $tpl->outputHtml();
  break;
  case 'ban_process':
	$banned_user = $db->filterMySQL($_POST['banned_user']);

	#error check.
	if ($banned_user == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['blankfield'];

		#direct user.
		redirect('PM.php?action=ban', false, 0);
	}
	if(strlen($banned_user) > 25){
		#setup error session.
		$_SESSION['errors'] = $lang['longbanuser'];

		#direct user.
		redirect('PM.php?action=ban', false, 0);
	}

	//process query
	$db->SQL = "INSERT INTO ebb_pm_banlist (Banned_User, Ban_Creator) VALUES('$banned_user', '$logged_user')";
	$db->query();

	//bring user back
	redirect('PM.php?action=banlist', false, 0);
break;
case 'banlist':
	$db->SQL = "SELECT Banned_User, id FROM ebb_pm_banlist WHERE Ban_Creator='$logged_user'";
	$banlistQ = $db->query();
	$banCount = $db->affectedRows();

	#load template file.
	$tpl = new templateEngine($style, "pm-viewbanlist_head");
	$tpl->parseTags(array(
	"BOARDDIR" => "$boardDir",
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[pm]",
	"LANG-BANLIST" => "$lang[banlisttitle]",
	"TEXT" => "$lang[pmbanlist]",
	"LANG-BANUSER" => "$lang[banusertitle]",
	"LANG-BANNEDUSER" => "$lang[banneduser]",
	"LANG-DELETE" => "$lang[del]",
	"LANG-NOBAN" => "$lang[noban]"));

	#do some decision making.
	if ($banCount > 0){
		$tpl->removeBlock("noresults");
	}
    echo $tpl->outputHtml();

    if ($banCount > 0){
    	#get banlist data.
	    while ($banlist = mysql_fetch_assoc($banlistQ)) {
	 		#load template file.
			$tpl = new templateEngine($style, "pm-viewbanlist");
			$tpl->parseTags(array(
			"BANNEDUSER" => "$banlist[Banned_User]",
			"ID" => "$banlist[id]",
			"LANG-DELPROMPT" => "$lang[banlistconfirm]",
			"LANG-DELETEUSER" => "$lang[del]"));
		    echo $tpl->outputHtml();
	    }
    }
	#load template file.
	$tpl = new templateEngine($style, "pm-viewbanlist_foot");
	echo $tpl->outputHtml();
break;
case 'del_ban':
	#validate PM ID.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$displayMsg = new notifySys($lang['nopmid'], true);
		$displayMsg->displayError();
	}else{
		$id = $db->filterMySQL($_GET['id']);
	}

	$db->SQL = "SELECT Ban_Creator FROM ebb_pm_banlist WHERE id='$id'";
	$ban_r = $db->fetchResults();

	//see if user banned the user they wish to delete.
	if ($ban_r['Ban_Creator'] !== $logged_user){
		$displayMsg = new notifySys($lang['accessdenied'], true);
		$displayMsg->displayError();
	}

	//process query
	$db->SQL = "DELETE FROM ebb_pm_banlist WHERE id='$id'";
	$db->query();

	//bring user back
	redirect('PM.php?action=banlist', false, 0);
  break;
  default:
  	#get current folder location.
	if(!empty($_GET['folder'])){
		$pmFolder = $db->filterMySQL($_GET['folder']);
	}else{
		$pmFolder = "Inbox";
	}
	
	#see if folder name are valid.
	if (($pmFolder == "Inbox") or ($pmFolder == "Outbox") or ($pmFolder == "Archive")){
		
		//pagination
		$count = 0;
		$count2 = 0;
		if(!isset($_GET['pg'])){
		    $pg = 1;
		}else{
		    $pg = $db->filterMySQL($_GET['pg']);
		}

		$perPg = $boardPref->getPreferenceValue("per_page");
		// Figure out the limit for the query based on the current page number.
		$from = (($pg * $perPg) - $perPg);
		
		// Figure out the total number of results in DB:
		if($pmFolder == "Outbox"){
			$db->SQL = "SELECT id, Subject, Sender, Date, Read_Status FROM ebb_pm WHERE Sender='$logged_user' AND Read_Status='' ORDER BY Date DESC LIMIT $from, $perPg";
			$query = $db->query();

			$db->SQL = "SELECT id FROM ebb_pm WHERE Sender='$logged_user' AND Read_Status=''";
			$num = $db->affectedRows();

		}else{
			$db->SQL = "SELECT id, Subject, Sender, Date, Read_Status FROM ebb_pm WHERE Reciever='$logged_user' AND Folder='$pmFolder' ORDER BY Date DESC LIMIT $from, $perPg";
			$query = $db->query();

			$db->SQL = "SELECT id FROM ebb_pm WHERE Reciever='$logged_user' AND Folder='$pmFolder'";
			$num = $db->affectedRows();

		}
		#output pagination.
		$pagenation = pagination('');

		#calculate percentage used from quota.
		if ($pmFolder == "Inbox"){
			$percentageUsed = Round(($num / $boardPref->getPreferenceValue("pm_quota")) * 100);
			$pm_quota = $boardPref->getPreferenceValue("pm_quota");
			$pm_lang_quota = $lang['pmquota'];
		}elseif ($pmFolder == "Outbox"){
			$percentageUsed = '&#8734;';
			$pm_quota = '&#8734;';
			$pm_lang_quota = $lang['pmquota'];
		}elseif ($pmFolder == "Archive"){
			$percentageUsed = Round(($num / $boardPref->getPreferenceValue("archive_quota")) * 100);
			$pm_quota = $boardPref->getPreferenceValue("archive_quota");
			$pm_lang_quota = $lang['archivequota'];
		}else{
			$displayMsg = new notifySys($lang['invalidfolder'], true);
			$displayMsg->displayError();
		}

		#load template file.
		$tpl = new templateEngine($style, "pm-inbox_head");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[pm]",
		"PAGENATION" => "$pagenation",
		"LANG-VIEWBANLIST" => "$lang[banlist]",
		"LANG-POSTPM" => "$lang[postpmalt]",
		"LANG-PMRULE" => "$pm_lang_quota",
		"PMRULE" => "$pm_quota",
		"LANG-CURRENTAMOUNT" => "$lang[curquota]",
		"CURRENTAMOUNT" => "$percentageUsed",
		"LANG-INBOX" => "$lang[inbox]",
		"LANG-OUTBOX" => "$lang[outbox]",
		"LANG-ARCHIVE" => "$lang[archive]",
		"LANG-SUBJECT" => "$lang[subject]",
		"LANG-SENDER" => "$lang[sender]",
		"LANG-PMDATE" => "$lang[date]",
		"LANG-NOPM" => "$lang[nopm]"));

		#do some decision making.
		if ($num > 0){
			$tpl->removeBlock("noresults");
		}
	    echo $tpl->outputHtml();

		#get PM list(if any).
		if($num > 0){
			while ($pmLst = mysql_fetch_assoc($query)) {
				$pmDate = formatTime($timeFormat, $pmLst['Date'], $gmt);

                #load template file.
            	$tpl = new templateEngine($style, "pm-inbox");

				#get status of pm messages.
				if ($pmLst['Read_Status'] == "old"){
					$icon = $tpl->displayPath($style)."images/old.gif";
				}else{
					$icon = $tpl->displayPath($style)."images/new.gif";
				}

				$tpl->parseTags(array(
				"READICON" => "$icon",
				"PMID" => "$pmLst[id]",
				"SUBJECT" => "$pmLst[Subject]",
				"SENDER" => "$pmLst[Sender]",
				"LANG-POSTEDBY" => "$lang[Postedby]",
				"POSTDATE" => "$pmDate"));

			    echo $tpl->outputHtml();
			}
		}
		#pm inbox footer.
		$tpl = new templateEngine($style, "pm-inbox_foot");
	    echo $tpl->outputHtml();
	}else{
		$displayMsg = new notifySys($lang['invalidfolder'], true);
		$displayMsg->displayError();
	}
}

#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();
ob_end_flush();
?>