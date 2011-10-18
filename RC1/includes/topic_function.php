<?php
if (!defined('IN_EBB') ) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: topic_function.php
Last Modified: 4/19/2010

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

/**
*readBoardStat
*
*Check read status on a selected board.
*
*@modified 9/22/09
*
*@param integer $bid - Board ID to select a board.
*@param string $user - Username to check against.
*
*@return integer $readCt - either read(1) or unread(0).
*
*/
function readBoardStat($bid, $user){

	global $db;
	
	#see if user is guest.
	if($user == "guest"){
	    $readCt = 1;
	}else{
		$db->SQL = "select Board from ebb_read_board WHERE Board='$bid' and User='$user'";
		$readCt = $db->affectedRows();
	}

	return ($readCt);
}

/**
*readTopicStat
*
*Check read status on a selected board.
*
*@modified 9/22/09
*
*@param integer $tid - Topic ID to select a topic.
*@param string $user - Username to check against.
*
*@return integer $readCt - either read(1) or unread(0).
*
*/
function readTopicStat($tid, $user){

	global $db;
	
	#see if user is guest.
	if($user == "guest"){
	    $readCt = 1;
	}else{
		$db->SQL = "select Topic from ebb_read_topic WHERE Topic='$tid' and User='$user'";
		$readCt = $db->affectedRows();
	}

	return ($readCt);
}
?>
