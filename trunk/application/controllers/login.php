<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * login.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 05/15/2012
*/

/**
 * Login Controller
 * @abstract EBB_Controller 
 */
class Login extends EBB_Controller {

    public function __construct() {
 		parent::__construct();
		$this->load->helper(array('common', 'posting'));
	}

	/**
	 * Index Page for this controller.
	 * @version 10/11/11
	 * @access public
	 * @example index.php/login
	*/
	public function index() {
		redirect('/login/Login', 'location'); //redirect user to login form.
	}
	
	/**
	 * Processes login form and logs user in.
	 * @version 05/07/12
	 * @access public
	 * @example index.php/login/LogIn
	*/
	public function LogIn() {

    	// LOAD LIBRARIES
        $this->load->library(array('encrypt', 'form_validation'));
        $this->load->helper(array('form', 'user'));

        $this->form_validation->set_rules('username', $this->lang->line('nouser'), 'required');
        $this->form_validation->set_rules('password', $this->lang->line('nopass'), 'required');
		$this->form_validation->set_error_delimiters('<div class="ui-widget" style="width: 45%;"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;text-align:left;"><p id="validateResSvr">', '</p></div></div>');

		//see if any validation rules failed.
		if ($this->form_validation->run() == FALSE) {
			#if Group Access property is not 0, redirect user.
			if ($this->groupAccess <> 0) {
				//show success message.
				$this->session->set_flashdata('NotifyType', 'warning');
				$this->session->set_flashdata('NotifyMsg', $this->lang->line('alreadyloggedin'));

				#direct user to their previous location.
				redirect('/', 'location');
				exit();
			} else {
				//load breadcrumb library
				$this->load->library('breadcrumb');

				// add breadcrumbs
				$this->breadcrumb->append_crumb($this->title, '/boards/');
				$this->breadcrumb->append_crumb($this->lang->line('login'), '/login');

				//render to HTML.
				echo $this->twig->render($this->style, 'login', array (
				'boardName' => $this->title,
				'BOARD_URL' => $this->boardUrl,
				'APP_URL' => $this->boardUrl.APPPATH,
				'NOTIFY_TYPE' => $this->notifyType,
				'NOTIFY_MSG' =>  $this->notifyMsg,
				'LANG' => $this->lng,
				'groupAccess' => $this->groupAccess,
				'LANG_WELCOME'=> $this->lang->line('loggedinas'),
				'LANG_WELCOMEGUEST' => $this->lang->line('welcomeguest'),
				'LOGGEDUSER' => $this->logged_user,
				'LANG_JSDISABLED' => $this->lang->line('jsdisabled'),
				'LANG_INFO' => $this->lang->line('info'),
				'LANG_LOGIN' => $this->lang->line('login'),
				'LANG_LOGOUT' => $this->lang->line('logout'),
				'LOGINFORM' => form_open('login/LogIn', array('name' => 'frmQLogin')),
				'LOGINFORM_FULL' => form_open('login/LogIn', array('name' => 'frmLogin')),
				'VALIDATION_USERNAME' => form_error('username'),
				'VALIDATION_PASSWORD' => form_error('password'),
				'LANG_USERNAME' => $this->lang->line('username'),
				'LANG_REGISTER' => $this->lang->line('register'),
				'LANG_PASSWORD' => $this->lang->line('pass'),
				'LANG_FORGOT' => $this->lang->line('forgot'),
				'LANG_REMEMBERTXT' => $this->lang->line('remembertxt'),
				'LANG_QUICKSEARCH' => $this->lang->line('quicksearch'),
				'LANG_SEARCH' => $this->lang->line('search'),
				'LANG_CP' => $this->lang->line('admincp'),
				'LANG_NEWPOSTS' => $this->lang->line('newposts'),
				'LANG_HOME' => $this->lang->line('home'),
				'LANG_HELP' => $this->lang->line('help'),
				'LANG_MEMBERLIST' => $this->lang->line('members'),
				'LANG_PROFILE' => $this->lang->line('profile'),
				'LANG_POWERED' => $this->lang->line('poweredby'),
				'BREADCRUMB' =>$this->breadcrumb->output()
				));
			}
		} else {

            //setup login object.
            $params = array(
				  'usr' => $this->input->post('username', TRUE),
				  'pwd' => $this->input->post('password', TRUE)
				  );
			$this->load->library('auth', $params);

            //see if we want to keep user logged in on next visit.
            $remember = $this->input->post('auto_login', TRUE);
			
			#see if login was found valid.
			if($this->auth->validateLogin()) {
			
			    #see if user is inactive.
				if($this->auth->isActive() == false) {
				    $this->session->set_flashdata('NotifyType', 'error');
					$this->session->set_flashdata('NotifyMsg', $this->lang->line('inactiveuser'));

					redirect('/login/LogIn', 'location');
				} else {
				
					#see if board is disabled.
					if($this->preference->getPreferenceValue("board_status") == 0){
						#see if user has proper rights to access under this limited operational status.
                        $this->load->model('Groupmodel', 'alc');
                        $this->load->model('Usermodel', 'usr');

                        //get group data.
                        $this->usr->getUser($this->input->post('username', TRUE));
                        $this->alc->GetGroupData($this->usr->getGid());


						if($this->alc->getLevel() == 1){
							#clear any failed login attempts from their record.
							$this->auth->clearFailedLogin();

							#setup cookie or session(based on user's preference.
							$this->auth->logOn($remember);

							#direct user to their previous location.
       						redirect('/', 'location');
						}else{
							#setup error session.
							$this->session->set_flashdata('NotifyType', 'error');
                            $this->session->set_flashdata('NotifyMsg', $this->lang->line('offlinemsg'));

							#direct user.
       						redirect('/', 'location');
						}
					}else{
						#clear any failed login attempts from their record.
						$this->auth->clearFailedLogin();

						#setup cookie or session(based on user's preference.
						$this->auth->logOn($remember);
						
						//show success message.
						$this->session->set_flashdata('NotifyType', 'success');
						$this->session->set_flashdata('NotifyMsg', $this->lang->line('loggedinas'). $this->session->userdata('ebbUser'));

						#direct user to their previous location.
	     				redirect('/', 'location');
					}
				
				} //END active validation.
			
			
			} else{
	        	#get current failed login count
				if($this->auth->getFailedLoginCt() == 5){
				    #deactivate the user's account(for their safety).
					$this->auth->deactivateUser();

				    #alert user of reaching their limit of incorrect login attempts.
					$this->session->set_flashdata('NotifyType', 'error');
     				$this->session->set_flashdata('NotifyMsg', $this->lang->line('lockeduser'));

					#direct user.
     				redirect('/login/LogIn', 'location');
				}else{
				    #add to failed login count.
					$this->auth->setFailedLogin();

					#setup error session.
					$this->session->set_flashdata('NotifyType', 'error');
                    $this->session->set_flashdata('NotifyMsg', $this->lang->line('invalidlogin'));

					#direct user.
     				redirect('/login/LogIn', 'location');
				}
			} //END login validation.
			
		} //END form validation.
	}
	
