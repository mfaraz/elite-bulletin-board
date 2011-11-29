<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * topicmodel.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 11/22/2011
*/

class Topicmodel extends CI_Model {

	/**
	 * DATA MEMBERS
	*/
	
	private $author;
	private $tiD;
	private $bid;
	private $topic;
	private $body;
	private $type;
	private $important;
	private $ip;
	private $originalDate;
	private $lastUpdate;
	private $postedUser;
	private $postLink;
	private $locked;
	private $views;
	private $question;
	private $disableBbCode;
	private $disableSmiles;


	/**
	 * store for old instance after object has been modified
	 *
	 * @var Topicmodel
	 */
	private $oldInstance=null;
	
    public function __construct() {
        parent::__construct();
    }

	/**
	 * get old instance if this has been modified, otherwise return null
	 *
	 * @return Topicmodel
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
	 * PROPERTIES
	*/

	/**
	 * set value for author
	 *
	 * type:VARCHAR,size:25,default:
	 *
	 * @param mixed $author
	 * @return Topicmodel
	*/
	public function &setAuthor($author) {
		$this->notifyChanged("AUTHOR");
		$this->author=$author;
		return $this;
	}

	/**
	 * get value for author
	 *
	 * type:VARCHAR,size:25,default:
	 *
	 * @return mixed
	*/
	public function getAuthor() {
		return $this->author;
	}

	/**
	 * set value for tid
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:null,primary,unique,autoincrement
	 *
	 * @param mixed $tiD
	 * @return Topicmodel
	*/
	public function &setTiD($tiD) {
		$this->notifyChanged("TID");
		$this->tiD=$tiD;
		return $this;
	}

	/**
	 * get value for tid
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:null,primary,unique,autoincrement
	 *
	 * @return mixed
	*/
	public function getTiD() {
		return $this->tiD;
	}

	/**
	 * set value for bid
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @param mixed $bid
	 * @return Topicmodel
	*/
	public function &setBid($bid) {
		$this->notifyChanged("bid");
		$this->bid=$bid;
		return $this;
	}

	/**
	 * get value for bid
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @return mixed
	*/
	public function getBid() {
		return $this->bid;
	}

	/**
	 * set value for Topic
	 *
	 * type:VARCHAR,size:50,default:
	 *
	 * @param mixed $topic
	 * @return Topicmodel
	*/
	public function &setTopic($topic) {
		$this->notifyChanged("Topic");
		$this->topic=$topic;
		return $this;
	}

	/**
	 * get value for Topic
	 *
	 * type:VARCHAR,size:50,default:
	 *
	 * @return mixed
	*/
	public function getTopic() {
		return $this->topic;
	}

	/**
	 * set value for Body
	 *
	 * type:TEXT,size:65535,default:null
	 *
	 * @param mixed $body
	 * @return Topicmodel
	*/
	public function &setBody($body) {
		$this->notifyChanged("Body");
		$this->body=$body;
		return $this;
	}

	/**
	 * get value for Body
	 *
	 * type:TEXT,size:65535,default:null
	 *
	 * @return mixed
	*/
	public function getBody() {
		return $this->body;
	}

	/**
	 * set value for Type
	 *
	 * type:VARCHAR,size:5,default:
	 *
	 * @param mixed $type
	 * @return Topicmodel
	*/
	public function &setType($type) {
		$this->notifyChanged("Type");
		$this->type=$type;
		return $this;
	}

	/**
	 * get value for Type
	 *
	 * type:VARCHAR,size:5,default:
	 *
	 * @return mixed
	*/
	public function getType() {
		return $this->type;
	}

	/**
	 * set value for important
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @param mixed $important
	 * @return Topicmodel
	*/
	public function &setImportant($important) {
		$this->notifyChanged("important");
		$this->important=$important;
		return $this;
	}

	/**
	 * get value for important
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @return mixed
	*/
	public function getImportant() {
		return $this->important;
	}

	/**
	 * set value for IP
	 *
	 * type:VARCHAR,size:40,default:
	 *
	 * @param mixed $ip
	 * @return Topicmodel
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
	 * set value for Original_Date
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @param mixed $originalDate
	 * @return Topicmodel
	*/
	public function &setOriginalDate($originalDate) {
		$this->notifyChanged("Original_Date");
		$this->originalDate=$originalDate;
		return $this;
	}

	/**
	 * get value for Original_Date
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @return mixed
	*/
	public function getOriginalDate() {
		return $this->originalDate;
	}

	/**
	 * set value for last_update
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @param mixed $lastUpdate
	 * @return Topicmodel
	*/
	public function &setLastUpdate($lastUpdate) {
		$this->notifyChanged("last_update");
		$this->lastUpdate=$lastUpdate;
		return $this;
	}

	/**
	 * get value for last_update
	 *
	 * type:VARCHAR,size:14,default:
	 *
	 * @return mixed
	*/
	public function getLastUpdate() {
		return $this->lastUpdate;
	}

	/**
	 * set value for Posted_User
	 *
	 * type:VARCHAR,size:25,default:
	 *
	 * @param mixed $postedUser
	 * @return Topicmodel
	*/
	public function &setPostedUser($postedUser) {
		$this->notifyChanged("Posted_User");
		$this->postedUser=$postedUser;
		return $this;
	}

