<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * upload.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 11/15/2011
*/

/**
 * Handler of uploaded files.
 */
class Upload extends EBB_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->helper(array('form', 'url'));
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/upload
	 *	- or -
	 * 		http://example.com/index.php/upload/index
	*/	
	public function index() {
		//replace with TWIG setup.
		//$this->load->view('admin/upload', array('error' => ''));
	}
	
	/**
	 * Uploads file(s) to web server.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/upload/do_upload
	*/	
	public function do_upload() {

		$upload_path_url = base_url().'uploads/';

		$config['upload_path'] = FCPATH.'uploads/';
		$config['allowed_types'] = 'jpg'; //multiple extension example -> mp3|gif|jpg|txt
		$config['max_size'] = '30000'; //max file size in kilobytes.
		$config['encrypt_name'] = TRUE; //to prevent sniffing of files.
		$config['remove_spaces'] = TRUE; //to make web-ready.

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload()) {
			$error = array('error' => $this->upload->display_errors());
			//$this->load->view('upload', $error); replace with twig
		} else { 		
			$data = $this->upload->data();
			/*	
					  // to re-size for thumbnail images un-comment and set path here and in json array	
			   $config = array(
				'source_image' => $data['full_path'],
				'new_image' => $this->$upload_path_url '/thumbs',
				'maintain_ration' => true,
				'width' => 80,
				'height' => 80
			  );

			$this->load->library('image_lib', $config);
			$this->image_lib->resize();
			*/
			//set the data for the json array	
			$data->name = $data['file_name'];
			$data->size = $data['file_size'];
			$data->type = $data['file_type'];
			$data->url = $upload_path_url .$data['file_name'];
			$data->thumbnail_url = $upload_path_url .$data['file_name'];//I set this to original file since I did not create thumbs.  change to thumbnail directory if you do = $upload_path_url .'/thumbs' .$data['file_name']
			$data->delete_url = base_url().'upload/deleteImage/'.$data['file_name'];
			$data->delete_type = 'DELETE';

			//ensure this is an AJAX call
			if (IS_AJAX) {
				echo json_encode(array($data));
				//this has to be the only data returned or you will get an error.
				//if you don't give this a json array it will give you a Empty file upload result error
				//it you set this without the if(IS_AJAX)...else... you get ERROR:TRUE (my experience anyway)
			} else {
				//fallback if javascript is not enabled.
				$file_data['upload_data'] = $this->upload->data();				
				//$this->load->view('admin/upload_success', $file_data); replace with twig.
			}

		}

	}

	/**
	 * Deletes a file from the server.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/upload/delete/some_file
	*/	
	public function delete($file) {
		//gets the job done but you might want to add error checking and security 
		$success = unlink(FCPATH.'uploads/' .$file);
		
		//info to see if it is doing what it is supposed to.
		$info->sucess = $success;
		$info->path = base_url().'uploads/' .$file;
		$info->file = is_file(FCPATH.'uploads/' .$file);

		//ensure this is an AJAX call
		if (IS_AJAX) {
			echo json_encode(array($info));
		} else {
			//here you will need to decide what you want to show for a successful delete
			$file_data['delete_data'] = $file;
			//$this->load->view('admin/delete_success', $file_data); replace with twig.
		}	
	}
} //END Class
?>
