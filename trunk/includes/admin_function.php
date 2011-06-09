<?php
if (!defined('IN_EBB') ) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: admin_function.php
Last Modified: 2/18/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/


/**
*attachment_whitelist
*
*Obtains attachment extension whitelist.
*
*@modified 6/26/10
*
*/
function attachment_whitelist(){

	global $db;

	#create query.
	$db->SQL = "SELECT id, ext FROM ebb_attachment_extlist";
	$attach_q = $db->query();

	#start output.
	$attachment_list = '<select name="attachsel" class="text">';
	while ($whtLst = mysql_fetch_assoc($attach_q)){
		$attachment_list .= '<option value="'.$whtLst['id'].'">'.$whtLst['ext'].'</option>';
	}
	$attachment_list .= '</select>';
	return ($attachment_list);
}

/**
*admin_smilelisting
*
*Obtains smiles list.
*
*@modified 7/13/10
*
*/
function admin_smilelisting(){

	global $style, $title, $lang, $db;
	
	$db->SQL = "SELECT id, img_name, code FROM ebb_smiles";
	$smileQ = $db->query();

	#smile listing header.
	$tpl = new templateEngine($style, "cp-smiles_head");
	$tpl->parseTags(array(
  	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-SMILES" => "$lang[smiles]",
	"LANG-DELPROMPT" => "$lang[condel]",
	"LANG-ADDSMILES" => "$lang[addsmiles]",
	"LANG-SMILE" => "$lang[smiletbl]",
	"LANG-CODE" => "$lang[codetbl]",
	"LANG-FILENAME" => "$lang[filename]"));
	echo $tpl->outputHtml();

	while ($smilesLst = mysql_fetch_assoc($smileQ)){
		#smile listing data.
		$tpl = new templateEngine($style, "cp-smiles");
		$tpl->parseTags(array(
		"SMILEID" => "$smilesLst[id]",
		"LANG-MODIFY" => "$lang[modify]",
		"LANG-DELETE" => "$lang[del]",
		"SMILEFILENAME" => "$smilesLst[img_name]",
		"SMILECODE" => "$smilesLst[code]"));
		echo $tpl->outputHtml();
	}

	#show list of smiles installers.
	$installer = new EBBInstaller();
	$smiles = $installer->acpSmileInstaller();
	
	#smile listing footer.
	$tpl = new templateEngine($style, "cp-smiles_foot");
	$tpl->parseTags(array(
	"LANG-SMILEINSTALL" => "$lang[smileinstall]",
	"SMILEINSTALL" => "$smiles"));
	echo $tpl->outputHtml();
}

/**
*warn_log
*
*Obtains detailed list of warned users.
*
*@modified 6/26/10
*
*/
function warn_log(){

	global $db, $title, $lang, $warn_log_q, $style, $boardAddr;

	#warn log header.
	$tpl = new templateEngine($style, "cp-warnlog_head");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-WARNLOG" => "$lang[warninglist]",
	"BOARDDIR" => "$boardAddr",
	"LANG-CLEARPROMPT" => "$lang[deletewarnlogtxt]",
	"LANG-TEXT" => "$lang[warnlogtxt]",
	"LANG-DELETE" => "$lang[deletewarnlog]",
	"LANG-PROFORMEDBY" => "$lang[warnperformed]",
	"LANG-PROFORMEDTO" => "$lang[warneffecteduser]",
	"LANG-ACTION" => "$lang[warnaction]",
	"LANG-REASON" => "$lang[warnreason]"));
	echo $tpl->outputHtml();

	while($warnLst = mysql_fetch_assoc($warn_log_q)){
		#get message based on action id.
		if($warnLst['Action'] == 1){
			$action = $lang['actionraise'];
		}elseif($warnLst['Action'] == 2){
			$action = $lang['actionlowered'];
		}elseif($warnLst['Action'] == 3){
			$action = $lang['actionbanned'];
		}elseif($warnLst['Action'] == 4){
			$action = $lang['actionsuspend'];
		}else{
			$action = $lang['actionblank'];
		}
		#warn log data.
		$tpl = new templateEngine($style, "cp-warnlog");
		$tpl->parseTags(array(
		"BOARDDIR" => "$boardAddr",
		"LANG-DELCONFIRM" => "$lang[revoketext]",
		"ID" => "$warnLst[id]",
		"LANG-REVOKE" => "$lang[revokeaction]",
		"PERFORMEDBY" => "$warnLst[Authorized]",
		"PERFORMEDTO" => "$warnLst[Username]",
		"ACTION" => "$action",
		"REASON" => "$warnLst[Message]"));
		echo $tpl->outputHtml();
	}

	#warn log footer.
	$tpl = new templateEngine($style, "cp-warnlog_foot");
	echo $tpl->outputHtml();
}

