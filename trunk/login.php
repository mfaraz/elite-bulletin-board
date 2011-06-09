<?php
define('IN_EBB', true);
/**
Filename: login.php
Last Modified: 3/15/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";
require_once FULLPATH."/includes/validation.class.php";

//display login system.
if(isset($_GET['mode'])){
	$mode = var_cleanup($_GET['mode']);
}else{
	$mode = ''; 
}
#get page title
switch($mode){
case 'verify_acct':
	$logintitle = $lang['activationtitle'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'lostpassword':
case 'process_lostpassword':
	$logintitle = $lang['passwordrecovery'];
	$helpTitle = $help['lostpwdtitle'];
	$helpBody = $help['lostpwdbody'];
break;
case 'logout':
	$logintitle = "&nbsp;";
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
default:
	$logintitle = $lang['login'];
	$helpTitle = $help['logintitle'];
	$helpBody = $help['loginbody'];
}

#header template
$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$logintitle",
    "LANG-HELP-TITLE" => "$helpTitle",
    "LANG-HELP-BODY" => "$helpBody",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

//output top
if($logged_user != "guest"){
	$pmMsg = $userData->getNewPMCount();
}else{
	$pmMsg = '';
}

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
}else{
	$tpl->removeBlock("user");
	$tpl->removeBlock("admin");
	$tpl->removeBlock("userMenu");

	#update guest's activity.
	echo update_whosonline_guest();
}
#output top template file.
echo $tpl->outputHtml();

switch ($mode){
case 'logout':
	#see if a guest user is trying to go here for some unknown reason.
	if($logged_user == "guest"){
		redirect('index.php', false, 0);
	}
	
	#see if user is using SESSIONS or is using COOKIES.
	if(isset($_SESSION['ebb_user'])){
		#call user class.
    	$usrAuth = new login($db->filterMySQL($_SESSION['ebb_user']), $db->filterMySQL($_SESSION['ebb_pass']));

		#execute logout procedure.
		$usrAuth->logOut();

		#redirect user to index.php
	    redirect('index.php', false, 0);
	}elseif(isset($_COOKIE['ebbuser'])){
		#call user class.
    	$usrAuth = new login($db->filterMySQL($_COOKIE['ebbuser']), $db->filterMySQL($_COOKIE['ebbpass']));

		#execute logout procedure.
		$usrAuth->logOut();

		#redirect user to index.php
	    redirect('index.php', false, 0);
    }else{
        $displayMsg = new notifySys("INVALID LOGOUT METHOD!", true);
		$displayMsg->displayError();
    }
break;
case 'verify_acct':
	#see if activation key is missing.
	if((!isset($_GET['key'])) or (empty($_GET['key']))){
        $displayMsg = new notifySys($lang['noacctkey'], true);
		$displayMsg->displayError();
	}else{
		$key = $db->filterMySQL($_GET['key']);
	}
	
	#see if username is missing.
	if((!isset($_GET['u'])) or (empty($_GET['u']))){
        $displayMsg = new notifySys($lang['nouser'], true);
		$displayMsg->displayError();
	}else{
		$u = $db->filterMySQL(var_cleanup($_GET['u']));
	}
	
	//check for correct key code & username.
	$db->SQL = "SELECT id FROM ebb_users WHERE Username='$u' AND act_key='$key' LIMIT 1";
	$acctChk = $db->affectedRows();

	if($acctChk == 1){
		#set user as active.
		$db->SQL = "UPDATE ebb_users SET active='1' WHERE Username='$u' LIMIT 1";
		$db->query();
		
		#display message.
        $displayMsg = new notifySys($lang['correctinfo'], true);
		$displayMsg->displayMessage();
	}else{
        $displayMsg = new notifySys($lang['incorrectinfo'], true);
		$displayMsg->displayError();
	}
break;
case 'lostpassword':
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
	#login template.
	$tpl = new templateEngine($style, "lostpassword");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[passwordrecovery]",
	"LANG-NOUSERNAME" => "$lang[nouser]",
	"LANG-INVALIDEMAIL" => "$lang[invalidemail]",
	"LANG-USERNAME" => "$lang[username]",
	"LANG-REGISTER" => "$lang[reg]",
	"LANG-EMAIL" => "$lang[email]",
	"LANG-GETPASS" => "$lang[getpassword]"));

	echo $tpl->outputHtml();
  break;
  case 'process_lostpassword':
	$lost_user = $db->filterMySQL(var_cleanup($_POST['lost_user']));
	$lost_email = $db->filterMySQL(var_cleanup($_POST['lost_email']));
	$IP = detectProxy();
	
	#call validation class.
	$validate = new validation();
	
	#generate a new user verification key.
	$newActKey = md5(makeRandomPassword());

	#validation.
	if($validate->validateAlphaNumeric($lost_user) == false){
		#setup error session.
		$_SESSION['errors'] = $lang['invaliduser'];

		#direct user.
		redirect('login.php?mode=lostpassword', false, 0);
	}
	if($validate->validateEmail($lost_email) == false){
		#setup error session.
		$_SESSION['errors'] = $lang['invalidemail'];

		#direct user.
		redirect('login.php?mode=lostpassword', false, 0);
	}
	#check against the database to see if the username match.
	$db->SQL = "SELECT id FROM ebb_users WHERE Username='".$lost_user."' LIMIT 1";
	$validateUsr = $db->affectedRows();
	
	if($validateUsr == 0){
		#setup error session.
		$_SESSION['errors'] = $lang['invaliduser'];

		#direct user.
		redirect('login.php?mode=lostpassword', false, 0);
	}
	#END

	#call user class.
	$getUserData = new user($lost_user);
	
	//see if the email matches the one on record.
	if ($lost_email !== $getUserData->userSettings("Email")){
		#setup error session.
		$_SESSION['errors'] = $lang['noemailmatch'];

		#direct user.
		redirect('login.php?mode=lostpassword', false, 0);
	}
	#see if any errors occured and if so report it.
	#generate new password.
	$new_pwd = makeRandomPassword();

	#generate a new password.
	$encryptPwd = sha1($new_pwd.$getUserData->getPasswordSalt());

	#change password.
	$db->SQL = "UPDATE ebb_users SET Password='$encryptPwd', failed_attempts='0', active='0', act_key='$newActKey' WHERE Username='$lost_user' LIMIT 1";
	$db->query();

	#send out email.
	require_once FULLPATH."/lang/".$lng.".email.php";
	require_once FULLPATH."/includes/swift/swift_required.php";

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
	$message = Swift_Message::newInstance($lang['passwordrecovery'])
		->setFrom(array($boardPref->getPreferenceValue("board_email") => $title)) //Set the From address
		->setTo(array($getUserData->userSettings("Email") => $getUserData->userSettings("Username")))
		->setBody(pwd_reset()); //set email body

	#create array for replacements.
	$replacements[$email] = array(
		'{title}'=>$title,
		'{boardAddr}'=>$boardAddr,
		'{username}'=>$lost_user,
		'{key}'=>$newActKey,
		'{new-pwd}' => $new_pwd
	);

	#setup mailer template.
	$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
	$mailer->registerPlugin($decorator);

	#send message out.
	//TODO: Add a failure list to this method to help administrators weed out "dead" accounts.
	$mailer->Send($message);

	#display message.
	$displayMsg = new notifySys($lang['emailsent'], false);
	$displayMsg->displayMessage();
break;
default:
	if ((!isset($_COOKIE['ebbuser'])) OR (!isset($_SESSION['ebb_user']))){
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

		#login template.
        $tpl = new templateEngine($style, "login");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[login]",
		"LANG-USERNAME" => "$lang[username]",
		"LANG-REGISTER" => "$lang[reg]",
		"LANG-PASSWORD" => "$lang[pass]",
		"LANG-FORGOT" => "$lang[forgot]",
		"LANG-REMEMBER" => "$lang[rememberlogin]",
		"LANG-REMEMBERTXT" => "$lang[remembertxt]",
		"LANG-LOGIN" => "$lang[login]"));

		echo $tpl->outputHtml();
	}else{
		$error = new notifySys($lang['alreadylogged'], true);
		$error->displayMessage();
	}
}
#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();

ob_end_flush();
?>
