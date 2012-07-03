<?php
if (!defined('IN_EBB')) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: searchEngine.php
Last Modified: 7/7/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

class searchEngine{

	/**
	*topicResults
	*
	*Get results for topics.
	*
	*@modified 4/25/10
	*
	*@access public
	*/
	public function topicResults(){

		global $title, $style, $pagenation, $search_result, $db, $num, $lang, $groupPolicy;

		#search results header.
		$tpl = new templateEngine($style, "searchresults_head");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[search]",
		"LANG-SEARCHRESULTS" => "$lang[searchresults]",
		"PAGINATION" => "$pagenation",
		"NUM-RESULTS" => "$num",
		"LANG-RESULTS" => "$lang[result]",
		"LANG-USERNAME" => "$lang[author]",
		"LANG-TOPIC" => "$lang[topics]",
		"LANG-POSTEDIN" => "$lang[postedin]"));
		echo $tpl->outputHtml();

		while ($topicR = mysql_fetch_assoc($search_result)) {

			//check for the posting rule.
			$db->SQL = "SELECT B_Read FROM ebb_board_access WHERE B_id='$topicR[bid]'";
			$boardRule = $db->fetchResults();

			//see if the user can access this spot.
			if ($groupPolicy->validateAccess(0, $boardRule['B_Read']) == true){
				#get board details.
				$db->SQL = "SELECT Board FROM ebb_boards where id='$topicR[bid]'";
				$boardR = $db->fetchResults();

				#search results data.
                $tpl = new templateEngine($style, "searchresults_topic");
				$tpl->parseTags(array(
				"BID" => "$topicR[bid]",
				"TID" => "$topicR[tid]",
				"TOPICNAME" => "$topicR[Topic]",
				"AUTHOR" => "$topicR[author]",
				"LANG-POSTEDIN" => "$lang[postedin]",
				"BOARDNAME" => "$boardR[Board]"));
				echo $tpl->outputHtml();
			}
		}
		#search results footer.
		$tpl = new templateEngine($style, "searchresults_foot");
		echo $tpl->outputHtml();
	}
	
	/**
	*postResults
	*
	*Get results for replies.
	*
	*@modified 7/7/10
	*
	*@access public
	*/
	public function postResults(){

		global $title, $style, $pagenation, $search_result, $db, $num, $lang, $groupPolicy;

		#search results header.
        $tpl = new templateEngine($style, "searchresults_head");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[search]",
		"LANG-SEARCHRESULTS" => "$lang[searchresults]",
		"PAGINATION" => "$pagenation",
		"NUM-RESULTS" => "$num",
		"LANG-RESULTS" => "$lang[result]",
		"LANG-USERNAME" => "$lang[author]",
		"LANG-TOPIC" => "$lang[topics]",
		"LANG-POSTEDIN" => "$lang[postedin]"));
		echo $tpl->outputHtml();

		while ($postR = mysql_fetch_assoc($search_result)){
			//check for the posting rule.
			$db->SQL = "SELECT B_Read FROM ebb_board_access WHERE B_id='$postR[bid]'";
			$boardRule = $db->fetchResults();

			//see if the user can access this spot.
			if ($groupPolicy->validateAccess(0, $boardRule['B_Read']) == true){
				#get topic details.
				$db->SQL = "SELECT Topic FROM ebb_topics WHERE tid='$postR[tid]'";
				$topicR = $db->fetchResults();

				#get board details.
				$db->SQL = "SELECT Board FROM ebb_boards WHERE id='$postR[bid]'";
				$boardR = $db->fetchResults();

				#search results data.
                $tpl = new templateEngine($style, "searchresults_post");
				$tpl->parseTags(array(
				"BID" => "$postR[bid]",
				"TID" => "$postR[tid]",
				"PID" => "$postR[pid]",
				"TOPICNAME" => "$topicR[Topic]",
				"AUTHOR" => "$postR[re_author]",
				"LANG-POSTEDIN" => "$lang[postedin]",
				"BOARDNAME" => "$boardR[Board]"));
				echo $tpl->outputHtml();
			}
		}

		#search results footer.
		$tpl = new templateEngine($style, "searchresults_foot");
		echo $tpl->outputHtml();
	}

}
