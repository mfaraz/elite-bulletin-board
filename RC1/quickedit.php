<?php
define('IN_EBB', true);
/**
Filename: quickedit.php
Last Modified: 11/7/2010

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

require_once "config.php";
require_once FULLPATH."/header.php";


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

$type = var_cleanup($_GET['type']);

switch($mode){
case 'edit':
	//see what type of post this is.
	if($type == "topic"){	
		#see if request exist.
		$db->SQL = "SELECT author FROM ebb_topics WHERE tid='$tid'";
		$checkboard = $db->affectedRows();
		$topic = $db->fetchResults();

		if ($checkboard == 0){
			$error = new notifySys($lang['doesntexist'], true);
			$error->genericError();
		}

		//check to see if this user is the author of this post.otherwise this action will be canceled.
		if(($groupAccess == 1) or ($groupAccess == 2)){
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

		#can user edit post?
		if ($canEdit == true){
	    	#get information from quick form.
			$editText = $db->filterMySQL(var_cleanup($_POST['value']));
	
			#error check.
			if(empty($editText)){
				$error = new notifySys($lang['nopost'], true);
				$error->genericError();
			}else{
				//spam check.
				$post_chk = language_filter($editText, 2);
				
				//update the topic.
				$db->SQL = "UPDATE ebb_topics SET Body='$editText' WHERE tid='$tid'";
				$db->query();

				#display text.
				echo nl2br(smiles(BBCode(language_filter($editText, 1), true)));
			}	
		}else{
			$error = new notifySys($lang['denied'], true);
			$error->genericError();
		}
	}elseif($type == "post"){
		//see if topic exist
		$db->SQL = "SELECT re_author FROM ebb_posts WHERE pid='$pid'";
		$checkboard = $db->affectedRows();
		$postR = $db->fetchResults();

		if ($checkboard == 0){
			$error = new notifySys($lang['doesntexist'], true);
			$error->genericError();
		}
		
		//check to see if this user is the author of this post.otherwise this action will be canceled.
		if(($groupAccess == 1) or ($groupAccess == 2)){
			//find what usergroup this user belongs to.
			$userGroupPolicy = new groupPolicy($postR['re_author']);
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
			if (($logged_user == $postR['re_author']) AND ($groupPolicy->validateAccess(1, 20) == true)){
				$canEdit = true;
			}else{
				$canEdit = false;
			}
		}

		#can user edit post?
		if ($canEdit == true){
	    	#get information from quick form.
			$editText = $db->filterMySQL(var_cleanup($_POST['value']));
    	   	
			#error check.
			if(empty($editText)){
				$error = new notifySys($lang['nopost'], true);
				$error->genericError();
			}else{
				//spam check.
				$post_chk = language_filter($EditText, 2);
				//update the topic.
				$db->SQL = "UPDATE ebb_posts SET Body='$editText' WHERE pid='$pid'";
				$db->query();

				#display text.
				echo nl2br(smiles(BBCode(language_filter($editText, 1), true)));
			}
		}
	}else{
		$error = new notifySys($lang['denied'], true);
		$error->genericError();
	}
break;
default:
	//see what type of post this is.
	if($type == "topic"){
		$db->SQL = "SELECT author, Body FROM ebb_topics WHERE tid='$tid'";
		$checkboard = $db->affectedRows();
		$topic = $db->fetchResults();

		if ($checkboard == 0){
			$error = new notifySys($lang['doesntexist'], true);
			$error->genericError();
		}

		//check to see if this user is the author of this post.otherwise this action will be canceled.
		if(($groupAccess == 1) or ($groupAccess == 2)){
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

		#can user edit post?
		if ($canEdit == true){
		    echo html_entity_decode($topic['Body'], ENT_QUOTES, "UTF-8");
		}else{
			$error = new notifySys($lang['denied'], true);
			$error->genericError();
		}
	}elseif($type == "post"){
		//see if topic exist
		$db->SQL = "select re_author, Body from ebb_posts WHERE pid='$pid'";
		$checkboard = $db->affectedRows();
		$postR = $db->fetchResults();

		if ($checkboard == 0){
			$error = new notifySys($lang['doesntexist'], true);
			$error->genericError();
		}

		//check to see if this user is the author of this post.otherwise this action will be canceled.
		if(($groupAccess == 1) or ($groupAccess == 2)){
			//find what usergroup this user belongs to.
			$userGroupPolicy = new groupPolicy($postR['re_author']);
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
			if (($logged_user == $postR['re_author']) AND ($groupPolicy->validateAccess(1, 20) == true)){
				$canEdit = true;
			}else{
				$canEdit = false;
			}
		}

		#can user edit post?
		if ($canEdit == true){
			echo html_entity_decode($postR['Body'], ENT_QUOTES, "UTF-8");
		}else{
			$error = new notifySys($lang['denied'], true);
			$error->genericError();
		}
	}
}
?>
