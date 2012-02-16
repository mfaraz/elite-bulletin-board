<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * attachmentsmodel.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 02/02/2012
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

	public function GetAttachment() {

	}

	public function CreateAttachment() {

	}

	public function DeleteAttachment() {
		
	}

}
?>