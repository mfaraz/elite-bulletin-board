<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
	 *   user_helper.php
	 * @package Elite Bulletin Board v3
	 * @author Elite Bulletin Board Team <http://elite-board.us>
	 * @copyright  (c) 2006-2011
	 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
	 * @version 10/1/2011
*/

/**
	*Displays a list of users & guest currently online.
	*@version 10/1/11
*/
function whosonline() {

	#obtain codeigniter object.
	$ci =& get_instance();
	
	$online = '';

	$onlineLogged = $ci->db->query("SELECT DISTINCT Username FROM ebb_online WHERE ip=''");
	foreach ($onlineLogged->result() as $row) {
	    #gain status of users.
		$params[0] = $row->Username;
		$ci->load->library('grouppolicy', $params, 'groupsonline');

		if ($ci->groupsonline->groupAccessLevel() == 1){
			$online .= '<b><a href="index.php/profile/view/'.$row->Username.'">'.$row->Username.'</a></b>&nbsp;';
		}elseif ($ci->groupsonline->groupAccessLevel() == 2){
			$online .= '<i><a href="index.php/profile/view/'.$row->Username.'">'.$row->Username.'</a></i>&nbsp;';
		}elseif($ci->groupsonline->groupAccessLevel() == 3){
			$online .= '<a href="index.php/profile/view/'.$row->Username.'">'.$row->Username.'</a>&nbsp;';
		}else{
			$online .= '&nbsp;';
		}
	}
	return ($online);
}

/**
*makeRandomPassword
*
*Used to create a completely random password.
*
*@modified 7/24/11
*
*/
function makeRandomPassword() {
	$salt = "abchefghjkmnpqrstuvwxyz0123456789";
	$pass = "";
	srand((double)microtime()*1000000);
  	$i = 0;
  	while ($i <= 7) {
		$num = rand() % 33;
		$tmp = substr($salt, $num, 1);
		$pass = $pass . $tmp;
		$i++;
  	}
  	return $pass;
}

/**
*createPwdSalt
*
*Used to create a random-generated password salt.
*
*@modified 4/19/10
*
*/
function createPwdSalt() {
  $salt = "/*-+^&~abchefghjkmnpqrstuvwxyz0123456789";
  srand((double)microtime()*1000000);
  	$i = 0;
  	$pSalt = '';
  	while ($i <= 7) {
    		$num = rand() % 33;
    		$tmp = substr($salt, $num, 1);
    		$pSalt = $pSalt . $tmp;
    		$i++;
  	}
  	return ($pSalt);
}

/**
	*Used to update the whos online system.
	* @param string $user The username we want to update.
	*@version 10/1/11
*/
function update_whosonline_users($user){

	#obtain codeigniter object.
	$ci =& get_instance();

	$countUser = $ci->db->query("SELECT Username FROM ebb_online WHERE Username=?", $user);

	if ($countUser->num_rows() == 0){
		#setup values.
		$data = array(
		   'Username' => $user,
		   'time' => time(),
		   'location' => $_SERVER['PHP_SELF']);

		#add new preference.
		$ci->db->insert('ebb_online', $data);
	}else{
		//user is still here so lets up their time to let the script know the user is still around.
		$data = array(
		   'time' => time(),
		   'location' => $_SERVER['PHP_SELF']);
		
		$ci->db->where('Username', $user);
		$ci->db->update('ebb_online', $data);
	}
}

/**
	* Used to update the whos online system.
	* @version 10/1/11
*/
function update_whosonline_guest(){

	#obtain codeigniter object.
	$ci =& get_instance();
	$ip = detectProxy();

	$countGuest = $ci->db->query("SELECT Username FROM ebb_online WHERE ip=?", $ip);

	//see if we add or update online status.
	if ($countGuest->num_rows() == 0){
		#setup values.
		$data = array(
		   'ip' => $ip,
		   'time' => time(),
		   'location' => $_SERVER['PHP_SELF']);

		#add new preference.
		$ci->db->insert('ebb_online', $data);
	}else{
		//user is still here so lets up their time to let the script know the user is still around.
		$data = array(
		   'time' => time(),
		   'location' => $_SERVER['PHP_SELF']);
		
		$ci->db->where('ip', $ip);
		$ci->db->update('ebb_online', $data);
	}
}

/**
*update_user
*
*Flood check.
*
*@modified 4/19/10
*
*/
function update_user($user){

	global $db, $time;

	//update user's last post.
	$db->SQL = "Update ebb_users SET last_post='$time' WHERE Username='$user'";
	$db->query();

}

