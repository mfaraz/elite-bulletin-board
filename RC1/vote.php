<?php
define('IN_EBB', true);
/**
Filename: vote.php
Last Modified: 11/10/2010

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

#see if user added a poll option, if not terminate any further outputting.
if((!isset($_POST['vote'])) or (empty($_POST['vote']))){
	$error = new notifySys($lang['novote'], true);
	$error->genericError();
}else{
	$vote = $db->filterMySQL($_POST['vote']);
}

//get posting rules.
$db->SQL = "SELECT B_Vote FROM ebb_board_access WHERE B_id='$bid'";
$boardRule = $db->fetchResults();

//see who can vote on the poll.
if($groupPolicy->validateAccess(1, 36) == false){
	if ($groupPolicy->validateAccess(0, $boardRule['B_Vote']) == false){
		$error = new notifySys($lang['cantvote'], true);
		$error->genericError();
	}
}else{
	//perform query.
	$db->SQL = "INSERT INTO ebb_votes (Username, tid, Vote) VALUES('$logged_user', '$tid', '$vote')";
	$db->query();

	//direct user back to topic.
	redirect('viewtopic.php?bid='.$bid.'&amp;tid='.$tid, false, 0);
}
ob_end_flush();
?>
