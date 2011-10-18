<?php
define('IN_EBB', true);

/**
Filename: viewgrouproster.php
Last Modified: 12/12/2010

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
#make sure the call is from an AJAX request.
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
	die("<strong>THIS PAGE CANNOT BE ACCESSED DIRECTLY!</strong>");
}

require_once "../config.php";
require_once FULLPATH."/header.php";

#make sure this isnt being accessed by a non-logged in user.
if ($logged_user == "guest"){
	$displayMsg = new notifySys($lang['accessdenied'], false);
	$displayMsg->genericError();
}else{
	#keyword variable.
	$groupid = $db->filterMySQL($_GET['groupid']);
	
	#see if group ID is valid.
    $db->SQL = "SELECT id FROM ebb_groups WHERE id='$groupid'";
	$validateGroupID = $db->affectedRows();
	
	$db->SQL = "SELECT Username FROM ebb_group_users WHERE Status='Active' and gid='$groupid'";
	$rosterQ = $db->query();
	$rosterCt = $db->affectedRows();
	
	if($validateGroupID == 0){
		$displayMsg = new notifySys($lang['notexist'], false);
		$displayMsg->genericError();
	}elseif($rosterCt == 0){
		$displayMsg = new notifySys($lang['nomembers'], false);
		$displayMsg->genericError();
	}else{
		#get list of users that belong to the defined group.
        while ($roster = mysql_fetch_assoc ($rosterQ)) {
			#get user's profile.
			$userInfo = new user($roster['Username']);
			
			#format a few values.
			$postCount = number_format($userInfo->userSettings("Post_Count"));
			$joinDate = formatTime($timeFormat, $userInfo->userSettings("Date_Joined"), $gmt);
			
			#output group roster.
        	$tpl = new templateEngine($style, "grouproster");
			$tpl->parseTags(array(
			"GROUPUSER" => "$roster[Username]",
			"POSTCOUNT" => "$postCount",
			"JOINDATE" => "$joinDate",
			"LANG-POSTS" => "$lang[posts]"));
			
			echo $tpl->outputHtml();
		}
	}
}
?>
