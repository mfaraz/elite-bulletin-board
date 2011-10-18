<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * posting_helper.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 10/10/2011
*/

/**
* converts text-based smiles into graphical ones.
* @param string $string - the smile code we're trying to make into an image.
* @version 09/21/2011
*/
function smiles($string) {

	#obtain codeigniter object.
	$ci =& get_instance();

 	#SQL to get info data.
 	$ci->db->select('code, img_name')->from('ebb_smiles');
	$smilesQ = $ci->db->get();

	foreach ($smilesQ->result() as $smileRes) {
		$smilecode[] = $smileRes;
		foreach ($smileRes as $smiles) {
			$string = str_replace($smiles, '<img src="images/smiles/'.$smileRes->img_name.'" alt="" />',$string);
		}
	}

	return ($string);
}

/**
 * Outputs the list of smiles available(up to 30).
 * @version 10/10/11
*/
function form_smiles(){
		
	#obtain codeigniter object.
	$ci =& get_instance();
	
	$smile = '';
	$x = 0;
	
	$Query = $ci->db->query("SELECT DISTINCT img_name, code FROM ebb_smiles limit 30");	
	
	foreach ($query->result() as $emoticon) {
		if (($x % 30) == 0) {
			//line break once we've reached our number per row assigned.
			$smile .= '<br />';

			//reset counter for next row.
			$x = 0;
		}

		//output smiles and increment counter.
		$smile .= '<a href="#smiles" title="'.$emoticon->code.'"><img src="images/smiles/'.$emoticon->img_name.'" alt="'.$emoticon->code.'" /></a>';
		$x++;
	}

	return ($smile);
}

/**
 * Formats our messages converting over BBCode tags into HTML content.
 * @param string $string the string to check for our BBCode tags.
 * @param boolean $allowimgs do we want to parse the [img] tag?
 * @version 10/10/11
*/
function BBCode($string, $allowimgs = false) {

	$string = preg_replace('~\[i\](.*?)\[\/i\]~is', '<em>\\1</em>', $string);
    $string = preg_replace('~\[b\](.*?)\[\/b\]~is', '<strong>\\1</strong>', $string);
    $string = preg_replace('~\[u\](.*?)\[\/u\]~is', '<span style="text-decoration:underline;">\\1</span>', $string);
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
 * This is a helper function for the youTube BBCode.
 * @param string $vCode the vcode assigned by youtube.
 * @version 12/12/10
*/
function youtubeParse($vCode) {
	return '<object width="425" height="350"><param name="movie" value="http://www.youtube.com/v/'.$vCode.'"></param><param name="wmode" value="transparent"></param><embed src="http://www.youtube.com/v/'.$vCode.'" type="application/x-shockwave-flash" wmode="transparent" width="425" height="350"></embed></object>';
}

/**
 * Same functionality as BBcode, only used for printer-friendly pages and has a limited number of things it'll parse.
 * @param string $string string to check for BBCode tags.
 * @version 10/10/11
*/
function BBCode_print($string) {

	$string = preg_replace('~\[i\](.*?)\[\/i\]~is', '<i>\\1</i>', $string);
    $string = preg_replace('~\[b\](.*?)\[\/b\]~is', '<b>\\1</b>', $string);
    $string = preg_replace('~\[u\](.*?)\[\/u\]~is', '<span style="text-decoration:underline;">\\1</span>', $string);
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
 * @param string $string item to look for on banlist.
 * @param integer $type (1=foul language;2=spam check)
 * @version 12/12/10
*/
function language_filter($string, $type) {
	
	#obtain codeigniter object.
	$ci =& get_instance();
	
	if((!isset($string)) or (empty($string))){
		die('spam check is null.');
	}
	
	//TODO do i want to keep this?
	if((!isset($type)) or (empty($type))){
		die($lang['invalidcensoraction']);
	}
	
	#determine type action.
   	if($type == 1){		
		#see what to do based on action type.
		$stars = '';
		
		$Query = $ci->db->query("SELECT Original_Word FROM `ebb_censor` WHERE action='1'");
		foreach ($query->result() as $words) {
			if (stristr(trim($string), $words)) {
				for ($i = 1; $i <= strlen($curse_word->Original_Word); $i++) {
					$stars .= "*";
				}
				$string = eregi_replace($curse_word->Original_Word, $stars, trim($string));
				$stars = "";
			}			
		}
	
		return ($string);
	}else{		
		$Query = $ci->db->query("SELECT Original_Word FROM `ebb_censor` WHERE action='2'");
		
		foreach ($query->result() as $spam) {
			//see if anything matches the spam word list.
			if (preg_match("/\b".$spam->Original_Word."\b/i", $string)) {
				$params = array(
					'message' => $this->ci->lang->line('spammer'),
					'eheader' => $this->ci->lang->line('error'),
					'debug' => false,
					'ln' => null,
					'fle' => null);
				$ci->load->library('notifysys', $params, 'SpammerErr');
				$ci->SpammerErr->genericError();
			}
		}
		
		return (null);
	}   
}

/**
 * Prevent users from performing an action too soon from another action.
 * @param string $string item to look for on database.
 * @param string $type (posting;search)
 * @version 12/12/10
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
 * increments the user's post count.
 * @param string $string user to increment count.
 * @version 12/12/10
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
 * updates the last post field.
 * @param integer $bid BoardID.
 * @param string $newlink new link to newest post.
 * @param string $user the nw posted by user.
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
 * @param integer $tid TopicID.
 * @param string $newlink new link to newest post.
 * @param string $user the nw posted by user.
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
