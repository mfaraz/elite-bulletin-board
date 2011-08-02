<?php
session_start();
ob_start();
define('IN_EBB', true);
/**
 * install.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 7/29/2011
*/

if(isset($_GET['do'])){
	$do = $_GET['do'];
}else{
	$do = '';
}

//only need the template engine notification class at the moment, the rest of the insaller will need more.
if (($do == "") OR ($do == "createConfig")){
	require_once "../includes/function.php";
	require_once "../includes/notifySys.php";
	require_once "../includes/templateEngine.php";
} else {
	#see if config file is already writtened.
	$file_size = filesize('../config.php');
	if($file_size == 0){
		#display validation message.
		$displayMsg = new notifySys('The config file is blank, please go to Create Configuration File.', false);
		exit($displayMsg->displayAjaxError("error"));
	}
	
	#load function and libraries.
	require_once "../config.php";
	require_once FULLPATH."/includes/templateEngine.php";
	require_once FULLPATH."/includes/MySQL.php";
	require_once FULLPATH."/includes/preference.class.php";
	require_once FULLPATH."/includes/notifySys.php";
	require_once FULLPATH."/lang/English.lang.php";
	require_once FULLPATH."/includes/function.php";
	require_once FULLPATH."/includes/user_function.php";

	//set default data.
	$style = 1;
	$boardDir = trailingSlashRemover(dirname(dirname($_SERVER["SCRIPT_NAME"])));
	$boardFolder = ltrim($boardDir, '/');

	#call up the db class.
	$db = new dbMySQL();

	#call up preference class.
	$boardPref = new preference();
}

