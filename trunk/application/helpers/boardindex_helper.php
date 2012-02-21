<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * boardindex_helper.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 11/22/2011
*/

/**
 * Get a total of various things.
 * @version 1/12/12
 * @param int $id any kind of integer.
 * @param string $type The total we're looking for.
 * @access public
*/
function GetCount($id, $type) {

	//grab Codeigniter objects.
	$ci =& get_instance();

	#see what total we want to grab.
	switch($type) {
		case 'TopicCount':
			$ci->db->select('tid')->from('ebb_topics')->where('bid', $id);
			return number_format($ci->db->count_all_results());
		break;
		case 'PostCount':
			$ci->db->select('pid')->from('ebb_posts')->where('bid', $id);
			return number_format($ci->db->count_all_results());
		break;
		case 'TopicReplies':
			$ci->db->select('pid')->from('ebb_posts')->where('tid', $id);
			return number_format($ci->db->count_all_results());
		break;
		case 'TopicViews':
			return number_format($id);
		break;
		case 'PollCount':
			$ci->db->select('tid')->from('ebb_votes')->where('tid', $id);
			return number_format($ci->db->count_all_results());
		break;
		default:
			return FALSE;//invalid choice.
		break;
	}	
}

/**
 * Get a total of votes casted for a defined option.
 * @param integer $vote the vote we're calculating.
 * @param integer $tid the topic id tosearch by
 * @return integer
 * @version 1/12/12
 */
function CalcVotes($vote, $tid) {
	//grab Codeigniter objects.
	$ci =& get_instance();
	$ci->db->select('tid')->from('ebb_votes')->where('Vote', $vote)->where('tid', $tid);
	return $ci->db->count_all_results();
}


/**
 * Check to see defined user casted a vote.
 * @param string $usr Logged In User
 * @param integer $tid TopicID
 * @return boolean
 * @version 1/12/12
 */
