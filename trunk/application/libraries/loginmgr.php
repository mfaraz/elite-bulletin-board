<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * loginmgr.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 10/27/2011
*/

class loginmgr{

    #declare data members
    
    /**
	 * Username In Session
	 * @var string
	*/
    private $user;
    
    /**
	 * Password In Session
	 * @var string
	*/
    private $pass;
    
    /**
	 * Codeigniter Object.
	 * @var Object
	*/
    private $ci;

    /**
	 * Setup common value.
	 * @version 10/5/11
	 * @param string $usr - username under request.
	 * @param string $pwd - password under request.
	 * @access public
	*/
	public function __construct($params){
	
		//get CodeIgniter objects.
		$this->ci =& get_instance();	

		#define data member values.
		$this->user = $params['usr'];
		$this->pass = $params['pwd'];
	}

    /**
	 * Clean-up class after we're done.
	 * @version 8/10/09
	 * @access public
	 */
	public function __destruct(){

		unset($this->user);
		unset($this->pass);
	}

    /**
	 * Performs a check through the database to ensure the requested username is valid.
	 * @version 10/4/11
	 * @return bool
	 * @access private
	*/
	private function validateUser(){

	    #check against the database to see if the username and password match.
	    $validateUser = $this->ci->db->select('id')->from('ebb_users')->where('Username', $this->user)->limit(1)->count_all_results();
	    
		#setup boolean return.
		if($validateUser == 0){
		    return(false);
		}else{
		    return(true);
		}
	}
	
    /**
	 * Performs a check through the database to ensure the requested password is valid.
	 * @version 10/27/11
	 * @return bool
	 * @access private
	*/
	private function validatePwd(){

        #encrypt password.
	    $encryptPwd = sha1($this->pass.$this->getPwdSalt());

	    #check against the database to see if the username and password match.
		$this->ci->db->select('id')->from('ebb_users')->where('Password', $encryptPwd)->limit(1);
		$validatePwd = $this->ci->db->count_all_results();

		#setup boolean return.
		if($validatePwd == 0){
		    return(false);
		}else{
		    return(true);
		}
	}

	/**
	 * Validates current login password.
	 * @access Private
	 * @version 10/27/11
	*/
	private function validatePwdEncrypted() {

	    #check against the database to see if the username and password match.
		$this->ci->db->select('id')->from('ebb_users')->where('Password', $this->pass)->limit(1);
		$validatePwd = $this->ci->db->count_all_results();

		#setup boolean return.
		if($validatePwd == 0){
		    return(false);
		}else{
		    return(true);
		}
	}
	
    /**
	 * Get password salt for requested user.
	 * @version 10/27/11
	 * @return string $pwdSlt
	 * @access private
    */
	private function getPwdSalt(){

	    #check against the database to see if the username and password match.
		$this->ci->db->select('salt')->from('ebb_users')->where('Username', $this->user)->limit(1);
		$query = $this->ci->db->get();
		$pwdSlt = $query->row();
		return($pwdSlt->salt);
	}

    /**
	 * Performs a check through the database to ensure the requested information is valid.
	 * @version 7/24/11
	 * @return bool
	 * @access public
	*/
	public function validateLogin(){

		#See if this is a guest account.
		if(($this->user == "guest") OR ($this->pass == "guest")){
		    return(false);
		}else{
			#see if user entered the correct information.
			if(($this->validateUser()) AND ($this->validatePwd())){
			    return(true);
			}else{
			    return(false);
			}
		}
	}
	
	/**
	 * Validates current login session.
	 * @access Public
	 * @version 7/24/2011
	*/
	public function validateLoginSession(){

		#See if this is a guest account.
		if(($this->user == "guest") OR ($this->pass == "guest")){
		    return(false);
		}else{
			#see if user entered the correct information.
			if(($this->validateUser()) AND ($this->validatePwdEncrypted())){
			    return(true);
			}else{
			    return(false);
			}
		}
	}

    /**
	 * Performs a check through the database to ensure the user can access the adminstration panel.
	 * @version 7/24/11
	 * @return bool
	 * @access public
	*/
	public function validateAdministrator(){
	
		#See if this is a guest account.
		if(($this->user == "guest") OR ($this->pass == "guest")){
		    return(false);
		}else{
			#see if user entered the correct information.
			if(($this->validateUser()) AND ($this->validatePwd())){
				#see if user is an administrator.
                $validateGroupPolicy = new groupPolicy($this->user);
				if($validateGroupPolicy->groupAccessLevel() == 1){
			    	return(true);
				}else{
			    	return(false);
			    }//END group validation.
			}else{
				return(false);
			}//END user validation.
		}//END guest filtering.
	}

