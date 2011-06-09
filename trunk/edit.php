<?php
define('IN_EBB', true);
/**
Filename: edit.php
Last Modified: 2/22/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";
include_once FULLPATH."/includes/attachmentMgr.php";


$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$lang[edittopic]",
    "LANG-HELP-TITLE" => "$help[nohelptitle]",
    "LANG-HELP-BODY" => "$help[nohelpbody]",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

//output top
if($logged_user == "guest"){
	redirect('login.php', false, 0);
}else{
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

#see if Board ID was declared, if not terminate any further outputting.
if((!isset($_GET['bid'])) or (empty($_GET['bid']))){
	$displayMsg = new notifySys($lang['nobid'], true);
	$displayMsg->displayError();
}else{
	$bid = $db->filterMySQL($_GET['bid']);
}
#see if Topic ID was declared, if not terminate any further outputting.
if((!isset($_GET['tid'])) or (empty($_GET['tid']))){
	$displayMsg = new notifySys($lang['notid'], true);
	$displayMsg->displayError();
}else{
	$tid = $db->filterMySQL($_GET['tid']);
}
#see if Post ID was declared, if not leave it blank as we're assuming no replies are yet created.
if(!isset($_GET['pid'])){
	$pid = '';
}else{
	$pid = $db->filterMySQL($_GET['pid']);
}

if(isset($_GET['mode'])){
	$mode = var_cleanup($_GET['mode']);
}else{
	$mode = ''; 
}
//get board rules.
$db->SQL = "SELECT Smiles, BBcode, Image FROM ebb_boards WHERE id='$bid'";
$bName = $db->fetchResults();

$allowsmile = $bName['Smiles'];
$allowbbcode = $bName['BBcode'];
$allowimg = $bName['Image'];

#see if user can use attachments.
$permissionChkAttach = $groupPolicy->validateAccess(1, 26);
switch ($mode){
case 'edit_topic':
	#sql to get authors name.
	$db->SQL = "SELECT author FROM ebb_topics WHERE tid='$tid'";
	$topic = $db->fetchResults();

	#check to see if this user is the author of this post.otherwise this action will be canceled.
	if(($groupAccess == 1) OR ($groupAccess == 2)){
		//find what usergroup this user belongs to.
		$userGroupPolicy = new groupPolicy($topic['author']);
		$userGroupRank = $userGroupPolicy->groupAccessLevel();

		#if author is an admin, mods can't edit the post.
		if(($groupAccess == 2) and ($userGroupRank == 1)){
			$canEdit = false;
		}else{
			if ($groupPolicy->validateAccess(1, 20) == true){
				$canEdit = true;
			}else{
				$canEdit = false;
			}
		}
	}elseif($groupAccess == 3){
		if (($logged_user == $topic['author']) AND ($groupPolicy->validateAccess(1, 20) == true)){
			$canEdit = true;
		}else{
			$canEdit = false;
		}	
	}
	#check to see if user can edit post.
	if ($canEdit == true){
		//get form values.
		$topicSubject = $db->filterMySQL(var_cleanup($_POST['topic']));
		$postBody = $db->filterMySQL(var_cleanup($_POST['body']));
		$subscribe = $db->filterMySQL(var_cleanup($_POST['subscribe']));
		$noSmile = $db->filterMySQL(var_cleanup($_POST['no_smile']));
		$noBbcode = $db->filterMySQL(var_cleanup($_POST['no_bbcode']));

		//spam check.
		$topic_chk = language_filter($topicSubject, 2);
		$post_chk = language_filter($postBody, 2);
		//do some error-checking.	
		if (empty($topicSubject)){
			#setup error session.
			$_SESSION['errors'] = $lang['nosubject'];

			#direct user.
			redirect('edit.php?mode=edittopic&amp;bid='.$bid.'&amp;tid='.$tid, false, 0);
		}
		if (empty($postBody)){
			#setup error session.
			$_SESSION['errors'] = $lang['nopost'];

			#direct user.
   			redirect('edit.php?mode=edittopic&amp;bid='.$bid.'&amp;tid='.$tid, false, 0);
		}
		if(strlen($topicSubject) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longsubject'];

			#direct user.
   			redirect('edit.php?mode=edittopic&amp;bid='.$bid.'&amp;tid='.$tid, false, 0);
		}
		//set the disable variables to 0 if not selected.
		if(empty($noSmile)){
			$noSmile = 0;
		}else{
			$noSmile = 1;
		}
		if(empty($noBbcode)){
			$noBbcode = 0;
		}else{
			$noBbcode = 1;
		}
		//see if this user already subscribed to this topic.
		$db->SQL = "SELECT tid FROM ebb_topic_watch WHERE username='$logged_user' AND tid='$tid'";
		$check_subscription = $db->affectedRows();

		if (($check_subscription == 0) AND ($subscribe == "yes")){
			//add user to list
			$db->SQL = "INSERT INTO ebb_topic_watch (username, tid, status) VALUES('$logged_user', '$tid', 'Unread')";
			$db->query();
		}
		//update the topic.
		$db->SQL = "UPDATE ebb_topics SET Topic='$topicSubject', Body='$postBody', disable_smiles='$noSmile', disable_bbcode='$noBbcode' WHERE tid='$tid'";
		$db->query();

		if($permissionChkAttach == 1){
			#see if user uploaded a file, if so lets assign the file to the topic.
			$db->SQL = "SELECT id FROM ebb_attachments WHERE Username='$logged_user' AND tid='0' AND pid='0'";
			$attach_ct = $db->affectedRows();
			$attachQ = $db->query();

			if($attach_ct > 0){
			    while($attachID = mysql_fetch_assoc($attachQ)){
					#add attachment to db for listing purpose.
					$db->SQL = "UPDATE ebb_attachments SET tid='$tid' WHERE id='$attachID[id]'";
					$db->query();
				}

				//direct user to topic.
				redirect('viewtopic.php?bid='.$bid.'&amp;tid='.$tid, false, 0);
			}else{
				//direct user to topic.
				redirect('viewtopic.php?bid='.$bid.'&amp;tid='.$tid, false, 0);
			}
		}else{
			//direct user to topic.
			redirect('viewtopic.php?bid='.$bid.'&amp;tid='.$tid, false, 0);
		}
	}else{
		$displayMsg = new notifySys($lang['accessdenied'], true);
		$displayMsg->displayError();
	}
break;
case 'edit_post':
	#sql to get authors name.
	$db->SQL = "SELECT re_author FROM ebb_posts WHERE pid='$pid'";
	$postAuthor = $db->fetchResults();

	//check to see if this user is the author of this post.otherwise this action will be canceled.
	if(($groupAccess == 1) OR ($groupAccess == 2)){
		//find what usergroup this user belongs to.
		$userGroupPolicy = new groupPolicy($postAuthor['re_author']);
		$userGroupRank = $userGroupPolicy->groupAccessLevel();

		#if author is an admin, mods can't edit the post.
		if(($groupAccess == 2) and ($userGroupRank == 1)){
			$canEdit = false;
		}else{
			if ($groupPolicy->validateAccess(1, 20) == true){
				$canEdit = true;
			}else{
				$canEdit = false;
			}
		}
	}elseif($groupAccess == 3){
		if (($logged_user == $postAuthor['re_author']) AND ($groupPolicy->validateAccess(1, 20) == true)){
			$canEdit = true;
		}else{
			$canEdit = false;
		}	
	}
	
	#check to see if user can edit post.
	if ($canEdit == true){
		//get form values.
		$reply_post = $db->filterMySQL(var_cleanup($_POST['reply_post']));
		$subscribe = $db->filterMySQL($_POST['subscribe']);
		$noSmile = $db->filterMySQL($_POST['no_smile']);
		$noBbcode = $db->filterMySQL($_POST['no_bbcode']);

		//spam check.
		$post_chk = language_filter($reply_post, 2);

		//error-checking.
		if (empty($reply_post)){
			#setup error session.
			$_SESSION['errors'] = $lang['nopost'];

			#direct user.
			redirect('edit.php?mode=editpost&amp;bid='.$bid.'&amp;tid='.$tid.'&amp;pid='.$pid, false, 0);
		}
		//set the disable variables to 0 if not selected.
		if(empty($noSmile)){
			$noSmile = 0;
		}else{
			$noSmile = 1;
		}
		if(empty($noBbcode)){
			$noBbcode = 0;
		}else{
			$noBbcode = 1;
		}

		//see if this user already subscribed to this topic.
		$db->SQL = "SELECT tid FROM ebb_topic_watch WHERE username='$logged_user' AND tid='$tid'";
		$check_subscription = $db->affectedRows();

		if (($check_subscription == 0) AND ($subscribe == "yes")){
			//add user to list
			$db->SQL = "INSERT INTO ebb_topic_watch (username, tid, status) VALUES('$logged_user', '$tid', 'Unread')";
			$db->query();
		}

		//update post
		$db->SQL = "UPDATE ebb_posts SET Body='$reply_post', disable_smiles='$noSmile', disable_bbcode='$noBbcode' WHERE pid='$pid'";
		$db->query();

		if($permissionChkAttach == 1){
			#see if user uploaded a file, if so lets assign the file to the topic.
			$db->SQL = "SELECT id FROM ebb_attachments WHERE Username='$logged_user' AND tid='0' AND pid='0'";
			$attach_ct = $db->affectedRows();
			$attachQ = $db->query();

			if($attach_ct > 0){
				while($attachID = mysql_fetch_assoc($attachQ)){
					#add attachment to db for listing purpose.
					$db->SQL = "UPDATE ebb_attachments SET tid='$tid' WHERE id='$attachID[id]'";
					$db->query();
				}

				//direct user to topic.
				redirect('viewtopic.php?bid='.$bid.'&amp;tid='.$tid, false, 0);
			}else{
				//direct user to topic.
				redirect('viewtopic.php?bid='.$bid.'&amp;tid='.$tid, false, 0);
			}
		}else{
			//direct user to topic.
			redirect('viewtopic.php?bid='.$bid.'&amp;tid='.$tid, false, 0);
		}
	}else{
		$displayMsg = new notifySys($lang['accessdenied'], true);
		$displayMsg->displayError();
	}
break;
case 'editpost':
	//see if topic exist
	$db->SQL = "SELECT re_author, Body, disable_smiles, disable_bbcode FROM ebb_posts WHERE pid='$pid'";
	$checkboard = $db->affectedRows();
	$postData = $db->fetchResults();

	if ($checkboard == 0){
		$displayMsg = new notifySys($lang['doesntexist'], true);
		$displayMsg->displayError();
	}

	#check to see if this user is the author of this post.otherwise this action will be canceled.
	if(($groupAccess == 1) OR ($groupAccess == 2)){
		//find what usergroup this user belongs to.
		$userGroupPolicy = new groupPolicy($postData['re_author']);
		$userGroupRank = $userGroupPolicy->groupAccessLevel();

		#if author is an admin, mods can't edit the post.
		if(($groupAccess == 2) and ($userGroupRank == 1)){
			$canEdit = false;
		}else{
			if ($groupPolicy->validateAccess(1, 20) == true){
				$canEdit = true;
			}else{
				$canEdit = false;
			}
		}
	}elseif($groupAccess == 3){
		if (($logged_user == $postData['re_author']) AND ($groupPolicy->validateAccess(1, 20) == true)){
			$canEdit = true;
		}else{
			$canEdit = false;
		}
	}
	#can user edit post?
	if ($canEdit == true){
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

		#formatting buttons.
		$smile = form_smiles();
		$attachMgr = new attachmentMgr();

		//get subscription status.
		$db->SQL = "SELECT tid FROM ebb_topic_watch WHERE username='$logged_user' AND tid='$tid'";
		$check_subscription = $db->affectedRows();

		#setup upload limit.
		if($groupPolicy->validateAccess(1, 26) == false){
			$uploadLimit = 0;
		}else{
			$uploadLimit = ($boardPref->getPreferenceValue("upload_limit") - $attachMgr->uploadCount("Post", $pid));
		}

		#load reply template.
		$tpl = new templateEngine($style, "editpost");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[editpost]",
		"BID" => "$bid",
		"TID" => "$tid",
		"PID" => "$pid",
		"BBCODE" => "$bbcode",
		"LANG-SMILES" => "$lang[moresmiles]",
		"SMILES" => "$smile",
		"LANG-USERNAME" => "$lang[username]",
		"USERNAME" => "$logged_user",
		"BODY" => "$postData[Body]",
		"LANG-UPLOAD" => "$lang[uploadfile]",
		"LANG-CLEAR" => "$lang[clearfile]",
		"LANG-VIEWFILES" => "$lang[viewfiles]",
		"ATTACHMENTLIMIT" => "$uploadLimit",
		"LANG-DISABLERTF" => "$lang[disablertf]",
		"LANG-OPTIONS" => "$lang[options]",
		"LANG-NOTIFY" => "$lang[notify]",
		"LANG-DISABLESMILES" => "$lang[disablesmiles]",
		"LANG-DISABLEBBCODE" => "$lang[disablebbcode]",
		"LANG-EDITTOPIC" => "$lang[editpost]"));

		#can the user upload a file with this reply?
		if($groupPolicy->validateAccess(1, 26) == false){
			$tpl->removeBlock("upload");
		}

		//check for subscription status.
		if($check_subscription == 1){
   			$tpl->removeBlock("subscribe");
		}else{
			$tpl->removeBlock("subscribed");
		}

		//check for smile status.
		if($postData['disable_smiles'] == 1){
   			$tpl->removeBlock("disablesmiles");
		}else{
   			$tpl->removeBlock("disabledsmiles");
		}

		//check for bbcode status
		if($postData['disable_bbcode'] == 1){
   			$tpl->removeBlock("disablebbcode");
		}else{
   			$tpl->removeBlock("disabledbbcode");
		}

		echo $tpl->outputHtml();
	}else{
		$displayMsg = new notifySys($lang['accessdenied'], true);
		$displayMsg->displayError();
	}
break;
case 'edittopic';
	$db->SQL = "select author, Topic, Body, important, disable_bbcode, disable_smiles from ebb_topics WHERE tid='$tid'";
	$checkboard = $db->affectedRows();
	$topicData = $db->fetchResults();

	if ($checkboard == 0){
		$displayMsg = new notifySys($lang['doesntexist'], true);
		$displayMsg->displayError();
	}
	//get subscription status.
	$db->SQL = "Select tid from ebb_topic_watch where username='$logged_user' and tid='$tid'";
	$check_subscription = $db->affectedRows();

	#check to see if this user is the author of this post.otherwise this action will be canceled.
	if(($groupAccess == 1) OR ($groupAccess == 2)){
		//find what usergroup this user belongs to.
		$userGroupPolicy = new groupPolicy($topicData['author']);
		$userGroupRank = $userGroupPolicy->groupAccessLevel();

		#if author is an admin, mods can't edit the post.
		if(($groupAccess == 2) and ($userGroupRank == 1)){
			$canEdit = false;
		}else{
			if ($groupPolicy->validateAccess(1, 20) == true){
				$canEdit = true;
			}else{
				$canEdit = false;
			}
		}
	}elseif($groupAccess == 3){
		if (($logged_user == $topicData['author']) AND ($groupPolicy->validateAccess(1, 20) == true)){
			$canEdit = true;
		}else{
			$canEdit = false;
		}
	}
	#can user edit post?
	if ($canEdit == true){
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
		
		#formatting buttons.
		$smile = form_smiles();
		$attachMgr = new attachmentMgr();

		#setup upload limit.
		if($groupPolicy->validateAccess(1, 26) == false){
			$uploadLimit = 0;
		}else{
			$uploadLimit = ($boardPref->getPreferenceValue("upload_limit") - $attachMgr->uploadCount("Topic", $tid));
		}

		//get subscription status.
		$db->SQL = "SELECT tid FROM ebb_topic_watch WHERE username='$logged_user' AND tid='$tid'";
		$check_subscription = $db->affectedRows();

		#load reply template.
		$tpl = new templateEngine($style, "edittopic");
		$tpl->parseTags(array(
		"TITLE" => "$title",
  		"LANG-TITLE" => "$lang[edittopic]",
		"BID" => "$bid",
		"TID" => "$tid",
		"BBCODE" => "$bbcode",
		"LANG-SMILES" => "$lang[moresmiles]",
		"SMILES" => "$smile",
		"LANG-USERNAME" => "$lang[username]",
		"USERNAME" => "$logged_user",
		"LANG-TOPIC" => "$lang[topic]",
  		"TOPIC" => "$topic[Topic]",
		"BODY" => "$topicData[Body]",
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
  		"LANG-EDITTOPIC" => "$lang[edittopic]"));

		#can the user upload a file with this reply?
		if($groupPolicy->validateAccess(1, 26) == false){
			$tpl->removeBlock("upload");
		}

		#see if user can mark topics sa important.
		if($groupPolicy->validateAccess(1, 39) == true){
			//check for topic type.
			if ($topicData['important'] == 1){
				$tpl->removeBlock("normal");
			}else{
				$tpl->removeBlock("important");
			}
		}else{
			$tpl->removeBlock("normal");
			$tpl->removeBlock("important");
		}

		//check for subscription status.
		if($check_subscription == 1){
   			$tpl->removeBlock("subscribe");
		}else{
			$tpl->removeBlock("subscribed");
		}

		//check for smile status.
		if($postData['disable_smiles'] == 1){
   			$tpl->removeBlock("disablesmiles");
		}else{
   			$tpl->removeBlock("disabledsmiles");
		}

		//check for bbcode status
		if($postData['disable_bbcode'] == 1){
   			$tpl->removeBlock("disablebbcode");
		}else{
   			$tpl->removeBlock("disabledbbcode");
		}

		echo $tpl->outputHtml();
	}else{
	    $displayMsg = new notifySys($lang['accessdenied'], true);
		$displayMsg->displayError();
	}
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