	/**
	 * Activates inactive account.
	 * @version 05/15/12
	 * @access public
	 * @example index.php/login/ActivateAccount/12345/user
	*/
	public function ActivateAccount($key, $u) {
	
		$this->db->select('active')
		  ->from('ebb_users')
		  ->where('act_key', $key)
		  ->where('Username', $u);
		$query = $this->db->get();

		//see if we have any records to show.
		if($query->num_rows() == 1) {
			$userData = $query->row();
			
			//see if user has already been activated.
			if ($userData->active == 1) {
				#setup error session.
				$this->session->set_flashdata('NotifyType', 'warning');
				$this->session->set_flashdata('NotifyMsg', $this->lang->line('alreadyactive'));
				
				#direct user.
				redirect('/', 'location');
				exit();
			} else {
				//set user as active.
				$data = array('active' => 1);
				$this->db->where('Username', $u);
				$this->db->update('ebb_users', $data);

				#setup error session.
				$this->session->set_flashdata('NotifyType', 'success');
				$this->session->set_flashdata('NotifyMsg', $this->lang->line('correctinfo'));
				
				#direct user.
				redirect('/login/LogIn', 'location');
			}
		} else {
			#setup error session.
			$this->session->set_flashdata('NotifyType', 'error');
			$this->session->set_flashdata('NotifyMsg', $this->lang->line('incorrectinfo'));

			#direct user.
			redirect('/login/LogIn', 'location');
		}
	}
	
