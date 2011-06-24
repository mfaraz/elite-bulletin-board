<?php
define('IN_EBB', true);
/**
 * pm.ajax.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 6/22/2011
*/

#make sure the call is from an AJAX request.
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
	die("<strong>THIS PAGE CANNOT BE ACCESSED DIRECTLY!</strong>");
}

require_once "../config.php";
require_once FULLPATH."/header.php";
require_once FULLPATH."/includes/PM.class.php";
require_once FULLPATH."/includes/swift/swift_required.php";

if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = '';
}

//block guest users.
if($logged_user == "guest"){
	$error = new notifySys($lang['guesterror'], true);
	exit($displayMsg->displayError());
}

#see if user can access PM.
if($groupPolicy->validateAccess(1, 27) == false){
    $displayMsg = new notifySys($lang['accessdenied'], true);
	$displayMsg->displayError();
}

#set some posting rules.
$allowsmile = 1;
$allowbbcode = 1;
$allowimg = 0;

switch ($action){
	case 'viewMsg';
		#validate PM ID.
		if((!isset($_GET['id'])) or (empty($_GET['id']))){
			$displayMsg = new notifySys($lang['nopmid'], true);
			exit($displayMsg->displayError());
		}else{
			$id = $db->filterMySQL($_GET['id']);
		}

		$pmObj = new PM($id, $logged_user); #setup PM object.

		$pmMsgQry = $pmObj->ReadMessage();

		#see if pm message exist.
		if($pmObj->PMExists() == false){
			$displayMsg = new notifySys($lang['pm404'], true);
			exit($displayMsg->displayError());
		}

		//see if pm message belong to the right user.
		if ($pmObj->IsPMOwner() == false){
			$displayMsg = new notifySys($lang['accessdenied'], true);
			$displayMsg->displayError();
		}

		//mark as read
		if (empty($pmMsgQry['Read_status'])){
			$db->SQL = "UPDATE ebb_pm SET Read_Status='old' WHERE id='$id'";
			$db->query();
		}

		//bbcode & other formating processes.
		$pmMsg = nl2br(smiles(BBCode(language_filter($pmMsgQry['Message'], 1), true)));

		//get the date
		$pmDate = formatTime($timeFormat, $pmMsgQry['Date'], $gmt);

		#get information regarding the sender.
		$userInfo = new user($pmMsgQry['Sender']);

		//get sig.
		$uSig = $userInfo->userSettings("Sig");
		if(empty($uSig)){
			$sig = "";
		}else{
			$pmsig = nl2br(smiles(BBCode(language_filter($userInfo->userSettings("Sig"), 1), true)));
			$sig = "_____________<br />".$pmsig;
		}

		#load template file.
		$tpl = new templateEngine($style, "pm-read");
		$tpl->parseTags(array(
		"BOARDDIR" => "$boardDir",
		"LANG-READPM" => "$lang[readpm]",
		"LANG-DELPROMPT" => "$lang[confirmdelete]",
		"LANG-MOVEPROMPT" => "$lang[moveconfirm]",
		"LANG-REPLY" => "$lang[replypm]",
		"ID" => "$pmMsgQry[id]",
		"LANG-FROM" => "$lang[from]",
		"FROM" => "$pmMsgQry[Sender]",
		"LANG-TO" => "$lang[to]",
		"TO" => "$pmMsgQry[Reciever]",
		"LANG-DATE" => "$lang[date]",
		"DATE" => "$pmDate",
		"LANG-SUBJECT" => "$lang[subject]",
		"SUBJECT" => "$pmMsgQry[Subject]",
		"PM-MESSAGE" => "$pmMsg",
		"SIGNATURE" => "$sig",
		"LANG-DELETEPM" => "$lang[delpm]",
		"LANG-MOVEPM" => "$lang[movemsg]"));

		#do some decision making.
		if($pmMsgQry['Folder'] == "Archive"){
			$tpl->removeBlock("reply");
			$tpl->removeBlock("inbox");
		}
		echo $tpl->outputHtml();
	break;
	case 'newMsg':
		#see if a specific user is defined.
		if(isset($_GET['user'])){
			$user = $db->filterMySQL($_GET['user']);
		}else{
			$user = '';
		}

		#load template file.
		$bName['Smiles'] = 1;
		$smiles = form_smiles();
		
		$tpl = new templateEngine($style, "pm-postpm");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[pm]",
		"LANG-POSTPM" => "$lang[PostPM]",
		"LANG-SMILES" => "$lang[moresmiles]",
		"SMILES" => "$smiles",
		"LANG-DISABLERTF" => "$lang[disablertf]",
		"LANG-USERNAME" => "$lang[username]",
		"USERNAME" => "$logged_user",
		"LANG-TO" => "$lang[send]",
		"TO" => "$user",
		"LANG-SUBJECT" => "$lang[subject]",
		"LANG-SENDPM" => "$lang[sendpm]"));
		echo $tpl->outputHtml();

	break;
	case 'sendPM':
		//get the values from the form.
		$send = $db->filterMySQL($_POST['send']);
		$subject = $db->filterMySQL($_POST['subject']);
		$message = $db->filterMySQL(var_cleanup($_POST['message']));
		$time = time();
		$pmObj = new PM(0, $logged_user); #setup PM object.

		#error check.
		if (empty($send)){
			#display validation message.
			$displayMsg = new notifySys($lang['nosend'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if (empty($subject)){
			#display validation message.
			$displayMsg = new notifySys($lang['nosubject'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if (empty($message)){
			#display validation message.
			$displayMsg = new notifySys($lang['nomessage'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($send) > 25){
			#display validation message.
			$displayMsg = new notifySys($lang['longuser'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($subject) > 25){
			#display validation message.
			$displayMsg = new notifySys($lang['longsubject'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}

		#setup and obtain user group status & information.
		$userGroupPolicy = new groupPolicy($send);
		$userInfo = new user($send);

		#does user have PM Access?
		if($userGroupPolicy->validateAccess(1, 27) == false){
			$displayMsg = new notifySys($lang['pm_access_user'], true);
			exit($displayMsg->displayAjaxError("warning"));
		}

		//check to see if the from user's inbox is full.
		if (!$pmObj->QuotaCheck('Inbox', $send)) {
			$displayMsg = new notifySys($lang['overquota'], true);
			exit($displayMsg->displayAjaxError("warning"));
		}

		//check to see if this user is on the ban list.
		if ($pmObj->IsBannedByUser($send)) {
			$displayMsg = new notifySys($lang['blocked'], true);
			exit($displayMsg->displayAjaxError("warning"));
		}

		//send form values to db.
		$pmObj->ComposeMessage($send, $logged_user, $subject, $message, $time);

		#display success message.
		$displayMsg = new notifySys($lang['sentpmsuccessfully'], false);
		$displayMsg->displayAjaxError("success");

		#see if user wishes to recieve an email about new PMs.
		if ($userInfo->userSettings("PM_Notify") == 1){
			//get pm id.
			$db->SQL = "SELECT id FROM ebb_pm ORDER BY id DESC limit 1";
			$pm_id_result = $db->fetchResults();

			//grab values from PM message.
			$db->SQL = "SELECT Reciever, Sender, Subject, id FROM ebb_pm WHERE id='$pm_id_result[id]'";
			$pm_data = $db->fetchResults();

			#load mail language file.
			require_once FULLPATH."/lang/".$lng.".email.php";

			#see what kind of transport to use.
			if($boardPref->getPreferenceValue("mail_type") == 0){
				#see if we're using some form of encryption.
				if ($boardPref->getPreferenceValue("smtp_encryption") == ""){
					//Create the Transport
					$transport = Swift_SmtpTransport::newInstance($boardPref->getPreferenceValue("smtp_host"), $boardPref->getPreferenceValue("smtp_port"))
					  ->setUsername($boardPref->getPreferenceValue("smtp_user"))
					  ->setPassword($boardPref->getPreferenceValue("smtp_pwd"));
				} else {
					//Create the Transport
					$transport = Swift_SmtpTransport::newInstance($boardPref->getPreferenceValue("smtp_host"), $boardPref->getPreferenceValue("smtp_port"), $boardPref->getPreferenceValue("smtp_encryption"))
					  ->setUsername($boardPref->getPreferenceValue("smtp_user"))
					  ->setPassword($boardPref->getPreferenceValue("smtp_pwd"));
				}

				//Create the Mailer using your created Transport
				$mailer = Swift_Mailer::newInstance($transport);
			} else if ($boardPref->getPreferenceValue("mail_type") == 2){
				//TODO add sendmail option to administation panel
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
			$message = Swift_Message::newInstance($lang['pmsubject'])
				->setFrom(array($boardPref->getPreferenceValue("board_email") => $title)) //Set the From address
				->setTo(array($userInfo->userSettings("Email") => $send))
				->setBody(pm_notify()); //set email body

			#create array for replacements.
			$replacements[$userInfo->userSettings("Email")] = array(
				'{pm-receiver}'=>$pm_data['Reciever'],
				'{pm-sender}'=>$pm_data['Sender'],
				'{pm-subject}' =>$pm_data['Sender'],
				'{boardAddr}'=>$boardAddr,
				'{pm-id}'=>$pm_data['id']
			);

			#setup mailer template.
			$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
			$mailer->registerPlugin($decorator);

			#send message out.
			//TODO: Add a failure list to this method to help administrators weed out "dead" accounts.
			$mailer->Send($message);
		}
	break;
	case 'replyMsg':
		#validate PM ID.
		if((!isset($_GET['id'])) or (empty($_GET['id']))){
			$displayMsg = new notifySys($lang['nopmid'], true);
			exit($displayMsg->displayAjaxError("error"));
		}else{
			$id = $db->filterMySQL($_GET['id']);
		}

		//get data needed for reply.
		$db->SQL = "SELECT Sender, Subject FROM ebb_pm WHERE id='$id'";
		$reply = $db->fetchResults();

		#load template file.
		$bName['Smiles'] = 1;
		$smiles = form_smiles();

		$tpl = new templateEngine($style, "pm-replypm");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[pm]",
		"LANG-POSTPM" => "$lang[PostPM]",
		"LANG-SMILES" => "$lang[moresmiles]",
		"SMILES" => "$smiles",
		"LANG-DISABLERTF" => "$lang[disablertf]",
		"LANG-USERNAME" => "$lang[username]",
		"USERNAME" => "$logged_user",
		"LANG-TO" => "$lang[send]",
		"TO" => "$reply[Sender]",
		"LANG-SUBJECT" => "$lang[subject]",
		"SUBJECT" => "$reply[Subject]",
		"LANG-SENDPM" => "$lang[sendpm]"));
		echo $tpl->outputHtml();
	break;
	case 'sendPMReply':
		//get the values from the form.
		$send = $db->filterMySQL($_POST['send']);
		$subject = $db->filterMySQL($_POST['subject']);
		$message = $db->filterMySQL(var_cleanup($_POST['message']));
		$time = time();
		$pmObj = new PM(0, $logged_user); #setup PM object.

		#error check.
		if (empty($send)){
			#display validation message.
			$displayMsg = new notifySys($lang['nosend'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if (empty($subject)){
			#display validation message.
			$displayMsg = new notifySys($lang['nosubject'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if (empty($message)){
			#display validation message.
			$displayMsg = new notifySys($lang['nomesage'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($send) > 25){
			#display validation message.
			$displayMsg = new notifySys($lang['longuser'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}
		if(strlen($subject) > 25){
			#display validation message.
			$displayMsg = new notifySys($lang['longsubject'], false);
			exit($displayMsg->displayAjaxError("warning"));
		}

		#setup and obtain user group status & information.
		$userGroupPolicy = new groupPolicy($send);
		$userInfo = new user($send);

		#does user have PM Access?
		if($userGroupPolicy->validateAccess(1, 27) == false){
			$displayMsg = new notifySys($lang['pm_access_user'], true);
			exit($displayMsg->displayAjaxError("warning"));
		}

		//check to see if the from user's inbox is full.
		if (!$pmObj->QuotaCheck('Inbox')) {
			$displayMsg = new notifySys($lang['overquota'], true);
			exit($displayMsg->displayAjaxError("warning"));
		}

		//check to see if this user is on the ban list.
		if ($pmObj->IsBannedByUser($send)) {
			$displayMsg = new notifySys($lang['blocked'], true);
			exit($displayMsg->displayAjaxError("warning"));
		}

		//send form values to db.
		$pmObj->ComposeMessage($send, $logged_user, $subject, $message, $time);

		#see if user wishes to recieve an email about new PMs.
		if ($userInfo->userSettings("PM_Notify") == 1){
			//get pm id.
			$db->SQL = "SELECT id FROM ebb_pm ORDER BY id DESC limit 1";
			$pm_id_result = $db->fetchResults();

			//grab values from PM message.
			$db->SQL = "SELECT Reciever, Sender, Subject, id FROM ebb_pm WHERE id='$pm_id_result[id]'";
			$pm_data = $db->fetchResults();

			#load mail language file.
			require_once FULLPATH."/lang/".$lng.".email.php";

			#see what kind of transport to use.
			if($boardPref->getPreferenceValue("mail_type") == 0){
				#see if we're using some form of encryption.
				if ($boardPref->getPreferenceValue("smtp_encryption") == ""){
					//Create the Transport
					$transport = Swift_SmtpTransport::newInstance($boardPref->getPreferenceValue("smtp_host"), $boardPref->getPreferenceValue("smtp_port"))
					  ->setUsername($boardPref->getPreferenceValue("smtp_user"))
					  ->setPassword($boardPref->getPreferenceValue("smtp_pwd"));
				} else {
					//Create the Transport
					$transport = Swift_SmtpTransport::newInstance($boardPref->getPreferenceValue("smtp_host"), $boardPref->getPreferenceValue("smtp_port"), $boardPref->getPreferenceValue("smtp_encryption"))
					  ->setUsername($boardPref->getPreferenceValue("smtp_user"))
					  ->setPassword($boardPref->getPreferenceValue("smtp_pwd"));
				}

				//Create the Mailer using your created Transport
				$mailer = Swift_Mailer::newInstance($transport);
			} else if ($boardPref->getPreferenceValue("mail_type") == 2){
				//TODO add sendmail option to administation panel
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
			$message = Swift_Message::newInstance($lang['pmsubject'])
				->setFrom(array($boardPref->getPreferenceValue("board_email") => $title)) //Set the From address
				->setTo(array($userInfo->userSettings("Email") => $send))
				->setBody(pm_notify()); //set email body

			#create array for replacements.
			$replacements[$userInfo->userSettings("Email")] = array(
				'{pm-receiver}'=>$pm_data['Reciever'],
				'{pm-sender}'=>$pm_data['Sender'],
				'{pm-subject}' =>$pm_data['Sender'],
				'{boardAddr}'=>$boardAddr,
				'{pm-id}'=>$pm_data['id']
			);

			#setup mailer template.
			$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
			$mailer->registerPlugin($decorator);

			#send message out.
			//TODO: Add a failure list to this method to help administrators weed out "dead" accounts.
			$mailer->Send($message);
		}

		#display success message.
		$displayMsg = new notifySys($lang['sentpmsuccessfully'], false);
		$displayMsg->displayAjaxError("success");
	break;
	case 'deleteMsg':
		#validate PM ID.
		if((!isset($_GET['id'])) or (empty($_GET['id']))){
			$displayMsg = new notifySys($lang['nopmid'], true);
			exit($displayMsg->displayAjaxError("warning"));
		}else{
			$id = $db->filterMySQL($_GET['id']);
		}

		$pmObj = new PM($id, $logged_user); #setup PM object.
		$pmObj->DeleteMessage(); #delete defined PM.
		
		#display success message.
		$displayMsg = new notifySys($lang['delpmsuccessfully'], false);
		$displayMsg->displayAjaxError("success");
	break;
	case 'moveMsg';
		#validate PM ID.
		if((!isset($_GET['id'])) or (empty($_GET['id']))){
			$displayMsg = new notifySys($lang['nopmid'], true);
			exit($displayMsg->displayAjaxError("warning"));
		}else{
			$id = $db->filterMySQL($_GET['id']);
		}

		$pmObj = new PM($id, $logged_user); #setup PM object.
		$pmObj->ArchiveMessage(); #archive defined PM.

		#display success message.
		$displayMsg = new notifySys($lang['archpmsuccessfully'], false);
		$displayMsg->displayAjaxError("success");
	break;
	default:
	  	#get current folder location.
		if(!empty($_GET['folder'])){
			$pmFolder = $db->filterMySQL($_GET['folder']);
		}else{
			$pmFolder = "Inbox";
		}

		#see if folder name are valid.
		if (($pmFolder == "Inbox") or ($pmFolder == "Outbox") or ($pmFolder == "Archive")) {
			$pmObj = new PM(0,$logged_user); #setup PM object.
			$QuotaUsed = $pmObj->GetUsageAmount($pmFolder);

			#calculate percentage used from quota.
			if ($pmFolder == "Inbox"){
				$percentageUsed = Round(($QuotaUsed / $boardPref->getPreferenceValue("pm_quota")) * 100);
				$pmQuota = $boardPref->getPreferenceValue("pm_quota");
				$lngPmQuota = $lang['pmquota'];
			}elseif ($pmFolder == "Outbox"){
				$percentageUsed = '&#8734;';
				$pmQuota = '&#8734;';
				$lngPmQuota = $lang['pmquota'];
			}elseif ($pmFolder == "Archive"){
				$percentageUsed = Round(($QuotaUsed / $boardPref->getPreferenceValue("archive_quota")) * 100);
				$pmQuota = $boardPref->getPreferenceValue("archive_quota");
				$lngPmQuota = $lang['archivequota'];
			}else{
				$displayMsg = new notifySys($lang['invalidfolder'], true);
				$displayMsg->displayError();
			}

			#load template file.
			$tpl = new templateEngine($style, "pm-inbox_head");
			$tpl->parseTags(array(
			"TITLE" => "$title",
			"LANG-TITLE" => "$lang[pm]",
			"LANG-PMRULE" => "$lngPmQuota",
			"PMRULE" => "$pmQuota",
			"LANG-CURRENTAMOUNT" => "$lang[curquota]",
			"CURRENTAMOUNT" => "$percentageUsed",
			"LANG-INBOX" => "$lang[inbox]",
			"LANG-OUTBOX" => "$lang[outbox]",
			"LANG-ARCHIVE" => "$lang[archive]",
			"LANG-SUBJECT" => "$lang[subject]",
			"LANG-SENDER" => "$lang[sender]",
			"LANG-PMDATE" => "$lang[date]",
			"LANG-NOPM" => "$lang[nopm]"));

			#do some decision making.
			if ($QuotaUsed > 0){
				$tpl->removeBlock("noresults");
			}
			echo $tpl->outputHtml();

			#get PM list(if any).
			if($QuotaUsed > 0){

				$pmQuery = $pmObj->ListMessages($pmFolder);

				while ($pmLst = mysql_fetch_assoc($pmQuery)) {
					$pmDate = formatTime($timeFormat, $pmLst['Date'], $gmt);

					#load template file.
					$tpl = new templateEngine($style, "pm-inbox");

					#get status of pm messages.
					if ($pmLst['Read_Status'] == "old"){
						$icon = $tpl->displayPath($style)."images/old.gif";
					}else{
						$icon = $tpl->displayPath($style)."images/new.gif";
					}

					$tpl->parseTags(array(
					"READICON" => "$icon",
					"PMID" => "$pmLst[id]",
					"SUBJECT" => "$pmLst[Subject]",
					"SENDER" => "$pmLst[Sender]",
					"LANG-POSTEDBY" => "$lang[Postedby]",
					"POSTDATE" => "$pmDate"));

					echo $tpl->outputHtml();
				}
			}
			#pm inbox footer.
			$tpl = new templateEngine($style, "pm-inbox_foot");
			echo $tpl->outputHtml();

		} else {
			$displayMsg = new notifySys($lang['invalidfolder'], true);
			$displayMsg->displayError();
		}
	break;
}

?>
