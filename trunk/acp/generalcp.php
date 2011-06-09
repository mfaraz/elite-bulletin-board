<?php
define('IN_EBB', true);
/**
Filename: generalcp.php
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
require_once FULLPATH."/includes/admin_function.php";
require_once FULLPATH."/includes/phpmailer/class.phpmailer.php";
require_once FULLPATH."/includes/acp/ebbinstaller.class.php";

if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = ''; 
}
#get title.
switch($action){
case 'newsletter':
case 'mail_send':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 4) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$generalcptitle = $lang['generalmenu'].' - '.$lang['newsletter'];
	$helpTitle = $help['newslettertitle'];
	$helpBody = $help['newsletterbody'];
break;
case 'smiles':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 6) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$generalcptitle = $lang['generalmenu'].' - '.$lang['smiles'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'add_smiles':
case 'add_smiles_process':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 6) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$generalcptitle = $lang['generalmenu'].' - '.$lang['addsmiles'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'modify_smiles':
case 'modify_smiles_process':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 6) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$generalcptitle = $lang['generalmenu'].' - '.$lang['modifysmiles'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'delete_smiles':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 6) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$generalcptitle = $lang['generalmenu'].' - '.$lang['delsmiles'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'censor':
case 'censor_add':
case 'censor_modify':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 5) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$generalcptitle = $lang['generalmenu'].' - '.$lang['censor'];
	$helpTitle = $help['censortitle'];
	$helpBody = $help['censorbody'];
break;
default:
	$generalcptitle = $lang['generalmenu'];
}
$tpl = new templateEngine($style, "acp_header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$generalcptitle",
    "LANG-HELP-TITLE" => "$helpTitle",
    "LANG-HELP-BODY" => "$helpBody",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

//output top
$pmMsg = $userData->getNewPMCount();

$tpl = new templateEngine($style, "top-acp");
$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-WELCOME" => "$lang[welcome]",
	"LOGGEDUSER" => "$logged_user",
	"LANG-LOGOUT" => "$lang[logout]",
	"NEWPM" => "$pmMsg",
	"LANG-CP" => "$lang[admncp]",
	"LANG-NEWPOSTS" => "$lang[newposts]",
	"ADDRESS" => "$address",
	"LANG-HOME" => "$lang[home]",
	"LANG-SEARCH" => "$lang[search]",
	"LANG-CLOSE" => "$lang[close]",
	"LANG-QUICKSEARCH" => "$lang[quicksearch]",
	"LANG-ADVANCEDSEARCH" => "$lang[advsearch]",
	"LANG-HELP" => "$lang[help]",
	"LANG-MEMBERLIST" => "$lang[members]",
	"LANG-PROFILE" => "$lang[profile]"));

#update user's activity.
echo update_whosonline_reg($logged_user);

#output top template file.
echo $tpl->outputHtml();

switch($action){
case 'newsletter':
	#see if any errors were reported by auth.php.
	if(isset($_SESSION['errors'])){
	    #format error(s) for the user.
		$errors = var_cleanup($_SESSION['errors']);

		#display validation message.
	    $displayMsg = new notifySys($errors, false);
		$displayMsg->displayValidate();

		#destroy errors session data, its no longer needed.
       	unset($_SESSION['errors']);
	}

	#newslatter form.
	$tpl = new templateEngine($style, "cp-newsletter");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-NEWSLETTER" => "$lang[newsletter]",
	"LANG-TEXT" => "$lang[newslettertxt]",
	"LANG-SUBJECT" => "$lang[subject]",
	"LANG-MESSAGE" => "$lang[message]",
	"LANG-SENDNEWSLETTER" => "$lang[sendnewsletter]"));

	#output template file.
	echo $tpl->outputHtml();
break;
case 'mail_send':
	$subject = stripslashes($_POST['subject']);
	$mail_message = stripslashes($_POST['mail_message']);

	//error checking.
	if(empty($subject)){
		#setup error session.
		$_SESSION['errors'] = $lang['nosubject'];

        #direct user.
		redirect('acp/generalcp.php?action=newsletter', false, 0);
	}
	if (empty($mail_message)){
		#setup error session.
		$_SESSION['errors'] = $lang['nomailmsg'];

        #direct user.
		redirect('acp/generalcp.php?action=newsletter', false, 0);
	}

	//get user's email.
	$db->SQL = "SELECT Email FROM ebb_users";
	$newsletterQ = $db->query();

	#setup mailer.
	$mailer = new PHPMailer();
	$mailer->Subject = $subject;
	$mailer->Body = $mail_message;
	$mailer->SetFrom($boardPref->getPreferenceValue("board_email"), $title);

	#see if SMTP is used.
	if($boardPref->getPreferenceValue("mail_type") == 0){
	    $mailer->IsSMTP();
	    $mailer->SMTPAuth = true;
		$mailer->Host = $boardPref->getPreferenceValue("smtp_host");
		$mailer->Port = $boardPref->getPreferenceValue("smtp_port");
		$mailer->Username = $boardPref->getPreferenceValue("smtp_user");
		$mailer->Password = $boardPref->getPreferenceValue("smtp_pwd");
		}else{
			$mailer->IsMail();
		}
		
	#add users to mail list.
	while($mailTo = mysql_fetch_assoc($newsletterQ)){
		$mailer->AddBCC($mailTo['Email']);

		//send out the email.
		$mailer->Send();

		//clear the list to prevent any double emails.
		$mailer->ClearAllRecipients();
	}

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Sent out Newsletter", $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/index.php', false, 0);
break;
case 'smiles':
	#display smiles.
	admin_smilelisting();
break;
case 'add_smiles':
	#see if any errors were reported by auth.php.
	if(isset($_SESSION['errors'])){
	    #format error(s) for the user.
		$errors = var_cleanup($_SESSION['errors']);

		#display validation message.
	    $displayMsg = new notifySys($errors, false);
		$displayMsg->displayValidate();

		#destroy errors session data, its no longer needed.
       	unset($_SESSION['errors']);
	}

	#add smiles form.
	$tpl = new templateEngine($style, "cp-newsmiles");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-ADDSMILES" => "$lang[addsmiles]",
	"LANG-SMILECODE" => "$lang[smilecode]",
	"LANG-SMILEFILE" => "$lang[smilefile]"));

	#output template file.
	echo $tpl->outputHtml();
break;
case 'add_smiles_process':
	$smile_code = $db->filterMySQL($_POST['smile_code']);
	$smile_file = $db->filterMySQL($_POST['smile_file']);

	#error checking.
	if (empty($smile_code)){
		#setup error session.
		$_SESSION['errors'] = $lang['nosmilecodeerror'];

        #direct user.
		redirect('acp/generalcp.php?action=add_smiles', false, 0);
	}
	if (empty($smile_file)){
		#setup error session.
		$_SESSION['errors'] = $lang['nosmilefileerror'];

        #direct user.
		redirect('acp/generalcp.php?action=add_smiles', false, 0);
	}
	if(strlen($smile_code) > 30){
		#setup error session.
		$_SESSION['errors'] = $lang['longsmilecode'];

        #direct user.
		redirect('acp/generalcp.php?action=add_smiles', false, 0);
	}
	if(strlen($smile_file) > 80){
		#setup error session.
		$_SESSION['errors'] = $lang['longsmilepath'];

        #direct user.
		redirect('acp/generalcp.php?action=add_smiles', false, 0);
	}

	//process query
	$db->SQL = "INSERT INTO ebb_smiles (code, img_name) VALUES('$smile_code', '$smile_file')";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Added new smile", $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/generalcp.php?action=smiles', false, 0);
break;
case 'modify_smiles':
	#see if user added the Smile ID or not.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$error = new notifySys($lang['nosmid'], true);
		$error->displayError();
	}else{
		$id = $db->filterMySQL($_GET['id']);
	}
	$db->SQL = "SELECT code, img_name FROM ebb_smiles WHERE id='$id'";
	$smilesRes = $db->fetchResults();
	$smileChk = $db->AffectedRows();

	#see if the smile exist.
	if($smileChk == 0){
		$error = new notifySys($lang['smilenotexist'], true);
		$error->displayError();
	}else{
		#see if any errors were reported.
		if(isset($_SESSION['errors'])){
		    #format error(s) for the user.
			$errors = var_cleanup($_SESSION['errors']);

			#display validation message.
		    $displayMsg = new notifySys($errors, false);
			$displayMsg->displayValidate();

			#destroy errors session data, its no longer needed.
	       	unset($_SESSION['errors']);
		}

		#modify smiles form.
		$tpl = new templateEngine($style, "cp-modifysmiles");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[admincp]",
		"LANG-MODIFYSMILES" => "$lang[modifysmiles]",
		"ID" => "$id",
		"LANG-SMILECODE" => "$lang[smilecode]",
		"SMILECODE" => "$smilesRes[code]",
		"LANG-SMILEFILE" => "$lang[smilefile]",
		"SMILEFILE" => "$smilesRes[img_name]"));

		#output template file.
		echo $tpl->outputHtml();
	}
break;
case 'modify_smiles_process':
	#see if user added the Smile ID or not.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$error = new notifySys($lang['nosmid'], true);
		$error->displayError();
	}else{
		$id = $db->filterMySQL($_GET['id']);
	}
	$db->SQL = "select id from ebb_smiles where id='$id'";
	$smileChk = $db->AffectedRows();

	#see if the smile exist.
	if($smileChk == 0){
		$error = new notifySys($lang['smilenotexist'], true);
		$error->displayError();
	}	
	$mod_smile_code = $db->filterMySQL($_POST['smile_code']);
	$mod_smile_file = $db->filterMySQL($_POST['smile_file']);

	#error check.
	if (empty($mod_smile_code)){
		#setup error session.
		$_SESSION['errors'] = $lang['nosmilecodeerror'];

        #direct user.
		redirect('acp/generalcp.php?action=modify_smiles&id='.$id, false, 0);
	}
	if (empty($mod_smile_file)){
		#setup error session.
		$_SESSION['errors'] = $lang['nosmilefileerror'];

        #direct user.
		redirect('acp/generalcp.php?action=modify_smiles&id='.$id, false, 0);
	}
	if(strlen($mod_smile_code) > 30){
		#setup error session.
		$_SESSION['errors'] = $lang['longsmilecode'];

        #direct user.
		redirect('acp/generalcp.php?action=modify_smiles&id='.$id, false, 0);
	}
	if(strlen($mod_smile_file) > 80){
		#setup error session.
		$_SESSION['errors'] = $lang['longsmilepath'];

        #direct user.
		redirect('acp/generalcp.php?action=modify_smiles&id='.$id, false, 0);
	}

	//process query
	$db->SQL = "UPDATE ebb_smiles SET code='$mod_smile_code', img_name='$mod_smile_file' WHERE id='$id'";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Modified smile", $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/generalcp.php?action=smiles', false, 0);
break;
case 'delete_smiles':
	#see if user added the Smile ID or not.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$error = new notifySys($lang['nosmid'], true);
		$error->displayError();
	}else{
		$id = $db->filterMySQL($_GET['id']);
	}
	$db->SQL = "select id from ebb_smiles where id='$id'";
	$smileChk = $db->AffectedRows();

	#see if the smile exist.
	if($smileChk == 0){
		$error = new notifySys($lang['smilenotexist'], true);
		$error->displayError();
	}else{
		//process query
		$db->SQL = "DELETE FROM ebb_smiles WHERE id='$id'";
		$db->query();

		#log this into our audit system.
		$acpAudit = new auditSystem();
		$acpAudit->logAction("Deleted smile", $acpUsr, time(), detectProxy());

		//bring user back to board section
		redirect('acp/generalcp.php?action=smiles', false, 0);
	}
break;
case 'censor':
	#see if any errors were reported.
	if(isset($_SESSION['errors'])){
	    #format error(s) for the user.
		$errors = var_cleanup($_SESSION['errors']);

		#display validation message.
	    $displayMsg = new notifySys($errors, false);
		$displayMsg->displayValidate();

		#destroy errors session data, its no longer needed.
       	unset($_SESSION['errors']);
	}

	#load censorlist.
	admin_censorlist();
break;
case 'censor_add':
	$addcensor = $db->filterMySQL($_POST['addcensor']);
	$censoraction = $db->filterMySQL($_POST['censoraction']);

	#error check.
	if (empty($addcensor)){
		#setup error session.
		$_SESSION['errors'] = $lang['nocensor'];

        #direct user.
		redirect('acp/generalcp.php?action=censor', false, 0);
	}
	if(strlen($addcensor) > 50){
		#setup error session.
		$_SESSION['errors'] = $lang['longcensor'];

        #direct user.
		redirect('acp/generalcp.php?action=censor', false, 0);
	}
	if($censoraction == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nocensoraction'];

        #direct user.
		redirect('acp/generalcp.php?action=censor', false, 0);
	}
	if(!is_numeric($censoraction)){
		#setup error session.
		$_SESSION['errors'] = $lang['invalidcensoraction'];

        #direct user.
		redirect('acp/generalcp.php?action=censor', false, 0);
	}

	//process query
	$db->SQL = "INSERT INTO ebb_censor (Original_Word, action) VALUES('$addcensor', '$censoraction')";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Added word to censor list", $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/generalcp.php?action=censor', false, 0);
break;
case 'censor_modify':
	#see if user added the censor ID or not.
	if((!isset($_GET['id'])) or (empty($_GET['id']))){
		$error = new notifySys($lang['nocensorid'], true);
		$error->displayError();
	}else{
		$id = $db->filterMySQL($_GET['id']);
	}
	$db->SQL = "SELECT id FROM ebb_censor WHERE id='$id'";
	$censorChk = $db->AffectedRows();

	#see if the word exist.
	if($censorChk == 0){
		$error = new notifySys($lang['censornotfound'], true);
		$error->displayError();
	}else{
		//process query
		$db->SQL = "DELETE FROM ebb_censor WHERE id='$id'";
		$db->query();

		#log this into our audit system.
		$acpAudit = new auditSystem();
		$acpAudit->logAction("Deleted a word from censor list", $acpUsr, time(), detectProxy());

		//bring user back to board section
		redirect('acp/generalcp.php?action=censor', false, 0);
	}
break;
default:
	header("Location: index.php"); 
}
#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();
ob_end_flush();
?>