	/**
	 * Password recovery View. Also validate & process lost password request.
	 * @version 05/15/12
	 * @access public
	 * @example index.php/login/PasswordRecovery
	*/
	public function PasswordRecovery() {

		//LOAD LIBRARIES
        $this->load->library(array('encrypt', 'email', 'form_validation', 'user_agent', 'datetime_52'));
        $this->load->helper(array('form', 'user'));
		
		//setup validation rules.
        $this->form_validation->set_rules('recover_info', $this->lang->line('username').'/'.$this->lang->line('email'), 'required|callback_ValidateAccount|xss_clean');
		$this->form_validation->set_error_delimiters('<div class="ui-widget" style="width: 45%;"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;text-align:left;"><p id="validateResSvr">', '</p></div></div>');
		
		//see if any validation rules failed.
		if ($this->form_validation->run() == FALSE) {
			#if Group Access property is not 0, redirect user.
			if ($this->groupAccess <> 0) {
				//show success message.
				$this->session->set_flashdata('NotifyType', 'warning');
				$this->session->set_flashdata('NotifyMsg', $this->lang->line('alreadyloggedin'));

				#direct user to their previous location.
				redirect('/', 'location');
				exit();
			} else {
				//load breadcrumb library
				$this->load->library('breadcrumb');

				// add breadcrumbs
				$this->breadcrumb->append_crumb($this->title, '/boards/');
				$this->breadcrumb->append_crumb($this->lang->line('login'), '/login');
				$this->breadcrumb->append_crumb($this->lang->line('passwordrecovery'), '/login/PasswordRecovery');		

				//render to HTML.
				echo $this->twig->render($this->style, 'lostpassword', array (
				'boardName' => $this->title,
				'BOARD_URL' => $this->boardUrl,
				'APP_URL' => $this->boardUrl.APPPATH,
				'NOTIFY_TYPE' => $this->session->flashdata('NotifyType'),
				'NOTIFY_MSG' =>  $this->session->flashdata('NotifyMsg'),
				'LANG' => $this->lng,
				'groupAccess' => $this->groupAccess,
				'LANG_WELCOME'=> $this->lang->line('loggedinas'),
				'LANG_WELCOMEGUEST' => $this->lang->line('welcomeguest'),
				'LOGGEDUSER' => $this->logged_user,
				'LANG_JSDISABLED' => $this->lang->line('jsdisabled'),
				'LANG_INFO' => $this->lang->line('info'),
				'LANG_LOGIN' => $this->lang->line('login'),
				'LANG_LOGOUT' => $this->lang->line('logout'),
				'LOGINFORM' => form_open('login/LogIn', array('name' => 'frmQLogin')),
				'PWDRECOVERFORM' => form_open('login/PasswordRecovery', array('name' => 'frmPasswordRecovery')),
				'VALIDATION_USERNAME_EMAIL' => form_error('recover_info'),
				'LANG_USERNAME' => $this->lang->line('username'),
				'LANG_REGISTER' => $this->lang->line('register'),
				'LANG_PASSWORD' => $this->lang->line('pass'),
				'LANG_FORGOT' => $this->lang->line('forgot'),
				'LANG_REMEMBERTXT' => $this->lang->line('remembertxt'),
				'LANG_QUICKSEARCH' => $this->lang->line('quicksearch'),
				'LANG_SEARCH' => $this->lang->line('search'),
				'LANG_CP' => $this->lang->line('admincp'),
				'LANG_NEWPOSTS' => $this->lang->line('newposts'),
				'LANG_HOME' => $this->lang->line('home'),
				'LANG_HELP' => $this->lang->line('help'),
				'LANG_MEMBERLIST' => $this->lang->line('members'),
				'LANG_PROFILE' => $this->lang->line('profile'),
				'LANG_POWERED' => $this->lang->line('poweredby'),
				'LANG_POSTEDBY' => $this->lang->line('Postedby'),
				'BREADCRUMB' =>$this->breadcrumb->output(),
				'LANG_EMAIL' => $this->lang->line('email'),
				'LANG_PWDRECOVER' => $this->lang->line('passwordrecovery'),
				'LANG_GETPASS' => $this->lang->line('getpassword')
				));
			}
		} else {
			#generate new activation tokin.
			$act_key = md5(makeRandomPassword());
			$pwdRecoveryReq = time();
			$newPwd = makeRandomPassword();
			
			//create blowfish hash.
			$hash = makeHash($newPwd);
			
			$data = array(
			  'Password' => $hash,
			  'failed_attempts' => 0,
			  'active' => 0,
			  'act_key' => $act_key,
			  'password_recovery_date' => $pwdRecoveryReq
			  );
			
			#get username.
			$this->db->select('Username, Email')
			  ->from('ebb_users')
			  ->where('Username', $this->input->post('recover_info', TRUE))
			  ->or_where('Email', $this->input->post('recover_info', TRUE))
			  ->limit(1);
			$query = $this->db->get();
			$userData = $query->row();

			//load user model.
			$this->load->model('Usermodel');
			
			#set username field.
			$this->Usermodel->setUserName($userData->Username);
			
			#update user.
			$this->Usermodel->UpdateUser($data);
			
			#email user.
			$config = array();
			if ($this->preference->getPreferenceValue("mail_type") == 2) {
				$config['protocol'] = 'sendmail';
				$config['mailpath'] = $this->preference->getPreferenceValue("sendmail_path");
				$this->email->initialize($config);
			} elseif ($this->preference->getPreferenceValue("mail_type") == 0) {
				$config['protocol'] = 'smtp';
				$config['smtp_host'] = $this->preference->getPreferenceValue("smtp_host");
				$config['smtp_user'] = $this->preference->getPreferenceValue("smtp_user");
				$config['smtp_pass'] = $this->preference->getPreferenceValue("smtp_pwd");
				$config['smtp_port'] = $this->preference->getPreferenceValue("smtp_port");
				$config['smtp_timeout'] = $this->preference->getPreferenceValue("smtp_timeout");
				$this->email->initialize($config);
			}
			
			//send out email.
			$this->email->to($userData->Email);
			$this->email->from($this->preference->getPreferenceValue("board_email"), $this->title);
			$this->email->subject($this->lang->line('passwordrecovery'));
			$this->email->message($this->twig->renderNoStyle('/emails/'.$this->lng.'/eml_pwd_reset.twig', array(
			  'TimeFormat' => $this->timeFormat,
			  'TimeZone' => $this->timeZone,
			  'USERNAME' => $userData->Username,
			  'NEW_PWD' => $newPwd,
			  'TITLE' => $this->title,
			  'BOARDADDR' => $this->boardUrl,
			  'KEY' => $act_key,
			  'IP_ADDR' => detectProxy(),
			  'PWD_RECOVERY_REQ' => $pwdRecoveryReq,
			  'USER_AGENT' => $this->agent->agent_string()
			)));

			//send out email.
			//$this->email->send();
			
			if ($this->email->send()) {
				#let user know their account is created.
				$this->session->set_flashdata('NotifyType', 'success');
				$this->session->set_flashdata('NotifyMsg', $this->lang->line('newpwdsent'));

				#direct user.
				redirect('/login/LogIn', 'location');
			} else {
				log_message('error', $this->email->print_debugger()); //log error for debugging.
				show_error($this->lang->line('pwdrecoveremlfail'),500, $this->lang->line('error'));
			}
		}
	}