/**
*admin_censorlist
*
*Obtains list of censored words or banned words.
*
*@modified 7/13/10
*
*/
function admin_censorlist(){

	global $style, $title, $lang, $db, $boardAddr;

    $db->SQL = "SELECT id, Original_Word, action FROM ebb_censor";
	$censorQ = $db->query();
	$censorCt = $db->affectedRows();

	#censorlist header.
	$tpl = new templateEngine($style, "cp-censorlist_head");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-CENSORLIST" => "$lang[censor]",
	"LANG-CENSORACTION" => "$lang[censoraction]",
	"LANG-ORIGINALWORD" => "$lang[originalword]",
	"LANG-NOCENSOR" => "$lang[nocensor]",
	"LANG-LONGCENSOR" => "$lang[longcensor]",
	"LANG-NOCENSORACTION" => "$lang[nocensoraction]",
	"LANG-EMPTYCENSOR" => "$lang[emptycensorlist]"));

	#do some decision making.
	if($censorCt > 0){
		$tpl->removeBlock("noResults");
	}
	echo $tpl->outputHtml();

    if($censorCt > 0){
		while ($censorLst = mysql_fetch_assoc($censorQ)){
			#output action.
			if($censorLst['action'] == 1){
				$censor_action = $lang['censorban'];
			}else{
				$censor_action = $lang['censorspam'];
			}

			#censorlist data.
			$tpl = new templateEngine($style, "cp-censorlist");
			$tpl->parseTags(array(
			"BOARDDIR" => "$boardAddr",
			"LANG-DELPROMPT" => "$lang[condel]",
			"CENSORID" => "$censorLst[id]",
			"LANG-DELETE" => "$lang[del]",
			"ORGINALWORD" => "$censorLst[Original_Word]",
			"DESIREDACTION" => "$censor_action"));

			echo $tpl->outputHtml();
		}
	}

	#censorlist footer.
	$tpl = new templateEngine($style, "cp-censorlist_foot");
	$tpl->parseTags(array(
	"LANG-CENSORLIST" => "$lang[censor]",
	"LANG-ADDCENSOR" => "$lang[addcensor]",
	"LANG-CENSORACTION" => "$lang[censoraction]",
	"LANG-CENSORACTIONHINT" => "$lang[censoractionhint]",
	"LANG-CENSORBAN" => "$lang[censorban]",
	"LANG-CENSORSPAM" => "$lang[censorspam]",
	"LANG-SUBMIT" => "$lang[submit]"));

	echo $tpl->outputHtml();
}

/**
*inactive_users
*
*Obtains list of inactive users.
*
*@modified 2/18/11
*
*/
function inactive_users(){

	global $style, $lang, $title, $inactive_q, $user_ct, $timeFormat, $gmt;
	
	#inactive userlist header.
	$tpl = new templateEngine($style, "cp-activateusers_head");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-ACTIVATEUSER" => "$lang[activateacct]",
	"LANG-STYLENAME" => "$lang[stylename]",
	"LANG-USERNAME" => "$lang[username]",
	"LANG-JOINDATE" => "$lang[joindate]",
	"LANG-NOINACTIVEUSER" => "$lang[noinactiveusers]"));

	#do some decision making.
	if($user_ct > 0){
		$tpl->removeBlock("noResults");
	}
	echo $tpl->outputHtml();

	#display ianctive userlist(if anything exists.
	if($user_ct > 0){
		while($iusrLst = mysql_fetch_assoc($inactive_q)){
        	#date formatting.
			$joinDate = formatTime($timeFormat, $iusrLst['Date_Joined'], $gmt);

			#inactive userlist data.
			$tpl = new templateEngine($style, "cp-activateusers");
			$tpl->parseTags(array(
			"USERID" => "$iusrLst[id]",
			"LANG-ACCEPTUSER" => "$lang[pendingaccept]",
			"LANG-DENYUSER" => "$lang[pendingdeny]",
			"USERNAME" => "$iusrLst[Username]",
			"JOINDATE" => "$joinDate"));

			echo $tpl->outputHtml();
		}
	}

	#inactive userlist footer.
	$tpl = new templateEngine($style, "cp-activateusers_foot");
	echo $tpl->outputHtml();
}

