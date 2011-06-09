<?php
if (!defined('IN_EBB') ) {
die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: posting_function.php
Last Modified: 1/15/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

/**
*smiles
*
*converts text-based smiles into graphical ones.
*@param string [str] - the smile code we're trying to make into an iamge.
*@modified 12/12/10
*
*/
function smiles($string) {

	global $db;

	$db->SQL = "SELECT code, img_name FROM ebb_smiles";
	$smiles_q = $db->query();

	while ($row = mysql_fetch_assoc ($smiles_q)) {
		$smilecode = array ($row['code']);
		foreach ($smilecode as $smiles) {
			$string = str_replace($smiles, '<img src="images/smiles/'.$row['img_name'].'" alt="" />',$string);
		}
	}
	return ($string);
}

/**
*form_smiles
*
*Outputs the list of smiles available(up to 30).
*@modified 12/12/10
*
*/
function form_smiles(){

	global $bName, $db;

	if ($bName['Smiles'] == 0){
		$smile = '';
	}else{
		$smile = '';
		$x = 0;
		$db->SQL = "SELECT DISTINCT img_name, code FROM ebb_smiles limit 30";
		$smiles = $db->query();

		while($row = mysql_fetch_assoc($smiles)){
			if (($x % 30) == 0) {
				//line break once we've reached our number per row assigned.
				$smile .= "<br />";

				//reset counter for next row.
				$x = 0;
			}

			//output smiles and increment counter.
			$smile .= '<a href="#smiles" title="'.$row['code'].'"><img src="images/smiles/'.$row['img_name'].'" alt="'.$row['code'].'" /></a>';
			$x++;
		}
	}
	return ($smile);
}

/**
*BBCode
*
*Formats our messages converting over BBCode tags into HTML content.
*@param string [str] - the string to check for our BBCode tags.
*@modified 12/12/10
*
*/
function BBCode($string, $allowimgs = false) {

	$string = preg_replace('~\[i\](.*?)\[\/i\]~is', '<em>\\1</em>', $string);
    $string = preg_replace('~\[b\](.*?)\[\/b\]~is', '<strong>\\1</strong>', $string);
    $string = preg_replace('~\[u\](.*?)\[\/u\]~is', '<u>\\1</u>', $string);
    $string = preg_replace('~\[url\="?(.*?)"?\](.*?)\[\/url\]~is', '<a href="\1" target="_blank">\2</a>', $string);
	//get back to this task later...
	$string = preg_replace('#([^\'"=\]]|^)(http[s]?|ftp[s]?|gopher|irc){1}://([:a-z_\-\\./0-9%~]+){1}(\?[a-z=0-9\-_&;]*)?(\#[a-z0-9]+)?#mi', '\1<a href="\2://\3\4\5" target="_blank">\2://\3\4\5</a>', $string);
	$string = preg_replace('~\[list\](.*?)\[\/list\]~is', '<ul>\1</ul>', $string);
    $string = preg_replace('~\[list\=(.*?)\](.*?)\[\/list\]~is', '<ol start="\1">\2</ol>', $string);
	$string = preg_replace('/\[\*\]\s?(.*?)\n/ms', '<li>\\1</li>', $string);
	$string = preg_replace('~\[size\="?(.*?)"?\](.*?)\[\/size\]~is', '<span style="font-size:\1%">\2</span>', $string);
	$string = preg_replace('~\[center\](.*?)\[\/center\]~is', '<div align="center">\\1</div>', $string);
    $string = preg_replace('~\[right\](.*?)\[\/right\]~is', '<div align="right">\\1</div>', $string);
    $string = preg_replace('~\[left\](.*?)\[\/left\]~is', '<div align="left">\\1</div>', $string);
    $string = preg_replace('~\[sub\](.*?)\[\/sub\]~is', '<sub>\\1</sub>', $string);
    $string = preg_replace('~\[sup\](.*?)\[\/sup\]~is', '<sup>\\1</sup>', $string);
    $string = preg_replace('~\[color=(.*?)\](.*?)\[\/color\]~is', '<span style="color: \\1">\\2</span>', $string);
    $string = preg_replace('~\[quote\](.*?)\[\/quote\]~is', "<div class=\"quoteheader\">Quote:</div><div class=\"quote\">\\1</div>", $string);
    $string = preg_replace('~\[quote=(.*?)\](.*?)\[\/quote\]~is', "<div class=\"quoteheader\">\\1 Wrote:</div><div class=\"quote\">\\2</div>", $string);
    $string = preg_replace('~\[code\](.*?)\[\/code\]~is', "<div class=\"codeheader\">Code:</div><div class=\"code\"><pre style=\"display: inline;\">\\1</pre></div>", $string);
    $string = preg_replace("/\\[youtube(=([0-9]+),([0-9]+))?\\](.+?)\\[\\/youtube\\]/se","youtubeParse('\\4')", $string);


    //we don't want to allow imgs all the time!
    if ($allowimgs == true) {
		$string = preg_replace('~\[img\](.*?)\[\/img\]~is', '<img src="\\1" alt="" />', $string);
    }
    return ($string);
}

/**
*youtubeParse
*
*This is a helper function for the you tube BBCode.
*@param vCode [str] - the vcode assigned by youtube.
*@modified 12/12/10
*
*/
function youtubeParse($vCode) {
	return '<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/'.$vCode.'"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/'.$vCode.'" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>';
}

/**
*BBCode_print
*
*Same functionality as BBcode, only used for printer-friendly pages and has a limited number of things it'll parse.
*@param string [str] - string to check for BBCode tags.
*@modified 12/12/10
*
*/
function BBCode_print($string) {

	$string = preg_replace('~\[i\](.*?)\[\/i\]~is', '<i>\\1</i>', $string);
    $string = preg_replace('~\[b\](.*?)\[\/b\]~is', '<b>\\1</b>', $string);
    $string = preg_replace('~\[u\](.*?)\[\/u\]~is', '<u>\\1</u>', $string);
    $string = preg_replace('~\[url\](.*?)\[\/url\]~is', '<a href="\\1">\\1</a>', $string);
    $string = preg_replace('#([^\'"=\]]|^)(http[s]?|ftp[s]?|gopher|irc){1}://([:a-z_\-\\./0-9%~]+){1}(\?[a-z=0-9\-_&;]*)?(\#[a-z0-9]+)?#mi', '\1<a href="\2://\3\4\5" target="_blank">\2://\3\4\5</a>', $string);
    $string = preg_replace('~\[list\](.*?)\[\/list\]~is', '<li>\\1</li>', $string);
    $string = preg_replace('~\[center\](.*?)\[\/center\]~is', '<div align="center">\\1</div>', $string);
    $string = preg_replace('~\[right\](.*?)\[\/right\]~is', '<div align="right">\\1</div>', $string);
    $string = preg_replace('~\[left\](.*?)\[\/left\]~is', '<div align="left">\\1</div>', $string);
    $string = preg_replace('~\[sub\](.*?)\[\/sub\]~is', '<sub>\\1</sub>', $string);
    $string = preg_replace('~\[sup\](.*?)\[\/sup\]~is', '<sup>\\1</sup>', $string);
    $string = preg_replace('~\[quote\](.*?)\[\/quote\]~is', "<div class=\"quoteheader\">Quote:</div><div class=\"quote\">\\1</div>", $string);
    $string = preg_replace('~\[quote=(.*?)\](.*?)\[\/quote\]~is', "<div class=\"quoteheader\">\\1 Wrote:</div><div class=\"quote\">\\2</div>", $string);
    $string = preg_replace('~\[code\](.*?)\[\/code\]~is', "<div class=\"codeheader\">Code:</div><div class=\"code\"><pre style=\"display: inline;\">\\1</pre></div>", $string);

	return ($string);
}

/**
*language_filter
*
*Checks for foul language and spam-ish words.
*@param string [str] - item to look for on banlist.
*@param type [int] - (1=foul language;2=spam check)
*@modified 12/12/10
*
*/
function language_filter($string, $type) {

	global $db, $lang;
	
	if((!isset($string)) or (empty($string))){
		die('spam check is null.');
	}
	if((!isset($type)) or (empty($type))){
		die($lang['invalidcensoraction']);
	}
	#determine type action.
   	if($type == 1){
		$db->SQL = "SELECT Original_Word FROM `ebb_censor` WHERE action='1'";
		$words = $db->query();

		#see what to do based on action type.
		$stars = '';
		while ($row = mysql_fetch_assoc ($words)) {
			$obscenities = array ($row['Original_Word']);
			foreach ($obscenities as $curse_word) {
				if (stristr(trim($string), $curse_word)) {
					$length = strlen($curse_word);
					for ($i = 1; $i <= $length; $i++) {
						$stars .= "*";
					}
					$string = eregi_replace($curse_word,$stars,trim($string));
					$stars = "";
				}
			}
		}
	}else{
		$db->SQL = "SELECT Original_Word FROM `ebb_censor` where action='2'";
		$words = $db->query();

		while ($row = mysql_fetch_assoc ($words)) {
			//see if anything matches the spam word list.
			if (preg_match("/\b".$row['Original_Word']."\b/i", $string)) {
				die('SPAMMING ATTEMPT!');
			}
		}
	}
   return ($string);
}

/**
*flood_check
*
*Prevent users from performing an action too soon from another action.
*@param string [str] - item to look for on database.
*@param type [str] - (posting;search)
*@modified 12/12/10
*
*/
function flood_check($string, $type){

	global $db;

   	if((!isset($string)) or (empty($string))){
		die('No string found.');
	}
	if((!isset($type)) or (empty($type))){
		die('No Type found.');
	}

	#see what action to perform based on type.
	switch($type){
	case 'posting':
		$currtime = time() - 30;
		$db->SQL = "SELECT last_post FROM ebb_users WHERE Username='$string'";
		$get_time_r = $db->fetchResults();

		#see if user is posting too quickly.
		if ($get_time_r['last_post'] > $currtime){
			$flood = 1;
		}else{
			$flood = 0;
		}
	break;
	case 'search':
		$currtime = time() - 20;
		$db->SQL = "SELECT last_search FROM ebb_users WHERE Username='$string'";
		$get_time_r = $db->fetchResults();

		#see if user is posting too quickly.
		if ($get_time_r['last_search'] > $currtime){
			$flood = 1;
		}else{
			$flood = 0;
		}	
	break;
	}
	return ($flood);
}

/**
*post_count
*
*increments the user's post count.
*@param string [str] - user to increment count.
*@modified 12/12/10
*
*/
function post_count($string){

	global $db;

	//get current post count then add on to it.
	$db->SQL = "select Post_Count from ebb_users where Username='$string'";
	$get_num = $db->fetchResults();

	$increase_count = $get_num['Post_Count'] + 1;
	$db->SQL = "UPDATE ebb_users SET Post_Count='$increase_count' WHERE Username='$string'";
	$db->query();
}

#error here!!! (should have explained what that was (~hindsight)
/**
*update_board
*
*updates the last post field.
*@param bid [int] - BoardID.
*@param newlink [str] - new link to newest post.
*@param user [str] - the nw posted by user.
*@modified 12/12/10
*
*/
function update_board($bid, $newlink, $user){

	global $db, $time; 
	#update lasy post details for the selected board.
	$db->SQL = "update ebb_boards SET last_update='$time' WHERE id='$bid'";
	$db->query();

	//update post link for board.
	$db->SQL = "Update ebb_boards SET Post_Link='$newlink', Posted_User='$user' WHERE id='$bid'";
	$db->query();

	#clear data from read table for the board selected.
	$db->SQL = "DELETE FROM ebb_read_board WHERE Board='$bid'";
	$db->query();

}

#error here!!! (should have explained what that was (~hindsight)
/**
*update_topic
*
*updates the last post field.
*@param tid [int] - TopicID.
*@param newlink [str] - new link to newest post.
*@param user [str] - the nw posted by user.
*@modified 12/12/10
*
*/
function update_topic($tid, $newlink, $user){

	global $db, $time;
	#update lasy post details for the selected topic.
	$db->SQL = "update ebb_topics SET last_update='$time' WHERE tid='$tid'";
	$db->query();

	#clear data from read table for the topic selected.
	$db->SQL = "DELETE FROM ebb_read_topic WHERE Topic='$tid'";
	$db->query();

	//update post link for topic.
	$db->SQL = "Update ebb_topics SET Post_Link='$newlink' WHERE tid='$tid'";
	$db->query();

	//update last poster for topic.
	$db->SQL = "Update ebb_topics SET Posted_User='$user' WHERE tid='$tid'";
	$db->query();

}
?>
