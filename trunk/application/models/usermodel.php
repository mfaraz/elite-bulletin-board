<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * usermodel.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 11/16/2011
*/

class Usermodel extends CI_Model {

	private $id;
	private $userName;
	private $password;
	private $salt;
	private $email;
	private $customTitle;
	private $lastVisit;
	private $pmNotify;
	private $hideEmail;
	private $mSn;
	private $aol;
	private $yahoo;
	private $icq;
	private $www;
	private $location;
	private $avatar;
	private $sig;
	private $timeFormat;
	private $timeZone;
	private $dateJoined;
	private $ip;
	private $style;
	private $language;
	private $postCount;
	private $lastPost;
	private $lastSearch;
	private $failedAttempts;
	private $active;
	private $actKey;
	private $warningLevel;
	private $suspendLength;
	private $suspendTime;

	/**
	 * store for old instance after object has been modified
	 *
	 * @var Usermodel
	 */
	private $oldInstance=null;

	public function __construct() {
        parent::__construct();
    }

	/**
	 * get old instance if this has been modified, otherwise return null
	 *
	 * @return Usermodel
	 */
	public function getOldInstance() {
		return $this->oldInstance;
	}

	/**
	 * called when the field with the passed id has changed
	 *
	 * @param int $fieldId
	 */
	protected function notifyChanged($fieldId) {
		if (is_null($this->getOldInstance())) {
			$this->oldInstance=clone $this;
			$this->oldInstance->notifyPristine();
		}
	}

	/**
	 * set this instance into pristine state
	 */
	public function notifyPristine() {
		$this->oldInstance=null;
	}

