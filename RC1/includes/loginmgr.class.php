<?php
if (!defined('IN_EBB')) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: loginmgr.class.php
Last Modified: 7/29/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

class login{

    #declare data members
    private $user;
    private $pass;

    /**
	*__construct
	*
	*Setup common value.
	*
	*@modified 8/10/09
	*
	*@param string $usr - username under request.
	*@param string $pwd - password under request.
	*
	*@access public
	*/
	public function __construct($usr, $pwd){

		#define data member values.
		$this->user = $usr;
		$this->pass = $pwd;
	}

    /**
	*__destruct
	*
	*Clean-up class after we're done.
	*
	*@modified 8/10/09
	*
	*
	*@access public
	*/
	public function __destruct(){

		unset($this->user);
		unset($this->pass);
	}

    /**
	*validateUser
	*
	*Performs a check through the database to ensure the requested username is valid.
	*
	*@modified 10/19/09
	*
	*@return bool
	*
	*@access private
	*/
	private function validateUser(){

	    global $db;

	    #check against the database to see if the username and password match.
        $db->SQL = "SELECT id FROM ebb_users WHERE Username='".$this->user."' LIMIT 1";
		$validateUser = $db->affectedRows();

		#setup boolean return.
		if($validateUser == 0){
		    return(false);
		}else{
		    return(true);
		}
	}
	
