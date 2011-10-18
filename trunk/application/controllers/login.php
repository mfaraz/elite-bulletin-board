<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
	* login.php
	* @package Elite Bulletin Board v3
	* @author Elite Bulletin Board Team <http://elite-board.us>
	* @copyright  (c) 2006-2011
	* @license http://opensource.org/licenses/gpl-license.php GNU Public License
	* @version 10/7/2011
*/


class Login extends EBB_Controller {

    public function __construct() {
 		parent::__construct();
		$this->load->helper(array('common', 'posting'));
	}


	/**
		 * Index Page for this controller.
		 *
		 * Maps to the following URL
		 * 		http://example.com/index.php/login
		 *	- or -
		 * 		http://example.com/index.php/login/index
		 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index() {

	}
	
	/**
	 * Processes login form and logs user in.
	 */
	public function LogIn() {

    	// LOAD LIBRARIES
        $this->load->library(array('encrypt', 'form_validation'));
        $this->load->helper('form');

        $this->form_validation->set_rules('username', $this->lang->line('blank'), 'required');
        $this->form_validation->set_rules('password', $this->lang->line('blank'), 'required');
        $this->form_validation->set_error_delimiters('<div class="ui-state-error">', '</div>');

		//see if any validation rules failed.
		if ($this->form_validation->run() == FALSE) {
		//re-show form with error(s).
		echo $this->twig->render('/page/form.htm', array (
			'VALIDATION' => form_error('test'),
			'FORMTAG' => form_open('page/forumSubmit'),
			'pageTitle'=> 'Form posting Test',
			'curVersion'=> '2.1.12',
			'curYear' => date("Y")
			));
		} else {
		
			$params = array('usr' => $this->input->post('username', TRUE), 'pwd' => $this->input->post('password', TRUE));
			$this->load->library('loginmgr', $params, 'auth');

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
					if($this->preference->getPreferenceValue("board_status")->pref_value == 0){
						#see if user has proper rights to access under this limited operational status.
      					$params[0] = $this->input->post('username', TRUE);
						$this->load->library('grouppolicy', $params, 'alc');

						if($this->alc->groupAccessLevel() == 1){
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
       						redirect('/login/', 'location');
						}
					}else{
						#clear any failed login attempts from their record.
						$this->auth->clearFailedLogin();

						#setup cookie or session(based on user's preference.
						$this->auth->logOn($remember);
						
						//show success message.
						$this->session->set_flashdata('NotifyType', 'success');
						$this->session->set_flashdata('NotifyMsg', "Logged In As: ". $this->session->userdata('ebbuser')); //$this->input->post('username', TRUE)

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
                    $this->session->set_flashdata('NotifyMsg', $this->lang->line('nomatch'));

					#direct user.
     				redirect('/login/', 'location');
				}
			} //END login validation.
			
		} //END form validation.
	}
	
	/**
		* Validates inactive account.
	*/
	public function validateAccount($key, $u) {
	
	}
	
	/**
		 * Password recovery View.
		 *
		 * Maps to the following URL
		 * 		http://example.com/index.php/login/PasswordRecovery
		 *	- or -
		 * 		http://example.com/index.php/login/index
		 * @see http://codeigniter.com/user_guide/general/urls.html
	*/
	public function PasswordRecovery() {


	}
	
	/**
		* Validate & process lost password request.
	*/
	public function PasswordRecoverySubmit() {


	}

    /**
		* Logs user out and clears all active login sessions.
	*/
	public function LogOut() {


	}
}
?>