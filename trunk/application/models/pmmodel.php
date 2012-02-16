<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * pmmodel.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 02/02/2012
*/

/**
 * PM Entity
 */
class Pmmodel extends CI_Model {

	/**
	 * DATA MEMBERS
	*/

	private $id;
	private $subject;
	private $sender;
	private $receiver;
	private $folder;
	private $message;
	private $date;
	private $readStatus;

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
	 * @return EbbPmmodel
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
	 * set value for Subject 
	 *
	 * type:VARCHAR,size:25,default:
	 *
	 * @param mixed $subject
	 * @return EbbPmmodel
	 */
	public function &setSubject($subject) {
		$this->subject=$subject;
		return $this;
	}

	/**
	 * get value for Subject 
	 *
	 * type:VARCHAR,size:25,default:
	 *
	 * @return mixed
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * set value for Sender 
	 *
	 * type:VARCHAR,size:25,default:
	 *
	 * @param mixed $sender
	 * @return EbbPmmodel
	 */
	public function &setSender($sender) {
		$this->sender=$sender;
		return $this;
	}

	/**
	 * get value for Sender 
	 *
	 * type:VARCHAR,size:25,default:
	 *
	 * @return mixed
	 */
	public function getSender() {
		return $this->sender;
	}

	/**
	 * set value for Receiver
	 *
	 * type:VARCHAR,size:25,default:
	 *
	 * @param mixed $receiver
	 * @return EbbPmmodel
	 */
	public function &setReceiver($receiver) {
		$this->receiver=$receiver;
		return $this;
	}

	/**
	 * get value for Receiver
	 *
	 * type:VARCHAR,size:25,default:
	 *
	 * @return mixed
	 */
	public function getReceiver() {
		return $this->receiver;
	}

	/**
	 * set value for Folder 
	 *
	 * type:VARCHAR,size:7,default:null
	 *
	 * @param mixed $folder
	 * @return EbbPmmodel
	 */
	public function &setFolder($folder) {
		$this->folder=$folder;
		return $this;
	}

	/**
	 * get value for Folder 
	 *
	 * type:VARCHAR,size:7,default:null
	 *
	 * @return mixed
	 */
	public function getFolder() {
		return $this->folder;
	}

	/**
	 * set value for Message 
	 *
	 * type:TEXT,size:65535,default:null
	 *
	 * @param mixed $message
	 * @return EbbPmmodel
	 */
	public function &setMessage($message) {
		$this->message=$message;
		return $this;
	}

	/**
	 * get value for Message 
	 *
	 * type:TEXT,size:65535,default:null
	 *
	 * @return mixed
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * set value for Date 
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @param mixed $date
	 * @return EbbPmmodel
	 */
	public function &setDate($date) {
		$this->date=$date;
		return $this;
	}

	/**
	 * get value for Date 
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @return mixed
	 */
	public function getDate() {
		return $this->date;
	}

	/**
	 * set value for Read_Status 
	 *
	 * type:CHAR,size:3,default:
	 *
	 * @param mixed $readStatus
	 * @return EbbPmmodel
	 */
	public function &setReadStatus($readStatus) {
		$this->readStatus=$readStatus;
		return $this;
	}

	/**
	 * get value for Read_Status 
	 *
	 * type:CHAR,size:3,default:
	 *
	 * @return mixed
	 */
	public function getReadStatus() {
		return $this->readStatus;
	}

	/**
	 * METHODS
	*/

	

}
?>