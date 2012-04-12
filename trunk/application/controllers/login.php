<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 *  login.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 02/29/2012
*/


class Login extends EBB_Controller {

    public function __construct() {
 		parent::__construct();
		$this->load->helper(array('common', 'posting'));
	}
	
	/**
	 * Output Login Form.
	 * @version 02/20/12
	 */
	private function LoginForm() {
		
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
		  'NOTIFY_TYPE' => $this->session->flashdata('NotifyType'),
		  'NOTIFY_MSG' =>  $this->session->flashdata('NotifyMsg'),
		  'LANG' => $this->lng,
		  'TimeFormat' => $this->timeFormat,
		  'TimeZone' => $this->timeZone,
		  'LANG_WELCOME'=> $this->lang->line('welcome'),
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
		  'LANG_MEMBERLIST' => $this->lang->line('profile'),
		  'LANG_PROFILE' => $this->lang->line('logout'),
		  'LANG_POWERED' => $this->lang->line('poweredby'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby'),
		  'BREADCRUMB' =>$this->breadcrumb->output()
		));
	}
	
	/**
	 * Outputs Password Recovery Form
	 * @version 02/20/12
	 */
	private function PwdRecoverForm() {
		
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
		  'TimeFormat' => $this->timeFormat,
		  'TimeZone' => $this->timeZone,
		  'LANG_WELCOME'=> $this->lang->line('welcome'),
		  'LANG_WELCOMEGUEST' => $this->lang->line('welcomeguest'),
		  'LOGGEDUSER' => $this->logged_user,
		  'LANG_JSDISABLED' => $this->lang->line('jsdisabled'),
		  'LANG_INFO' => $this->lang->line('info'),
		  'LANG_LOGIN' => $this->lang->line('login'),
		  'LANG_LOGOUT' => $this->lang->line('logout'),
		  'LOGINFORM' => form_open('login/LogIn', array('name' => 'frmQLogin')),
		  'PWDRECOVERFORM' => form_open('login/PasswordRecoverySubmit'),
		  'VALIDATION_USERNAME' => form_error('lost_user'),
		  'VALIDATION_EMAIL' => form_error('lost_email'),
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
		  'LANG_MEMBERLIST' => $this->lang->line('profile'),
		  'LANG_PROFILE' => $this->lang->line('logout'),
		  'LANG_POWERED' => $this->lang->line('poweredby'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby'),
		  'BREADCRUMB' =>$this->breadcrumb->output(),
		  'LANG_EMAIL' => $this->lang->line('email'),
		  'LANG_PWDRECOVER' => $this->lang->line('passwordrecovery'),
		  'LANG_GETPASS' => $this->lang->line('getpassword')
		));
	}
	
	/**
	 * Registration Form.
	 * @version 02/20/12
	 */
	private function registerForm() {
		
		//load breadcrumb library
		$this->load->library('breadcrumb');
		
		// add breadcrumbs
		$this->breadcrumb->append_crumb($this->title, '/boards/');
		$this->breadcrumb->append_crumb($this->lang->line('register'), '/login/register');
		
		//render to HTML.
		echo $this->twig->render($this->style, 'register', array (
		  'boardName' => $this->title,
		  'BOARD_URL' => $this->boardUrl,
		  'APP_URL' => $this->boardUrl.APPPATH,
		  'NOTIFY_TYPE' => $this->session->flashdata('NotifyType'),
		  'NOTIFY_MSG' =>  $this->session->flashdata('NotifyMsg'),
		  'LANG' => $this->lng,
		  'TimeFormat' => $this->timeFormat,
		  'TimeZone' => $this->timeZone,
		  'LANG_WELCOME'=> $this->lang->line('welcome'),
		  'LANG_WELCOMEGUEST' => $this->lang->line('welcomeguest'),
		  'LOGGEDUSER' => $this->logged_user,
		  'LANG_JSDISABLED' => $this->lang->line('jsdisabled'),
		  'LANG_INFO' => $this->lang->line('info'),
		  'LANG_LOGIN' => $this->lang->line('login'),
		  'LANG_LOGOUT' => $this->lang->line('logout'),
		  'LOGINFORM' => form_open('login/LogIn', array('name' => 'frmQLogin')),
		  'REGISTERFORM' => form_open('login/CreateUser', array('name' => 'frmRegister')),
		  'VALIDATION_USERNAME' => form_error('lost_user'),
		  'VALIDATION_EMAIL' => form_error('lost_email'),
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
		  'LANG_MEMBERLIST' => $this->lang->line('profile'),
		  'LANG_PROFILE' => $this->lang->line('logout'),
		  'LANG_POWERED' => $this->lang->line('poweredby'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby'),
		  'BREADCRUMB' =>$this->breadcrumb->output(),
		  'LANG_EMAIL' => $this->lang->line('email'),
		  'LANG_RULE' => $this->lang->line('nospecialchar'),
		  'LANG_CONFIRMPWD' => $this->lang->line('confirmpass'),
		  'LANG_TIME' => $this->lang->line('timezone'),
		  'LANG_TIMEFORMAT' => $this->lang->line('timeformat'),
		  'TIMEFORMAT' => $this->timeFormat,
		  'LANG_TIMEINFO' => $this->lang->line('timeinfo'),
		  'LANG_PMNOTIFY' => $this->lang->line('pm_notify'),
		  'LANG_SHOWEMAIL' => $this->lang->line('showemail'),
		  'LANG_YES' => $this->lang->line('yes'),
		  'LANG_NO' => $this->lang->line('no'),
		  'LANG_STYLE' => $this->lang->line('style'),
		  'STYLE' => ThemeList($this->style),
		  'LANG_LANGUAGE' => $this->lang->line('defaultlang'),
		  'LANGUAGE' => LanguageList($this->lng),
		  'LANG_CAPTCHA' => $this->lang->line('captcha'),
		  'LANG_CAPTCHAHELP' => $this->lang->line('securitynotice'),
		  'LANG_RELOAD' => $this->lang->line('reloadimg'),
		  'LANG_AGREE' => $this->lang->line('agree'),
		  'PREF_RULES' => $this->preference->getPreferenceValue('rules_status'),
		  'RULES' => $this->preference->getPreferenceValue('rules'),
		  'COPPA' => $this->preference->getPreferenceValue("coppa"),
		  'COPPA_13' => $this->lang->line('coppa13'),
		  'COPPA_16' => $this->lang->line('coppa16'),
		  'COPPA_18' => $this->lang->line('coppa18'),
		  'COPPA_21' => $this->lang->line('coppa21'),
		  'CAPTCHA' => GenerateCaptchaQuestion()
		));
	}


	/**
	  * Index Page for this controller.
	  * @version 10/11/11
	  * Maps to the following URL
	  * http://example.com/index.php/login
	  *	- or -
	  * http://example.com/index.php/login/index
	  * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index() {

		// LOAD LIBRARIES
        $this->load->helper('form');		
		
		//show login form.
		$this->LoginForm();
		
	}
	
	/**
	  * Processes login form and logs user in.
	 *  @version 02/29/12
	 */
	public function LogIn() {

    	// LOAD LIBRARIES
        $this->load->library(array('encrypt', 'form_validation'));
        $this->load->helper(array('form', 'user'));

        $this->form_validation->set_rules('username', $this->lang->line('nouser'), 'required');
        $this->form_validation->set_rules('password', $this->lang->line('nopass'), 'required');
        $this->form_validation->set_error_delimiters('<div class="ui-state-error">', '</div>');

		//see if any validation rules failed.
		if ($this->form_validation->run() == FALSE) {
			//show login form.
			$this->LoginForm();
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

					redirect('/login/', 'location');
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
						$this->session->set_flashdata('NotifyMsg', "Logged In As: ". $this->session->userdata('ebbUser')); //$this->input->post('username', TRUE)

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
     				redirect('/login/', 'location');
				}else{
				    #add to failed login count.
					$this->auth->setFailedLogin();

					#setup error session.
					$this->session->set_flashdata('NotifyType', 'error');
                    $this->session->set_flashdata('NotifyMsg', $this->lang->line('invalidlogin'));

					#direct user.
     				redirect('/login/', 'location');
				}
			} //END login validation.
			
		} //END form validation.
	}
	
	/**
	 * Validates inactive account.
	 * @version 10/11/11
	*/
	public function validateAccount($key, $u) {
	
	}
	
	/**
	 * Password recovery View.
	 * @version 10/12/11
	 * Maps to the following URL
	 * http://example.com/index.php/login/PasswordRecovery
	 * @see http://codeigniter.com/user_guide/general/urls.html
	*/
	public function PasswordRecovery() {

		// LOAD LIBRARIES
        $this->load->helper('form');
		$this->PwdRecoverForm();

	}
	
	/**
	 * Validate & process lost password request.
	 * @version 10/11/11
	*/
	public function PasswordRecoverySubmit() {


	}

    /**
	 * Logs user out and clears all active login sessions.
	 * @version 10/11/11
	*/
	public function LogOut() {


	}
	
	/**
	 * Create New User View.
	 * @version 10/11/11
	 * Maps to the following URL
	 * http://example.com/index.php/login/register	 
	 * @see http://codeigniter.com/user_guide/general/urls.html
	*/
	public function register() {
		// LOAD LIBRARIES
        $this->load->helper('form');
		
		$this->registerForm();
	}
	
	/**
	 * Create New User form submit action.
	 * @version 10/11/11
	*/
	public function CreateUser() {

		//create blowfish hash.
		//$hash = makeHash('testing123');


		//clears session
		//$this->ci->session->all_userdata();

	}
}
?>