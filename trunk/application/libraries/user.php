<?php
if (!defined('IN_EBB') ) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
 * user.class.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 6/5/2011
*/

class user{

	#define data member.
	private $user;
	
    /**
	*__construct
	*
	*Setup User class structure.
	*
	*@modified 3/9/10
	*
	*@access public
	*/
	public function __construct($uName){
		$this->user = trim($uName);
		
		#run a validation on the username entered.
		if($this->checkUsername() == false){
			$error = new notifySys("USERNAME NOT FOUND!", true, true, __FILE__, __LINE__);
			$error->genericError();
		}
	}

    /**
	*__destruct
	*
	*Clear User data member.
	*
	*@modified 9/28/09
	*
	*@access public
	*/
	public function __destruct(){
  		unset($this->user);
	}
	
	/**
	*checkUsername
	*
	*see if username entered is valid.
	*
	*@modified 10/27/09
	*
	*@return bool
	*
	*@access private
	*/
	private function checkUsername(){
	
	    global $db;

	    #check against the database to see if the username match.
        $db->SQL = "SELECT id FROM ebb_users WHERE Username='".$this->user."' LIMIT 1";
		$validateStatus = $db->affectedRows();

		#setup bool. value to see if user is active or not.
		if($validateStatus == 0){
		    return(false);
		}else{
		    return(true);
		}
	}

	/**
			 * Obtains Status on any new Messages for defined user.
			 * @param string $ucpMode determines if we want it in notification mode or just the count.
			 * @access Public
			 * @version 6/5/2011
			 * @return  total new PMs to user.
		 */
	public function getNewPMCount($ucpMode=false){

		global $db, $lang;

		#total of new PM messages.
		$db->SQL = "SELECT Read_Status FROM ebb_pm WHERE Reciever='".$this->user."' AND Read_Status=''";
		$newPm = $db->affectedRows();

		//see if we're trying to get a count only.
		if ($ucpMode) {
			return ($newPm);
		} else {
			if($newPm == 0){
				$pmMsg = $lang['nonewpm'];
			}else{
				$pmMsg = $newPm.$lang['newpm'];
			}

			return($pmMsg);
		}
	}

	/**
	*userSettings
	*
	*Obtains selected user data from database.
	*
	*@param string $val - user value to fetch.
	*
	*@modified 10/19/09
	*
	*@return string $userPref - fetched value.
	*
	*@access public
	*/
	public function userSettings($val){

		global $db;

	    $uSetting = $db->filterMySQL($val);
		$db->SQL = "SELECT ".$uSetting." FROM ebb_users WHERE Username='".$this->user."' LIMIT 1";
		$userpref = $db->fetchResults();
		
		return ($userpref[$uSetting]);
	}
	
	/**
	*getPasswordSalt
	*
	*Obtains user's password salt when having to change their passwords.
	*
	*@modified 10/27/09
	*
	*@return password salt.
	*
	*@access public
	*/
	
	public function getPasswordSalt(){

	    global $db;

	    #obtain password salt.
        $db->SQL = "SELECT salt FROM ebb_users WHERE Username='".$this->user."' LIMIT 1";
		$pwdSlt = $db->fetchResults();

		return($pwdSlt['salt']);
	}

	/**
	*userWarn
	*
	*List current warning level of user and, if available, allow admin or moderator to raise or lower warning level.
	*
	*@modified 2/9/11
	*
	*@return string $warnBar - output warning level.
	*
	*@access public
	*/
	public function userWarn(){

		global $db, $groupAccess, $groupPolicy, $userGroupPolicy, $styleImgDir, $lang, $tid, $bid, $logged_user;

		#get user's warning level.
		$db->SQL = "SELECT warning_level FROM ebb_users WHERE Username='".$this->user."' LIMIT 1";
		$warn_r = $db->fetchResults();
		$userChk = $db->affectedRows();

		#see if username placed in the function is invalid.
		if($userChk == 0){
			$error = new notifySys($lang['nousernameentered'], true, true, __FILE__, __LINE__);
			$error->genericError();
		}
		#see if user has moderator status, if so let them alter warning level.
		if (($groupAccess == 1) OR ($groupAccess == 2)){
			#see if user they wish to warn is higher in rank than them, if so don't let them set anything.
			if(($groupPolicy->groupAccessLevel() == 2) and ($userGroupPolicy->groupAccessLevel() == 1)){
				$warn_bar = '<div class="warningheader">'.$lang['warnlevel'].'</div><div class="warnlevel"><img src="'.$styleImgDir.'images/bar.gif" alt="'.$lang['warnlevel'].'" height="10" width="'.$warn_r['warning_level'].'" />&nbsp;('.$warn_r['warning_level'].'%)</div>';
			}else{
				#see if user has permission to alter warning value.
				if($groupPolicy->validateAccess(1, 25) == true){
					$warn_bar = '<div class="warningheader">'.$lang['warnlevel'].'</div><div class="warnlevel"><img src="'.$styleImgDir.'images/bar.gif" alt="'.$lang['warnlevel'].'" height="10" width="'.$warn_r['warning_level'].'" />&nbsp;(<a href="manage.php?mode=warn&amp;user='.$this->user.'&amp;bid='.$bid.'&amp;tid='.$tid.'">'.$warn_r['warning_level'].'%</a>)</div>';
				}else{
					$warn_bar = '<div class="warningheader">'.$lang['warnlevel'].'</div><div class="warnlevel"><img src="'.$styleImgDir.'images/bar.gif" alt="'.$lang['warnlevel'].'" height="10" width="'.$warn_r['warning_level'].'" />&nbsp;('.$warn_r['warning_level'].'%)</div>';
				}
			}
		}else{
			#see if user is the actual user.
			if($this->user == $logged_user){
				$warn_bar = '<div class="warningheader">'.$lang['warnlevel'].'</div><div class="warnlevel"><img src="'.$styleImgDir.'images/bar.gif" alt="'.$lang['warnlevel'].'" height="10" width="'.$warn_r['warning_level'].'" />&nbsp;('.$warn_r['warning_level'].'%)</div>';
			}else{
				$warn_bar = '';
			}
		}

		return($warn_bar);
	}

