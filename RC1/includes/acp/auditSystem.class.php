<?php
if (!defined('IN_EBB') ) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: auditSystem.class.php
Last Modified: 11/10/2010

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
class auditSystem{

    /**
	*logAction
	*
	*Logs the action performed by a user within the ACP.
	*
	*@modified 5/5/10
	*
	*@param string $action - the specific action that was performed.
	*@param string $user - username used in action.
	*@param string $date - date/time this occured.
	*@param string $ip - user's IP Address.
	*
	*@access public
	*/
	public function logAction($action, $user, $date, $ip){

		global $db;

		if ((empty($user)) or (empty($action)) or (empty($date)) or (empty($ip))){
			$error = new notifySys("Function not used correctly!", true, true, __FILE__, __LINE__);
			$error->genericError();
		}else{
			#add info to log.
			$db->SQL = "INSERT INTO ebb_cplog (User, Action, Date, IP) VALUES('$user', '$action', '$date', '$ip')";
			$updater = $db->query();
		}
	}
	
	/**
	*viewAuditLog
	*
	*Outputs the audit log.
	*
	*@modified 5/5/10
	*
	*
	*@access public
	*/
	public function viewAuditLog(){

		global $db, $gmt, $timeFormat, $lang;

		$db->SQL = "SELECT User, IP, Date, Action FROM ebb_cplog ORDER BY Date DESC LIMIT 5";
		$acplogQ = $db->query();
		$acpCount = $db->affectedRows();

		#see if there are any logged reports.
		if($acpCount == 0){
			$auditLog = '<p>'.$lang['noacplog'].'</p>';
		}else{
			$auditLog = '';
			while ($log = mysql_fetch_assoc ($acplogQ)) {
				#format date/time.
				$auditDate = formatTime($timeFormat, $log['Date'], $gmt);
				
				#display logged information.
				$auditLog .= '<p>'.$log['User'].'('.$log['IP'].') on&nbsp;'.$auditDate.':&nbsp;'.$log['Action'].'</p>';
			}
		}
		return ($auditLog);
	}
	
	/**
	*viewFullAuditLog
	*
	*Outputs the entire audit log.
	*
	*@modified 5/5/10
	*
	*
	*@access public
	*/
	public function viewFullAuditLog(){

		global $db, $gmt, $timeFormat, $lang;

		$db->SQL = "SELECT User, IP, Date, Action FROM ebb_cplog ORDER BY Date DESC";
		$acplogQ = $db->query();
		$acpCount = $db->affectedRows();

		#see if there are any logged reports.
		if($acpCount == 0){
			$auditLog = '<p>'.$lang['noacplog'].'</p>';
		}else{
			$auditLog = '';
			while ($log = mysql_fetch_assoc ($acplogQ)) {
				#format date/time.
				$auditDate = formatTime($timeFormat, $log['Date'], $gmt);

				#display logged information.
				$auditLog .= '<p>'.$log['User'].'('.$log['IP'].') on&nbsp;'.$auditDate.':&nbsp;'.$log['Action'].'</p>';
			}
		}
		return ($auditLog);
	}
	
	/**
	*clearAuditLog
	*
	*clears the entire audit log.
	*
	*@modified 7/5/10
	*
	*
	*@access public
	*/
	public function clearAuditLog(){

    	global $db, $acpUsr;

		#clear audit log table.
		$db->SQL = "TRUNCATE TABLE ebb_cplog";
		$db->query();

		#log action in database.
		$this->logAction("Cleared Audit Log", $acpUsr, time(), detectProxy());
	}
	
}//END class
?>
