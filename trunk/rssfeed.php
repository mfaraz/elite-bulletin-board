<?php
define('IN_EBB', true);
/**
Filename: rssdeed.php
Last Modified: 11/10/2010

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";

#see if board id was defined.
if(isset($_GET['bid'])){
	$bid = $db->filterMySQL($_GET['bid']);
}else{
	$error = new notifySys($lang['nobid'], true);
	$error->genericError();
}

#see if board exists
$db->SQL = "SELECT Board, type FROM ebb_boards WHERE id='$bid'";
$checkboard = $db->affectedRows();
$board_r = $db->fetchResults();


//see if board existsa dn is not a category-board.
if(($checkboard == 1) and ($board_r['type'] != 1)){
	#get last 10 new topics created.
	$db->SQL = "SELECT tid, bid, Topic, Body, Original_Date FROM ebb_topics WHERE bid='$bid' ORDER BY Original_Date DESC LIMIT 10";
	$topic_query = $db->query();


	#get last 10 new replies created.
	$db->SQL = "SELECT pid, tid, bid, Body, Original_Date FROM ebb_posts WHERE bid='$bid' ORDER BY Original_Date DESC LIMIT 10";
	$post_query = $db->query();


	#set headers to make it an xml file.
	header("Content-type: text/xml");
 	echo '<?xml version="1.0" encoding="UTF-8" ?>';
 	echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
  	echo '<channel>
	<title>'.$title.'</title>
	<description>'.$board_r['Board'].'</description>
	<link>'.$address.'/'.$boardDir.'</link>';

	#get latest topics.	
	while($topic = mysql_fetch_array($topic_query)) {

		//check for the posting rule.
		$db->SQL = "SELECT B_Read FROM ebb_board_access WHERE B_id='$topic[bid]'";
		$boardRule = $db->fetchResults();

		//see if the user can access this spot.
		if ($groupPolicy->validateAccess(0, $boardRule['B_Read']) == true){
		
			#if body is over 100 characters, cut it off.
			if(strlen($topic['Body']) > 100){
				$rss_desc = substr_replace($db->filterMySQL(var_cleanup($topic['Body'])),'[...]',100);
			}else{
				$rss_desc = $db->filterMySQL(var_cleanup($topic['Body']));
			}
			//setup date
			$topicDate = formatTime("r", $topic['Original_Date'], $gmt);
			
			#output data.
			echo '<item>
			<link>'.$board_address.'/viewtopic.php?bid='.$topic['bid'].'&amp;tid='.$topic['tid'].'</link>
			<title>'.$topic['Topic'].'</title>
			<description>'.$rss_desc.'</description>
			<pubDate>'. $topicDate .'</pubDate>
			</item>';
		}
	}
	#get latest replies.
	while($post = mysql_fetch_array($post_query)) {

		//check for the posting rule.
		$db->SQL = "SELECT B_Read FROM ebb_board_access WHERE B_id='$post[bid]'";
		$boardRule = $db->fetchResults();

		//see if the user can access this spot.
		if ($groupPolicy->validateAccess(0, $boardRule['B_Read']) == true){
		
			#if body is over 100 characters, cut it off.
			if(strlen($post['Body']) > 100){
				$rss_desc = substr_replace($db->filterMySQL(var_cleanup($post['Body'])),'[...]',100);
			}else{
				$rss_desc = $db->filterMySQL(var_cleanup($post['Body']));
			}
			//setup date
			$postDate = formatTime("r", $topic['Original_Date'], $gmt);
			
			#get topic details.
			$db->SQL = "SELECT Topic FROM ebb_topics where tid='$post[tid]'";
			$topic_r = $db->fetchResults();

		    #output data.
			echo '<item>';
			echo '<link>'.$board_address.'/viewtopic.php?bid='.$post['bid'].'&amp;tid='.$post['tid'].'&amp;pid='.$post['pid'].'#'.$post['pid'].'</link>';
			echo '<title>'.$topic_r['Topic'].'</title>';
			echo '<description>'.$rss_desc.'</description>';
			echo '<pubDate>'. $postDate .'</pubDate>';
			echo '</item>';
		}
	}
	echo '</channel></rss>';
}else{
	$error = new notifySys($lang['invalidopt'], true);
	$error->genericError();
}
?>
