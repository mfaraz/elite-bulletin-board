<?php
define('IN_EBB', true);
/**
Filename: index.php
Last Modified: 6/25/2011

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
require_once FULLPATH."/includes/acp/versionChecker.class.php";

if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = ''; 
}

switch($action){
case 'info':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 9) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$acptitle = $lang['php_info'];
	$helpTitle = $help['phpinfotitle'];
	$helpBody = $help['phpinfobody'];
break;
default:
	$acptitle = $lang['admincp'];
	$helpTitle = $help['acptitle'];
	$helpBody = $help['acpbody'];
break;
}

$tpl = new templateEngine($style, "acp_header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$acptitle",
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
case 'info':
	ob_start();
	phpinfo();
	$string = ob_get_contents();
	$string = strchr($string, '</style>');
	$string = str_replace('</style>','',$string);
	$string = str_replace('class="p"','',$string);
	$string = str_replace('class="e"','class="td2"',$string);
	$string = str_replace('class="v"','class="td1"',$string);
	$string = str_replace('class="h"','class="td1"',$string);
	$string = str_replace('class="center"','',$string);
	ob_end_clean();

	#output html.
	$tpl = new templateEngine($style, "cp-phpinfo");
	$tpl->parseTags(array(
  	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-PHPINFO" => "$lang[php_info]",
	"LANG-TEXT" => "$lang[phpinfo_detail]",
	"PHPINFO" => "$string"));
	echo $tpl->outputHtml();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Viewed PHP Info Page", $acpUsr, time(), detectProxy());
	ob_end_flush();
break;
default:
	#display ACP header.
	$tpl = new templateEngine($style, "cp-mainmenu");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-BOARDMENU" => "$lang[boardmenu]",
	"LANG-USERMENU" => "$lang[usermenu]",
	"LANG-GENERALMENU" => "$lang[generalmenu]",
	"LANG-GROUPMENU" => "$lang[groupmenu]",
	"LANG-STYLEMENU" => "$lang[stylemenu]",
	"LANG-SETTINGS" => "$lang[settings]",
	"LANG-MANAGE" => "$lang[manage]",
	"LANG-BOARDSETUP" => "$lang[boardsetup]",
	"LANG-SMILES" => "$lang[smiles]",
	"LANG-NEWSLETTER" => "$lang[newsletter]",
	"LANG-USERSETTINGS" => "$lang[usersettings]",
	"LANG-BOARDSETTINGS" => "$lang[boardsettings]",
	"LANG-ANNOUNCEMENTSETTINGS" =>"$lang[announcementsettings]",
	"LANG-MAILSETTINGS" => "$lang[mailsettings]",
	"LANG-COOKIESETTINGS" => "$lang[cookiesettings]",
	"LANG-ATTACHMENTSETTINGS" => "$lang[attachmentsettings]",
	"LANG-GROUPSETUP" => "$lang[groupsetup]",
	"LANG-BAN" => "$lang[banlist]",
	"LANG-BLACKLISTUSERS" => "$lang[blacklist]",
	"LANG-ACTIVATE" => "$lang[activateacct]",
	"LANG-USERWARN" => "$lang[warninglist]",
	"LANG-CENSOR" => "$lang[censor]",
	"LANG-PRUNE" => "$lang[prune]",
	"LANG-USERPRUNE" => "$lang[userprune]"));
	echo $tpl->outputHtml();

	#check permissions.	
	if($groupPolicy->validateAccess(1, 10) == true){
		#call version Checker Class.
		$versionDetails = new versionChecker();
		
		#see if version is up to date.
		if ($versionDetails->verifyVersion() == null){
			$versionChk = $lang['updateerr'];
		}elseif($versionDetails->verifyVersion()){
		    $versionChk = $lang['verok'];
		}else{
		    $versionChk = $lang['verold'];
		}

		#output.
		$tpl = new templateEngine($style, "cp-updatechk");
		$tpl->parseTags(array(
		"LANG-VERSIONDETAIL" => "$lang[verdetails]",
		"LANG-CHECKVERSION" => "$versionChk",
		"LANG-VERSIONDETAILS" => "$lang[versiondetails]",
		"LANG-MODINSTALLER" => "$lang[modlist]",
		"LANG-REPORTBUGS" => "$lang[reportbugs]",
		"LANG-INFO" => "$lang[helpimprove]"));
		echo $tpl->outputHtml();
	}
	#see if user can see the server information.
	if($groupPolicy->validateAccess(1, 9) == true){
		#get php version.
		$phpVer = phpversion();
		#get mysql version.
		$mysqlVer = $db->dbVersion();

		#output.
		$tpl = new templateEngine($style, "cp-serverinformation");
		$tpl->parseTags(array(
		"LANG-SERVERINFO" => "$lang[server_info]",
		"LANG-PHPVERSION" => "$lang[php_ver]",
		"PHPVERSION" => "$phpVer",
		"LANG-MYSQLVERSION" => "$lang[mysql_ver]",
		"MYSQLVERSION" => "$mysqlVer",
		"LANG-PHPINFO" => "$lang[php_info]"));
		echo $tpl->outputHtml();
	}
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 11) == true){
		#load admin log.
		$auditSys = new auditSystem();
		$acpAuditLog = $auditSys->viewAuditLog();

		#output.
		$tpl = new templateEngine($style, "cp-lastlogactions");
		$tpl->parseTags(array(
		"LANG-ACPLOG" => "$lang[acp_log]",
		"ACPLOG" => "$acpAuditLog",
		"LANG-VIEWLOG" => "$lang[acp_full]"));
		echo $tpl->outputHtml();
	}
break;
}
#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();
ob_end_flush();
?>
