<?php
define('IN_EBB', true);
/**
Filename: acp_login.php
Last Modified: 06/28/2012

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

$tpl = new templateEngine($style, "acp_header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$lang[admincp]",
    "LANG-HELP-TITLE" => "$help[acplogintitle]",
    "LANG-HELP-BODY" => "$help[acploginbody]",
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

#see if user confirm login.
if (isset($_COOKIE['ebbacpu']) and (isset($_COOKIE['ebbacpp']))){
	#go to login page.
    redirect('index.php', false, 0);
}
//display admin CP
if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = ''; 
}
switch ( $action ){
case 'auth':
	//process login.
	$username = $db->filterMySQL(var_cleanup($_POST['username']));
	$password = $db->filterMySQL(var_cleanup($_POST['password']));
	$sessionLength = $db->filterMySQL(var_cleanup($_POST['sessionlength']));
	if((empty($username)) OR (empty($password))){
		#setup error session.
		$_SESSION['errors'] = $lang['blank'];

		#direct user.
		redirect('acp/acp_login.php', false, 0);
	}else if(empty($sessionLength) || $sessionLength == 0){
		#setup error session.
		$_SESSION['errors'] = $lang['nosession'];

		#direct user.
		redirect('acp/acp_login.php', false, 0);
	}else if(!is_numeric($sessionLength)){
		#setup error session.
		$_SESSION['errors'] = $lang['invalidsession'];

		#direct user.
		redirect('acp/acp_login.php', false, 0);
	}else if(strlen($sessionLength) > 1){
		#setup error session.
		$_SESSION['errors'] = $lang['sessiontoolong'];

		#direct user.
		redirect('acp/acp_login.php', false, 0);
	}else{
		#see if username inputted matches the username to the logged in user.
		if($username !== $logged_user){
			#log this into our audit system.
			$acpAudit = new auditSystem();
			$acpAudit->logAction("User tried to login with another user\'s name($username)", $logged_user, time(), detectProxy());
			redirect($boardAddr.'/index.php', false, 0);
		}
		
		#Call up login class.
        $usrAuth = new login($username, $password);
        
        #see if login was found valid.
		if($usrAuth->validateAdministrator() == true){
            #see if user is inactive.
			if($usrAuth->isActive() == false){
				#setup error session.
				$_SESSION['errors'] = $lang['inactiveuser'];

				#direct user.
				redirect('acp/acp_login.php', false, 0);
			}else{
				#clear any failed login attempts from their record.
				$usrAuth->clearFailedLogin();

				#setup cookie or session(based on user's preference.
				$usrAuth->acpLogOn();

				#regenerate Session Data.
				$usrAuth->regenerateSession();

				#log this into our audit system.
				$acpAudit = new auditSystem();
				$acpAudit->logAction("Logged into Administration Panel", $logged_user, time(), detectProxy());

				#direct user to main menu.
   				redirect('acp/index.php', false, 0);
			}
		}else{
        	#get current failed login count
			if($usrAuth->getFailedLoginCt() == 5){
			    #deactivate the user's account(for their safety).
				$usrAuth->deactivateUser();

			    #alert user of reaching their limit of incorrect login attempts.
				$_SESSION['errors'] = $lang['lockeduser'];

				#direct user.
				redirect('acp/acp_login.php', false, 0);
			}else{
			    #add to failed login count.
				$usrAuth->setFailedLogin();

				#setup error session.
				$_SESSION['errors'] = $lang['nomatch'];

				#direct user.
				redirect('acp/acp_login.php', false, 0);
			}
		}
	}
break;
default:
		#see if any errors were reported by processing action.
		if(isset($_SESSION['errors'])){
		    #format error(s) for the user.
			$errors = var_cleanup($_SESSION['errors']);

			#display validation message.
            $displayMsg = new notifySys($errors, false);
			$displayMsg->displayValidate();

			#destroy errors session data, its no longer needed.
            unset($_SESSION['errors']);
		}

		#display login form.
        $tpl = new templateEngine($style, "cp-login");
		$tpl->parseTags(array(
	 	  "TITLE" => "$title",
		  "LANG-TITLE" => "$lang[login]",
		  "LANG-USERNAME" => "$lang[username]",
		  "USERNAME" => "$logged_user",
		  "LANG-PASSWORD" => "$lang[pass]",
		  "LANG-SESSION" => "$lang[sessionlength]",
		  "LANG-LOGIN" => "$lang[login]"));
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
