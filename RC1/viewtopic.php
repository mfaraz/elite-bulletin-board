<?php
define('IN_EBB', true);
/**
Filename: viewtopic.php
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
include FULLPATH."/includes/attachmentMgr.php";

#see if Board ID was declared, if not terminate any further outputting.
//@TODO remove boardID from this page, bid is no longer vitial as of v3.
if((!isset($_GET['bid'])) or (empty($_GET['bid']))){
	$error = new notifySys($lang['nobid'], true);
	$error->genericError();
}else{
	$bid = $db->filterMySQL(var_cleanup($_GET['bid']));
}
#see if Topic ID was declared, if not terminate any further outputting.
if((!isset($_GET['tid'])) or (empty($_GET['tid']))){
	$error = new notifySys($lang['notid'], true);
	$error->genericError();
}else{
	$tid = $db->filterMySQL(var_cleanup($_GET['tid']));
}
#see if Post ID was declared, if not leave it blank as we're assuming no replies are yet created.
if((!isset($_GET['pid'])) or (empty($_GET['pid']))){
	$pid = '';
}else{
	$pid = $db->filterMySQL(var_cleanup($_GET['pid']));
}
#get topic & board name.
$db->SQL = "SELECT author, Topic, Body, Views, Locked, IP, Original_Date, Type, disable_smiles, disable_bbcode FROM ebb_topics WHERE tid='$tid'";
$checktopic = $db->affectedRows();
$tName = $db->fetchResults();

#obtain board data.
$db->SQL = "SELECT Board, Smiles, BBcode, Image FROM ebb_boards WHERE id='$bid'";
$checkboard = $db->affectedRows();
$bName = $db->fetchResults();

#page title.
$pageTitle = $lang['viewtopic']." - ".$tName['Topic'];

#output page header.
$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$pageTitle",
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
$read_ct = readTopicStat($tid, $logged_user);
if (($read_ct == 0) AND ($logged_user !== "guest")){
	$db->SQL = "INSERT INTO ebb_read_topic (Topic, User) VALUES('$tid', '$logged_user')";
	$db->query();
}
//check to see if topic exists or not and if it doesn't kill the program
if (($checkboard == 0) or ($checktopic == 0)){
	$error = new notifySys($lang['doesntexist'], true);
	$error->displayError();
}
//increment the total view of the topic by one(if user is NOT topic starter).
$addone = $tName['Views'] + 1;
$db->SQL = "UPDATE ebb_topics SET Views='$addone' WHERE tid='$tid' AND author!='$logged_user'";
$db->query();

//update the status of the topic watch.
$db->SQL = "SELECT status FROM ebb_topic_watch WHERE username='$logged_user' AND tid='$tid'";
$t_watch = $db->fetchResults();

if ($t_watch['status'] == "Unread"){
	$db->SQL = "UPDATE ebb_topic_watch SET status='Read' WHERE username='$logged_user' AND tid='$tid'";
	$db->query();

}

//check for the posting rule.
$db->SQL = "SELECT B_Reply FROM ebb_board_access WHERE B_id='$bid'";
$boardRule = $db->fetchResults();

//begin pagenation
$count = 0;
$count2 = 0;
if(!isset($_GET['pg']) || empty($_GET['pg'])){
	$pg = 1;
}else{
	$pg = $db->filterMySQL(var_cleanup($_GET['pg']));
}

#call board setting function.
$perPg = $boardPref->getPreferenceValue("per_page");

// Figure out the limit for the query based on the current page number.
$from = (($pg * $perPg) - $perPg);

// Figure out the total number of results in DB:
$db->SQL = "SELECT re_author, pid, tid, bid, Body, IP, Original_Date, disable_smiles, disable_bbcode FROM ebb_posts WHERE tid='$tid' LIMIT $from, $perPg";
$query = $db->query();

$db->SQL = "SELECT pid FROM ebb_posts WHERE tid='$tid'";
$num = $db->affectedRows();

#output pagination.
$pagenation = pagination("bid=$bid&amp;tid=$tid&amp;");

#setup boardlisting class.
$boardList = new boardList();
$boardList->getTopic($tid);

#see if any replies are linked to this topic.
if($num > 0){
$boardList = new boardList();
$boardList->getReplies();
}

#see if user can reply to this topic.
if($groupAccess == 0) {
	$showReplyBox = FALSE;
} elseif ($groupPolicy->validateAccess(0, $boardRule['B_Reply']) == false) {
	$showReplyBox = FALSE;
} elseif ($groupPolicy->validateAccess(1, 38) == false) {
	$showReplyBox = FALSE;
} else {
	//see if a topic is locked before granting reply rights.
	if ($tName['Locked'] == 0) {
		$showReplyBox = TRUE;
	} else {
		$showReplyBox = FALSE;
	}
}

//see if user can post a reply.
if($showReplyBox){
	//bbcode buttons
	$smile = form_smiles();

	#setup upload limit.
	if($groupPolicy->validateAccess(1, 26) == false){
		$uploadLimit = 0;
	}else{
		$uploadLimit = $boardPref->getPreferenceValue("upload_limit");
	}

	#load reply template.
	$tpl = new templateEngine($style, "instantreply");
	$tpl->parseTags(array(
		"LANG-INSTANTREPLY" => "$lang[instantreply]",
		"BID" => "$bid",
		"TID" => "$tid",
		"LANG-SMILES" => "$lang[moresmiles]",
		"SMILES" => "$smile",
		"LANG-UPLOAD" => "$lang[uploadfile]",
		"LANG-CLEAR" => "$lang[clearfile]",
		"LANG-VIEWFILES" => "$lang[viewfiles]",
		"ATTACHMENTLIMIT" => "$uploadLimit",
		"LANG-DISABLERTF" => "$lang[disablertf]",
		"LANG-OPTIONS" => "$lang[options]",
		"LANG-NOTIFY" => "$lang[notify]",
		"LANG-DISABLESMILES" => "$lang[disablesmiles]",
		"LANG-DISABLEBBCODE" => "$lang[disablebbcode]",
		"LANG-REPLY" => "$lang[btnreply]",
		"PAGE" => "$pg"));
		
	#can the user upload a file with this reply?
	if($groupPolicy->validateAccess(1, 26) == false){
		$tpl->removeBlock("upload");
	}

	echo $tpl->outputHtml();
}

#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();
ob_end_flush();
?>
