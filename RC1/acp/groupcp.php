<?php
define('IN_EBB', true);
/**
Filename: groupcp.php
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
require_once FULLPATH."/includes/acp/groupAdministration.class.php";

if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = ''; 
}
#get title.
switch($action){
case 'new_group':
case 'new_group_process':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 3) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$groupcptitle = $lang['groupmenu'].' - '.$lang['addgroup'];
	$helpTitle = $help['newgrouptitle'];
	$helpBody = $help['newgroupbody'];
break;
case 'group_modify':
case 'modify_group_process':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 3) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$groupcptitle = $lang['groupmenu'].' - '.$lang['modifygroup'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'group_delete':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 3) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$groupcptitle = $lang['groupmenu'].' - '.$lang['delete'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'group_memberlist':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 3) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$groupcptitle = $lang['groupmenu'].' - '.$lang['modifygroup'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'groupmember_remove':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 3) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$groupcptitle = $lang['groupmenu'].' - '.$lang['removefromgroup'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'grouppermission':
case 'grouprights':
case 'grouprights_process':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 3) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$groupcptitle = $lang['groupmenu'].' - '.$lang['grouppermission'];
	$helpTitle = $help['grouprightstitle'];
	$helpBody = $help['grouprightsbody'];
break;
case 'pendinglist':
case 'pendingview':
case 'pending_stat':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 3) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$groupcptitle = $lang['groupmenu'].' - '.$lang['pendinglist'];
	$helpTitle = $help['pendinglisttitle'];
	$helpBody = $help['pendinglistbody'];
break;
case 'group_adduser':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 3) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$groupcptitle = $lang['groupmenu'].' - '.$lang['addtogroup'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'manageprofile':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 3) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$groupcptitle = $lang['groupmenu'].' - '.$lang['manageprofile'];
	$helpTitle = $help['groupprofiletitle'];
	$helpBody = $help['groupprofilebody'];
break;
case 'new_profile':
case 'new_profile_process':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 3) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$groupcptitle = $lang['groupmenu'].' - '.$lang['newprofile'];
	$helpTitle = $help['groupprofiletitle'];
	$helpBody = $help['groupprofilebody'];
break;
case 'profile_modify':
case 'profile_modify_process':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 3) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$groupcptitle = $lang['groupmenu'].' - '.$lang['modifyprofile'];
	$helpTitle = $help['groupprofiletitle'];
	$helpBody = $help['groupprofilebody'];
break;
case 'profile_delete':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 3) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$groupcptitle = $lang['groupmenu'].' - '.$lang['deleteprofile'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
default:
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 3) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$groupcptitle = $lang['groupmenu'].' - '.$lang['groupsetup'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
}

$tpl = new templateEngine($style, "acp_header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$groupcptitle",
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
case 'new_group':
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

	#display group manager.
	$groupManager = new groupAdministration();
	$groupProfileSel = $groupManager->groupProfileSelctor();

	#new group form.
	$tpl = new templateEngine($style, "cp-newgroup");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-CREATEGROUP" => "$lang[addgroup]",
	"LANG-GROUPNAME" => "$lang[name]",
	"LANG-GROUPDESCRIPTION" => "$lang[description]",
	"LANG-GROUPSTATUS" => "$lang[groupstat]",
	"LANG-OPEN" => "$lang[open]",
	"LANG-CLOSE" => "$lang[closed]",
	"LANG-HIDDEN" => "$lang[grouphidden]",
	"LANG-GROUPACCESS" => "$lang[groupaccess]",
	"LANG-SEL-LEVEL" => "$lang[sel_level]",
	"LANG-LEVEL-1" => "$lang[level1]",
	"LANG-LEVEL-2" => "$lang[level2]",
	"LANG-LEVEL-3" => "$lang[level3]",
	"LANG-GROUPPROFILE" => "$lang[groupprofile]",
	"LANG-GROUPPROFILEHINT" => "$lang[groupprofilehnt]",
	"GROUPPROFILE" => "$groupProfileSel",
	"LANG-ADDGROUP" => "$lang[addgroupbtn]"));

	#output template file.
	echo $tpl->outputHtml();
break;
case 'new_group_process':
	//get data from form.
	$group = $db->filterMySQL($_POST['group']);
	$description = $db->filterMySQL($_POST['description']);
	$status = $db->filterMySQL($_POST['status']);
	$groupaccess = $db->filterMySQL($_POST['groupaccess']);
	$gprofile = $db->filterMySQL($_POST['gprofile']);

	#get profile status.
	$db->SQL = "SELECT access_level FROM ebb_permission_profile WHERE profile='$gprofile'";
	$gprofile_level = $db->fetchResults();

	//do some error checking.
	if ($group == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nogroupname'];

        #direct user.
		redirect('acp/groupcp.php?action=new_group', false, 0);
	}
	if ($description == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nogroupdescription'];

        #direct user.
		redirect('acp/groupcp.php?action=new_group', false, 0);
	}
	if ($status == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['statusnotset'];

        #direct user.
		redirect('acp/groupcp.php?action=new_group', false, 0);
	}
	if ($groupaccess == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['noaccessset'];

        #direct user.
		redirect('acp/groupcp.php?action=new_group', false, 0);
	}
	if($gprofile == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nogprofilesel'];

        #direct user.
		redirect('acp/groupcp.php?action=new_group', false, 0);
	}
	if(strlen($group) > 30){
		#setup error session.
		$_SESSION['errors'] = $lang['longgroupname'];

        #direct user.
		redirect('acp/groupcp.php?action=new_group', false, 0);
	}
	if(strlen($description) > 255){
		#setup error session.
		$_SESSION['errors'] = $lang['longgroupdescription'];

        #direct user.
		redirect('acp/groupcp.php?action=new_group', false, 0);
	}
	if($gprofile_level !== $groupaccess){
		#setup error session.
		$_SESSION['errors'] = $lang['invalidprofilecho'];

        #direct user.
		redirect('acp/groupcp.php?action=new_group', false, 0);
	}

	//process query.
	$db->SQL = "INSERT INTO ebb_groups (Name, Description, Enrollment, Level, permission_type) VALUES('$group', '$description', '$status', '$groupaccess', '$gprofile')";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Created New Group: ".$group, $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/groupcp.php', false, 0);
break;
case 'group_modify':
	#see if user added the Group ID or not.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$error = new notifySys($lang['nogid'], true);
		$error->displayError();
	}else{
		$id = $db->filterMySQL($_GET['id']);
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

	#check for existing group.
	$db->SQL = "SELECT id FROM ebb_groups WHERE id='$id'";
	$group_chk = $db->affectedRows();

	if($group_chk == 0){
		$error = new notifySys($lang['groupnotexist'], true);
		$error->displayError();
	}
	#get group data.
	$db->SQL = "SELECT Name, Description, Enrollment, Level, permission_type FROM ebb_groups WHERE id='$id'";
	$groupData = $db->fetchResults();

	#display group manager.
	$groupManager = new groupAdministration();
	$groupProfileSel = $groupManager->groupProfileSelctor();

	#modify group form.
	$tpl = new templateEngine($style, "cp-modifygroup");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-MODIFYGROUP" => "$lang[modifygroup]",
	"ID" => "$id",
	"LANG-GROUPNAME" => "$lang[name]",
	"GROUPNAME" => "$groupData[Name]",
	"LANG-GROUPDESCRIPTION" => "$lang[description]",
	"GROUPDESCRIPTION" => "$groupData[Description]",
	"LANG-GROUPSTATUS" => "$lang[groupstat]",
	"LANG-OPEN" => "$lang[open]",
	"LANG-CLOSE" => "$lang[closed]",
	"LANG-HIDDEN" => "$lang[grouphidden]",
	"LANG-GROUPACCESS" => "$lang[groupaccess]",
	"LANG-SEL-LEVEL" => "$lang[sel_level]",
	"LANG-LEVEL-1" => "$lang[level1]",
	"LANG-LEVEL-2" => "$lang[level2]",
	"LANG-LEVEL-3" => "$lang[level3]",
	"LANG-GROUPPROFILE" => "$lang[groupprofile]",
	"LANG-GROUPPROFILEHINT" => "$lang[groupprofilehnt]",
	"GROUPPROFILE" => "$groupProfileSel"));

	#get membership type value.
	if ($group_r['Enrollment'] == 0){
	  $tpl->removeBlock("openStatus");
	  $tpl->removeBlock("hiddenStatus");
	}elseif ($group_r['Enrollment'] == 1){
	  $tpl->removeBlock("closedStatus");
	  $tpl->removeBlock("hiddenStatus");
	}else{
	  $tpl->removeBlock("openStatus");
	  $tpl->removeBlock("closedStatus");
	}

	#get group level value.
	if ($group_r['Level'] == 1){
	  $tpl->removeBlock("L2Access");
	  $tpl->removeBlock("L3Access");
	}elseif($group_r['Level'] == 2){
	  $tpl->removeBlock("L1Access");
	  $tpl->removeBlock("L3Access");
	}else{
	  $tpl->removeBlock("L1Access");
	  $tpl->removeBlock("L2Access");
	}

	#output template file.
	echo $tpl->outputHtml();
break;
case 'modify_group_process':
	#see if user added the Group ID or not.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$error = new notifySys($lang['nogid'], true);
		$error->displayError();;
	}else{
		$id = $db->filterMySQL($_GET['id']);
	}
	
	#check for existing group.
	$db->SQL = "SELECT id FROM ebb_groups WHERE id='$id'";
	$group_chk = $db->affectedRows();

	if($group_chk == 0){
		$error = new notifySys($lang['groupnotexist'], true);
		$error->displayError();
	}
	
	//get data from form.
	$group = $db->filterMySQL($_POST['group']);
	$description = $db->filterMySQL($_POST['description']);
	$status = $db->filterMySQL($_POST['status']);
	$groupaccess = $db->filterMySQL($_POST['groupaccess']);
	$gprofile = $db->filterMySQL($_POST['gprofile']);

	#get profile status.
	$db->SQL = "select access_level from ebb_permission_profile where profile='$gprofile'";
	$gprofile_level = $db->fetchResults();

	//do some error checking.
	if ($group == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nogroupname'];

        #direct user.
		redirect('acp/groupcp.php?action=group_modify', false, 0);
	}
	if ($description == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nogroupdescription'];

        #direct user.
		redirect('acp/groupcp.php?action=group_modify', false, 0);
	}
	if ($status == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['statusnotset'];

        #direct user.
		redirect('acp/groupcp.php?action=group_modify', false, 0);
	}
	if ($groupaccess == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['noaccessset'];

        #direct user.
		redirect('acp/groupcp.php?action=group_modify', false, 0);
	}
	if($gprofile == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nogprofilesel'];

        #direct user.
		redirect('acp/groupcp.php?action=group_modify', false, 0);
	}
	if(strlen($group) > 30){
		#setup error session.
		$_SESSION['errors'] = $lang['longgroupname'];

        #direct user.
		redirect('acp/groupcp.php?action=group_modify', false, 0);
	}
	if(strlen($description) > 255){
		#setup error session.
		$_SESSION['errors'] = $lang['longgroupdescription'];

        #direct user.
		redirect('acp/groupcp.php?action=group_modify', false, 0);
	}
	if($gprofile_level !== $groupaccess){
		#setup error session.
		$_SESSION['errors'] = $lang['invalidprofilecho'];

        #direct user.
		redirect('acp/groupcp.php?action=group_modify', false, 0);
	}

	//process query.
	$db->SQL = "UPDATE ebb_groups SET Name='$group', Description='$description', Enrollment='$status', Level='$groupaccess', permission_type='$gprofile' WHERE id='$id'";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Modified Group: ".$group, $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/groupcp.php', false, 0);
break;
case 'group_delete':
	#see if user added the Group ID or not.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$error = new notifySys($lang['nogid'], true);
		$error->displayError();
	}else{
		$id = $db->filterMySQL($_GET['id']);
	}
	#check for existing group.
	$db->SQL = "SELECT id, Name FROM ebb_groups WHERE id='$id' LIMIT 1";
	$group_chk = $db->affectedRows();
	$groupData = $db->fetchResults();

	if($group_chk == 0){
		$error = new notifySys($lang['groupnotexist'], true);
		$error->displayError();
	}
	//see if user is trying to remove the default groups, if so cancel that action!
	if (($id == 1) OR ($id == 2) OR ($id == 3) OR ($id == 4)){
		$error = new notifySys($lang['nodelgroup'], true);
		$error->displayError();
	}

	#make sure no one is a member of this group first.
	$db->SQL = "SELECT gid FROM ebb_group_users WHERE gid='$id'";
	$groupUsr_chk = $db->affectedRows();

	if($groupUsr_chk == 1){
        $error = new notifySys($lang['userexistgroup'], true);
		$error->displayError();
	}
	//proces query.
	$db->SQL = "DELETE FROM ebb_groups WHERE id='$id'";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Deleted Group: ".$groupData['Name'], $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/groupcp.php', false, 0);
break;
case 'group_memberlist':
	#see if user added the Group ID or not.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$error = new notifySys($lang['nogid'], true);
		$error->displayError();
	}else{
		$id = $db->filterMySQL($_GET['id']);
	}
	
	#check for existing group.
	$db->SQL = "SELECT id FROM ebb_groups WHERE id='$id'";
	$group_chk = $db->affectedRows();

	if($group_chk == 0){
		$error = new notifySys($lang['groupnotexist'], true);
		$error->displayError();
	}

	#display group manager.
	$groupManager = new groupAdministration();
	$groupManager->groupUserlistManager();
break;
case 'groupmember_remove':
	#see if user added the Group ID or not.
	if((!isset($_GET['gid'])) or (empty($_GET['gid']))){
		$error = new notifySys($lang['nogid'], true);
		$error->displayError();
	}else{
		$gid = $db->filterMySQL($_GET['gid']);
	}
	
	#see if user added the username or not.
	if((!isset($_GET['u'])) or (empty($_GET['u']))){
		$error = new notifySys($lang['nouser'], true);
		$error->displayError();
	}else{
		$u = $db->filterMySQL($_GET['u']);
	}
	
	//check to see if this move will remove all level 1 users.
	$db->SQL = "SELECT gid FROM ebb_group_users WHERE gid='$gid' LIMIT 1";
	$admin_num_chk = $db->affectedRows();

	if (($gid == 1) AND ($admin_num_chk == 1)){
		$error = new notifySys($lang['nouserdelete'], true);
		$error->displayError();
	}

	//change gid to regular member.
	$db->SQL = "UPDATE ebb_group_users SET gid='3' WHERE Username='$u'";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Changed Group Status: ".$u, $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/groupcp.php?action=group_memberlist&id='.$gid, false, 0);
break;
case 'pendinglist':
	#see if user added the Group ID or not.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$error = new notifySys($lang['nogid'], true);
		$error->displayError();
	}else{
		$id = $db->filterMySQL($_GET['id']);
	}
	#check for existing group.
	$db->SQL = "SELECT id FROM ebb_groups WHERE id='$id'";
	$groupChk = $db->affectedRows();

	if($groupChk == 0){
		$error = new notifySys($lang['groupnotexist'], true);
		$error->displayError();
	}else{
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
	
		#display group manager.
		$groupManager = new groupAdministration();
		$groupManager->groupPendingList();
	}
break;
case 'pending_stat':
	$accept = $db->filterMySQL($_GET['accept']);
	$gid = $db->filterMySQL($_GET['gid']);
	$u = $db->filterMySQL($_GET['u']);
	#error check.
	if (($accept == "") or ($gid == "") or ($u == "")){
	    #user just hit submit, send them back.
		redirect('acp/groupcp.php', false, 0);
	}
	
	#perform action based on user's action.
	if($accept == 1){
		//change group membership to the new memebrship.
		$db->SQL = "UPDATE ebb_group_users SET gid='$gid' WHERE Username='$u'";
		$db->query();

		//delete information from the request table.
		$db->SQL = "DELETE FROM ebb_group_member_request WHERE gid='$gid' AND username='$u'";
		$db->query();

		#log this into our audit system.
		$acpAudit = new auditSystem();
		$acpAudit->logAction("Granted User Group Status: ".$u, $acpUsr, time(), detectProxy());
	}else{
		//delete information from the request table.
		$db->SQL = "DELETE FROM ebb_group_member_request WHERE gid='$gid' AND username='$u'";
		$db->query();

		#log this into our audit system.
		$acpAudit = new auditSystem();
		$acpAudit->logAction("Denied User Group Status: ".$u, $acpUsr, time(), detectProxy());
	}
	//bring user back
	redirect('acp/groupcp.php', false, 0);
break;
case 'group_adduser':
	$user = $db->filterMySQL($_POST['user']);
	$id = $db->filterMySQL($_POST['id']);

	#check for existing group.
	$db->SQL = "SELECT Username FROM ebb_users WHERE Username='$user'";
	$usr_chk = $db->affectedRows();
	
	#validate gid against database to ensure its correct.
	$db->SQL = "SELECT id FROM ebb_groups WHERE id='$id'";
	$gid_chk = $db->affectedRows();

	if($usr_chk == 0){
		#setup error session.
		$_SESSION['errors'] = $lang['usernotexist'];

        #direct user.
		redirect('acp/groupcp.php?action=pendinglist', false, 0);
	}
	if($gid_chk == 0){
		#setup error session.
		$_SESSION['errors'] = $lang['groupnotexist'];

        #direct user.
		redirect('acp/groupcp.php?action=pendinglist', false, 0);
	}
	if($user == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nousername_group'];

        #direct user.
		redirect('acp/groupcp.php?action=pendinglist', false, 0);
	}
	if(strlen($user) > 25){
		#setup error session.
		$_SESSION['errors'] = $lang['longusername'];

        #direct user.
		redirect('acp/groupcp.php?action=pendinglist', false, 0);
	}
	if($id == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nogid'];

        #direct user.
		redirect('acp/groupcp.php?action=pendinglist', false, 0);
	}

	//proces query.
	$db->SQL = "UPDATE ebb_group_users SET gid='$id' WHERE Username='$user'";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Granted User Group Status: ".$user, $acpUsr, time(), detectProxy());

	//bring user back
	redirect('acp/groupcp.php', false, 0);
break;
case 'manageprofile':
	#display group manager.
	$groupManager = new groupAdministration();
	$groupManager->groupProfileManager();
break;
case 'new_profile':
	#see if type was defined.
	if((isset($_GET['type'])) or (!empty($_GET['type']))){
		$type = $db->filterMySQL($_GET['type']);
	}else{
		$error = new notifySys($lang['noprofiletype'], true);
		$error->displayError();
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

	#display group manager.
	$groupManager = new groupAdministration();
	$groupManager->groupProfileNew($type);
break;
case 'new_profile_process':
	#see if type was defined.
	if((isset($_GET['type'])) or (!empty($_GET['type']))){
		$type = $db->filterMySQL($_GET['type']);
	}else{
		$error = new notifySys($lang['noprofiletype'], true);
		$error->displayError();
	}
	#perform action based on profile type.
	if($type == 1){
		#define variables.
		$profilename = $db->filterMySQL($_POST['profilename']);
		$manage_boards = $db->filterMySQL($_POST['manage_boards']);
		$prune_boards = $db->filterMySQL($_POST['prune_boards']);
		$manage_groups = $db->filterMySQL($_POST['manage_groups']);
		$mass_email = $db->filterMySQL($_POST['mass_email']);
		$word_censor = $db->filterMySQL($_POST['word_censor']);
		$manage_smiles = $db->filterMySQL($_POST['manage_smiles']);
		$modify_settings = $db->filterMySQL($_POST['modify_settings']);
		$manage_styles = $db->filterMySQL($_POST['manage_styles']);
		$view_phpinfo = $db->filterMySQL($_POST['view_phpinfo']);
		$check_updates = $db->filterMySQL($_POST['check_updates']);
		$see_acp_log = $db->filterMySQL($_POST['see_acp_log']);
		$clear_acp_log = $db->filterMySQL($_POST['clear_acp_log']);
		$manage_banlist = $db->filterMySQL($_POST['manage_banlist']);
		$manage_users = $db->filterMySQL($_POST['manage_users']);
		$prune_users = $db->filterMySQL($_POST['prune_users']);
		$manage_blacklist = $db->filterMySQL($_POST['manage_blacklist']);
		$manage_warnlog = $db->filterMySQL($_POST['manage_warnlog']);
		$activate_users = $db->filterMySQL($_POST['activate_users']);

		#error check.
		if(empty($profilename)){
			#setup error session.
			$_SESSION['errors'] = $lang['profileerr'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if(strlen($profilename) > 30){
			#setup error session.
			$_SESSION['errors'] = $lang['longprofilenameerr'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($manage_boards == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_boards'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($prune_boards == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['prune_boards'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($manage_groups == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_groups'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($mass_email == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['mass_email'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($word_censor == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['word_censor'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($manage_smiles == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_smiles'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($modify_settings == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['modify_settings'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($manage_styles == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_styles'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($view_phpinfo == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['view_phpinfo'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($check_updates == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['check_updates'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($see_acp_log == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['see_acp_log'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($clear_acp_log == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['clear_acp_log'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($manage_banlist == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_banlist'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($manage_users == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_users'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($manage_blacklist == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_blacklist'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($manage_warnlog == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_warnlog'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($activate_users == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['activate_users'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}

		//add profile to profile table.
		$db->SQL = "INSERT INTO ebb_permission_profile (profile, access_level) VALUES('$profilename', '$type')";
		$db->query();

		#get profile ID.
		$db->SQL = "SELECT id FROM ebb_permission_profile ORDER BY id DESC LIMIT 1";
		$gprofile_id = $db->fetchResults();

		//add values into data table for profile.
		$db->SQL = "INSERT INTO ebb_permission_data (profile, permission, set_value) VALUES('$gprofile_id[id]', '1', '$manage_boards'),
('$gprofile_id[id]', '2', '$prune_boards'),
('$gprofile_id[id]', '3', '$manage_groups'),
('$gprofile_id[id]', '4', '$mass_email'),
('$gprofile_id[id]', '5', '$word_censor'),
('$gprofile_id[id]', '6', '$manage_smiles'),
('$gprofile_id[id]', '7', '$modify_settings'),
('$gprofile_id[id]', '8', '$manage_styles'),
('$gprofile_id[id]', '9', '$view_phpinfo'),
('$gprofile_id[id]', '10', '$check_updates'),
('$gprofile_id[id]', '11', '$see_acp_log'),
('$gprofile_id[id]', '12', '$clear_acp_log'),
('$gprofile_id[id]', '13', '$manage_banlist'),
('$gprofile_id[id]', '14', '$manage_users'),
('$gprofile_id[id]', '15', '$prune_users'),
('$gprofile_id[id]', '16', '$manage_blacklist'),
('$gprofile_id[id]', '18', '$manage_warnlog'),
('$gprofile_id[id]', '19', '$activate_users'),
('$gprofile_id[id]', '20', '1'),
('$gprofile_id[id]', '21', '1'),
('$gprofile_id[id]', '22', '1'),
('$gprofile_id[id]', '23', '1'),
('$gprofile_id[id]', '24', '1'),
('$gprofile_id[id]', '25', '1'),
('$gprofile_id[id]', '26', '1'),
('$gprofile_id[id]', '27', '1'),
('$gprofile_id[id]', '28', '1'),
('$gprofile_id[id]', '29', '1'),
('$gprofile_id[id]', '30', '1'),
('$gprofile_id[id]', '31', '1'),
('$gprofile_id[id]', '32', '1'),
('$gprofile_id[id]', '33', '1'),
('$gprofile_id[id]', '34', '1'),
('$gprofile_id[id]', '35', '1'),
('$gprofile_id[id]', '36', '1'),
('$gprofile_id[id]', '37', '1'),
('$gprofile_id[id]', '38', '1'),
('$gprofile_id[id]', '39', '1')";
		$db->query();
	}elseif($type == 2){
		#define variables.
		$profilename = $db->filterMySQL($_POST['profilename']);
		$edit_topics = $db->filterMySQL($_POST['edit_topics']);
		$delete_topics = $db->filterMySQL($_POST['delete_topics']);
		$lock_topics = $db->filterMySQL($_POST['lock_topics']);
		$move_topics = $db->filterMySQL($_POST['move_topics']);
		$view_ips = $db->filterMySQL($_POST['view_ips']);
		$warn_users = $db->filterMySQL($_POST['warn_users']);

		#error check.
		if(empty($profilename)){
			#setup error session.
			$_SESSION['errors'] = $lang['profileerr'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if(strlen($profilename) > 30){
			#setup error session.
			$_SESSION['errors'] = $lang['longprofilenameerr'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($edit_topics == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['edit_topics'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($delete_topics == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['delete_topics'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($lock_topics == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['lock_topics'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($move_topics == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['move_topics'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($view_ips == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['view_ips'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($warn_users == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['warn_users'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}

		//add profile to profile table.
		$db->SQL = "INSERT INTO ebb_permission_profile (profile, access_level) VALUES('$profilename', '$type')";
		$db->query();

		#get profile ID.
		$db->SQL = "SELECT id FROM ebb_permission_profile ORDER BY id DESC LIMIT 1";
		$gprofile_id = $db->fetchResults();

		//add values into data table for profile.
		$db->SQL = "INSERT INTO ebb_permission_data (profile, permission, set_value) VALUES('$gprofile_id[id]', '20', '$edit_topics'),
('$gprofile_id[id]', '21', '$delete_topic'),
('$gprofile_id[id]', '22', '$lock_topics'),
('$gprofile_id[id]', '23', '$move_topics'),
('$gprofile_id[id]', '24', '$view_ips'),
('$gprofile_id[id]', '25', '$warn_users'),
('$gprofile_id[id]', '26', '1'),
('$gprofile_id[id]', '27', '1'),
('$gprofile_id[id]', '28', '1'),
('$gprofile_id[id]', '29', '1'),
('$gprofile_id[id]', '30', '1'),
('$gprofile_id[id]', '31', '1'),
('$gprofile_id[id]', '32', '1'),
('$gprofile_id[id]', '33', '1'),
('$gprofile_id[id]', '34', '1'),
('$gprofile_id[id]', '35', '1'),
('$gprofile_id[id]', '36', '1'),
('$gprofile_id[id]', '37', '1'),
('$gprofile_id[id]', '38', '1'),
('$gprofile_id[id]', '39', '1')";
			$db->query();
	}elseif($type == 3){
		#define variables.
		$profilename = $db->filterMySQL($_POST['profilename']);
		$attach_files = $db->filterMySQL($_POST['attach_files']);
		$pm_access = $db->filterMySQL($_POST['pm_access']);
		$search_board = $db->filterMySQL($_POST['search_board']);
		$download_files = $db->filterMySQL($_POST['download_files']);
		$custom_titles = $db->filterMySQL($_POST['custom_titles']);
		$view_profile = $db->filterMySQL($_POST['view_profile']);
		$use_avatars = $db->filterMySQL($_POST['use_avatars']);
		$use_signatures = $db->filterMySQL($_POST['use_signatures']);
		$join_groups = $db->filterMySQL($_POST['join_groups']);
		$create_poll = $db->filterMySQL($_POST['create_poll']);
		$vote_poll = $db->filterMySQL($_POST['vote_poll']);
		$new_topic = $db->filterMySQL($_POST['new_topic']);
		$reply = $db->filterMySQL($_POST['reply']);
		$important_topic = $db->filterMySQL($_POST['important_topic']);

		#error check.
		if(empty($profilename)){
			#setup error session.
			$_SESSION['errors'] = $lang['profileerr'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if(strlen($profilename) > 30){
			#setup error session.
			$_SESSION['errors'] = $lang['longprofilenameerr'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($attach_files == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['attach_files'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($pm_access == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['pm_access'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($search_board == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['search_board'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($download_files == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['download_files'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($custom_titles == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['custom_titles'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($view_profile == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['view_profile'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($use_avatars == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['use_avatars'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($use_signatures == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['use_signatures'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($join_groups == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['join_groups'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($create_poll == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['create_poll'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($vote_poll == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['vote_poll'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($new_topic == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['new_topic'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($reply == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['reply'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}
		if($important_topic == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['important_topic'];

	        #direct user.
			redirect('acp/groupcp.php?action=new_profile&type='.$type, false, 0);
		}

		//add profile to profile table.
		$db->SQL = "INSERT INTO ebb_permission_profile (profile, access_level) VALUES('$profilename', '$type')";
		$db->query();

		#get profile ID.
		$db->SQL = "SELECT id FROM ebb_permission_profile ORDER BY id DESC LIMIT 1";
		$gprofile_id = $db->fetchResults();

		//add values into data table for profile.
		$db->SQL = "INSERT INTO ebb_permission_data (profile, permission, set_value) VALUES('$gprofile_id[id]', '26', '$attach_files'),
('$gprofile_id[id]', '27', '$pm_access'),
('$gprofile_id[id]', '28', '$search_board'),
('$gprofile_id[id]', '29', '$download_files'),
('$gprofile_id[id]', '30', '$custom_titles'),
('$gprofile_id[id]', '31', '$view_profile'),
('$gprofile_id[id]', '32', '$use_avatars'),
('$gprofile_id[id]', '33', '$use_signatures'),
('$gprofile_id[id]', '34', '$join_groups'),
('$gprofile_id[id]', '35', '$create_poll'),
('$gprofile_id[id]', '36', '$vote_poll'),
('$gprofile_id[id]', '37', '$new_topic'),
('$gprofile_id[id]', '38', '$reply'),
('$gprofile_id[id]', '39', '$important_topic')";
		$db->query();
	}
	
	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Added New Group Profile: ".$profilename, $acpUsr, time(), detectProxy());

	//bring user back
	redirect('acp/groupcp.php?action=manageprofile', false, 0);
break;
case 'profile_modify':
	#see if profile id was defined.
	if((isset($_GET['id'])) or (!empty($_GET['id']))){
		$id = $db->filterMySQL($_GET['id']);
	}else{
		$error = new notifySys($lang['noprofileid'], true);
		$error->displayError();
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

	#get profile name and access type.
    $db->SQL = "SELECT id, profile, access_level FROM ebb_permission_profile WHERE id='$id'";
	$gprofile_r = $db->fetchResults();

	#display group manager.
	$groupManager = new groupAdministration();
	$groupManager->groupProfileEdit($gprofile_r['id'], $gprofile_r['profile'], $gprofile_r['access_level']);
break;
case 'profile_modify_process':
	#see if type was defined.
	if((isset($_GET['type'])) or (!empty($_GET['type']))){
		$type = $db->filterMySQL($_GET['type']);
	}else{
		$error = new notifySys($lang['noprofiletype'], true);
		$error->displayError();
	}
	
	#see if profile id was defined.
	if((isset($_POST['id'])) or (!empty($_POST['id']))){
		$id = $db->filterMySQL($_POST['id']);
	}else{
		$error = new notifySys($lang['noprofileid'], true);
		$error->displayError();
	}
	#perform action based on profile type.
	if($type == 1){
		#define variables.
		$profilename = $db->filterMySQL($_POST['profilename']);
		$manage_boards = $db->filterMySQL($_POST['manage_boards']);
		$prune_boards = $db->filterMySQL($_POST['prune_boards']);
		$manage_groups = $db->filterMySQL($_POST['manage_groups']);
		$mass_email = $db->filterMySQL($_POST['mass_email']);
		$word_censor = $db->filterMySQL($_POST['word_censor']);
		$manage_smiles = $db->filterMySQL($_POST['manage_smiles']);
		$modify_settings = $db->filterMySQL($_POST['modify_settings']);
		$manage_styles = $db->filterMySQL($_POST['manage_styles']);
		$view_phpinfo = $db->filterMySQL($_POST['view_phpinfo']);
		$check_updates = $db->filterMySQL($_POST['check_updates']);
		$see_acp_log = $db->filterMySQL($_POST['see_acp_log']);
		$clear_acp_log = $db->filterMySQL($_POST['clear_acp_log']);
		$manage_banlist = $db->filterMySQL($_POST['manage_banlist']);
		$manage_users = $db->filterMySQL($_POST['manage_users']);
		$prune_users = $db->filterMySQL($_POST['prune_users']);
		$manage_blacklist = $db->filterMySQL($_POST['manage_blacklist']);
		$manage_warnlog = $db->filterMySQL($_POST['manage_warnlog']);
		$activate_users = $db->filterMySQL($_POST['activate_users']);

		#error check.
		if(empty($profilename)){
			#setup error session.
			$_SESSION['errors'] = $lang['profileerr'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if(strlen($profilename) > 30){
			#setup error session.
			$_SESSION['errors'] = $lang['longprofilenameerr'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($manage_boards == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_boards'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($prune_boards == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['prune_boards'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($manage_groups == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_groups'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($mass_email == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['mass_email'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($word_censor == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['word_censor'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($manage_smiles == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_smiles'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($modify_settings == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['modify_settings'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($manage_styles == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_styles'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($view_phpinfo == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['view_phpinfo'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($check_updates == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['check_updates'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($see_acp_log == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['see_acp_log'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($clear_acp_log == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['clear_acp_log'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($manage_banlist == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_banlist'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($manage_users == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_users'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($manage_blacklist == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_blacklist'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($manage_warnlog == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['manage_warnlog'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($activate_users == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['activate_users'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}

		//update profile name.
		$db->SQL = "UPDATE ebb_permission_profile SET profile='$profilename'";
		$db->query();

		#get profile ID.
		$db->SQL = "SELECT id FROM ebb_permission_profile WHERE profile='$profilename'";
		$gprofile_id = $db->fetchResults();

		//update values from data table.
		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$manage_boards' WHERE profile='$gprofile_id[id]' AND permission='1'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$prune_boards' WHERE profile='$gprofile_id[id]' AND permission='2'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$manage_groups' WHERE profile='$gprofile_id[id]' AND permission='3'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$mass_email' WHERE profile='$gprofile_id[id]' AND permission='4'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$word_censor' WHERE profile='$gprofile_id[id]' AND permission='5'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$manage_smiles' WHERE profile='$gprofile_id[id]' AND permission='6'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$modify_settings' WHERE profile='$gprofile_id[id]' AND permission='7'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$manage_styles' WHERE profile='$gprofile_id[id]' AND permission='8'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$view_phpinfo' WHERE profile='$gprofile_id[id]' AND permission='9'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$check_UPDATEs' WHERE profile='$gprofile_id[id]' AND permission='10'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$see_acp_log' WHERE profile='$gprofile_id[id]' AND permission='11'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$clear_acp_log' WHERE profile='$gprofile_id[id]' AND permission='12'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$manage_banlist' WHERE profile='$gprofile_id[id]' AND permission='13'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$manage_users' WHERE profile='$gprofile_id[id]' AND permission='14'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$prune_users' WHERE profile='$gprofile_id[id]' AND permission='15'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$manage_blacklist' WHERE profile='$gprofile_id[id]' AND permission='16'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$manage_warnlog' WHERE profile='$gprofile_id[id]' AND permission='18'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$activate_users' WHERE profile='$gprofile_id[id]' AND permission='19'";
		$db->query();
	}elseif($type == 2){
		#define variables.
		$profilename = $db->filterMySQL($_POST['profilename']);
		$edit_topics = $db->filterMySQL($_POST['edit_topics']);
		$delete_topics = $db->filterMySQL($_POST['delete_topics']);
		$lock_topics = $db->filterMySQL($_POST['lock_topics']);
		$move_topics = $db->filterMySQL($_POST['move_topics']);
		$view_ips = $db->filterMySQL($_POST['view_ips']);
		$warn_users = $db->filterMySQL($_POST['warn_users']);

		#error check.
		if(empty($profilename)){
			#setup error session.
			$_SESSION['errors'] = $lang['profileerr'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if(strlen($profilename) > 30){
			#setup error session.
			$_SESSION['errors'] = $lang['longprofilenameerr'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($edit_topics == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['edit_topics'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($delete_topics == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['delete_topics'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($lock_topics == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['lock_topics'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($move_topics == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['move_topics'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($view_ips == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['view_ips'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($warn_users == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['warn_users'];

	        #direct user.
			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}

		#update profile name.
		$db->SQL = "UPDATE ebb_permission_profile SET profile='$profilename'";
		$db->query();

		#get profile ID.
		$db->SQL = "SELECT id from ebb_permission_profile WHERE profile='$profilename'";
		$gprofile_id = $db->fetchResults();

		//update values from data table.
		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$edit_topics' WHERE profile='$gprofile_id[id]' AND permission='20'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$delete_topics' WHERE profile='$gprofile_id[id]' AND permission='21'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$lock_topics' WHERE profile='$gprofile_id[id]' AND permission='22'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$move_topics' WHERE profile='$gprofile_id[id]' AND permission='23'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$view_ips' WHERE profile='$gprofile_id[id]' AND permission='24'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$warn_users' WHERE profile='$gprofile_id[id]' AND permission='25'";
		$db->query();
	}elseif($type == 3){
		#define variables.
		$profilename = $db->filterMySQL($_POST['profilename']);
		$attach_files = $db->filterMySQL($_POST['attach_files']);
		$pm_access = $db->filterMySQL($_POST['pm_access']);
		$search_board = $db->filterMySQL($_POST['search_board']);
		$download_files = $db->filterMySQL($_POST['download_files']);
		$custom_titles = $db->filterMySQL($_POST['custom_titles']);
		$view_profile = $db->filterMySQL($_POST['view_profile']);
		$use_avatars = $db->filterMySQL($_POST['use_avatars']);
		$use_signatures = $db->filterMySQL($_POST['use_signatures']);
		$join_groups = $db->filterMySQL($_POST['join_groups']);
		$create_poll = $db->filterMySQL($_POST['create_poll']);
		$vote_poll = $db->filterMySQL($_POST['vote_poll']);
		$new_topic = $db->filterMySQL($_POST['new_topic']);
		$reply = $db->filterMySQL($_POST['reply']);
		$important_topic = $db->filterMySQL($_POST['important_topic']);

		#error check.
		if(empty($profilename)){
			#setup error session.
			$_SESSION['errors'] = $lang['profileerr'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if(strlen($profilename) > 30){
			#setup error session.
			$_SESSION['errors'] = $lang['longprofilenameerr'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($attach_files == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['attach_files'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($pm_access == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['pm_access'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($search_board == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['search_board'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($download_files == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['download_files'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($custom_titles == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['custom_titles'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($view_profile == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['view_profile'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($use_avatars == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['use_avatars'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($use_signatures == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['use_signatures'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($join_groups == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['join_groups'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($create_poll == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['create_poll'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($vote_poll == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['vote_poll'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($new_topic == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['new_topic'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($reply == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['reply'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}
		if($important_topic == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['actionerr'].":&nbsp;".$lang['important_topic'];

	        #direct user.
   			redirect('acp/groupcp.php?action=profile_modify&id='.$id, false, 0);
		}

		#update profile name.
		$db->SQL = "UPDATE ebb_permission_profile SET profile='$profilename'";
		$db->query();

		#get profile ID.
		$db->SQL = "SELECT id from ebb_permission_profile WHERE profile='$profilename'";
		$gprofile_id = $db->fetchResults();

		//update values from data table.
		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$attach_files' WHERE profile='$gprofile_id[id]' AND permission='26'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$pm_access' WHERE profile='$gprofile_id[id]' AND permission='27'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$search_board' WHERE profile='$gprofile_id[id]' AND permission='28'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$download_files' WHERE profile='$gprofile_id[id]' AND permission='29'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$custom_titles' WHERE profile='$gprofile_id[id]' AND permission='30'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$view_profile' WHERE profile='$gprofile_id[id]' AND permission='31'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$use_avatars' WHERE profile='$gprofile_id[id]' AND permission='32'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$use_signatures' WHERE profile='$gprofile_id[id]' AND permission='33'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$join_groups' WHERE profile='$gprofile_id[id]' AND permission='34'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$create_poll' WHERE profile='$gprofile_id[id]' AND permission='35'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$vote_poll' WHERE profile='$gprofile_id[id]' AND permission='36'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$new_topic' WHERE profile='$gprofile_id[id]' AND permission='37'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$reply' WHERE profile='$gprofile_id[id]' AND permission='38'";
		$db->query();

		$db->SQL = "UPDATE ebb_permission_data SET permission, set_value='$important_topic' WHERE profile='$gprofile_id[id]' AND permission='39'";
		$db->query();
	}
	
	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Modified Group Profile: ".$profilename, $acpUsr, time(), detectProxy());

	//bring user back
	redirect('acp/groupcp.php?action=manageprofile', false, 0);
break;
case 'profile_delete':
	#see if id was defined.
	if((isset($_GET['id'])) or (!empty($_GET['id']))){
		$id = $db->filterMySQL($_GET['id']);
	}else{
		$error = new notifySys($lang['noprofileid'], true);
		$error->displayError();
	}

	#see if any groups use the profile.
	$db->SQL = "SELECT id FROM ebb_groups WHERE permission_type='$id'";
	$chk_group = $db->affectedRows();

	if($chk_group > 0){
		$error = new notifySys($lang['inuseprofile'], true);
		$error->displayError();
	}

	#see if profile is a system-based profile.
	$db->SQL = "SELECT profile, system FROM ebb_permission_profile WHERE id='$id'";
	$chk_system = $db->fetchResults();

	if($chk_system['system'] == 1){
		$error = new notifySys($lang['reservedprofile'], true);
		$error->displayError();
	}

	#delete profile from db.	
	$db->SQL = "DELETE ebb_permission_profile WHERE id='$id'";
	$db->query();

	#delete all profile action data associated with the profile.
	$db->SQL = "DELETE ebb_permission_data WHERE profile='$id'";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Deleted Group Profile: ".$chk_system['profile'], $acpUsr, time(), detectProxy());

	//bring user back
	redirect('acp/groupcp.php?action=manageprofile', false, 0);
break;
default:
	#display group manager.
	$groupManager = new groupAdministration();
	$groupManager->groupManager();
}
#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();
ob_end_flush();
?>
