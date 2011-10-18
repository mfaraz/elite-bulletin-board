<?php
define('IN_EBB', true);
/**
Filename: auth.php
Last Modified: 12/29/2010

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

#load up some needed files.
require_once "config.php";
require_once FULLPATH."/header.php";

#see if the user is already logged in.
if($logged_user == "guest"){
	#Obtain login form values.
    $usr = $db->filterMySQL($_POST['username']);
    $pwd = $db->filterMySQL($_POST['password']);
    $ipAddr = detectProxy();
    $remember = (isset($_POST['auto_login'])) ? 1 : 0;

	#do some validation.
	if((empty($usr)) AND (empty($pwd))){
		#setup error session.
		$_SESSION['errors'] = $lang['blank'];

		#direct user.
		redirect('login.php', false, 0);
	}else{
	    #Call up login class.
        $usrAuth = new login($usr, $pwd);
        
        #see if login was found valid.
		if($usrAuth->validateLogin() == true){
			#see if user is inactive.
			if($usrAuth->isActive() == false){
				#setup error session.
				$_SESSION['errors'] = $lang['inactiveuser'];

				#direct user.
				redirect('login.php', false, 0);
			}else{
			    #see if board is disabled.
				if($boardPref->getPreferenceValue("board_status") == 0){
					#see if user has proper rights to access under this limited operational status.
		            $chkGroupPolicy = new groupPolicy($usr);
		            $getGroupAccess = $chkGroupPolicy->groupAccessLevel();
		            
					if($getGroupAccess == 1){
						#clear any failed login attempts from their record.
						$usrAuth->clearFailedLogin();

						#setup cookie or session(based on user's preference.
						$usrAuth->logOn();

						#direct user to their previous location.
						redirect('index.php', false, 0);
					}else{
						#setup error session.
						$_SESSION['errors'] = $lang['offlinemsg'];

						#direct user.
						redirect('login.php', false, 0);
					}
				}else{
					#clear any failed login attempts from their record.
					$usrAuth->clearFailedLogin();
			
					#setup cookie or session(based on user's preference.
					$usrAuth->logOn();
					
					#direct user to their previous location.
     				redirect('index.php', false, 0);
				}
			}
		}else{
        	#get current failed login count
			if($usrAuth->getFailedLoginCt() == 5){
			    #deactivate the user's account(for their safety).
				$usrAuth->deactivateUser();
				
			    #alert user of reaching their limit of incorrect login attempts.
				#setup error session.
				$_SESSION['errors'] = $lang['lockeduser'];

				#direct user.
				redirect('login.php', false, 0);
			}else{
			    #add to failed login count.
				$usrAuth->setFailedLogin();

				#setup error session.
				$_SESSION['errors'] = $lang['nomatch'];

				#direct user.
				redirect('login.php', false, 0);
			}
		}
	}
}else{
	#user is logged in, so place them elsewhere.
    redirect('index.php', false, 0);
}
?>
