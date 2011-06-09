<?php
define('IN_EBB', true);
/**
Filename: modinstaller.php
Last Modified: 11/1/2010

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "../config.php";
require_once FULLPATH."/header.php";
require_once FULLPATH."/acp/acpheader.php";
require_once FULLPATH."/includes/admin_function.php";
require_once FULLPATH."/includes/acp/ebbinstaller.class.php";

#see if user has access to this portion of the script.
if($groupPolicy->validateAccess(1, 10) == true){
	#show list of plugin installers.
	$installer = new EBBInstaller();
	$pluginInstaller = $installer->acpPluginInstaller();

	#display audit log.
	$tpl = new templateEngine($style, "cp-modinstaller");
	$tpl->parseTags(array(
	"PAGETITLE" => "$lang[modlist]",
	"LANG-INFO" => "$lang[info]",
	"LANG-CLOSE" => "$lang[close]",
	"LANG-JSDISABLED" => "$lang[jsdisabled]",
	"LANG-TEXT" => "$lang[modinstalltxt]",
	"MODINSTALL-LIST" => "$pluginInstaller",
	"LANG-CLOSEWINDOW" => "$lang[closewindow]"));

	echo $tpl->outputHtml();
}else{
	$error = new notifySys($lang['noaccess'], true);
	$error->displayError();
}
?>