switch ($do) {
case 'createConfig':
	#see if config file is already writtened.
	$config_path = '../config.php';
	$file_size = filesize($config_path);
	if($file_size > 0){
		#display error message.
		$displayMsg = new notifySys('The config file already contains database data.', false);
		exit($displayMsg->displayAjaxError("error"));
	}
	#get form values.
	$host = stripslashes($_POST['host']);
	$database = stripslashes($_POST['database']);
	$user = stripslashes($_POST['user']);
	$password = stripslashes($_POST['password']);
	$board_dir = stripslashes($_POST['board_dir']);

	#setup full path for config file.
	$fullPath = trailingSlashRemover($_SERVER['DOCUMENT_ROOT']).'/'.$board_dir;

	#error check.
	if((empty($host)) or (empty($database)) or (empty($user)) or (empty($password))){
		#display validation message.
		$displayMsg = new notifySys('You did not fill out the database connection correctly.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}else{
	    #create setup date.
		$creationDate = formatTime("m/d/Y", time(), 0);

		#config data.
$data = "<?php
/**
	 * config.php
	 * @package Elite Bulletin Board v3
	 * @author Elite Bulletin Board Team <http://elite-board.us>
	 * @copyright  (c) 2006-2011
	 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
	 * @version $creationDate
	 *
	 *THIS FILE WAS MADE BY EBB INSTALLER
	 *DO NOT ALTER ANYTHING IN HERE UNLESS NECESSARY!!!
	 *
*/

#Disable direct access.
if(!defined('IN_EBB')){
	die('<b>!!ACCESS DENIED HACKER!!</b>');
}

#Database Connection Settings.
define('DB_HOST', '$host'); //usually this is localhost if it isnt ask your provider
define('DB_NAME', '$database'); //Name of your Database
define('DB_USER', '$user'); //Username of Database
define('DB_PASS', '$password'); //Password of database

#full path to bulletin board. This was created during the install.
define('FULLPATH', '$fullPath');

#Installation Status.
define('EBBINSTALLED', true);
?>";
		#write the file.
		$filename = '../config.php';
		// Let's make sure the file exists and is writable first.
		if (is_writable($filename)) {
			if (!$handle = fopen($filename, 'a')){
				#display error message.
				$displayMsg = new notifySys('Cannot open file ('.$filename.')', false);
				exit($displayMsg->displayAjaxError("error"));
			}

			// Write data to config file.
			if (fwrite($handle, $data) === false){
				#display error message.
				$displayMsg = new notifySys('Cannot write to file ('.$filename.')', false);
				exit($displayMsg->displayAjaxError("error"));
			}
			
			#display success message.
			$displayMsg = new notifySys('Successfully created config file. NEXT: SQL Dump.', false);
			$displayMsg->displayAjaxError("success");

			//echo "Successfully created config file. onto <a href=\"index.php?cmd=sqldump\">step 2</a>.";
			fclose($handle);
		}else{
			#display error message.
			$displayMsg = new notifySys('Cannot write to file ('.$filename.')', false);
			exit($displayMsg->displayAjaxError("error"));
		}
	}
	break;
	case 'sqldump':

//ebb_attachment_extlist
$db->SQL = "CREATE TABLE `ebb_attachment_extlist` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ext` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_attachments
$db->SQL = "CREATE TABLE `ebb_attachments` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `Username` varchar(25) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `pid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `Filename` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `encryptedFileName` varchar(40) COLLATE utf8_bin NOT NULL,
  `File_Type` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `File_Size` int(20) NOT NULL DEFAULT '0',
  `Download_Count` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_banlist
$db->SQL = "CREATE TABLE `ebb_banlist` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ban_item` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `ban_type` varchar(5) COLLATE utf8_bin NOT NULL DEFAULT '',
  `match_type` varchar(8) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_blacklist
$db->SQL = "CREATE TABLE `ebb_blacklist` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `blacklisted_username` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `match_type` varchar(8) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_board_access
$db->SQL = "CREATE TABLE `ebb_board_access` (
  `B_Read` tinyint(1) NOT NULL DEFAULT '0',
  `B_Post` tinyint(1) NOT NULL DEFAULT '0',
  `B_Reply` tinyint(1) NOT NULL DEFAULT '0',
  `B_Vote` tinyint(1) NOT NULL DEFAULT '0',
  `B_Poll` tinyint(1) NOT NULL DEFAULT '0',
  `B_id` mediumint(8) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
$db->query();

//ebb_board
$db->SQL = "CREATE TABLE `ebb_boards` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `Board` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Description` tinytext COLLATE utf8_bin NOT NULL,
  `last_update` varchar(14) COLLATE utf8_bin DEFAULT NULL,
  `Posted_User` varchar(25) COLLATE utf8_bin DEFAULT NULL,
  `Post_Link` varchar(100) COLLATE utf8_bin DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `Category` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `Smiles` tinyint(1) NOT NULL DEFAULT '0',
  `BBcode` tinyint(1) NOT NULL DEFAULT '0',
  `Post_Increment` tinyint(1) NOT NULL DEFAULT '0',
  `Image` tinyint(1) NOT NULL DEFAULT '0',
  `B_Order` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_censor
$db->SQL = "CREATE TABLE `ebb_censor` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `Original_Word` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `action` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
$db->query();

//ebb_cplog
$db->SQL = "CREATE TABLE `ebb_cplog` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `User` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Action` text COLLATE utf8_bin NOT NULL,
  `Date` varchar(14) COLLATE utf8_bin NOT NULL DEFAULT '',
  `IP` varchar(40) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_grouplist
$db->SQL="CREATE TABLE `ebb_grouplist` (
  `group_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `board_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
$db->query();

//ebb_group_users
$db->SQL = "CREATE TABLE `ebb_group_users` (
  `Username` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `gid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `Status` varchar(7) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
$db->query();

//ebb_groups
$db->SQL = "CREATE TABLE `ebb_groups` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Description` tinytext COLLATE utf8_bin NOT NULL,
  `Enrollment` tinyint(1) NOT NULL DEFAULT '0',
  `Level` tinyint(1) NOT NULL DEFAULT '0',
  `permission_type` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_group_member_request
$db->SQL="CREATE TABLE `ebb_group_member_request` (
  `username` varchar(25) COLLATE utf8_bin NOT NULL,
  `gid` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`gid`),
  UNIQUE KEY `gid` (`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
$db->query();

//ebb_information_ticker
$db->SQL="CREATE TABLE `ebb_information_ticker` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `information` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
$db->query();

//ebb_permission_actions
$db->SQL = "CREATE TABLE `ebb_permission_actions` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `permission` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_permission_data
$db->SQL = "CREATE TABLE `ebb_permission_data` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `profile` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `permission` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `set_value` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_permission_profile
$db->SQL = "CREATE TABLE `ebb_permission_profile` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `profile` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT '',
  `access_level` tinyint(1) NOT NULL DEFAULT '0',
  `system` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_online
$db->SQL = "CREATE TABLE `ebb_online` (
  `Username` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `ip` varchar(40) COLLATE utf8_bin NOT NULL DEFAULT '',
  `time` varchar(40) COLLATE utf8_bin NOT NULL DEFAULT '',
  `location` varchar(90) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
$db->query();

//ebb_pm
$db->SQL = "CREATE TABLE `ebb_pm` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `Subject` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Sender` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Reciever` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Folder` varchar(7) COLLATE utf8_bin NOT NULL,
  `Message` text COLLATE utf8_bin NOT NULL,
  `Date` varchar(14) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Read_Status` char(3) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_poll
$db->SQL = "CREATE TABLE `ebb_poll` (
  `Poll_Option` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `tid` mediumint(8) NOT NULL,
  `option_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`option_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_posts
$db->SQL = "CREATE TABLE `ebb_posts` (
  `re_author` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `pid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `bid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `Body` text COLLATE utf8_bin NOT NULL,
  `IP` varchar(40) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Original_Date` varchar(14) COLLATE utf8_bin NOT NULL DEFAULT '',
  `disable_bbcode` tinyint(1) NOT NULL DEFAULT '0',
  `disable_smiles` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_preference
$db->SQL = "CREATE TABLE `ebb_preference` (
  `pref_name` varchar(255) COLLATE utf8_bin NOT NULL,
  `pref_value` varchar(255) COLLATE utf8_bin NOT NULL,
  `pref_type` tinyint(1) NOT NULL,
  UNIQUE KEY `pref_name` (`pref_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
$db->query();

//ebb_read_board
$db->SQL = "CREATE TABLE `ebb_read_board` (
  `Board` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `User` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
$db->query();

//ebb_read_topic
$db->SQL = "CREATE TABLE `ebb_read_topic` (
  `Topic` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `User` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
$db->query();

//ebb_relationship
$db->SQL="CREATE TABLE `ebb_relationship` (
  `rid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) unsigned NOT NULL,
  `friend` mediumint(8) unsigned NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`rid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_smiles
$db->SQL = "CREATE TABLE `ebb_smiles` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8_bin NOT NULL DEFAULT '',
  `img_name` varchar(80) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_style
$db->SQL = "CREATE TABLE `ebb_style` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Temp_Path` varchar(80) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_topic_watch
$db->SQL = "CREATE TABLE `ebb_topic_watch` (
  `username` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `status` varchar(6) COLLATE utf8_bin NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
$db->query();

//ebb_topics
$db->SQL = "CREATE TABLE `ebb_topics` (
  `author` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `tid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `bid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `Topic` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Body` text COLLATE utf8_bin NOT NULL,
  `Type` varchar(5) COLLATE utf8_bin NOT NULL DEFAULT '',
  `important` tinyint(1) NOT NULL DEFAULT '0',
  `IP` varchar(40) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Original_Date` varchar(14) COLLATE utf8_bin NOT NULL DEFAULT '',
  `last_update` varchar(14) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Posted_User` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Post_Link` varchar(100) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Locked` tinyint(1) NOT NULL DEFAULT '0',
  `Views` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `Question` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `disable_bbcode` tinyint(1) NOT NULL DEFAULT '0',
  `disable_smiles` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_users
$db->SQL = "CREATE TABLE `ebb_users` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `Username` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Password` varchar(40) COLLATE utf8_bin NOT NULL,
  `salt` varchar(8) COLLATE utf8_bin NOT NULL,
  `Email` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Custom_Title` varchar(20) COLLATE utf8_bin NOT NULL,
  `last_visit` varchar(14) COLLATE utf8_bin NOT NULL DEFAULT '',
  `PM_Notify` tinyint(1) NOT NULL DEFAULT '0',
  `Hide_Email` tinyint(1) NOT NULL DEFAULT '0',
  `MSN` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `AOL` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Yahoo` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `ICQ` varchar(15) COLLATE utf8_bin NOT NULL DEFAULT '',
  `WWW` varchar(200) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Location` varchar(70) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Avatar` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Sig` tinytext COLLATE utf8_bin NOT NULL,
  `Time_format` varchar(14) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Time_Zone` varchar(5) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Date_Joined` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `IP` varchar(40) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Style` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `Language` varchar(50) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Post_Count` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `last_post` varchar(14) COLLATE utf8_bin NOT NULL DEFAULT '',
  `last_search` varchar(14) COLLATE utf8_bin NOT NULL DEFAULT '',
  `failed_attempts` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `act_key` varchar(32) CHARACTER SET ucs2 COLLATE ucs2_bin NOT NULL DEFAULT '',
  `warning_level` tinyint(1) NOT NULL DEFAULT '0',
  `suspend_length` tinyint(1) NOT NULL DEFAULT '0',
  `suspend_time` varchar(14) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//ebb_votes
$db->SQL = "CREATE TABLE `ebb_votes` (
  `Username` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `tid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `Vote` mediumint(8) unsigned NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
$db->query();

//ebb_warnlog
$db->SQL = "CREATE TABLE `ebb_warnlog` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `Username` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Authorized` varchar(25) COLLATE utf8_bin NOT NULL DEFAULT '',
  `Action` tinyint(1) NOT NULL DEFAULT '0',
  `Message` tinytext COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1";
$db->query();

//
// ADD IN DEFAULT DATA.
//

//insert extension whitelist
$db->SQL = "INSERT INTO `ebb_attachment_extlist` (`id`, `ext`) VALUES
(1, 'gif'),
(2, 'png'),
(3, 'jpg'),
(4, 'jpeg'),
(5, 'tif'),
(6, 'tiff'),
(7, 'tga'),
(8, 'gtar'),
(9, 'zip'),
(10, 'gz'),
(11, 'tar'),
(12, 'rar'),
(13, 'ace'),
(14, 'tgz'),
(15, 'bz2'),
(16, '7z'),
(17, 'txt'),
(18, 'rtf'),
(19, 'doc'),
(20, 'csv'),
(21, 'xls'),
(22, 'cvs'),
(23, 'xlsx'),
(24, 'xlsm'),
(25, 'xlsb'),
(26, 'docx'),
(27, 'docm'),
(28, 'dot'),
(29, 'dotx'),
(30, 'dotm'),
(31, 'pdf'),
(32, 'ai'),
(33, 'psp'),
(34, 'sql'),
(35, 'psd'),
(36, 'ppt'),
(37, 'pptx'),
(38, 'pptm'),
(39, 'odg'),
(40, 'odt'),
(41, 'odp'),
(42, 'rm'),
(43, 'ram'),
(44, 'wma'),
(45, 'wmv'),
(46, 'swf'),
(47, 'mov'),
(48, 'mp3'),
(49, 'mp4'),
(50, 'qt'),
(51, 'mpg'),
(52, 'mpeg'),
(53, 'avi'),
(54, 'asf'),
(55, 'chm')";
$db->query();

#enter data to ebb_groups.
$db->SQL = "INSERT INTO `ebb_groups` (`id`, `Name`, `Description`, `Enrollment`, `Level`, `permission_type`) VALUES
(1, 'Administrator', 'These are the people who are in charge. They have full power over the board.', 0, 1, 1),
(2, 'Moderator', 'These are the people who help the administrators manage the board. They have minor power over the board.', 1, 2, 3),
(3, 'Regular Member', 'Regular Member Status.', 2, 3, 4),
(4, 'Banned User', 'Users has no rights, lowest possible rank.', 0, 0, 6)";
$db->query();

#enter data to ebb_preference.
$db->SQL = "INSERT INTO `ebb_preference` (`pref_name`, `pref_value`, `pref_type`) VALUES
('board_name', 'Board Name Here', 1),
('website_url', 'http://127.0.0.1/ebbv3', 1),
('board_url', 'http://127.0.0.1/ebbv3', 1),
('board_status', '1', 3),
('board_email', 'board@mysite.com', 1),
('board_directory', '".$boardFolder."', 1),
('offline_msg', '', 1),
('infobox_status', '1', 3),
('default_style', '1', 2),
('default_language', 'English', 1),
('rules_status', '0', 3),
('rules', '', 1),
('per_page', '20', 2),
('captcha', '1', 3),
('pm_quota', '50', 2),
('archive_quota', '10', 2),
('activation', 'None', 1),
('allow_newusers', '1', 3),
('userstat', '0', 2),
('coppa', '21', 2),
('timezone', '0', 2),
('timeformat', 'M d Y g:i a', 1),
('cookie_domain', '127.0.0.1', 1),
('cookie_path', '/ebbv3/', 1),
('cookie_secure', '0', 3),
('attachment_quota', '2048000', 2),
('allow_guest_downloads', '0', 3),
('mx_check', '0', 3),
('warning_threshold', '50', 2),
('mail_type', '1', 3),
('sendmail_path', '/usr/sbin/sendmail', 1),
('smtp_host', '', 1),
('smtp_port', '25', 2),
('smtp_user', '', 1),
('smtp_pwd', '', 1),
('smtp_encryption', '', 2),
('mail_antiflood', '30', 2),
('upload_limit', '5', 2),
('version_main', '3', 0),
('version_minor', '0', 0),
('version_patch', '0', 0),
('version_build', 'RC1', 0)";
$db->query();

//insert profile actions.
$db->SQL = "INSERT INTO `ebb_permission_actions` (`id`, `permission`, `type`) VALUES
(1, 'MANAGE_BOARDS', 1),
(2, 'PRUNE_BOARDS', 1),
(3, 'MANAGE_GROUPS', 1),
(4, 'MASS_EMAIL', 1),
(5, 'WORD_CENSOR', 1),
(6, 'MANAGE_SMILES', 1),
(7, 'MODIFY_SETTINGS', 1),
(8, 'MANAGE_STYLES', 1),
(9, 'VIEW_PHPINFO', 1),
(10, 'CHECK_UPDATES', 1),
(11, 'SEE_ACP_LOG', 1),
(12, 'CLEAR_ACP_LOG', 1),
(13, 'MANAGE_BANLIST', 1),
(14, 'MANAGE_USERS', 1),
(15, 'PRUNE_USERS', 1),
(16, 'MANAGE_BLACKLIST', 1),
(18, 'MANAGE_WARNLOG', 1),
(19, 'ACTIVATE_USERS', 1),
(20, 'EDIT_TOPICS', 2),
(21, 'DELETE_TOPICS', 2),
(22, 'LOCK_TOPICS', 2),
(23, 'MOVE_TOPICS', 2),
(24, 'VIEW_IPS', 2),
(25, 'WARN_USERS', 2),
(26, 'ATTACH_FILES', 3),
(27, 'PM_ACCESS', 3),
(28, 'SEARCH_BOARD', 3),
(29, 'DOWNLOAD_FILES', 3),
(30, 'CUSTOM_TITLES', 3),
(31, 'VIEW_PROFILE', 3),
(32, 'USE_AVATARS', 3),
(33, 'USE_SIGNATURES', 3),
(34, 'JOIN_GROUPS', 3),
(35, 'CREATE_POLL', 3),
(36, 'VOTE_POLL', 3),
(37, 'NEW_TOPIC', 3),
(38, 'REPLY', 3),
(39, 'IMPORTANT_TOPIC', 3)";
$db->query();

//insert default profile templates.
$db->SQL = "INSERT INTO `ebb_permission_profile` (`id`, `profile`, `access_level`, `system`) VALUES
(1, 'Full Administrator', 1, 1),
(2, 'Limited Administrator', 1, 1),
(3, 'Moderator', 2, 1),
(4, 'User', 3, 1),
(5, 'Limited User', 3, 1)";
$db->query();

//insert default profile permissions into db.
$db->SQL = "INSERT INTO `ebb_permission_data` (`id`, `profile`, `permission`, `set_value`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 1, 3, 1),
(4, 1, 4, 1),
(5, 1, 5, 1),
(6, 1, 6, 1),
(7, 1, 7, 1),
(8, 1, 8, 1),
(9, 1, 9, 1),
(10, 1, 10, 1),
(11, 1, 11, 1),
(12, 1, 12, 1),
(13, 1, 13, 1),
(14, 1, 14, 1),
(15, 1, 15, 1),
(16, 1, 16, 1),
(18, 1, 18, 1),
(19, 1, 19, 1),
(20, 1, 20, 1),
(21, 1, 21, 1),
(22, 1, 22, 1),
(23, 1, 23, 1),
(24, 1, 24, 1),
(25, 1, 25, 1),
(26, 1, 26, 1),
(27, 1, 27, 1),
(28, 1, 28, 1),
(29, 1, 29, 1),
(30, 1, 30, 1),
(31, 1, 31, 1),
(32, 1, 32, 1),
(33, 1, 33, 1),
(34, 1, 34, 1),
(35, 1, 35, 1),
(36, 1, 36, 1),
(37, 1, 37, 1),
(38, 1, 38, 1),
(39, 1, 39, 1),
(40, 2, 1, 1),
(41, 2, 2, 1),
(42, 2, 3, 1),
(43, 2, 4, 0),
(44, 2, 5, 1),
(45, 2, 6, 0),
(46, 2, 7, 0),
(47, 2, 8, 0),
(48, 2, 9, 0),
(49, 2, 10, 0),
(50, 2, 11, 0),
(51, 2, 12, 0),
(52, 2, 13, 1),
(53, 2, 14, 0),
(54, 2, 15, 0),
(55, 2, 16, 1),
(56, 2, 17, 1),
(57, 2, 18, 0),
(58, 2, 19, 0),
(59, 2, 20, 1),
(60, 2, 21, 1),
(61, 2, 22, 1),
(62, 2, 23, 1),
(63, 2, 24, 1),
(64, 2, 25, 1),
(65, 2, 26, 1),
(66, 2, 27, 1),
(67, 2, 28, 1),
(68, 2, 29, 1),
(69, 2, 30, 1),
(70, 2, 31, 1),
(71, 2, 32, 1),
(72, 2, 33, 1),
(73, 2, 34, 1),
(74, 2, 35, 1),
(75, 2, 36, 1),
(76, 2, 37, 1),
(77, 2, 38, 1),
(78, 2, 39, 1),
(79, 3, 20, 1),
(80, 3, 21, 1),
(81, 3, 22, 1),
(82, 3, 23, 1),
(83, 3, 24, 1),
(84, 3, 25, 1),
(85, 4, 26, 1),
(86, 4, 27, 1),
(87, 4, 28, 1),
(88, 4, 29, 1),
(89, 4, 30, 1),
(90, 4, 31, 1),
(91, 4, 32, 1),
(92, 4, 33, 1),
(93, 4, 34, 1),
(94, 4, 35, 1),
(95, 4, 36, 1),
(96, 4, 37, 1),
(97, 4, 38, 1),
(98, 4, 39, 1),
(99, 5, 26, 0),
(100, 5, 27, 1),
(101, 5, 28, 1),
(102, 5, 29, 1),
(103, 5, 30, 0),
(104, 5, 31, 0),
(105, 5, 32, 1),
(106, 5, 33, 1),
(107, 5, 34, 0),
(108, 5, 35, 1),
(109, 5, 36, 1),
(110, 5, 37, 1),
(111, 5, 38, 1),
(112, 5, 39, 0)";
$db->query();

//insert smiles
$db->SQL = "INSERT INTO `ebb_smiles` (`id`, `code`, `img_name`) VALUES
(1, ':?', 'smiley-confuse.png'),
(2, '8)', 'smiley-cool.png'),
(3, ':D', 'smiley-grin.png'),
(4, ':angry:', 'smiley-mad.png'),
(5, ':|', 'smiley-neutral.png'),
(6, ':oops:', 'smiley-red.png'),
(7, ':(', 'smiley-sad.png'),
(8, '8O', 'smiley-surprise.png'),
(9, ':)', 'smiley.png'),
(10, ':P', 'smiley-razz.png'),
(11, ';)', 'smiley-wink.png'),
(12, ':cry:', 'smiley-cry.png'),
(13, ':''(', 'smiley-cry.png'),
(14, ':eek:', 'smiley-eek.png'),
(15, ':evil:', 'smiley-evil.png'),
(16, ':kiss:', 'smiley-kiss.png'),
(17, ':lol:', 'smiley-lol.png'),
(18, ':mrgreen:', 'smiley-mr-green.png'),
(19, ':roll:', 'smiley-roll.png'),
(20, ':stressed:', 'smiley-roll-sweat.png'),
(21, ':zzz:', 'smiley-sleep.png'),
(22, ':sweat:', 'smiley-sweat.png'),
(23, ':twisted:', 'smiley-twist.png'),
(24, ':yell:', 'smiley-yell.png'),
(25, ':%', 'smiley-zipper.png'),
(26, ':halo:', 'smiley-angel.png'),
(27, ':geek:', 'smiley-nerd.png')";
$db->query();

	//insert styles
	$db->SQL = "INSERT INTO `ebb_style` (id, Name, Temp_Path) VALUES (1, 'Simple2', 'simple2')";
	$db->query();

	#display success message.
	$displayMsg = new notifySys('Database setup complete. <b>Configure Board</b>', false);
	$displayMsg->displayAjaxError("success");
break;
	//	end--added new code
case 'configureBoard':

	//get our timezone list.
	$timezone = timezone_select(0);

	//do our best to sniff the parent folder to better aid the user.
	$smartDomainDetection = parse_url($_SERVER["HTTP_HOST"]);
	$smartPathDetection = trailingSlashRemover(dirname(dirname($_SERVER["SCRIPT_NAME"]))).'/';

	#load template file.
	$tpl = new templateEngine(0, "installer-config-board", "installer");
	$tpl->parseTags(array("TIMEZONE" => "$timezone",
		"DOMAIN-DETECT" => "$smartDomainDetection[host]",
		"PATH-DETECT" => "$smartPathDetection"));

	echo $tpl->outputHtml();

  break;
  case 'saveconfig':
	//define form results.
	$board_name = $db->filterMySQL(var_cleanup($_POST['board_name']));
	$site_address = $db->filterMySQL($_POST['site_address']);
	$board_address = $db->filterMySQL($_POST['board_address']);
	$board_email = $db->filterMySQL($_POST['board_email']);
	$captcha_stat = $db->filterMySQL($_POST['captcha_stat']);
	$active_stat = $db->filterMySQL($_POST['active_stat']);
	$reg_stat = $db->filterMySQL($_POST['reg_stat']);
	$default_zone = $db->filterMySQL($_POST['time_zone']);
	$cookie_domain = $db->filterMySQL($_POST['cookie_domain']);
	$cookie_path = $db->filterMySQL($_POST['cookie_path']);
	$secure_stat = $db->filterMySQL($_POST['secure_stat']);
	$mail_type = $db->filterMySQL($_POST['mail_type']);
	$sendmail_path = $db->filterMySQL($_POST['sendmail_path']);
	$mail_antiflood = $db->filterMySQL($_POST['mail_antiflood']);
	$smtp_host = $db->filterMySQL($_POST['smtp_host']);
	$smtp_port = $db->filterMySQL($_POST['smtp_port']);
	$smtp_user = $db->filterMySQL($_POST['smtp_user']);
	$smtp_pass = $db->filterMySQL($_POST['smtp_pass']);
	$encrption_type = $db->filterMySQL($_POST['encrption_type']);
	
	//error checking.
	if (empty($board_name)){
		#display validation message.
		$displayMsg = new notifySys('Please enter a value for Board Name.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if (empty($site_address)){
		#display validation message.
		$displayMsg = new notifySys('No URL was set for your main website.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if (empty($board_address)){
		#display validation message.
		$displayMsg = new notifySys('No URL was set for your board.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if (empty($board_email)){
		#display validation message.
		$displayMsg = new notifySys('No admin email was set for this board.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if ($captcha_stat == ""){
		#display validation message.
		$displayMsg = new notifySys('You forgot to set your CAPTCHA value.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if($active_stat == ""){
		#display validation message.
		$displayMsg = new notifySys('You forgot to set ther activate type for your board.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if($reg_stat == ""){
		#display validation message.
		$displayMsg = new notifySys('You forgot to set the registration status of your board.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if ($default_zone == ""){
		#display validation message.
		$displayMsg = new notifySys('Please set your timezone for your board.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if(empty($cookie_domain)){
		#display validation message.
		$displayMsg = new notifySys('Please enter a domain for the cookie.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if (empty($cookie_path)){
		#display validation message.
		$displayMsg = new notifySys('Please enter a directory for the cookie.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if ($secure_stat == ""){
		#display validation message.
		$displayMsg = new notifySys('Please set your cookie security setting.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if ($mail_antiflood == "") {
		#display validation message.
		$displayMsg = new notifySys('Please set your mailer flood settings.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}

	if(strlen($board_name) > 50){
		#display validation message.
		$displayMsg = new notifySys('Please enter a shorter Board Name.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if(strlen($site_address) > 50){
		#display validation message.
		$displayMsg = new notifySys('the site URL is too long.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if (!preg_match('/http:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/', $site_address)) {
		#display validation message.
		$displayMsg = new notifySys('Please enter a valid site address URL.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if(strlen($board_address) > 50){
		#display validation message.
		$displayMsg = new notifySys('Please enter a shorter board URL.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if (!preg_match('/http:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/', $board_address)) {
		#display validation message.
		$displayMsg = new notifySys('Please a valid board URL.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if(strlen($board_email) > 255){
		#display validation message.
		$displayMsg = new notifySys('Please enter a shorter board email.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if(strlen($cookie_domain) > 50){
		#display validation message.
		$displayMsg = new notifySys('Please enter a shorter URL for cookies..', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if(strlen($cookie_path) > 50){
		#display validation message.
		$displayMsg = new notifySys('Please enter a shorter cookie path.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if($mail_type == ""){
		#display validation message.
		$displayMsg = new notifySys('Please select a mail method.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	#check for smtp values.
	if($mail_type == 0){
		if(empty($smtp_host)){
			#display validation message.
			$displayMsg = new notifySys('Please enter a SMTP address.', false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(empty($smtp_port)){
			#display validation message.
			$displayMsg = new notifySys('Please enter a SMTP server port.', false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(empty($smtp_user)){
			#display validation message.
			$displayMsg = new notifySys('Please set your SMTP Username.', false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(empty($smtp_pass)){
			#display validation message.
			$displayMsg = new notifySys('Please set your SMTP Password.', false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($encrption_type == "") {
			#display validation message.
			$displayMsg = new notifySys('Please set your SMTP encryption setup.', false);
			exit($displayMsg->displayAjaxError("warning"));
		}

		if(strlen($smtp_host) > 255){
			#display validation message.
			$displayMsg = new notifySys('SMTP server URL is too long.', false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($smtp_port) > 5){
			#display validation message.
			$displayMsg = new notifySys('SMTP Port too long.', false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($smtp_user) > 255){
			#display validation message.
			$displayMsg = new notifySys('SMTP Username too long.', false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($smtp_pass) > 255){
			#display validation message.
			$displayMsg = new notifySys('SMTP Password too long.', false);
			exit($displayMsg->displayAjaxError("warning"));
		}
	}else if ($mail_type == 2) {
		if ($sendmail_path == "") {
			#display validation message.
			$displayMsg = new notifySys('Please set the sendmail path.', false);
			exit($displayMsg->displayAjaxError("warning"));
		}
	}

	#save settings
	$boardPref->savePreferences("captcha", $captcha_stat);
	$boardPref->savePreferences("allow_newusers", $reg_stat);
	$boardPref->savePreferences("activation", $active_stat);
	$boardPref->savePreferences("board_name", $board_name);
	$boardPref->savePreferences("board_url", $board_address);
	$boardPref->savePreferences("board_email", $board_email);
	$boardPref->savePreferences("website_url", $site_address);
	$boardPref->savePreferences("timezone", $default_zone);
	$boardPref->savePreferences("mail_type", $mail_type);
	$boardPref->savePreferences("smtp_server", $smtp_host);
	$boardPref->savePreferences("smtp_port", $smtp_port);
	$boardPref->savePreferences("smtp_user", $smtp_user);
	$boardPref->savePreferences("smtp_pwd", $smtp_pass);
	$boardPref->savePreferences("cookie_path", $cookie_path);
	$boardPref->savePreferences("cookie_domain", $cookie_domain);
	$boardPref->savePreferences("cookie_secure", $secure_stat);
	$boardPref->savePreferences("sendmail_path", $sendmail_path);
	$boardPref->savePreferences("smtp_encryption", $encrption_type);
	$boardPref->savePreferences("mail_antiflood", $mail_antiflood);

	#display success message.
	$displayMsg = new notifySys('Settings were saved successfully! <b>Create Administrator</b>.', false);
	$displayMsg->displayAjaxError("success");
  break;
  case 'createAdministrator':
	$timezone = timezone_select(0);

	#load template file.
	$tpl = new templateEngine(0, "installer-create-admin", "installer");
	$tpl->parseTags(array("TIMEZONE" => "$timezone"));

	echo $tpl->outputHtml();

  break;
  case 'makeAdmin':
	//get values from form.
	$email = $db->filterMySQL($_POST['email']);
	$username = $db->filterMySQL($_POST['username']);
	$password = $db->filterMySQL($_POST['password']);
	$vert_password = $db->filterMySQL($_POST['vert_password']);
	$time_zone = $db->filterMySQL($_POST['time_zone']);
	$time_format = $db->filterMySQL($_POST['time_format']);
	$pm_notice = $db->filterMySQL($_POST['pm_notice']);
	$show_email = $db->filterMySQL($_POST['show_email']);
	$IP = detectProxy();

	//do some error checking
	if ($time_zone == ""){
		#display validation message.
		$displayMsg = new notifySys('Please select a timezone.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if ($time_format == ""){
		#display validation message.
		$displayMsg = new notifySys('Please select a time format.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if ($pm_notice == ""){
		#display validation message.
		$displayMsg = new notifySys('Please setup your PM Notification setting.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if($show_email == ""){
		#display validation message.
		$displayMsg = new notifySys('Please setup your email privacy setting.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if ($username == ""){
		#display validation message.
		$displayMsg = new notifySys('Please enter a username.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if ($email == ""){
		#display validation message.
		$displayMsg = new notifySys('Please enter an email.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if ($password == ""){
		#display validation message.
		$displayMsg = new notifySys('Please enter a password.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if ($vert_password == ""){
		#display validation message.
		$displayMsg = new notifySys('Please confirm your password.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if (!preg_match("/^[a-zA-Z0-9]+$/", $username)){
		#display validation message.
		$displayMsg = new notifySys('Please enter a valid username.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if ($vert_password !== $password){
		#display validation message.
		$displayMsg = new notifySys('Password mis-match.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if(strlen($username) > 25){
		#display validation message.
		$displayMsg = new notifySys('Please choose a shorter username.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if(strlen($username) < 4){
		#display validation message.
		$displayMsg = new notifySys('Please choose a longer username.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	if(strlen($email) > 255){
		#display validation message.
		$displayMsg = new notifySys('Please enter a shorter email address.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}
	
	#generate a new salt for the user's password.
	$pwdSalt = createPwdSalt();
	$pass = sha1($password.$pwdSalt);
	$time = time();

	//add user to db.
	$db->SQL = "INSERT INTO ebb_users (Email, Username, Password, salt, Date_Joined, IP, Time_format, Time_Zone, PM_Notify, Hide_Email, Style, Language, active) VALUES('$email', '$username', '$pass', '$pwdSalt', $time, '$IP', '$time_format', '$time_zone', '$pm_notice', '$show_email', '1', 'English', '1')";
	$db->query();

	$db->SQL = "INSERT INTO ebb_group_users (Username, gid, Status) values('$username', '1', 'Active')";
	$db->query();

	#display success message.
	$displayMsg = new notifySys('Administration Account Created Successfully! Next: <b>Setup Category</b>.', false);
	$displayMsg->displayAjaxError("success");
  break;
  case 'createCategory':
	#load template file.
	$tpl = new templateEngine(0, "installer-create-category", "installer");
	echo $tpl->outputHtml();
  break;
  case 'createBoard':
	require_once FULLPATH."/includes/acp/boardAdministration.class.php";

	#call board administrator class.
	$boardManager = new boardAdministration();

	$parentBoardList = $boardManager->parentBoardSelection("parent");

	#load template file.
	$tpl = new templateEngine(0, "installer-create-board", "installer");
	$tpl->parseTags(array("CATLIST" => "$parentBoardList"));

	echo $tpl->outputHtml();
break;
case 'makeBoards':
	$type = $db->filterMySQL($_GET['type']);

	#see where to go based on type variable.
	if($type == 1){
		#get form values.
		$board_name = $db->filterMySQL(var_cleanup($_POST['board_name']));
		$description = 'null';
		$readaccess = $db->filterMySQL(var_cleanup($_POST['readaccess']));
		$writeaccess = 4;
		$replyaccess = 4;
		$voteaccess = 4;
		$pollaccess = 4;
		$catsel = 0;
		$bbcode = 0;
		$increment = 0;
		$smiles = 0;
		$img = 0;
		$board_order = 1;
		$successmsg = 'Parent Board created. Next: Create Child Board.';

		#error check.
		if(empty($board_name)){
			#display validation message.
			$displayMsg = new notifySys('Please name for the board.', false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($board_name) > 50){
			#display validation message.
			$displayMsg = new notifySys('Please enter a shorter board name.', false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($readaccess == ""){
			#display validation message.
			$displayMsg = new notifySys('Please enter the access level for this board.', false);
			exit($displayMsg->displayAjaxError("warning"));
		}
	}else{
		$board_name = $db->filterMySQL(var_cleanup($_POST['board_name']));
		$description = $db->filterMySQL(var_cleanup($_POST['description']));
		$readaccess = $db->filterMySQL(var_cleanup($_POST['readaccess']));
		$writeaccess = $db->filterMySQL(var_cleanup($_POST['writeaccess']));
		$replyaccess = $db->filterMySQL(var_cleanup($_POST['replyaccess']));
		$voteaccess = $db->filterMySQL(var_cleanup($_POST['voteaccess']));
		$pollaccess = $db->filterMySQL(var_cleanup($_POST['pollaccess']));
		$catsel = $db->filterMySQL(var_cleanup($_POST['catsel']));
		$increment = $db->filterMySQL(var_cleanup($_POST['increment']));
		$bbcode = $db->filterMySQL(var_cleanup($_POST['bbcode']));
		$smiles = $db->filterMySQL(var_cleanup($_POST['smiles']));
		$img = $db->filterMySQL(var_cleanup($_POST['img']));
		$board_order = 1;
		$successmsg = 'Child Board created. Next: Complete install.';

		//do some error checking.
		if (empty($board_name)){
			#display validation message.
			$displayMsg = new notifySys($lang['boardnameerror'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if (empty($description)){
			#display validation message.
			$displayMsg = new notifySys($lang['descriptionerror'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($readaccess == ""){
			#display validation message.
			$displayMsg = new notifySys($lang['noreadsetting'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($writeaccess == ""){
			#display validation message.
			$displayMsg = new notifySys($lang['nowritesetting'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($replyaccess == ""){
			#display validation message.
			$displayMsg = new notifySys($lang['noreplysetting'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($voteaccess == ""){
			#display validation message.
			$displayMsg = new notifySys($lang['novotesetting'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($pollaccess == ""){
			#display validation message.
			$displayMsg = new notifySys($lang['nopollsetting'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if (empty($catsel)){
			#display validation message.
			$displayMsg = new notifySys($lang['categoryerror'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if($increment == ""){
			#display validation message.
			$displayMsg = new notifySys($lang['incrementerror'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($bbcode == ""){
			#display validation message.
			$displayMsg = new notifySys($lang['bbcodeerror'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($smiles == ""){
			#display validation message.
			$displayMsg = new notifySys($lang['smileserror'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if ($img == ""){
			#display validation message.
			$displayMsg = new notifySys($lang['imgerror'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($board_name) > 50){
			#display validation message.
			$displayMsg = new notifySys($lang['longboardname'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
	}
	//process the query.
	$db->SQL = "INSERT INTO ebb_boards (Board, Description, type, Category, Smiles, Post_Increment, BBcode, Image, B_Order) VALUES('$board_name', '$description', '$type', '$catsel', '$smiles', '$increment', '$bbcode', '$img', '$board_order')";
	$db->query();

	//insert the permission rules into the permission table.
	$db->SQL = "SELECT id FROM ebb_boards order by id DESC LIMIT 1";
	$r_id = $db->fetchResults();

	// process query.
	$db->SQL = "INSERT INTO ebb_board_access (B_Read, B_Post, B_Reply, B_Vote, B_Poll, B_id) VALUES('$readaccess', '$writeaccess', '$replyaccess', '$voteaccess', '$pollaccess', '$r_id[id]')";
	$db->query();

	#display success message.
	$displayMsg = new notifySys($successmsg, false);
	$displayMsg->displayAjaxError("success");
break;
case 'finalize':
	//try to remove the installer files from server.
	$delInstaller = @unlink ('install.php');
	$delUpgraderV2 = @unlink ('upgrade-v2.php');
	$delUpgraderV3 = @unlink ('upgrade-v3.php');
	$delInstallerBase = @unlink ('index.php');

	//see if we deleted everything.
	if (($delInstaller) AND ($delUpgraderV2) AND ($delUpgraderV3) AND ($delInstallerBase)){
		echo '<p class="ui-widget-content">Deleting install files...Success!</p>
						<p class="ui-widget-content">Elite Bulletin Board is now ready to use.</p>';
	}else{
		//try to chmod it programically.
		if ((chmod("install.php", 0755)) AND (chmod("upgrade-v2.php", 0755)) AND (chmod("upgrade-v3.php", 0755))){
			//try to delet again.
			$delInstaller_2 = @unlink ('install.php');
			$delUpgraderV2_2 = @unlink ('upgrade-v2.php');
			$delUpgraderV3_2 = @unlink ('upgrade-v3.php');
			$delInstallerBase_2 = @unlink ('index.php');

			//see if we deleted everything, this time.
			if (($delInstaller_2) AND ($delUpgraderV2_2) AND ($delUpgraderV3_2) AND ($delInstallerBase_2)){
				echo '<p class="ui-widget-content">Deleting install files...Success!</p>
												<p class="ui-widget-content">Elite Bulletin Board is now ready to use.</p>';
			}else {
				echo '<p class="ui-state-error">Deleting install files...<b>Failed!</b> didn\'t CHMOD folder 777 or 755.</p>
												<p class="ui-state-error">Delete installer files <b>immediately</b> to prevent someone from overwriting this install!<br /><br />
												Elite Bulletin Board is now ready to use.</p>';
			} //END deletion attempt #2.
		}else {
			echo '<p class="ui-state-error">Deleting install files...<b>Failed!</b> didn\'t CHMOD folder 777 or 755.</p>
													<p class="ui-state-error">Delete installer files <b>immediately</b> to prevent someone from overwriting this install!<br /><br />
													Elite Bulletin Board is now ready to use.</p>';
		} //END CHMOD ammept.
	} //END deletion attempt.
break;
default:
	#see if install can cont.
	$installStat = 0;

	#load template file.
	$tpl = new templateEngine(0, "installer-config-setup", "installer");

	#check install folder.
	if(!is_writable("../install")){
		//try to chmod this folder.
		if (chmod("../install", 0755)){
			#check install folder.
			if(!is_writable("../install")){
				$tpl->removeBlock("canwrite");
				$installStat = 0;
			} else {
				$tpl->removeBlock("cantwrite");
				$installStat = 1;
			}
		} else {
			$tpl->removeBlock("canwrite");
			$installStat = 0;
		}
	}else{
		$tpl->removeBlock("cantwrite");
		$installStat = 1;
	}

	#check base folder.
	if(!is_writable("../config.php")){
		$tpl->removeBlock("canwriteconfig");
		$installStat = 0;

		//try to chmod this folder.
		if (chmod("../config.php", 0755)){
			//try again.
			if(!is_writable("../config.php")){
				$tpl->removeBlock("canwriteconfig");
				$installStat = 0;
			} else {
				$tpl->removeBlock("cantwriteconfig");
				$installStat = 1;
			}
		} else {
			$tpl->removeBlock("canwriteconfig");
			$installStat = 0;
		}
	} else {
		$tpl->removeBlock("cantwriteconfig");
		$installStat = 1;
	}

	#see if user has a new enough version of php.
	if(phpversion() < "5.2"){
		$tpl->removeBlock("okphp");
	} else {
		$tpl->removeBlock("oldphp");
	}

	// use curl if it exists
	if (!function_exists('curl_init')) {
		$tpl->removeBlock("hascurl");
	}

	//see if  pspell exists.
	if (!function_exists('pspell_check')) {
		$tpl->removeBlock("haspspell");
	}

	#see if installer can continue.
	if($installStat == 0){
		$tpl->removeBlock('caninstall');
	}

	echo $tpl->outputHtml();
}
ob_end_flush();
?>
