<?php

class Pagemodel extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

	public function forumSubmit() {
	
		$txtField = $this->input->post('test', TRUE);
	
		if(!$txtField) {
			die('nothing entered.');
		} else {
			echo "Form value equals => ".$txtField;
		}
	
	}

}

?>
