<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 *  EBB_Controller.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 02/29/2012
*/

class EBB_Controller extends CI_Controller {

	/**
	 * define data member.
	*/

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
	 * User's GroupID.
	 * @var int
	 */
	public $gid;

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
	 * @version 02/15/12
	 */
	public function __construct() {

		parent::__construct();
		
		$this->load->helper('user');
		
		//update online data.
		$sessionTimeout = time() - 300;

		//delete any online data from the last 3 minutes.
		$this->db->delete('ebb_online', array('time <' => $sessionTimeout));

		#login setup
		if (($this->input->cookie('ebbUser', TRUE) <> FALSE) OR ($this->session->userdata('ebbUser') <> FALSE)) {

			//see if user is logged in via cookies.
			if ($this->input->cookie('ebbUser', TRUE) <> FALSE) {
				$ebbuser = $this->input->cookie('ebbUser', TRUE);
			} elseif ($this->session->userdata('ebbUser') <> FALSE) {
				$ebbuser = $this->session->userdata('ebbUser');
			} else {
				exit(show_error('INVALID LOGIN METHOD!', 500, $this->lang->line('error')));
			} //END authenication check.

			$params = array(
			  'user' => $ebbuser
			);
			$this->load->library('user', $params);

			//validate login session
			if ($this->user->validateLoginSession($this->session->userdata('ebbLoginKey'), 0)){

				//load user model.
				$this->load->model('Usermodel');
				$this->Usermodel->getUser($ebbuser);

				//setup logged in user.
				$this->gid = $this->Usermodel->getGid();
				$this->logged_user = $this->Usermodel->getUserName();
				$this->style = $this->Usermodel->getStyle();
				$this->timeFormat = $this->Usermodel->getTimeFormat();
				$this->timeZone = $this->Usermodel->getTimeZone();
				$this->lng = $this->Usermodel->getLanguage();
				$this->suspend_length = $this->Usermodel->getSuspendLength();
				$this->suspend_time = $this->Usermodel->getSuspendTime();

				//get group data.
				$this->Groupmodel->GetGroupData($this->gid);

				#see if user is marked as banned.
				if($this->Groupmodel->getPermissionType() == 6){
					exit(show_error($ci->lang->line('banned')));
				}

				//detect group status.
				$this->groupAccess = $this->Groupmodel->getLevel();
				
				//see if a user is either suspended or banned.
				checkBan();

				//update user's onhline status.
				update_whosonline_users($this->logged_user);
			} else {
				exit(show_error('INVALID COOKIE OR SESSION!',500, $this->lang->line('error')));
			}
		} else {
			//guest account.
			$this->logged_user = "guest";
			$this->Groupmodel->IsGuest = TRUE;
			$this->groupAccess = 0;
			$this->groupProfile = 0;
			$this->gid = 0;
			$this->style = $this->preference->getPreferenceValue("default_style");
			$this->timeFormat = $this->preference->getPreferenceValue("timeformat");
			$this->timeZone = $this->preference->getPreferenceValue("timezone");
			$this->lng = $this->preference->getPreferenceValue("default_language");
			
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
