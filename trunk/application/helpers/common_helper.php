<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * common_helper.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 10/9/2011 
*/


/**
 * Will format time based on user preference.
 * @version 9/8/09	
 * @param time() $time - timestamp generated by time()
 * @param string $format - format in use.
 * @param string $GMT - GMT timezone to offset by.
*/
function datetimeFormatter($time, $format, $GMT) {

 	#obtain codeigniter object.
	$ci =& get_instance();

	$ci->datetime_52->setTimestamp($time)->setTimezone(new DateTimeZone($GMT));

	return $ci->datetime_52->format($format);
	
}

/**
 * Loads a URL using cURL.
 * @param string $url
 * @version 6/24/11
 * @return string
 */
function curlLoadFromUrl($url) {

	try {
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);

		/* Check for 404 (file not found). */
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if($httpCode == 404) {
			return null;
		}

		curl_close($curl);
		return $result;
	} catch(Exception $e) {
		$error = new notifySys($e, true, true, __FILE__, __LINE__);
		$error->genericError();
    }
  }

/**
 * Will direct user to a secure connection is SSL is setup.
 * @version 10/26/09
*/
function redirectToHttps(){
	if($_SERVER['HTTPS'] !== "on"){
		$redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		header("Location: $redirect");
	}
}

/**
 * Checks to see if installation files are still on server.
 * @version 10/5/09
 * @return integer $setupExist - results of check.
*/
function checkInstall(){

	if (file_exists("install/install.php")){
		$setupExist = 1;
	}else{
		$setupExist = 0;
	}
	return ($setupExist);
}

/**
 * loads data for infoBox.
 * @version 11/12/10
 * @return string - infobox data.
*/
function informationPanel() {

	#obtain codeigniter object.
	$ci =& get_instance();
	
 	#SQL to get info data.
 	$ci->db->select('information')->from('ebb_information_ticker');
	$infoQ = $ci->db->get();

	#if no news exists on DB, just close it up.
	if ($ci->db->count_all_results() == 0) {
		#prime with stat-up data.
		$infoLst = '<ul id="news">'."\n".'
						<li><strong>'.$ci->preference->getPreferenceValue("board_name")->pref_value.' - '.$ci->lang->line('ticker_txt').'</strong></li>'."\n".'
						<li>'.$lang['nonews'].'</li>'."\n".'</ul>';
	} else {
		#prime with stat-up data.
		$infoLst = '<ul id="news">'."\n".'<li><strong>'.$ci->preference->getPreferenceValue("board_name")->pref_value.' - '.$ci->lang->line('ticker_txt').'</strong></li>'."\n";

		//loop through data.
		foreach ($infoQ->result() as $ticker) {
			$infoLst .= '<li>'.smiles(BBCode($ticker->information)).'</li>'."\n";
			//$infoLst .= '<li>'.$ticker->information.'</li>'."\n";
		}

		#finish list.
		$infoLst . '</ul>';
	}

	return $infoLst;
}

/**
 * get a collection of unread topics for search engine results.
 * @version 10/5/09
 * @return integer $count - results of count.
*/
function newpost_counter(){

	global $search_result, $search_result2, $logged_user, $db;
	//output any topics
	$count = 0;	 
	#get topic count.
	while ($r = mysql_fetch_assoc($search_result)) {
		$db->SQL = "select Topic from ebb_read_topic WHERE Topic='$r[tid]' and User='$logged_user'";
		$read_stat = $db->affectedRows();

		if ($read_stat == 0){
			//increment count
			$count++;
		}
	}
	#post count
	while ($r2 = mysql_fetch_assoc($search_result2)){
		//see if post is new.
		$db->SQL = "select Topic from ebb_read_topic WHERE Topic='$r2[tid]' and User='$logged_user'";
		$read_stat2 = $db->affectedRows();

		if ($read_stat2 == 0){
			//increment count
			$count++;
		}
	}
	return ($count);
}

/**
 * Get information regarding IP Address of user.
 * @version 10/5/09
 * @return string $iplist - IP Information.
*/
function ip_checker(){

	global $u, $ip, $lang, $db;

	$db->SQL = "select Username from ebb_users where Username='$u' or IP='$ip'";
	$query = $db->query();

	$iplist = '';
	while ($row = mysql_fetch_assoc ($query)){
		//get number of times the ip was used by this user.
		$db->SQL = "select author from ebb_topics where author='$row[Username]' and IP='$ip'";
		$count1 = $db->affectedRows();

		
		$db->SQL = "select re_author from ebb_posts where re_author='$row[Username]' and IP='$ip'";
		$count2 = $db->affectedRows();

		$total_count = $count1 + $count2;

		$iplist .= "$row[Username] - $total_count $lang[posts]<br />";
	}
	return $iplist;
}

/**
 * Get information regarding IP Address of user that they are tied to.
 * @version 10/5/09
 * @return string $iplist - IP Information.
*/
function other_ip_check(){

	global $ip, $u, $db;

	#topic IP check.
   	$db->SQL = "select DISTINCT IP from ebb_topics where author='$u'";
	$q = $db->query();
	$ip_ct = $db->affectedRows();

	$ipcheck = '';
	if($ip_ct > 0){
		while ($row = mysql_fetch_assoc ($q)){
			//get number of times the ip was used by this user.
			$ipcheck .= "$row[IP]<br />";
		}
	}
	#post IP check.
   	$db->SQL = "select DISTINCT IP from ebb_posts where re_author='$u'";
	$q2 = $db->query();
	$ip2_ct = $db->affectedRows();


	if($ip2_ct > 0){
		while ($row2 = mysql_fetch_assoc ($q2)){
			//get number of times the ip was used by this user.
			$ipcheck .= "$row[IP]<br />";
		}
	}
	return ($ipcheck);
}
?>