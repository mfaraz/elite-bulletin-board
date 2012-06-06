<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * attachmentsmodel.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 06/03/12
*/

/**
 * Attachment Entity
 */
class Attachmentsmodel extends CI_Model {

	/**
	 * DATA MEMBERS
	*/

	private $id;
	private $userName;
	private $tiD;
	private $piD;
	private $fileName;
	private $encryptedFileName;
	private $encryptionSalt;
	private $fileType;
	private $fileSize;
	private $downloadCount;

	/**
	 * PROPERTIES
	*/

	/**
	 * set value for id 
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:null,primary,unique,autoincrement
	 *
	 * @param mixed $id
	 * @return EbbAttachmentsmodel
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
	 * set value for Username 
	 *
	 * type:VARCHAR,size:25,default:
	 *
	 * @param mixed $userName
	 * @return EbbAttachmentsmodel
	 */
	public function &setUserName($userName) {
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
	 * set value for tid 
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @param mixed $tiD
	 * @return EbbAttachmentsmodel
	 */
	public function &setTiD($tiD) {
		$this->tiD=$tiD;
		return $this;
	}

	/**
	 * get value for tid 
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @return mixed
	 */
	public function getTiD() {
		return $this->tiD;
	}

	/**
	 * set value for pid 
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @param mixed $piD
	 * @return EbbAttachmentsmodel
	 */
	public function &setPiD($piD) {
		$this->piD=$piD;
		return $this;
	}

	/**
	 * get value for pid 
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @return mixed
	 */
	public function getPiD() {
		return $this->piD;
	}

	/**
	 * set value for Filename 
	 *
	 * type:VARCHAR,size:100,default:
	 *
	 * @param mixed $fileName
	 * @return EbbAttachmentsmodel
	 */
	public function &setFileName($fileName) {
		$this->fileName=$fileName;
		return $this;
	}

	/**
	 * get value for Filename 
	 *
	 * type:VARCHAR,size:100,default:
	 *
	 * @return mixed
	 */
	public function getFileName() {
		return $this->fileName;
	}

	/**
	 * set value for encryptedFileName 
	 *
	 * type:VARCHAR,size:40,default:null
	 *
	 * @param mixed $encryptedFileName
	 * @return EbbAttachmentsmodel
	 */
	public function &setEncryptedFileName($encryptedFileName) {
		$this->encryptedFileName=$encryptedFileName;
		return $this;
	}

	/**
	 * get value for encryptedFileName 
	 *
	 * type:VARCHAR,size:40,default:null
	 *
	 * @return mixed
	 */
	public function getEncryptedFileName() {
		return $this->encryptedFileName;
	}

	/**
	 * set value for encryptionSalt 
	 *
	 * type:VARCHAR,size:8,default:null
	 *
	 * @param mixed $encryptionSalt
	 * @return EbbAttachmentsmodel
	 */
	public function &setEncryptionSalt($encryptionSalt) {
		$this->encryptionSalt=$encryptionSalt;
		return $this;
	}

	/**
	 * get value for encryptionSalt 
	 *
	 * type:VARCHAR,size:8,default:null
	 *
	 * @return mixed
	 */
	public function getEncryptionSalt() {
		return $this->encryptionSalt;
	}

	/**
	 * set value for File_Type 
	 *
	 * type:VARCHAR,size:100,default:
	 *
	 * @param mixed $fileType
	 * @return EbbAttachmentsmodel
	 */
	public function &setFileType($fileType) {
		$this->fileType=$fileType;
		return $this;
	}

	/**
	 * get value for File_Type 
	 *
	 * type:VARCHAR,size:100,default:
	 *
	 * @return mixed
	 */
	public function getFileType() {
		return $this->fileType;
	}

	/**
	 * set value for File_Size 
	 *
	 * type:INT,size:10,default:0
	 *
	 * @param mixed $fileSize
	 * @return EbbAttachmentsmodel
	 */
	public function &setFileSize($fileSize) {
		$this->fileSize=$fileSize;
		return $this;
	}

	/**
	 * get value for File_Size 
	 *
	 * type:INT,size:10,default:0
	 *
	 * @return mixed
	 */
	public function getFileSize() {
		return $this->fileSize;
	}

	/**
	 * set value for Download_Count 
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @param mixed $downloadCount
	 * @return EbbAttachmentsmodel
	 */
	public function &setDownloadCount($downloadCount) {
		$this->downloadCount=$downloadCount;
		return $this;
	}

	/**
	 * get value for Download_Count 
	 *
	 * type:MEDIUMINT UNSIGNED,size:8,default:0
	 *
	 * @return mixed
	 */
	public function getDownloadCount() {
		return $this->downloadCount;
	}

	/**
	 * METHODS
	*/

	/**
	 * Get a list of files uploaded by defined user.
	 * @param string $user user who uploaded the files.
	 * @return boolean|array
	 * @version 06/03/12
	 */
	public function GetAttachment($user) {
		
		//setup reply data array.
		$files = array();

		//SQL grabbing count of all topics for this board.
		$this->db->select('id, Filename, File_Size, File_Type')
		  ->from('ebb_attachments')
		  ->where('Username', $user)
		  ->where('tid', 0)
		  ->where('pid', 0);
		$query = $this->db->get();

		//see if we have any records to show.
		if($query->num_rows() > 0) {
			//loop through data and bind to an array.
			foreach ($query->result() as $row) {
				$files[] = $row;
			}
			return $files;
		} else {
			//no record was found, throw an error.
			return FALSE;
		}
		
	}

	/**
	 * Adds attachment to database.
	 * @access public
	 * @version 05/31/12 
	 */
	public function CreateAttachment() {
		#setup values.
		$data = array(
		  'Username' => $this->getUserName(),
		  'tid' => $this->getTiD(),
		  'pid' => $this->getPiD(),
		  'Filename' => $this->getFileName(),
		  'encryptedFileName' => $this->getEncryptedFileName(),
		  'encryptionSalt' => $this->getEncryptionSalt(),
		  'File_Type' => $this->getFileType(),
		  'File_Size' => $this->getFileSize(),
		  'Download_Count' => $this->getDownloadCount()
        );

		#add new user.
		$this->db->insert('ebb_attachments', $data);
	}
	
	/**
	 * Assigns attachment to user and topic id.
	 * @param string $user user who uploaded file(s)
	 * @param integer $tid Topic ID.
	 * @param integer $attachId Attachment ID.
	 * @access public
	 * @version 05/29/12
	 */
	public function AssignAttachment($tid, $pid, $attachId) {
		
		$data = array(
		  "tid" => $tid,
		  "pid" => $pid
		);
		
		#update attachment.
		$this->db->where('id', $attachId);
		$this->db->update('ebb_attachments', $data);
	}

	/**
	 * Delete file from DB and file system.
	 * @param string $file filename.
	 * @return boolean
	 * @version 06/03/12
	 */
	public function DeleteAttachment($file) {
		
		if (is_file(UPLOAD_PATH.$file)) {
			$success = unlink(UPLOAD_PATH.$file);

			//see if the file successfully deleted.
			if ($success) {
				//remove entry from db.
				$this->db->where('Filename', $file);
				$this->db->delete('ebb_attachments');
				
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
		
	}

}
?>