    /**
	 * Logs user out and clears all active login sessions.
	 * @version 05/11/12
	 * @access public
	 * @example index.php/login/LogOut
	*/
	public function LogOut() {

		//session is invalid, log user out and clear session data.
		$this->db->where('username', $this->logged_user);
		$this->db->delete('ebb_login_session');
		
		//clear online status session.
		$this->db->delete('ebb_online', array('Username' => $this->logged_user));

		#clear session data.				
		$this->session->unset_userdata('ebbUser');
		$this->session->unset_userdata('ebbLastActive');
		$this->session->unset_userdata('ebbLoginKey');
		
		#direct user.
		redirect('/', 'location');

	}

	/**
	 * Create New User form submit action.
	 * @version 05/15/12
	 * @access public
	 * @example index.php/login/register
	*/
	public function register() {

		//LOAD LIBRARIES
        $this->load->library(array('encrypt', 'email', 'form_validation'));
        $this->load->helper(array('form', 'user'));

		//setup validation rules.
        $this->form_validation->set_rules('username', $this->lang->line('username'), 'required|alpha_numeric|callback_ValidateUserName|xss_clean');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'required|xss_clean');
		$this->form_validation->set_rules('password_confirm', $this->lang->line('confirmpass'), 'required|matches[password]|xss_clean');
		$this->form_validation->set_rules('email', $this->lang->line('email'), 'required|valid_email|xss_clean'); //callback_ValidateEmail|
		$this->form_validation->set_rules('time_format', $this->lang->line('timeformat'), 'required|xss_clean');
		$this->form_validation->set_rules('time_zone', $this->lang->line('timezone'), 'required|xss_clean');
		$this->form_validation->set_rules('language', $this->lang->line('nolang'), 'required|xss_clean');
		$this->form_validation->set_rules('style', $this->lang->line('style'), 'required|numeric|xss_clean');
		$this->form_validation->set_rules('captcha', $this->lang->line('captcha'), 'required|numeric|callback_ValidateCaptcha|xss_clean');
		$this->form_validation->set_error_delimiters('<div class="ui-widget" style="width: 45%;"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;text-align:left;"><p id="validateResSvr">', '</p></div></div>');

