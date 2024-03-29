<?php
define('IN_EBB', true);
/**
Filename: Profile.php
Last Modified: 6/24/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";
include_once FULLPATH."/includes/attachmentMgr.php";

#move guest to main page.
if($logged_user == "guest"){
	header("Location: index.php");
}

if($groupPolicy->validateAccess(1, 31) == false){
	$displayMsg = new notifySys($lang['accessdenied'], false);
	$displayMsg->genericError();
}
$pagetitle = $lang['profile']. " - " . $lang['viewprofile'];
$helpTitle = $help['nohelptitle'];
$helpBody = $help['nohelpbody'];

$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$pagetitle",
    "LANG-HELP-TITLE" => "$helpTitle",
    "LANG-HELP-BODY" => "$helpBody",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

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

#parse html.
$tpl = new templateEngine($style, "profile");
$tpl->parseTags(array(
"TITLE" => "$title",
"LANG-TITLE" => "$lang[profile]",
"LANG-ACCTMENU" => "$lang[accountmenu]",
"LANG-PROFILEMENU" => "$lang[profilemenu]",
"LANG-PMMENU" => "$lang[pmmenu]",
"LANG-NOTESMENU" => "$lang[notesmenu]",
"LANG-PRIVACYMENU" => "$lang[privacymenu]",
"LANG-EDITPROFILE" => "$lang[editinfo]",
"LANG-EDITSIG" => "$lang[editsig]",
"LANG-AVATARSETTINGS" => "$lang[avatarsetting]",
"LANG-UPDATEEMAIL" => "$lang[emailupdate]",
"LANG-CHANGEPASSWORD" => "$lang[changepassword]",
"LANG-MANAGEGROUPS" => "$lang[managegroups]",
"LANG-MANAGEATTACHMENTS" => "$lang[manageattach]",
"LANG-MANAGESUBSCRIPTIONS" => "$lang[subscriptionsetting]",
"LANG-NEWPM" => "$lang[PostPM]",
"LANG-PM-INBOX" => "$lang[inbox]",
"NEW-PM-COUNT" => $userData->getNewPMCount(true),
"LANG-PM-OUTBOX" => "$lang[outbox]",
"LANG-PM-ARCHIVE" => "$lang[archive]",
"LANG-PM-RELATIONSHIP" => "",
"LANG-NEWNOTES" => "$lang[newnote]",
"LANG-MANAGENOTES" => "$lang[managenotes]",
"LANG-NOTESSETTINGS" => "$lang[notessettings]",
"LANG-FRIENDSLIST" => "$lang[friendslist]",
"PROFILESETTINGS" => "$lang[profilesettings]",
"LANG-OPTION" => "$lang[profilemenu]"));

echo $tpl->outputHtml();

#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();

ob_end_flush();
?>
