<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
  *  Preference.php
  * @package Elite Bulletin Board v3
  * @author Elite Bulletin Board Team <http://elite-board.us>
  * @copyright  (c) 2006-2011
  * @license http://opensource.org/licenses/gpl-license.php GNU Public License
  * @version 10/11/2011
*/

class Preference{

	/**
	  * @var object CodeIgniter object.
	*/
	private $ci;


    public function __construct() {
        $this->ci =& get_instance();
    }
 	
	/**
	 * Obtains the value of a defined preference.
	 * @version 10/11/11
	 * @param string $prefName Name of preference to look for.
	 * @access public
	*/
	public function getPreferenceValue($prefName){
		//SQL grabbing count of all topics for this board.
		$this->ci->db->select('pref_value')->from('ebb_preference')->where('pref_name', $prefName)->limit(1);
		$query = $this->ci->db->get();
		$res = $query->row();

		return $res->pref_value;
	}

	/**
	 * Obtains the type of a defined preference.
	 * @version 10/11/11
	 * @param string $prefName Name of preference to look for.
	 * @access public
	*/
	public function getPreferenceType($prefName){
		//SQL grabbing count of all topics for this board.
		$this->ci->db->select('pref_type')->from('ebb_preference')->where('pref_name', $prefName)->limit(1);
		$query = $this->ci->db->get();
		$res = $query->row();

		return $res->pref_value;
	}
	
	/**
	 * Save the defined preference value.
	 * @version 9/6/11
	 * @param string $prefName Name of preference to look for.
	 * @param string $prefValue New value of preference.
	 * @access public
	*/
	public function savePreferences($prefName, $prefValue){		
		#update preferences.
		$this->ci->db->where('pref_name', $prefName);
		$this->ci->db->update('ebb_preference', array('pref_value' => $prefValue));
	}

	/**
	 * Create a new preference value(used for updates or modification-purposes only).
	 * @version 10/1/11
	 * @param string $prefName Name of preference to look for.
	 * @param string $prefValue Value of preference.
	 * @param string $prefType Type of preference.
	 * @access public
	*/
	public function newPreference($prefName, $prefValue, $prefType){
		#setup values.
		$data = array(
		   'pref_name' => $prefName,
		   'pref_value' => $prefValue,
		   'pref_type' => $prefType);

		#add new preference.
		$this->ci->db->insert('ebb_preference', $data);
	}

	/**
	 * Deletes a preference from the database(used for updates or modification-purposes only).
	 * @version 10/1/11
	 * @param string $prefName Name of preference to look for.
	 * @access public
	*/
	public function deletePreference($prefName){
		$this->ci->db->delete('ebb_preference', array(' pref_name' => $prefName));
	}
}
?>