	/**
	 * set value for id
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:null,primary,unique,autoincrement
	 *
	 * @param mixed $id
	 * @return Usermodel
	 */
	public function &setId($id) {
		$this->notifyChanged("id");
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
	 * set value for Username
	 *
	 * type:VARCHAR,size:25,default:
	 *
	 * @param mixed $userName
	 * @return Usermodel
	 */
	public function &setUserName($userName) {
		$this->notifyChanged("Username");
		$this->userName=$userName;
		return $this;
	}

	/**
	 * get value for Username
	 *
	 * type:VARCHAR,size:25,default:
	 *
	 * @return mixed
	 */
	public function getUserName() {
		return $this->userName;
	}

	/**
	 * set value for Password
	 *
	 * type:VARCHAR,size:40,default:null
	 *
	 * @param mixed $password
	 * @return Usermodel
	 */
	public function &setPassword($password) {
		$this->notifyChanged("Password");
		$this->password=$password;
		return $this;
	}

	/**
	 * get value for Password
	 *
	 * type:VARCHAR,size:40,default:null
	 *
	 * @return mixed
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * set value for salt
	 *
	 * type:VARCHAR,size:8,default:null
	 *
	 * @param mixed $salt
	 * @return Usermodel
	 */
	public function &setSalt($salt) {
		$this->notifyChanged("salt");
		$this->salt=$salt;
		return $this;
	}

	/**
	 * get value for salt
	 *
	 * type:VARCHAR,size:8,default:null
	 *
	 * @return mixed
	 */
	public function getSalt() {
		return $this->salt;
	}

	/**
	 * set value for Email
	 *
	 * type:VARCHAR,size:255,default:
	 *
	 * @param mixed $email
	 * @return Usermodel
	 */
	public function &setEmail($email) {
		$this->notifyChanged("Email");
		$this->email=$email;
		return $this;
	}

	/**
	 * get value for Email
	 *
	 * type:VARCHAR,size:255,default:
	 *
	 * @return mixed
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * set value for Custom_Title
	 *
	 * type:VARCHAR,size:20,default:null
	 *
	 * @param mixed $customTitle
	 * @return Usermodel
	 */
	public function &setCustomTitle($customTitle) {
		$this->notifyChanged("Custom_Title");
		$this->customTitle=$customTitle;
		return $this;
	}

	/**
	 * get value for Custom_Title
	 *
	 * type:VARCHAR,size:20,default:null
	 *
	 * @return mixed
	 */
	public function getCustomTitle() {
		return $this->customTitle;
	}

	/**
	 * set value for last_visit
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @param mixed $lastVisit
	 * @return Usermodel
	 */
	public function &setLastVisit($lastVisit) {
		$this->notifyChanged("last_visit");
		$this->lastVisit=$lastVisit;
		return $this;
	}

	/**
	 * get value for last_visit
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @return mixed
	 */
	public function getLastVisit() {
		return $this->lastVisit;
	}

	/**
	 * set value for PM_Notify
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @param mixed $pmNotify
	 * @return Usermodel
	 */
	public function &setPmNotify($pmNotify) {
		$this->notifyChanged("PM_Notify");
		$this->pmNotify=$pmNotify;
		return $this;
	}

	/**
	 * get value for PM_Notify
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @return mixed
	 */
	public function getPmNotify() {
		return $this->pmNotify;
	}

	/**
	 * set value for Hide_Email
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @param mixed $hideEmail
	 * @return Usermodel
	 */
	public function &setHideEmail($hideEmail) {
		$this->notifyChanged("Hide_Email");
		$this->hideEmail=$hideEmail;
		return $this;
	}

	/**
	 * get value for Hide_Email
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @return mixed
	 */
	public function getHideEmail() {
		return $this->hideEmail;
	}

	/**
	 * set value for MSN
	 *
	 * type:VARCHAR,size:255,default:
	 *
	 * @param mixed $mSn
	 * @return Usermodel
	 */
	public function &setMSn($mSn) {
		$this->notifyChanged("MSN");
		$this->mSn=$mSn;
		return $this;
	}

	/**
	 * get value for MSN
	 *
	 * type:VARCHAR,size:255,default:
	 *
	 * @return mixed
	 */
	public function getMSn() {
		return $this->mSn;
	}

	/**
	 * set value for AOL
	 *
	 * type:VARCHAR,size:255,default:
	 *
	 * @param mixed $aol
	 * @return Usermodel
	 */
	public function &setAol($aol) {
		$this->notifyChanged("AOL");
		$this->aol=$aol;
		return $this;
	}

	/**
	 * get value for AOL
	 *
	 * type:VARCHAR,size:255,default:
	 *
	 * @return mixed
	 */
	public function getAol() {
		return $this->aol;
	}

	/**
	 * set value for Yahoo
	 *
	 * type:VARCHAR,size:255,default:
	 *
	 * @param mixed $yahoo
	 * @return Usermodel
	 */
	public function &setYahoo($yahoo) {
		$this->notifyChanged("Yahoo");
		$this->yahoo=$yahoo;
		return $this;
	}

	/**
	 * get value for Yahoo
	 *
	 * type:VARCHAR,size:255,default:
	 *
	 * @return mixed
	 */
	public function getYahoo() {
		return $this->yahoo;
	}

	/**
	 * set value for ICQ
	 *
	 * type:VARCHAR,size:15,default:
	 *
	 * @param mixed $icq
	 * @return Usermodel
	 */
	public function &setIcq($icq) {
		$this->notifyChanged("ICQ");
		$this->icq=$icq;
		return $this;
	}

	/**
	 * get value for ICQ
	 *
	 * type:VARCHAR,size:15,default:
	 *
	 * @return mixed
	 */
	public function getIcq() {
		return $this->icq;
	}

	/**
	 * set value for WWW
	 *
	 * type:VARCHAR,size:200,default:
	 *
	 * @param mixed $www
	 * @return Usermodel
	 */
	public function &setWww($www) {
		$this->notifyChanged("WWW");
		$this->www=$www;
		return $this;
	}

	/**
	 * get value for WWW
	 *
	 * type:VARCHAR,size:200,default:
	 *
	 * @return mixed
	 */
	public function getWww() {
		return $this->www;
	}

	/**
	 * set value for Location
	 *
	 * type:VARCHAR,size:70,default:
	 *
	 * @param mixed $location
	 * @return Usermodel
	 */
	public function &setLocation($location) {
		$this->notifyChanged("Location");
		$this->location=$location;
		return $this;
	}

	/**
	 * get value for Location
	 *
	 * type:VARCHAR,size:70,default:
	 *
	 * @return mixed
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 * set value for Avatar
	 *
	 * type:VARCHAR,size:255,default:
	 *
	 * @param mixed $avatar
	 * @return Usermodel
	 */
	public function &setAvatar($avatar) {
		$this->notifyChanged("Avatar");
		$this->avatar=$avatar;
		return $this;
	}

	/**
	 * get value for Avatar
	 *
	 * type:VARCHAR,size:255,default:
	 *
	 * @return mixed
	 */
	public function getAvatar() {
		return $this->avatar;
	}

	/**
	 * set value for Sig
	 *
	 * type:TINYTEXT,size:255,default:null
	 *
	 * @param mixed $sig
	 * @return Usermodel
	 */
	public function &setSig($sig) {
		$this->notifyChanged("Sig");
		$this->sig=$sig;
		return $this;
	}

	/**
	 * get value for Sig
	 *
	 * type:TINYTEXT,size:255,default:null
	 *
	 * @return mixed
	 */
	public function getSig() {
		return $this->sig;
	}

	/**
	 * set value for Time_format
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @param mixed $timeFormat
	 * @return Usermodel
	 */
	public function &setTimeFormat($timeFormat) {
		$this->notifyChanged("Time_format");
		$this->timeFormat=$timeFormat;
		return $this;
	}

	/**
	 * get value for Time_format
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @return mixed
	 */
	public function getTimeFormat() {
		return $this->timeFormat;
	}

	/**
	 * set value for Time_Zone
	 *
	 * type:VARCHAR,size:255,default:
	 *
	 * @param mixed $timeZone
	 * @return Usermodel
	 */
	public function &setTimeZone($timeZone) {
		$this->notifyChanged("Time_Zone");
		$this->timeZone=$timeZone;
		return $this;
	}

	/**
	 * get value for Time_Zone
	 *
	 * type:VARCHAR,size:255,default:
	 *
	 * @return mixed
	 */
	public function getTimeZone() {
		return $this->timeZone;
	}

	/**
	 * set value for Date_Joined
	 *
	 * type:VARCHAR,size:50,default:
	 *
	 * @param mixed $dateJoined
	 * @return Usermodel
	 */
	public function &setDateJoined($dateJoined) {
		$this->notifyChanged("Date_Joined");
		$this->dateJoined=$dateJoined;
		return $this;
	}

	/**
	 * get value for Date_Joined
	 *
	 * type:VARCHAR,size:50,default:
	 *
	 * @return mixed
	 */
	public function getDateJoined() {
		return $this->dateJoined;
	}

	/**
	 * set value for IP
	 *
	 * type:VARCHAR,size:40,default:
	 *
	 * @param mixed $ip
	 * @return Usermodel
	 */
	public function &setIp($ip) {
		$this->notifyChanged("IP");
		$this->ip=$ip;
		return $this;
	}

	/**
	 * get value for IP
	 *
	 * type:VARCHAR,size:40,default:
	 *
	 * @return mixed
	 */
	public function getIp() {
		return $this->ip;
	}

	/**
	 * set value for Style
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @param mixed $style
	 * @return Usermodel
	 */
	public function &setStyle($style) {
		$this->notifyChanged("Style");
		$this->style=$style;
		return $this;
	}

	/**
	 * get value for Style
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @return mixed
	 */
	public function getStyle() {
		return $this->style;
	}

	/**
	 * set value for Language
	 *
	 * type:VARCHAR,size:50,default:
	 *
	 * @param mixed $language
	 * @return Usermodel
	 */
	public function &setLanguage($language) {
		$this->notifyChanged("Language");
		$this->language=$language;
		return $this;
	}

	/**
	 * get value for Language
	 *
	 * type:VARCHAR,size:50,default:
	 *
	 * @return mixed
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * set value for Post_Count
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @param mixed $postCount
	 * @return Usermodel
	 */
	public function &setPostCount($postCount) {
		$this->notifyChanged("Post_Count");
		$this->postCount=$postCount;
		return $this;
	}

	/**
	 * get value for Post_Count
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @return mixed
	 */
	public function getPostCount() {
		return $this->postCount;
	}

	/**
	 * set value for last_post
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @param mixed $lastPost
	 * @return Usermodel
	 */
	public function &setLastPost($lastPost) {
		$this->notifyChanged("last_post");
		$this->lastPost=$lastPost;
		return $this;
	}

	/**
	 * get value for last_post
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @return mixed
	 */
	public function getLastPost() {
		return $this->lastPost;
	}

	/**
	 * set value for last_search
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @param mixed $lastSearch
	 * @return Usermodel
	 */
	public function &setLastSearch($lastSearch) {
		$this->notifyChanged("last_search");
		$this->lastSearch=$lastSearch;
		return $this;
	}

	/**
	 * get value for last_search
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @return mixed
	 */
	public function getLastSearch() {
		return $this->lastSearch;
	}

	/**
	 * set value for failed_attempts
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @param mixed $failedAttempts
	 * @return Usermodel
	 */
	public function &setFailedAttempts($failedAttempts) {
		$this->notifyChanged("failed_attempts");
		$this->failedAttempts=$failedAttempts;
		return $this;
	}

	/**
	 * get value for failed_attempts
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @return mixed
	 */
	public function getFailedAttempts() {
		return $this->failedAttempts;
	}

	/**
	 * set value for active
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @param mixed $active
	 * @return Usermodel
	 */
	public function &setActive($active) {
		$this->notifyChanged("active");
		$this->active=$active;
		return $this;
	}

	/**
	 * get value for active
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @return mixed
	 */
	public function getActive() {
		return $this->active;
	}

	/**
	 * set value for act_key
	 *
	 * type:VARCHAR,size:32,default:
	 *
	 * @param mixed $actKey
	 * @return Usermodel
	 */
	public function &setActKey($actKey) {
		$this->notifyChanged("act_key");
		$this->actKey=$actKey;
		return $this;
	}

	/**
	 * get value for act_key
	 *
	 * type:VARCHAR,size:32,default:
	 *
	 * @return mixed
	 */
	public function getActKey() {
		return $this->actKey;
	}

	/**
	 * set value for warning_level
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @param mixed $warningLevel
	 * @return Usermodel
	 */
	public function &setWarningLevel($warningLevel) {
		$this->notifyChanged("warning_level");
		$this->warningLevel=$warningLevel;
		return $this;
	}

	/**
	 * get value for warning_level
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @return mixed
	 */
	public function getWarningLevel() {
		return $this->warningLevel;
	}

	/**
	 * set value for suspend_length
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @param mixed $suspendLength
	 * @return Usermodel
	 */
	public function &setSuspendLength($suspendLength) {
		$this->notifyChanged("suspend_length");
		$this->suspendLength=$suspendLength;
		return $this;
	}

	/**
	 * get value for suspend_length
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @return mixed
	 */
	public function getSuspendLength() {
		return $this->suspendLength;
	}

	/**
	 * set value for suspend_time
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @param mixed $suspendTime
	 * @return Usermodel
	 */
	public function &setSuspendTime($suspendTime) {
		$this->notifyChanged("suspend_time");
		$this->suspendTime=$suspendTime;
		return $this;
	}

	/**
	 * get value for suspend_time
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @return mixed
	 */
	public function getSuspendTime() {
		return $this->suspendTime;
	}

	/**
	 * Assign values from hash where the indexes match the tables field names
	 * @version 11/16/11
	 * @param array $result
	 */
	public function getUser($user) {

		//SQL grabbing count of all topics for this board.
		$this->db->select('id, Username, Password, salt, Email, Custom_Title, last_visit, PM_Notify, Hide_Email,MSN, AOL, Yahoo, ICQ, WWW, Location, Avatar, Sig, Time_format, Time_Zone, Date_Joined, IP, Style, Language, Post_Count, last_post, last_search, failed_attempts, active, act_key, warning_level, suspend_length, suspend_time')->from('ebb_users')->where('Username', $user);
		$query = $this->db->get();
		$userData = $query->row();

		//populate properties with values.
		$this->setId($userData->id);
		$this->setUserName($userData->Username);
		$this->setPassword($userData->Password);
		$this->setSalt($userData->salt);
		$this->setEmail($userData->Email);
		$this->setCustomTitle($userData->Custom_Title);
		$this->setLastVisit($userData->last_visit);
		$this->setPmNotify($userData->PM_Notify);
		$this->setHideEmail($userData->Hide_Email);
		$this->setMSn($userData->MSN);
		$this->setAol($userData->AOL);
		$this->setYahoo($userData->Yahoo);
		$this->setIcq($userData->ICQ);
		$this->setWww($userData->WWW);
		$this->setLocation($userData->Location);
		$this->setAvatar($userData->Avatar);
		$this->setSig($userData->Sig);
		$this->setTimeFormat($userData->Time_format);
		$this->setTimeZone($userData->Time_Zone);
		$this->setDateJoined($userData->Date_Joined);
		$this->setIp($userData->IP);
		$this->setStyle($userData->Style);
		$this->setLanguage($userData->Language);
		$this->setPostCount($userData->Post_Count);
		$this->setLastPost($userData->last_post);
		$this->setLastSearch($userData->last_search);
		$this->setFailedAttempts($userData->failed_attempts);
		$this->setActive($userData->active);
		$this->setActKey($userData->act_key);
		$this->setWarningLevel($userData->warning_level);
		$this->setSuspendLength($userData->suspend_length);
		$this->setSuspendTime($userData->suspend_time);
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

}
?>