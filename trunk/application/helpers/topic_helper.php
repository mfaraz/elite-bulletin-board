<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
Filename: topic_function.php
Last Modified: 07/03/2012

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

/**
 * Builds a list of boards & sub-boards.
 * @params integer $boardID Board ID to select.
 * @return string
 * @version 07/03/12 
 */
function boardListSelect($boardID) {
	
	#obtain codeigniter object.
	$ci =& get_instance();
	
	$data = array();
	
	$ci->db->select('id, Board')->from('ebb_boards')->where('type', 2)->or_where('type',3)->order_by("B_Order", "asc");
	$query = $ci->db->get();
	foreach ($query->result_array() as $row) {
			$data[$row['id']] = $row['Board'];
	}
	
	//setup form based on user's selection.
	return form_dropdown('movetopic', $data, $boardID, 'class="text"', 'id="movetopic"');
}

/**
 * Check read status on a selected board.
 * @version 11/22/11
 * @param integer $bid - Board ID to select a board.
 * @param string $user - Username to check against.
 * @return integer $readCt - either read(1) or unread(0).
*/
function readBoardStat($bid, $user){

	#obtain codeigniter object.
	$ci =& get_instance();

	#grab last post date.
	$ci->db->select('last_update')->from('ebb_boards')->where('id', $bid);
	$Query = $ci->db->get();
	$res = $Query->row();

	
	#see if user is guest.
	if (($user == "guest") OR ($res->last_update == "")) {
	    $readCt = 1;
	}else{
		$ci->db->select('Board')->from('ebb_read_board')->where('Board', $bid)->where('User', $user);
		$readCt = $ci->db->count_all_results();
	}

	return ($readCt);
}