	/**
	 * get value for Posted_User
	 *
	 * type:VARCHAR,size:25,default:
	 *
	 * @return mixed
	*/
	public function getPostedUser() {
		return $this->postedUser;
	}

	/**
	 * set value for Post_Link
	 *
	 * type:VARCHAR,size:100,default:
	 *
	 * @param mixed $postLink
	 * @return Topicmodel
	*/
	public function &setPostLink($postLink) {
		$this->notifyChanged("Post_Link");
		$this->postLink=$postLink;
		return $this;
	}

	/**
	 * get value for Post_Link
	 *
	 * type:VARCHAR,size:100,default:
	 *
	 * @return mixed
	*/
	public function getPostLink() {
		return $this->postLink;
	}

	/**
	 * set value for Locked
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @param mixed $locked
	 * @return Topicmodel
	*/
	public function &setLocked($locked) {
		$this->notifyChanged("Locked");
		$this->locked=$locked;
		return $this;
	}

	/**
	 * get value for Locked
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @return mixed
	*/
	public function getLocked() {
		return $this->locked;
	}

	/**
	 * set value for Views
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @param mixed $views
	 * @return Topicmodel
	*/
	public function &setViews($views) {
		$this->notifyChanged("Views");
		$this->views=$views;
		return $this;
	}

	/**
	 * get value for Views
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @return mixed
	*/
	public function getViews() {
		return $this->views;
	}

	/**
	 * set value for Question
	 *
	 * type:VARCHAR,size:50,default:
	 *
	 * @param mixed $question
	 * @return Topicmodel
	*/
	public function &setQuestion($question) {
		$this->notifyChanged("Question");
		$this->question=$question;
		return $this;
	}

	/**
	 * get value for Question
	 *
	 * type:VARCHAR,size:50,default:
	 *
	 * @return mixed
	*/
	public function getQuestion() {
		return $this->question;
	}

	/**
	 * set value for disable_bbcode
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @param mixed $disableBbCode
	 * @return Topicmodel
	*/
	public function &setDisableBbCode($disableBbCode) {
		$this->notifyChanged("disable_bbcode");
		$this->disableBbCode=$disableBbCode;
		return $this;
	}

	/**
	 * get value for disable_bbcode
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @return mixed
	*/
	public function getDisableBbCode() {
		return $this->disableBbCode;
	}

	/**
	 * set value for disable_smiles
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @param mixed $disableSmiles
	 * @return Topicmodel
	*/
	public function &setDisableSmiles($disableSmiles) {
		$this->notifyChanged("disable_smiles");
		$this->disableSmiles=$disableSmiles;
		return $this;
	}

	/**
	 * get value for disable_smiles
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @return mixed
	*/
	public function getDisableSmiles() {
		return $this->disableSmiles;
	}

	/**
	 * METHODS
	*/
	
	public function CreateTopic() {
		
	}
	
	public function CreatePoll() {
		
	}
	
	public function CreateReply() {
		
	}
	
	public function ModifyTopic() {
		
	}
	
	public function ModifyPoll() {
		
	}
	
	public function ModifyReply() {
		
	}
	
	public function DeleteTopic() {
		
	}
	
	public function DeletePoll() {
		
	}
	
	public function DeleteReply() {
		
	}

	/**
	 * Grab topic data.
	 * @param int $tid
	 */
	public function GetTopicData($tid) {

		$this->db->select('tid, bid, author, Topic, Body, Type, important, IP, Original_Date, last_update, Posted_User, Post_Link, Locked, Views, Question, disable_bbcode, disable_smiles')->from('ebb_topics')->where('tid', $tid);
		$query = $this->db->get();
		$TopicData = $query->row();

		//populate properties with values.
		$this->setAuthor($TopicData->author);
		$this->setBid($TopicData->bid);
		$this->setBody($TopicData->Body);
		$this->setDisableBbCode($TopicData->disable_bbcode);
		$this->setDisableSmiles($TopicData->disable_smiles);
		$this->setImportant($TopicData->important);
		$this->setIp($TopicData->IP);
		$this->setLastUpdate($TopicData->last_update);
		$this->setLocked($TopicData->Locked);
		$this->setOriginalDate($TopicData->Original_Date);
		$this->setPostLink($TopicData->Post_Link);
		$this->setPostedUser($TopicData->Posted_User);
		$this->setQuestion($TopicData->Question);
		$this->setTiD($TopicData->tid);
		$this->setTopic($TopicData->Topic);
		$this->setType($TopicData->Type);
		$this->setViews($TopicData->Views);
	}

	/**
	 * Get a list of all replies to a topic.
	 * @version 9/6/11
	 * @param int $tid Topic ID.
	 * @param int $limit amount to show per page.
	 * @param int $start what entry to start from.
	 * @access public
	 * @return array
	*/
	public function GetReplies($tid, $limit, $start) {
		//SQL to get all topics from defined board.
		$this->db->select('author, pid, tid, bid, Body, IP, Original_Date, disable_smiles, disable_bbcode')->from('ebb_posts')->where('tid', $tid)->limit($limit, $start);
		$query = $this->db->get();

		//see if we have any records to show.
		if($query->num_rows() > 0) {

			//loop through data and bind to an array.
			foreach ($query->result() as $row) {
				$replies[] = $row;
			}

			return $replies;
		} else {
			return FALSE;
		}
	}
	
	public function GetPoll() {
		
	}
	
	public function CastVote() {
		
	}

}

?>