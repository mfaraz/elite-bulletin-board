<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * usermodel.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 10/5/2011
*/

class Usermodel extends CI_Model {

	/**
	 * DATA MEMBERS
	 */

	/**
	 * @var object $ci Codeigniter object.
	 */
	private $ci;

	/**
	 * @var string Username varchar(25)
	 */
	public $Username;

	/**
	 * @var string Password varchar(40)
	 */
	public $Password;

	/**
	 * @var string Email varchar(255)
	 */
	public $Email;

	/**
	 * @var string Custom_Title varchar(20)
	 */
	public $Custom_Title;

	/**
	 * @var integer PM_Notify tinyint(1)
	 */
	public $PM_Notify;

	/**
	 * @var integer Hide_Email tinyint(1)
	 */
	public $Hide_Email;

	/**
	 * @var string MSN varchar(255)
	 */
	public $MSN;

	/**
	 * @var string AOL varchar(255)
	 */
	public $AOL;

	/**
	 * @var string Yahoo varchar(225)
	 */
	public $Yahoo;

	/**
	 * @var string ICQ varchar(15)
	 */
	public $ICQ;

	/**
	 * @var string WWW varchar(200)
	 */
	public $WWW;

	/**
	 * @var string Location varchar(70)
	 */
	public $Location;

	/**
	 * @var string Avatar varchar(255)
	 */
	public $Avatar;

	/**
	 * @var string Sig varchar(255)
	 */
	public $Sig;

	/**
	 * @var string Time_format varchar(14)
	 */
	public $Time_format;

	/**
	 * @var string Time_Zone varchar(5)
	 */
	public $Time_Zone;

	/**
	 * @var string Date_Joined varchar(14)
	 */
	public $Date_Joined;

	/**
	 * @var string IP varchar(40)
	 */
	public $IP;

	/**
	 * @var string style mediumint(8)
	 */
	public $Style;

	/**
	 * @var string Language varchar(50)
	 */
	public $Language;

	/**
	 * @var string Post_Count mediumint(8)
	 */
	public $Post_Count;

	/**
	 * @var string last_post varchar(14)
	 */
	public $last_post;

	/**
	 * @var string last_search varchar(14)
	 */
	public $last_search;

	/**
	 * @var integer failed_attempts tinyint(1)
	 */
	public $failed_attempts;

	/**
	 * @var integer active tinyint(1)
	 */
	public $active;

	/**
	 * @var string act_key varchar(32)
	 */
	public $act_key;

	/**
	 * @var integer Date_Joined tinyint(1)
	 */
	public $warning_level;

	/**
	 * @var integer suspend_length tinyint(1)
	 */
	public $suspend_length;

	/**
	 * @var string suspend_time varchar(14)
	 */
	public $suspend_time;

	public function __construct()
    {
        parent::__construct();

		//load up codeigniter object.
		$this->ci =& get_instance();
    }

	/**
	 * Creates a new user.
	 * @access Public
	 * @version 9/5/2011
	*/
	public function CreateUser() {

	}

	/**
	 * Update a current user.
	 * @param string $user User who is getting updated.
	 * @access Public
	 * @version 9/5/2011
	*/
	public function UpdateUser($user) {

	}

	/**
	 * Grabs basic data about user.
	 * @param string $user The user we want to get info on.
	 * @access Public
	 * @version 9/12/11
	 */
	public function GetBasicUserData($user){
		
		//SQL grabbing count of all topics for this board.
		$Query = $this->ci->db->query("SELECT Username, Language, Time_format, Time_Zone, Style, last_visit, suspend_length, suspend_time FROM ebb_users WHERE Username=? LIMIT 1", $user);
		$userData = $Query->row();

		//setup data members.
		$this->Username = $userData->Username;
		$this->Language = $userData->Language;
		$this->Time_Zone = $userData->Time_Zone;
		$this->Time_format = $userData->Time_format;
		$this->Style = $userData->Style;
		//$this->last_visit = $userData->last_visit;
		$this->suspend_length = $userData->suspend_length;
		$this->suspend_time = $userData->suspend_time;
	}

	/**
	 * Grabs profile data about user.
	 * @param string $user The user we want to get info on.
	 * @access Public
	 * @version 9/12/11
	 */
	public function GetUserProfileData($user) {

	}

}

?>