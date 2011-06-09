<?php
define('IN_EBB', true);
/**
Filename: delete.php
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
    "PAGETITLE" => "$lang[deletemessages]",
    "LANG-HELP-TITLE" => "$help[indextitle]",
    "LANG-HELP-BODY" => "$help[indexbody]",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

//output top
if($logged_user == "guest"){
	redirect('login.php', false, 0);
}

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
	$error = new notifySys($lang['nobid'], true);
	$error->genericError();
}else{
	$bid = $db->filterMySQL($_GET['bid']);
}

#see if Topic ID was declared, if not terminate any further outputting.
if((!isset($_GET['tid'])) or (empty($_GET['tid']))){
	$error = new notifySys($lang['notid'], true);
	$error->genericError();
}else{
	$tid = $db->filterMySQL($_GET['tid']);
}

#see if Post ID was declared, if not leave it blank as we're assuming no replies are yet created.
if((!isset($_GET['pid'])) or (empty($_GET['pid']))){
	$pid = '';
}else{
	$pid = $db->filterMySQL($_GET['pid']);
}

if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = ''; 
}

switch ($action){
case 'deleteTopic':
	//see if topic exist
	$db->SQL = "SELECT tid FROM ebb_topics WHERE tid='$tid'";
	$checkTopic = $db->affectedRows();

	if ($checkTopic == 0){
	    $displayMsg = new notifySys($lang['doesntexist'], true);
		$displayMsg->displayError();
	}
	#get author name to verify.
	$db->SQL = "SELECT author FROM ebb_topics WHERE tid='$tid' LIMIT 1";
	$topic = $db->fetchResults();

	#see if user has rights to do this.
	if(($groupAccess == 1) OR ($groupAccess == 2)){
		//find what usergroup this user belongs to.
		$userGroupPolicy = new groupPolicy($topic['author']);
		$userGroupRank = $userGroupPolicy->groupAccessLevel();
		
		#if author is an admin, mods can't edit the post.
		if(($groupAccess == 2) and ($userGroupRank == 1)){
			$canDel = false;
		}else{
			if ($groupPolicy->validateAccess(1, 21) == true){
				$canDel = true;
			}else{
				$canDel = false;
			}
		}
	}elseif($groupAccess == 3){
		if (($logged_user == $topic['author']) AND ($groupPolicy->validateAccess(1, 21) == true)){
			$canDel = true;
		}else{
			$canDel = false;
		}	
	}
	
	if($canDel == true){
		//delete topics.
		$db->SQL = "DELETE FROM ebb_topics WHERE tid='$tid'";
		$db->query();

		//delete polls made by topics in this board.
		$db->SQL = "DELETE FROM ebb_poll WHERE tid='$tid'";
		$db->query();

		#delete any votes.
		$db->SQL = "DELETE FROM ebb_votes WHERE tid='$tid'";
		$db->query();

		//delete replies, if any.
		$db->SQL = "DELETE FROM ebb_posts WHERE tid='$tid'";
		$db->query();

		//delete read status from topics made in this board.
		$db->SQL = "DELETE FROM ebb_read WHERE Topic='$tid'";
		$db->query();

		//delete any subscriptions to this topic.
		$db->SQL = "DELETE FROM ebb_topic_watch WHERE tid='$tid'";
		$db->query();

		//update last posted section.
		$db->SQL = "SELECT id FROM ebb_boards WHERE id='$bid'";
		$boardTopicCount = $db->affectedRows();

		if($boardTopicCount == 0){
			$db->SQL = "UPDATE ebb_boards SET last_update='' WHERE tid='$tid'";
			$db->query();
		}else{
			$db->SQL = "SELECT last_update, Posted_User, Post_Link FROM ebb_topics WHERE bid='$bid' ORDER BY last_update DESC LIMIT 1";
			$board_r = $db->fetchResults();

			//update the last_update colume for ebb_boards.
			$db->SQL = "UPDATE ebb_boards SET last_update='$board_r[last_update]', Posted_User='$board_r[Posted_User]', Post_Link='$board_r[Post_Link]'  WHERE id='$bid'";
			$db->query();
		}
		#delete any attachments thats tied to this topic.
		$db->SQL = "SELECT Filename FROM ebb_attachments WHERE tid='$tid'";
		$attachQ = $db->query();
		$attachChk = $db->affectedRows();

		if($attachChk > 0){
		    while($delAttach = mysql_fetch_assoc($attachQ)){
				#delete file from web space.
				@unlink (FULLPATH.'/uploads/'. $delAttach['Filename']);

				#delete entry from db.
				$db->SQL = "DELETE FROM ebb_attachments WHERE tid='$tid'";
				$db->query();
			}
		}
		#alert users that message was deleted.
	    $displayMsg = new notifySys($lang['msgdeleted'], true);
		$displayMsg->displayMessage();

		#bring user back.
	  	redirect('viewboard.php?bid='.$bid, true, 3);
	}else{
	    $displayMsg = new notifySys($lang['accessdenied'], true);
		$displayMsg->displayError();
	}
break;
case 'deletePost':
	//see if the post exist.
	$db->SQL = "SELECT pid FROM ebb_posts WHERE pid='$pid'";
	$checkPost = $db->affectedRows();

	if ($checkPost == 0){
	    $displayMsg = new notifySys($lang['doesntexist'], true);
		$displayMsg->displayError();
	}
	#get author's name to verify.
	$db->SQL = "SELECT re_author FROM ebb_posts WHERE pid='$pid'";
	$post = $db->fetchResults();

	#see if user has rights to do this.
	if(($groupAccess == 1) OR ($groupAccess == 2)){
		//find what usergroup this user belongs to.
		$userGroupPolicy = new groupPolicy($post['re_author']);
		$userGroupRank = $userGroupPolicy->groupAccessLevel();

		#if author is an admin, mods can't edit the post.
		if(($groupAccess == 2) and ($userGroupRank == 1)){
			$canDel = false;
		}else{
			if ($groupPolicy->validateAccess(1, 21) == true){
				$canDel = true;
			}else{
				$canDel = false;
			}
		}
	}elseif($groupAccess == 3){
		if (($logged_user == $post['re_author']) AND ($groupPolicy->validateAccess(1, 21) == true)){
			$canDel = true;
		}else{
			$canDel = false;
		}
	}

	if($canDel == true){
		//delete topics.
		$db->SQL = "DELETE FROM ebb_posts WHERE pid='$pid'";
		$db->query();

		#delete any attachments thats tied to this topic.
		$db->SQL = "select Filename from ebb_attachments where pid='$pid'";
		$attachQ = $db->query();
		$attachChk = $db->affectedRows();

		if($attachChk > 0){
		    while($delAttach = mysql_fetch_assoc($attachQ)){
				#delete file from web space.
				@unlink (FULLPATH.'/uploads/'. $delAttach['Filename']);

				#delete entry from db.
				$db->SQL = "DELETE FROM ebb_attachments WHERE pid='$pid'";
				$db->query();
			}
		}
		
		//update last posted section.
		$db->SQL = "SELECT tid FROM ebb_posts WHERE tid='$tid'";
		$postCount = $db->affectedRows();

		if($postCount == 0){
			$db->SQL = "SELECT bid, tid, Original_Date, author FROM ebb_topics WHERE tid='$tid'";
			$post_r = $db->fetchResults();

			#create link to original post.
			$originalupdate = "bid=". $post_r['bid'] . "&tid=". $post_r['tid'];
			//update topic last_update.
			$db->SQL = "UPDATE ebb_topics SET last_update='$post_r[Original_Date]', Posted_User='$post_r[author]', Post_Link='$originalupdate' WHERE tid='$tid'";
			$db->query();

			//bring user back
			header("Location: viewtopic.php?$originalupdate");
		}else{
			$db->SQL = "SELECT pid, Original_Date, re_author FROM ebb_posts WHERE tid='$tid' ORDER BY Original_Date DESC LIMIT 1";
			$topic_r = $db->fetchResults();

			//create new post link.
			$newlink = "bid=". $bid . "&tid=". $tid . "&pid=". $topic_r['pid'] . "#". $topic_r['pid'];
			//update the last_update colume for ebb_boards.
			$db->SQL = "UPDATE ebb_topics SET last_update='$topic_r[Original_Date]', Posted_User='$topic_r[re_author]', Post_Link='$newlink'  WHERE tid='$tid'";
			$db->query();

			#alert users that message was deleted.
		    $displayMsg = new notifySys($lang['msgdeleted'], true);
			$displayMsg->displayMessage();

			#bring user back.
		  	redirect('viewtopic.php?'.$newlink, true, 3);
		}
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
