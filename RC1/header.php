<?php
session_start();
ob_start();
if (!defined('IN_EBB')) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: header.php
Last Modified: 06/28/2012

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

//see if we're to redirect to installer, also see if the installer exists.
if (!defined('EBBINSTALLED')) {
	#see if installer files exist.
	if(!file_exists('install/install.php')){
		die('Installer Not Found! Make sure all files were uploaded correctly.');
	}
	#load installer.
    header('Location: install/index.php');
    exit;
}

#load functions
require_once FULLPATH."/includes/function.php";

#consider placing this in where its needed, dont need this set of function 100% of the time.
require_once FULLPATH."/includes/posting_function.php";
require_once FULLPATH."/includes/user_function.php";

#load up libraries.
require_once FULLPATH."/includes/loginmgr.class.php";
require_once FULLPATH."/includes/preference.class.php";
require_once FULLPATH."/includes/notifySys.php";
require_once FULLPATH."/includes/MySQL.php";
require_once FULLPATH."/includes/templateEngine.php";
require_once FULLPATH."/includes/groupPolicy.class.php";
require_once FULLPATH."/includes/user.class.php";

#call up the db class.
$db = new dbMySQL();

#call up preference class.
$boardPref = new preference();

#user check
if ((isset($_COOKIE['ebbuser']) && ($_COOKIE['ebbpass'])) OR (isset($_SESSION['ebb_user'])) && ($_SESSION['ebb_pass'])){
	#get username value.
	if(isset($_SESSION['ebb_user'])){
		$logged_user = $db->filterMySQL(var_cleanup($_SESSION['ebb_user']));
		$chkpwd = $db->filterMySQL(var_cleanup($_SESSION['ebb_pass']));
	}elseif(isset($_COOKIE['ebbuser'])){
		$logged_user = $db->filterMySQL(var_cleanup($_COOKIE['ebbuser']));
		$chkpwd = $db->filterMySQL(var_cleanup($_COOKIE['ebbpass']));
	}else{
   		$error = new notifySys("INVALID LOGIN METHOD!", true, true, __FILE__, __LINE__);
		$error->genericError();
	}
	
	#start-up login checker.
	$userAuth = new login($logged_user, $chkpwd);

	if($userAuth->validateLoginSession()){
		#perform session credibility check & refresh session ID.
		$userAuth->validateSession();

		#validate & setup group policy.
		$groupPolicy = new groupPolicy($logged_user);
		$groupAccess = $groupPolicy->groupAccessLevel(); //old var was $access_level
		$groupProfile = $groupPolicy->getGroupProfile(); //old var was $permission_type
		
		#call user function.
        $userData = new user($logged_user);

		#set-up vars
		$style = $userData->userSettings("Style"); // old var was $template.
		$timeFormat = $userData->userSettings("Time_format");
		$lng = $userData->userSettings("Language");
		$gmt = $userData->userSettings("Time_Zone");
		$last_visit = $userData->userSettings("last_visit");
		$suspend_length = $userData->userSettings("suspend_length");
		$suspend_date = $userData->userSettings("suspend_time");

		#perform a range of ban checks.
        $userAuth->checkBan();
	}else{
		$error = new notifySys("INVALID COOKIE OR SESSION!", true, true, __FILE__, __LINE__);
		$error->genericError();
	}
}else{
	$logged_user = "guest";
	$groupPolicy = new groupPolicy($logged_user);
	$groupAccess = 0;
	$groupProfile = 0;
	#set-up vars
    $style = $boardPref->getPreferenceValue("default_style"); // old var was $template.
	$lng = $boardPref->getPreferenceValue("default_language");
	$timeFormat = $boardPref->getPreferenceValue("timeformat"); //old var was $time_format.
	$gmt = $boardPref->getPreferenceValue("timezone");
}
#settings values used throughout the entire application.
$title = $boardPref->getPreferenceValue("board_name");
$address = $boardPref->getPreferenceValue("website_url");
$boardAddr = $boardPref->getPreferenceValue("board_url");

#see if board directory is at the root.
if($boardPref->getPreferenceValue("board_directory") == "/"){
	$boardDir = '';
}else{
	$boardDir = $boardPref->getPreferenceValue("board_directory");
}
#$boardStatus = $boardPref->getPreferenceValue("board_status"); //old var was $board_status.

#language loading
require_once FULLPATH."/lang/".$lng.".lang.php";
require_once FULLPATH."/lang/".$lng.".help.php";

//check to see if the install file is stil on the user's server.
if (checkInstall() == 1){
	if ($groupAccess == 1){
		$error = new notifySys($lang['installadmin'], true);
		$error->displayError();
	}else{
		$error = new notifySys($lang['install'], true);
  		exit($error->displayMessage());
	}
}

//check to see if the board is on or off.
if ($boardPref->getPreferenceValue("board_status") == 0){
	$offMsg = nl2br($boardPref->getPreferenceValue("offline_msg"));
	if ($groupAccess == 1){
		$offMsg .= '<p>[<a href="'.$boardAddr.'/acp/index.php">'.$lang['cp'].'</a>]</p>';
	}else{
		$offMsg .= '<p>[<a href="'.$boardAddr.'/login.php">'.$lang['login'].'</a>]</p>';
	}
	$error = new notifySys($offMsg, true);
  	exit($error->displayMessage());
}
?>
