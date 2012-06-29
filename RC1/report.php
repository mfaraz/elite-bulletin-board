<?php
define('IN_EBB', true);
/**
Filename: report.php
Last Modified: 06/28/2012

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";
require_once FULLPATH."/includes/swift/swift_required.php";

$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$lang[report2mod]",
    "LANG-HELP-TITLE" => "$help[reporttitle]",
    "LANG-HELP-BODY" => "$help[reportbody]",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

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

//display search
if(isset($_GET['mode'])){
	$mode = var_cleanup($_GET['mode']);
}else{
	$mode = ''; 
}
switch($mode){
case 'topic':
	#see if Topic ID was declared, if not terminate any further outputting.
	if((!isset($_GET['tid'])) or (empty($_GET['tid']))){
		$displayMsg = new notifySys($lang['notid'], true);
		$displayMsg->displayError();
	}else{
		$tid = $db->filterMySQL(var_cleanup($_GET['tid']));
	}

	//check to see if topic exists or not and if it doesn't kill the program
	$db->SQL = "SELECT tid FROM ebb_topics WHERE tid='$tid'";
	$checktopic = $db->affectedRows();

	if ($checktopic == 0){
		$displayMsg = new notifySys($viewtopic['doesntexist'], true);
		$displayMsg->displayError();
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


	$tpl = new templateEngine($style, "report-topic");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[report2mod]",
	"LANG-TEXT" => "$lang[topicreporttxt]",
	"TID" => "$tid",
	"LANG-REPORTEDBY" => "$lang[Reportedby]",
	"USERNAME" => "$logged_user",
	"LANG-REASON" => "$lang[reason]",
	"LANG-SPAMPOST" => "$lang[spampost]",
	"LANG-FIGHTPOST" => "$lang[fightpost]",
	"LANG-ADVERT" => "$lang[advert]",
	"LANG-USERPROBLEMS" => "$lang[userproblems]",
	"LANG-OTHER" => "$lang[other]",
	"LANG-MESSAGE" => "$lang[message]",
	"LANG-SUBMIT" => "$lang[submit]"));

	echo $tpl->outputHtml();
break;
case 'report_topic':
	#see if Topic ID was declared, if not terminate any further outputting.
	if((!isset($_GET['tid'])) or (empty($_GET['tid']))){
		$displayMsg = new notifySys($lang['notid'], true);
		$displayMsg->displayError();
	}else{
		$tid = $db->filterMySQL(var_cleanup($_GET['tid']));
	}

	#load mail language file.
	require_once FULLPATH."/lang/".$lng.".email.php";

	$reason = $db->filterMySQL(var_cleanup($_POST['reason']));
	$msg = $db->filterMySQL(var_cleanup($_POST['msg']));

	#error check.
	if(empty($reason)){
		#setup error session.
		$_SESSION['errors'] = $lang['noreason'];

		#direct user.
		redirect('report.php?mode=topic', false, 0);
	}
	if(empty($msg)){
		#setup error session.
		$_SESSION['errors'] = $lang['nomsg'];

		#direct user.
		redirect('report.php?mode=topic', false, 0);
	}

	#get board ID.
	//TODO restructure setup so that bid is no longer needed.
	$db->SQL = "SELECT bid FROM ebb_topics WHERE tid='$tid'";
	$t = $db->fetchResults();

	#get users that have an admin or moderator status assigned to them.
	$db->SQL = "SELECT gu.Username FROM ebb_group_users gu LEFT JOIN ebb_groups g ON gu.gid = g.id WHERE g.Level = '1' OR g.Level = '2'";
	$u = $db->query();

	#see what kind of transport to use.
	if($boardPref->getPreferenceValue("mail_type") == 0){

		#see if we're using some form of encryption.
		if ($boardPref->getPreferenceValue("smtp_encryption") == ""){
			//Create the Transport
			$transport = Swift_SmtpTransport::newInstance($boardPref->getPreferenceValue("smtp_host"), $boardPref->getPreferenceValue("smtp_port"))
			  ->setUsername($boardPref->getPreferenceValue("smtp_user"))
			  ->setPassword($boardPref->getPreferenceValue("smtp_pwd"));
		} else{
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
	$message = Swift_Message::newInstance($lang['reportsubject'])
		->setFrom(array($boardPref->getPreferenceValue("board_email") => $title)) //Set the From address		
		->setBody(report_topic()); //set email body

	//setup anti-flood plugin.
	$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(100, $boardPref->getPreferenceValue("mail_antiflood")));

	#get user's profile
	while($r = mysql_fetch_assoc($u)){
		$db->SQL = "SELECT Email FROM ebb_users WHERE Username='$r[Username]'";
		$usr = $db->fetchResults();

		#create array for replacements.
		$replacements[$usr['Email']] = array(
			'{reason}'=>$reason,
			'{msg}'=>$msg,
			'{boardurl}'=>$boardAddr,
			'{bid}'=>$t['bid'],
			'{tid}'=>$tid
		);

		//Set the To addresses
		$message->setTo(array($usr['Email'] => $r['Username']));
		
		#setup mailer template.
		$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
		$mailer->registerPlugin($decorator);

		#send message out.
		//TODO: Add a failure list to this method to help administrators weed out "dead" accounts.
		$mailer->send($message);
	}
	
	
	
	#display thank you message.
	$displayMsg = new notifySys($lang['reportsent'], true);
	$displayMsg->displayMessage();

	#redirect user to index.php, delay it 10 seconds so user can see confirm message..
    redirect('index.php', true, 10);
break;
case 'post':
	#see if postID is declared, if not alert the user.
	if((!isset($_GET['pid'])) or (empty($_GET['pid']))){
		$displayMsg = new notifySys($lang['nopid'], true);
		$displayMsg->displayError();
	}else{
		$pid = $db->filterMySQL(var_cleanup($_GET['pid']));
	}

	//check to see if topic exists or not and if it doesn't kill the program
	$db->SQL = "select pid FROM ebb_posts WHERE pid='$pid'";
	$checktopic = $db->affectedRows();

	if ($checktopic == 0){
		$displayMsg = new notifySys($lang['doesntexist'], true);
		$displayMsg->displayError();
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

	$tpl = new templateEngine($style, "report-post");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[report2mod]",
	"LANG-TEXT" => "$lang[topicreporttxt]",
	"PID" => "$pid",
	"LANG-REPORTEDBY" => "$lang[Reportedby]",
	"USERNAME" => "$logged_user",
	"LANG-REASON" => "$lang[reason]",
	"LANG-SPAMPOST" => "$lang[spampost]",
	"LANG-FIGHTPOST" => "$lang[fightpost]",
	"LANG-ADVERT" => "$lang[advert]",
	"LANG-USERPROBLEMS" => "$lang[userproblems]",
	"LANG-OTHER" => "$lang[other]",
	"LANG-MESSAGE" => "$lang[message]",
	"LANG-SUBMIT" => "$lang[submit]"));

	echo $tpl->outputHtml();
break;
case 'report_post':
	if((!isset($_GET['pid'])) or (empty($_GET['pid']))){
		$displayMsg = new notifySys($lang['nopid'], true);
		$displayMsg->displayError();
	}else{
		$pid = $db->filterMySQL(var_cleanup($_GET['pid']));
	}

	#load mail language file.
	require_once FULLPATH."/lang/".$lng.".email.php";

	#get form values.
	$reason = $db->filterMySQL(var_cleanup($_POST['reason']));
	$msg = $db->filterMySQL(var_cleanup($_POST['msg']));

	#error check.
	if(empty($reason)){
		#setup error session.
		$_SESSION['errors'] = $lang['noreason'];

		#direct user.
		redirect('report.php?mode=post', false, 0);
	}
	if(empty($msg)){
		#setup error session.
		$_SESSION['errors'] = $lang['nomsg'];

		#direct user.
		redirect('report.php?mode=post', false, 0);
	}
	
	#get info on moderators to send out email.
	$db->SQL = "SELECT tid FROM ebb_posts WHERE pid='$pid'";
	$p = $db->fetchResults();

	$db->SQL = "SELECT bid FROM ebb_topics WHERE tid='$p[tid]'";
	$t = $db->fetchResults();

	#get users that have an admin or moderator status assigned to them.
	$db->SQL = "SELECT gu.Username FROM ebb_group_users gu LEFT JOIN ebb_groups g ON gu.gid = g.id WHERE g.Level = '1' OR g.Level = '2'";
	$u = $db->query();

	#see what kind of transport to use.
	if($boardPref->getPreferenceValue("mail_type") == 0){
		#see if we're using some form of encryption.
		if ($boardPref->getPreferenceValue("smtp_encryption") == ""){
			//Create the Transport
			$transport = Swift_SmtpTransport::newInstance($boardPref->getPreferenceValue("smtp_host"), $boardPref->getPreferenceValue("smtp_port"))
			  ->setUsername($boardPref->getPreferenceValue("smtp_user"))
			  ->setPassword($boardPref->getPreferenceValue("smtp_pwd"));
		} else{
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
	$message = Swift_Message::newInstance($lang['reportsubject'])
		->setFrom(array($boardPref->getPreferenceValue("board_email") => $title)) //Set the From address
		->setBody(report_post()); //set email body

	//setup anti-flood plugin.
	$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(100, $boardPref->getPreferenceValue("mail_antiflood")));	

	#get user's profile
	while($r = mysql_fetch_assoc($u)){
		$db->SQL = "SELECT Email FROM ebb_users WHERE Username='$r[Username]'";
		$usr = $db->fetchResults();

		#create array for replacements.
		$replacements[$usr['Email']] = array(
			'{reason}'=>$reason,
			'{msg}'=>$msg,
			'{boardurl}'=>$boardAddr,
			'{bid}'=>$t['bid'],
			'{tid}'=>$tid,
			'{pid}'=>$pid
		);

		//Set the To addresses
		$message->setTo(array($usr['Email'] => $r['Username']));
		
		#setup mailer template.
		$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
		$mailer->registerPlugin($decorator);

		#send message out.
		//TODO: Add a failure list to this method to help administrators weed out "dead" accounts.
		$mailer->send($message);
	}

	#setup mailer template.
	$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
	$mailer->registerPlugin($decorator);

	#send message out.
	$mailer->batchSend($message);

	#display thank you message.
	$displayMsg = new notifySys($lang['reportsent'], true);
	$displayMsg->displayMessage();

	#redirect user to index.php, delay it 10 seconds so user can see confirm message..
    redirect('index.php', true, 10);
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
