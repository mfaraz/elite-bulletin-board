<?php
define('IN_EBB', true);
/**
Filename: acpsettings.php
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
require_once FULLPATH."/includes/validation.class.php";

if(isset($_GET['section'])){
	$section = var_cleanup($_GET['section']);
}else{
	$section = ''; 
}
#get title.
switch($section){
case 'user':
case 'save_user':
	$usercptitle = $lang['settings'].' - '.$lang['usersettings'];
	$helpTitle = $lang['usersettings'];
	$helpBody = $help['usercpbody'];
break;
case 'board':
case 'save_board':
	$usercptitle = $lang['settings'].' - '.$lang['boardsettings'];
	$helpTitle = $lang['boardsettings'];
	$helpBody = $help['boardcpbody'];
break;
case 'mail':
case 'save_mail':
	$usercptitle = $lang['settings'].' - '.$lang['mailsettings'];
	$helpTitle = $lang['mailsettings'];
	$helpBody = $help['mailcpbody'];
break;
case 'cookie':
case 'save_cookies':
	$usercptitle = $lang['settings'].' - '.$lang['cookiesettings'];
	$helpTitle = $lang['cookiesettings'];
	$helpBody = $help['cookiecpbody'];
break;
case 'attachment':
case 'save_attachment':
case 'save_attachmentlist':
	$usercptitle = $lang['settings'].' - '.$lang['attachmentsettings'];
	$helpTitle = $lang['attachmentsettings'];
	$helpBody = $help['attachcpbody'];
break;
default:
	$usercptitle = $lang['settings'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
}

#load header file.
$tpl = new templateEngine($style, "acp_header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$usercptitle",
    "LANG-HELP-TITLE" => "$helpTitle",
    "LANG-HELP-BODY" => "$helpBody",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

#see if user has access to this portion of the script.
if($groupPolicy->validateAccess(1, 7) == false){
	$error = new notifySys($lang['noaccess'], true);
	$error->displayError();
}

//output top
$pmMsg = $userData->getNewPMCount();

$tpl = new templateEngine($style, "top-acp");
$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-WELCOME" => "$lang[welcome]",
	"LOGGEDUSER" => "$logged_user",
	"LANG-LOGOUT" => "$lang[logout]",
	"NEWPM" => "$pmMsg",
	"LANG-CP" => "$lang[admincp]",
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

//display admin CP
switch ( $section ){
case 'user':
	require_once FULLPATH."/includes/acp/groupAdministration.class.php";
	
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

	#load group manager.
	$groupManager = new groupAdministration();
	$ustat = $groupManager->groupListSelector();
	
	#get setting values.
	$terms = $boardPref->getPreferenceValue("rules");
	$pmQuota = $boardPref->getPreferenceValue("pm_quota");
	$archiveQuota = $boardPref->getPreferenceValue("archive_quota");
	
	#modify user form.
	$tpl = new templateEngine($style, "cp-usersettings");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-USERSETTINGS" => "$lang[usersettings]",
	"LANG-YES" => "$lang[yes]",
	"LANG-NO" => "$lang[no]",
	"LANG-ON" => "$lang[on]",
	"LANG-OFF" => "$lang[off]",
	"LANG-NONE" => "$lang[none]",
	"LANG-USER" => "$lang[activeusers]",
	"LANG-ADMIN" => "$lang[activeadmin]",
	"LANG-AGE13" => "$lang[al13]",
	"LANG-AGE16" => "$lang[al16]",
	"LANG-AGE18" => "$lang[al18]",
	"LANG-AGE21" => "$lang[al21]",
	"LANG-TOSSTAT" => "$lang[tosstat]",
	"LANG-TOS" => "$lang[tos]",
	"TOS" => "$terms",
	"LANG-REG-STAT" => "$lang[registerstat]",
	"LANG-ACTIVE-TYPE" => "$lang[activation]",
	"LANG-USERSTAT" => "$lang[autogroupsel]",
	"USERSTAT" => "$ustat",
	"LANG-COPPAVALIDATION" => "$lang[copparule]",
	"LANG-IMGVERT" => "$lang[securityimage]",
	"LANG-GDREQ" => "$lang[gdreq]",
	"LANG-MXCHECK" => "$lang[mxcheck]",
	"LANG-MXCHECKHINT" => "$lang[mxcheckhint]",
	"LANG-WARNINGTHRESHOLD" => "$lang[warnthreshold]",
	"LANG-WARNINGTHRESHOLD-HINT" => "$lang[warnthresholdhint]",
	"LANG-PMQUOTA" => "$lang[pmquota]",
	"PMQUOTA" => "$pmQuota",
	"LANG-ARCHIVEQUOTA" => "$lang[archivequota]",
	"ARCHIVEQUOTA" => "$archiveQuota",
	"LANG-SAVESETTINGS" => "$lang[savesettings]"));

	#tos status detection
	if ($boardPref->getPreferenceValue("rules_status") == 1){
		$tpl->removeBlock("tosOff");
	}else{
  		$tpl->removeBlock("tosOn");
	}

	#registration detection.
	if($boardPref->getPreferenceValue("allow_newusers") == 1){
		$tpl->removeBlock("regOff");
	}else{
		$tpl->removeBlock("regOn");
	}

	#activation detection
	if($boardPref->getPreferenceValue("activation") == "User"){
		$tpl->removeBlock("activateNone");
		$tpl->removeBlock("activateAdmin");
	}else if($boardPref->getPreferenceValue("activation") == "Admin"){
		$tpl->removeBlock("activateNone");
		$tpl->removeBlock("activateUser");
	}else{
		$tpl->removeBlock("activateUser");
		$tpl->removeBlock("activateAdmin");
	}

	//security image detection
	if ($boardPref->getPreferenceValue("captcha") == 1){
		$tpl->removeBlock("captchaOff");
	}else{
		$tpl->removeBlock("captchaOn");
	}

	#MX Record detect.
	if($boardPref->getPreferenceValue("mx_check") == 0){
		$tpl->removeBlock("mxYes");
	}else{
		$tpl->removeBlock("mxNo");
	}
	#get threshold of warning setting.
	if($boardPref->getPreferenceValue("warning_threshold") == 30){
		$tpl->removeBlock("warn40");
		$tpl->removeBlock("warn50");
		$tpl->removeBlock("warn60");
		$tpl->removeBlock("warn70");
		$tpl->removeBlock("warn80");
		$tpl->removeBlock("warn90");
		$tpl->removeBlock("warn100");
	}
	if($boardPref->getPreferenceValue("warning_threshold") == 40){
		$tpl->removeBlock("warn30");
		$tpl->removeBlock("warn50");
		$tpl->removeBlock("warn60");
		$tpl->removeBlock("warn70");
		$tpl->removeBlock("warn80");
		$tpl->removeBlock("warn90");
		$tpl->removeBlock("warn100");
	}
	if($boardPref->getPreferenceValue("warning_threshold") == 50){
		$tpl->removeBlock("warn40");
		$tpl->removeBlock("warn30");
		$tpl->removeBlock("warn60");
		$tpl->removeBlock("warn70");
		$tpl->removeBlock("warn80");
		$tpl->removeBlock("warn90");
		$tpl->removeBlock("warn100");
	}
	if($boardPref->getPreferenceValue("warning_threshold") == 60){
		$tpl->removeBlock("warn40");
		$tpl->removeBlock("warn50");
		$tpl->removeBlock("warn30");
		$tpl->removeBlock("warn70");
		$tpl->removeBlock("warn80");
		$tpl->removeBlock("warn90");
		$tpl->removeBlock("warn100");
	}
	if($boardPref->getPreferenceValue("warning_threshold") == 70){
		$tpl->removeBlock("warn40");
		$tpl->removeBlock("warn50");
		$tpl->removeBlock("warn60");
		$tpl->removeBlock("warn30");
		$tpl->removeBlock("warn80");
		$tpl->removeBlock("warn90");
		$tpl->removeBlock("warn100");
	}
	if($boardPref->getPreferenceValue("warning_threshold") == 80){
		$tpl->removeBlock("warn40");
		$tpl->removeBlock("warn50");
		$tpl->removeBlock("warn60");
		$tpl->removeBlock("warn70");
		$tpl->removeBlock("warn30");
		$tpl->removeBlock("warn90");
		$tpl->removeBlock("warn100");
	}
	if($boardPref->getPreferenceValue("warning_threshold") == 90){
		$tpl->removeBlock("warn40");
		$tpl->removeBlock("warn50");
		$tpl->removeBlock("warn60");
		$tpl->removeBlock("warn70");
		$tpl->removeBlock("warn80");
		$tpl->removeBlock("warn30");
		$tpl->removeBlock("warn100");
	}
	if($boardPref->getPreferenceValue("warning_threshold") == 100){
		$tpl->removeBlock("warn40");
		$tpl->removeBlock("warn50");
		$tpl->removeBlock("warn60");
		$tpl->removeBlock("warn70");
		$tpl->removeBlock("warn80");
		$tpl->removeBlock("warn90");
		$tpl->removeBlock("warn30");
	}
	#COPPA rule
	if($boardPref->getPreferenceValue("coppa") == 0){
		$tpl->removeBlock("age13");
		$tpl->removeBlock("age16");
		$tpl->removeBlock("age18");
		$tpl->removeBlock("age21");
	}
	if($boardPref->getPreferenceValue("coppa") == 13){
		$tpl->removeBlock("ageNone");
		$tpl->removeBlock("age16");
		$tpl->removeBlock("age18");
		$tpl->removeBlock("age21");
	}
	if($boardPref->getPreferenceValue("coppa") == 16){
		$tpl->removeBlock("age13");
		$tpl->removeBlock("ageNone");
		$tpl->removeBlock("age18");
		$tpl->removeBlock("age21");
	}
	if($boardPref->getPreferenceValue("coppa") == 18){
		$tpl->removeBlock("age13");
		$tpl->removeBlock("age16");
		$tpl->removeBlock("ageNone");
		$tpl->removeBlock("age21");
	}
	if($boardPref->getPreferenceValue("coppa") == 21){
		$tpl->removeBlock("age13");
		$tpl->removeBlock("age16");
		$tpl->removeBlock("age18");
		$tpl->removeBlock("ageNone");
	}

	#output template file.
	echo $tpl->outputHtml();
break;
case 'save_user':
	#get form values.
	$term_stat = $db->filterMySQL($_POST['term_stat']);
	$term_msg = $db->filterMySQL($_POST['term_msg']);
	$coppa = $db->filterMySQL($_POST['coppa']);
	$ustat = $db->filterMySQL($_POST['ustat']);
	$captcha = $db->filterMySQL($_POST['captcha']);
	$mx_stat = $db->filterMySQL($_POST['mx_stat']);
	$warn_threshold = $db->filterMySQL($_POST['warnthreshold']);
	$reg_stat = $db->filterMySQL($_POST['reg_stat']);
	$active_stat = $db->filterMySQL($_POST['active_stat']);
	$pm_quota = $db->filterMySQL($_POST['pm_quota']);
	$archive_quota = $db->filterMySQL($_POST['archive_quota']);

	#error check.
	if($term_stat == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['noterm'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if (($term_stat == 1) AND (empty($term_msg))){
		#setup error session.
		$_SESSION['errors'] = $lang['notos'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if ($captcha == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nocaptcha'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if($mx_stat == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nomxcheck'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if($reg_stat == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['noreg'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if($active_stat == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['noactivation'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if($ustat == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['noustat'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if($coppa == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nocoppa'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if($warn_threshold == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nowarnthreshold'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if((!is_numeric($warn_threshold)) or ($warn_threshold < 30) or ($warn_threshold > 100)){
		#setup error session.
		$_SESSION['errors'] = $lang['invalidwarnthreshold'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if (empty($pm_quota)){
		#setup error session.
		$_SESSION['errors'] = $lang['nopmquota'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if(strlen($pm_quota) > 4){
		#setup error session.
		$_SESSION['errors'] = $lang['longpmquota'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if (empty($archive_quota)){
		#setup error session.
		$_SESSION['errors'] = $lang['nopmquota'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if(strlen($archive_quota) > 4){
		#setup error session.
		$_SESSION['errors'] = $lang['longpmquota'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	if((!is_numeric($pm_quota)) OR (!is_numeric($pm_quota))){
		#setup error session.
		$_SESSION['errors'] = $lang['invalidpmquota'];

        #direct user.
		redirect('acp/acpsettings.php?section=user', false, 0);
	}
	
	#save settings
	$boardPref->savePreferences("rules_status", $term_stat);
	$boardPref->savePreferences("rules", $term_msg);
	$boardPref->savePreferences("coppa", $coppa);
	$boardPref->savePreferences("userstat", $ustat);
	$boardPref->savePreferences("captcha", $captcha);
	$boardPref->savePreferences("mx_check", $mx_stat);
	$boardPref->savePreferences("warning_threshold", $warn_threshold);
	$boardPref->savePreferences("allow_newusers", $reg_stat);
	$boardPref->savePreferences("activation", $active_stat);
	$boardPref->savePreferences("pm_quota", $pm_quota);
	$boardPref->savePreferences("archive_quota", $archive_quota);

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Modified User Settings", $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/acpsettings.php?section=user', false, 0);
break;
case 'board':
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

	#get setting values.
	//$m_boardName = $boardPref->getPreferenceValue("board_name");
	$m_boardUrl = $boardPref->getPreferenceValue("board_url");
	$m_boardEml = $boardPref->getPreferenceValue("board_email");
	$m_websiteUrl = $boardPref->getPreferenceValue("website_url");
	$m_perPg = $boardPref->getPreferenceValue("per_page");
	$m_offlineMsg = $boardPref->getPreferenceValue("offline_msg");
	$m_infoboxMsg = $boardPref->getPreferenceValue("infobox_msg");
	$m_timeFormat = $boardPref->getPreferenceValue("timeformat");

	#load functions.
	$m_timeZone = timezone_select($boardPref->getPreferenceValue("timezone"));
	$m_style = style_select($boardPref->getPreferenceValue("default_style"));
	$m_lang = lang_select($boardPref->getPreferenceValue("default_language"));

	#board settings form.
	$tpl = new templateEngine($style, "cp-boardsettings");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-BOARDSETTINGS" => "$lang[boardsettings]",
	"LANG-ON" => "$lang[on]",
	"LANG-OFF" => "$lang[off]",
	"LANG-BOARDNAME" => "$lang[boardname]",
	"BOARDNAME" => "$title",
	"LANG-SITEADDRESS" => "$lang[sitelink]",
	"LANG-SITEADDESSTXT" => "$lang[sitelink_txt]",
	"SITEADDRESS" => "$m_websiteUrl",
	"LANG-PERPAGE" => "$lang[perpg]",
	"LANG-PERPAGEHINT" => "$lang[perpghint]",
	"PERPAGE" => "$m_perPg",
	"LANG-BOARDSTATUS" => "$lang[boardstatus]",
	"LANG-OFFMSG" => "$lang[boardoffmsg]",
	"OFFMSG" => "$m_offlineMsg",
	"LANG-BOARDADDRESS" => "$lang[boardlink]",
	"LANG-BOARDADDRESSTXT" => "$lang[boardlink_txt]",
	"BOARDADDRESS" => "$m_boardUrl",
	"LANG-BOARDEMAIL" => "$lang[boardemail]",
	"BOARDEMAIL" => "$m_boardEml",
	"LANG-ANNOUNCEMENTSTATUS" => "$lang[announcestat]",
	"LANG-ANNOUNCEMENT" => "$lang[announce]",
	"ANNOUNCEMENT" => "$m_infoboxMsg",
	"LANG-ANNOUNCERULE" => "$lang[onelineannounce]",
	"LANG-DEFAULTSTYLE" => "$lang[defaultstyle]",
	"DEFAULTSTYLE" => "$m_style",
	"LANG-DEFAULTLANGUAGE" => "$lang[defaultlang]",
	"DEFAULTLANGUAGE" => "$m_lang",
	"LANG-SPELLCHECKER" => "$lang[spellchecker]",
	"LANG-PSPELL" => "$lang[pspell]",
	"LANG-TIMEZONE" => "$lang[defaulttimezone]",
	"TIMEZONE" => "$m_timeZone",
	"LANG-TIMEFORMAT" => "$lang[defaultimtformat]",
	"LANG-TIMERULE" => "$lang[timeformat]",
	"TIMEFORMAT" => "$m_timeFormat",
	"LANG-SAVESETTINGS" => "$lang[savesettings]"));

	#board status detection
	if ($boardPref->getPreferenceValue("board_status") == 1){
		$tpl->removeBlock("boardOff");
	}else{
  		$tpl->removeBlock("boardOn");
	}

	#announcement status detection.
	if($boardPref->getPreferenceValue("infobox_status") == 1){
		$tpl->removeBlock("announcementOff");
	}else{
		$tpl->removeBlock("announcementOn");
	}

	//security image detection
	if ($boardPref->getPreferenceValue("spellcheck") == 1){
		$tpl->removeBlock("spellOff");
	}else{
		$tpl->removeBlock("spellOn");
	}

	#output template file.
	echo $tpl->outputHtml();
break;
case 'save_board':
	#get form values.
	$board_name = $db->filterMySQL($_POST['board_name']);
	$site_address = $db->filterMySQL($_POST['site_address']);
	$perpg = $db->filterMySQL($_POST['perpg']);
	$board_stat = $db->filterMySQL($_POST['board_stat']);
	$off_msg = $db->filterMySQL($_POST['off_msg']);
	$board_address = $db->filterMySQL($_POST['board_address']);
	$board_email = $db->filterMySQL($_POST['board_email']);
	$announce_stat = $db->filterMySQL($_POST['announce_stat']);
	$announce_msg = $db->filterMySQL($_POST['announce_msg']);
	$dstyle = $db->filterMySQL($_POST['style']);
	$default_lang = $db->filterMySQL($_POST['default_lang']);
	$spell_stat = $db->filterMySQL($_POST['spell_stat']);
	$default_zone = $db->filterMySQL($_POST['time_zone']);
	$default_time = $db->filterMySQL($_POST['default_time']);

	#call validation class.
	$validate = new validation();

	#error check.
	if (empty($board_name)){
		#setup error session.
		$_SESSION['errors'] = $lang['noboardname'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if (empty($site_address)){
		#setup error session.
		$_SESSION['errors'] = $lang['nositeaddress'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if (!preg_match('/http:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/', $site_address)) {
		#setup error session.
		$_SESSION['errors'] = $lang['invalidsiteaddr'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if(empty($perpg)){
		#setup error session.
		$_SESSION['errors'] = $lang['noperpg'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if($validate->validateNumeric($perpg) == false){
		#setup error session.
		$_SESSION['errors'] = $lang['invalidperpg'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if(strlen($perpg) > 3){
		#setup error session.
		$_SESSION['errors'] = $lang['longperpg'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if (($board_stat == 0) AND (empty($off_msg))){
		#setup error session.
		$_SESSION['errors'] = $lang['noclosemsg'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if (empty($board_address)){
		#setup error session.
		$_SESSION['errors'] = $lang['noboardaddress'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if (!preg_match('/http:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/', $board_address)) {
		#setup error session.
		$_SESSION['errors'] = $lang['invalidboardaddr'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if (empty($board_email)){
		#setup error session.
		$_SESSION['errors'] = $lang['noemail'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if($validate->validateEmail($board_email) == false){
		#setup error session.
		$_SESSION['errors'] = $lang['invalidemail'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if (($announce_stat == 1) AND (empty($announce_msg))){
		#setup error session.
		$_SESSION['errors'] = $lang['noannounce'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if (empty($dstyle)){
		#setup error session.
		$_SESSION['errors'] = $lang['nostyle'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if (empty($default_lang)){
		#setup error session.
		$_SESSION['errors'] = $lang['nolang'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if ($spell_stat == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nospellchecker'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if ($default_zone == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['notimezone'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if (empty($default_time)){
		#setup error session.
		$_SESSION['errors'] = $lang['notimeformat'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if(strlen($board_name) > 50){
		#setup error session.
		$_SESSION['errors'] = $lang['longboardname'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if(strlen($site_address) > 255){
		#setup error session.
		$_SESSION['errors'] = $lang['longsiteaddress'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if(strlen($board_address) > 255){
		#setup error session.
		$_SESSION['errors'] = $lang['longboardaddress'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if(strlen($board_email) > 255){
		#setup error session.
		$_SESSION['errors'] = $lang['longboardemail'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if(strlen($off_msg) > 255){
		#setup error session.
		$_SESSION['errors'] = $lang['longoffmsg'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if(strlen($announce_msg) > 255){
		#setup error session.
		$_SESSION['errors'] = $lang['longannouce'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}
	if(strlen($default_time) > 14){
		#setup error session.
		$_SESSION['errors'] = $lang['longtimeformat'];

        #direct user.
		redirect('acp/acpsettings.php?section=board', false, 0);
	}

	#save settings
	$boardPref->savePreferences("board_name", $board_name);
	$boardPref->savePreferences("board_url", $board_address);
	$boardPref->savePreferences("board_email", $board_email);
	$boardPref->savePreferences("website_url", $site_address);
	$boardPref->savePreferences("per_page", $perpg);
	$boardPref->savePreferences("offline_msg", $off_msg);
	$boardPref->savePreferences("infobox_msg", $announce_msg);
	$boardPref->savePreferences("timeformat", $default_time);
	$boardPref->savePreferences("board_status", $board_stat);
	$boardPref->savePreferences("infobox_status", $announce_stat);
	$boardPref->savePreferences("spellcheck", $spell_stat);
	$boardPref->savePreferences("timezone", $default_zone);
	$boardPref->savePreferences("default_style", $dstyle);
	$boardPref->savePreferences("default_language", $default_lang);

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Modified Board Settings", $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/acpsettings.php?section=board', false, 0);
break;
case 'mail':
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

	#get setting values.
	$smtpServer = $boardPref->getPreferenceValue("smtp_server");
	$smtpPort = $boardPref->getPreferenceValue("smtp_port");
	$smtpUser = $boardPref->getPreferenceValue("smtp_user");
	$smtpPass = $boardPref->getPreferenceValue("smtp_pwd");

	#modify user form.
	$tpl = new templateEngine($style, "cp-mailsettings");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-MAILSETTINGS" => "$lang[mailsettings]",
	"LANG-MAILTYPE" => "$lang[mailtype]",
	"LANG-MAIL" => "$lang[mailreg]",
	"LANG-SMTP" => "$lang[mailsmtp]",
	"LANG-HOST" => "$lang[smtphost]",
	"HOST" => "$smtpServer",
	"LANG-PORT" => "$lang[smtpport]",
	"PORT" => "$smtpPort",
	"LANG-USERNAME" => "$lang[smtpuser]",
	"USERNAME" => "$smtpUser",
	"LANG-PASSWORD" => "$lang[smtppass]",
	"PASSWORD" => "$smtpPass",
	"LANG-SAVESETTINGS" => "$lang[savesettings]"));

	#mail type detection
	if ($boardPref->getPreferenceValue("mail_type") == 1){
		$tpl->removeBlock("smtp");
	}else{
  		$tpl->removeBlock("mail");
	}

	#output template file.
	echo $tpl->outputHtml();
break;
case 'save_mail':
	#get form values.
	$mail_type = $db->filterMySQL($_POST['mail_type']);
	$smtp_host = $db->filterMySQL($_POST['smtp_host']);
	$smtp_port = $db->filterMySQL($_POST['smtp_port']);
	$smtp_user = $db->filterMySQL($_POST['smtp_user']);
	$smtp_pass = $db->filterMySQL($_POST['smtp_pass']);

	#error check.
	if($mail_type == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nomailrule'];

        #direct user.
		redirect('acp/acpsettings.php?section=mail', false, 0);
	}
	if($mail_type == 0){
		if(empty($smtp_host)){
			#setup error session.
			$_SESSION['errors'] = $lang['nosmtphost'];

	        #direct user.
			redirect('acp/acpsettings.php?section=mail', false, 0);
		}
		if(empty($smtp_port)){
			#setup error session.
			$_SESSION['errors'] = $lang['nosmtpport'];

	        #direct user.
			redirect('acp/acpsettings.php?section=mail', false, 0);
		}
		if(empty($smtp_user)){
			#setup error session.
			$_SESSION['errors'] = $lang['nosmtpuser'];

	        #direct user.
			redirect('acp/acpsettings.php?section=mail', false, 0);
		}
		if(empty($smtp_pass)){
			#setup error session.
			$_SESSION['errors'] = $lang['nosmtppass'];

	        #direct user.
			redirect('acp/acpsettings.php?section=mail', false, 0);
		}
		if(strlen($smtp_host) > 255){
			#setup error session.
			$_SESSION['errors'] = $lang['longsmtphost'];

	        #direct user.
			redirect('acp/acpsettings.php?section=mail', false, 0);
		}
		if(strlen($smtp_port) > 4){
			#setup error session.
			$_SESSION['errors'] = $lang['longsmtpport'];

	        #direct user.
			redirect('acp/acpsettings.php?section=mail', false, 0);
		}
		if(strlen($smtp_user) > 255){
			#setup error session.
			$_SESSION['errors'] = $lang['longusername'];

	        #direct user.
			redirect('acp/acpsettings.php?section=mail', false, 0);
		}
		if(strlen($smtp_pass) > 255){
			#setup error session.
			$_SESSION['errors'] = $lang['longsmtppwd'];

	        #direct user.
			redirect('acp/acpsettings.php?section=mail', false, 0);
		}
	}

	#save settings
    $boardPref->savePreferences("mail_type", $mail_type);
	$boardPref->savePreferences("smtp_server", $smtp_host);
	$boardPref->savePreferences("smtp_port", $smtp_port);
	$boardPref->savePreferences("smtp_user", $smtp_user);
	$boardPref->savePreferences("smtp_pwd", $smtp_pass);

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Modified Mail Settings", $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/acpsettings.php?section=mail', false, 0);
break;
case 'cookie':
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

	#get setting values.
	$cookiesPath = $boardPref->getPreferenceValue("cookie_path");
	$cookiesDomain = $boardPref->getPreferenceValue("cookie_domain");

	#modify user form.
	$tpl = new templateEngine($style, "cp-cookiesettings");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-COOKIESETTINGS" => "$lang[cookiesettings]",
	"LANG-COOKIEDOMAIN" => "$lang[cookiedomain]",
	"LANG-COOKIEDOMAINTXT" => "$lang[cookiedomain_txt]",
	"COOKIEDOMAIN" => "$cookiesDomain",
	"LANG-COOKIEPATH" => "$lang[cookiepath]",
	"LANG-COOKIEPATHTXT" => "$lang[cookiepath_txt]",
	"COOKIEPATH" => "$cookiesPath",
	"LANG-COOKIESECURE" => "$lang[cookiesecure]",
	"LANG-SSL" => "$lang[ssl]",
	"LANG-ENABLE" => "$lang[enable]",
	"LANG-DISABLE" => "$lang[disable]",
	"LANG-SAVESETTINGS" => "$lang[savesettings]"));

 	#ssl detection status
	if ($boardPref->getPreferenceValue("cookie_secure") == 1){
		$tpl->removeBlock("sslDisabled");
	}else{
  		$tpl->removeBlock("sslEnabled");
	}

	#output template file.
	echo $tpl->outputHtml();
break;
case 'save_cookies':
	#get form values.
	$cookie_domain = $db->filterMySQL($_POST['cookie_domain']);
	$cookie_path = $db->filterMySQL($_POST['cookie_path']);
	$secure_stat = $db->filterMySQL($_POST['secure_stat']);

	#error check.
	if(empty($cookie_domain)){
		#setup error session.
		$_SESSION['errors'] = $lang['nocookiedomain'];

        #direct user.
		redirect('acp/acpsettings.php?section=cookie', false, 0);
	}
	if (empty($cookie_path)){
		#setup error session.
		$_SESSION['errors'] = $lang['nocookiepath'];

        #direct user.
		redirect('acp/acpsettings.php?section=cookie', false, 0);
	}
	if ($secure_stat == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nocookiesecure'];

        #direct user.
		redirect('acp/acpsettings.php?section=cookie', false, 0);
	}
	if(strlen($cookie_domain) > 255){
		#setup error session.
		$_SESSION['errors'] = $lang['longcookiedomain'];

        #direct user.
		redirect('acp/acpsettings.php?section=cookie', false, 0);
	}
	if(strlen($cookie_path) > 255){
		#setup error session.
		$_SESSION['errors'] = $lang['longcookiepath'];

        #direct user.
		redirect('acp/acpsettings.php?section=cookie', false, 0);
	}

	#save settings
	$boardPref->savePreferences("cookie_path", $cookie_path);
	$boardPref->savePreferences("cookie_domain", $cookie_domain);
	$boardPref->savePreferences("cookie_secure", $secure_stat);

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Modified Cookie Settings", $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/acpsettings.php?section=cookie', false, 0);
break;
case 'attachment':
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
	
	#load attachment whitelist.
	$attachmentList = attachment_whitelist();

	#get setting values.
	$attachmentQuota = $boardPref->getPreferenceValue("attachment_quota");

	#modify user form.
	$tpl = new templateEngine($style, "cp-attachmentsettings");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-ATTACHMENTSETTINGS" => "$lang[attachmentsettings]",
	"LANG-YES" => "$lang[yes]",
	"LANG-NO" => "$lang[no]",
	"LANG-ATTACHMENTQUOTA" => "$lang[attachmentquota]",
	"LANG-ATTACHMENTQUOTATXT" => "$lang[attachmentquotahint]",
	"ATTACHMENTQUOTA" => "$attachmentQuota",
	"LANG-DOWNLOADRULE" => "$lang[guestdownload]",
	"LANG-SAVESETTINGS" => "$lang[savesettings]",
	"LANG-ATTACHMENTWHITELIST" => "$lang[attachmentwhitelist]",
	"LANG-ATTACHMENTWHITELISTHINT" => "$lang[extensionhint]",
	"LANG-ATTACHMENTWHITELISTTXT" => "$lang[attachmentwhitelisthint]",
	"LANG-ADDEXTENSION" => "$lang[addextension]",
	"LANG-REMOVEATTACHMENTWHITELIST" => "$lang[removeattachwhitelist]",
	"LANG-REMOVEATTACHMENTWHITELISTHINT" => "$lang[removeattachwhitelisthint]",
	"EXTENSIONLIST" => "$attachmentList",
	"LANG-REMOVEEXTENSION" => "$lang[removeextension]"));

 	#ssl detection status
	if ($boardPref->getPreferenceValue("allow_guest_downloads") == 1){
		$tpl->removeBlock("disallowGuestDwnld");
	}else{
  		$tpl->removeBlock("allowGuestDwnld");
	}

	#output template file.
	echo $tpl->outputHtml();
break;
case 'save_attachment':
	#get form values.
	$attach_quota = $db->filterMySQL($_POST['attach_quota']);
	$download_stat = $db->filterMySQL($_POST['download_stat']);

	#error check.
	if(empty($attach_quota)){
		#setup error session.
		$_SESSION['errors'] = $lang['noattachquota'];

        #direct user.
		redirect('acp/acpsettings.php?section=attachment', false, 0);
	}
	if($download_stat == ""){
		#setup error session.
		$_SESSION['errors'] = $lang['nodwnloadrule'];

        #direct user.
		redirect('acp/acpsettings.php?section=attachment', false, 0);
	}
	if($attach_quota > 102400000){
		#setup error session.
		$_SESSION['errors'] = $lang['attachquotahigh'];

        #direct user.
		redirect('acp/acpsettings.php?section=attachment', false, 0);
	}
	if(!is_numeric($attach_quota)){
		#setup error session.
		$_SESSION['errors'] = $lang['invalidattach'];

        #direct user.
		redirect('acp/acpsettings.php?section=attachment', false, 0);
	}

	#save settings
	$boardPref->savePreferences("attachment_quota", $attach_quota);
	$boardPref->savePreferences("allow_guest_downloads", $download_stat);

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Modified Attachment Settings", $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/acpsettings.php?section=attachment', false, 0);
break;
case 'save_attachmentlist';
	#see if cmd is called.
	if(!isset($_POST['cmd'])){
		$error = new notifySys($lang['nocmdid'], true);
		$error->displayError();
	}else{
		$cmd = $db->filterMySQL($_POST['cmd']);
	}

	#see how to proces the data.
	if($cmd == "addext"){
		#get form values.
		$add_ext = $db->filterMySQL($_POST['add_ext']);

		#error check.
		if(empty($add_ext)){
			#setup error session.
			$_SESSION['errors'] = $lang['noext'];

	        #direct user.
			redirect('acp/acpsettings.php?section=attachment', false, 0);
		}
		if(strlen($add_ext) > 100){
			#setup error session.
			$_SESSION['errors'] = $lang['longext'];

	        #direct user.
			redirect('acp/acpsettings.php?section=attachment', false, 0);
		}

		#check for exact matches.
		$db->SQL = "SELECT ext FROM ebb_attachment_extlist WHERE ext='$add_ext'";
		$ext_match = $db->AffectedRows();

		if($ext_match == 1){
			#setup error session.
			$_SESSION['errors'] = $lang['extexist'];

	        #direct user.
			redirect('acp/acpsettings.php?section=attachment', false, 0);
		}

		#insert extension into database.
		$db->SQL = "INSERT INTO ebb_attachment_extlist (ext) VALUES('$add_ext')";
		$db->query();

		#log this into our audit system.
		$acpAudit = new auditSystem();
		$acpAudit->logAction("Added ".$add_ext." to Attachment Whitelist", $acpUsr, time(), detectProxy());

		//bring user back to board section
		redirect('acp/acpsettings.php?section=attachment', false, 0);
	}elseif($cmd == "removeext"){
		$attachsel = $db->filterMySQL($_POST['attachsel']);

		#error check.
		if($attachsel == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['noextselected'];

	        #direct user.
			redirect('acp/acpsettings.php?section=attachment', false, 0);
		}

		#see if theres at least 5 extensions in the whitelist.
		$db->SQL = "SELECT ext FROM ebb_attachment_extlist";
		$ext_ct = $db->AffectedRows();

		if($ext_ct <= 5){
			#setup error session.
			$_SESSION['errors'] = $lang['extlow'];

	        #direct user.
			redirect('acp/acpsettings.php?section=attachment', false, 0);
		}

		//remove extension from whitelist
		$db->SQL = "DELETE FROM ebb_attachment_extlist WHERE id='$attachsel'";
		$db->query();

		#log this into our audit system.
		$acpAudit = new auditSystem();
		$acpAudit->logAction("Deleted an extension from Attachment Whitelist", $acpUsr, time(), detectProxy());

		//bring user back to board section
		redirect('acp/acpsettings.php?section=attachment', false, 0);
	}else{
		$error = new notifySys($lang['invalidopt'], true);
		$error->displayError();
	}
break;
default:
	#go to main menu.
	redirect('acp/index.php', false, 0);
break;
}

#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();
ob_end_flush();
?>
