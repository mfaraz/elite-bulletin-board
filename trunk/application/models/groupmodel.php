<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * groupmodel.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1/3/2012
*/

class Groupmodel extends CI_Model {

	#
	#define data member.
	#

	/**
	 * @var str Username.
	 */
	public $user;

	/**
 	 * @var int GroupID.
	 */
	public $gid;

    public function __construct()
    {
        parent::__construct();
    }

    /**
	 * Validates gid value used within class to ensure user is correctly authenticated.
	 * @version 1/3/12
	 * @return boolean
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
			$this->db->select('id')->from('ebb_permission_profile')->where('id', $this->gid)->limit(1);
			$validateGroup = $this->db->count_all_results();

			if($validateGroup == 1){
			    return (true);
			}else{
			    return(false);
			}
		}
	}

	/**
	 * Obtains the access level of the defined user.
	 * @version 1/3/12
	 * @return string SQL result of requested access level.
	 * @access public
	*/
	public function getGroupAccessLevel(){

	    #see if user is guest, if so, they have zero-level access.
		if($this->user == "guest"){
		    return(0);
		}else{
			$this->db->select('access_level')->from('ebb_permission_profile')->where('id', $this->gid)->limit(1);
			$Query = $this->db->get();
			$accessLevel = $Query->row();
			return($accessLevel->access_level);
		}
	}

	/**
	 * Obtain the group name for defined group.
	 * @version 1/3/12
	 * @return string SQL result of requested group name.
	 * @access public
	*/
	public function getGroupName(){

	    #see if user is guest, if so, set gorpu name as simply guest.
		if($this->user == "guest"){
			return('guest');
		}else{
			$this->db->select('profile')->from('ebb_permission_profile')->where('id', $this->gid)->limit(1);
			$Query = $this->db->get();
			$getGroupName = $Query->row();
			return($getGroupName->profile);
		}
	}

	/**
	 * use to either promote or demote a user.
	 * @version 1/3/12
	 * @param integer $newGID new GID user is part of.
	 * @access public
	*/
	public function changeGroupID($newGID){

	    #see if user is guest, if so, exit without result.
		if($this->user == "guest"){
            show_error($this->lang->line('groupstatus'),500, $this->lang->line('error'));
		}else{
			$this->db->where('Username', $this->user);
			$this->db->update('ebb_users', array('gid' => $newGID));
		}
	}

	/**
	 * validate user's privileges.
	 * @version 1/3/12
	 * @param string $permissionAction action code being validated.
	 * @return integer $permissionValue automatic deny return for guest account.
	 * @return string filtered string to use in SQL query.
	 * @access private
	*/
	private function accessVaildator($permissionAction){

		#see if user is guest, if so, deny any requests.
		if ($this->user == "guest") {
			return (false);
		} else {
			#see if user ID is incorrect or Null.
			if ($this->validateGroup()) {

                #lets also check to make sure the action requested is valid.
                $this->db->select('id')->from('ebb_permission_actions')->where('id', $permissionAction);
                $permissionActionChk = $this->db->count_all_results();

                if ($permissionActionChk == 0) {
                    show_error($this->lang->line('invalidaction').'<hr />File:'.__FILE__.'<br />Line:'.__LINE__,500, $this->lang->line('error'));
                } else {
                  	#see if user has correct permission to access requested permission.
                    $this->db->select('set_value')->from('ebb_permission_data')->where('profile', $this->gid)->where('permission', $permissionAction);
                    $Query = $this->db->get();
                    $validatePermission = $Query->row();

                    #output value in script.
                    if($validatePermission->set_value == 1){
                        return(true);
                    }else{
                        return(false);
                    }
                }
            } else {
                show_error($this->lang->line('invalidprofile').'<hr />File:'.__FILE__.'<br />Line:'.__LINE__,500, $this->lang->line('error'));
			}
		}
	}

	/**
	 * Validate to see if user can access the requested area.
	 * @version 12/30/11
	 * @param string $action action in check.
	 * @return boolean
	 * @access private
	*/
	private function permissionCheck($action){

		#autmatically fail check if user is a guest, and its not set to public.
		if(($this->user == "guest") AND ($action != 0)) {
		    $permissionChk = false;
		} else {
			if(($action == 1) AND ($this->groupAccessLevel() == 1)) { //ADMIN ONLY
				$permissionChk = true;
			} elseif(($action == 2) AND ($this->groupAccessLevel() == 1) or ($this->groupAccessLevel() == 2)) { //MODERATOR OR ADMIN
				$permissionChk = true;
			} elseif(($action == 3) AND ($this->groupAccessLevel() == 3) or ($this->groupAccessLevel() == 2) or ($this->groupAccessLevel() == 1)) { //REG. USERS
				$permissionChk = true;
			} elseif($action == 4){ //NO ONE
				$permissionChk = false;
			} elseif(($action == 5) and ($checkgroup == 1) or ($this->groupAccessLevel() == 1) or ($checkmod == 1)) { //PRIVATE
				//REBUILD THIS LOGIC
				$permissionChk = true;
			} elseif($action == 0) { //EVERYONE
				$permissionChk = true;
			} else {
				$permissionChk = false;
			}
		}
		return($permissionChk);
	}

	/**
	 * Validate to see if user can access the requested area.
	 * @version 3/22/10
	 * @param int $type type of permission being checked (0=board, 1=group).
	 * @param string $action The action being validated.
	 * @return boolean
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