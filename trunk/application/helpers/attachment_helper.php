<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * attachment_helper.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1/11/2012
*/

/**
 * Grab an array of attachments to a topic or post.
 * @version 1/11/12
 * @param string $type topic or post attachments?
 * @param string $user the author who attached the file.
 * @param integer $id the TopicID or PostID.
 * @return array or FALSE is no data is found.
*/
function GetAttachments($type, $user, $id){

	//grab Codeigniter objects.
	$ci =& get_instance();

	//setup attachment data array.
	$attachments = array();

	if($type == "topic"){

		//SQL to get all attachments associated with defined topic id.
		$ci->db->select('id, Filename, File_Size, Download_Count');
		$ci->db->from('ebb_attachments');
		$ci->db->where('Username', $user);
		$ci->db->where('tid', $id);
		$query = $ci->db->get();

		//see if we have any records to show.
		if($query->num_rows() > 0) {

			//loop through data and bind to an array.
			foreach ($query->result() as $row) {
				$attachments[] = $row;
			}

			return $attachments;
		} else {
			return FALSE;
		}
	}elseif($type == "post"){

		//SQL to get all attachments associated with defined topic id.
		$ci->db->select('id, Filename, File_Size, Download_Count');
		$ci->db->from('ebb_attachments');
		$ci->db->where('Username', $user);
		$ci->db->where('pid', $id);
		$query = $ci->db->get();

		//see if we have any records to show.
		if($query->num_rows() > 0) {

			//loop through data and bind to an array.
			foreach ($query->result() as $row) {
				$attachments[] = $row;
			}

			return $attachments;
		} else {
			return FALSE;
		}
	}else{
		#function not called correctly.
		return FALSE;
	}
}

/**
* Converts file size of bytes to Kb.
* @version 1/11/12
* @param string $file - file size to convert.
* @return int - converted value of file size.
*/
function getFileSize($file){
	$fileSize = ceil($file / 1024);

	return ($fileSize);
}
?>