		//see if any validation rules failed.
		if ($this->form_validation->run() == FALSE) {
			#if Group Access property is not 0, redirect user.
			if ($this->groupAccess <> 0) {
				//show success message.
				$this->session->set_flashdata('NotifyType', 'warning');
				$this->session->set_flashdata('NotifyMsg', $this->lang->line('alreadyreg'));

				#direct user to their previous location.
				redirect('/', 'location');
				exit();
			} else {
				//load breadcrumb library
				$this->load->library('breadcrumb');
				$this->load->helper('user');

				// add breadcrumbs
				$this->breadcrumb->append_crumb($this->title, '/boards/');
				$this->breadcrumb->append_crumb($this->lang->line('register'), '/login/register');

				//render to HTML.
				echo $this->twig->render($this->style, 'register', array (
				'boardName' => $this->title,
				'BOARD_URL' => $this->boardUrl,
				'APP_URL' => $this->boardUrl.APPPATH,
				'NOTIFY_TYPE' => $this->notifyType,
				'NOTIFY_MSG' =>  $this->notifyMsg,
				'LANG' => $this->lng,
				'TimeZone' => TimeZoneList($this->timeZone),
				'groupAccess' => $this->groupAccess,
				'LANG_WELCOME'=> $this->lang->line('loggedinas'),
				'LANG_WELCOMEGUEST' => $this->lang->line('welcomeguest'),
				'LOGGEDUSER' => $this->logged_user,
				'LANG_JSDISABLED' => $this->lang->line('jsdisabled'),
				'LANG_INFO' => $this->lang->line('info'),
				'LANG_LOGIN' => $this->lang->line('login'),
				'LANG_LOGOUT' => $this->lang->line('logout'),
				'LOGINFORM' => form_open('login/LogIn', array('name' => 'frmQLogin')),
				'REGISTERFORM' => form_open('login/register', array('name' => 'frmRegister')),
				'LANG_USERNAME' => $this->lang->line('username'),
				'VALIDATION_USERNAME' => form_error('username'),
				'LANG_REGISTER' => $this->lang->line('register'),
				'LANG_PASSWORD' => $this->lang->line('pass'),
				'VALIDATION_PASSWORD' => form_error('password'),
				'LANG_FORGOT' => $this->lang->line('forgot'),
				'LANG_REMEMBERTXT' => $this->lang->line('remembertxt'),
				'LANG_QUICKSEARCH' => $this->lang->line('quicksearch'),
				'LANG_SEARCH' => $this->lang->line('search'),
				'LANG_CP' => $this->lang->line('admincp'),
				'LANG_NEWPOSTS' => $this->lang->line('newposts'),
				'LANG_HOME' => $this->lang->line('home'),
				'LANG_HELP' => $this->lang->line('help'),
				'LANG_MEMBERLIST' => $this->lang->line('members'),
				'LANG_PROFILE' => $this->lang->line('profile'),
				'LANG_POWERED' => $this->lang->line('poweredby'),
				'LANG_POSTEDBY' => $this->lang->line('Postedby'),
				'BREADCRUMB' =>$this->breadcrumb->output(),
				'LANG_EMAIL' => $this->lang->line('email'),
				'VALIDATION_EMAIL' => form_error('email'),
				'LANG_RULE' => $this->lang->line('nospecialchar'),
				'LANG_CONFIRMPWD' => $this->lang->line('confirmpass'),
				'VALIDATION_VPASSWORD' => form_error('password_confirm'),
				'LANG_TIME' => $this->lang->line('timezone'),
				'VALIDATION_TIMEZONE' => form_error('time_zone'),
				'LANG_TIMEFORMAT' => $this->lang->line('timeformat'),
				'TIMEFORMAT' => $this->timeFormat,
				'VALIDATION_TIMEFORMAT' => form_error('time_format'),
				'LANG_TIMEINFO' => $this->lang->line('timeinfo'),
				'LANG_PMNOTIFY' => $this->lang->line('pm_notify'),
				'LANG_SHOWEMAIL' => $this->lang->line('showemail'),
				'LANG_YES' => $this->lang->line('yes'),
				'LANG_NO' => $this->lang->line('no'),
				'LANG_STYLE' => $this->lang->line('style'),
				'STYLE' => ThemeList($this->style),
				'VALIDATION_STYLE' => form_error('style'),
				'LANG_LANGUAGE' => $this->lang->line('defaultlang'),
				'LANGUAGE' => LanguageList($this->lng),
				'VALIDATION_LANGUAGE' => form_error('language'),
				'LANG_CAPTCHA' => $this->lang->line('captcha'),
				'LANG_CAPTCHAHELP' => $this->lang->line('securitynotice'),
				'VALIDATION_CAPTCHA' => form_error('captcha'),
				'LANG_AGREE' => $this->lang->line('agree'),
				'PREF_RULES' => $this->preference->getPreferenceValue('rules_status'),
				'RULES' => $this->preference->getPreferenceValue('rules'),
				'PREF_COPPA' => $this->preference->getPreferenceValue("coppa"),
				'COPPA_13' => $this->lang->line('coppa13'),
				'COPPA_16' => $this->lang->line('coppa16'),
				'COPPA_18' => $this->lang->line('coppa18'),
				'COPPA_21' => $this->lang->line('coppa21'),
				'LANG_COPPA' => $this->lang->line('coppavalidate'),
				'PREF_CAPTCHA' => $this->preference->getPreferenceValue("captcha"),
				'CAPTCHA' => GenerateCaptchaQuestion()
				));
			}
		} else {
			//get values from form.
			$email = $this->input->post('email', TRUE);
			$username = $this->input->post('username', TRUE);
			$password = $this->input->post('password', TRUE);
			$time_zone = $this->input->post('time_zone', TRUE);
			$time_format = $this->input->post('time_format', TRUE);
			$pm_notice = $this->input->post('pm_notice', TRUE);
			$show_email = $this->input->post('show_email', TRUE);
			$ustyle = $this->input->post('style', TRUE);
			$default_lang = $this->input->post('language', TRUE);
		
			#clear session data.
			$this->session->unset_userdata('CAPTCHA_Ans');
			
			//see if activation is set to either User or Admin.
			if($this->preference->getPreferenceValue("activation") == "User"){
				$active_stat = 0;
				$act_key = md5(makeRandomPassword());
			}elseif($this->preference->getPreferenceValue("activation") == "Admin"){
				$active_stat = 0;
				$act_key = '';
			}else{
				$active_stat = 1;
				$act_key = '';
			}
			
			//see if user is assigned to a special group.
			$newuser_gid = $this->preference->getPreferenceValue("userstat");
			
			//create blowfish hash.
			$hash = makeHash($password);
			
			//load user model.
			$this->load->model('Usermodel');
			
			//populate model.
			$this->Usermodel->setUserName($username);
			$this->Usermodel->setPassword($hash);
			$this->Usermodel->setEmail($email);
			$this->Usermodel->setGid($newuser_gid); //this will be dynamic soon!
			$this->Usermodel->setCustomTitle(null);
			$this->Usermodel->setLastVisit(null);
			$this->Usermodel->setPmNotify($pm_notice);
			$this->Usermodel->setHideEmail($show_email);
			$this->Usermodel->setMSn(null);
			$this->Usermodel->setAol(null);
			$this->Usermodel->setYahoo(null);
			$this->Usermodel->setIcq(null);
			$this->Usermodel->setWww(null);
			$this->Usermodel->setLocation(null);
			$this->Usermodel->setAvatar(null);
			$this->Usermodel->setSig(null);
			$this->Usermodel->setTimeFormat($time_format);
			$this->Usermodel->setTimeZone($time_zone);
			$this->Usermodel->setDateJoined(time());
			$this->Usermodel->setIp(detectProxy());
			$this->Usermodel->setStyle($ustyle);
			$this->Usermodel->setLanguage($default_lang);
			$this->Usermodel->setPostCount(0);
			$this->Usermodel->setLastPost(null);
			$this->Usermodel->setLastSearch(null);
			$this->Usermodel->setFailedAttempts(0);
			$this->Usermodel->setActive($active_stat);
			$this->Usermodel->setActKey($act_key);
			$this->Usermodel->setPasswordRecoveryDate(null);
			$this->Usermodel->setWarningLevel(0);
			$this->Usermodel->setSuspendLength(0);
			$this->Usermodel->setSuspendTime(null);
			
			#insert user to DB.
			$this->Usermodel->CreateUser();
			
			#email user.
			$config = array();
			if ($this->preference->getPreferenceValue("mail_type") == 2) {
				$config['protocol'] = 'sendmail';
				$config['mailpath'] = $this->preference->getPreferenceValue("sendmail_path");
				$this->email->initialize($config);
			} elseif ($this->preference->getPreferenceValue("mail_type") == 0) {
				$config['protocol'] = 'smtp';
				$config['smtp_host'] = $this->preference->getPreferenceValue("smtp_host");
				$config['smtp_user'] = $this->preference->getPreferenceValue("smtp_user");
				$config['smtp_pass'] = $this->preference->getPreferenceValue("smtp_pwd");
				$config['smtp_port'] = $this->preference->getPreferenceValue("smtp_port");
				$config['smtp_timeout'] = $this->preference->getPreferenceValue("smtp_timeout");
				$this->email->initialize($config);
			}
			
			if ($this->preference->getPreferenceValue("activation") == "None") {
				//send out email.        	
				$this->email->to($email);
				$this->email->from($this->preference->getPreferenceValue("board_email"), $this->title);
				$this->email->subject($this->lang->line('nonesubject').' '.$this->title);
				$this->email->message($this->twig->renderNoStyle('/emails/'.$this->lng.'/eml_none_confirm.twig', array(
				  'USERNAME' => $username,
				  'TITLE' => $this->title,
				  'BOARDADDR' => $this->boardUrl
				)));
				
				//send out email.
				$this->email->send();
				
				#let user know their account is created.
				$this->session->set_flashdata('NotifyType', 'success');
				$this->session->set_flashdata('NotifyMsg', $this->lang->line('acctmade'));
				
				#auto log user in.
				$params = array(
					'usr' => $username,
					'pwd' => $password
					);
				$this->load->library('auth', $params);
				
				#setup cookie or session(based on user's preference.
				$this->auth->logOn(FALSE);

				#direct user.
				redirect('/', 'location');
			} elseif ($this->preference->getPreferenceValue("activation") == "User") {
				//send out email.        	
				$this->email->to($email);
				$this->email->from($this->preference->getPreferenceValue("board_email"), $this->title);
				$this->email->subject($this->lang->line('usersubject'));
				$this->email->message($this->twig->renderNoStyle('/emails/'.$this->lng.'/eml_user_confirm.twig', array(
				  'USERNAME' => $username,
				  'TITLE' => $this->title,
				  'BOARDADDR' => $this->boardUrl,
				  'KEY' => $act_key
				)));
				
				//send out email.
				$this->email->send();
				
				#let user know their account is created.
				$this->session->set_flashdata('NotifyType', 'warning');
				$this->session->set_flashdata('NotifyMsg', $this->lang->line('acctuser'));

				#direct user.
				redirect('/login/LogIn', 'location');
			} elseif ($this->preference->getPreferenceValue("activation") == "Admin") {
				//send out email.        	
				$this->email->to($email);
				$this->email->from($this->preference->getPreferenceValue("board_email"), $this->title);
				$this->email->subject($this->lang->line('adminsubject'));
				$this->email->message($this->twig->renderNoStyle('/emails/'.$this->lng.'/eml_admin_confirm.twig', array(
				  'USERNAME' => $username,
				  'TITLE' => $this->title
				)));
				
				//send out email.
				$this->email->send();
				
				#let user know their account is created.
				$this->session->set_flashdata('NotifyType', 'warning');
				$this->session->set_flashdata('NotifyMsg', $this->lang->line('acctadmin'));

				#direct user.
				redirect('/login/LogIn', 'location');
			}
			
		}
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
		if(checkdnsrr(array_pop(explode("@",$str)),"MX")) {
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
	
}
?>