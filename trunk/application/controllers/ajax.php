<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * ajax.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 05/30/2012
*/

/**
 * Ajax Controller
 * @abstract EBB_Controller 
 */
class Ajax extends EBB_Controller {
	
	public function __construct() {
 		parent::__construct();
		
		//see if user is calling directly or by AJAX.
		if (!IS_AJAX) {
			exit(show_error($this->lang->line('ajaxerror'), 403, $this->lang->line('error')));
		}

		//see if user is logged in.
		if ($this->logged_user == "guest") {
			exit($this->lang->line('guesterror'));
		}
		
		$this->load->helper(array('posting'));
	}

	/**
	 * search for similar topics
	 * @version 05/30/12
	 * @example index.php/ajax/SimilarTopics
	*/
	public function SimilarTopics() {
		
		//LOAD LIBRARIES
		$this->load->model(array('Boardaccessmodel'));
		$this->load->helper(array('user', 'posting'));

		$q = $this->input->post('topic', TRUE);

		//flood check.
		if (flood_check("search", $this->Usermodel->getLastSearch())) {
			exit($this->lang->line('flood'));
		} else {
			if ($q == "") {
				exit($this->lang->line('nokeyword'));
			} else {
				#update search flood info.
				$data = array(
				"last_search" => time()
				);
				$this->db->where('Username', $this->logged_user);
				$this->db->update('ebb_users', $data);

				//search for similar topics.
				$this->db->select('bid, tid, author, Topic');
				$this->db->from('ebb_topics');
				$this->db->like('Topic', $q, 'after');
				$this->db->or_like('Body', $q, 'after');
				$query = $this->db->get();

				//see if we have any records to show.
				if($query->num_rows() > 0) {
					echo '<strong>'.$this->lang->line('relatedtopics').'</strong><hr />';

					//loop through results.
					foreach ($query->result() as $row) {

						//get rules for board.
						$this->Boardaccessmodel->GetBoardAccess($row->bid);

						//see if the user can access this spot.
						if ($this->Groupmodel->validateAccess(0, $this->Boardaccessmodel->getBRead())){
							echo '<em>'.anchor('/boards/viewtopic/'.$row->tid, $row->Topic).'</em> - '.$row->author.'<br />';
						}
					}
				} else {
					echo $this->lang->line('nosimilar');
				} //END results.
			} //END query keyword check.
		} //END flood check.
	}
	
}