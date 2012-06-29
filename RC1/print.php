<?php
define('IN_EBB', true);
/**
Filename: print.php
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
	$bid = $db->filterMySQL(var_cleanup($_GET['bid']));
}

#see if Topic ID was declared, if not terminate any further outputting.
if((!isset($_GET['tid'])) or (empty($_GET['tid']))){
	$error = new notifySys($lang['notid'], true);
	$error->genericError();
}else{
	$tid = $db->filterMySQL(var_cleanup($_GET['tid']));
}

//topic & board query.
$db->SQL = "select Original_Date, Body, Topic, author FROM ebb_topics WHERE tid='$tid'";
$checkTopic = $db->affectedRows();
$topicData = $db->fetchResults();

$db->SQL = "select id FROM ebb_boards WHERE id='$bid'";
$checkBoard = $db->affectedRows();

//check to see if topic exists or not and if it doesn't kill the program.
if (($checkBoard == 0) or ($checkTopic == 0)){
	$error = new notifySys($lang['doesntexist'], true);
	$error->genericError();
}

#format topic.
$msg = nl2br(BBCode_print(smiles(language_filter($topicData['Body'], 1))));

#get topic date.
$topicDate = formatTime($timeFormat, $topicData['Original_Date'], $gmt);

#output topic.
$tpl = new templateEngine($style, "print");
$tpl->parseTags(array(
  "TITLE" => "$title",
  "LANG-TITLE" => "$lang[ptitle]",
  "BID" => "$bid",
  "TID" => "$tid",
  "SUBJECT" => "$topicData[Topic]",
  "TOPIC-DATE" => "$topicDate",
  "AUTHOR" => "$topicData[author]",
  "TOPIC" => "$msg"));
echo $tpl->outputHtml();

#setup replies SQL.
$db->SQL = "SELECT Original_Date, Body, re_author FROM ebb_posts WHERE tid='$tid'";
$query = $db->query();
$replyCount = $db->affectedRows();

#setup replies.
if($replyCount > 0){
	while ($postData = mysql_fetch_assoc ($query)) {
		#get date
		$postDate = formatTime($timeFormat, $postData['Original_Date'], $gmt);

		#format topic.
		$reMsg = nl2br(BBCode_print(smiles(language_filter($postData['Body'], 1))));

		#output topic.
		$tpl = new templateEngine($style, "print-replies");
		$tpl->parseTags(array(
		  "REPLY-DATE" => "$postDate",
		  "AUTHOR" => "$postData[re_author]",
		  "MESSAGE" => "$reMsg"));
		echo $tpl->outputHtml();
	}
}

#display footer
$tpl = new templateEngine($style, "print-foot");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();
?>
