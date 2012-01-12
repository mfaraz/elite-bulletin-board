<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 *  groupPolicy.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 11/20/2011
*/

//
// REBUILT THIS NOW THATGID IS INCLUDED ON EBB_USERS.
//

class groupPolicy{

	#
	#define data member.
	#

	/**
	 * @var str Username.
	 */
	private $user;

	/**
 	 * @var int GroupID.
	 */
	private $gid;

	/**
	 * @var object CodeIgniter object.
	*/
	private $ci;

    /**
	 * Validates user's group status and access.
	 * @version 3/9/10
	 * @param string $username - username used in group policy processing.
	 * @access public
	*/
	public function __construct($username){
	
		$this->ci =& get_instance();
	
		#see if user = guest.
		if($username['usr'] == "guest"){
			$this->gid = 0;
			$this->user = "guest";
		}else{
			$this->user = $username['usr'];

			#run a check on system
			if($this->validateGroupStatus() == true){
			    #get group ID.
				$this->gid = $this->getGroupID();

				#validate the group exists.
				if($this->validateGroup() == false){
					$params = array(
						  'message' => $this->ci->lang->line('nogid'),
						  'titleStat' => true,
						  'debug' => true,
						  'ln' => __FILE__,
						  'fle' => __LINE__);
					$this->ci->load->library('notifysys', $params);
					$this->ci->notifysys->genericError();
				}
			}else{
				show_error($this->ci->lang->line('groupstatus').': '.$this->user.'<hr />File:'.__FILE__.'<br />Line:'.__LINE__, 500, $this->ci->lang->line('error'));
			}
		}
	}

    /**
	 * clear data member values.
	 * @version 8/27/09
	 * @access public
	*/
	public function __destruct(){
		unset($this->user);
		unset($this->gid);
	}
	
    /**
	 * Validates gid value used within class to ensure user is correctly authenticated.
	 * @version 10/27/11
	 * @return boolean (true|false)
	 * @access private
	*/
	private function validateGroup(){

	    #see if this is a guest, if so, do some hard-coded checks.
		if($this->user == "guest"){
		    if(($this->user == "guest") and ($this->gid == 0)){
		        return(true);
		    }else{
		        return(false);
		    }
		}else{
			$this->ci->db->select('id')->from('ebb_groups')->where('id', $this->gid)->limit(1);
			$validateGroup = $this->ci->db->count_all_results();

			if($validateGroup == 1){
			    return (true);
			}else{
			    return(false);
			}
		}
	}
	
	/**
	 * Validates that the group user has an active membership in defined gid.
	 * @version 9/29/11
	 * @return boolean (true|false)
	 * @access private
	 * @deprecated no longer needed as of v3 RC2.
	*/
	private function validateGroupStatus(){
	
	    #see if this is guest, if so, do some hard-coded checks.
		if($this->user == "guest"){
		    if(($this->user == "guest") AND ($this->gid == 0)){
		        return(true);
		    }else{
		        return(false);
		    }
		}else{
			$this->ci->db->select('gid')->from('ebb_group_users')->where('Username', $this->user)->where('Status', 'Active')->limit(1);
			$validateGroupStatus = $this->ci->db->count_all_results();
			
			if($validateGroupStatus == 1){
			    return (true);
			}else{
			    return(false);
			}
		}
	}
	
	/**
	 * Obtains the GroupID to the group the defined user belongs to.
	 * @version 10/27/11
	 * @return string SQL result of the requested groupID.
	 * @access private
	 * @deprecated no longer needed as of v3 RC2.
	*/
	private function getGroupID(){
	    
	    #set to 0, guest has no group setup.
		if($this->user == "guest"){
		    $getGID = 0;
		    return($getGID);
		}else{
			$this->ci->db->select('gid')->from('ebb_group_users')->where('Username', $this->user)->where('Status', 'Active')->limit(1);
			$Query = $this->ci->db->get();
			$getGID = $Query->row();

			return($getGID->gid);
		}
	}
	
	/**
	 * Obtains the access level of the defined user.
	 * @version 10/27/11
	 * @return string  SQL result of requested access level.
	 * @access public
	*/
	public function groupAccessLevel(){
	    
	    #see if user is guest, if so, they have zero-level access.
		if($this->user == "guest"){
		    return(0);
		}else{
			$this->ci->db->select('Level')->from('ebb_groups')->where('id', $this->gid)->limit(1);
			$Query = $this->ci->db->get();
			$accessLevel = $Query->row();
			return($accessLevel->Level);
		}
	}
	
	/**
	 * Obtain the profile in use for defined group.
	 * @version 10/27/11
	 * @return string  SQL result of requested group profile.
	 * @access public
	*/
	public function getGroupProfile(){

	    #see if user is guest, if so, set profile to zero-level access.
		if($this->user == "guest"){
			return(0);
		}else{
			$this->ci->db->select('permission_type')->from('ebb_groups')->where('id', $this->gid)->limit(1);
			$groupProfileQ = $this->ci->db->get();
			$groupProfileR = $groupProfileQ->row();
			return($groupProfileR->permission_type);
		}
	}
	
