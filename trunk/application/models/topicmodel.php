<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * topicmodel.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 06/13/2012
*/

/**
 * Topic Entity
 */
class Topicmodel extends CI_Model {

	/**
	 * DATA MEMBERS
	*/
	
	private $author;
	private $tiD;
	private $piD;
	private $bid;
	private $topic;
	private $body;
	private $topicType;
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
	private $author_gid;
	private $author_postcount;
	private $author_warninglevel;
	private $author_avatar;
	private $author_signature;
	private $author_ctitle;
	private $author_group_profile;
	private $author_group_access;
		
    public function __construct() {
        parent::__construct();
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
	 * set value for pid
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:null,primary,unique,autoincrement
	 *
	 * @param mixed $piD
	 * @return Topicmodel
	*/
	public function &setPiD($piD) {
		$this->piD=$piD;
		return $this;
	}

	/**
	 * get value for pid
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:null,primary,unique,autoincrement
	 *
	 * @return mixed
	*/
	public function getPiD() {
		return $this->piD;
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
	 * set value for topic_type
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @param mixed $type
	 * @return Topicmodel
	*/
	public function &setTopicType($topicType) {
		$this->topicType = $topicType;
		return $this;
	}

	/**
	 * get value for topic_type
	 *
	 * type:BIT,size:0,default:0
	 *
	 * @return mixed
	*/
	public function getTopicType() {
		return $this->topicType;
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
	 * set value for gid
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:null
	 *
	 * @param mixed $gid
	 * @return Usermodel
	 */
	public function &setGid($gid) {
		$this->author_gid=$gid;
		return $this;
	}

	/**
	 * get value for gid
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:null
	 *
	 * @return mixed
	 */
	public function getGid() {
		return $this->author_gid;
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
		$this->author_postcount=$postCount;
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
		return $this->author_postcount;
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
		$this->author_warninglevel=$warningLevel;
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
		return $this->author_warninglevel;
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
		$this->author_avatar=$avatar;
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
		return $this->author_avatar;
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
		$this->author_signature=$sig;
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
		return $this->author_signature;
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
		$this->author_ctitle=$customTitle;
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
		return $this->author_ctitle;
	}

	/**
	 * set value for profile
	 *
	 * type:VARCHAR,size:30,default:null
	 *
	 * @param mixed $groupProfile
	 * @return Groupmodel
	 */
	public function &setGroupProfile($groupProfile) {
		$this->author_group_profile=$groupProfile;
		return $this;
	}

	/**
	 * get value for profile
	 *
	 * type:VARCHAR,size:30,default:null
	 *
	 * @return mixed
	 */
	public function getGroupProfile() {
		return $this->author_group_profile;
	}

	/**
	 * set value for access_level
	 *
	 * type:TINYINT,size:1,default:null
	 *
	 * @param mixed $groupAccess
	 * @return Groupmodel
	 */
	public function &setGroupAccess($groupAccess) {
		$this->author_group_access=$groupAccess;
		return $this;
	}

	/**
	 * get value for access_level
	 *
	 * type:TINYINT,size:1,default:null
	 *
	 * @return mixed
	 */
	public function getGroupAccess() {
		return $this->author_group_access;
	}

	/**
	 * METHODS
	*/
	
	/**
	 * Creates a new topic
	 * @access public
	 * @version 05/23/12 
	 * @return integer
	*/
	public function CreateTopic() {
		#setup values.
		$data = array(
		  'author' => $this->getAuthor(),
		  'bid' => $this->getBid(),
		  'Topic' => $this->getTopic(),
		  'Body' => $this->getBody(),
		  'topic_type' => $this->getTopicType(),
		  'important' => $this->getImportant(),
		  'IP' => $this->getIp(),
		  'Original_Date' => $this->getOriginalDate(),
		  'last_update' => $this->getLastUpdate(),
		  'Posted_User' => $this->getPostedUser(),
		  'Locked' => $this->getLocked(),
		  'Views' => $this->getViews(),
		  'Question' => $this->getQuestion(),
		  'disable_bbcode' => $this->getDisableBbCode(),
		  'disable_smiles' => $this->getDisableSmiles()
        );

		#add new topic.
		$this->db->insert('ebb_topics', $data);
		
		//get tid
		return $this->db->insert_id();
	}
	
	/**
	 * Create poll option.
	 * @param string $optionValue The poll option value.
	 * @param integer $topicId Topic ID
	 * @access public
	 * @version 05/29/12
	*/
	public function CreatePoll($optionValue, $topicId) {
		#setup values.
		$data = array(
		  'option_value' => $optionValue,
		  'tid' => $topicId
        );

		#add new topic.
		$this->db->insert('ebb_poll', $data);
	}
	
	/**
	 * Creates a reply
	 * @return integer
	 * @version 06/05/12 
	*/
	public function CreateReply() {
		#setup values.
		$data = array(
		  'author' => $this->getAuthor(),
		  'bid' => $this->getBid(),
		  'tid' => $this->getTiD(),
		  'Body' => $this->getBody(),
		  'IP' => $this->getIp(),
		  'Original_Date' => $this->getOriginalDate(),
		  'disable_bbcode' => $this->getDisableBbCode(),
		  'disable_smiles' => $this->getDisableSmiles()
        );

		#add new topic.
		$this->db->insert('ebb_posts', $data);
		
		//get tid
		return $this->db->insert_id();
	}
	
	/**
	 * Update topic data.
	 * @version 06/13/12
	 */
	public function ModifyTopic() {
		#setup values.
		$data = array(
		  'Topic' => $this->getTopic(),
		  'Body' => $this->getBody(),
		  'important' => $this->getImportant(),
		  'disable_bbcode' => $this->getDisableBbCode(),
		  'disable_smiles' => $this->getDisableSmiles()
        );
		
		#update topic.
		$this->db->where('tid', $this->getTiD());
		$this->db->update('ebb_topics', $data);
	}
	
	public function ModifyPoll() {
		
	}
	
	/**
	 * Update reply data.
	 * @version 06/13/12
	 */
	public function ModifyReply() {
		#setup values.
		$data = array(
		  'Body' => $this->getBody(),
		  'disable_bbcode' => $this->getDisableBbCode(),
		  'disable_smiles' => $this->getDisableSmiles()
        );
		
		#update reply.
		$this->db->where('pid', $this->getPiD());
		$this->db->update('ebb_posts', $data);
	}
	
	/**
	 * Delete topic data.
	 * @version 06/18/12
	 * @return boolean
	 */
	public function DeleteTopic() {
		
		$this->db->select('Filename')
		  ->from('ebb_attachments')
		  ->where('tid', $this->getTiD());
		$query = $this->db->get();
		
		//see if we have any records to delete.
		if($query->num_rows() > 0) {
			foreach ($query->result() as $attachRow) {
				$success = unlink(UPLOAD_PATH.$attachRow->Filename);
				
				//see if the file successfully deleted.
				if ($success) {
					//remove entry from db.
					$this->db->where('tid', $this->getTiD())
					  ->where('Filename', $attachRow->Filename)
					  ->delete('ebb_attachments');
				}
			}

			$this->db->where('tid', $this->getTiD())
			  ->delete('ebb_topics');

			return TRUE;
		} else {
			//no attachments, so just delete the topic.
			$this->db->where('tid', $this->getTiD())
			  ->delete('ebb_topics');
			return TRUE;
		}
		
	}
	
	/**
	 * Delete poll data for defined Topic ID.
	 * @version 06/18/12
	 */
	public function DeletePoll() {
		$this->db->where('tid', $this->getTiD())
		  ->delete('ebb_poll');
		
		$this->db->where('tid', $this->getTiD())
		  ->delete('ebb_votes');
	}
	
	/**
	 * Delete Replies.
	 * @param boolean $deleteAll Delete all replies or just one?
	 * @version 06/18/12
	 * @return boolean
	 */
	public function DeleteReply($deleteAll = FALSE) {
		//see if we want to delete all replies associated with a topic or just one reply.
		if ($deleteAll) {
			
			$this->db->select('pid')
			  ->from('ebb_posts')
			  ->where('tid', $this->getTiD());
			$rQuery = $this->db->get();
			
			//see if we have any replies to delete.
			if($rQuery->num_rows() == 0) {
				return TRUE; //no replies, just exit then.
			} else {
				//loop through data and clear attachments.
				foreach ($rQuery->result() as $replyRow) {
					$this->db->select('Filename')
					  ->from('ebb_attachments')
					  ->where('pid', $replyRow->pid);
					$aQuery = $this->db->get();
					
					//see if we have any records to show.
					if($aQuery->num_rows() > 0) {
						
						foreach ($aQuery->result() as $attachRow) {
							$success = unlink(UPLOAD_PATH.$attachRow->Filename);
							//see if the file successfully deleted.
							if ($success) {
								//remove entry from db.
								$this->db->where('pid', $replyRow->pid)
								  ->where('Filename', $attachRow->Filename)
								  ->delete('ebb_attachments');
							}
						}
					}
				}
				//delete all replies tied to defined topic id.
				$this->db->where('tid', $this->getTiD())
				  ->delete('ebb_posts');
				return TRUE;
			}
		} else {
			$this->db->select('Filename')
			  ->from('ebb_attachments')
			  ->where('pid', $this->getPiD());
			$query = $this->db->get();
			
			//see if we have any records to show.
			if($query->num_rows() > 0) {
				foreach ($query->result() as $attachRow) {
					$success = unlink(UPLOAD_PATH.$attachRow->Filename);
					
					//see if the file successfully deleted.
					if ($success) {
						//remove entry from db.
						$this->db->where('pid', $this->getPiD())
						  ->where('Filename', $attachRow->Filename)
						  ->delete('ebb_attachments');
					}
					
				}
				$this->db->where('pid', $this->getPiD())
				  ->delete('ebb_posts');

				return TRUE;
			} else {
				//no attachments, just delete the post.
				$this->db->where('pid', $this->getPiD())
				  ->delete('ebb_posts');
			}
		}
	}

	/**
	 * Grab topic data.
	 * @param int $tid TopicID
	 * @version 06/13/12
	 * @access public
	 * @return boolean
	*/
	public function GetTopicData($tid) {

		//fetch topic data.
		$this->db->select('t.tid, t.bid, t.author, t.Topic, t.Body, t.topic_type, t.important, t.IP, t.Original_Date, t.last_update, t.pid, t.Locked, t.Views, t.Question, t.disable_bbcode, t.disable_smiles, u.Post_Count, u.warning_level, u.Avatar, u.Sig, u.Custom_Title, g.profile, g.access_level')
		  ->from('ebb_topics t')
		  ->join('ebb_users u', 't.author=u.Username', 'LEFT')
		  ->join('ebb_permission_profile g', 'g.id=u.gid', 'LEFT')
		  ->where('tid', $tid);
		$query = $this->db->get();

		//see if we have any records to show.
		if($query->num_rows() > 0) {
		
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
			$this->setQuestion($TopicData->Question);
			$this->setTiD($TopicData->tid);
			$this->setTopic($TopicData->Topic);
			$this->setTopicType($TopicData->topic_type);
			$this->setViews($TopicData->Views);
			$this->setAvatar($TopicData->Avatar);
			$this->setPostCount($TopicData->Post_Count);
			$this->setSig($TopicData->Sig);
			$this->setWarningLevel($TopicData->warning_level);
			$this->setCustomTitle($TopicData->Custom_Title);
			$this->setGroupAccess($TopicData->access_level);
			$this->setGroupProfile($TopicData->profile);
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Get a single reply record.
	 * @param integer $pid Post ID
	 * @version 06/13/12
	 * @return boolean
	 */
	public function GetReplyData($pid){
		//fetch topic data.
		$this->db->select('p.author, p.pid, p.tid, p.bid, p.Body, p.IP, p.Original_Date, p.disable_smiles, p.disable_bbcode, t.Topic')
		  ->from('ebb_posts p')
		  ->join('ebb_topics t', 'p.tid=t.tid', 'LEFT')
		  ->where('p.pid', $pid);
		$query = $this->db->get();

		//see if we have any records to show.
		if($query->num_rows() > 0) {
		
			$PostData = $query->row();

			//populate properties with values.
			$this->setAuthor($PostData->author);
			$this->setTopic($PostData->Topic);
			$this->setPiD($PostData->pid);
			$this->setTiD($PostData->tid);
			$this->setBid($PostData->bid);
			$this->setBody($PostData->Body);
			$this->setDisableBbCode($PostData->disable_bbcode);
			$this->setDisableSmiles($PostData->disable_smiles);
			$this->setIp($PostData->IP);
			$this->setOriginalDate($PostData->Original_Date);
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Get a list of all replies to a topic.
	 * @version 06/13/12
	 * @param int $tid Topic ID.
	 * @param int $limit amount to show per page.
	 * @param int $start what entry to start from.
	 * @access public
	 * @return array,boolean
	*/
	public function GetReplies($tid, $limit, $start) {

		//setup reply data array.
		$replies = array();

		//SQL to get all topics from defined board.
		$this->db->select('p.author, p.pid, p.tid, p.bid, p.Body, p.IP, p.Original_Date, p.disable_smiles, p.disable_bbcode, u.Post_Count, u.warning_level, u.Avatar, u.Sig, u.Custom_Title, g.profile, g.access_level')
		  ->from('ebb_posts p')
		  ->join('ebb_users u', 'p.author=u.Username', 'LEFT')
		  ->join('ebb_permission_profile g', 'g.id=u.gid', 'LEFT')
		  ->where('tid', $tid)
		  ->limit($limit, $start);
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
	
    /**
     * Get Poll Options
     * @param integer $tid TopicID
     * @return array,boolean
	 * @version 06/13/12
    */
	public function GetPoll($tid) {
		
        //setup reply data array.
		$pollOpt = array();

		//SQL to get all topics from defined board.
		$this->db->select('option_value, option_id')
		  ->from('ebb_poll')
		  ->where('tid', $tid);
		$query = $this->db->get();

		//see if we have any records to show.
		if($query->num_rows() > 0) {

			//loop through data and bind to an array.
			foreach ($query->result() as $row) {
				$pollOpt[] = $row;
			}

			return $pollOpt;
		} else {
			return FALSE;
		}
        
	}

	/**
	 * Saves Vote Cast in the DataBase.
	 * @param string $user User who casted the vote
	 * @param integer $tid the topic that holding the vote
	 * @param integer $vote The vote value user selected
	 * @version 01/12/12
	*/
	public function CastVote($user, $tid, $vote) {

		$data = array(
		  'Username' => $user,
		  'tid' => $tid,
		  'Vote' => $vote
		);
		$this->db->insert('ebb_votes', $data);

	}
    
	/**
	 * Show the results of a poll.
	 * @param integer $tid Topic ID.
	 * @return boolean|array
	 * @version 06/06/12
	*/
    public function ShowPollResults($tid) {
       
        //setup reply data array.
		$pollRes = array();

		//SQL to get all topics from defined board.
		$this->db->select('Poll_Option, option_id');
		$this->db->from('ebb_poll');
		$this->db->where('tid', $tid);
		$query = $this->db->get();

		//see if we have any records to show.
		if($query->num_rows() > 0) {

			//loop through data and bind to an array.
			foreach ($query->result() as $row) {
				$pollRes[] = $row;
			}

			return $pollRes;
		} else {
			return FALSE;
		}
        
    }
}