<?php
define('IN_EBB', true);
/**
Filename: index.php
Last Modified: 11/26/2010

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";
include_once FULLPATH."/includes/topic_function.php";
require_once FULLPATH."/includes/boardlisting.php";

$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
	"TITLE" => "$title",
    "PAGETITLE" => "$lang[index]",
    "LANG-HELP-TITLE" => "$help[indextitle]",
    "LANG-HELP-BODY" => "$help[indexbody]",
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
	$tpl->removeBlock("searchBar");
	
	#update guest's activity.
	echo update_whosonline_guest();
}
#output top template file.
echo $tpl->outputHtml();

#show InfoBox, if enabled.
if ($boardPref->getPreferenceValue("infobox_status") == 1){

	$infoPnl = informationPanel();
	//load template
	$tpl = new templateEngine($style, "announcement");
	$tpl->parseTags(array(
	"LANG-TICKER" => "$lang[ticker_txt]",
	"ANNOUNCEMENT" => "$infoPnl"));
	echo $tpl->outputHtml();
}

#setup boardlisting class.
$boardList = new boardList();
$boardList->getBoardList();

#get board stats.
$memberCount = boardStats("member");
$topicCount = boardStats("topic");
$postCount = boardStats("post");
$newUser = boardStats("newuser");

//call the whos online function
$online = whosonline();

//load board stat-icon
$tpl = new templateEngine($style, "boardstat");
$tpl->parseTags(array(
"LANG-BOARDSTAT" => "$lang[boardstatus]",
"LANG-ICONGUIDE" => "$lang[iconguide]",
"LANG-NEWESTMEMBER" => "$lang[newestmember]",
"NEWESTMEMBER" => "$newUser[Username]",
"TOTAL-TOPIC" => "$topicCount",
"LANG-TOTALTOPIC" => "$lang[topics]",
"TOTAL-POST" => "$postCount",
"LANG-TOTALPOST" => "$lang[posts]",
"TOTAL-USER" => "$memberCount",
"LANG-TOTALUSER" => "$lang[membernum]",
"LANG-NEWPOST" => "$lang[newpost]",
"LANG-OLDPOST" => "$lang[oldpost]"));
echo $tpl->outputHtml();

//update online data.
$timeout = time() - 300;

//delete any old entries
$db->SQL = "DELETE FROM ebb_online WHERE time<$timeout";
$db->query();

//grab total online currently
$db->SQL = "SELECT DISTINCT Username FROM ebb_online WHERE ip=''";
$online_logged_count = $db->affectedRows();

$db->SQL = "SELECT DISTINCT ip FROM ebb_online WHERE Username=''";
$online_guest_count = $db->affectedRows();

//output who's online.
$tpl = new templateEngine($style, "whosonline");
$tpl->parseTags(array(
  "LANG-WHOSONLINE" => "$lang[whosonline]",
  "LANG-ONLINEKEY" => "$lang[onlinekey]",
  "LOGGED-ONLINE" => "$online_logged_count",
  "LANG-LOGGED-ONLINE" => "$lang[membernum]",
  "GUEST-ONLINE" => "$online_guest_count",
  "LANG-GUEST-ONLINE" => "$lang[guestonline]",
  "WHOSONLINE"=> "$online"));
echo $tpl->outputHtml();

#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();

ob_end_flush();
?>
