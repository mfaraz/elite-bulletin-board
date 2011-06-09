<?php
if (!defined('IN_EBB')) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: acpheader.php
Last Modified: 11/11/2010

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

#load requires files.
require_once FULLPATH."/includes/admin_function.php";
require_once FULLPATH."/includes/acp/auditSystem.class.php";

//check to see if this user is able to access this area.
if (($groupAccess == 2) OR ($groupAccess == 0) OR ($groupAccess == 3)){
	redirect('index.php', false, 0);
}

#see if user confirmed login.
if (isset($_COOKIE['ebbacpu']) and (isset($_COOKIE['ebbacpp']))){
		#validate & filter values.
		$acpUsr = $db->filterMySQL($_COOKIE['ebbacpu']);
		$acpPwd = $db->filterMySQL($_COOKIE['ebbacpp']);
		
		#see if the ACP cookies matches the root-level cookies.
		if($acpUsr !== $logged_user){
			#log this into our audit system.
			$acpAudit = new auditSystem();
			$acpAudit->logAction("User Authorization Mismatch Detected!", $acpUsr, time(), detectProxy());
			
			#display message.
			$error = new notifySys("USER AUTHORIZATION MISMATCH!", true, true, __FILE__, __LINE__);
			$error->genericError();
		}

		#see if cookie value belongs to a user on the roster.
	    $acpUsrAuth = new login($acpUsr, $acpPwd);

        #validate administation account.
		if($acpUsrAuth->validateAdministrator() == false){
			#log this into our audit system.
			$acpAudit = new auditSystem();
			$acpAudit->logAction("Invalid Administrator Detected!", $acpUsr, time(), detectProxy());
			
			#display message.
			$error = new notifySys("INVALID COOKIE OR SESSION!", true, true, __FILE__, __LINE__);
			$error->genericError();
		}else{
			#direct administrator to system updater, if the file exists.
			if (file_exists(FULLPATH."/install/update.php")){
				redirect('install/update.php', false, 0);
			}
		}
}else{
	#make sure user isn't already at the login form.
	if((basename($_SERVER['PHP_SELF']) == 'acp_login.php') OR (basename($_SERVER['PHP_SELF']) == 'acp_login.php?action=auth')){
		//do nothing.
	}else{
		#go to login page.
		redirect('acp/acp_login.php', false, 0);
	}
}
?>
