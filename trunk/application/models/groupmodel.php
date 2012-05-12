<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * groupmodel.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 05/10/2012
*/

/**
 * Group Entity
 */
class Groupmodel extends CI_Model {

	/**
	 * DATA MEMBERS
	*/

	private $id;
	private $name;
	private $description;
	private $enrollment;
	private $level;
	private $permissionType;
	public $IsGuest = FALSE; //flag to see if user is a guest or not.

	public function __construct() {
        parent::__construct();
    }

	/**
	 * PROPERTIES
	*/

	/**
	 * set value for id
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:null,primary,unique,autoincrement
	 *
	 * @param mixed $id
	 * @return Groupmodel
	 */
	public function &setId($id) {
		$this->id=$id;
		return $this;
	}

	/**
	 * get value for id
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:null,primary,unique,autoincrement
	 *
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * set value for Name
	 *
	 * type:VARCHAR,size:30,default:
	 *
	 * @param mixed $name
	 * @return Groupmodel
	 */
	public function &setName($name) {
		$this->name=$name;
		return $this;
	}

	/**
	 * get value for Name
	 *
	 * type:VARCHAR,size:30,default:
	 *
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * set value for Description
	 *
	 * type:TINYTEXT,size:255,default:null
	 *
	 * @param mixed $description
	 * @return Groupmodel
	 */
	public function &setDescription($description) {
		$this->description=$description;
		return $this;
	}

	/**
	 * get value for Description
	 *
	 * type:TINYTEXT,size:255,default:null
	 *
	 * @return mixed
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * set value for Enrollment
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @param mixed $enrollment
	 * @return Groupmodel
	 */
	public function &setEnrollment($enrollment) {
		$this->enrollment=$enrollment;
		return $this;
	}

	/**
	 * get value for Enrollment
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @return mixed
	 */
	public function getEnrollment() {
		return $this->enrollment;
	}

	/**
	 * set value for Level
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @param mixed $level
	 * @return Groupmodel
	 */
	public function &setLevel($level) {
		$this->level=$level;
		return $this;
	}

	/**
	 * get value for Level
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @return mixed
	 */
	public function getLevel() {
		return $this->level;
	}

	/**
	 * set value for permission_type
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @param mixed $permissionType
	 * @return Groupmodel
	 */
	public function &setPermissionType($permissionType) {
		$this->permissionType=$permissionType;
		return $this;
	}

	/**
	 * get value for permission_type
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @return mixed
	 */
	public function getPermissionType() {
		return $this->permissionType;
	}

	/**
	 * METHODS
	*/

	/**
	 * Populate properties with data.
	 * @param integer $gid defined GroupID assigned to logged in user.
	 * @version 04/12/12
	 */
	public function GetGroupData($gid) {

		//fetch topic data.
		$this->db->select('id, Name, Description, Enrollment, Level, permission_type');
		$this->db->from('ebb_groups');
		$this->db->where('id', $gid);
		$query = $this->db->get();

		//see if we have any records to show.
		if($query->num_rows() > 0) {
			$GroupData = $query->row();
			
			$this->setId($GroupData->id);
			$this->setName($GroupData->Name);
			$this->setDescription($GroupData->Description);
			$this->setEnrollment($GroupData->Enrollment);
			$this->setLevel($GroupData->Level);
			$this->setPermissionType($GroupData->permission_type);
		} else {
			//no record was found, throw an error.
			show_error($this->lang->line('invalidgid').'<hr />File:'.__FILE__.'<br />Line:'.__LINE__, 500, $this->lang->line('error'));
			log_message('error', 'invalid GroupID was provided.'); //log error in error log.
		}

	}

	/**
	 * use to either promote or demote a user.
	 * @version 02/15/12
	 * @param integer $newGID new GID user is part of.
	 * @access public
	*/
	public function changeGroupID($newGID){

	    #see if user is guest, if so, exit without result.
		if($this->IsGuest == TRUE){
            show_error($this->lang->line('groupstatus'),500, $this->lang->line('error'));
		}else{
			$this->db->where('Username', $this->user);
			$this->db->update('ebb_users', array('gid' => $newGID));
		}
	}

	/**
	 * validate user's privileges.
	 * @version 05/10/12
	 * @param string $permissionAction action code being validated.
	 * @return integer $permissionValue automatic deny return for guest account.
	 * @return string filtered string to use in SQL query.
	 * @access private
	*/
	private function accessVaildator($permissionAction){

		#see if user is guest, if so, deny any requests.
		if ($this->IsGuest == TRUE) {
			return FALSE;
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
                    $this->db->select('set_value')->from('ebb_permission_data')->where('profile', $this->permissionType)->where('permission', $permissionAction);
                    $Query = $this->db->get();
                    $validatePermission = $Query->row();

                    #output value in script.
					if($Query->num_rows() > 0) {
						if($validatePermission->set_value == 1){
							return TRUE;
						}else{
							return FALSE;
						}	
					} else {
						//show_error($this->lang->line('invaliduser').'-'.$permissionAction.'-'.$this->gid.'<hr />File:'.__FILE__.'<br />Line:'.__LINE__, 500, $this->lang->line('error'));
						return FALSE;
					}
                }
            } else {
                show_error($this->lang->line('invalidprofile').'<hr />File:'.__FILE__.'<br />Line:'.__LINE__,500, $this->lang->line('error'));
			}
		}
	}

	/**
	 * Validate to see if user can access the requested area.
	 * @version 02/15/12
	 * @param string $action action in check.
	 * @return boolean
	 * @access private
	*/
	private function permissionCheck($action){

		#autmatically fail check if user is a guest, and its not set to public.
		if(($this->IsGuest == TRUE) AND ($action != 0)) {
		    $permissionChk = false;
		} else {
			if(($action == 1) AND ($this->getLevel() == 1)) { //ADMIN ONLY
				$permissionChk = true;
			} elseif(($action == 2) AND ($this->getLevel() == 1) or ($this->getLevel() == 2)) { //MODERATOR OR ADMIN
				$permissionChk = true;
			} elseif(($action == 3) AND ($this->getLevel() == 3) or ($this->getLevel() == 2) or ($this->getLevel() == 1)) { //REG. USERS
				$permissionChk = true;
			} elseif($action == 4){ //NO ONE
				$permissionChk = false;
			//} elseif(($action == 5) and ($checkgroup == 1) or ($this->groupAccessLevel() == 1) or ($checkmod == 1)) { //PRIVATE
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
	
	/**
	 * Validates the entered group is valid.
	 * @return boolean
	 * @version 04/11/12
	 */
	private function validateGroup(){

		$this->db->select('id')->from('ebb_groups')->where('id', $this->gid)->limit(1);
        $validateGroup = $this->db->count_all_results();

		if($validateGroup == 1){
			return (true);
		}else{
			return(false);
		}
	}

}

?>