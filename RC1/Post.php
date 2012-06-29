<?php
define('IN_EBB', true);
/**
Filename: Post.php
Last Modified: 06/28/2012

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";

if(isset($_GET['mode'])){
	$mode = var_cleanup($_GET['mode']);
}else{
	$mode = '';
}
//determine the page title.
if ($mode == "topic"){
	$pageTitle = $lang['newtopic'];
	$helpTitle = $help['attachtitle'];
	$helpBody = $help['attachbody'];
}elseif ($mode == "reply"){
	$pageTitle = $lang['reply'];
	$helpTitle = $help['attachtitle'];
	$helpBody = $help['attachbody'];
}else{
	redirect('index.php', false, 0);
}

#load header template file.
$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$pageTitle",
    "LANG-HELP-TITLE" => "$helpTitle",
    "LANG-HELP-BODY" => "$helpBody",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

//see if this is a guest trying to post.
if ($logged_user == "guest"){
    redirect('login.php', false, 0);
}

//output top
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

#see if Board ID was declared, if not terminate any further outputting.
if((!isset($_GET['bid'])) or (empty($_GET['bid']))){
	$displayMsg = new notifySys($lang['nobid'], true);
	$displayMsg->displayError();
}else{
	$bid = $db->filterMySQL(var_cleanup($_GET['bid']));
}
#get board bbcode rules.
$db->SQL = "SELECT Smiles, BBcode, Image, type, Board FROM ebb_boards WHERE id='$bid'";
$bName = $db->fetchResults();

//get posting rules.
$db->SQL = "select B_Post, B_Reply, B_Poll from ebb_board_access WHERE B_id='$bid'";
$boardAccess = $db->fetchResults();

#see if user is trying to post on category-type board.
if($bName['type'] == 1){
	redirect('index.php', false, 0);
}

//set var to posting rules.
$allowsmile = $bName['Smiles'];
$allowbbcode = $bName['BBcode'];
$allowimg = $bName['Image'];

#smiles check
if($allowsmile == 1){
	$allowSmiles = $lang['on'];
}else{
	$allowSmiles = $lang['off'];
}

#bbcode check
if($allowbbcode == 1){
	$allowBbcode = $lang['on'];
}else{
	$allowBbcode = $lang['off'];
}

#[img] check
if($allowimg == 1){
	$allowImg = $lang['on'];
}else{
	$allowImg = $lang['off'];
}

switch ($mode){
case 'topic':
	#get polltopic variable from querystring.
    if(isset($_GET['polltopic'])){
  		$pollTopic = var_cleanup($_GET['polltopic']);
	}else{
		$pollTopic = false;
	}

	#display error if user cant post.
	if($groupPolicy->validateAccess(1, 37) == false){
	    #see if the board-based check will veto the group access.
        if($groupPolicy->validateAccess(0, $boardAccess['B_Post']) == false){
        	$displayMsg = new notifySys($lang['nowrite'], true);
			$displayMsg->displayError();
        }
	}
	
	#see if this user can post a poll on this board.
	if(($groupPolicy->validateAccess(1, 35) == false) AND ($pollTopic == true)){
		#see if the board-based check will veto the group access.
		if(($groupPolicy->validateAccess(0, $boardAccess['B_Poll']) == false) AND ($pollTopic == true)){
			$displayMsg = new notifySys($lang['nopoll'], true);
			$displayMsg->displayError();

			#define poll var.
			$allowPoll = false;
		}else{
			if($pollTopic == true){
				#define poll var.
				$allowPoll = true;
			}else{
				#define poll var.
				$allowPoll = false;
			}
		}
	}else{
		if($pollTopic == true){
			#define poll var.
			$allowPoll = true;
		}else{
			#define poll var.
			$allowPoll = false;
		}
	}

	#get format controls.
	$smile = form_smiles();

	#setup upload limit.
	if($groupPolicy->validateAccess(1, 26) == false){
		$uploadLimit = 0;
	}else{
		$uploadLimit = $boardPref->getPreferenceValue("upload_limit");
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

	#load reply template.
	$tpl = new templateEngine($style, "postnewtopic");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[newtopic]",
	"POSTINGBOARD" => "$bName[Board]",
	"LANG-POSTINGRULES" => "$lang[postingrules]",
	"LANG-ALLOWSMILES" => "$lang[smiles]",
	"ALLOWSMILES" => "$allowSmiles",
	"LANG-ALLOWBBCODE" => "$lang[bbcode]",
	"ALLOWBBCODE" => "$allowBbcode",
	"LANG-ALLOWIMG" => "$lang[img]",
	"ALLOWIMG" => "$allowImg",
	"BID" => "$bid",
	"POLLOPTION" => "$pollTopic",	
	"LANG-SMILES" => "$lang[moresmiles]",
	"SMILES" => "$smile",
	"LANG-USERNAME" => "$lang[username]",
	"USERNAME" => "$logged_user",
	"LANG-TOPIC" => "$lang[topic]",
	"LANG-UPLOAD" => "$lang[uploadfile]",
	"LANG-CLEAR" => "$lang[clearfile]",
	"LANG-VIEWFILES" => "$lang[viewfiles]",
	"ATTACHMENTLIMIT" => "$uploadLimit",
	"LANG-DISABLERTF" => "$lang[disablertf]",
	"LANG-OPTIONS" => "$lang[options]",
	"LANG-POSTTYPE" => "$lang[type]",
	"LANG-IMPORTANT" => "$lang[important]",
	"LANG-NORMAL" => "$lang[normal]",
	"LANG-NOTIFY" => "$lang[notify]",
	"LANG-DISABLESMILES" => "$lang[disablesmiles]",
	"LANG-DISABLEBBCODE" => "$lang[disablebbcode]",
	"LANG-POLL" => "$lang[polltext]",
	"LANG-QUESTION" => "$lang[question]",
	"LANG-OPTION1" => "$lang[pollopt1]",
	"LANG-OPTION2" => "$lang[pollopt2]",
	"LANG-OPTION3" => "$lang[pollopt3]",
	"LANG-OPTION4" => "$lang[pollopt4]",
	"LANG-OPTION5" => "$lang[pollopt5]",
	"LANG-OPTION6" => "$lang[pollopt6]",
	"LANG-OPTION7" => "$lang[pollopt7]",
	"LANG-OPTION8" => "$lang[pollopt8]",
	"LANG-OPTION9" => "$lang[pollopt9]",
	"LANG-OPTION10" => "$lang[pollopt10]",
	"LANG-POSTTOPIC" => "$lang[posttopic]"));

	#is spellcheck enabled?
	if($boardPref->getPreferenceValue("spellcheck") == 0){
		$tpl->removeBlock("spellchk");
	}else{
		$tpl->removeBlock("nospell");
	}

	#can the user upload a file with this reply?
	if($groupPolicy->validateAccess(1, 26) == false){
		$tpl->removeBlock("upload");
	}

	#see if user can mark topics as important.
	if($groupPolicy->validateAccess(1, 39) == true){
		$tpl->removeBlock("normal");
	}else{
		$tpl->removeBlock("important");
	}
	
	#see if user can post a poll and if so, are they trying to.
	if(($pollTopic == true) AND ($allowPoll == false)){
		$tpl->removeBlock("poll");
	}elseif($pollTopic == false){
		$tpl->removeBlock("poll");
	}
	
	echo $tpl->outputHtml();
break;
case 'reply':
	#see if Topic ID was declared, if not terminate any further outputting.
	if((!isset($_GET['tid'])) or (empty($_GET['tid']))){
		$displayMsg = new notifySys($lang['notid'], true);
		$displayMsg->displayError();
	}else{
		$tid = $db->filterMySQL(var_cleanup($_GET['tid']));
	}
	#see if Post ID was declared, if not leave it blank as we're assuming no replies are yet created.
	if((!isset($_GET['pid'])) or (empty($_GET['pid']))){
		$pid = '';
	}else{
		$pid = $db->filterMySQL(var_cleanup($_GET['pid']));
	}
	#see if a page number is found, otherwise its just page 1.
	if(isset($_GET['pg'])){
		$pg = $db->filterMySQL(var_cleanup($_GET['pg']));
	}else{
		$pg = 1; 
	}

	#display error if user cant post.
	if($groupPolicy->validateAccess(1, 38) == false){
		#see if board-based access will veto group-based access.
        if($groupPolicy->validateAccess(0, $boardAccess['B_Reply']) == false){
			$displayMsg = new notifySys($lang['nowrite'], true);
			$displayMsg->displayError();
		}
	}

	#see if user has quoted someone.
	if(isset($_GET['quser'])){
		$quser = $db->filterMySQL(var_cleanup($_GET['quser']));
		$type = $db->filterMySQL(var_cleanup($_GET['type']));
		if((empty($quser)) and (empty($type))){
			$quotetxt = ''; 
		}else{
			if($type == 1){ 
				#get topic post from requested topic.
				$db->SQL = "SELECT Body FROM ebb_topics WHERE tid='$tid' AND author='$quser' LIMIT 1";
				$quoteData = $db->fetchResults();

			}else{
			    #get topic post from requested topic.
				$db->SQL = "SELECT Body FROM ebb_posts WHERE pid='$pid' AND re_author='$quser' LIMIT 1";
				$quoteData = $db->fetchResults();

			}
			#setup quote for 
			$quotetxt = '[quote='.$quser.']'.$quoteData['Body'].'[/quote]';
		}
	}else{
		$quotetxt = ''; 
	}

	#get format controls.
	$smile = form_smiles();

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

	#load reply template.
	$tpl = new templateEngine($style, "postreply");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[newtopic]",
	"POSTINGBOARD" => "$bName[Board]",
	"LANG-POSTINGRULES" => "$lang[postingrules]",
	"LANG-ALLOWSMILES" => "$lang[smiles]",
	"ALLOWSMILES" => "$allowSmiles",
	"LANG-ALLOWBBCODE" => "$lang[bbcode]",
	"ALLOWBBCODE" => "$allowBbcode",
	"LANG-ALLOWIMG" => "$lang[img]",
	"ALLOWIMG" => "$allowImg",
	"BID" => "$bid",
	"TID" => "$tid",
	"QUOTES" => "$quotetxt",
	"LANG-SMILES" => "$lang[moresmiles]",
	"SMILES" => "$smile",
	"LANG-USERNAME" => "$lang[username]",
	"USERNAME" => "$logged_user",
	"LANG-TOPIC" => "$lang[topic]",
	"TOPIC" => "$topic[Topic]",
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
	"PAGE" => "$pg",
	"LANG-RELOAD" => "$lang[btnreload]",
	"LANG-TOPICREVIEW" => "$lang[topicreview]"));

	#can the user upload a file with this reply?
	if($groupPolicy->validateAccess(1, 26) == false){
		$tpl->removeBlock("upload");
	}

	echo $tpl->outputHtml();
break;
default:
	redirect('index.php', false, 0);
}
#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();

ob_end_flush();
?>
