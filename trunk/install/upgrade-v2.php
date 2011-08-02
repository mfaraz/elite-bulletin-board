<?php
session_start();
ob_start();
define('IN_EBB', true);
/**
 * upgrade-v2.php
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
	#load function and libraries.
	require_once "../config.php";
	require_once FULLPATH."/includes/templateEngine.php";
	require_once FULLPATH."/includes/MySQL.php";
	require_once FULLPATH."/includes/preference.class.php";
	require_once FULLPATH."/includes/notifySys.php";
	require_once FULLPATH."/lang/English.lang.php";
	require_once FULLPATH."/includes/function.php";
	require_once FULLPATH."/includes/user_function.php";

	#see if config file is already writtened.
	$file_size = filesize('../config.php');
	if($file_size == 0){
		#display validation message.
		$displayMsg = new notifySys('The config file is blank, please go to Create Configuration File.', false);
		exit($displayMsg->displayAjaxError("error"));
	}

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
		
	//
	// CONVERT TABLES TO UTF-8
	//

	//ebb_attachment_extlist
	$db->SQL="ALTER TABLE ebb_attachment_extlist CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_attachments
	$db->SQL="ALTER TABLE ebb_attachments CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	$db->SQL="ALTER TABLE `ebb_attachments` ADD encryptedFileName varchar(40) COLLATE utf8_bin NOT NULL";
	$db->query();

	#get old data for conversion.
	$db->SQL="SELECT Filename, id FROM `ebb_attachments`";
	$attachCt = $db->affectedRows();
	$attachQ = $db->query();

	//do this only if we have attachment records to convert.
	//NOTE this will truncate, which may fail some downloads. consider hashing the current attachments.
	if ($attachCt > 0) {
		while($r = mysql_fetch_assoc($attachQ)){
			$db->SQL="UPDATE `ebb_attachments` SET encryptedFileName='$r[Filename]' WHERE id='$r[id]'";
			$db->query();
		}
	}

	//ebb_banlist
	$db->SQL="ALTER TABLE ebb_banlist CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_blacklist
	$db->SQL="ALTER TABLE ebb_blacklist CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_board_access
	$db->SQL="ALTER TABLE ebb_board_access CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	$db->SQL="ALTER TABLE `ebb_board_access`
					  DROP `B_Delete`,
					  DROP `B_Edit`,
					  DROP `B_Important`,
					  DROP `B_Attachment`";
	$db->query();

	//ebb_board
	$db->SQL="ALTER TABLE ebb_boards CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_censor
	$db->SQL="ALTER TABLE ebb_censor CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_cplog
	$db->SQL="ALTER TABLE ebb_cplog CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_grouplist
	$db->SQL="ALTER TABLE ebb_grouplist CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_group_users
	$db->SQL="ALTER TABLE ebb_group_users CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_groups
	$db->SQL="ALTER TABLE ebb_groups CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	$db->SQL="TRUNCATE TABLE `ebb_groups`";
	$db->query();

	//ebb_permission_actions
	$db->SQL="ALTER TABLE ebb_permission_actions CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_permission_data
	$db->SQL="ALTER TABLE ebb_permission_data CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_permission_profile
	$db->SQL="ALTER TABLE ebb_permission_profile CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_online
	$db->SQL="ALTER TABLE ebb_online CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_pm
	$db->SQL="ALTER TABLE ebb_pm CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_poll
	$db->SQL="ALTER TABLE ebb_poll CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_posts
	$db->SQL="ALTER TABLE ebb_posts CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_read_board
	$db->SQL="ALTER TABLE ebb_read_board CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_read_topic
	$db->SQL="ALTER TABLE ebb_read_topic CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_smiles
	$db->SQL="ALTER TABLE ebb_smiles CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();
	
	$db->SQL="TRUNCATE TABLE `ebb_smiles`";
	$db->query();

	//ebb_style
	$db->SQL="ALTER TABLE ebb_style CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	$db->SQL="TRUNCATE TABLE `ebb_style`";
	$db->query();

	//ebb_topic_watch
	$db->SQL="ALTER TABLE ebb_topic_watch CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_topics
	$db->SQL="ALTER TABLE ebb_topics CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_users
	$db->SQL="ALTER TABLE ebb_users CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	$db->SQL="ALTER TABLE `ebb_users`
					  DROP `rssfeed1`,
					  DROP `rssfeed2`,
					  DROP `banfeeds`,
					  DROP `Status`,
					  CHANGE `Password` `Password` VARCHAR( 40 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
					  ADD `salt` VARCHAR( 8 ) NOT NULL AFTER `Password`";
	$db->query();

	//ebb_votes
	$db->SQL="ALTER TABLE ebb_votes CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//ebb_warnlog
	$db->SQL="ALTER TABLE ebb_warnlog CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci";
	$db->query();

	//
	// CREATE NEW TABLES
	//

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

	//ebb_preference
	$db->SQL = "CREATE TABLE `ebb_preference` (
	  `pref_name` varchar(255) COLLATE utf8_bin NOT NULL,
	  `pref_value` varchar(255) COLLATE utf8_bin NOT NULL,
	  `pref_type` tinyint(1) NOT NULL,
	  UNIQUE KEY `pref_name` (`pref_name`)
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

	//
	// ADD IN DEFAULT DATA.
	//

	#enter data to ebb_groups.
	$db->SQL = "INSERT INTO `ebb_groups` (`id`, `Name`, `Description`, `Enrollment`, `Level`, `permission_type`) VALUES
	(1, 'Administrator', 'These are the people who are in charge. They have full power over the board.', 0, 1, 1),
	(2, 'Moderator', 'These are the people who help the administrators manage the board. They have minor power over the board.', 1, 2, 3),
	(3, 'Regular Member', 'Regular Member Status.', 2, 3, 4),
	(4, 'Banned User', 'Users has no rights, lowest possible rank.', 0, 0, 6)";
	$db->query();

	$db->SQL="SELECT * FROM `ebb_settings`";
	$oldSettings = $db->fetchResults();

	#enter data to ebb_preference.
	$db->SQL = "INSERT INTO `ebb_preference` (`pref_name`, `pref_value`, `pref_type`) VALUES
	('board_name', '".$oldSettings['Site_Title']."', 1),
	('website_url', '".$oldSettings['Site_Address']." ', 1),
	('board_url', '".$oldSettings['Board_Address']."', 1),
	('board_status', '1', 3),
	('board_email', '".$oldSettings['Board_Email']."', 1),
	('board_directory', '".$boardFolder."', 1),
	('offline_msg', '', 1),
	('infobox_status', '1', 3),
	('default_style', '1', 2),
	('default_language', 'English', 1),
	('rules_status', '".$oldSettings['TOS_Status']."', 3),
	('rules', '".$oldSettings['TOS_Rules']."', 1),
	('per_page', '".$oldSettings['per_page']."', 2),
	('captcha', '1', 3),
	('pm_quota', '".$oldSettings['PM_Quota']."', 2),
	('archive_quota', '".$oldSettings['Archive_Quota']."', 2),
	('activation', '".$oldSettings['activation']."', 1),
	('allow_newusers', '".$oldSettings['register_stat']."', 3),
	('userstat', '".$oldSettings['userstat']."', 2),
	('coppa', '".$oldSettings['coppa']."', 2),
	('timezone', '".$oldSettings['Default_Zone']."', 2),
	('timeformat', '".$oldSettings['Default_Time']."', 1),
	('cookie_domain', '".$oldSettings['cookie_domain']."', 1),
	('cookie_path', '".$oldSettings['cookie_path']."', 1),
	('cookie_secure', '".$oldSettings['cookie_secure']."', 3),
	('attachment_quota', '".$oldSettings['attachment_quota']."', 2),
	('allow_guest_downloads', '".$oldSettings['download_attachments']."', 3),
	('mx_check', '".$oldSettings['mx_check']."', 3),
	('warning_threshold', '".$oldSettings['warning_threshold']."', 2),
	('mail_type', '".$oldSettings['mail_type']."', 3),
	('sendmail_path', '/usr/sbin/sendmail', 1),
	('smtp_host', '".$oldSettings['smtp_server']."', 1),
	('smtp_port', '".$oldSettings['smtp_port']."', 2),
	('smtp_user', '".$oldSettings['smtp_user']."', 1),
	('smtp_pwd', '".$oldSettings['smtp_pass']."', 1),
	('smtp_encryption', '', 2),
	('mail_antiflood', '30', 2),
	('upload_limit', '5', 2),
	('version_main', '3', 0),
	('version_minor', '0', 0),
	('version_patch', '0', 0),
	('version_build', 'RC1', 0)";
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

	//
	// DROP OUTDATED TABLES
	//
	$db->SQL="DROP TABLE ebb_settings, ebb_pm_banlist, ebb_ranks";
	$db->query();

	#display success message.
	$displayMsg = new notifySys('Database setup complete. <b>Enhance Administrator Password</b>', false);
	$displayMsg->displayAjaxError("success");
break;
case 'convertAdministrator':
	#load template file.
	$tpl = new templateEngine(0, "upgrade2-convert-admin-password", "installer");
	echo $tpl->outputHtml();
break;
case 'convertAdmin':
	//get values from form.
	$password = $db->filterMySQL($_POST['password']);
	$vert_password = $db->filterMySQL($_POST['vert_password']);

	//do some error checking
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
	if ($vert_password !== $password){
		#display validation message.
		$displayMsg = new notifySys('Password mis-match.', false);
		exit($displayMsg->displayAjaxError("warning"));
	}

	#generate a new salt for the user's password.
	$pwdSalt = createPwdSalt();
	$pass = sha1($password.$pwdSalt);

	//update user.
	$db->SQL = "UPDATE ebb_users SET Password='$pass', salt='$pwdSalt' WHERE id='1'";
	$db->query();

	#display success message.
	$displayMsg = new notifySys('Administration Account Converted Successfully! Next: <b>User Password</b>.', false);
	$displayMsg->displayAjaxError("success");
break;
case 'convertUserPwd':

	#start sql
	$db->SQL = "SELECT id, Email, Username FROM ebb_users where id!='1'";
	$userConvertQ = $db->query();

	$boardTitle = $boardPref->getPreferenceValue("board_name");
	$boardAddr = $boardPref->getPreferenceValue("board_url");

	#send out an email.
	require_once FULLPATH."/includes/swift/swift_required.php";

	while($convertUsr = mysql_fetch_assoc($userConvertQ)){

		#generate a new salt for the user's password.
		$pwdSalt = createPwdSalt();

		#generate a new user verification key.
		$newActKey = md5(makeRandomPassword());

		#generate new password.
		$new_pwd = makeRandomPassword();

		#generate a new password.
		$encryptPwd = sha1($new_pwd.$pwdSalt);

		$db->SQL = "UPDATE ebb_users SET Password='$encryptPwd', salt='$pwdSalt', active='0', Language='English', Style='1', act_key='$newActKey' where id='$convertUsr[id]'";
		$db->query();

		//add user to Regular Member group.
		$db->SQL = "INSERT INTO ebb_group_users (Status, gid, Username) VALUES('Active', '3', '$convertUsr[Username]')";
		$db->query();

		#MAILER
		//send out email to users.
		$activateMessage = "Hello {user},

		{title} has upgraded to a new version of Elite Bulletin Board.

		With that, all members of {title}  had to get a new password due to a new password system
		in place, which is more secure than it's previous setup. All accounts were also disabled for security reasons.

		VERY IMPORTANT: before re-activating your account, delete any cookies made by this board, failing to do this will
		lock you out. If you are unsure on how to do this, contact your board administrator.

		Some of your settings have been altered, you may adjust them once you login.

		To re-activate your account please go to the link below:

		{boardurl}/login.php?mode=verify_acct&u={user}&key={acctkey}

		When your account is re-activated, you will login with a new password, your new password is:

		{newpwd}

		You may change your password once you login into your account.

		If you have any problems or any questions, please refer that to the Board Administrator.

		{title} Staff";

		#see what kind of transport to use.
		if($boardPref->getPreferenceValue("mail_type") == 0){
			#see if we're using some form of encryption.
			if ($boardPref->getPreferenceValue("smtp_encryption") == ""){
				//Create the Transport
				$transport = Swift_SmtpTransport::newInstance($boardPref->getPreferenceValue("smtp_host"), $boardPref->getPreferenceValue("smtp_port"))
				  ->setUsername($boardPref->getPreferenceValue("smtp_user"))
				  ->setPassword($boardPref->getPreferenceValue("smtp_pwd"));
			} else{
				//Create the Transport
				$transport = Swift_SmtpTransport::newInstance($boardPref->getPreferenceValue("smtp_host"), $boardPref->getPreferenceValue("smtp_port"), $boardPref->getPreferenceValue("smtp_encryption"))
				  ->setUsername($boardPref->getPreferenceValue("smtp_user"))
				  ->setPassword($boardPref->getPreferenceValue("smtp_pwd"));
			}

			//Create the Mailer using your created Transport
			$mailer = Swift_Mailer::newInstance($transport);
		} else if ($boardPref->getPreferenceValue("mail_type") == 2){
			//Create the Transport
			$transport = Swift_SendmailTransport::newInstance($boardPref->getPreferenceValue("sendmail_path").' -bs');

			//Create the Mailer using your created Transport
			$mailer = Swift_Mailer::newInstance($transport);
		} else {
			//Create the Transport
			$transport = Swift_MailTransport::newInstance();

			//Create the Mailer using your created Transport
			$mailer = Swift_Mailer::newInstance($transport);
		}

		#build email.
		$message = Swift_Message::newInstance("Board Upgraded, Please Re-Activare Your Account")
			->setFrom(array($boardPref->getPreferenceValue("board_email") => $boardTitle)) //Set the From address
			->setTo(array($convertUsr['Email'] => $convertUsr['Username']))
			->setBody($activateMessage); //set email body

		#create array for replacements.
		$replacements[$convertUsr['Email']] = array(
			'{title}'=>$boardTitle,
			'{boardurl}'=>$boardAddr,
			'{user}'=>$convertUsr['Username'],
			'{newpwd}'=>$new_pwd,
			'{acctkey}'=>$newActKey
		);

		#setup mailer template.
		$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
		$mailer->registerPlugin($decorator);

		#send message out.
		$mailer->Send($message);
		#END MAILER
	} //END loop

	#display success message.
	$displayMsg = new notifySys('User Accounts Converted Successfully! Next: <b>Complete Upgrade</b>.', false);
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
	$tpl = new templateEngine(0, "upgrade2-config-setup", "installer");

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