function CheckVoteStatus($usr, $tid) {
	
	//set to false if user is not logged in.
	if ($usr == "guest") {
		return FALSE;
	} else {
		//grab Codeigniter objects.
		$ci =& get_instance();
		$ci->db->select('tid')->from('ebb_votes')->where('Username', $usr)->where('tid', $tid);
		$voteCount = $ci->db->count_all_results();

		//see if logged in user already voted.
		if ($voteCount == 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

/**
 * checks to see if a board has neww topics or not.
 * @version 11/22/11
 * @param int $bid Board ID.
 * @param string $user user to check against.
 * @access public
*/
function CheckReadStatus($bid, $user) {

	$readCt = readBoardStat($bid, $user);
	if ($readCt > 0){
		$icon = TRUE; //read
	}else{
		$icon = FALSE; //unread
	}

	return $icon;
}

/**
 * Check read status on a selected board.
 * @version 11/22/11
 * @param integer $tid - Topic ID to select a topic.
 * @param string $user - Username to check against.
 * @return boolean
*
*/
function readTopicStat($tid, $user){

	#obtain codeigniter object.
	$ci =& get_instance();

	#see if user is guest.
	if ($user == "guest") {
	    $icon = 1; //UnRead.
	}else{
		//see if user visited the topic yet.
		$ci->db->select('Topic')->from('ebb_read_topic')->where('Topic', $tid)->where('User', $user);
		$readCt = $ci->db->count_all_results();

		if ($readCt == 0) {
			$icon = false; //UnRead.
		} else {
			$icon = true; //Read.
		}
	}

	return ($icon);
}

/**
 *See if topic has an attachment.
 * @param integer $tid TopicID
 * @param integer $pid PostID (0 by default)
 * @return boolean
 * @version 11/12/11
 */
function HasAttachment($tid) {

	#obtain codeigniter object.
	$ci =& get_instance();

	#get reply count.
	//@TODO: this should probably be in a loop.
	$ci->db->select('pid, tid')->from('ebb_posts')->where('tid', 7);
	$postQ = $ci->db->get();
	$postID = $postQ->row();
	$PostCT = $ci->db->count_all_results();

	if ($PostCT == 0){
		$attachPost = 0;
	} else {
		#see if any attachments are added to the reply of a topic.
		$ci->db->select('id')->from('ebb_attachments')->where('pid', $postID->pid);
		$attachPost = $ci->db->count_all_results();
	}

	//see if topic has attachment.
	$ci->db->select('id')->from('ebb_attachments')->where('tid', $tid);
	$attachTopic = $ci->db->count_all_results();

	//see if we have any attachments.
	if($attachTopic == 1) {
		return TRUE;
	} else {
		if ($attachPost == 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	} //END Topic Attachment Check.
}

/**
 * Get a count of sub-boards.
 * @param int $boardID
 * @return int
 * @version 11/12/11
 */
function GetSubBoardCount($boardID) {
	
	#obtain codeigniter object.
	$ci =& get_instance();

	//SQL grabbing count of all topics for this board.
	$ci->db->select('id')->from('ebb_boards')->where('type', 3)->where('Category', $boardID);
	return $ci->db->count_all_results();
}

/**
 * Obtains a few stats about the board.
 * @version 9/28/11
 * @return int  - results of stats.
*/
function boardStats($type){

    //grab Codeigniter objects.
	$ci =& get_instance();

	#see what we're counting.
	switch($type){
	    case 'member':
			#get member count.
			$ci->db->select('id')->from('ebb_users')->where('active', 1);
			return number_format($ci->db->count_all_results());
	    break;
	    case 'topic':
			#get topic count.
			$ci->db->select('tid')->from('ebb_topics');
			return number_format($ci->db->count_all_results());
		break;
	    case 'post':
			#get post count.
			$ci->db->select('pid')->from('ebb_posts');
			return number_format($ci->db->count_all_results());
	    break;
	    case 'newuser':
			#get newest user.
			$ci->db->select('Username')->from('ebb_users')->where('active', 1)->order_by("Date_Joined", "desc")->limit(1);
			$query = $ci->db->get();
			$newUser = $query->row();
			return $newUser->Username;
	    break;
		case 'guestonline':
			//get total guest online.
			$ci->db->distinct('ip')->from('ebb_online')->where('Username', '');
			return number_format($ci->db->count_all_results());
		break;
		case 'memberonline':
			//get total members online.
			$ci->db->distinct('Username')->from('ebb_online')->where('ip', '');
			return number_format($ci->db->count_all_results());
		break;
	    default:
	        return (0);
	    break;
	}
}


/**
 * Will list all sub-boards linked to a parent board.
 * @version 02/15/12
 * @param int $boardID - Board ID to search for any sub-boards.
 * @access public
*/
function getSubBoard($boardID) {

	//grab Codeigniter objects.
	$ci =& get_instance();

	$subBoardQuery = $ci->db->query("SELECT id, Board FROM ebb_boards WHERE type='3' AND Category=? ORDER BY B_Order", $boardID);
	$countSub = $subBoardQuery->num_rows();
	
	if($countSub == 0){
		$subBoard = '';
	}else{
		$subBoard = $ci->lang->line('subboards').":&nbsp;";

		#counter variable.
		$counter = 0;

		foreach ($subBoardQuery->result() as $row) {

			#see if we've reached the end of our query results.
			if($countSub == 1){
				$marker = '';
			}elseif($counter < $countSub - 1){
				$marker = ',&nbsp;';
			}else{
				$marker = '';
			}

			//TODO: use entity here

			#board rules sql.
			$boardRule = $ci->db->query("SELECT B_Read FROM ebb_board_access WHERE B_id=?", $row->id);
			$readAccess = $boardRule->row();
			
			#see if user can view the board.
			if ($ci->Groupmodel->validateAccess(0, $readAccess->B_Read)){
				$subBoard .= sprintf("<i>%s</i>%s", anchor('boards/viewboard/'.$row->id, $row->Board), $marker);
			}

		} #END forloop
	}

	return $subBoard;
}

/**
 * Format our topic body.
 * @param boolean $topic_smiles Topic Settings to Allow Smiles.
 * @param boolean $board_smiles Board Settings to Allow Smiles.
 * @param boolean $topic_bbcode Topic Settings to Allow BBCode.
 * @param boolean $board_bbcode Board Settings to Allow BBCode.
 * @param boolean $board_Image Board Settings to Allow Images.
 * @param mixed $body Topic Body to format.
 * @version 11/30/11
 * @return string
 */
function FormatTopicBody($topic_smiles, $board_smiles, $topic_bbcode, $board_bbcode, $board_Image, $body) {

	$topicBody = $body;

	#see if user wish to allow smiles.
	if($topic_smiles == 0){
		#see if board allows smiles.
		if ($board_smiles == 1){
			$topicBody = smiles($topicBody);
		}
	}

	#see if user wish to allow bbcode.
	if($topic_bbcode == 0){
		#see if board allow BBCode formatting.
		if ($board_bbcode == 1){
			$topicBody = BBCode($topicBody);
		}

		#see if board allows use of [img] tag.
		if ($board_Image == 1){
			$topicBody = BBCode($topicBody, true);
		}
	}

	return ($topicBody);
}
?>
