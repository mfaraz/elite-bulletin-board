<?php
define('IN_EBB', true);
/**
Filename: acp_log.php
Last Modified: 2/22/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "../config.php";
require_once FULLPATH."/header.php";
require_once FULLPATH."/acp/acpheader.php";

if(isset($_GET['mode'])){
	$mode = var_cleanup($_GET['mode']);
}else{
	$mode = ''; 
}	

#load header file.
$tpl = new templateEngine($style, "acp_logheader");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$lang[admincp]",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

//display admin CP
switch($mode){
case 'clear':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 12) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}else{
		#flush the audit system.
		$acpAudit = new auditSystem();
		$acpAudit->clearAuditLog();

		//close window.
		echo "<script type=\"text/javascript\">javascript:self.close();</script>";
	}
break;
default:
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 11) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}

	#view the full audit system.
	$acpAudit = new auditSystem();
	$auditLog = $acpAudit->viewFullAuditLog();

	#display audit log.
	$tpl = new templateEngine($style, "cp-acp_log");
	$tpl->parseTags(array(
	"BOARDDIR" => "$boardAddr",
	"LANG-CLEARLIST" => "$lang[acp_lclear]",
	"LANG-DELPROMPT" => "$lang[confirmacpclear]",
	"LANG-ACPLOG" => "$lang[acp_title]",
	"ACPLOG" => "$auditLog",
	"LANG-CLOSE" => "$txt[closewindow]"));
		
	#see if user can clear the audit log.
	if ($groupPolicy->validateAccess(1, 12) == false){
		$tpl->removeBlock("clearList");
	}
		
	echo $tpl->outputHtml();
break;
}
ob_end_flush();
?>
