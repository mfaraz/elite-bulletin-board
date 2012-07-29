<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * user_helper.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 07/25/2012
*/

/**
 * Displays a list of users & guest currently online.
 * @return string
 * @version 05/11/12
*/
function whosonline() {

	#obtain codeigniter object.
	$ci =& get_instance();
	
	$online = '';
	
	$ci->db->select('u.id, u.Username')
	  ->from('ebb_online o')
	  ->join('ebb_users u', 'o.Username=u.id', 'LEFT')
	  ->where('o.ip IS NULL');
	$onlineLogged = $ci->db->get();
	foreach ($onlineLogged->result() as $row) {
	    #gain status of users.
		$ci->load->model('Groupmodel', 'groupsonline');
		$ci->load->model('Usermodel', 'usersonline');
		$ci->usersonline->getUser($row->id);
		$ci->groupsonline->GetGroupData($ci->usersonline->getGid());

		if ($ci->groupsonline->getLevel() == 1){
			$online .=  '<strong>'.anchor('/users/view/'.$row->id, $row->Username).'</strong>&nbsp;';
		}elseif ($ci->groupsonline->getLevel() == 2){
			$online .=  '<em>'.anchor('/users/view/'.$row->id, $row->Username).'</em>&nbsp;';
		}elseif($ci->groupsonline->getLevel() == 3){
			$online .=  anchor('/users/view/'.$row->id, $row->Username).'&nbsp;';
		}else{
			$online .= '&nbsp;';
		}
	}
	return ($online);
}

/**
 * Used to create a completely random password.
 * @return string
 * @version 7/24/11
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
 * Used to create a random-generated password salt.
 * @return string
 * @version 02/20/12
*/
function makeSalt() {
    static $seed = "./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    $algo = '$2a'; //DON'T CHANGE THIS!!
    $strength = '$12'; //too high will time out script.
    $salt = '$';
    for ($i = 0; $i < 22; $i++) {
        $salt .= substr($seed, mt_rand(0, 63), 1);
    }
    return $algo . $strength . $salt;
}

/**
 * Blowfish encrypt user's password.
 * @param string $password
 * @return string
 * @version 02/20/12
 */
function makeHash($password) {
    return crypt($password, makeSalt());
}

/**
 * Verify the hash encryption is valid.
 * @param string $password
 * @param string $hash
 * @return boolean
 * @version 02/20/12
 */
function verifyHash($password, $hash) {
    return $hash == crypt($password, $hash);
}