    /**
	*validatePwd
	*
	*Performs a check through the database to ensure the requested password is valid.
	*
	*@modified 10/19/09
	*
	*@return bool
	*
	*@access private
	*/
	private function validatePwd(){

	    global $db;

        #encrypt password.
	    $encryptPwd = sha1($this->pass.$this->getPwdSalt());

	    #check against the database to see if the username and password match.
        $db->SQL = "SELECT id FROM ebb_users WHERE Password='".$encryptPwd."' LIMIT 1";
		$validatePwd = $db->affectedRows();

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
			 * @version 7/24/2011
			*/
	private function validatePwdEncrypted() {

	    global $db;

	    #check against the database to see if the username and password match.
        $db->SQL = "SELECT id FROM ebb_users WHERE Password='".$this->pass."' LIMIT 1";
		$validatePwd = $db->affectedRows();

		#setup boolean return.
		if($validatePwd == 0){
		    return(false);
		}else{
		    return(true);
		}
	}
	
    /**
	*getPwdSalt
	*
	*Get password salt for requested user.
	*
	*@modified 10/27/09
	*
	*@return string $pwdSlt
	*
	*@access private
	*/
	private function getPwdSalt(){

	    global $db;

	    #check against the database to see if the username and password match.
        $db->SQL = "SELECT salt FROM ebb_users WHERE Username='".$this->user."' LIMIT 1";
		$pwdSlt = $db->fetchResults();
		
		return($pwdSlt['salt']);
	}

    /**
	*validateLogin
	*
	*Performs a check through the database to ensure the requested information is valid.
	*
	*@modified 7/24/11
	*
	*@return bool
	*
	*@access public
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
	*validateAdministrator
	*
	*Performs a check through the database to ensure the user can access the adminstration panel.
	*
	*@modified 7/24/11
	*
	*@return bool
	*
	*@access public
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
	*acpLogOn
	*
	*Performs login process, creating any sessions or cookies needed for the ACP.
	*
	*@modified 12/28/10
	*
	*@access public
	*/
	public function acpLogOn(){

    	global $boardPref, $sessionLength;

		#set session to a secure status.
		//ini_get('session.cookie_secure',true);

		#encrypt password.
	    $encryptPwd = sha1($this->pass.$this->getPwdSalt());
	    
	    #create session marker for time limit.
	    //@TO-DO: Add this value into a session table in the database, plan for RC 2.
	    $_SESSION['ebbacptimer'] = $sessionLength;
	
		#user is an admin, let them log in. set to last as long as user selected.
		//@todo: make this session value, NOT a cookie.
		$expire = time()+3600*$sessionLength;
		setcookie("ebbacpu", $this->user, $expire, $boardPref->getPreferenceValue("cookie_path"), $boardPref->getPreferenceValue("cookie_domain"), $boardPref->getPreferenceValue("cookie_secure"), true);
		setcookie("ebbacpp", $encryptPwd, $expire, $boardPref->getPreferenceValue("cookie_path"), $boardPref->getPreferenceValue("cookie_domain"), $boardPref->getPreferenceValue("cookie_secure"), true);

		#generate session-based validation.
		$this->regenerateSession(true);
	}

    /**
	*acpLogOut
	*
	*Performs logout process, removing any sessions or cookies needed for the ACP.
	*
	*@modified 5/19/10
	*
	*@access public
	*/
	public function acpLogOut(){

		global $boardPref;

   		#set session to a secure status.
		//ini_get('session.cookie_secure',true);

		#close out ACP cookies.
		if (isset($_COOKIE['ebbacpu']) and (isset($_COOKIE['ebbacpp']))){
	        #encrypt password.
		    $encryptPwd = sha1($this->pass.$this->getPwdSalt());

		    #get session time.
	        $sessionLength = $_SESSION['ebbacptimer'];

			#get cookie time.
   			$expire = time()+3600*$sessionLength;

			#destroy cookies.
			setcookie("ebbacpu", $this->user, $expire, $boardPref->getPreferenceValue("cookie_path"), $boardPref->getPreferenceValue("cookie_domain"), $boardPref->getPreferenceValue("cookie_secure"), true);
			setcookie("ebbacpp", $encryptPwd, $expire, $boardPref->getPreferenceValue("cookie_path"), $boardPref->getPreferenceValue("cookie_domain"), $boardPref->getPreferenceValue("cookie_secure"), true);
			
			#clear session data.
			session_destroy();
		}
	}

    /**
	*logOn
	*
	*Performs login process, creating any sessions or cookies needed for the system.
	*
	*@modified 12/28/10
	*
	*@access public
	*/
	public function logOn(){

		global $boardPref, $remember, $ipAddr, $db;

        #encrypt password.
	    $encryptPwd = sha1($this->pass.$this->getPwdSalt());
	    
		#set session to a secure status.
		//ini_get('session.cookie_secure',true);
		
		#see if user wants to remain logged on.
		if($remember == 0){
			#create a session.
			$_SESSION['ebb_user'] = $this->user;
			$_SESSION['ebb_pass'] = $encryptPwd;
			
			#generate session-based validation.
			$this->regenerateSession(true);
		}else{
			#setup session length.
			$expireTime = time() + (2592000);

			#create cookie.
			setcookie("ebbuser", $this->user, $expireTime, $boardPref->getPreferenceValue("cookie_path"), $boardPref->getPreferenceValue("cookie_domain"), $boardPref->getPreferenceValue("cookie_secure"), true);
			setcookie("ebbpass", $encryptPwd, $expireTime, $boardPref->getPreferenceValue("cookie_path"), $boardPref->getPreferenceValue("cookie_domain"), $boardPref->getPreferenceValue("cookie_secure"), true);
			
			#remove user's IP from who's online list.
			$db->SQL = "delete from ebb_online where ip='$ipAddr'";
			$db->query();
			
			#generate session-based validation.
			$this->regenerateSession(true);
		}
	}

	
    /**
	*logOut
	*
	*Performs logout process, removing any sessions or cookies created from the system.
	*
	*@modified 7/29/11
	*
	*
	*@access public
	*/
	public function logOut(){

		global $boardPref, $db;

		#set session to a secure status.
		//ini_get('session.cookie_secure',true);

		#setup session length.
		$expireTime = time() - (2592000);

		#see if user wants to remain logged on.
		if(isset($_COOKIE['ebbuser'])){			

			#destroy cookies.
			setcookie("ebbuser", $this->user, $expireTime, $boardPref->getPreferenceValue("cookie_path"), $boardPref->getPreferenceValue("cookie_domain"), $boardPref->getPreferenceValue("cookie_secure"), true);
			setcookie("ebbpass", $this->pass, $expireTime, $boardPref->getPreferenceValue("cookie_path"), $boardPref->getPreferenceValue("cookie_domain"), $boardPref->getPreferenceValue("cookie_secure"), true);

			#remove user from who's online list.
			$db->SQL = "DELETE FROM ebb_online WHERE Username='".$this->user."'";
			$db->query();
			
			#close out ACP cookie if needed
			if (isset($_COOKIE['ebbacpu']) and (isset($_COOKIE['ebbacpp']))){
				$expire = time()-3600;
				setcookie("ebbacpu", $this->user, $expireTime, $boardPref->getPreferenceValue("cookie_path"), $boardPref->getPreferenceValue("cookie_domain"), $boardPref->getPreferenceValue("cookie_secure"), true);
				setcookie("ebbacpp", $this->pass, $expireTime, $boardPref->getPreferenceValue("cookie_path"), $boardPref->getPreferenceValue("cookie_domain"), $boardPref->getPreferenceValue("cookie_secure"), true);
			}
			
			#clear session data.
			session_destroy();
		}else{
			#remove user from who's online list.
			$db->SQL = "DELETE FROM ebb_online WHERE Username='".$this->user."'";
			$db->query();

			#close out ACP cookie if needed
			if (isset($_COOKIE['ebbacpu']) and (isset($_COOKIE['ebbacpp']))){
				$expire = time()-3600;
				setcookie("ebbacpu", $this->user, $expireTime, $boardPref->getPreferenceValue("cookie_path"), $boardPref->getPreferenceValue("cookie_domain"), $boardPref->getPreferenceValue("cookie_secure"), true);
				setcookie("ebbacpp", $this->pass, $expireTime, $boardPref->getPreferenceValue("cookie_path"), $boardPref->getPreferenceValue("cookie_domain"), $boardPref->getPreferenceValue("cookie_secure"), true);
			}

			#clear session data.
			session_destroy();
		}
	}

    /**
	*validateSession
	*
	*Performs a check to ensure the session value is valid and not hijacked.
	*@param $destroy bool - true will detroy old session data; false will not.
	*@modified 12/28/10
	*@access public
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
	*regenerateSession
	*
	*creates a new session id and destroys the old session id(if any exists).
	*@param $destroy bool - true will detroy old session data; false will not.
	*@modified 12/28/10
	*@access public
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
	*isActive
	*
	*Checks to see if the user is verified as active or still waiting for activation.
	*
	*@modified 8/10/09
	*
	*
	*@access public
	*/
	public function isActive(){

		global $db;

	    #check against the database to see if the username and password match.
        $db->SQL = "SELECT active FROM ebb_users WHERE Username='".$this->user."' LIMIT 1";
		$validateStatus = $db->fetchResults();

		#setup bool. value to see if user is active or not.
		if($validateStatus['active'] == 0){
		    return(false);
		}else{
		    return(true);
		}
	}

    /**
	*deactivateUser
	*
	*disable user's active status.
	*
	*@modified 10/26/09
	*
	*@access public
	*/
	public function deactivateUser(){

		global $db;
		
		$db->SQL = "UPDATE ebb_users SET active='0' WHERE Username='".$this->user."' LIMIT 1";
		$db->query();
	}

	/**
	*activateUser
	*
	*activates the user to allow them access the system.
	*
	*@modified 10/26/09
	*
	*@access public
	*/
	public function activateUser(){

		global $db;

		$db->SQL = "UPDATE ebb_users SET active='1' WHERE Username='".$this->user."' LIMIT 1";
		$db->query();
	}

    /**
	*getFailedLoginCt
	*
	*See how many times the user failed to login correctly.
	*
	*@modified 8/10/09
	*
	*
	*@access public
	*/
	public function getFailedLoginCt(){

	    global $db;

	    #get the count from the user table..
        $db->SQL = "SELECT failed_attempts FROM ebb_users WHERE Username='".$this->user."' LIMIT 1";
		$getFailedLoginCt = $db->fetchResults();

		return($getFailedLoginCt);
	}

    /**
	*setFailedLogin
	*
	*increment fail count for defined user.
	*
	*@modified 7/24/11
	*
	*
	*@access public
	*/
	public function setFailedLogin(){

	    global $db;

	    #get new count.
		$newCount = $this->getFailedLoginCt();
		$incrementFailedCt = $newCount['failed_attempts'] + 1;

	    #get the count from the user table.
        $db->SQL = "UPDATE ebb_users SET failed_attempts='$incrementFailedCt' WHERE Username='".$this->user."' LIMIT 1";
		$db->query();
	}

    /**
	*clearFailedLogin
	*
	*Clears the failed count to 0.
	*
	*@modified 8/10/09
	*
	*
	*@access public
	*/
	public function clearFailedLogin(){

	    global $db;

	    #clear count.
        $db->SQL = "UPDATE ebb_users SET failed_attempts='0' WHERE Username='".$this->user."' LIMIT 1";
		$db->query();
	}

    /**
	*checkBan
	*
	*Checks to see if the user is banned or suspended.
	*
	*@modified 4/5/10
	*
	*
	*@access public
	*/
	public function checkBan(){

		global $db, $lang, $suspend_length, $suspend_date, $groupProfile;

		#see if user is marked as banned.
		if($groupProfile == 6){
			$error = new notifySys($lang['banned'], true);
			$error->displayError();
		}

		#see if user is suspended.
		if($suspend_length > 0){
			#see if user is still suspended.
			$math = 3600 * $suspend_length;
			$suspend_time = $suspend_date + $math;
			$today = time() - $math;
			if($suspend_time > $today){
				$error = new notifySys($lang['suspended'], true);
				$error->displayError();
			}
		}
		#see if the IP of the user is banned.
        $uip = detectProxy();
		$db->SQL = "SELECT ban_item FROM ebb_banlist WHERE ban_type='IP' AND ban_item LIKE '%$uip%'";
		$banChk = $db->affectedRows();
		
		#output an error msg.
		if($banChk == 1){
			$error = new notifySys($lang['banned'], true);
			$error->displayError();
		}
	}
}//END CLASS
?>
