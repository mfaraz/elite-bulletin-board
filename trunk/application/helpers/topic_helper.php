<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
Filename: topic_function.php
Last Modified: 07/02/2012

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

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
