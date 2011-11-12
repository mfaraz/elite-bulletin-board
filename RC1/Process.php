<?php
define('IN_EBB', true);
/**
Filename: Process.php
Last Modified: 11/11/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";
require_once FULLPATH."/includes/swift/swift_required.php";


#header template
$tpl = new templateEngine($style, "header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "&nbsp;",
    "LANG-HELP-TITLE" => "$help[nohelptitle]",
    "LANG-HELP-BODY" => "$help[nohelpbody]",
	"LANG" => "$lng",
    "LANG-INFO" => "$lang[info]",
    "LANG-CLOSE" => "$lang[close]",
    "LANG-JSDISABLED" => "$lang[jsdisabled]"));

echo $tpl->outputHtml();

//check to see if this user is a registered or not.
if ($logged_user == "guest"){
	redirect('index.php', false, 0);
}

//output top
if($logged_user != "guest"){
	$pmMsg = $userData->getNewPMCount();
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
	"LANG-PROFILE" => "$lang[profile]",
	"LANG-NOUSERNAME" => "$lang[nouser]",
	"LANG-NOPASSWORD" => "$lang[nopass]",
	"LANG-USERNAME" => "$lang[username]",
	"LANG-PASSWORD" => "$lang[pass]",
	"LANG-FORGOT" => "$lang[forgot]",
	"LANG-REMEMBERTXT" => "$lang[remembertxt]",
	"LANG-LOGIN" => "$lang[login]"));

#do some decision making.
if($groupAccess == 1){
	$tpl->removeBlock("user");
	$tpl->removeBlock("guest");
	$tpl->removeBlock("guestMenu");
	$tpl->removeBlock("login");

	#update user's activity.
	echo update_whosonline_reg($logged_user);
}elseif(($groupAccess == 2) or ($groupAccess == 3)){
	$tpl->removeBlock("admin");
	$tpl->removeBlock("guest");
	$tpl->removeBlock("guestMenu");
	$tpl->removeBlock("login");

	#update user's activity.
	echo update_whosonline_reg($logged_user);
}
#output top template file.
echo $tpl->outputHtml();

#get page mode.
if(isset($_GET['mode'])){
	$mode = var_cleanup($_GET['mode']);
}else{
	$mode = ''; 
}
#see if Board ID was declared, if not terminate any further outputting.
if((!isset($_GET['bid'])) or (empty($_GET['bid']))){
	$displayMsg = new notifySys($lang['nobid'], true);
	$displayMsg->displayError();
}else{
	$bid = $db->filterMySQL($_GET['bid']);
}

#see if post form had a page number listed for refence(reply topics only).
if(isset($_POST['page'])){
	$pg = $db->filterMySQL($_POST['page']);
}else{
	$pg = 1; 
}

#get board rules.
$db->SQL = "SELECT Post_Increment, type FROM ebb_boards WHERE id='$bid' LIMIT 1";
$boardPolicy = $db->fetchResults();


#see if user is trying to post on a category-type board.
if($boardPolicy['type'] == 1){
	redirect('index.php', false, 0);
}

//get posting rules.
$db->SQL = "select B_Post, B_Reply, B_Poll from ebb_board_access WHERE B_id='$bid'";
$boardAccess = $db->fetchResults();

switch ($mode){
	case 'topic':
	//check for topic type rules.
	$postType = $db->filterMySQL($_POST['post_type']);
		
	#see if topic includes a poll.
	$pollTopic = var_cleanup($_GET['polltopic']);

	#check permission.
	if($groupPolicy->validateAccess(1, 37) == false){
	    #see if board-based acess will veto group-based access.
		if($groupPolicy->validateAccess(0, $boardAccess['B_Post']) == false){
			$displayMsg = new notifySys($lang['nowrite'], true);
			$displayMsg->displayError();
		}
	}
	
	#see if user is trying to post an important topic, but doesn't have the correct access to.
	if(($groupPolicy->validateAccess(1, 39) == false) AND ($postType == 1)){
		$displayMsg = new notifySys($lang['noimportant'], true);
		$displayMsg->displayError();
	}

	//get form values.
	$topic = $db->filterMySQL(var_cleanup($_POST['topic']));
	$post = $db->filterMySQL(var_cleanup($_POST['post']));
	$no_smile = (isset($_POST['no_smile'])) ? $db->filterMySQL($_POST['no_smile']) : 0;
	$no_bbcode = (isset($_POST['no_bbcode'])) ? $db->filterMySQL($_POST['no_bbcode']) : 0;
	$subscribe = (isset($_POST['subscribe'])) ? $db->filterMySQL($_POST['subscribe']) : 0;

	#see if this topic has a poll.
	if($pollTopic == true){
		$question = $db->filterMySQL(var_cleanup($_POST['question']));
		$pollOtp1 = $db->filterMySQL(var_cleanup($_POST['poll_otp1']));
		$pollOtp2 = $db->filterMySQL(var_cleanup($_POST['poll_otp2']));
		$pollOtp3 = $db->filterMySQL(var_cleanup($_POST['poll_otp3']));
		$pollOtp4 = $db->filterMySQL(var_cleanup($_POST['poll_otp4']));
		$pollOtp5 = $db->filterMySQL(var_cleanup($_POST['poll_otp5']));
		$pollOtp6 = $db->filterMySQL(var_cleanup($_POST['poll_otp6']));
		$pollOtp7 = $db->filterMySQL(var_cleanup($_POST['poll_otp7']));
		$pollOtp8 = $db->filterMySQL(var_cleanup($_POST['poll_otp8']));
		$pollOtp9 = $db->filterMySQL(var_cleanup($_POST['poll_otp9']));
		$pollOtp10 = $db->filterMySQL(var_cleanup($_POST['poll_otp10']));

		if (empty($question)){
			#setup error session.
			$_SESSION['errors'] = $lang['noquestion'];

			#direct user.
			redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
		}
		if ((empty($pollOtp1)) OR (empty($pollOtp2))){
			#setup error session.
			$_SESSION['errors'] = $lang['moreoption'];

			#direct user.
			redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
		}
		if (strlen($question) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longquestion'];

			#direct user.
			redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
		}
		if(strlen($pollOtp1) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longpoll'];

			#direct user.
			redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
		}
		if(strlen($pollOtp2) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longpoll'];

			#direct user.
			redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
		}
		if(strlen($pollOtp3) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longpoll'];

			#direct user.
			redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
		}
		if(strlen($pollOtp4) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longpoll'];

			#direct user.
			redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
		}
		if(strlen($pollOtp5) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longpoll'];

			#direct user.
			redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
		}
		if(strlen($pollOtp6) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longpoll'];

			#direct user.
			redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
		}
		if(strlen($pollOtp7) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longpoll'];

			#direct user.
			redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
		}
		if(strlen($pollOtp8) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longpoll'];

			#direct user.
			redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
		}
		if(strlen($pollOtp9) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longpoll'];

			#direct user.
			redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
		}
		if(strlen($pollOtp10) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longpoll'];

			#direct user.
			redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
		}
	}

	//set the disable variables to 0 if not selected.
	if(empty($no_smile)){
		$no_smile = 0;
	}else{
		$no_smile = 1;
	}
	if(empty($no_bbcode)){
		$no_bbcode = 0;
	}else{
		$no_bbcode = 1;
	}

	//do some error checking
	if (empty($topic)){
		#setup error session.
		$_SESSION['errors'] = $lang['nosubject'];

		#direct user.
		redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
	}
	if (empty($post)){
		#setup error session.
		$_SESSION['errors'] = $lang['notopicbody'];

		#direct user.
		redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
	}
	if(strlen($topic) > 50){
		#setup error session.
		$_SESSION['errors'] = $lang['longsubject'];

		#direct user.
		redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
	}

	//spam check.
	$topic_chk = language_filter($topic, 2);
	$post_chk = language_filter($post, 2);

	//flood check.
	if (flood_check($logged_user, "posting") == 1){
		#setup error session.
		$_SESSION['errors'] = $lang['flood'];

		#direct user.
		redirect('Post.php?mode=topic&bid='.$bid.'&polltopic='.$pollTopic, false, 0);
	}

	//process request.
	$ip = detectProxy();
	$time = time();
	
	if($pollTopic == true){
		$db->SQL = "INSERT INTO ebb_topics (author, Topic, Body, Type, important, IP, Original_Date, last_update, bid, Question, disable_smiles, disable_bbcode) VALUES('$logged_user', '$topic', '$post', 'Poll', '$postType', '$ip', '$time', '$time', '$bid', '$question', '$no_smile', '$no_bbcode')";
		$db->query();
	}else{
		$db->SQL = "INSERT INTO ebb_topics (author, Topic, Body, Type, important, IP, Original_Date, bid, last_update, disable_smiles, disable_bbcode) VALUES('$logged_user', '$topic', '$post', 'Topic', '$postType', '$ip', '$time', '$bid', '$time', '$no_smile', '$no_bbcode')";
		$db->query();
 	}

	//get tid.
	$db->SQL = "SELECT tid FROM ebb_topics ORDER BY tid DESC LIMIT 1";
	$topicIDResult = $db->fetchResults();

	#get topicID.
    $tid = $topicIDResult['tid'];

	//update post link.
	$newlink = "bid=". $bid . "&amp;tid=". $tid;

	#update board & topic details.
	update_board($bid, $newlink, $logged_user);
	update_topic($tid, $newlink, $logged_user);

	#see if this is a poll topic, if so, lets add the option to the DB.
	if($pollTopic == true){
		//add poll options
		for($i=1;$i<=10;$i++){
			if ($_POST['poll_otp'.$i] == ""){} else {
				$db->SQL = "INSERT INTO ebb_poll (Poll_Option, tid) VALUES('".$db->filterMySQL(var_cleanup($_POST['poll_otp'.$i]))."', '$tid')";
				$db->query();
        	}
		}
	}

	//update user's last post.
	update_user($logged_user);

	#see if this board can allow post count increase.
	if($boardPolicy['Post_Increment'] == 1){
		//get current post count then add on to it.
		post_count($logged_user);
	}
	
	//check to see if the author wishes to recieve a email when a reply is added.
	if ($subscribe == 1){
		$db->SQL = "INSERT INTO ebb_topic_watch (username, tid, status) VALUES('$logged_user', '$tid', 'Unread')";
		$db->query();
	}
	if($groupPolicy->validateAccess(1, 26) == true){
	 	#see if user uploaded a file, if so lets assign the file to the topic.
		$db->SQL = "SELECT id FROM ebb_attachments WHERE Username='$logged_user' AND tid='0' AND pid='0'";
		$attach_ct = $db->affectedRows();
		$attach_id = $db->fetchResults();

		if($attach_ct > 0){
			#add attachment to db for listing purpose.
			$db->SQL = "UPDATE ebb_attachments SET tid='$tid' WHERE id='$attach_id[id]'";
			$db->query();

			//direct user to topic.
			redirect('viewtopic.php?bid='.$bid.'&tid='.$tid, false, 0);
		}else{
			//direct user to topic.
   			redirect('viewtopic.php?bid='.$bid.'&tid='.$tid, false, 0);
		}
	}else{
		//direct user to topic.
  		redirect('viewtopic.php?bid='.$bid.'&tid='.$tid, false, 0);
	}
	break;
	case 'reply':
		#see if Topic ID was declared, if not terminate any further outputting.
		if((!isset($_GET['tid'])) or (empty($_GET['tid']))){
			$displayMsg = new notifySys($lang['notid'], true);
			$displayMsg->displayError();
		}else{
			$tid = $db->filterMySQL($_GET['tid']);
		}

		#check permission.
		if($groupPolicy->validateAccess(1, 38) == false){
		    #see if board-based access will veto group-based access.
            if($groupPolicy->validateAccess(0, $boardAccess['B_Reply']) == false){
				$displayMsg = new notifySys($lang['nowrite'], true);
				$displayMsg->displayError();
			}
		}

		//get form values.
		$no_smile = (isset($_POST['no_smile'])) ? $db->filterMySQL(var_cleanup($_POST['no_smile'])) : 0;
		$no_bbcode = (isset($_POST['no_bbcode'])) ? $db->filterMySQL(var_cleanup($_POST['no_bbcode'])) : 0;
		$subscribe = (isset($_POST['subscribe'])) ? var_cleanup($_POST['subscribe']) : 0;
		$reply_post = $db->filterMySQL(var_cleanup($_POST['reply_post']));

		//set the disable variables to 0 if not selected.
		if(empty($no_smile)){
			$no_smile = 0;
		}else{
			$no_smile = 1; 
		}
		if(empty($no_bbcode)){
			$no_bbcode = 0;
		}else{
			$no_bbcode = 1; 
		}

		//error check
		if (empty($reply_post)){
			#setup error session.
			$_SESSION['errors'] = $lang['notopicbody'];

			#direct user.
			redirect('Post.php?mode=reply&tid='.$tid.'&bid='.$bid.'&pg='.$pg, false, 0);
		}

		//spam check.
		$post_chk = language_filter($reply_post, 2);

		//flood check.
		if (flood_check($logged_user, "posting") == 1){
			#setup error session.
			$_SESSION['errors'] = $lang['flood'];

			#direct user.
			redirect('Post.php?mode=reply&tid='.$tid.'&bid='.$bid.'&pg='.$pg, false, 0);
		}

		//process this
		$ip = detectProxy();
		$time = time();
		$db->SQL = "INSERT INTO ebb_posts (re_author, tid, bid, Body, IP, Original_Date, disable_smiles, disable_bbcode) VALUES('$logged_user', '$tid', '$bid', '$reply_post', '$ip', '$time', '$no_smile', '$no_bbcode')";
		$db->query();

		//get pid.
		$db->SQL = "SELECT pid FROM ebb_posts ORDER BY pid DESC LIMIT 1";
		$postIDResult = $db->fetchResults();

		$pid = $postIDResult['pid'];

		//get reply number
		$db->SQL = "SELECT pid FROM ebb_posts WHERE tid='$tid'";
		$reply_num = $db->affectedRows();

		$total_pages = $reply_num / $boardPref->getPreferenceValue("per_page");

		//update post link.
		if ($pg < $total_pages){
			$next = ($pg + 1);
			$newlink = "pg=". $next . "&amp;bid=". $bid . "&amp;tid=". $tid . "&amp;pid=". $pid . "#post". $pid;
			#link for header function.
			$redirect = "pg=". $next . "&bid=". $bid . "&tid=". $tid . "&pid=". $pid . "#post". $pid;
		}else{
			$newlink = "bid=". $bid . "&amp;tid=". $tid . "&amp;pid=". $pid . "#post". $pid;
			#link for header function.
			$redirect = "bid=". $bid . "&tid=". $tid . "&pid=". $pid . "#post". $pid;
		}
		#update board & topic details.
		update_board($bid, $newlink, $logged_user);
		update_topic($tid, $newlink, $logged_user);

		//update user's last post.
		update_user($logged_user);

		#see if board has disabled post count increments.
		if($boardPolicy['Post_Increment'] == 1){
			//get current post count then add on to it.
			post_count($logged_user);
		}
		
		//check to see if the author wishes to receive a email when a reply is added.
		if ($subscribe == 1){
			$db->SQL = "INSERT INTO ebb_topic_watch (username, tid, status) VALUES('$logged_user', '$tid', 'Read')";
			$db->query();
		}

		//update topic watch table to set post as new again.
		$db->SQL = "UPDATE ebb_topic_watch SET status='Unread' WHERE tid='$tid' AND username!='$logged_user'";
		$db->query();

		//gather info for email.
		$db->SQL = "SELECT username, tid FROM ebb_topic_watch WHERE tid='$tid' AND status='Unread'";
		$notify = $db->query();
		$scriberCount = $db->affectedRows();

		//see if anyone has scribed to this topic, besides the author.
		if ($scriberCount > 0){

			//grab topic info.
			$db->SQL = "SELECT re_author, bid, tid, pid FROM ebb_posts WHERE pid='$pid'";
			$topic = $db->fetchResults();

			//get topic name
			$db->SQL = "SELECT Topic FROM ebb_topics WHERE tid='$topic[tid]'";
			$name = $db->fetchResults();

			//set values for email.
			$digest_subject = "RE:".$name['Topic'];

			//get message part of email.
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
			$message = Swift_Message::newInstance($digest_subject)
				->setFrom(array($boardPref->getPreferenceValue("board_email") => $title)) //Set the From address
				->setBody(digest()); //set email body

			//setup anti-flood plugin.
			$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(100, $boardPref->getPreferenceValue("mail_antiflood")));

			while ($enotify = mysql_fetch_array($notify)) {

				//get email addresses
				$db->SQL = "SELECT Email FROM ebb_users WHERE Username='$enotify[username]'";
				$emailResult = $db->fetchResults();

				#create array for replacements.
				$replacements[$emailResult['Email']] = array(
					'{title}'=>$title,
					'{username}'=>$enotify['username'],
					'{author}'=>$topic['re_author'],
					'{topic}'=>$name['Topic'],
					'{boardAddr}'=>$boardAddr,
					'{pid}'=>$topic['pid'],
					'{tid}'=>$topic['tid']
				);

				//Set the To addresses
				$message->setTo(array($emailResult['Email'] => $enotify['username']));

				#setup mailer template.
				$decorator = new Swift_Plugins_DecoratorPlugin($replacements);
				$mailer->registerPlugin($decorator);

				#send message out.
				//TODO: Add a failure list to this method to help administrators weed out "dead" accounts.
				$mailer->send($message);

			}
		}

		//finalize attachments.
		if($groupPolicy->validateAccess(1, 26) == true){
		 	#see if user uploaded a file, if so lets assign the file to the topic.
			$db->SQL = "SELECT id from ebb_attachments WHERE Username='$logged_user' AND pid='0' AND tid='0'";
			$attach_ct = $db->affectedRows();
			$attach_id = $db->fetchResults();

			if($attach_ct > 0){
			#add attachment to db for listing purpose.
				$db->SQL = "UPDATE ebb_attachments SET pid='$pid' WHERE id='$attach_id[id]'";
				$db->query();

				//direct user to topic.
				redirect('viewtopic.php?'.$redirect, false, 0);
			}else{
				//direct user to topic.
    			redirect('viewtopic.php?'.$redirect, false, 0);
			}
		}else{
			//direct user to topic.
			redirect('viewtopic.php?'.$redirect, false, 0);
		}
	break;
	default:
		redirect('index.php', false, 0);
	}
#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();

ob_end_flush();
?>
