<?php
define('IN_EBB', true);
/**
Filename: Members.php
Last Modified: 2/22/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";

$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
	"TITLE" => "$title",
	"PAGETITLE" => "$lang[members]",
    "LANG-HELP-TITLE" => "$help[nohelptitle]",
    "LANG-HELP-BODY" => "$help[nohelpbody]",
    "LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

#see if user can access this section.
if(($groupPolicy->validateAccess(1, 31) == false) OR ($groupAccess == 0)){
	$error = new notifySys($lang['accessdenied'], true);
	$error->displayError();
}

//output top
if($logged_user != "guest"){
	$pmMsg = $userData->getNewPMCount();
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
}
#output top template file.
echo $tpl->outputHtml();

//display memberpage.
$count = 0;
$count2 = 0;
//pagination
if(!isset($_GET['pg'])){
    $pg = 1;
} else {
    $pg = $db->filterMySQL(var_cleanup($_GET['pg']));
}
#setup perPg settings value.
$perPg = $boardPref->getPreferenceValue("per_page");


//Figure out the limit for the query based on the current page number.
$from = (($pg * $perPg) - $perPg);

#get sql data based on user type.
if($groupAccess == 1){
	//get the data from the DB.
	$db->SQL = "SELECT Username, Post_Count, Date_Joined, Location FROM ebb_users LIMIT $from, $perPg";
	$query = $db->query();

	#get the number result for this
	$db->SQL = "SELECT id FROM ebb_users";
	$num = $db->affectedRows();

}else{
	//get the data from the DB.
	$db->SQL = "SELECT Username, Post_Count, Date_Joined, Location FROM ebb_users WHERE active='1' LIMIT $from, $perPg";
	$query = $db->query();

	#get the number result for this
	$db->SQL = "SELECT id FROM ebb_users WHERE active='1'";
	$num = $db->affectedRows();
}
#output pagination.
$pagenation = pagination('');

#load and setup memberlist template files.
$tpl = new templateEngine($style, "memberlist_head");

#setup tag code.
$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[members]",
	"PAGENATION" => "$pagenation",
	"LANG-USERNAME" => "$lang[username]",
	"LANG-POSTCOUNT" => "$lang[posts]",
	"LANG-LOCATION" => "$lang[location]",
	"LANG-REGISTRATIONDATE" => "$lang[joindate]"));

echo $tpl->outputHtml();

#setup memberlist
while ($uList = mysql_fetch_assoc ($query)){

	#format date
    $joinDate = formatTime($timeFormat, $uList['Date_Joined'], $gmt);

	$tpl = new templateEngine($style, "memberlist");

	#setup tag code.
	$tpl->parseTags(array(
	"USERNAME" => "$uList[Username]",
	"LANG-POSTCOUNT" => "$lang[posts]",
	"POSTCOUNT" => "$uList[Post_Count]",
	"LOCATION" => "$uList[Location]",
	"REGISTATIONDATE" => "$joinDate"));

	echo $tpl->outputHtml();
}

$tpl = new templateEngine($style, "memberlist_foot");

echo $tpl->outputHtml();

#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();

ob_end_flush();
?>
