<?php
define('IN_EBB', true);
/**
Filename: manage.php
Last Modified: 06/28/2012

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";
require_once FULLPATH."/includes/boardlisting.php";

if(isset($_GET['mode'])){
	$mode = var_cleanup($_GET['mode']);
}else{
	$mode = ''; 
}
#get page title.
switch($mode){
case 'viewip':
	#See if user can access this portion of the page.
	if($groupPolicy->validateAccess(1, 24) == false){
		$error = new notifySys($lang['accessdenied'], true);
		$error->genericError();
	}
	$modtitle = $lang['modcp'].' - '.$lang['ipinfo'];
	$helpTitle = $help['ipinfotitle'];
	$helpBody = $help['ipinfobody'];
break;
case 'dnslookup':
	#See if user can access this portion of the page.
	if($groupPolicy->validateAccess(1, 24) == false){
		$error = new notifySys($lang['accessdenied'], true);
		$error->genericError();
	}
	$modtitle = $lang['modcp'].' - '.$lang['getdns'];
	$helpTitle = $help['dnslookuptitle'];
	$helpBody = $help['dnslookupbody'];
break;
case 'warn':
case 'warn_process':
	#See if user can access this portion of the page.
	if($groupPolicy->validateAccess(1, 25) == false){
		$error = new notifySys($lang['accessdenied'], true);
		$error->genericError();
	}
	$modtitle = $lang['modcp'].' - '.$lang['warnuser'];
	$helpTitle = $help['warnusertitle'];
	$helpBody = $help['warnuserbody'];
break;
case 'move':
case 'move_process':
	#See if user can access this portion of the page.
	if($groupPolicy->validateAccess(1, 23) == false){
		$error = new notifySys($lang['accessdenied'], true);
		$error->genericError();
	}
	$modtitle = $lang['modcp'].' - '.$lang['movetopic'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'delete':
	#See if user can access this portion of the page.
	if($groupPolicy->validateAccess(1, 21) == false){
		$error = new notifySys($lang['accessdenied'], true);
		$error->genericError();
	}
	$modtitle = $lang['modcp'].' - '.$lang['title'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'lock':
case 'unlock':
	#See if user can access this portion of the page.
	if($groupPolicy->validateAccess(1, 22) == false){
		$error = new notifySys($lang['accessdenied'], true);
		$error->genericError();
	}
	$modtitle = $lang['modcp'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
default:
	$modtitle = $lang['modcp'];
}

$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$modtitle",
    "LANG-HELP-TITLE" => "$helpTitle",
    "LANG-HELP-BODY" => "$helpBody",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

//output top
if(($logged_user == "guest") OR ($groupAccess == 3)){
	redirect('index.php', false, 0);
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
}elseif($groupAccess == 2){
	$tpl->removeBlock("admin");
	$tpl->removeBlock("guest");
	$tpl->removeBlock("guestMenu");
	$tpl->removeBlock("login");

	#update user's activity.
	echo update_whosonline_reg($logged_user);
}
#output top template file.
echo $tpl->outputHtml();

#see if Board ID was declared, if not terminate any further outputting.
if((!isset($_GET['bid'])) or (empty($_GET['bid']))){
	$displayMsg = new notifySys($lang['nobid'], true);
	$displayMsg->displayError();
}else{
	$bid = $db->filterMySQL(var_cleanup($_GET['bid']));
}

#see if Topic ID was declared, if not terminate any further outputting.
if((!isset($_GET['tid'])) or (empty($_GET['tid']))){
	$displayMsg = new notifySys($lang['notid'], true);
	$displayMsg->displayError();
}else{
	$tid = $db->filterMySQL(var_cleanup($_GET['tid']));
}

if((!isset($_GET['pid'])) or (empty($_GET['pid']))){
	$pid = '';
}else{
	$pid = $db->filterMySQL(var_cleanup($_GET['pid']));
}

switch ($mode){
case 'viewip':
	#see if the user supplied an IP Address.
	if((!isset($_GET['ip'])) or (empty($_GET['ip']))){
		$displayMsg = new notifySys($lang['noip'], true);
		$displayMsg->displayError();
	}else{
		$ip = $db->filterMySQL(var_cleanup($_GET['ip']));
	}
	
	#see if a user added the user's name.
	if((!isset($_GET['u'])) or (empty($_GET['u']))){
		$displayMsg = new notifySys($lang['nouser'], true);
		$displayMsg->displayError();
	}else{
		$u = $db->filterMySQL(var_cleanup($_GET['u']));
	}

	//get number of users this ip matches.
	$iplist = ip_checker();

	//get other ips the poster used before.
	$ipcheck = other_ip_check();

	//output html.
	$tpl = new templateEngine($style, "mod-viewip");
	$tpl->parseTags(array(
	"BID" => "$bid",
	"TID" => "$tid",
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[modcp]",
	"LANG-IPINFO" => "$lang[ipinfo]",
	"LANG-IP" => "$lang[topicip]",
	"IP" => "$ip",
	"LANG-DNSLOOKUP" => "$lang[getdns]",
	"LANG-USERNAME" => "$lang[ipusermatch]",
	"USERNAME" => "$iplist",
	"LANG-TOTALCOUNT" => "$lang[totalcount]",
	"TOTALCOUNT" => "$ipcheck"));

	echo $tpl->outputHtml();
break;
case 'dnslookup':
	#see if the user supplied an IP Address.
	if((!isset($_GET['ip'])) or (empty($_GET['ip']))){
		$displayMsg = new notifySys($lang['noip'], true);
		$displayMsg->displayError();
	}else{
		$ip = $db->filterMySQL(var_cleanup($_GET['ip']));
	}

	#get DNS info.
	$dnslookup = gethostbyaddr($ip);	

	//output html.
	$tpl = new templateEngine($style, "mod-dnslookup");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[modcp]",
	"LANG-DNSLOOKUP" => "$lang[getdns]",
	"DNSLOOKUP" => "$dnslookup"));

	echo $tpl->outputHtml();
break;
case 'warn':
	#see if username was declared, if not terminate any further outputting.
	if((!isset($_GET['user'])) or (empty($_GET['user']))){
		$displayMsg = new notifySys($lang['nousernameentered'], true);
		$displayMsg->displayError();
	}else{
		$user = $db->filterMySQL(var_cleanup($_GET['user']));
	}

	#see if username exist on db.
	$db->SQL = "Select username, suspend_length FROM ebb_users WHERE Username='$user'";
	$user_chk = $db->affectedRows();
	$user_r = $db->fetchResults();

	if($user_chk == 0){
		$displayMsg = new notifySys($lang['usernotexist'], true);
		$displayMsg->displayError();
	}else{
		//find what usergroup this user belongs to.
		$userGroupPolicy = new groupPolicy($user);
		$userGroupRank = $userGroupPolicy->groupAccessLevel();

        #see if user is an administrator and the user setting the ban is a lower in rank.
		if(($groupAccess == 2) and ($userGroupRank == 1)){
			$displayMsg = new notifySys($lang['nocontrol'], true);
			$displayMsg->displayError();
		}

		#see if any errors were reported.
		if(isset($_SESSION['errors'])){
		    #format error(s) for the user.
			$errors = var_cleanup($_SESSION['errors']);

			#display validation message.
	        $displayMsg = new error($errors, false);
			$displayMsg->displayValidate();

			#destroy errors session data, its no longer needed.
	        unset($_SESSION['errors']);
		}

		//warning form.
		$tpl = new templateEngine($style, "mod-warnuser");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[modcp]",
		"LANG-TEXT" => "$lang[warntxt]",
		"LANG-WARNOPTION" => "$lang[warnopt]",
		"LANG-RAISEWARN" => "$lang[raisewarn]",
		"LANG-LOWERWARN" => "$lang[lowerwarn]",
		"LANG-WARNREASON" => "$lang[warnreason]",
		"LANG-SUSPENDIONLENGTH" => "$lang[suspensionlength]",
		"LANG-SUSPENDHINT" => "$lang[suspendhint]",
		"SUSPENDIONLENGTH" => "$user_r[suspend_length]",
		"LANG-CONTACTOPTION" => "$lang[contactopt]",
		"LANG-NOCONTACT" => "$lang[nocontact]",
		"LANG-PMCONTACT" => "$lang[pmcontact]",
		"LANG-EMAILCONTACT" => "$lang[email]",
		"LANG-CONTACT-TEXT" => "$lang[contacttxt]",
		"LANG-SUBMIT" => "$lang[warnuser]",
		"BID" => "$bid",
		"TID" => "$tid",
		"USER" => "$user"));

		echo $tpl->outputHtml();
  	}
break;
case 'warn_process':
	#Form values.
	$warnopt = $db->filterMySQL(var_cleanup($_POST['warnopt']);
	$reason = $db->filterMySQL(var_cleanup($_POST['reason']);
	$suspend = $db->filterMySQL(var_cleanup($_POST['suspend']);
	$contactopt = $db->filterMySQL(var_cleanup($_POST['contactopt']);
	$body = $db->filterMySQL(var_cleanup($_POST['body']));
	$user = $db->filterMySQL(var_cleanup($_POST['user']));

	#time variable for suspension.
	$time = time();

	#error check.
	if(empty($user)){
		#setup error session.
		$_SESSION['errors'] = $lang['nouser'];

		#direct user.
		redirect('manage.php?mode=warn', false, 0);
	}
	
	#get user's current warning level.
	$db->SQL = "SELECT warning_level, Email, Language FROM ebb_users WHERE Username='$user'";
	$warn_r = $db->fetchResults();
	
	//find what usergroup this user belongs to.
	$userGroupPolicy = new groupPolicy($user);
	$userGroupRank = $userGroupPolicy->groupAccessLevel();

	#see if user is an administrator and the user setting the ban is a lower in rank.
	if(($groupAccess == 2) and ($userGroupRank == 1)){
		$displayMsg = new notifySys($lang['nocontrol'], true);
		$displayMsg->displayError();
	}
	
	#see if warning level is already at the threshold point.
	if($warn_r['warning_level'] == $boardPref->getPreferenceValue("warning_threshold")){
		#setup error session.
		$_SESSION['errors'] = $lang['alreadybanned'];

		#direct user.
		redirect('manage.php?mode=warn', false, 0);
	}
	if($warnopt == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nowarn'];

		#direct user.
		redirect('manage.php?mode=warn', false, 0);
	}
	if(empty($reason)){
		#setup error session.
		$_SESSION['errors'] = $lang['noreason'];

		#direct user.
		redirect('manage.php?mode=warn', false, 0);
	}
	if(strlen($reason) > 255){
		#setup error session.
		$_SESSION['errors'] = $lang['longreason'];

		#direct user.
		redirect('manage.php?mode=warn', false, 0);
	}
	if(($contactopt != "None") and ($body == "")){
		#setup error session.
		$_SESSION['errors'] = $lang['nocontacterr'];

		#direct user.
		redirect('manage.php?mode=warn', false, 0);
	}

	#add reason of warning set to db.
	if($warnopt == 10){
		$db->SQL = "INSERT INTO ebb_warnlog (Username, Authorized, Action, Message) VALUES('$user', '$logged_user', '1', '$reason')";
		$db->query();
	}else{
		$db->SQL = "INSERT INTO ebb_warnlog (Username, Authorized, Action, Message) VALUES('$user', '$logged_user', '2', '$reason')";
		$db->query();
	}

	#update user's warning level based on form result.
	$warnAdjust = $warn_r['warning_level'] + $warnopt;
	if($warnAdjust == $boardPref->getPreferenceValue("warning_threshold")){
		#set user as banned.
		$userGroupPolicy = new groupPolicy($user);
		$userGroupRank = $userGroupPolicy->changeGroupID(4);

		#update warning level of user.
		$db->SQL = "UPDATE ebb_users SET warning_level='$warn_adjust' WHERE Username='$user'";
		$db->query();

		#log this action to warning log.
		$db->SQL = "INSERT INTO ebb_warnlog (Username, Authorized, Action, Message) VALUES('$user', '$logged_user', '3', '$reason')";
		$db->query();
	}else{
		$db->SQL = "UPDATE ebb_users SET warning_level='$warnAdjust' WHERE Username='$user'";
		$db->query();
	}

	#see if mod requested to contact user.
	if(($contactopt == "PM") or ($contactopt == "Email")){

		#load mail language file.
		require_once FULLPATH."/lang/".$warn_r['Language'].".email.php";
		require_once FULLPATH."/includes/swift/swift_required.php";

		#see what way to contact this user.
		if($contactopt == "PM"){
			//create PM Message.
			$db->SQL = "INSERT INTO ebb_pm (Sender, Reciever, Subject, Message, Date) VALUES('$title', '$user', '$contact_subject', '$body', '$time')";
			$db->query();

			#email user to alert them of the pm message.
			//get pm id.
			$db->SQL = "SELECT id FROM ebb_pm ORDER BY id DESC limit 1";
			$pm_id_result = $db->fetchResults();

			//grab values from PM message.
			$db->SQL = "SELECT Reciever, Sender, Subject, id FROM ebb_pm WHERE id='$pm_id_result[id]'";
			$pm_data = $db->fetchResults();

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
				->setTo(array($warn_r['Email'] => $pm_data['Reciever']))
				->setBody(pm_notify()); //set email body

			#create array for replacements.
			$replacements[$warn_r['Email']] = array(
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

			#see if user has to be suspended.
			if($suspend > 0){
				$db->SQL = "UPDATE ebb_users SET suspend_length='$suspend', suspend_time='$time' WHERE Username='$user'";
				$db->query();

				#log this action to warning log.
				$db->SQL = "INSERT INTO ebb_warnlog (Username, Authorized, Action, Message) VALUES('$user', '$logged_user', '4', '$reason')";
				$db->query();
			}

			#redirect back to topic.
            redirect('viewtopic.php?bid='.$bid.'&tid='.$tid, false, 0);
		}
		if($contactopt == "Email"){

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
			$message = Swift_Message::newInstance($lang['contactsubject']. $title)
				->setFrom(array($boardPref->getPreferenceValue("board_email") => $title)) //Set the From address
				->setTo(array($warn_r['Email'] => $user))
				->setBody(email_notify_warn()); //set email body

			#create array for replacements.
			$replacements[$warn_r['Email']] = array(
				'{title}'=>$title,
				'{body}'=>$body
			);

			#setup mailer template.
			$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
			$mailer->registerPlugin($decorator);

			#send message out.
			//TODO: Add a failure list to this method to help administrators weed out "dead" accounts.
			$mailer->Send($message);

			#see if user has to be suspended.
			if($suspend > 0){
				$db->SQL = "UPDATE ebb_users SET suspend_length='$suspend', suspend_time='$time' WHERE Username='$user'";
				$db->query();

				#log this action to warning log.
				$db->SQL = "INSERT INTO ebb_warnlog (Username, Authorized, Action, Message) VALUES('$user', '$logged_user', '4', '$reason')";
				$db->query();
			}

			#redirect back to topic.
            redirect('viewtopic.php?bid='.$bid.'&tid='.$tid, false, 0);
		}
	}else{
		#see if user has to be suspended.
		if($suspend > 0){
			$db->SQL = "UPDATE ebb_users SET suspend_length='$suspend', suspend_time='$time' WHERE Username='$user'";
			$db->query();

			#log this action to warning log.
			$db->SQL = "INSERT INTO ebb_warnlog (Username, Authorized, Action, Message) VALUES('$user', '$logged_user', '4', '$reason')";
			$db->query();
		}

		#redirect back to topic.
		redirect('viewtopic.php?bid='.$bid.'&tid='.$tid, false, 0);
	}
break;
case 'move':
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

	#setup board selection.
	$boardObj = new boardList();
	$boardlist = $boardObj->boardSelect();

	#load template file.
	$tpl = new templateEngine($style, "mod-movetopic");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[modcp]",
	"LANG-TEXT" => "$lang[move]",
	"TID" => "$tid",
	"BOARDLIST" => "$boardlist",
	"LANG-SUBMIT" => "$lang[movetopic]"));

	echo $tpl->outputHtml();
break;
case 'move_process':
	//process query
	$board = $db->filterMySQL(var_cleanup($_POST['board']));
	$tid = $db->filterMySQL(var_cleanup($_GET['tid']));

	#error check.
	if(empty($board)){
		#setup error session.
		$_SESSION['errors'] = $lang['noboard'];

		#direct user.
		redirect('manage.php?mode=move', false, 0);
	}
	if(empty($tid)){
		#setup error session.
		$_SESSION['errors'] = $lang['notid'];

		#direct user.
		redirect('manage.php?mode=move', false, 0);
	}

	#change board info on tables.
	$db->SQL = "SELECT bid, tid FROM ebb_topics WHERE tid='$tid'";
	$board_chk = $db->fetchResults();
	$chpost_q = $db->query();

	#see if user chose same board as the current topic location.
	if($board_chk['bid'] == $board){
		#setup error session.
		$_SESSION['errors'] = $lang['sameboard'];

		#direct user.
		redirect('manage.php?mode=move', false, 0);
	}

	#move over topics & posts to new location.
	while($r = mysql_fetch_assoc($chpost_q)){
		$db->SQL = "Update ebb_posts SET bid='$board' WHERE tid='$r[tid]'";
		$db->query();
	}
	$db->SQL = "UPDATE ebb_topics SET bid='$board' WHERE tid='$tid'";
	$db->query();

	//update last posted section of the old topic location.
	$db->SQL = "SELECT id FROM ebb_boards WHERE id='$bid'";
	$board_num = $db->affectedRows();

	if($board_num == 0){
		$db->SQL = "UPDATE ebb_boards SET last_update='' WHERE tid='$tid'";
		$db->query();
	}else{
		$db->SQL = "SELECT last_update, Posted_User, Post_Link FROM ebb_topics WHERE bid='$bid' ORDER BY last_update DESC LIMIT 1";
		$board_r = $db->fetchResults();

		//update the last_update colume for ebb_boards.
		$db->SQL = "UPDATE ebb_boards SET last_update='$board_r[last_update]', Posted_User='$board_r[Posted_User]', Post_Link='$board_r[Post_Link]'  WHERE id='$bid'";
		$db->query();
	}

	//update board of new location, if the topic is newer.
	$db->SQL = "SELECT last_update FROM ebb_boards WHERE id='$board'";
	$board_chk = $db->fetchResults();

	$db->SQL = "SELECT last_update, Posted_User, Post_Link FROM ebb_topics WHERE tid='$tid'";
	$topic_chk = $db->fetchResults();

	if($board_chk['last_update'] < $topic_chk['last_update']){
		//update the last_update colume for ebb_boards.
		$db->SQL = "UPDATE ebb_boards SET last_update='$topic_chk[last_update]', Posted_User='$topic_chk[Posted_User]', Post_Link='$topic_chk[Post_Link]'  WHERE id='$board'";
		$db->query();
	}

	//bring user back
	redirect('viewtopic.php?bid='.$board.'&tid='.$tid, false, 0);
break;
case 'delete':
	//delete polls made by topics in this board.
	$db->SQL = "DELETE FROM ebb_poll WHERE tid='$tid'";
	$db->query();

	#delete any votes.
	$db->SQL = "DELETE FROM ebb_votes WHERE tid='$tid'";
	$db->query();

	//delete read status from topics made in this board.
	$db->SQL = "DELETE FROM ebb_read WHERE Topic='$tid'";
	$db->query();

	#delete any attachments thats tied to a topic under this board.
	$db->SQL = "SELECT Filename FROM ebb_attachments WHERE tid='$tid'";
 	$attachQ = $db->query();
	$attach_chk = $db->affectedRows();

	if($attach_chk > 0){
		while($delAttach = mysql_fetch_assoc($attachQ)){
			#delete file from web space.
			@unlink (FULLPATH.'/uploads/'. $delAttach['Filename']);

			#delete entry from db.
			$db->SQL = "DELETE FROM ebb_attachments WHERE tid='$tid'";
			$db->query();
		}
	}
	//delete topic.
	$db->SQL = "DELETE FROM ebb_topics WHERE tid='$tid'";
	$db->query();

	#get post detials
	$db->SQL = "SELECT pid FROM ebb_posts WHERE tid='$tid'";
	$pid_q = $db->query();

	while($r = mysql_fetch_assoc($pid_q)){
		#delete any attachments thats tied to a post under this board.
		$db->SQL = "SELECT Filename FROM ebb_attachments WHERE pid='$r[pid]'";
  		$attachQ2 = $db->query();
		$attach_chk2 = $db->affectedRows();

		if($attach_chk2 == 1){
			while($delAttach2 = mysql_fetch_assoc($attachQ2)){
				#delete file from web space.
				@unlink (FULLPATH.'/uploads/'. $delAttach2['Filename']);

				#delete entry from db.
				$db->SQL = "DELETE FROM ebb_attachments WHERE pid='$pid'";
				$db->query();
			}
		}
	}
	//delete replies, if any.
	$db->SQL = "DELETE FROM ebb_posts WHERE tid='$tid'";
	$db->query();

	//delete any subscriptions to this topic.
	$db->SQL = "DELETE FROM ebb_topic_watch WHERE tid='$tid'";
	$db->query();

	//update last posted section.
	$db->SQL = "SELECT id FROM ebb_boards WHERE id='$bid'";
	$board_num = $db->affectedRows();

	if($board_num == 0){
		$db->SQL = "UPDATE ebb_boards SET last_update='' WHERE tid='$tid'";
		$db->query();
	}else{
		$db->SQL = "SELECT last_update, Posted_User, Post_Link FROM ebb_topics WHERE bid='$bid' ORDER BY last_update DESC LIMIT 1";
		$board_r = $db->affectedRows();

		//update the last_update colume for ebb_boards.
		$db->SQL = "UPDATE ebb_boards SET last_update='$board_r[last_update]', Posted_User='$board_r[Posted_User]', Post_Link='$board_r[Post_Link]'  WHERE id='$bid'";
		$db->query();
 	}
	//bring user back
	redirect('viewboard.php?bid='.$bid, false, 0);
break;
case 'lock':
	//process query
	$db->SQL = "UPDATE ebb_topics SET Locked='1' WHERE tid='$tid'";
	$db->query();

	//bring user back
	redirect('viewtopic.php?bid='.$bid.'&amp;tid='.$tid, false, 0);
break;
case 'unlock':
	//process query
	$db->SQL = "UPDATE ebb_topics SET Locked='0' WHERE tid='$tid'";
	$db->query();

	//bring user back
	redirect('viewtopic.php?bid='.$bid.'&amp;tid='.$tid, false, 0);
break;
default:
	redirect('index.php', false, 0);
}

#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();

ob_end_flush();
?>
