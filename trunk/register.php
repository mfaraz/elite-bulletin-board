<?php
define('IN_EBB', true);
/**
Filename: register.php
Last Modified: 3/15/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";
require_once FULLPATH."/includes/validation.class.php";
require_once FULLPATH."/includes/swift/swift_required.php";

$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$lang[register]",
    "LANG-HELP-TITLE" => "$help[registertitle]",
    "LANG-HELP-BODY" => "$help[registerbody]",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));
echo $tpl->outputHtml();

//output top
if($logged_user != "guest"){
	header("Location: index.php");
}

$tpl = new templateEngine($style, "top");
$tpl->parseTags(array(
	"TITLE" => "$title",
	"LANG-WELCOME" => "$lang[welcome]",
	"LANG-WELCOMEGUEST" => "$lang[welcomeguest]",
	"LOGGEDUSER" => "$logged_user",
	"LANG-LOGIN" => "$lang[login]",
	"LANG-REGISTER" => "$lang[register]",
	"LANG-LOGOUT" => "$lang[logout]",
	"NEWPM" => "",
	"LANG-CP" => "$lang[admincp]",
	"LANG-NEWPOSTS" => "$lang[newposts]",
	"ADDRESS" => "$address",
	"LANG-HOME" => "$lang[home]",
	"LANG-SEARCH" => "$lang[search]",
	"LANG-CLOSE" => "$lang[closewindow]",
	"LANG-QUICKSEARCH" => "$lang[quicksearch]",
	"LANG-ADVANCEDSEARCH" => "$lang[advsearch]",
	"LANG-HELP" => "$lang[help]",
	"LANG-MEMBERLIST" => "$lang[members]",
	"LANG-PROFILE" => "$lang[profile]",
	"LANG-USERNAME" => "$lang[username]",
	"LANG-PASSWORD" => "$lang[pass]",
	"LANG-FORGOT" => "$lang[forgot]",
	"LANG-REMEMBERTXT" => "$lang[remembertxt]",
	"LANG-LOGIN" => "$lang[login]"));

#do some decision making.
$tpl->removeBlock("user");
$tpl->removeBlock("admin");
$tpl->removeBlock("userMenu");
$tpl->removeBlock("searchBar");

#update guest's activity.
echo update_whosonline_guest();

#output top template file.
echo $tpl->outputHtml();

#see if registration is open.
if($boardPref->getPreferenceValue("allow_newusers") == 0){
	$displayMsg = new notifySys($lang['disabled'], true);
	exit($displayMsg->displayMessage());
}

//display register form.
if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = ''; 
}
switch ($action){
	case 'process':
		//get values from form.
		$email = $db->filterMySQL($_POST['email']);
		$username = $db->filterMySQL($_POST['username']);
		$password = $db->filterMySQL($_POST['password']);
		$vert_password = $db->filterMySQL($_POST['vert_password']);
		$time_zone = $db->filterMySQL($_POST['time_zone']);
		$time_format = $db->filterMySQL($_POST['time_format']);
		$pm_notice = $db->filterMySQL($_POST['pm_notice']);
		$show_email = $db->filterMySQL($_POST['show_email']);
		$ustyle = $db->filterMySQL($_POST['style']);
		$default_lang = $db->filterMySQL($_POST['default_lang']);
		$iagree = $db->filterMySQL($_POST['iagree']);
		$coppavalid = $db->filterMySQL($_POST['coppavalid']);
		$captcha = $db->filterMySQL($_POST['captcha']);
		//@TODO remove RSS from core.
		$rss1 = "http://rss.msnbc.msn.com/id/3032091/device/rss/rss.xml";
		$rss2 = "http://news.google.com/nwshp?hl=en&tab=wn&output=rss";
		$IP = detectProxy();
		
		#call validation class.
		$validate = new validation();

		//check to see if the user & email have already been used already.
		$db->SQL = "SELECT Email FROM ebb_users WHERE Email='$email'";
		$email_check = $db->affectedRows();

		$db->SQL = "SELECT Username FROM ebb_users WHERE Username='$username'";
		$username_check = $db->affectedRows();
		
		#BEGIN validation.
		if (($boardPref->getPreferenceValue("captcha") == 1) AND (empty($captcha))){
			#setup error session.
			$_SESSION['errors'] = $lang['nocaptcha'];

            #direct user.
			redirect('register.php', false, 0);
		}else if (($boardPref->getPreferenceValue("rules_status") == 1) AND ($iagree == "")){
			#setup error session.
			$_SESSION['errors'] = $lang['disagreetos'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif (($boardPref->getPreferenceValue("coppa") != 0) AND ($coppavalid == "")){
			#setup error session.
			$_SESSION['errors'] = $lang['disagreecoppa'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif ($validate->validateNumeric($ustyle) == false){
			#setup error session.
			$_SESSION['errors'] = $lang['nostyle'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif ($validate->validateAlphaNumeric($default_lang) == false){
			#setup error session.
			$_SESSION['errors'] = $lang['nolang'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif ($validate->validateNumeric($time_zone) == false){
			#setup error session.
			$_SESSION['errors'] = $lang['notimezone'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif ($time_format == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['notimeformat'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif ($pm_notice == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['nopmnotify'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif ($show_email == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['noshowemail'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif ($validate->validateAlphaNumeric($username) == false){
			#setup error session.
			$_SESSION['errors'] = $lang['nouser'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif ($validate->validateEmail($email) == false){
			#setup error session.
			$_SESSION['errors'] = $lang['invalidemail'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif ($validate->validateAlphaNumeric($password) == false){
			#setup error session.
			$_SESSION['errors'] = $lang['nopass'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif ($validate->validateAlphaNumeric($vert_password) == false){
			#setup error session.
			$_SESSION['errors'] = $lang['novertpass'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif ($vert_password !== $password){
			#setup error session.
			$_SESSION['errors'] = $lang['nomatch'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif(strlen($username) > 25){
			#setup error session.
			$_SESSION['errors'] = $lang['longusername'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif(strlen($username) < 4){
			#setup error session.
			$_SESSION['errors'] = $lang['shortusername'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif((strlen($vert_password) < 5) or (strlen($password) < 5)){
			#setup error session.
			$_SESSION['errors'] = $lang['shortpassword'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif(strlen($email) > 255){
			#setup error session.
			$_SESSION['errors'] = $lang['longemail'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif(strlen($time_format) > 14){
			#setup error session.
			$_SESSION['errors'] = $lang['longtimeformat'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif($email_check == 1){
			#setup error session.
			$_SESSION['errors'] = $lang['emailexist'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif($username_check == 1){
			#setup error session.
			$_SESSION['errors'] = $lang['usernameexist'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif ($validate->blacklistedUsernames($username) == true) {
			#setup error session.
			$_SESSION['errors'] = $lang['usernameblacklisted'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif ($validate->bannedEmails($email) == true){
			#setup error session.
			$_SESSION['errors'] = $lang['emailban'];

            #direct user.
			redirect('register.php', false, 0);
		}elseif (($boardPref->getPreferenceValue("captcha") == 1) AND ($captcha !== $_SESSION['CAPTCHA_Ans'])){
			#setup error session.
			$_SESSION['errors'] = $lang['captchanomatch'];

	        #direct user.
			redirect('register.php', false, 0);
		}else{
			//CAPTCHA check approves, remove the random value from session.
			session_destroy();
			
			#spam check.
			$username_chk = language_filter($username, 2);

		    #generate a new salt for the user's password.
			$pwdSalt = createPwdSalt();
			$pass = sha1($password.$pwdSalt);
			$time = time();

			//see if activation is set to either User or Admin.
			if($boardPref->getPreferenceValue("activation") == "User"){
				$active_stat = 0;
				$act_key = md5(makeRandomPassword());
			}elseif($boardPref->getPreferenceValue("activation") == "Admin"){
				$active_stat = 0;
				$act_key = '';
			}else{
				$active_stat = 1;
				$act_key = '';
			}
			
			#see if admin has set a group rule to new users.
			if($boardPref->getPreferenceValue("userstat") == 0){
				//add user to db.
				$db->SQL = "INSERT INTO ebb_users (Email, Username, Password, salt, Date_Joined, IP, Time_format, Time_Zone, PM_Notify, Hide_Email, Style, Language, active, act_key, rssfeed1, rssfeed2, banfeeds) VALUES('$email', '$username', '$pass', '$pwdSalt', $time, '$IP', '$time_format', '$time_zone', '$pm_notice', '$show_email', '$ustyle', '$default_lang', '$active_stat', '$act_key', '$rss1', '$rss2', '0')";
				$db->query();

				//add user to Regular Member group.
				$db->SQL = "INSERT INTO ebb_group_users (Status, gid, Username) VALUES('Active', '3', '$username')";
				$db->query();
			}else{
				//add user to db.
				$db->SQL = "INSERT INTO ebb_users (Email, Username, Password, salt, Date_Joined, IP, Time_format, Time_Zone, PM_Notify, Hide_Email, Style, Language, active, act_key, rssfeed1, rssfeed2, banfeeds) VALUES('$email', '$username', '$pass', '$pwdSalt', $time, '$IP', '$time_format', '$time_zone', '$pm_notice', '$show_email', '$style', '$default_lang', '$active_stat', '$act_key', '$rss1', '$rss2', '0')";
				$db->query();
				
				//add user to group admin requested.
				$db->SQL = "INSERT INTO ebb_group_users (Status, gid, Username) VALUES('Active', '".$boardPref->getPreferenceValue("userstat")."', '$username')";
				$db->query();
			}

			//send out email to remind user they created an account.
			require_once "lang/".$lng.".email.php";

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


			if($boardPref->getPreferenceValue("activation") == "User"){
				#build email.
				$message = Swift_Message::newInstance($lang['usersubject'])
					->setFrom(array($boardPref->getPreferenceValue("board_email") => $title)) //Set the From address
					->setTo(array($email => $username))
					->setBody(user_confirm()); //set email body

				#create array for replacements.
				$replacements[$email] = array(
					'{title}'=>$title,
					'{boardAddr}'=>$boardAddr,
					'{username}'=>$username,
					'{key}'=>$act_key
				);

				#setup mailer template.
				$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
				$mailer->registerPlugin($decorator);

				#send message out.
				//TODO: Add a failure list to this method to help administrators weed out "dead" accounts.
				$mailer->Send($message);

				#display a message.
				$displayMsg = new notifySys($lang['acctuser'], true);
				$displayMsg->displayMessage();
				
				#direct user.
				redirect('index.php', true, 5);
			}elseif($boardPref->getPreferenceValue("activation") == "Admin"){

				#build email.
				$message = Swift_Message::newInstance($lang['adminsubject'])
					->setFrom(array($boardPref->getPreferenceValue("board_email") => $title)) //Set the From address
					->setTo(array($email => $username))
					->setBody(admin_confirm()); //set email body

				#create array for replacements.
				$replacements[$email] = array(
					'{title}'=>$title,
					'{username}'=>$username,
				);

				#setup mailer template.
				$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
				$mailer->registerPlugin($decorator);

				#send message out.
				//TODO: Add a failure list to this method to help administrators weed out "dead" accounts.
				$mailer->Send($message);

				#display a message.
				$displayMsg = new notifySys($lang['acctadmin'], true);
				$displayMsg->displayMessage();
					
				#direct user.
				redirect('index.php', true, 5);
			}else{

				#build email.
				$message = Swift_Message::newInstance($lang['nonesubject'].' '.$title)
					->setFrom(array($boardPref->getPreferenceValue("board_email") => $title)) //Set the From address
					->setTo(array($email => $username))
					->setBody(none_confirm()); //set email body

				#create array for replacements.
				$replacements[$email] = array(
					'{title}'=>$title,
					'{boardAddr}'=>$boardAddr,
					'{username}'=>$username,
				);

				#setup mailer template.
				$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
				$mailer->registerPlugin($decorator);

				#send message out.
				//TODO: Add a failure list to this method to help administrators weed out "dead" accounts.
				$mailer->Send($message);

				#display a message.
				$displayMsg = new notifySys($lang['acctmade'], true);
				$displayMsg->displayMessage();
					
				#direct user.
				redirect('login.php', true, 5);
			}
		} #END Validation.
	break;
	default:
		#see if any errors were reported by register.php.
		if(isset($_SESSION['errors'])){
		    #format error(s) for the user.
			$errors = var_cleanup($_SESSION['errors']);

			#display validation message.
			$displayMsg = new notifySys($errors, false);
			$displayMsg->displayValidate();

			#destroy errors session data, its no longer needed.
			unset($_SESSION['errors']);
		}
		#TOS check.
		if($boardPref->getPreferenceValue("rules_status") == 1){
			$bRules = $boardPref->getPreferenceValue("rules");
		}else{
			$bRules = '';
		}
		
		#COPPA check.
		if($boardPref->getPreferenceValue("coppa") == 13){
			$coppaAge = $lang['coppa13'];
		}elseif($boardPref->getPreferenceValue("coppa") == 16){
			$coppaAge = $lang['coppa16'];
		}elseif($boardPref->getPreferenceValue("coppa") == 18){
			$coppaAge = $lang['coppa18'];
		}elseif($boardPref->getPreferenceValue("coppa") == 21){
			$coppaAge = $lang['coppa21'];
		}else{
			$coppaAge = 0;
		}
		
		#load up some functions.
		$timeZone = timezone_select($gmt);
		$styleForm = style_select($style);
		$language = lang_select($lang);

		#load register template file.
		$tpl = new templateEngine($style, "register");
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[register]",
		"LANG-EMAIL" => "$lang[email]",
		"LANG-USERNAME" => "$lang[username]",
		"LANG-RULE" => "$lang[nospecialchar]",
		"LANG-PASSWORD" => "$lang[pass]",
		"LANG-CORNFIRMPASSWORD" => "$lang[confirmpass]",
		"LANG-TIME" => "$lang[timezone]",
		"TIME" => "$timeZone",
		"LANG-TIMEFORMAT" => "$lang[timeformat]",
		"LANG-TIMEINFO" => "$lang[timeinfo]",
		"TIMEFORMAT" => "$timeFormat",
		"LANG-PMNOTIFY" => "$lang[pm_notify]",
		"LANG-SHOWEMAIL" => "$lang[showemail]",
		"LANG-YES" => "$lang[yes]",
		"LANG-NO" => "$lang[no]",
		"LANG-STYLE" => "$lang[style]",
		"STYLE" => "$styleForm",
		"LANG-LANGUAGE" => "$lang[defaultlang]",
		"LANGUAGE" => "$language",
		"LANG-CAPTCHA" => "$lang[captcha]",
		"LANG-CAPTCHAHELP" => "$lang[securitynotice]",
		"LANG-RELOAD" => "$lang[reloadimg]",
		"RULES" => "$bRules",
		"LANG-AGREE" => "$lang[agree]",
		"COPPA" => "$coppaAge",
		"SUBMIT" => "$lang[register]"));
		
  		#get CAPTCHA status
  		if ($boardPref->getPreferenceValue("captcha") == 0){
	  	  $tpl->removeBlock("captcha");
		}

		#get COPPA status
        if($boardPref->getPreferenceValue("coppa") == 0){
			$tpl->removeBlock("coppa");
        }

		#get board rules status
		if($boardPref->getPreferenceValue("rules_status") == 0){
			$tpl->removeBlock("rules");
		}

		echo $tpl->outputHtml();
}
//display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();

ob_end_flush();
?>
