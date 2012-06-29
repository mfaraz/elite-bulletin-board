<?php
define('IN_EBB', true);
/**
Filename: topicreview.php
Last Modified: 06/28/2012

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";
include_once FULLPATH."/includes/topic_function.php";

#output page header.
$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "&nbsp;",
    "LANG-HELP-TITLE" => "$help[nohelptitle]",
    "LANG-HELP-BODY" => "$help[nohelpbody]",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

#see if Board ID was declared, if not terminate any further outputting.
if((!isset($_GET['bid'])) or (empty($_GET['bid']))){
	$displayMsg = new notifySys($lang['nobid'], true);
	$displayMsg->displayError();
}else{
	$bid = $db->filterMySQL(var_cleanup($_GET['bid']));
}
#see if Topic ID was declared, if not terminate any further outputting.
if((!isset($_GET['tid'])) or (empty($_GET['tid']))){
	$displayMsg = new notifySys($lang['notid'], true);
	$displayMsg->displayError();
}else{
	$tid = $db->filterMySQL(var_cleanup($_GET['tid']));
}
#get board stats.
$db->SQL = "SELECT Smiles, BBcode, Image FROM ebb_boards WHERE id='$bid'";
$checkboard = $db->affectedRows();
$bbcodeData = $db->fetchResults();

#get topic.
$db->SQL = "select author, Body, Topic, Original_Date, disable_smiles, disable_bbcode FROM ebb_topics WHERE tid='$tid'";
$checktopic = $db->affectedRows();
$topicData = $db->fetchResults();

#see if board and topic exist.
if (($checkboard == 0) or ($checktopic == 0)){
	$displayMsg = new notifySys($lang['doesntexist'], true);
	$displayMsg->displayError();
}

#get variables.
$allowsmile = $bbcodeData['Smiles'];
$allowbbcode = $bbcodeData['BBcode'];
$allowimg = $bbcodeData['Image'];

//get date
$topicDate = formatTime($timeFormat, $topicData['Original_Date'], $gmt);

//set bbcode for replies.
$msg = $topicData['Body'];

//see if user wish to disable smiles in post.
if($topicData['disable_smiles'] == 0){
	if ($allowsmile == 1){
		$msg = smiles($msg);
	}
}

//see if user wish to disable bbcode in post.
if($topicData['disable_bbcode'] == 0){
	if ($allowimg == 1){
		$msg = BBCode($msg, true);
	}
	if ($allowbbcode == 1){
		$msg = BBCode($msg);
	}
}
$msg = language_filter($msg, 1);
$msg = nl2br($msg);

#output topic.
$tpl = new templateEngine($style, "topicreview");
$tpl->parseTags(array(
  "TOPICSUBJECT" => "$topicData[Topic]",
  "LANG-POSTEDON" => "$lang[postedon]",
  "POSTEDON" => "$topicDate",
  "AUTHOR" => "$topicData[author]",
  "BODY" => "$msg"));
echo $tpl->outputHtml();

#get replies if any.
$db->SQL = "SELECT pid FROM ebb_posts WHERE tid='$tid'";
$replyCount = $db->affectedRows();

if($replyCount > 0){
	$db->SQL = "SELECT re_author, Body, Original_Date, disable_smiles, disable_bbcode FROM ebb_posts WHERE tid='$tid'";
	$query = $db->query();

	while ($postData = mysql_fetch_assoc($query)) {
		#obtain & format date.
		$topicDate = formatTime($timeFormat, $postData['Original_Date'], $gmt);
		
		//set bbcode for replies.
		$reMsg = $postData['Body'];
		
		//see if user wish to disable smiles in post.
		if($postData['disable_smiles'] == 0){
			if ($allowsmile == 1){
				$reMsg = smiles($reMsg);
			}
		}
		
		//see if user wish to disable bbcode in post.
		if($postData['disable_bbcode'] == 0){
			if ($allowimg == 1){
				$reMsg = BBCode($reMsg, true);
			}
			if ($allowbbcode == 1){
				$reMsg = BBCode($reMsg);
			}
		}
		$reMsg = language_filter($re_msg, 1);
		$reMsg = nl2br($re_msg);

		#output replies.
		$tpl = new templateEngine($style, "topicreview");
		$tpl->parseTags(array(
		  "TOPICSUBJECT" => "&nbsp;",
		  "LANG-POSTEDON" => "$lang[postedon]",
		  "POSTEDON" => "$postDate",
		  "AUTHOR" => "$postData[re_author]",
		  "BODY" => "$reMsg"));
		echo $tpl->outputHtml();
	}
}

#output topic.
$tpl = new templateEngine($style, "topicreview-foot");
echo $tpl->outputHtml();

ob_end_flush();
?>