	/**
	*getFeed
	*
	*Grab RSS feed data from defined rss address.
	*
	*@modified 9/28/09
	*
	*@param string $rss - RSS file being parsed.
	*
	*@return string $rssFeed - parsed RSS file.
	*
	*@access public
	*/
	public function getFeed($rss){

		#prime loop var.
		$rssFeed = '';
		foreach ($rss->get_items() as $item){
			$feedLink = $item->get_link(0);
			$itemTitle = $item->get_title();
			$itemDesc = $item->get_description();
			$rssFeed .= '<a href="'.$feedLink.'">'.$itemTitle.'</a><br />'.$itemDesc.'<hr />';
		}

		return ($rssFeed);
	}

	/**
	*latestTopics
	*
	*Grab latest topics created by the user.
	*
	*@modified 3/18/11
	*
	*@return string $latestTopics - outputted results.
	*
	*@access public
	*/
	public function latestTopics(){

		global $db, $groupPolicy;

		$db->SQL = "select tid, bid, author, Topic, Body, Original_Date FROM ebb_topics where author='".$this->user."' Order By Original_Date DESC limit 10";
		$topicQuery = $db->query();

		#prime var.
		$latestTopics = '';
		while($topic = mysql_fetch_array($topicQuery)) {

			//check for the posting rule.
			$db->SQL = "select B_Read from ebb_board_access WHERE B_id='$topic[bid]'";
			$boardRule = $db->fetchResults();

			//see if the user can access this spot.
			if ($groupPolicy->validateAccess(0, $boardRule['B_Read']) == true){

				#if body is over 100 characters, cut it off.
				if(strlen($topic['Body']) > 200){
					$topicBody = substr_replace(var_cleanup($topic['Body']),'[...]',100);
				}else{
					$topicBody = var_cleanup($topic['Body']);
				}

				#output topics
				$latestTopics .= "<b>$topic[Topic]</b><br />$topicBody<hr />";
			}
		}
		return ($latestTopics);
	}

	/**
	*latestPosts
	*
	*Grab latest replies created by the user.
	*
	*@modified 3/81/11
	*
	*@return string $latestPosts - outputted results.
	*
	*@access public
	*/
	public function latestPosts(){

		global $db, $groupPolicy;

		$db->SQL = "SELECT re_author, pid, tid, bid, Body, Original_Date FROM ebb_posts WHERE re_author='".$this->user."' ORDER BY Original_Date DESC LIMIT 10";
		$postQuery = $db->query();

		#prime var.
		$latestPosts = '';
		while($post = mysql_fetch_array($postQuery)) {

			//check for the posting rule.
			$db->SQL = "select B_Read from ebb_board_access WHERE B_id='$post[bid]'";
			$boardRule = $db->fetchResults();

			//see if the user can access this spot.
			if ($groupPolicy->validateAccess(0, $boardRule['B_Read']) == true){

				#if body is over 100 characters, cut it off.
				if(strlen($post['Body']) > 200){
					$postBody = substr_replace(var_cleanup($post['Body']),'[...]',100);
				}else{
					$postBody = var_cleanup($post['Body']);
				}

				#get topic details.
				$db->SQL = "SELECT Topic FROM ebb_topics where tid='$post[tid]'";
				$topic_r = $db->fetchResults();

				#output topics
				$latestPosts .= "<b>$topic_r[Topic]</b><br />$postBody<hr />";
			}
		}
		return ($latestPosts);
	}
}
?>
