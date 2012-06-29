<?php
define('IN_EBB', true);

/**
Filename: livesearch.php
Last Modified: 06/28/2012

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

include "../config.php";
include FULLPATH."/header.php";

#see if a guest-level user is trying to access this file.
if ($logged_user == "guest"){
	$error = new notifySys($lang['guesterror'], true);
	$error->genericError();
}else{
	#see if user can access this portion of the site.
	if($groupPolicy->validateAccess(1, 28) == false){
		$error = new notifySys($lang['accessdenied'], true);
		$error->genericError();
	}

	//flood check.
	if (flood_check($logged_user, "search") == 1){
		$error = new notifySys($lang['flood'], false);
		$error->genericError();
	}

	#keyword variable.
	$keyword = $db->filterMySQL(var_cleanup($_GET['q']));

	#error check.
	if(empty($keyword)){
		$error = new notifySys($lang['nokeyword'], false);
		$error->genericError();
	}
	#update last_search colume.
	$db->SQL = "UPDATE ebb_users SET last_search=UNIX_TIMESTAMP() WHERE Username='$logged_user'";
	$db->query();

	#Topic query
	$db->SQL = "SELECT Topic, author, bid, tid FROM ebb_topics WHERE Topic LIKE '%$keyword%' OR Body LIKE '%$keyword%' LIMIT 5";
	$topicQ = $db->query();
	$countT = number_format($db->affectedRows());

	#post query.
	$db->SQL = "SELECT bid, tid, pid FROM ebb_posts WHERE Body LIKE '%$keyword%' LIMIT 5";
	$RepliesQ = $db->query();
	$countP = number_format($db->affectedRows());

	#see if anything in there, if so lets display it.
	if(($countT == 0) AND ($countP == 0)){
		$error = new notifySys($lang['noresults'], false);
		$error->genericError();
	}else{
		//output any topics
		while ($topic = mysql_fetch_assoc($topicQ)) {
		
			//check for the posting rule.
			$db->SQL = "SELECT B_Read FROM ebb_board_access WHERE B_id='$topic[bid]'";
			$boardRule = $db->fetchResults();

			//see if the user can access this spot.
			if ($groupPolicy->validateAccess(0, $boardRule['B_Read']) == true){
				#search results data.
				echo "<a href=\"viewtopic.php?bid=$topic[bid]&amp;tid=$topic[tid]\">$topic[Topic]</a><br />";
			}//end permission check.
		}//end loop.
		
		//output any posts
		while ($reply = mysql_fetch_assoc($RepliesQ)){
			$db->SQL = "SELECT Topic FROM ebb_topics WHERE tid='$reply[tid]'";
			$topicSubject = $db->fetchResults();

			//check for the posting rule.
			$db->SQL = "SELECT B_Read FROM ebb_board_access WHERE B_id='$reply[bid]'";
			$boardRule = $db->fetchResults();

			//see if the user can access this spot.
			if ($groupPolicy->validateAccess(0, $boardRule['B_Read']) == true){
				#search results data.
				echo "<a href=\"viewtopic.php?bid=$reply[bid]&amp;tid=$reply[tid]&amp;pid=$reply[pid]\">RE: $topicSubject[Topic]</a><br />";
			}//end permission check.
		}//end loop.
	}//end flood check.
}//end guest check.
?>
