<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * boardmodel.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 11/12/2011
*/

class Boardmodel extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

	/**
	 * Return Name of Board.
	 * @param int $bid BoardID
	 * @return string Board Name
	 * @version 10/26/11
	 */
	public function GetBoardName($bid) {
        $this->db->select('Board')->from('ebb_boards')->where('id', $bid);
		$query = $this->db->get();
		$row = $query->row();
		return $row->Board;
	}

	/**
	 * Grab the type of board.
	 * @param int $bid
	 * @return int type
	 */
	public function GetBoardType($bid) {
		$this->db->select('type')->from('ebb_boards')->where('id', $bid);
		$query = $this->db->get();
		$row = $query->row();
		return $row->type;
	}

	public function ValidateBoardID($bid) {

		//SQL grabbing count of all topics for this board.
		$this->db->select('id')->from('ebb_boards')->where('id', $bid);
		return $this->db->count_all_results();

	}


	/**
	 * Get an array of sub-boards.
	 * @param int $boardID
	 * @return array
	 * @version 11/12/11
	*/
	public function GetSubBoards($boardID) {

		#board sql.
		$this->db->select('id, Board, Description, last_update, Posted_User, Post_Link')->from('ebb_boards')->where('type', 3)->where('Category', $boardID)->order_by("B_Order", "asc");
		$query = $this->db->get();

		//see if we have any records to show.
		if($query->num_rows() > 0) {
			//loop through data and bind to an array.
			foreach ($query->result() as $row) {
				$subboards[] = $row;
			}

			return $subboards;
		} else {
			return FALSE;
		}
	}

	/**
	 * Get a list of all replies to a topic.
	 * @version 9/6/11
	 * @param int $bid Topic ID.
	 * @param int $limit amount to show per page.
	 * @param int $start what entry to start from.
	 * @access public
	 * @return array
	*/
	public function GetTopics($bid, $limit, $start) {
		//SQL to get all topics from defined board.
		$this->db->select('bid, last_update, Topic, author, Posted_User, Post_Link, tid, Views, Type, important, Locked')->from('ebb_topics')->where('bid', $bid)->order_by('last_update', 'desc')->limit($limit, $start);
		$query = $this->db->get();

		//see if we have any records to show.
		if($query->num_rows() > 0) {

			//loop through data and bind to an array.
			foreach ($query->result() as $row) {
				$topics[] = $row;
			}

			return $topics;
		} else {
			return FALSE;
		}
	}

	/**
	 * Get a count of all replies to a topic.
	 * @version 9/6/11
	 * @param int $tid Topic ID.
	 * @access public
	*/
	public function CountReplies($tid) {
		//SQL grabbing count of all topics for this board.
		$this->db->select('pid')->from('ebb_posts')->where('tid', $tid);
		return $this->db->count_all_results();
	}

	/**
	 * Get selected  topic.
	 * @version 9/6/11
	 * @param int $tid Topic ID.
	 * @access public
	*/
	public function ReadTopic($tid) {
		//SQL to get all topics from defined board.
		$this->db->select('author, Topic, Body, Views, Locked, IP, Original_Date, Type, disable_smiles, disable_bbcode')->from('ebb_topics')->where('tid', $tid);
		$query = $this->db->get();

		//see if we have any records to show.
		if($query->num_rows() > 0) {
			return $query->result();
		} else {
			return FALSE;
		}
	}

} //END Class
?>