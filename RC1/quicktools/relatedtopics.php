<?php
define('IN_EBB', true);

/**
Filename: relatedtopics.php
Last Modified: 1/15/2011

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

if ($logged_user == "guest"){
	exit($lang['guesterror']);
}else{

	//flood check.
	$flood = flood_check($logged_user, "search");
	if ($flood == 1){
		exit($lang['flood']);
	}
	#keyword variable.
	$topic = $db->filterMySQL($_POST['topic']);

	#if blank, simply do nothing.
	if(empty($topic)){
		exit('nothing entered.');
	}	
	#update last_search colume.
	$time = time();
	$db->SQL = "UPDATE ebb_users SET last_search='$time' WHERE Username='$logged_user'";
	$db->query();

	#Topic query
	$db->SQL = "SELECT Topic, author, bid, tid FROM ebb_topics WHERE Topic LIKE '%$topic%' OR Body LIKE '%$topic%' LIMIT 5";
	$search_result = $db->query();
	$count_t = $db->affectedRows();

	#post query.
	$db->SQL = "SELECT re_author, bid, tid, pid FROM ebb_posts WHERE Body LIKE '%$topic%' LIMIT 5";
	$search_result2 = $db->query();
	$count_p = $db->affectedRows();

	#see if anything in there, if so lets display it.
	if(($count_t == 0) AND ($count_p == 0)){
		echo $lang['nosimilar'];
	}else{
		echo $lang['relatedtopics'].':<hr />';
		//output any topics
		while ($row = mysql_fetch_assoc($search_result)) {
		
			//check for the posting rule.
			$db->SQL = "SELECT B_Read FROM ebb_board_access WHERE B_id='$row[bid]'";
			$boardRule = $db->fetchResults();

			//see if the user can access this spot.
			if ($groupPolicy->validateAccess(0, $boardRule['B_Read']) == true){
				#search results data.
				echo "<a href=\"viewtopic.php?bid=$row[bid]&amp;tid=$row[tid]\">$row[Topic]</a> - $row[author]<br />";	
			}//end promission check.
		}//end loop.
		
		//output any posts
		while ($row2 = mysql_fetch_assoc($search_result2)){
			$db->SQL = "SELECT Topic FROM ebb_topics WHERE tid='$row2[tid]'";
			$topic_r = $db->fetchResults();

			//check for the posting rule.
			$db->SQL = "SELECT B_Read FROM ebb_board_access WHERE B_id='$row2[bid]'";
			$boardRule = $db->fetchResults();

			//see if the user can access this spot.
			if ($groupPolicy->validateAccess(0, $boardRule['B_Read']) == true){
				#search results data.
				echo "<a href=\"viewtopic.php?bid=$row2[bid]&amp;tid=$row2[tid]&amp;pid=$row2[pid]\">RE: $topic_r[Topic]</a> - $row2[re_author]<br />";
			}//end promission check.				
		}//end loop.
	}//end flood check.
}//end guest check.
?>
