<?php
define('IN_EBB', true);
/**
Filename: Search.php
Last Modified: 7/7/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";
require_once FULLPATH."/includes/searchEngine.php";
require_once FULLPATH."/includes/boardlisting.php";

$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$lang[search]",
    "LANG-HELP-TITLE" => "$help[searchtitle]",
    "LANG-HELP-BODY" => "$help[searchbody]",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

#see if user can access this portion of the site.
if(($groupPolicy->validateAccess(1, 28) == false) OR ($groupAccess == 0)){
	$error = new notifySys($lang['accessdenied'], true);
	$error->displayError();
}

//output top
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

//display search
if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = ''; 
}

switch ($action){
case 'user_result';
	//get query text to perform search.
	$search_type = $db->filterMySQL($_GET['search_type']);
	$poster = $db->filterMySQL($_GET['poster']);

	//flood check.
	if (flood_check($logged_user, "search") == 1){
		#setup error session.
		$_SESSION['errors'] = $lang['flood'];

		#direct user.
		redirect('Search.php', false, 0);
	}
	
	#update last_search colume.
	$time = time();
	$db->SQL = "UPDATE ebb_users SET last_search='$time' WHERE Username='$logged_user'";
	$db->query();

	//see if user added too many characters in query.
	if (strlen($poster) > 50){
		#setup error session.
		$_SESSION['errors'] = $lang['toolong'];

		#direct user.
		redirect('Search.php', false, 0);
	}
	if (($poster == "") and ($search_type == "")){
		#setup error session.
		$_SESSION['errors'] = $lang['nokeyword'];

		#direct user.
		redirect('Search.php', false, 0);
	}

	//get results
	if ($search_type == "topic"){
		//start pagenation.
		$count = 0;
		$count2 = 0;
		if(!isset($_GET['pg'])){
			$pg = 1;
		}else{
			$pg = $db->filterMySQL($_GET['pg']);
		}

		// Figure out the limit for the query based on the current page number.
		$perPg = $boardPref->getPreferenceValue("per_page");
		$from = (($pg * $perPg) - $perPg);

		$db->SQL = "SELECT Topic, author, bid, tid FROM ebb_topics WHERE author LIKE '$poster' LIMIT $from, $perPg";
		$search_result = $db->query();

		$db->SQL = "SELECT Topic, author, bid, tid FROM ebb_topics WHERE author LIKE '$poster'";
		$num = $db->affectedRows();

		#output pagination.
		$pagenation = pagination("action=user_result&amp;search_type=$search_type&amp;poster=$poster&amp;");

		#see if theres no results.
		if ($num == 0){
			$error = new notifySys($lang['noresults'], true);
			$error->displayMessage();
		}else{
		    #display results.
			$topicSearch = new searchEngine();
			$topicSearch->topicResults();
		}
	}
	if ($search_type == "post"){

		//start pagenation.
		$count = 0;
		$count2 = 0;

		if(!isset($_GET['pg'])){
		    $pg = 1;
		}else{
		    $pg = $db->filterMySQL($_GET['pg']);
		}

		// Figure out the limit for the query based on the current page number.
		$perPg = $boardPref->getPreferenceValue("per_page");
		$from = (($pg * $perPg) - $perPg);
		// Figure out the total number of results in DB:
		$db->SQL = "SELECT re_author, bid, tid, pid FROM ebb_posts WHERE re_author LIKE '$poster' LIMIT $from, $perPg";
		$search_result = $db->query();

		$db->SQL = "SELECT re_author, bid, tid, pid FROM ebb_posts WHERE re_author LIKE '$poster'";
		$num = $db->affectedRows();

		#output pagination.
		$pagenation = pagination("action=user_result&amp;search_type=$search_type&amp;poster=$poster&amp;");

		#see if keyword catches anything.
		if ($num == 0){
			$error = new notifySys($lang['noresults'], true);
			$error->displayMessage();
		}else{
		    #display results.
			$postSearch = new searchEngine();
			$postSearch->postResults();
		}
	}
break;
case 'result';
	//get query text to perform search.
	$search_type = $db->filterMySQL($_POST['search_type']);
	$keyword = $db->filterMySQL($_POST['keyword']);
	$poster = $db->filterMySQL($_POST['poster']);
	$board = $db->filterMySQL($_POST['board']);

	//flood check.
	if (flood_check($logged_user, "search") == 1){
		#setup error session.
		$_SESSION['errors'] = $lang['flood'];

		#direct user.
		redirect('Search.php', false, 0);
	}
	#update last_search colume.
	$time = time();
	$db->SQL = "UPDATE ebb_users SET last_search='$time' WHERE Username='$logged_user'";
	$db->query();

	//see if user added too many characters in query.
	if ((strlen($keyword) > 50) or (strlen($poster) > 25)){
		#setup error session.
		$_SESSION['errors'] = $lang['toolong'];

		#direct user.
		redirect('Search.php', false, 0);
	}
	if ((empty($keyword)) or (empty($search_type))){
		#setup error session.
		$_SESSION['errors'] = $lang['nokeyword'];

		#direct user.
		redirect('Search.php', false, 0);
	}

	//get results
	if ($search_type == "topic"){
		//start pagenation.
		$count = 0;
		$count2 = 0;
		if(!isset($_GET['pg'])){
		    $pg = 1;
		}else{
		    $pg = $db->filterMySQL($_GET['pg']);
		}
		// Figure out the limit for the query based on the current page number.
		$perPg = $boardPref->getPreferenceValue("per_page");
		$from = (($pg * $perPg) - $perPg);

		// Figure out the total number of results in DB:
		$db->SQL = "SELECT Topic, author, bid, tid FROM ebb_topics WHERE author LIKE '$poster' OR Topic LIKE '$keyword' OR Body LIKE '%$keyword%' OR bid LIKE '$board' LIMIT $from, $perPg";
		$search_result = $db->query();

		$db->SQL = "SELECT Topic, author, bid, tid FROM ebb_topics WHERE author LIKE '$poster' OR Topic LIKE '$keyword' OR Body LIKE '%$keyword%' OR bid LIKE '$board'";
		$num = $db->affectedRows();

		#output pagination.
		$pagenation = pagination("action=result&amp;");

		#see if keyword catches anything.
		if ($num == 0){
			$error = new notifySys($lang['noresults'], true);
			$error->displayMessage();
		}else{
		    #display results.
			$postSearch = new searchEngine();
			$postSearch->topicResults();
		}
	}elseif ($search_type == "post"){
		//start pagenation.
		$count = 0;
		$count2 = 0;

		if(!isset($_GET['pg'])){
		    $pg = 1;
		}else{
		    $pg = $db->filterMySQL($_GET['pg']);
		}

		// Figure out the limit for the query based on the current page number.
		$perPg = $boardPref->getPreferenceValue("per_page");
		$from = (($pg * $perPg) - $perPg);

		$db->SQL = "SELECT re_author, bid, tid, pid FROM ebb_posts WHERE re_author LIKE '$poster' OR Body LIKE '%$keyword%' OR bid LIKE '$board' LIMIT $from, $perPg";
		$search_result = $db->query();

		$db->SQL = "SELECT re_author, bid, tid, pid FROM ebb_posts WHERE re_author LIKE '$poster' OR Body LIKE '%$keyword%' OR bid LIKE '$board'";
		$num = $db->affectedRows();

		#output pagination.
		$pagenation = pagination("action=result&amp;");

		#see if keyword catches anything.
		if ($num == 0){
			$error = new notifySys($lang['noresults'], true);
			$error->displayMessage();
		}else{
		    #display results.
			$postSearch = new searchEngine();
			$postSearch->postResults();
		}
	}else{
		$error = new notifySys($lang['nokeyword'], true);
		$error->displayError();
	}
break;
case 'newposts':
	//flood check.
	if (flood_check($logged_user, "search") == 1){
		$error = new notifySys($lang['flood'], true);
		$error->displayError();
	}
	#update last_search colume.
	$time = time();
	$db->SQL = "UPDATE ebb_users SET last_search='$time' WHERE Username='$logged_user'";
	$db->query();

	//find topics
	$db->SQL = "SELECT Topic, author, bid, tid FROM ebb_topics WHERE Original_Date<='$last_visit' or last_update='$last_visit'";
	$search_result = $db->query(); //search query for counter.
	$search_results = $db->query(); //search query for loop.

	//find posts
	$db->SQL = "SELECT re_author, bid, tid, pid FROM ebb_posts WHERE Original_Date<='$last_visit'";
	$search_result2 = $db->query(); //search query for counter.
	$search_results2 = $db->query(); //search query for loop.

	#get total count.
	$count = newpost_counter();
	#get results.
	if($count == 0){
		$error = new notifySys($lang['noresults'], true);
		$error->displayMessage();
	}else{
		#display results.
		$newpostSearch = new searchEngine();
		$newpostSearch->topicResults();
		$newpostSearch->postResults();
	}
break;
default:

	#setup board selection.
	$boardObj = new boardList();
	$boardlist = $boardObj->boardSelect();

	#see if any errors were reported.
	if(isset($_SESSION['errors'])){
	    #format error(s) for the user.
		$errors = var_cleanup($_SESSION['errors']);

		#display validation message.
        $displayMsg = new notifySys($errors, false);
		$displayMsg->displayValidate();

		#destroy errors session data, its no longer needed.
        unset($_SESSION['errors']);
	}

	$tpl = new templateEngine($style, "search");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[search]",
	"LANG-TEXT" => "$lang[searchtext]",
	"LANG-KEYWORD" => "$lang[keyword]",
	"LANG-USERNAME" => "$lang[author]",
	"LANG-SELBOARD" => "$lang[selboard]",
	"BOARDLIST" => "$boardlist",
	"LANG-TEXT2" => "$lang[selectsearchtype]",
	"LANG-TOPIC" => "$lang[topics]",
	"LANG-POST" => "$lang[posts]",
	"LANG-SEARCH" => "$lang[search]"));

	echo $tpl->outputHtml();
}

#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();

ob_end_flush();
?>
