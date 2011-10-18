<?php
session_start();
ob_start();
define('IN_EBB', true);
/**
 * upgrade-v3.php
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
	// UPDATE TABLE STRUCTURE
	//

	//ebb_attachments
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

	//update ebb_poll
	$db->SQL="ALTER TABLE `ebb_poll` CHANGE `tid` `tid` MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0'";
	$db->query();

	//ebb_smiles
	$db->SQL="TRUNCATE TABLE `ebb_smiles`";
	$db->query();

	//ebb_style
	$db->SQL="TRUNCATE TABLE `ebb_style`";
	$db->query();

	//ebb_users
	$db->SQL="ALTER TABLE `ebb_users`
					  DROP `rssfeed1`,
					  DROP `rssfeed2`,
					  DROP `banfeeds`";
	$db->query();

	#save settings
	$boardPref->savePreferences("version_build", "RC1");

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
	$db->SQL="DROP TABLE  ebb_pm_banlist";
	$db->query();

	#display success message.
	$displayMsg = new notifySys('Database setup complete. <b>Complete Upgrade</b>', false);
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
	$tpl = new templateEngine(0, "upgrade3-config-setup", "installer");

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
