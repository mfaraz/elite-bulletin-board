<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 *  EBB_Controller.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 11/16/2011
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
		$this->db->delete('ebb_online', array('time <' => $sessionTimeout));

		//echo var_dump($this->session->all_userdata());

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
				$this->Usermodel->getUser($ebbuser);

				//setup logged in user.
				$this->logged_user = $this->Usermodel->getUserName();
				$this->style = $this->Usermodel->getStyle();
				$this->timeFormat = $this->Usermodel->getTimeFormat();
				$this->timeZone = $this->Usermodel->getTimeZone();
				$this->lng = $this->Usermodel->getLanguage();
				$this->suspend_length = $this->Usermodel->getSuspendLength();
				$this->suspend_time = $this->Usermodel->getSuspendTime();
				
				//see if a user is either suspended or banned.
				//TODO take this out of loginmgr library and place in user helper.
				$this->loginmgr->checkBan();

				//update user's onhline status.
				update_whosonline_users($this->logged_user);
			} else {
				show_error('INVALID COOKIE OR SESSION!',500,"ERROR!");
			}
		} else {
			//guest account.
			$params[0] = 'guest';
			$this->load->library('grouppolicy', $params);
			$this->logged_user = "guest";
			$this->groupAccess = 0;
			$this->groupProfile = 0;
			$this->style = $this->preference->getPreferenceValue("default_style");
			$this->timeFormat = $this->preference->getPreferenceValue("timeformat");
			$this->timeZone = $this->preference->getPreferenceValue("timezone");
			$this->lng = $this->preference->getPreferenceValue("default_language");
			
			$this->load->helper('user');
			
			//keep guest session in tact.
			update_whosonline_guest();

		} //END login session check.

		//language setup.
		$this->lang->load('ebb', $this->lng);

		//load up global settings.
		$this->title = $this->preference->getPreferenceValue("board_name");
		$this->boardUrl = $this->preference->getPreferenceValue("board_url");

	} //END cookie/session check

} //END Class.
?>