/**
*detectProxy
*
*Sniffs out any proxy and displays actual IP.
*
*@modified 4/19/10
*
*/
function detectProxy(){

	$ip_sources = array("HTTP_X_FORWARDED_FOR",
	"HTTP_X_FORWARDED",
	"HTTP_FORWARDED_FOR",
	"HTTP_FORWARDED",
	"HTTP_X_COMING_FROM",
	"HTTP_COMING_FROM",
	"REMOTE_ADDR");
	foreach ($ip_sources as $ip_source){
		// If the ip source exists, capture it
		if (isset($_SERVER[$ip_source])){
			$proxy_ip = $_SERVER[$ip_source];
		break;
		}
	}
	#if all else fails, just set a false value.
	$proxy_ip = (isset($proxy_ip)) ? $proxy_ip : $_SERVER["REMOTE_ADDR"];
	// Return the IP
	return ($proxy_ip);
}

/**
*timezone_select
*
*Used to auto-select a value already setup by the user.
*
*@modified 4/19/10
*
*/
function timezone_select($tzone){

	$timezone = '<select name="time_zone" class="text">';
	#see if any settings are set, if not set value at 0.
	if($tzone == ""){
		$tzone = 0;
	}
	#-12 GMT
	if ($tzone == "-12"){
		$timezone .= '<option value="-12" selected=selected>(GMT -12:00) Eniwetok, Kwajalein</option>';
	}else{
		$timezone .= '<option value="-12">(GMT -12:00) Eniwetok, Kwajalein</option>';
	}
	#-11 GMT
	if ($tzone == "-11"){
		$timezone .= '<option value="-11" selected=selected>(GMT -11:00) Midway Island, Samoa</option>';
	}else{
		$timezone .= '<option value="-11">(GMT -11:00) Midway Island, Samoa</option>';
	}
	#-10 GMT
	if ($tzone == "-10"){
		$timezone .= '<option value="-10" selected=selected>(GMT -10:00) Hawaii</option>';
	}else{
		$timezone .= '<option value="-10">(GMT -10:00) Hawaii</option>';
	}
	#-9 GMT
	if ($tzone == "-9"){
		$timezone .= '<option value="-9" selected=selected>(GMT -9:00) Alaska</option>';
	}else{
		$timezone .= '<option value="-9">(GMT -9:00) Alaska</option>';
	}
	#-8 GMT
	if ($tzone == "-8"){
		$timezone .= '<option value="-8" selected=selected>(GMT -8:00) Pacific Time (US &amp; Canada), Tijuana</option>';
	}else{
		$timezone .= '<option value="-8">(GMT -8:00) Pacific Time (US &amp; Canada), Tijuana</option>';
	}
	#-7 GMT
	if ($tzone == "-7"){
		$timezone .= '<option value="-7" selected=selected>(GMT -7:00) Mountain Time (US &amp; Canada), Arizona</option>';
	}else{
		$timezone .= '<option value="-7">(GMT -7:00) Mountain Time (US &amp; Canada), Arizona</option>';
	}
	#-6 GMT
	if ($tzone == "-6"){
		$timezone .= '<option value="-6" selected=selected>(GMT -6:00) Central Time (US &amp; Canada), Mexico City, Central America</option>';
	}else{
		$timezone .= '<option value="-6">(GMT -6:00) Central Time (US &amp; Canada), Mexico City, Central America</option>';
	}
	#-5 GMT
	if ($tzone == "-5"){
		$timezone .= '<option value="-5" selected=selected>(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima, Quito</option>';
	}else{
		$timezone .= '<option value="-5">(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima, Quito</option>';
	}
	#-4 GMT
	if ($tzone == "-4"){
		$timezone .= '<option value="-4" selected=selected>(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz, Santiago</option>';
	}else{
		$timezone .= '<option value="-4">(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz, Santiago</option>';
	}
	#-3.5 GMT
	if ($tzone == "-3.5"){
		$timezone .= '<option value="-3.5" selected=selected>(GMT -3:30) Newfoundland</option>';
	}else{
		$timezone .= '<option value="-3.5">(GMT -3:30) Newfoundland</option>';
	}
	#-3 GMT
	if ($tzone == "-3"){
		$timezone .= '<option value="-3" selected=selected>(GMT -3:00) Brasilia, Buenos Aires, Georgetown, Greenland</option>';
	}else{
		$timezone .= '<option value="-3">(GMT -3:00) Brasilia, Buenos Aires, Georgetown, Greenland</option>';
	}
	#-2 GMT
	if ($tzone == "-2"){
		$timezone .= '<option value="-2" selected=selected>(GMT -2:00) Mid-Atlantic, Ascension Islands, St. Helena</option>';
	}else{
		$timezone .= '<option value="-2">(GMT -2:00) Mid-Atlantic, Ascension Islands, St. Helena</option>';
	}
	#-1 GMT
	if ($tzone == "-1"){
		$timezone .= '<option value="-1" selected=selected>(GMT -1:00) Azores, Cape Verde Islands</option>';
	}else{
		$timezone .= '<option value="-1">(GMT -1:00) Azores, Cape Verde Islands</option>';
	}
	#0 GMT
	if ($tzone == "0"){
		$timezone .= '<option value="0" selected=selected>(GMT) Casablanca, Dublin, Edinburgh, Lisbon, London, Monrovia</option>';
	}else{
		$timezone .= '<option value="0">(GMT) Casablanca, Dublin, Edinburgh, Lisbon, London, Monrovia</option>';
	}
	#+1 GMT
	if ($tzone == "1"){
		$timezone .= '<option value="1" selected=selected>(GMT +1:00) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome</option>';
	}else{
		$timezone .= '<option value="1">(GMT +1:00) Amsterdam, Berlin, Brussels, Madrid, Paris, Rome</option>';
	}
	#+2 GMT
	if ($tzone == "2"){
		$timezone .= '<option value="2" selected=selected>(GMT +2:00) Cairo, Helsinki, Kaliningrad, South Africa</option>';
	}else{
		$timezone .= '<option value="2">(GMT +2:00) Cairo, Helsinki, Kaliningrad, South Africa</option>';
	}
	#+3 GMT
	if ($tzone == "3"){
		$timezone .= '<option value="3" selected=selected>(GMT +3:00) Baghdad, Riyadh, Moscow, Nairobi</option>';
	}else{
		$timezone .= '<option value="3">(GMT +3:00) Baghdad, Riyadh, Moscow, Nairobi</option>';
	}
	#+3.5 GMT
	if ($tzone == "3.5"){
		$timezone .= '<option value="3.5" selected=selected>(GMT +3:30) Tehran</option>';
	}else{
		$timezone .= '<option value="3.5">(GMT +3:30) Tehran</option>';
	}
	#+4 GMT
	if ($tzone == "4"){
		$timezone .= '<option value="4" selected=selected>(GMT +4:00) Abu Dhabi, Baku, Muscat, Tbilii</option>';
	}else{
		$timezone .= '<option value="4">(GMT +4:00) Abu Dhabi, Baku, Muscat, Tbilii</option>';
	}
	#+4.5 GMT
	if ($tzone == "4.5"){
		$timezone .= '<option value="4.5" selected=selected>(GMT +4:30) Kabul</option>';
	}else{
		$timezone .= '<option value="4.5">(GMT +4:30) Kabul</option>';
	}
	#+5 GMT
	if ($tzone == "5"){
		$timezone .= '<option value="5" selected=selected>(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>';
	}else{
		$timezone .= '<option value="5">(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>';
	}
	#+5.5 GMT
	if ($tzone == "5.5"){
		$timezone .= '<option value="5.5" selected=selected>(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>';
	}else{
		$timezone .= '<option value="5.5">(GMT +5:30) Bombay, Calcutta, Madras, New Delhi</option>';
	}
	#+5.75 GMT
	if ($tzone == "5.75"){
		$timezone .= '<option value="5.75" selected=selected>(GMT +5:45) Kathmandu</option>';
	}else{
		$timezone .= '<option value="5.75">(GMT +5:45) Kathmandu</option>';
	}
	#+6 GMT
	if ($tzone == "6"){
		$timezone .= '<option value="6" selected=selected>(GMT +6:00) Almaty, Colombo, Dhaka, Novosibirsk, Sri Jayawardenepura</option>';
	}else{
		$timezone .= '<option value="6">(GMT +6:00) Almaty, Colombo, Dhaka, Novosibirsk, Sri Jayawardenepura</option>';
	}
	#+6.5 GMT
	if ($tzone == "6.5"){
		$timezone .= '<option value="6.5" selected=selected>(GMT +6:30) Rangoon</option>';
	}else{
		$timezone .= '<option value="6.5">(GMT +6:30) Rangoon</option>';
	}
	#+7 GMT
	if ($tzone == "7"){
		$timezone .= '<option value="7" selected=selected>(GMT +7:00) Bangkok, Hanoi, Jakarta, Krasnoyarsk</option>';
	}else{
		$timezone .= '<option value="7">(GMT +7:00) Bangkok, Hanoi, Jakarta, Krasnoyarsk</option>';
	}
	#+8 GMT
	if ($tzone == "8"){
		$timezone .= '<option value="8" selected=selcted>(GMT +8:00) Beijing, Hong Kong, Perth, Singapore, Taipei</option>';
	}else{
		$timezone .= '<option value="8">(GMT +8:00) Beijing, Hong Kong, Perth, Singapore, Taipei</option>';
	}
	#+9 GMT
	if ($tzone == "9"){
		$timezone .= '<option value="9" selected=selected>(GMT +9:00) Osaka, Sapporo, Seoul, Tokyo, Yakutsk</option>';
	}else{
		$timezone .= '<option value="9">(GMT +9:00) Osaka, Sapporo, Seoul, Tokyo, Yakutsk</option>';
	}
	#+9.5 GMT
	if ($tzone == "9.5"){
		$timezone .= '<option value="9.5" selected=selected>(GMT +9:30) Adelaide, Darwin</option>';
	}else{
		$timezone .= '<option value="9.5">(GMT +9:30) Adelaide, Darwin</option>';
	}
	#+10 GMT
	if ($tzone == "10"){
		$timezone .= '<option value="10" selected=selected>(GMT +10:00) Canberra, Guam, Melbourne, Sydney, Vladivostok</option>';
	}else{
		$timezone .= '<option value="10">(GMT +10:00) Canberra, Guam, Melbourne, Sydney, Vladivostok</option>';
	}
	#+11 GMT
	if ($tzone == "11"){
		$timezone .= '<option value="11" selected=selected>(GMT +11:00) Magadan, New Caledonia, Solomon Islands</option>';
	}else{
		$timezone .= '<option value="11">(GMT +11:00) Magadan, New Caledonia, Solomon Islands</option>';
	}
	#+12 GMT
	if ($tzone == "12"){
		$timezone .= '<option value="12" selected=selected>(GMT +12:00) Auckland, Fiji, Kamchatka, Marshall Island, Wellington</option>';
	}else{
		$timezone .= '<option value="12">(GMT +12:00) Auckland, Fiji, Kamchatka, Marshall Island, Wellington</option>';
	}
	#+13 GMT
	if ($tzone == "13"){
		$timezone .= '<option value="13" selected=selected>(GMT +13:00) Nuku\' alofa</option>';
	}else{
		$timezone .= '<option value="13">(GMT +13:00) Nuku\' alofa</option>';
	}
	$timezone .= '</select>';

	return ($timezone);
}

