<?php
define('IN_EBB', true);
/**
Filename: viewboard.php
Last Modified: 06/28/2012

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
#see if Board ID was declared, if not terminate any further outputting.
if((!isset($_GET['bid'])) or (empty($_GET['bid']))){
	$error = new notifySys($lang['nobid'], true);
	$error->genericError();
}else{
	$bid = $db->filterMySQL(var_cleanup($_GET['bid']));
}
//check to see if board exists or not and if it doesn't kill the program
$db->SQL = "select id from ebb_boards WHERE id='$bid'";
$checkboard = $db->affectedRows();

if ($checkboard == 0){
	$error = new notifySys($lang['doesntexist'], true);
	$error->genericError();
}
#get board name.
$db->SQL = "SELECT Board, type FROM ebb_boards WHERE id='$bid'";
$rules = $db->fetchResults();


#see if board is a category board.
if($rules['type'] == 1){
	#send user to main page.
	header("Location: index.php");
}
#make title variable.
$pagetitle = $lang['viewboard']." - ".$rules['Board'];

#output page header.
$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$pagetitle",
    "LANG-HELP-TITLE" => "$help[nohelptitle]",
    "LANG-HELP-BODY" => "$help[nohelpbody]",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

#setup menu template.
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

//record user coming in here
$read_ct = readBoardStat($bid, $logged_user);
if (($read_ct == 0) AND ($logged_user !== "guest")){
	$db->SQL = "INSERT INTO ebb_read_board (Board, User) VALUES('$bid', '$logged_user')";
	$db->query();
}

#check to see if any sub-boards exist.
$db->SQL = "SELECT id FROM ebb_boards WHERE type='3' and Category='$bid'";
$subboardChk = $db->affectedRows();

if($subboardChk == 1){
	#setup boardlisting class.
	$subBoardList = new boardList();
	$subBoardList->getSubBoardList($bid);
}
//check for the posting rule.
$db->SQL = "SELECT B_Read, B_Post, B_Reply, B_Poll, B_Vote FROM ebb_board_access WHERE B_id='$bid'";
$boardRule = $db->fetchResults();

//start pagenation.
$count = 0;
$count2 = 0;
//pagination
if(!isset($_GET['pg'])){
    $pg = 1;
}else{
    $pg = $db->filterMySQL(var_cleanup($_GET['pg']));
}
#setup perPg setting value.
$perPg = $boardPref->getPreferenceValue("per_page");

// Figure out the limit for the query based on the current page number.
$from = (($pg * $perPg) - $perPg);
// Figure out the total number of results in DB:
$db->SQL = "SELECT bid, last_update, Topic, author, Posted_User, Post_Link, tid, Views, Type, important, Locked FROM ebb_topics WHERE bid='$bid' ORDER BY important DESC, last_update DESC LIMIT $from, $perPg";
$query = $db->query();

$db->SQL = "SELECT bid, last_update, Topic, author, Posted_User, Post_Link, tid, Views, Type, important, Locked FROM ebb_topics WHERE bid='$bid' ORDER BY important DESC, last_update DESC";
$num = $db->affectedRows();

#output pagination.
$pagenation = pagination("bid=$bid&amp;");

#setup boardlisting class.
$boardList = new boardList();
$boardList->getBoardTopics($bid);

#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();

ob_end_flush();
?>
