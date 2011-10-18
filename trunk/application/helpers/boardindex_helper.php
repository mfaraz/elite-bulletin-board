<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
	 * boardindex_helper.php
	 * @package Elite Bulletin Board v3
	 * @author Elite Bulletin Board Team <http://elite-board.us>
	 * @copyright  (c) 2006-2011
	 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
	 * @version 10/4/2011
*/

/**
	* Get a total of various things.
	* @version 9/23/11
	* @param int $id Board/Topic/Misc ID. Can be NULL.
	* @param string $type The total we're looking for.
	* @access public
*/
function GetCount($id, $type) {

	//grab Codeigniter objects.
	$ci =& get_instance();

	#see what total we want to grab.
	switch($type) {
		case 'TopicCount':
			$Query = $ci->db->query("select tid AS TopicCount from ebb_topics WHERE bid=?", $id);
			return number_format($Query->num_rows());
		break;
		case 'PostCount':
			$Query = $ci->db->query("select pid AS PostCount from ebb_posts WHERE bid=?", $id);
			return number_format($Query->num_rows());
		break;
	}	
}

/**
	* checks to see if a board has neww topics or not.
	* @version 10/4/11
	* @param int $id Board ID.
	* @param string $user user to check against.
	* @access public
*/
function CheckReadStatus($bid, $user) {

	$readCt = readBoardStat($bid, $user);
	if ($readCt == 1){
		$icon = true; //read
	}else{
		$icon = false; //unread
	}

	return $icon;
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
			$Query = $ci->db->query("SELECT id FROM ebb_users WHERE active='1'");
			return (number_format($Query->num_rows()));
	    break;
	    case 'topic':
			#get topic count.
			$Query = $ci->db->query("SELECT tid FROM ebb_topics");
			return (number_format($Query->num_rows()));
		break;
	    case 'post':
			#get post count.
			$Query = $ci->db->query("SELECT pid FROM ebb_posts");
			return (number_format($Query->num_rows()));
	    break;
	    case 'newuser':
			#get newest user.
			$Query = $ci->db->query("SELECT Username FROM ebb_users WHERE active='1' ORDER BY Date_Joined DESC LIMIT 1");
			$newUser = $Query->row();
			return $newUser->Username;
	    break;
		case 'guestonline':
			//get total guest online.
			$Query = $ci->db->query("SELECT DISTINCT ip FROM ebb_online WHERE Username=''");
			return (number_format($Query->num_rows()));			
		break;
		case 'memberonline':
			//get total members online.
			$Query = $ci->db->query("SELECT DISTINCT Username FROM ebb_online WHERE ip=''");
			return (number_format($Query->num_rows()));
		break;
	    default:
	        return (0);
	    break;
	}
}


/**
	* Will list all sub-boards linked to a parent board.
	* @version 9/26/11
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

			#board rules sql.
			$boardRule = $ci->db->query("SELECT B_Read FROM ebb_board_access WHERE B_id=?", $row->id);
			$readAccess = $boardRule->row();
			
			#see if user can view the board.
			if ($ci->grouppolicy->validateAccess(0, $readAccess->B_Read) == true){
				$subBoard .= '<i><a href="index.php/boards/viewboard/'.$row->id.'">'.$row->Board.'</a></i>'.$marker;
			}

		} #END forloop
	}

	return $subBoard;

}
?>
