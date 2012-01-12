<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * posting_helper.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 12/01/2011
*/

/**
 * converts text-based smiles into graphical ones.
 * @param string $string - the smile code we're trying to make into an image.
 * @version 11/30/2011
 * @return mixed
*/
function smiles($string) {

	#obtain codeigniter object.
	$ci =& get_instance();

 	#SQL to get info data.
 	$ci->db->select('code, img_name')->from('ebb_smiles');
	$smilesQ = $ci->db->get();

	//loop through data and format.
	foreach ($smilesQ->result() as $smileRes) {
		$string = str_replace($smileRes->code, img('images/smiles/'.$smileRes->img_name), $string);
	}

	return ($string);
}

/**
 * Outputs the list of smiles available(up to 30).
 * @param int $boardPref_smiles Does the board allow smiles?
 * @version 12/01/11
 * @return mixed
*/
function form_smiles($boardPref_smiles = 1){

	#obtain codeigniter object.
	$ci =& get_instance();

	if ($boardPref_smiles == 0){
		$smile = '';
	}else{
		$smile = '';
		$x = 0;

		#SQL to get info data.
		$ci->db->distinct('img_name, code')->from('ebb_smiles')->limit(30);
		$smilesQ = $ci->db->get();

		//loop through to build a list of smiles.
		foreach ($smilesQ->result() as $smileRes) {
			if (($x % 30) == 0) {
				//line break once we've reached our number per row assigned.
				$smile .= "<br />";

				//reset counter for next row.
				$x = 0;
			}

			//setup image properties.
			$image_properties = array(
			  'src' => 'images/smiles/'.$smileRes->img_name,
			  'alt' => $smileRes->code,
			  'title' => $smileRes->code
			);

			//output smiles and increment counter.
			$smile .= '<a href="#smiles" title="'.$smileRes->code.'">'.img($image_properties).'</a>';
			$x++;
		}
	}
	return ($smile);
}

/**
 * Formats our messages converting over BBCode tags into HTML content.
 * @param string $string the string to check for our BBCode tags.
 * @param boolean $allowimgs allow parsing of image tags?
 * @version 12/12/10
 * @return mixed
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
 * This is a helper function for the you tube BBCode.
 * @param vCode [str] - the vcode assigned by youtube.
 * @version 12/12/10
 * @return mixed
*/
function youtubeParse($vCode) {
	return '<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/'.$vCode.'"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/'.$vCode.'" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>';
}

/**
 * Same functionality as BBcode, only used for printer-friendly pages and has a limited number of things it'll parse.
 * @param string [str] - string to check for BBCode tags.
 * @version 12/12/10
 * @return mixed
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
 * Checks for foul language and spam-ish words.
 * @param string [str] item to look for on banlist.
 * @param type [int] (1=foul language;2=spam check)
 * @version 12/01/11
 * @return mixed
*/
function language_filter($string, $type) {

	#obtain codeigniter object.
	$ci =& get_instance();

	#see if an invalid operation was set.
	if((!isset($string)) or (empty($string))){
		show_error($ci->lang->line('invalidcensoraction'), 500, $ci->lang->line('error'));
	}

	#see if anything was entered.
	if((!isset($type)) or (empty($type))){
		show_error($ci->lang->line('nullspam'), 500, $ci->lang->line('error'));
	}

	#grab our banned words..
	$ci->db->select('Original_Word')->from('ebb_censor')->where('action', $type);
	$wordsQ = $ci->db->get();

	#determine type action.
   	if($type == 1){
		#
		# BAD WORD FILTER.
		#

		$stars = '';

		//go through the list and clean-up any bad-words
		foreach ($wordsQ->result() as $wordsRes) {

			if (stristr(trim($string), $wordsRes->Original_Word)) {
				$length = strlen($wordsRes->Original_Word);
				for ($i = 1; $i <= $length; $i++) {
					$stars .= "*";
				}
				$string = eregi_replace($wordsRes->Original_Word,$stars,trim($string));
				$stars = "";
			}
			
		}
	}else{
		#
		# SPAM WORD FILTER.
		#

		//go through the list and stop any attempt to post spam.
		foreach ($wordsQ->result() as $wordsRes) {

			//see if anything matches the spam word list.
			if (preg_match("/\b".$wordsRes->Original_Word."\b/i", $string)) {
				show_error($ci->lang->line('spamwarn'), 500, $ci->lang->line('error'));
			}

		}
	}
   return ($string);
}

/**
 * Prevent users from performing an action too soon from another action.
 * @param string $type (posting;search).
 * @param string $LastPost DB entry for last post time.
 * @param string $LastSearch DB entry for last search time.
 * @version 12/01/11
 * @return boolean
*/
function flood_check($type, $LastPost, $LastSearch){

	#see what action to perform based on type.
	switch($type){
	case 'posting':
		//30 second check.
		$currtime = time() - 30;

		#see if user is posting too quickly.
		if ($LastPost > $currtime){
			$flood = 1;
		}else{
			$flood = 0;
		}
	break;
	case 'search':
		//20 second check.
		$currtime = time() - 20;

		#see if user is posting too quickly.
		if ($LastSearch > $currtime){
			$flood = 1;
		}else{
			$flood = 0;
		}	
	break;
	}
	return ($flood);
}

/**
* increments the user's post count.
* @param string $string user to increment count.
* @version 12/12/10
*/
function post_count($string){

	//may not be using this.

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
 * updates the last post field.
 * @param bid [int] - BoardID.
 * @param newlink [str] - new link to newest post.
 * @param user [str] - the nw posted by user.
 * @version 12/12/10
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
 * updates the last post field.
 * @param tid [int] - TopicID.
 * @param newlink [str] - new link to newest post.
 * @param user [str] - the nw posted by user.
 * @version 12/12/10
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