	/**
	 * Validates current adminCP session.
	 * @access Public
	 * @version 7/25/2011
	*/
	public function validateAdministratorSession() {

		#See if this is a guest account.
		if(($this->user == "guest") OR ($this->pass == "guest")){
		    return(false);
		}else{
			#see if user entered the correct information.
			if(($this->validateUser()) AND ($this->validatePwdEncrypted())){
				#see if user is an administrator.
                $validateGroupPolicy = new groupPolicy($this->user);
				if($validateGroupPolicy->groupAccessLevel() == 1){
			    	return(true);
				}else{
			    	return(false);
			    }//END group validation.
			}else{
				return(false);
			}//END user validation.
		}//END guest filtering.

	}
	
    /**
	 * Performs login process, creating any sessions or cookies needed for the ACP.
	 * @param int $sessionLength The duration of the session.
	 * @version 10/27/11
	 * @access public
	*/
	public function acpLogOn($sessionLength){

		#set session to a secure status.
		//ini_get('session.cookie_secure',true);

		#encrypt password.
	    $encryptPwd = sha1($this->pass.$this->getPwdSalt());
	    
	    #create session marker for time limit.
	    //@TODO: Add this value into a session table in the database, plan for RC 2.
        $this->ci->session->set_userdata('ebbacptimer', $sessionLength);
	
		#user is an admin, let them log in. set to last as long as user selected.
		//@todo: make this session value, NOT a cookie.
        $ebbacpu = array(
            'name'   => 'ebbacpu',
            'value'  => $this->user,
            'expire' => $expire,
            'domain' => '.'.$this->ci->preference->getPreferenceValue("cookie_domain"),
            'path'   => $this->ci->preference->getPreferenceValue("cookie_path"),
            'secure' => $this->ci->preference->getPreferenceValue("cookie_secure")
        );
        $this->input->set_cookie($ebbacpu);

        $ebbacpp = array(
            'name'   => 'ebbacpp',
            'value'  => $encryptPwd,
            'expire' => $expire,
            'domain' => '.'.$this->ci->preference->getPreferenceValue("cookie_domain"),
            'path'   => $this->ci->preference->getPreferenceValue("cookie_path"),
            'secure' => $this->ci->preference->getPreferenceValue("cookie_secure")
        );
        $this->input->set_cookie($ebbacpp);

		#generate session-based validation.
		$this->regenerateSession(true);
	}

    /**
	 * Performs logout process, removing any sessions or cookies needed for the ACP.
	 * @version 10/27/11
	 * @access public
	*/
	public function acpLogOut(){

   		#set session to a secure status.
		//ini_get('session.cookie_secure',true);

		#close out ACP cookies.
		if (($this->input->cookie('ebbacpu', TRUE) == null) AND ($this->input->cookie('ebbacpp', TRUE) == null)){
	        #encrypt password.
		    $encryptPwd = sha1($this->pass.$this->getPwdSalt());

		    #get session time.            
	        $sessionLength = $this->ci->session->userdata('ebbacptimer');

			#destroy cookies.
            $ebbacpu = array(
                'name'   => 'ebbacpu',
                'value'  => $this->user,
                'expire' => '',
                'domain' => '.'.$this->ci->preference->getPreferenceValue("cookie_domain"),
                'path'   => $this->ci->preference->getPreferenceValue("cookie_path"),
                'secure' => $this->ci->preference->getPreferenceValue("cookie_secure")
            );
            $this->input->set_cookie($ebbacpu);

            $ebbacpp = array(
                'name'   => 'ebbacpp',
                'value'  => $encryptPwd,
                'expire' => '',
                'domain' => '.'.$this->ci->preference->getPreferenceValue("cookie_domain"),
                'path'   => $this->ci->preference->getPreferenceValue("cookie_path"),
                'secure' => $this->ci->preference->getPreferenceValue("cookie_secure")
            );
            $this->input->set_cookie($ebbacpp);

			#clear session data.
			$this->ci->session->all_userdata();
		}
	}