/**
*style_select
*
*Used to auto-select a value already setup by the user.
*
*@modified 7/11/10
*
*/
function style_select($stylesel){

	global $db;

	$db->SQL = "SELECT id, Name FROM ebb_style";
	$style_query = $db->query();


	$style_select = "<select name=\"style\" class=\"text\">";
	while ($styleLst = mysql_fetch_assoc ($style_query)){
		#see what is currently selected already.
		if ($stylesel == ""){
			$style_select .= "<option value=\"$styleLst[id]\">$styleLst[Name]</option>";
		}else{
			if ($stylesel == $styleLst['id']){
				$style_select .= "<option value=\"$styleLst[id]\" selected=selected>$styleLst[Name]</option>";
			}else{
				$style_select .= "<option value=\"$styleLst[id]\">$styleLst[Name]</option>";
			}
		}
	}
	$style_select .= "</select>";

	return ($style_select);
}

/**
*lang_select
*
*Used to auto-select a value already setup by the user.
*
*@modified 5/8/10
*
*/
function lang_select($langsel){

	global $userpref;

	$lang = "<select name=\"default_lang\" class=\"text\">";
	$handle = opendir(FULLPATH."/lang");
	while (($file = readdir($handle))) {
		if (is_file(FULLPATH."/lang/$file") && false !== strpos($file, '.lang.php')) {

			$file = str_replace(".lang.php", "", $file);
			if($langsel == ""){
				$lang .= "<option value=\"$file\">$file</option>";
			}else{
				if ($langsel == $file){
					$lang .= "<option value=\"$file\" selected=selected>$file</option>";
				}else{
					$lang .= "<option value=\"$file\">$file</option>";
				}
			}
		}
	}
	$lang .= "</select>";
	return ($lang);
}
?>
