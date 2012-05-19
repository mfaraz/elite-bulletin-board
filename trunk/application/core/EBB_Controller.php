<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 *  EBB_Controller.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 05/18/2012
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
	 * The type of notification being thrown.
	 * @var string
	 */
	public $notifyType;
	
	/**
	 * The notification message to output to user.
	 * @var string
	 */
	public $notifyMsg;

	/**
	 * Loads global data.
	 * @access Public
	 * @version 05/14/12
	 */
	public function __construct() {

		parent::__construct();
		
		#grab any notification messages.
		$this->notifyType = $this->session->flashdata('NotifyType');
		$this->notifyMsg = $this->session->flashdata('NotifyMsg');
		
		#load user helper.
		$this->load->helper('user');
		
		//delete any online data from the last 3 minutes.
		$this->db->delete('ebb_online', array('time <' => SESSION_TIMEOUT));
		
		#login setup
		if ($this->session->userdata('ebbUser') <> FALSE) {

			//see if user is logged in via cookies.
			if ($this->input->cookie('ebbUser', TRUE) <> FALSE) {
				$ebbuser = $this->input->cookie('ebbUser', TRUE);
			} elseif ($this->session->userdata('ebbUser') <> FALSE) {
				$ebbuser = $this->session->userdata('ebbUser');
			} else {
				exit(show_error($this->lang->line('invalidlogin'), 500, $this->lang->line('error')));
			} //END authenication check.

			$params = array(
			  'user' => $ebbuser
			);
			$this->load->library('user', $params);

			//validate login session
			if ($this->user->validateLoginSession($this->session->userdata('ebbLastActive'), $this->session->userdata('ebbLoginKey'), 0)) {

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
					exit(show_error($this->lang->line('banned')));
				}

				//detect group status.
				$this->groupAccess = $this->Groupmodel->getLevel();
				
				//see if a user is either suspended or banned.
				//checkBan();

				//update user's onhline status.
				update_whosonline_users($this->logged_user);
			} else {
				//session is invalid, log user out and clear session data.
				$this->db->where('username', $ebbuser);
				$this->db->delete('ebb_login_session');
				
				//clear online status session.
				$this->db->delete('ebb_online', array('Username' => $this->logged_user));
				
				#clear session data.				
				$this->session->unset_userdata('ebbUser');
				$this->session->unset_userdata('ebbLastActive');
				$this->session->unset_userdata('ebbLoginKey');
				
				#set message to output to user.
				$this->session->set_flashdata('NotifyType', 'warning');
				$this->session->set_flashdata('NotifyMsg', "Your session has expired. Please re-login."); //$this->lang->line('expiredsess')
				redirect('/login/Login', 'location'); //session expired.
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

	}
	
	#
	# CI FORM VALIDATION METHODS.
	#
	
	/**
	 * Validates CAPTCHA.
	 * @param string $str the value we're validating.
	 * @return boolean
	 * @version 05/04/12
	 * @access public
	*/
	public function ValidateCaptcha($str) {

		if (sha1($str) <> $this->session->userdata("CAPTCHA_Ans")) {
			$this->form_validation->set_message('ValidateCaptcha', $this->lang->line('captchanomatch'));
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	 * Validate Email.
	 * @param string $str the form value under validation.
	 * @return boolean
	 * @version 05/07/12
	 * @access public 
	 */
	public function ValidateEmail($str) {
		
		#Level 1 - see if the MX record is valid.
		if (($this->preference->getPreferenceValue('mx_check') == 1) and (checkdnsrr(array_pop(explode("@",$str)),"MX"))) {
			#Level 2 - validate email isn't blacklisted.
			$checkDomain = explode("@", $str);
			$this->db->select('ban_email')->from('ebb_banlist_email')->where('ban_wildcard', 1)->like('ban_email', $checkDomain)->or_where('ban_email', $str);
			if ($this->db->count_all_results() == 0) {
				#Level 3 - ensure this email isn't already in use.
				$this->db->select('Email')->from('ebb_users')->where('Email', $str);
				if ($this->db->count_all_results() == 0) {
					return TRUE;
				} else {
					$this->form_validation->set_message('ValidateEmail', $this->lang->line('emailexist'));
					return FALSE;
				}
			} else {
				$this->form_validation->set_message('ValidateEmail', $this->lang->line('emailban'));
				return FALSE;	
			}
		} else {
			$this->form_validation->set_message('ValidateEmail', $this->lang->line('invalidemail'));
			return FALSE;
		}
		
	}

	/**
	 * Validates the username is not banned or in use.
	 * @param string $str the value we're validating
	 * @return boolean 
	 * @access public
	 * @version 05/07/12
	 */
	public function ValidateUserName($str) {
		#Level 1 - Validiate username isn't banned.
		$this->db->select('ban_user')
		  ->from('ebb_banlist_user')
		  ->where('ban_wildcard', 1)
		  ->like('ban_user', $str)
		  ->or_where('ban_user', $str);
		
		if ($this->db->count_all_results() == 0) {
			#Level 2 - validate the usename isn't already in use.
			$this->db->select('Username')->from('ebb_users')->where('Username', $str);
			if ($this->db->count_all_results() == 0) {
				return TRUE;
			} else {
				$this->form_validation->set_message('ValidateUserName', $this->lang->line('usernameexist'));
			return FALSE;
			}
		} else {
			$this->form_validation->set_message('ValidateUserName', $this->lang->line('usernameblacklisted'));
			return FALSE;
		}
	}
	
	/**
	 * Validate the info entered on the password recovery form.
	 * @param string $str The value from the form.
	 * @return boolean 
	 * @version 05/15/12
	 */
	public function ValidateAccount($str) {
		
		$this->db->select('id')
		  ->from('ebb_users')
		  ->where('Username', $str)
		  ->or_where('Email', $str)
		  ->limit(1);
		
		if ($this->db->count_all_results() == 0) {
			$this->form_validation->set_message('ValidateAccount', $this->lang->line('invalidrecoveryinfo'));
			return FALSE;
		} else {
			return TRUE;
		}

	}

} //END Class.