    /**
	 * Performs login process, creating any sessions or cookies needed for the system.
	 * @version 10/27/11
	 * @access public
	*/
	public function logOn($remember){

        #encrypt password.
	    $encryptPwd = sha1($this->pass.$this->getPwdSalt());
	    
		#set session to a secure status.
		//ini_get('session.cookie_secure',true);
		
		#see if user wants to remain logged on.
		if($remember == ""){
			#create a session.
			$this->ci->session->set_userdata('ebbuser', $this->user);
			$this->ci->session->set_userdata('ebbpass', $encryptPwd);
			
			
			//$_SESSION['ebbuser'] = $this->user;
			//$_SESSION['ebbpass'] = $encryptPwd;
		}else{
			#create cookie.
            $ebbuser = array(
                'name'   => 'ebbuser',
                'value'  => $this->user,
                'expire' => '2592000',
                'domain' => '.'.$this->ci->preference->getPreferenceValue("cookie_domain"),
                'path'   => $this->ci->preference->getPreferenceValue("cookie_path"),
                'secure' => $this->ci->preference->getPreferenceValue("cookie_secure")
            );
            $this->ci->input->set_cookie($ebbuser);
            
            $ebbpass = array(
                'name'   => 'ebbpass',
                'value'  => $encryptPwd,
                'expire' => '2592000',
                'domain' => '.'.$this->ci->preference->getPreferenceValue("cookie_domain"),
                'path'   => $this->ci->preference->getPreferenceValue("cookie_path"),
                'secure' => $this->ci->preference->getPreferenceValue("cookie_secure")
            );
            $this->ci->input->set_cookie($ebbpass);			
		}
		
		#generate session-based validation.
		$this->regenerateSession(true);
		
		#remove user's IP from who's online list.
        $this->ci->db->delete('ebb_online', array('ip' => detectProxy()));
	}
	
    /**
	 * Performs logout process, removing any sessions or cookies created from the system.
	 * @version 10/27/11
	 * @access public
	*/
	public function logOut(){

		#set session to a secure status.
		//ini_get('session.cookie_secure',true);

		#see if user is using cookies or sessions.
		if($this->input->cookie('ebbuser', TRUE) == null){	
            
            #delete cookies.
            $ebbuser = array(
                'name'   => 'ebbuser',
                'value'  => $this->user,
                'expire' => '',
                'domain' => '.'.$this->ci->preference->getPreferenceValue("cookie_domain"),
                'path'   => $this->ci->preference->getPreferenceValue("cookie_path"),
                'secure' => $this->ci->preference->getPreferenceValue("cookie_secure")
            );
            $this->ci->input->set_cookie($ebbuser);
            
            $ebbpass = array(
                'name'   => 'ebbpass',
                'value'  => $encryptPwd,
                'expire' => '',
                'domain' => '.'.$this->ci->preference->getPreferenceValue("cookie_domain"),
                'path'   => $this->ci->preference->getPreferenceValue("cookie_path"),
                'secure' => $this->ci->preference->getPreferenceValue("cookie_secure")
            );
            $this->input->set_cookie($ebbpass);			

			#remove user from who's online list.
            $this->ci->db->delete('ebb_online', array('Username' => $this->user));
            
			#close out ACP cookie if needed
			if (($this->input->cookie('ebbacpu', TRUE) == null) AND ($this->input->cookie('ebbacpp', TRUE) == null)) {
				$ebbacpu = array(
                'name'   => 'ebbacpu',
                'value'  => $this->user,
                'expire' => '',
                'domain' => '.'.$this->ci->preference->getPreferenceValue("cookie_domain"),
                'path'   => $this->ci->preference->getPreferenceValue("cookie_path"),
                'secure' => $this->ci->preference->getPreferenceValue("cookie_secure")
                );
                $this->input->set_cookie($ebbacpu);

                $ebbacpp = array(
                    'name'   => 'ebbacpp',
                    'value'  => $encryptPwd,
                    'expire' => '',
                    'domain' => '.'.$this->ci->preference->getPreferenceValue("cookie_domain"),
                    'path'   => $this->ci->preference->getPreferenceValue("cookie_path"),
                    'secure' => $this->ci->preference->getPreferenceValue("cookie_secure")
                );
                $this->input->set_cookie($ebbacpp);
			}
			
			#clear session data.
			session_destroy();
			$this->ci->session->all_userdata();
            
		}else{
			#remove user from who's online list.
			$this->ci->db->delete('ebb_online', array('Username' => $this->user));

			#close out ACP cookie if needed
			if (($this->input->cookie('ebbacpu', TRUE) == null) AND ($this->input->cookie('ebbacpp', TRUE) == null)){
				$ebbacpu = array(
                'name'   => 'ebbacpu',
                'value'  => $this->user,
                'expire' => '',
                'domain' => '.'.$this->ci->preference->getPreferenceValue("cookie_domain"),
                'path'   => $this->ci->preference->getPreferenceValue("cookie_path"),
                'secure' => $this->ci->preference->getPreferenceValue("cookie_secure")
                );
                $this->input->set_cookie($ebbacpu);

                $ebbacpp = array(
                    'name'   => 'ebbacpp',
                    'value'  => $encryptPwd,
                    'expire' => '',
                    'domain' => '.'.$this->ci->preference->getPreferenceValue("cookie_domain"),
                    'path'   => $this->ci->preference->getPreferenceValue("cookie_path"),
                    'secure' => $this->ci->preference->getPreferenceValue("cookie_secure")
                );
                $this->input->set_cookie($ebbacpp);
			}

			#clear session data.
			session_destroy();
			$this->ci->session->all_userdata();
		}
	}