/**
*admin_stylelisting
*
*Obtains list of installed styles.
*
*@modified 7/5/10
*
*/
function admin_stylelisting(){

	global $style, $title, $lang, $db, $boardAddr;

	$db->SQL = "SELECT id, Name FROM ebb_style";
	$stylesQ = $db->query();

	#style listing header.
	$tpl = new templateEngine($style, "cp-style_head");
	$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-TITLE" => "$lang[admincp]",
	"LANG-STYLES" => "$lang[managestyle]",
	"LANG-STYLENAME" => "$lang[stylename]"));

	echo $tpl->outputHtml();

	#get list of isntalled styles.
	while ($styles = mysql_fetch_assoc($stylesQ)){
		#style listing data.
		$tpl = new templateEngine($style, "cp-style");
		$tpl->parseTags(array(
		"BOARDDIR" => "$boardAddr",
		"LANG-DELPROMPT" => "$lang[confrmuninstall]",
		"STYLEID" => "$styles[id]",
		"LANG-UNINSTALL" => "$lang[styleuninstaller]",
		"STYLENAME" => "$styles[Name]"));

		echo $tpl->outputHtml();
	}
	#style listing footer.
	$tpl = new templateEngine($style, "cp-style_foot");

	echo $tpl->outputHtml();
}

/**
*admin_banlist_ip
*
*Obtains list of banned IPs.
*
*@modified 6/26/10
*
*/
function admin_banlist_ip(){

	global $lang, $db;

   	$db->SQL = "SELECT id, ban_item FROM ebb_banlist WHERE ban_type='IP'";
	$ipQ = $db->query();
	$ipCt = $db->affectedRows();


	$admin_banlist_ip = '<select name="ipsel" class="text">';

    #see if anything exists.
	if ($ipCt == 0){
		$admin_banlist_ip .= '<option value="">'.$lang['nobanlistip'].'</option>';
	}else{
		while ($ipLst = mysql_fetch_assoc($ipQ)){
			$admin_banlist_ip .= '<option value="'.$ipLst['id'].'">'.$ipLst['ban_item'].'</option>';
		}
	}
	$admin_banlist_ip .= '</select>';
	return ($admin_banlist_ip);
}

/**
*admin_banlist_email
*
*Obtains list of banned Emails.
*
*@modified 6/26/10
*
*/
function admin_banlist_email(){

	global $lang, $db;

   	$db->SQL = "SELECT id, ban_item FROM ebb_banlist WHERE ban_type='Email'";
	$query = $db->query();
	$emlCt = $db->affectedRows();


	$admin_banlist_email = '<select name="emailsel" class="text">';

    #see if anything exists.
	if ($emlCt == 0){
		$admin_banlist_email .= '<option value="">'.$lang['nobanlistemail'].'</option>';
	}else{
		while ($emlLst = mysql_fetch_assoc($query)){
			$admin_banlist_email .= '<option value="'.$emlLst['id'].'">'.$emlLst['ban_item'].'</option>';
		}
	}
	$admin_banlist_email .= '</select>';
	return ($admin_banlist_email);
}

/**
*admin_blacklist
*
*Obtains list of blacklisted usernames.
*
*@modified 6/26/10
*
*/
function admin_blacklist(){

	global $lang, $db;

   	$db->SQL = "SELECT id, blacklisted_username FROM ebb_blacklist";
	$blkUsrQ = $db->query();
	$blkUsrCt = $db->affectedRows();

    
	$username_blacklist = "<select name=\"blkusersel\" class=\"text\">";

	#see if anything exists.
	if ($blkUsrCt == 0){
		$username_blacklist .= '<option value="">'.$lang['noblacklistednames'].'</option>';
	}else{
		while ($blkUsrLst = mysql_fetch_assoc($blkUsrQ)){
			$username_blacklist .= '<option value="'.$blkUsrLst['id'].'">'.$blkUsrLst['blacklisted_username'].'</option>';
		}
	}
	$username_blacklist .= "</select>";
	return ($username_blacklist);
}
?>
