<?php
define('IN_EBB', true);
/**
Filename: stylecp.php
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
require_once FULLPATH."/includes/acp/ebbinstaller.class.php";

if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = ''; 
}
#get title.
switch($action){
case 'style_install':
	$stylecptitle = $lang['stylemenu'].' - '.$lang['styleinstaller'];
	$helpTitle = $help['addstyletitle'];
	$helpBody = $help['addstylebody'];
break;
case 'style_uninstall':
	$stylecptitle = $lang['stylemenu'].' - '.$lang['styleuninstaller'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
default:
	$stylecptitle = $lang['stylemenu'].' - '.$lang['managestyle'];
	$helpTitle = $help['stylemanagetitle'];
	$helpBody = $help['stylemanagebody'];
break;
}

#load header file.
$tpl = new templateEngine($style, "acp_header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$stylecptitle",
    "LANG-HELP-TITLE" => "$helpTitle",
    "LANG-HELP-BODY" => "$helpBody",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

#see if user has access to this portion of the script.
if($groupPolicy->validateAccess(1, 8) == false){
	$error = new notifySys($lang['noaccess'], true);
	$error->displayError();
}

//output top
$pmMsg = $userData->getNewPMCount();

$tpl = new templateEngine($style, "top-acp");
$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-WELCOME" => "$lang[welcome]",
	"LOGGEDUSER" => "$logged_user",
	"LANG-LOGOUT" => "$lang[logout]",
	"NEWPM" => "$pmMsg",
	"LANG-CP" => "$lang[admincpcp]",
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

switch($action){
case 'style_install':
	#show list of smiles installers.
	$installer = new EBBInstaller();
	$styleInstaller = $installer->acpSmileInstaller();

	$tpl = new templateEngine($style, "cp-styleinstaller");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-STYLEINSTALLER" => "$lang[styleinstaller]",
	"LANG-STYLESINSTALLERDESC" => "$lang[styleinstall]",
	"STYLEINSTALLER" => "$styleInstaller"));

	#output top template file.
	echo $tpl->outputHtml();
break;
case 'style_uninstall':
	#see if user added the Style ID or not.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$error = new notifySys($lang['nosid'], true);
		$error->displayError();
	}else{
		$id = $db->filterMySQL(var_cleanup($_GET['id']));
	}
	
	#see if any users are currently using the requested style.
	$db->SQL = "SELECT Style FROM ebb_users WHERE Style='$id'";
	$ustyleChk = $db->AffectedRows();

	if($ustyleChk > 0){
		$error = new notifySys($lang['delstylewarning'], true);
		$error->displayError();
	}

	#see if style exist.
	$db->SQL = "SELECT id FROM ebb_style WHERE id='$id'";
	$styleChk = $db->AffectedRows();

	if($styleChk == 0){
		$error = new notifySys($lang['stylenotexist'], true);
		$error->displayError();
	}else{
		//process query
		$db->SQL = "DELETE FROM ebb_style WHERE id='$id'";
		$db->query();

		#log this into our audit system.
		$acpAudit = new auditSystem();
		$acpAudit->logAction("Uninstalled Style", $acpUsr, time(), detectProxy());

		//bring user back to board section
		redirect('acp/stylecp.php', false, 0);
	}
break; 
default:
	#list installed styles.
	admin_stylelisting();
break;
}

#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();
ob_end_flush();
?>