    /**
	 * Performs a check to ensure the session value is valid and not hijacked.
	 * @param $destroy bool - true will detroy old session data; false will not.
	 * @version 12/28/10
	 * @access public
	*/
    public function validateSession($destroy = false){

		try{
            #validate User Agent and make sure it didn't just 'magically' change.
	        if($_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']){
	        	$error = new notifySys("USER AGENT VALIDATE ERROR, SESSION HIJACKING DETECTED!", false);
				$error->genericError();
			}else{
				#regenerate Session ID.
				#NOTE: We should only be clearing the old session IDs when performing important tasks
				#such as loging in or anything within the ACP.
	            $this->regenerateSession($destroy);
            }
    	}catch(Exception $e){
	        $error = new notifySys($e, true, true, __FILE__, __LINE__);
			$error->genericError();
    	}
	}

    /**
	 * creates a new session id and destroys the old session id(if any exists).
	 * @param $destroy bool - true will detroy old session data; false will not.
	 * @version 12/28/10
	 * @access public
	*/
    public function regenerateSession($destroy = false){

	    if(!isset($_SESSION['userAgent'])){
	        $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
		}

	    #Create new session & destroy the old one.
		if($destroy == true){
	    	session_regenerate_id(true);
	    }else{
	    	session_regenerate_id();
	    }
	}

    /**
	 * Checks to see if the user is verified as active or still waiting for activation.
	 * @version 10/27/11
	 * @access public
	*/
	public function isActive(){

	    #check against the database to see if the username and password match.
		$this->ci->db->select('active')->from('ebb_users')->where('Username', $this->user)->limit(1);
		$query = $this->ci->db->get();
		$validateStatus = $query->row();
        
		#setup bool. value to see if user is active or not.
		if($validateStatus->active == 0){
		    return(false);
		}else{
		    return(true);
		}
	}

    /**
	 * disable user's active status.
	 * @version 10/5/11
	 * @access public
	*/
	public function deactivateUser(){
		
        $this->ci->db->where('Username', $this->user);
        $this->ci->db->update('ebb_users', array('active' => 0));
	}

	/**
	 * activates the user to allow them access the system.
	 * @version 10/5/11
	 * @access public
	*/
	public function activateUser(){

		$this->ci->db->where('Username', $this->user);
        $this->ci->db->update('ebb_users', array('active' => 1));

	}

    /**
	 * See how many times the user failed to login correctly.
	 * @version 10/27/11
	 * @access public
	*/
	public function getFailedLoginCt(){

	    #get the count from the user table.
		$this->ci->db->select('failed_attempts')->from('ebb_users')->where('Username', $this->user)->limit(1);
		$query = $this->ci->db->get();
		$getFailedLoginCt = $query->row();

		return($getFailedLoginCt->failed_attempts);
	}

    /**
	 * increment fail count for defined user.
	 * @version 10/5/11
	 * @access public
	*/
	public function setFailedLogin(){

        #get new count.
		$newCount = $this->getFailedLoginCt();
		$incrementFailedCt = $newCount + 1;
        
	    $this->ci->db->where('Username', $this->user);
        $this->ci->db->update('ebb_users', array('failed_attempts' => $incrementFailedCt));
        
	}

    /**
	 *Clears the failed count to 0.
	 *@modified 10/5/11
	 *@access public
	*/
	public function clearFailedLogin(){

	    #clear count.
        $this->ci->db->where('Username', $this->user);
        $this->ci->db->update('ebb_users', array('failed_attempts' => 0));
        
	}

    /**
	 *Checks to see if the user is banned or suspended.
	 * @version 10/27/11
	 *  @access public
	*/
	public function checkBan(){

		#see if user is marked as banned.
		if($this->ci->groupProfile == 6){
			exit(show_error($this->ci->lang->line('banned')));
		}

		#see if user is suspended.
		if($this->ci->suspend_length > 0){
			#see if user is still suspended.
			$math = 3600 * $this->ci->suspend_length;
			$suspend_date = $this->ci->suspend_time + $math;
			$today = time() - $math;
			
			if($suspend_date > $today){
				exit(show_error($this->lang->line('suspended')));
			}
		}
		#see if the IP of the user is banned.
        $uip = detectProxy();

		$this->ci->db->distinct('ban_item')->from('ebb_banlist')->where('ban_type', 'IP')->like('ban_item', $uip)->limit(1);
		$banChk = $this->ci->db->count_all_results();
		
		#output an error msg.
		if($banChk == 1){
			exit(show_error($this->lang->line('banned')));
		}
	}
}//END CLASS
?>