	/**
	 * Obtain the group name for defined group.
	 * @version 10/27/11
	 * @return string SQL result of requested group name.
	 * @access public
	*/
	public function getGroupName(){

	    #see if user is guest, if so, set gorpu name as simply guest.
		if($this->user == "guest"){
			return('guest');
		}else{
			$this->ci->db->select('Name')->from('ebb_groups')->where('id', $this->gid)->limit(1);
			$Query = $this->ci->db->get();
			$getGroupName = $Query->row();
			return($getGroupName->Name);
		}
	}
	
	/**
	 * use to either promote or demote a user.
	 * @version 9/29/11
	 * @param integer $newGID new GID user is part of.
	 * @access public
	*/
	public function changeGroupID($newGID){

	    #see if user is guest, if so, exit without result.
		if($this->user == "guest"){
			$error = new notifySys($err['groupstatus'], true);
			$error->genericError();
		}else{	
			$this->ci->db->where('Username', $this->user);
			$this->ci->db->update('ebb_group_users', array('gid' => $newGID));
		}
	}

	/**
	 * validate user's privileges.
	 * @version 11/20/11
	 * @param string $permissionAction - action code being validated.
	 * @return integer $permissionValue - automatic deny return for guest account.
	 * @return string filtered string to use in SQL query.
	 * @access private
	*/
	private function accessVaildator($permissionAction){
		
		#see if user is guest, if so, deny any requests.
		if($this->user == "guest"){
			return (false);
		}else{
			#first lets make sure the permission profile used is valid.
			$gProfile = $this->getGroupProfile();

			$this->ci->db->select('id')->from('ebb_permission_profile')->where('id', $gProfile);
			$permissionProfileChk = $this->ci->db->count_all_results();

			#see if user ID is incorrect or Null.
			if(($permissionProfileChk == 0) and ($this->user !== "guest")){
				$params = array(
					  'message' => $this->ci->lang->line('invalidprofile'),
					  'titleStat' => false,
					  'debug' => true,
					  'line' => __FILE__,
					  'file' => __LINE__);
				$this->ci->load->library('notifysys', $params);
				$this->ci->notifysys->genericError();
			}

			#lets also check to make sure the action requested is valid.
			$this->ci->db->select('id')->from('ebb_permission_actions')->where('id', $permissionAction);
			$permissionActionChk = $this->ci->db->count_all_results();

			if($permissionActionChk == 0){
				$params = array(
					  'message' => $this->ci->lang->line('invalidaction'),
					  'titleStat' => false,
					  'debug' => true,
					  'line' => __FILE__,
					  'file' => __LINE__);
				$this->ci->load->library('notifysys', $params);
				$this->ci->notifysys->genericError();
			}

			#see if user has correct permission to access requested permission.
			$this->ci->db->select('set_value')->from('ebb_permission_data')->where('profile', $gProfile)->where('permission', $permissionAction);
			$Query = $this->ci->db->get();
			$validatePermission = $Query->row();

			#output value in script.
			if($validatePermission->set_value == 1){
			    return(true);
			}else{
			    return(false);
			}
		}
	}

	/**
	 * Validate to see if user can access the requested area.
	 * @version 8/27/09
	 * @param string $action - action in check.
	 * @return boolean $permissionChk - (true|false).
	 * @access private
	*/
	private function permissionCheck($action){
		
		//this will need work further later.
		//@TODO consider removing it, it is  outdated and not used on v3 core.
		$checkmod = 1;
		
		#autmatically fail check if user is a guest, and its not set to public.
		if(($this->user == "guest") AND ($action != 0)){
		    $permissionChk = false;
		}else{
			if($checkmod == 1){
				$permissionChk = true;
			}else{
				if(($action == 1) AND ($this->groupAccessLevel() == 1)){
					$permissionChk = true;
				}elseif(($action == 2) AND ($this->groupAccessLevel() == 1) or ($this->groupAccessLevel() == 2)){
					$permissionChk = true;
				}elseif(($action == 3) AND ($this->groupAccessLevel() == 3) or ($this->groupAccessLevel() == 2) or ($this->groupAccessLevel() == 1)){
					$permissionChk = true;
				}elseif($action == 4){
					$permissionChk = false;
				}elseif(($action == 5) and ($checkgroup == 1) or ($this->groupAccessLevel() == 1) or ($checkmod == 1)){
					$permissionChk = true;
				}elseif($action == 0){
					$permissionChk = true;
				}else{
					$permissionChk = false;
				}
			}
		}
		return($permissionChk);
	}
	
	/**
	 * Validate to see if user can access the requested area.
	 * @version 3/22/10
	 * @param int $type - type of permission being checked (0=board, 1=group).
	 * @param string $action - The action being validated.
	 * @return boolean $permissionChk - (true|false).
	 * @access public
	*/
	public function validateAccess($type, $action){
	
	    #see what type of permission to validate.
		if($type == 0){
		    #board-based permssion validation.
			if($this->permissionCheck($action) == true){
			    return(true);
			}else{
			    return(false);
			}
		}elseif($type == 1){
		    #group-based permission validation
			if($this->accessVaildator($action) == true){
			    return(true);
			}else{
			    return(false);
			}
		}else{
		    #invalid operation, return an automatic false.
		    return(false);
		}
	}
}
?>