/**
 * Used to update the whos online system.
 * @param string $user The username we want to update.
 * @version 11/1/11
*/
function update_whosonline_users($user){

	#obtain codeigniter object.
	$ci =& get_instance();
	
	$ci->db->select('id')->from('ebb_online')->where('Username', $user);
	
	//see if we add or update online status.
	if ($ci->db->count_all_results() == 0){
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
 * @version 11/16/11
*/
function update_whosonline_guest(){

	#obtain codeigniter object.
	$ci =& get_instance();
	
	$ip = detectProxy();

	$ci->db->select('ip')->from('ebb_online')->where('ip', $ip);

	//see if we add or update online status.
	if ($ci->db->count_all_results() == 0){
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
 * Flood check.
 * @param string $user User we're updating.
 * @version 05/29/12
*/
function update_user($user){
	
	#obtain codeigniter object.
	$ci =& get_instance();

	//update user's last post.
	$data = array(
		'last_post' => time());
		
	$ci->db->where('Username', $user);
	$ci->db->update('ebb_users', $data);
}

/**
 * Sniffs out any proxy and displays actual IP.
 * @return string
 * @version 4/19/10
*/
function detectProxy(){

	$ip_sources = array(
		"HTTP_X_FORWARDED_FOR",
		"HTTP_X_FORWARDED",
		"HTTP_FORWARDED_FOR",
		"HTTP_FORWARDED",
		"HTTP_X_COMING_FROM",
		"HTTP_COMING_FROM",
		"REMOTE_ADDR"
	);
	
	#loop through array.
	foreach ($ip_sources as $ip_source){
		#If the ip source exists, capture it
		if (isset($_SERVER[$ip_source])){
			$proxy_ip = $_SERVER[$ip_source];
			break;
		}
	}
	
	#if all else fails, just set a false value.
	$proxy_ip = (isset($proxy_ip)) ? $proxy_ip : $_SERVER["REMOTE_ADDR"];

	return ($proxy_ip);
}

/**
 * Used to auto-select a value already setup by the user.
 * @param string $tzone The current time zone defined by the user.
 * @version 11/1/11
*/
function TimeZoneList($tzone){

	//array list of timezones supported by PHP.
	$TimeZones = array(		
		'Africa/Abidjan' => 'Africa/Abidjan',
		'Africa/Accra' => 'Africa/Accra',
		'Africa/Addis_Ababa' => 'Africa/Addis_Ababa',
		'Africa/Algiers' => 'Africa/Algiers',
		'Africa/Algiers' => 'Africa/Algiers',
		'Africa/Asmera' => 'Africa/Asmera',
		'Africa/Bamako' => 'Africa/Bamako',
		'Africa/Bangui' => 'Africa/Bangui',
		'Africa/Banjul' => 'Africa/Banjul',
		'Africa/Bissau' => 'Africa/Bissau',
		'Africa/Blantyre' => 'Africa/Blantyre',
		'Africa/Brazzaville' => 'Africa/Brazzaville',
		'Africa/Bujumbura' => 'Africa/Bujumbura',
		'Africa/Cairo' => 'Africa/Cairo',
		'Africa/Casablanca' => 'Africa/Casablanca',
		'Africa/Ceuta' => 'Africa/Ceuta',
		'Africa/Conakry' => 'Africa/Conakry',
		'Africa/Dakar' => 'Africa/Dakar',
		'Africa/Dar_es_Salaam' => 'Africa/Dar_es_Salaam',
		'Africa/Djibouti' => 'Africa/Djibouti',
		'Africa/Douala' => 'Africa/Douala',
		'Africa/El_Aaiun' => 'Africa/El_Aaiun',
		'Africa/Freetown' => 'Africa/Freetown',
		'Africa/Gaborone' => 'Africa/Gaborone',
		'Africa/Harare' => 'Africa/Harare',
		'Africa/Johannesburg' => 'Africa/Johannesburg',
		'Africa/Kampala' => 'Africa/Kampala',
		'Africa/Khartoum' => 'Africa/Khartoum',
		'Africa/Kigali' => 'Africa/Kigali',
		'Africa/Kinshasa' => 'Africa/Kinshasa',
		'Africa/Lagos' => 'Africa/Lagos',
		'Africa/Libreville' => 'Africa/Libreville',
		'Africa/Lome' => 'Africa/Lome',
		'Africa/Luanda' => 'Africa/Luanda',
		'Africa/Lubumbashi' => 'Africa/Lubumbashi',
		'Africa/Lusaka' => 'Africa/Lusaka',
		'Africa/Malabo' => 'Africa/Malabo',
		'Africa/Maputo' => 'Africa/Maputo',
		'Africa/Maseru' => 'Africa/Maseru',
		'Africa/Mbabane' => 'Africa/Mbabane',
		'Africa/Mogadishu' => 'Africa/Mogadishu',
		'Africa/Monrovia' => 'Africa/Monrovia',
		'Africa/Nairobi' => 'Africa/Nairobi',
		'Africa/Ndjamena' => 'Africa/Ndjamena',
		'Africa/Niamey' => 'Africa/Niamey',
		'Africa/Nouakchott' => 'Africa/Nouakchott',
		'Africa/Ouagadougou' => 'Africa/Ouagadougou',
		'Africa/Porto-Novo' => 'Africa/Porto-Novo',
		'Africa/Sao_Tome' => 'Africa/Sao_Tome',
		'Africa/Timbuktu' => 'Africa/Timbuktu',
		'Africa/Tripoli' => 'Africa/Tripoli',
		'Africa/Tunis' => 'Africa/Tunis',
		'Africa/Windhoek' => 'Africa/Windhoek',
		'America/Adak' => 'America/Adak',
		'America/Anchorage' => 'America/Anchorage', #-9 GMT
		'America/Anguilla' => 'America/Anguilla',
		'America/Antigua' => 'America/Antigua',
		'America/Araguaina' => 'America/Araguaina',
		'America/Aruba' => 'America/Aruba',
		'America/Asuncion' => 'America/Asuncion',
		'America/Atka' => 'America/Atka',
		'America/Barbados' => 'America/Barbados',
		'America/Belem' => 'America/Belem',
		'America/Belize' => 'America/Belize',
		'America/Boa_Vista' => 'America/Boa_Vista',
		'America/Bogota' => 'America/Bogota',
		'America/Boise' => 'America/Boise',
		'America/Buenos_Aires' => 'America/Buenos_Aires', #-3 GMT
		'America/Cambridge_Bay' => 'America/Cambridge_Bay',
		'America/Cancun' => 'America/Cancun',
		'America/Caracas' => 'America/Caracas', #-4.30 GMT
		'America/Catamarca' => 'America/Catamarca',
		'America/Cayenne' => 'America/Cayenne',
		'America/Cayman' => 'America/Cayman',
		'America/Chicago' => 'America/Chicago',
		'America/Chihuahua' => 'America/Chihuahua',
		'America/Cordoba' => 'America/Cordoba',
		'America/Costa_Rica' => 'America/Costa_Rica',
		'America/Cuiaba' => 'America/Cuiaba',
		'America/Curacao' => 'America/Curacao',
		'America/Danmarkshavn' => 'America/Danmarkshavn',
		'America/Dawson' => 'America/Dawson',
		'America/Dawson_Creek' => 'America/Dawson_Creek',
		'America/Denver' => 'America/Denver', #-7 GMT
		'America/Detroit' => 'America/Detroit',
		'America/Dominica' => 'America/Dominica',
		'America/Edmonton' => 'America/Edmonton',
		'America/Eirunepe' => 'America/Eirunepe',
		'America/El_Salvador' => 'America/El_Salvador',
		'America/Ensenada' => 'America/Ensenada',
		'America/Fort_Wayne' => 'America/Fort_Wayne',
		'America/Fortaleza' => 'America/Fortaleza',
		'America/Glace_Bay' => 'America/Glace_Bay',
		'America/Godthab' => 'America/Godthab',
		'America/Goose_Bay' => 'America/Goose_Bay',
		'America/Grand_Turk' => 'America/Grand_Turk',
		'America/Grenada' => 'America/Grenada',
		'America/Guadeloupe' => 'America/Guadeloupe',
		'America/Guatemala' => 'America/Guatemala',
		'America/Guayaquil' => 'America/Guayaquil',
		'America/Guyana' => 'America/Guyana',
		'America/Halifax' => 'America/Halifax', #-4 GMT
		'America/Havana' => 'America/Havana',
		'America/Hermosillo' => 'America/Hermosillo',
		'America/Indiana/Indianapolis' => 'America/Indiana/Indianapolis',
		'America/Indiana/Knox' => 'America/Indiana/Knox',
		'America/Indiana/Marengo' => 'America/Indiana/Marengo',
		'America/Indiana/Vevay' => 'America/Indiana/Vevay',
		'America/Indianapolis' => 'America/Indianapolis',
		'America/Inuvik' => 'America/Inuvik',
		'America/Iqaluit' => 'America/Iqaluit',
		'America/Jamaica' => 'America/Jamaica',
		'America/Jujuy' => 'America/Jujuy',
		'America/Juneau' => 'America/Juneau',
		'America/Kentucky/Louisville' => 'America/Kentucky/Louisville',
		'America/Kentucky/Monticello' => 'America/Kentucky/Monticello',
		'America/Knox_IN' => 'America/Knox_IN',
		'America/La_Paz' => 'America/La_Paz',
		'America/Lima' => 'America/Lima',
		'America/Los_Angeles' => 'America/Los_Angeles', #-8 GMT
		'America/Louisville' => 'America/Louisville',
		'America/Maceio' => 'America/Maceio',
		'America/Managua' => 'America/Managua',
		'America/Manaus' => 'America/Manaus',
		'America/Martinique' => 'America/Martinique',
		'America/Mazatlan' => 'America/Mazatlan',
		'America/Mendoza' => 'America/Mendoza',
		'America/Menominee' => 'America/Menominee',
		'America/Merida' => 'America/Merida',
		'America/Mexico_City' => 'America/Mexico_City',
		'America/Miquelon' => 'America/Miquelon',
		'America/Monterrey' => 'America/Monterrey',
		'America/Montevideo' => 'America/Montevideo',
		'America/Montreal' => 'America/Montreal',
		'America/Montserrat' => 'America/Montserrat',
		'America/Nassau' => 'America/Nassau',
		'America/New_York' => 'America/New_York', #-5 GMT
		'America/Nipigon' => 'America/Nipigon',
		'America/Nome' => 'America/Nome',
		'America/Noronha' => 'America/Noronha',
		'America/North_Dakota/Center' => 'America/North_Dakota/Center',
		'America/Panama' => 'America/Panama',
		'America/Pangnirtung' => 'America/Pangnirtung',
		'America/Paramaribo' => 'America/Paramaribo',
		'America/Phoenix' => 'America/Phoenix',
		'America/Port-au-Prince' => 'America/Port-au-Prince',
		'America/Port_of_Spain' => 'America/Port_of_Spain',
		'America/Porto_Acre' => 'America/Porto_Acre',
		'America/Porto_Velho' => 'America/Porto_Velho',
		'America/Puerto_Rico' => 'America/Puerto_Rico',
		'America/Rainy_River' => 'America/Rainy_River',
		'America/Rankin_Inlet' => 'America/Rankin_Inlet',
		'America/Recife' => 'America/Recife',
		'America/Regina' => 'America/Regina',
		'America/Rio_Branco' => 'America/Rio_Branco',
		'America/Rosario' => 'America/Rosario',
		'America/Santiago' => 'America/Santiago',
		'America/Santo_Domingo' => 'America/Santo_Domingo',
		'America/Sao_Paulo' => 'America/Sao_Paulo',
		'America/Scoresbysund' => 'America/Scoresbysund',
		'America/Shiprock' => 'America/Shiprock',
		'America/St_Johns' => 'America/St_Johns', #-3.30 GMT
		'America/St_Kitts' => 'America/St_Kitts',
		'America/St_Lucia' => 'America/St_Lucia',
		'America/St_Thomas' => 'America/St_Thomas',
		'America/St_Vincent' => 'America/St_Vincent',
		'America/Swift_Current' => 'America/Swift_Current',
		'America/Tegucigalpa' => 'America/Tegucigalpa', #-6 GMT
		'America/Thule' => 'America/Thule',
		'America/Thunder_Bay' => 'America/Thunder_Bay',
		'America/Tijuana' => 'America/Tijuana',
		'America/Tortola' => 'America/Tortola',
		'America/Vancouver' => 'America/Vancouver',
		'America/Virgin' => 'America/Virgin',
		'America/Whitehorse' => 'America/Whitehorse',
		'America/Winnipeg' => 'America/Winnipeg',
		'America/Yakutat' => 'America/Yakutat',
		'America/Yellowknife' => 'America/Yellowknife',
		'Antarctica/Casey' => 'Antarctica/Casey',
		'Antarctica/Davis' => 'Antarctica/Davis',
		'Antarctica/DumontDUrville' => 'Antarctica/DumontDUrville',
		'Antarctica/Mawson' => 'Antarctica/Mawson',
		'Antarctica/McMurdo' => 'Antarctica/McMurdo',
		'Antarctica/Palmer' => 'Antarctica/Palmer',
		'Antarctica/South_Pole' => 'Antarctica/South_Pole',
		'Antarctica/Syowa' => 'Antarctica/Syowa',
		'Antarctica/Vostok' => 'Antarctica/Vostok',
		'Asia/Aden' => 'Asia/Aden',
		'Asia/Almaty' => 'Asia/Almaty',
		'Asia/Amman' => 'Asia/Amman',
		'Asia/Anadyr' => 'Asia/Anadyr',
		'Asia/Aqtau' => 'Asia/Aqtau',
		'Asia/Aqtobe' => 'Asia/Aqtobe',
		'Asia/Ashgabat' => 'Asia/Ashgabat',
		'Asia/Ashkhabad' => 'Asia/Ashkhabad',
		'Asia/Baghdad' => 'Asia/Baghdad',
		'Asia/Bahrain' => 'Asia/Bahrain',
		'Asia/Baku' => 'Asia/Baku',
		'Asia/Bangkok' => 'Asia/Bangkok',
		'Asia/Beirut' => 'Asia/Beirut',
		'Asia/Bishkek' => 'Asia/Bishkek',
		'Asia/Brunei' => 'Asia/Brunei', #+8 GMT
		'Asia/Calcutta' => 'Asia/Calcutta', #+5.5 GMT
		'Asia/Choibalsan' => 'Asia/Choibalsan',
		'Asia/Chongqing' => 'Asia/Chongqing',
		'Asia/Chungking' => 'Asia/Chungking',
		'Asia/Colombo' => 'Asia/Colombo',
		'Asia/Dacca' => 'Asia/Dacca',
		'Asia/Damascus' => 'Asia/Damascus',
		'Asia/Dhaka' => 'Asia/Dhaka', #+6 GMT
		'Asia/Dili' => 'Asia/Dili',
		'Asia/Dubai' => 'Asia/Dubai',
		'Asia/Dushanbe' => 'Asia/Dushanbe',
		'Asia/Gaza' => 'Asia/Gaza',
		'Asia/Harbin' => 'Asia/Harbin',
		'Asia/Hong_Kong' => 'Asia/Hong_Kong',
		'Asia/Hovd' => 'Asia/Hovd',
		'Asia/Irkutsk' => 'Asia/Irkutsk',
		'Asia/Ishigaki' => 'Asia/Ishigaki',
		'Asia/Istanbul' => 'Asia/Istanbul',
		'Asia/Jakarta' => 'Asia/Jakarta',
		'Asia/Jayapura' => 'Asia/Jayapura',
		'Asia/Jerusalem' => 'Asia/Jerusalem',
		'Asia/Kabul' => 'Asia/Kabul',
		'Asia/Kamchatka' => 'Asia/Kamchatka',
		'Asia/Karachi' => 'Asia/Karachi',
		'Asia/Kashgar' => 'Asia/Kashgar',
		'Asia/Katmandu' => 'Asia/Katmandu', #+5.45 GMT
		'Asia/Krasnoyarsk' => 'Asia/Krasnoyarsk', #+7 GMT
		'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur',
		'Asia/Kuching' => 'Asia/Kuching',
		'Asia/Kuwait' => 'Asia/Kuwait', #+3 GMT
		'Asia/Macao' => 'Asia/Macao',
		'Asia/Macau' => 'Asia/Macau',
		'Asia/Magadan' => 'Asia/Magadan', #+11 GMT
		'Asia/Manila' => 'Asia/Manila',
		'Asia/Muscat' => 'Asia/Muscat', #+4 GMT
		'Asia/Nicosia' => 'Asia/Nicosia',
		'Asia/Novosibirsk' => 'Asia/Novosibirsk',
		'Asia/Omsk' => 'Asia/Omsk',
		'Asia/Oral' => 'Asia/Oral',
		'Asia/Phnom_Penh' => 'Asia/Phnom_Penh',
		'Asia/Pontianak' => 'Asia/Pontianak',
		'Asia/Pyongyang' => 'Asia/Pyongyang',
		'Asia/Qatar' => 'Asia/Qatar',
		'Asia/Qyzylorda' => 'Asia/Qyzylorda',
		'Asia/Rangoon' => 'Asia/Rangoon', #+6.30 GMT
		'Asia/Riyadh' => 'Asia/Riyadh',
		'Asia/Riyadh87' => 'Asia/Riyadh87',
		'Asia/Riyadh88' => 'Asia/Riyadh88',
		'Asia/Riyadh89' => 'Asia/Riyadh89',
		'Asia/Saigon' => 'Asia/Saigon',
		'Asia/Sakhalin' => 'Asia/Sakhalin',
		'Asia/Samarkand' => 'Asia/Samarkand',
		'Asia/Seoul' => 'Asia/Seoul', #+9 GMT
		'Asia/Shanghai' => 'Asia/Shanghai',
		'Asia/Singapore' => 'Asia/Singapore',
		'Asia/Taipei' => 'Asia/Taipei',
		'Asia/Tashkent' => 'Asia/Tashkent',
		'Asia/Tbilisi' => 'Asia/Tbilisi',
		'Asia/Tehran' => 'Asia/Tehran', #+3.30 GMT
		'Asia/Tel_Aviv' => 'Asia/Tel_Aviv',
		'Asia/Thimbu' => 'Asia/Thimbu',
		'Asia/Thimphu' => 'Asia/Thimphu',
		'Asia/Tokyo' => 'Asia/Tokyo',
		'Asia/Ujung_Pandang' => 'Asia/Ujung_Pandang',
		'Asia/Ulaanbaatar' => 'Asia/Ulaanbaatar',
		'Asia/Ulan_Bator' => 'Asia/Ulan_Bator',
		'Asia/Urumqi' => 'Asia/Urumqi',
		'Asia/Vientiane' => 'Asia/Vientiane',
		'Asia/Vladivostok' => 'Asia/Vladivostok',
		'Asia/Yakutsk' => 'Asia/Yakutsk',
		'Asia/Yekaterinburg' => 'Asia/Yekaterinburg', #+5 GMT
		'Asia/Yerevan' => 'Asia/Yerevan',
		'Atlantic/Azores' => 'Atlantic/Azores', #-1 GMT
		'Atlantic/Bermuda' => 'Atlantic/Bermuda',
		'Atlantic/Canary' => 'Atlantic/Canary',
		'Atlantic/Cape_Verde' => 'Atlantic/Cape_Verde',
		'Atlantic/Faeroe=' => 'Atlantic/Faeroe=',
		'Atlantic/Jan_Mayen' => 'Atlantic/Jan_Mayen',
		'Atlantic/Madeira' => 'Atlantic/Madeira',
		'Atlantic/Reykjavik' => 'Atlantic/Reykjavik',
		'Atlantic/South_Georgia' => 'Atlantic/South_Georgia', #-2 GMT
		'Atlantic/St_Helena' => 'Atlantic/St_Helena',
		'Atlantic/Stanley' => 'Atlantic/Stanley',
		'Australia/ACT' => 'Australia/ACT',
		'Australia/Adelaide' => 'Australia/Adelaide',
		'Australia/Brisbane' => 'Australia/Brisbane',
		'Australia/Broken_Hill' => 'Australia/Broken_Hill',
		'Australia/Canberra' => 'Australia/Canberra', #+10 GMT
		'Australia/Darwin' => 'Australia/Darwin', #+9.30 GMT
		'Australia/Hobart' => 'Australia/Hobart',
		'Australia/LHI' => 'Australia/LHI',
		'Australia/Lindeman' => 'Australia/Lindeman',
		'Australia/Lord_Howe' => 'Australia/Lord_Howe',
		'Australia/Melbourne' => 'Australia/Melbourne',
		'Australia/NSW' => 'Australia/NSW',
		'Australia/North' => 'Australia/North',
		'Australia/Perth' => 'Australia/Perth',
		'Australia/Queensland' => 'Australia/Queensland',
		'Australia/South' => 'Australia/South',
		'Australia/Sydney' => 'Australia/Sydney',
		'Australia/Tasmania' => 'Australia/Tasmania',
		'Australia/Victoria' => 'Australia/Victoria',
		'Australia/West' => 'Australia/West',
		'Australia/Yancowinna' => 'Australia/Yancowinna',
		'China/Beijing' => 'China/Beijing',
		'China/Shanghai' => 'China/Shanghai',
		'Europe/Amsterdam' => 'Europe/Amsterdam',
		'Europe/Andorra' => 'Europe/Andorra',
		'Europe/Athens' => 'Europe/Athens',
		'Europe/Belfast' => 'Europe/Belfast',
		'Europe/Belgrade' => 'Europe/Belgrade', #+1 GMT
		'Europe/Berlin' => 'Europe/Berlin',
		'Europe/Bratislava' => 'Europe/Bratislava',
		'Europe/Brussels' => 'Europe/Brussels',
		'Europe/Bucharest' => 'Europe/Bucharest',
		'Europe/Budapest' => 'Europe/Budapest',
		'Europe/Chisinau' => 'Europe/Chisinau',
		'Europe/Copenhagen' => 'Europe/Copenhagen',
		'Europe/Dublin' => 'Europe/Dublin', #0 GMT
		'Europe/Gibraltar' => 'Europe/Gibraltar',
		'Europe/Helsinki' => 'Europe/Helsinki',
		'Europe/Istanbul' => 'Europe/Istanbul',
		'Europe/Kaliningrad' => 'Europe/Kaliningrad',
		'Europe/Kiev' => 'Europe/Kiev',
		'Europe/Lisbon' => 'Europe/Lisbon',
		'Europe/Ljubljana' => 'Europe/Ljubljana',
		'Europe/London' => 'Europe/London',
		'Europe/Luxembourg' => 'Europe/Luxembourg',
		'Europe/Madrid' => 'Europe/Madrid',
		'Europe/Malta' => 'Europe/Malta',
		'Europe/Minsk' => 'Europe/Minsk', #+2 GMT
		'Europe/Monaco' => 'Europe/Monaco',
		'Europe/Moscow' => 'Europe/Moscow',
		'Europe/Nicosia' => 'Europe/Nicosia',
		'Europe/Oslo' => 'Europe/Oslo',
		'Europe/Paris' => 'Europe/Paris',
		'Europe/Prague' => 'Europe/Prague',
		'Europe/Riga' => 'Europe/Riga',
		'Europe/Rome' => 'Europe/Rome',
		'Europe/Samara' => 'Europe/Samara',
		'Europe/San_Marino' => 'Europe/San_Marino',
		'Europe/Sarajevo' => 'Europe/Sarajevo',
		'Europe/Simferopol' => 'Europe/Simferopol',
		'Europe/Skopje' => 'Europe/Skopje',
		'Europe/Sofia' => 'Europe/Sofia',
		'Europe/Stockholm' => 'Europe/Stockholm',
		'Europe/Tallinn' => 'Europe/Tallinn',
		'Europe/Tirane' => 'Europe/Tirane',
		'Europe/Tiraspol' => 'Europe/Tiraspol',
		'Europe/Uzhgorod' => 'Europe/Uzhgorod',
		'Europe/Vaduz' => 'Europe/Vaduz',
		'Europe/Vatican' => 'Europe/Vatican',
		'Europe/Vienna' => 'Europe/Vienna',
		'Europe/Vilnius' => 'Europe/Vilnius',
		'Europe/Warsaw' => 'Europe/Warsaw',
		'Europe/Zagreb' => 'Europe/Zagreb',
		'Europe/Zaporozhye' => 'Europe/Zaporozhye',
		'Europe/Zurich' => 'Europe/Zurich',
		'Factory' => 'Factory',
		'Indian/Antananarivo' => 'Indian/Antananarivo',
		'Indian/Chagos' => 'Indian/Chagos',
		'Indian/Christmas' => 'Indian/Christmas',
		'Indian/Cocos' => 'Indian/Cocos',
		'Indian/Comoro' => 'Indian/Comoro',
		'Indian/Kerguelen' => 'Indian/Kerguelen',
		'Indian/Mahe' => 'Indian/Mahe',
		'Indian/Maldives' => 'Indian/Maldives',
		'Indian/Mauritius' => 'Indian/Mauritius',
		'Indian/Mayotte' => 'Indian/Mayotte',
		'Indian/Reunion' => 'Indian/Reunion',
		'Pacific/Apia' => 'Pacific/Apia',
		'Pacific/Auckland' => 'Pacific/Auckland',
		'Pacific/Chatham' => 'Pacific/Chatham',
		'Pacific/Easter' => 'Pacific/Easter',
		'Pacific/Efate' => 'Pacific/Efate',
		'Pacific/Enderbury' => 'Pacific/Enderbury',
		'Pacific/Fakaofo' => 'Pacific/Fakaofo',
		'Pacific/Fiji' => 'Pacific/Fiji', #+12 GMT
		'Pacific/Funafuti' => 'Pacific/Funafuti',
		'Pacific/Galapagos' => 'Pacific/Galapagos',
		'Pacific/Gambier' => 'Pacific/Gambier',
		'Pacific/Guadalcanal' => 'Pacific/Guadalcanal',
		'Pacific/Guam' => 'Pacific/Guam',
		'Pacific/Honolulu' => 'Pacific/Honolulu', #-10 GMT
		'Pacific/Johnston' => 'Pacific/Johnston',
		'Pacific/Kiritimati' => 'Pacific/Kiritimati',
		'Pacific/Kosrae' => 'Pacific/Kosrae',
		'Pacific/Kwajalein' => 'Pacific/Kwajalein',
		'Pacific/Majuro' => 'Pacific/Majuro',
		'Pacific/Marquesas' => 'Pacific/Marquesas',
		'Pacific/Midway' => 'Pacific/Midway',
		'Pacific/Nauru' => 'Pacific/Nauru', #-11 GMT
		'Pacific/Niue' => 'Pacific/Niue',
		'Pacific/Norfolk' => 'Pacific/Norfolk',
		'Pacific/Noumea' => 'Pacific/Noumea',
		'Pacific/Pago_Pago' => 'Pacific/Pago_Pago',
		'Pacific/Palau' => 'Pacific/Palau',
		'Pacific/Pitcairn' => 'Pacific/Pitcairn',
		'Pacific/Ponape' => 'Pacific/Ponape',
		'Pacific/Port_Moresby' => 'Pacific/Port_Moresby',
		'Pacific/Rarotonga' => 'Pacific/Rarotonga',
		'Pacific/Saipan' => 'Pacific/Saipan',
		'Pacific/Samoa' => 'Pacific/Samoa',
		'Pacific/Tahiti' => 'Pacific/Tahiti',
		'Pacific/Tarawa' => 'Pacific/Tarawa',
		'Pacific/Tongatapu' => 'Pacific/Tongatapu', #+13 GMT
		'Pacific/Truk' => 'Pacific/Truk',
		'Pacific/Wake' => 'Pacific/Wake',
		'Pacific/Wallis' => 'Pacific/Wallis',
		'Pacific/Yap' => 'Pacific/Yap',
		'UTC' => 'UTC'
	);
	
	//setup form based on user's selection.
	return form_dropdown('time_zone', $TimeZones, $tzone, 'class="text"');
}

/**
 * Used to auto-select a value already setup by the user.
 * @param string $theme The current theme defined by the user.
 * @version 11/1/11
*/
function ThemeList($theme){

	#obtain codeigniter object.
	$ci =& get_instance();

	//our assocative array.
	$StyleArray = array();

	//SQL query.
	$ci->db->select('id, Name')->from('ebb_style');
	$styleList = $ci->db->get();
	
	//build an array list for drop-down.
	foreach($styleList->result_array() as $value) {
		$StyleArray[$value['id']] = $value['Name'];
	}
	
	//setup form based on user's selection.
	return form_dropdown('style', $StyleArray, $theme, 'class="text"');
}

/**
 * Used to auto-select a value already setup by the user.
 * @param string $language The current language defined by the user.
 * @version 11/1/11
*/
function LanguageList($language){

	#obtain codeigniter object.
	$ci =& get_instance();
	
	//our assocative array.
	$AssocArray = array();

	//load directory helper.
	$ci->load->helper('directory');
	    
	//get a list of language folders in /langauge/ directory.
	$languages = directory_map('./application/language/', 1);
	
	//go through array list and convert it to an assocative array.
	foreach($languages as $key => $value) {
		$AssocArray[$value] = $value;
	}
	
	//setup form based on user's selection.
	return form_dropdown('language', $AssocArray, $language, 'class="text"');
}

/**
 * Subscribe or unsubscribe from a topic.
 * @param string $user the user we want to this to affect.
 * @param integer $tid the Topic ID we want this to affect.
 * @param string $mode are we subscribing or unsubscribing?
 * @version 06/06/12
 */
function subscriptionManager($user, $tid, $mode) {

	#obtain codeigniter object.
	$ci =& get_instance();
	
	//get a check to see if they are a part the topic defined.
	$ci->db->select('tid')
	  ->from('ebb_topic_watch')
	  ->where('username', $user);
	$q = $ci->db->get();
	
	//see if they want to subscribe or unsubscribe to a topic.
	if ($mode == "subscribe" && $q->num_rows() == 0) {
		$data = array(
		  "username" => $user,
		  "tid" => $tid,
		  "read_status" => 0
		);
		$ci->db->insert('ebb_topic_watch', $data);		
	} elseif ($mode == "unsubscribe" && $query->num_rows() > 0) {
		$ci->db->where('tid', $tid);
		$ci->db->where('username', $user);
		$ci->db->delete('ebb_topic_watch');
	}
}

/**
 * Checks to see if the user is banned or suspended.
 * @version 05/10/12
*/
function checkBan(){

	#obtain codeigniter object.
	$ci =& get_instance();

	#see if user is suspended.
	if($ci->suspend_length > 0){
		#see if user is still suspended.
		$math = 3600 * $ci->suspend_length;
		$suspend_date = $ci->suspend_time + $math;
		$today = time() - $math;

		if($suspend_date > $today){
			exit(show_error($ci->lang->line('suspended')));
		}
	}
	#see if the IP of the user is banned.
	$uip = detectProxy();

	$ci->db->distinct('ban_item')->from('ebb_banlist')->where('ban_type', 'IP')->like('ban_item', $uip)->limit(1);
	$banChk = $ci->db->count_all_results();

	#output an error msg.
	if($banChk == 1){
		exit(show_error($ci->lang->line('banned')));
	}
}