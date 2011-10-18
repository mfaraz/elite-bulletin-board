<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 *  EBB_Controller.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 10/8/2011
*/

class EBB_Controller extends CI_Controller {

	#
	#define data member.
	#

	/**
	 * Current Logged In user.
	 * @var string
	 */
	public $logged_user;

	/**
	 *  User's Group Access Level.
	 * @var intege
	 */
	public $groupAccess;

	/**
	 * User's Group Profile.
	 * @var integer
	 */
	public $groupProfile;

	/**
	 * User's Style selection.
	 * @var integer
	 */
	public $style;

	/**
	 * Time format.
	 * @var string
	 */
	public $timeFormat;

	/**
	 * Time Zone
	 * @var string
	 */
	public $timeZone;

	/**
	 * Language
	 * @var string
	 */
	public $lng;

	/**
	 * Board Title
	 * @var string
	 */
	public $title;
	
	/**
	 * Board URL
	 * @var string 
	 */
	public $boardUrl;
	
	/**
	 * Length of User's Suspension.
	 * @var integer
	 */
	public $suspend_length;
	
	/**
	 * Date user got suspended..
	 * @var integer
	 */
	public $suspend_time;

	/**
	 * Loads global data.
	 * @access Public
	 * @version 9/13/2011
	 */
	public function __construct() {

		parent::__construct();
		
		$this->load->helper('user');
		
		//update online data.
		$sessionTimeout = time() - 300;

		//delete any old entries
		$this->db->query("DELETE FROM ebb_online WHERE time<?", $sessionTimeout);

		#login setup
		if (($this->input->cookie('ebbuser', TRUE) <> null && ($this->input->cookie('ebbpass', TRUE) <> null)) OR ($this->session->userdata('ebbuser') <> FALSE) && ($this->session->userdata('ebbpass') <> FALSE)) {
			//see if user is logged in via cookies.
			if ($this->input->cookie('ebbuser', TRUE) <> null) {				
				$ebbuser = $this->input->cookie('ebbuser', TRUE);
				$params = array('usr' => $this->input->cookie('ebbuser', TRUE), 'pwd' => $this->input->cookie('ebbpass', TRUE));
				$this->load->library('loginmgr', $params);
			} elseif ($this->session->userdata('ebbuser') <> FALSE){
				$ebbuser = $this->session->userdata('ebbuser');
				$params = array('usr' => $this->session->userdata('ebbuser'), 'pwd' => $this->session->userdata('ebbpass'));
				$this->load->library('loginmgr', $params);
			} else {
				show_error('INVALID LOGIN METHOD!');
			} //END authenication check.

			//validate login session
			if ($this->loginmgr->validateLoginSession()){
				
				#perform session credibility check & refresh session ID.
				$this->loginmgr->validateSession();
				
				//get group data.
				$params[0] = $ebbuser;
				$this->load->library('grouppolicy', $params);

				//detect group status.
				$this->groupAccess = $this->grouppolicy->groupAccessLevel();
				$this->groupProfile = $this->grouppolicy->getGroupProfile();

				//load user model.
				$this->load->model('Usermodel');
				$this->Usermodel->GetBasicUserData($ebbuser);

				//setup logged in user.
				$this->logged_user = $this->Usermodel->Username;
				$this->style = $this->Usermodel->Style;
				$this->timeFormat = $this->Usermodel->Time_format;
				$this->timeZone = $this->Usermodel->Time_Zone;
				$this->lng = $this->Usermodel->Language;
				$this->suspend_length = $this->Usermodel->suspend_length;
				$this->suspend_time = $this->Usermodel->suspend_time;
				
				//see if a user is either suspended or banned.
				$this->loginmgr->checkBan();

				//update user's onhline status.
				update_whosonline_users($this->logged_user);
			} else {
				show_error('INVALID COOKIE OR SESSION!',500,"ERROR!");
			}
		} else {
		//if ((!$this->session->userdata('ebbuser')) OR ($this->input->cookie('ebbuser', TRUE) == null)) {
			//guest account.
			$params[0] = 'guest';
			$this->load->library('grouppolicy', $params);
			$this->logged_user = "guest";
			$this->groupAccess = 0;
			$this->groupProfile = 0;
			$this->style = $this->preference->getPreferenceValue("default_style")->pref_value;
			$this->timeFormat = $this->preference->getPreferenceValue("timeformat")->pref_value;
			$this->timeZone = $this->preference->getPreferenceValue("timezone")->pref_value;
			$this->lng = $this->preference->getPreferenceValue("default_language")->pref_value;
			
			$this->load->helper('user');
			
			//keep guest session in tact.
			update_whosonline_guest();

		} //END login session check.

		//language setup.
		$this->lang->load('ebb', $this->lng);

		//load up global settings.
		$this->title = $this->preference->getPreferenceValue("board_name")->pref_value;
		$this->boardUrl = $this->preference->getPreferenceValue("board_url")->pref_value;

	} //END cookie/session check

} //END Class.
?>
