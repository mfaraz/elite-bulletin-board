<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * user.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 10/28/2011
*/

class user{

	#define data member.
	
	/**
	 * Username in session.
	 * @var string
	*/
	private $user;
	
	/**
	 *  CodeIgniter object.
	 * @var object
	*/
	private $ci;
	
    /**
	 * Setup User class structure.
	 * @version 3/9/10
	 * @access public
	*/
	public function __construct($uName){
		
		//setup CodeIgniter instance.
		$this->ci =& get_instance();
		
		$this->user = trim($uName);
		
		#run a validation on the username entered.
		if($this->checkUsername() == false){
			$error = new notifySys("USERNAME NOT FOUND!", true, true, __FILE__, __LINE__);
			$error->genericError();
		}
	}

    /**
	 * Clear User data member.
	 * @version 9/28/09
	 * @access public
	*/
	public function __destruct(){
  		unset($this->user);
	}
	
	/**
	 * see if username entered is valid.
	 * @version 10/28/11
	 * @return bool
	 * @access private
	*/
	private function checkUsername(){

	    #check against the database to see if the username match.
		$this->ci->db->select('id')->from('ebb_users')->where('Username', $this->user);
		$validateStatus = $this->ci->db->count_all_results();

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

		#total of new PM messages.
		$this->ci->db->select('Read_Status')->from('ebb_pm')->where('Reciever', $this->user)->where('Read_Status', '');
		$newPm = $this->ci->db->count_all_results();

		//see if we're trying to get a count only.
		if ($ucpMode) {
			return ($newPm);
		} else {
			if($newPm == 0){
				$pmMsg = $this->ci->lang->line('nonewpm');
			}else{
				$pmMsg = $newPm.$this->ci->lang->line('newpm');
			}

			return($pmMsg);
		}
	}

	/**
	 * Obtains user's password salt when having to change their passwords.
	 * @version 10/28/11
	 * @return password salt.
	 * @access public
	*/
	
	public function getPasswordSalt(){

	    #obtain password salt.
		$this->ci->db->select('salt')->from('ebb_users')->where('Username', $this->user)->limit(1);
		$query = $this->ci->db->get();
		$pwdSlt = $query->row();

		return($pwdSlt->salt);
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
