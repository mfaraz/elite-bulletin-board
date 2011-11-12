<?php
if (!defined('IN_EBB') ) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: boardlisting.php
Last Modified: 11/11/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

class boardList{

    /**
	*getBoardList
	*
	*Will list all categories & boards.
	*
	*@modified 7/11/10
	*
	*@access public
	*/
	public function getBoardList(){
	
    	global $db, $lang, $style, $timeFormat, $gmt, $groupPolicy, $logged_user;
	    
	    #category sql.
		$db->SQL = "select id, Board from ebb_boards where type='1' ORDER BY B_Order";
		$categoryQuery = $db->query();
		
		#TO-DO: add permission check to category.

		while ($cat = mysql_fetch_assoc($categoryQuery)) {
		
			#call category template file.
			$tpl = new templateEngine($style, "index_category");
  			
			#setup tag code.
			$tpl->parseTags(array(
			  "CAT-NAME" => "$cat[Board]",
			  "LANG-BOARD" => "$lang[boards]",
			  "LANG-TOPIC" => "$lang[topics]",
			  "LANG-POST" => "$lang[posts]",
			  "LANG-LASTPOSTDATE" => "$lang[lastposteddate]"));
			echo $tpl->outputHtml();
        
		    #START BOARD
			#SQL for board query.
		    $db->SQL = "SELECT id, Board, Description, last_update, Posted_User, Post_Link FROM ebb_boards WHERE type='2' and Category='$cat[id]' ORDER BY B_Order";
			$boardQuery = $db->query();

			while ($board = mysql_fetch_assoc ($boardQuery)){

				#call board list template file.
	            $tpl = new templateEngine($style, "index_board");

				#Topic count.
				$db->SQL = "select tid from ebb_topics WHERE bid='$board[id]'";
				$topicNum = number_format($db->affectedRows());

				#Reply count.
				$db->SQL = "select pid from ebb_posts WHERE bid='$board[id]'";
				$postNum = number_format($db->affectedRows());

				#get sub-boards.
				$subBoard = $this->getSubBoard($board['id']);

				#get last post details.
				if (empty($board['last_update'])){
					$boardDate = $lang['noposts'];
					$lastPostLink = "";
				}else{
					$boardDate = formatTime($timeFormat, $board['last_update'], $gmt);
					$lastPostLink = '<a href="viewtopic.php?'.$board['Post_Link'].'">'.$lang['Postedby'].'</a>: '.$board['Posted_User'];
				}

				#get read status on board.
				#TO-DO: make this a decision block.
				$readCt = readBoardStat($board['id'], $logged_user);
				if (($readCt == 1) OR (empty($board['last_update']))){
					$icon = '<img src="'.$tpl->displayPath($style).'images/old.gif" alt="'.$lang['oldpost'].'" title="'.$lang['oldpost'].'" />';
				}else{
					$icon = '<img src="'.$tpl->displayPath($style).'images/new.gif" alt="'.$lang['newpost'].'" title="'.$lang['newpost'].'" />';
				}

				#get permission rules.
				$db->SQL = "select B_Read from ebb_board_access WHERE B_id='$board[id]'";
				$boardRule = $db->fetchResults();

				#see if user can view the board.
				if($groupPolicy->validateAccess(0, $boardRule['B_Read']) == true){

					#setup tag code.
	                $tpl->parseTags(array(
					  "POSTICON" => "$icon",
					  "BOARDID" => "$board[id]",
					  "BOARDNAME" => "$board[Board]",
					  "LANG-RSS" => "$lang[viewfeed]",
					  "BOARDDESCRIPTION" => "$board[Description]",
					  "SUBBOARDS" => "$subBoard",
					  "TOPICCOUNT" => "$topicNum",
					  "POSTCOUNT" => "$postNum",
					  "POSTDATE" => "$boardDate",
					  "POSTLINK" => "$lastPostLink"));
					#setup output variable.
					echo $tpl->outputHtml();

				}
			}
			#END BOARD

			#call board list footer template file.
			$tpl = new templateEngine($style, "index_footer");
			echo $tpl->outputHtml();
		}
	}
	
    /**
	*getSubBoardList
	*
	*Will list all sub-boards.
	*
	*@modified 2/28/11
	*
	*@param integer $boardID - Board ID to verify sub-board.
	*
	*@access public
	*/
	public function getSubBoardList($boardID){
	
		global $db, $lang, $style, $timeFormat, $gmt, $groupPolicy, $logged_user;
		
		#board sql.
		$db->SQL = "SELECT id, Board, Description, last_update, Posted_User, Post_Link FROM ebb_boards WHERE type='3' AND Category='$boardID' ORDER BY B_Order";
		$boardQuery = $db->query();
		
		#call category template file.
		$tpl = new templateEngine($style, "subboard_header");

		#setup tag code.
		$tpl->parseTags(array(
		"LANG-BOARD" => "$lang[boards]",
		"LANG-TOPIC" => "$lang[topics]",
		"LANG-POST" => "$lang[posts]",
		"LANG-LASTPOSTDATE" => "$lang[lastposteddate]"));
		echo $tpl->outputHtml();
		
		while ($board = mysql_fetch_assoc ($boardQuery)){
			#call board list template file.
	        $tpl = new templateEngine($style, "subboard");

			#Topic count.
			$db->SQL = "SELECT tid FROM ebb_topics WHERE bid='$board[id]'";
			$topicNum = $db->affectedRows();

			#Reply count.
			$db->SQL = "SELECT pid FROM ebb_posts WHERE bid='$board[id]'";
			$postNum = $db->affectedRows();

			#get sub-boards.
			$subBoard = $this->getSubBoard($board['id']);

			#get last post details.
			if (empty($board['last_update'])){
				$boardDate = $lang['noposts'];
				$lastPostLink = "";
			}else{
				$boardDate = formatTime($timeFormat, $board['last_update'], $gmt);
				$lastPostLink = '<a href="viewtopic.php?'.$board['Post_Link'].'">'.$lang['Postedby'].'</a>: '.$board['Posted_User'];
			}
			
			#get read status on board.
			#TO-DO: make this a decision block.
			$readCt = readBoardStat($board['id'], $logged_user);
			if (($readCt == 1) OR (empty($board['last_update'])) OR ($logged_user == "guest")){
				$icon = '<img src="'.$tpl->displayPath($style).'images/old.gif" alt="'.$lang['oldpost'].'" title="'.$lang['oldpost'].'" />';
			}else{
				$icon = '<img src="'.$tpl->displayPath($style).'images/new.gif" alt="'.$lang['newpost'].'" title="'.$lang['newpost'].'" />';
			}

			#get permission rules.
			$db->SQL = "select B_Read from ebb_board_access WHERE B_id='$board[id]'";
			$boardRule = $db->fetchResults();
			
			#see if user can view the board.
			if($groupPolicy->validateAccess(0, $boardRule['B_Read']) == true){
            	#setup tag code.
	            $tpl->parseTags(array(
				  "POSTICON" => "$icon",
				  "BOARDID" => "$board[id]",
				  "BOARDNAME" => "$board[Board]",
				  "LANG-RSS" => "$lang[viewfeed]",
				  "BOARDDESCRIPTION" => "$board[Description]",
				  "SUBBOARDS" => "$subBoard",
				  "TOPICCOUNT" => "$topicNum",
				  "POSTCOUNT" => "$postNum",
				  "POSTDATE" => "$boardDate",
				  "POSTLINK" => "$lastPostLink"));
				#setup output variable.
				echo $tpl->outputHtml();
			}
		}//end while

		#call board list footer template file.
		$tpl = new templateEngine($style, "subboard_footer");
		echo $tpl->outputHtml();
	}//end funct.

    /**
	*getBoardTopics
	*
	*Will list topics for current board.
	*
	*@modified 12/11/10
	*
	*@param integer $boardID - Board ID to select a board.
	*
	*@access public
	*/
	public function getBoardTopics($boardID){
	
		global $title, $db, $lang, $style, $timeFormat, $gmt, $groupPolicy, $logged_user, $boardRule, $num, $query, $rules, $pagenation;
		
		//see if user can read this board.
		if ($groupPolicy->validateAccess(0, $boardRule['B_Read']) == false){
			$boardmsg = $lang['noread'];
			$boarderr = 1;
		}elseif($num == 0){
			$boardmsg = $lang['nopost'];
			$boarderr = 1;
		}else{
			$boardmsg = '';
			$boarderr = 0;
		}
		
		#call header file.
		$tpl = new templateEngine($style, "viewboard_head");

		#setup tag code.
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$rules[Board]",
		"BID" => "$boardID",
		"LANG-ADDNEWTOPIC" => "$lang[addnewtopic]",
		"LANG-ADDNEWPOLL" => "$lang[addnewpoll]",
		"LANG-BOARD" => "$lang[boards]",
		"LANG-TOPIC" => "$lang[topics]",
		"LANG-POST" => "$lang[posts]",
		"LANG-LASTPOSTDATE" => "$lang[lastposteddate]",
		"PAGENATION" => "$pagenation",
		"LANG-TOPIC" => "$lang[topic]",
		"LANG-POSTEDBY" => "$lang[Postedby]",
		"LANG-REPLIES" => "$lang[replies]",
		"LANG-POSTVIEWS" => "$lang[views]",
		"LANG-LASTPOSTEDBY" => "$lang[lastpost]",
		"BOARDMSG" => "$boardmsg"));

		#setup decision block.

		#see if we need to display a special message.
		if($boarderr == 0){
           	$tpl->removeBlock("errors");
		}

		#permission-based check.
		if(($logged_user == "guest") AND ($groupPolicy->validateAccess(0, $boardRule['B_Poll']) == false)){
           	$tpl->removeBlock("allowpost");
           	$tpl->removeBlock("allowpoll");
		}elseif(($groupPolicy->validateAccess(1, 37) == false) AND ($groupPolicy->validateAccess(1, 35) == false)){
			$tpl->removeBlock("allowpost");
			$tpl->removeBlock("allowpoll");
		}else{
			$tpl->removeBlock("locked");
	            	
		    #new topic decision.
            if ($groupPolicy->validateAccess(0, $boardRule['B_Post']) == false){
            	$tpl->removeBlock("allowpost");
            }elseif($groupPolicy->validateAccess(1, 37) == false){
            	$tpl->removeBlock("allowpost");
            }

            #poll topic decision.
            if ($groupPolicy->validateAccess(0, $boardRule['B_Poll']) == false){
            	$tpl->removeBlock("allowpoll");
			}elseif($groupPolicy->validateAccess(1, 35) == false){
				$tpl->removeBlock("allowpoll");
			}
		}

		#output HTML.
		echo $tpl->outputHtml();
			
		while ($topics = mysql_fetch_assoc ($query)){
			
			#call viewboard template file.
			$tpl = new templateEngine($style, "viewboard");
			
			#Get date/time of topics.
			$topicDate = formatTime($timeFormat, $topics['last_update'], $gmt);
				
			#get reply count.
			$db->SQL = "select pid from ebb_posts WHERE tid='$topics[tid]'";
			$replyNum = number_format($db->affectedRows());
			$postID = $db->fetchResults();
				
			#format topic view
			$topicViews = number_format($topics['Views']);
				
			#see if any attachments are added to the topic, if so display an icon.
			$db->SQL = "select id from ebb_attachments where tid='$topics[tid]' and pid='0'";
			$attachTopic = $db->affectedRows();

			#see if any attachments are added to the reply of a topic, if so display an icon.
			$db->SQL = "select id from ebb_attachments where pid='$postID[pid]' and tid='0'";
			$attachPost = $db->affectedRows();
			if(($attachTopic == 1) or ($attachPost == 1)){
			    $attachIcon = '<img src="'.$tpl->displayPath($style).'images/attachment.png" alt="'.$lang['attachment'].'" title="'.$lang['attachment'].'" />';
			}else{
				$attachIcon = '';
			}
				
			#see if the post is new to the user or not.
			$readStatus = readTopicStat($topics['tid'], $logged_user);

			#icon setup.
			if ($topics['important'] == 1){
				$icon = $tpl->displayPath($style).'images/important.gif';
			}else{
				#see if topic is locked.
				if ($topics['Locked'] == 1){
					$icon = $tpl->displayPath($style).'images/locked_topic.gif';
				}

				#see if topic contains a poll.
				if ($topics['Type'] == "Poll"){
					$icon = $tpl->displayPath($style).'images/poll.gif';
				}elseif ($replyNum >= 15){
				    #topic is popular.
					$icon = $tpl->displayPath($style).'images/hottopic.gif';
				}elseif ($readStatus == 0){
				    #topic is new to the user.
					$icon = $tpl->displayPath($style).'images/new.gif';
				}elseif ($readStatus == 1){
				    #topic was already read by the user or the user is a guest.
					$icon = $tpl->displayPath($style).'images/old.gif';
				}
			}

			#setup tag code.
			$tpl->parseTags(array(
			"POSTINGICON" => "$icon",
			"ATTACHMENTICON" => "$attachIcon",
			"BOARDID" =>"$topics[bid]",
			"TOPICID" =>"$topics[tid]",
			"TOPICNAME" =>"$topics[Topic]",
			"AUTHOR" =>"$topics[author]",
			"LANG-REPLIES" => "$lang[repliedmsg]",
			"LANG-POSTVIEWS" => "$lang[views]",
			"REPLYCOUNT" => "$replyNum",
			"POSTVIEWS" => "$topicViews",
			"TOPICDATE" => "$topicDate",
			"POSTLINK" => "$topics[Post_Link]",
			"LANG-POSTEDUSER" => "$lang[Postedby]",
			"POSTEDUSER" => "$topics[Posted_User]"));

			echo $tpl->outputHtml();
			
		}//end while
			
		#footer here.
		$tpl = new templateEngine($style, "viewboard_foot");

		#setup tag code.
		$tpl->parseTags(array(
		"LANG-ICONGUIDE" => "$lang[iconguide]",
		"LANG-NEW" =>"$lang[newtopic]",
		"LANG-OLD" =>"$lang[oldtopic]",
		"LANG-POLL" =>"$lang[polltopic]",
		"LANG-LOCKED" =>"$lang[lockedtopic]",
		"LANG-IMPORTANT" => "$lang[importanttopic]",
		"LANG-HOTTOPIC" => "$lang[hottopic]"));

		echo $tpl->outputHtml();
	}//end funct.
	
    /**
	*getTopic
	*
	*Will obtain selected topic and parse it for the user.
	*
	*@modified 2/21/11
	*
	*@param integer $topicID - Topic ID to search for topic.
	*
	*@access public
	*/
	public function getTopic($topicID){
	
	    global $title, $db, $bid, $groupPolicy, $groupAccess, $lang, $bName, $tName, $style, $gmt, $timeFormat, $logged_user, $pg, $boardPref, $boardAddr, $pagenation;
	    
	    #check for the posting rule.
		$db->SQL = "SELECT B_Read, B_Reply, B_Poll, B_Vote FROM ebb_board_access WHERE B_id='$bid'";
		$boardRule = $db->fetchResults();
		
		if ($groupPolicy->validateAccess(0, $boardRule['B_Read']) == false){
			$error = new notifySys($lang['noread'], true);
			$error->displayError();
		}else{
            #parse template file.
			$tpl = new templateEngine($style, "viewtopic");
			
			#setup template path variable.
			$styleImgDir = $tpl->displayPath($style);

		    #setup and obtain user settings and group status.
            $userGroupPolicy = new groupPolicy($tName['author']);
            $userInfo = new user($tName['author']);
            
            #define user info variables
			$postCount = number_format($userInfo->userSettings("Post_Count"));
			$custTitle = $userInfo->userSettings("Custom_Title");
			$avatar = $userInfo->userSettings("Avatar");
			$userSig = $userInfo->userSettings("Sig");
			$warnBar = $userInfo->userWarn();
			
			#format a few things.
			$topicDate = formatTime($timeFormat, $tName['Original_Date'], $gmt);

			#get user custom title if one is made for them.
			if(empty($custTitle)){
				$customTitle = '';
			}else{
				$customTitle = $custTitle.'<br />';
			}
			
			#show avatar, if setup by user.
			if(empty($avatar)){
				$userAvatar = 'images/noavatar.gif';
			}else{
				$userAvatar = $avatar;
			}

			#format Sig or hide it if none are found.
			if(empty($userSig)){
				$sig = '';
			}else{
				$formatSig = nl2br(smiles(BBCode(language_filter($userSig, 1), true)));
				$sig = '_________________<br />'.$formatSig;
			}

			#format topic body.
            $topicBody = $tName['Body'];
            
            #see if user wish to allow smiles.
			if($tName['disable_smiles'] == 0){
				#see if board allows smiles.
				if ($bName['Smiles'] == 1){
					$topicBody = smiles($topicBody);
				}
			}

			#see if user wish to allow bbcode.
			if($tName['disable_bbcode'] == 0){
				#see if board allow BBCode formatting.
				if ($bName['BBcode'] == 1){
					$topicBody = BBCode($topicBody);
				}

				#see if board allows use of [img] tag.
				if ($bName['Image'] == 1){
					$topicBody = BBCode($topicBody, true);
					}
			}

			//censor convert.
			$topicBody = nl2br(language_filter($topicBody, 1));

            #determine rank.
			if($userGroupPolicy->groupAccessLevel() == 1){
				$rankIcon = '<img src="'.$tpl->displayPath($style).'images/adminstar.gif" alt="'.$userGroupPolicy->getGroupName().'" />';
				$rankName = $userGroupPolicy->getGroupName();
			}elseif($userGroupPolicy->groupAccessLevel() == 2){
				$rankIcon = '<img src="'.$tpl->displayPath($style).'images/modstar.gif" alt="'.$userGroupPolicy->getGroupName().'" />';
				$rankName = $userGroupPolicy->getGroupName();
			}elseif ($userGroupPolicy->groupAccessLevel() == 3){
				$rankIcon = '<img src="'.$tpl->displayPath($style).'images/fullstar.gif" alt="'.$userGroupPolicy->getGroupName().'" />';
				$rankName = $userGroupPolicy->getGroupName();
			}else{
				$rankIcon = '';
				$rankName = '';
			}
		
			#see if topic is setup to include a poll.
			if ($tName['Type'] == "Poll"){
				#check to see if a user already voted.
				$db->SQL = "SELECT tid FROM ebb_votes WHERE Username='$logged_user' AND tid='$topicID'";
				$voteStatus = $db->affectedRows();

				#display results.
				if (($voteStatus == 1) OR ($logged_user == "guest") or ($groupPolicy->validateAccess(0, $boardRule['B_Vote']) == false)){
					$poll = $this->viewResults();
				}elseif($groupPolicy->validateAccess(1, 36) == false){
					$poll = $this->viewResults();
				}else{
					#display poll.
					$poll = $this->viewPoll();
				}
			}else{
				#no poll exists, provide no results.
				$poll = '';
			}
			
			#load attachment manager.
			$attachMgr = new attachmentMgr();

			#list any attachments.
			$attachment = $attachMgr->attachmentBBCode("topic", $topicID);

			#setup tag code.
			$tpl->parseTags(array(
			  "TITLE" => "$title",
			  "LANG-TITLE" => "$bName[Board]",
			  "BID" => "$bid",
			  "TID" => "$topicID",
			  "PAGE" => "$pg",
			  "LANG-DELPROMPT" => "$lang[topiccon]",
			  "BOARDDIR" => "$boardAddr",
			  "LANG-REPLY" => "$lang[replytopicalt]",
			  "LANG-LOCKED" => "$lang[lockedtopic]",
			  "PAGENATION" => "$pagenation",
			  "LANG-IP" => "$lang[ipmod]",
			  "LANG-IPLOGGED" => "$lang[iplogged]",
			  "IP" => "$tName[IP]",
			  "POLL" => "$poll",
			  "LANG-PRINT" => "$lang[ptitle]",
			  "SUBJECT" => "$tName[Topic]",
			  "LANG-POSTED" => "$lang[postedon]",
			  "TOPIC-DATE" => "$topicDate",
			  "AUTHOR" => "$tName[author]",
			  "CUSTOMTITLE" => "$customTitle",
			  "RANK" => "$rankName",
			  "RANKICON" => "$rankIcon",
			  "AVATAR" => "$userAvatar",
			  "LANG-POSTCOUNT" => "$lang[posts]",
			  "POSTCOUNT" => "$postCount",
			  "WARNINGBAR" => "$warnBar",
			  "TOPIC" => "$topicBody",
			  "LANG-QUICKEDIT" => "$lang[edittopic]",
			  "LANG-CANCELEDIT" => "$lang[canceledit]",
			  "LANG-PROCESSINGEDIT" => "$lang[processingedit]",
			  "ATTACHMENT" => "$attachment",
			  "SIGNATURE" => "$sig"));
			  
			#hide all mod-based options if non-moderator.
			if(($groupAccess == 3) or ($logged_user == "guest")){
                   	$tpl->removeBlock("deletetopic");
                   	$tpl->removeBlock("move");
                   	$tpl->removeBlock("lock");
                   	$tpl->removeBlock("unlock");
			}
			
			#hide quote link if guest.
			if($logged_user == "guest"){
                   	$tpl->removeBlock("quote");
			}

			#see if user can reply to topic.
			if ($groupPolicy->validateAccess(0, $boardRule['B_Reply']) == false){
    			$tpl->removeBlock("reply");
			}elseif($groupPolicy->validateAccess(1, 38) == false){
    			$tpl->removeBlock("reply");
			}else{
				if ($tName['Locked'] == 0){
     				$tpl->removeBlock("locked");
				}else{
     				$tpl->removeBlock("reply");
				}
			}
			  
			#see if user is an administrator or moderator.
			if (($groupAccess == 1) OR ($groupAccess == 2)){

				#see if this user can perform a few moderator tasks.
                if($groupPolicy->validateAccess(1, 21) == false){
                   	$tpl->removeBlock("deletetopic");
				}
				
				if($groupPolicy->validateAccess(1, 23) == false){
                   	$tpl->removeBlock("move");
				}
				
				if($groupPolicy->validateAccess(1, 22) == false){
                   	$tpl->removeBlock("lock");
                   	$tpl->removeBlock("unlock");
				}else{
				    #is the topic locked or unlocked?
					if($tName['Locked'] == 1){
                   		$tpl->removeBlock("lock");
					}else{
                   		$tpl->removeBlock("unlock");
					}
				}
			
				#see if user is a mod, if so dont allow them to see admin's IP or mofiy their post.
				if(($groupAccess == 2) and ($userGroupPolicy->groupAccessLevel() == 1)){
                   	$tpl->removeBlock("viewip");
	        		$tpl->removeBlock("edit");
	        		$tpl->removeBlock("delete");
	        		$tpl->removeBlock("qedit");
				}else{
					#see  is user can view IPs.
					if($groupPolicy->validateAccess(1, 24) == false){
					    $tpl->removeBlock("viewip");
					}else{
		        		$tpl->removeBlock("iplogged");
					}

					#see if user can alter topic.
				    if($groupPolicy->validateAccess(1, 20) == false){
		        		$tpl->removeBlock("edit");
		        		$tpl->removeBlock("qedit");
					}elseif($groupPolicy->validateAccess(1, 21) == false){
		        		$tpl->removeBlock("delete");
					}
				}
			}else{
				#see if user is part of a group.
				if (($logged_user == $tName['author']) AND ($groupPolicy->validateAccess(1, 20) == true)){
      			#see if user can view their IP.

					if($groupPolicy->validateAccess(1, 24) == false){
						$tpl->removeBlock("viewip");
					}
				}elseif (($logged_user == $tName['author']) AND ($groupPolicy->validateAccess(1, 21) == true)){
					#see if user can view their IP.
					if($groupPolicy->validateAccess(1, 24) == false){
						$tpl->removeBlock("viewip");
					}
				}else{
					$tpl->removeBlock("viewip");
		           	$tpl->removeBlock("edit");
		           	$tpl->removeBlock("delete");
		           	$tpl->removeBlock("qedit");
				}
			}

			#output the results.
			echo $tpl->outputHtml();
		}
	}#end funct.
	
    /**
	*getReplies
	*
	*Will search and output any replies based on the defined topic ID.
	*
	*@modified 2/21/11
	*
	*@param integer $topicID - Topic ID to search for replies.
	*
	*@access public
	*/
	public function getReplies(){

	    global $query, $bid, $lang, $db, $groupPolicy, $groupAccess, $bName, $tName, $style, $gmt, $timeFormat, $logged_user, $boardPref, $boardAddr;
	    
	    #output any replies.
        while ($replies = mysql_fetch_assoc($query)){
		    #setup and obtain user settings and group status.
            $userGroupPolicy = new groupPolicy($replies['re_author']);
            $userInfo = new user($replies['re_author']);

            #define user info variables
			$postCount = $userInfo->userSettings("Post_Count");
			$custTitle = $userInfo->userSettings("Custom_Title");
			$avatar = $userInfo->userSettings("Avatar");
			$userSig = $userInfo->userSettings("Sig");
			$warnBar = $userInfo->userWarn();

			#format a few things.
			$replyDate = formatTime($timeFormat, $replies['Original_Date'], $gmt);
			$uPostCount = number_format($postCount);

			#get user custom title if one is made for them.
			if(empty($custTitle)){
				$customTitle = '';
			}else{
				$customTitle = $custTitle."<br />";
			}

			#show avatar, if setup by user.
			if(empty($avatar['Avatar'])){
				$userAvatar = 'images/noavatar.gif';
			}else{
				$userAvatar = $avatar;
			}

			#format Sig or hide it if none are found.
			if(empty($userSig)){
				$sig = '';
			}else{
				$formatSig = nl2br(smiles(BBCode(language_filter($userSig, 1), true)));
				$sig = '_________________<br />'.$formatSig;
			}

			#format topic body.
            $replyBody = $replies['Body'];

            #see if user wish to allow smiles.
			if($tName['disable_smiles'] == 0){
				#see if board allows smiles.
				if ($bName['Smiles'] == 1){
					$replyBody = smiles($replyBody);
				}
			}

			#see if user wish to allow bbcode.
			if($tName['disable_bbcode'] == 0){
				#see if board allow BBCode formatting.
				if ($bName['BBcode'] == 1){
					$replyBody = BBCode($replyBody);
				}

				#see if board allows use of [img] tag.
				if ($bName['Image'] == 1){
					$replyBody = BBCode($replyBody, true);
					}
			}

			//censor convert.
			$replyBody = nl2br(language_filter($replyBody, 1));

            #parse template file.
			$tpl = new templateEngine($style, "replylisting");

            #determine rank.
			if($userGroupPolicy->groupAccessLevel() == 1){
				$rankIcon = '<img src="'.$tpl->displayPath($style).'images/adminstar.gif" alt="'.$userGroupPolicy->getGroupName().'" />';
				$rankName = $userGroupPolicy->getGroupName();
			}elseif($userGroupPolicy->groupAccessLevel() == 2){
				$rankIcon = '<img src="'.$tpl->displayPath($style).'images/modstar.gif" alt="'.$userGroupPolicy->getGroupName().'" />';
				$rankName = $userGroupPolicy->getGroupName();
			}elseif ($userGroupPolicy->groupAccessLevel() == 3){
				$rankIcon = '<img src="'.$tpl->displayPath($style).'images/fullstar.gif" alt="'.$userGroupPolicy->getGroupName().'" />';
				$rankName = $userGroupPolicy->getGroupName();
			}else{
				$rankIcon = '';
				$rankName = '';
			}
			
			#load attachment manager.
			$attachMgr = new attachmentMgr();

			#list any attachments.
			$attachment = $attachMgr->attachmentBBCode("post", $replies['pid']);
			
			#setup tag code.
			$tpl->parseTags(array(
			  "BID" => "$replies[bid]",
			  "TID" => "$replies[tid]",
			  "POSTID" => "$replies[pid]",
			  "LANG-DELPROMPT" => "$lang[postcon]",
			  "BOARDDIR" => "$boardAddr",
			  "LANG-IP" => "$lang[ipmod]",
			  "LANG-IPLOGGED" => "$lang[iplogged]",
			  "IP" => "$replies[IP]",
			  "LANG-POSTEDON" => "$lang[postedon]",
			  "POSTEDON" => "$replyDate",
			  "AUTHOR" => "$replies[re_author]",
			  "CUSTOMTITLE" => "$customTitle",
			  "RANKNAME" => "$rankName",
			  "RANKICON" => "$rankIcon",
			  "AVATAR" => "$userAvatar",
			  "LANG-POSTCOUNT" => "$lang[posts]",
			  "POSTCOUNT" => "$uPostCount",
			  "WARNINGBAR" => "$warnBar",
			  "POSTBODY" => "$replyBody",
			  "LANG-QUICKEDIT" => "$lang[edittopic]",
			  "LANG-CANCELEDIT" => "$lang[canceledit]",
			  "LANG-PROCESSINGEDIT" => "$lang[processingedit]",
			  "ATTACHMENT" => "$attachment",
			  "SIGNATURE" => "$sig"));

			#hide quote link if guest.
			if($logged_user == "guest"){
                   	$tpl->removeBlock("quote");
			}
			
			#see if user is moderator.
			if (($groupAccess == 1) OR ($groupAccess == 2)){

				#see if user is a mod, if so dont allow them to see admin's IP or mofiy their post.
				if(($groupAccess == 2) and ($userGroupPolicy->groupAccessLevel() == 1)){
                   	$tpl->removeBlock("viewip");
	        		$tpl->removeBlock("edit");
	        		$tpl->removeBlock("delete");
	        		$tpl->removeBlock("qedit");
				}else{
					#see  is user can view IPs.
					if($groupPolicy->validateAccess(1, 24) == false){
					    $tpl->removeBlock("viewip");
					}else{
		        		$tpl->removeBlock("iplogged");
					}

					#see if user can alter topic.
				    if($groupPolicy->validateAccess(1, 20) == false){
		        		$tpl->removeBlock("edit");
		        		$tpl->removeBlock("qedit");
					}elseif($groupPolicy->validateAccess(1, 21) == false){
		        		$tpl->removeBlock("delete");
					}
				}
			}else{
				#see if user is part of a group.
				if (($logged_user == $replies['re_author']) AND ($groupPolicy->validateAccess(1, 20) == 1)){
     				#see if user can view their IP.
					if($groupPolicy->validateAccess(1, 24) == false){
						$tpl->removeBlock("viewip");
					}
				}elseif (($logged_user == $replies['re_author']) AND ($groupPolicy->validateAccess(1, 21) == 1)){
					#see if user can view their IP.
					if($groupPolicy->validateAccess(1, 24) == false){
						$tpl->removeBlock("viewip");
					}
				}else{
					$tpl->removeBlock("viewip");
		           	$tpl->removeBlock("edit");
		           	$tpl->removeBlock("delete");
		           	$tpl->removeBlock("qedit");
				}
			}

			#output the results.
			echo $tpl->outputHtml();

        }#end while loop
	}#end funct.

    /**
	*viewPoll
	*
	*Will generate a poll.
	*
	*@modified 12/7/09
	*
	*@access private
	*/
	private function viewPoll(){

		global $bid, $tid, $lang, $db, $style, $logged_user;

		$db->SQL = "SELECT Question FROM ebb_topics WHERE tid='$tid'";
		$questionResult = $db->fetchResults();

		//get poll options
		$db->SQL = "SELECT option_id, Poll_Option FROM ebb_poll WHERE tid='$tid'";
		$pollResults = $db->query();

		#call pollbox template file.
		$tpl = new templateEngine($style, "pollbox");

		#setup tag code.
		$tpl->parseTags(array(
		"BID" => "$bid",
		"TID" => "$tid",
		"QUESTION" => "$questionResult[Question]",
		"LANG-VOTE" => "$lang[vote]",
		"USERNAME" => "$logged_user"));
	
		#process poll options.
	    $tpl->replaceBlockTags("pollopt", $pollResults);

		echo $tpl->outputHtml();
	}

    /**
	*viewResults
	*
	*Will generate results of a poll.
	*
	*@modified 11/11/11
	*
	*@access private
	*/
	private function viewResults(){

		global $tid, $lang, $style, $db;

		//count how many votes were casted
		$db->SQL = "SELECT tid FROM ebb_votes WHERE tid='$tid'";
		$pollCount = $db->affectedRows();

		//pick up the info on the poll.
		$db->SQL = "SELECT option_id, Poll_Option FROM ebb_poll WHERE tid='$tid'";
		$pollQuestions = $db->query();

		//pick-up question of this poll.
		$db->SQL = "SELECT Question FROM ebb_topics WHERE tid='$tid'";
		$questionResult = $db->fetchResults();

		#call poll results template file.
		$tpl = new templateEngine($style, "pollresults-head");

		#setup tag code.
		$tpl->parseTags(array(
		"QUESTION" => "$questionResult[Question]"));

		echo $tpl->outputHtml();

		#generate results.
		while ($i = mysql_fetch_assoc($pollQuestions)){
			#grab results.
			$db->SQL = "SELECT tid FROM ebb_votes WHERE Vote='$i[option_id]' AND tid='$tid'";
			$pollResults = $db->affectedRows();

			#get percentage.
			if($pollCount == 0){
				$VotePercent = 0;
			}else{
				$VotePercent = Round(($pollResults / $pollCount) * 100);
			}

			#call poll results template file.
			$tpl = new templateEngine($style, "pollresults");

			#setup tag code.
			$tpl->parseTags(array(
			"POLLOPTION" => "$i[Poll_Option]",
			"PERCENTAGE" => "$VotePercent"));

			echo $tpl->outputHtml();
		}
		#call poll results template file.
		$tpl = new templateEngine($style, "pollresults-foot");

		#setup tag code.
		$tpl->parseTags(array(
		"LANG-TOTAL" => "$lang[total]",
		"TOTAL" => "$pollCount"));

		echo $tpl->outputHtml();
	}
	
    /**
	*getSubBoard
	*
	*Will list all sub-boards linked to a parent board.
	*
	*@modified 3/25/10
	*
	*@param integer $boardID - Board ID to search for any sub-boards.
	*
	*@access private
	*/
	private function getSubBoard($boardID){

		global $lang, $db, $groupPolicy;

		$db->SQL = "select id, Board from ebb_boards where type='3' and Category='$boardID' ORDER BY B_Order";
		$subBoardQuery = $db->query();
		$countSub = $db->affectedRows();

		if($countSub == 0){
			$subBoard = '';
		}else{
			$subBoard = $lang['subboards']. ":&nbsp;";
			
			#counter variable.
			$counter = 0;
			
			while ($row = mysql_fetch_assoc($subBoardQuery)){
			
				#see if we've reached the end of our query results.
				if($countSub == 1){
				    $marker = '';
				}elseif($counter < $countSub - 1){
				    $marker = ',&nbsp;';
				}else{
				    $marker = '';
				}

				#board rules sql.
				$db->SQL = "select B_Read from ebb_board_access WHERE B_id='$row[id]'";
				$boardRule = $db->fetchResults();

				#see if user can view the board.
				if($groupPolicy->validateAccess(0, $boardRule['B_Read']) == true){
					$subBoard .= "<i><a href=\"viewboard.php?bid=$row[id]\">$row[Board]</a></i>".$marker;
				}
			}#END WHILE.
		}
		return($subBoard);
	}
	
	
    /**
	*boardSelect
	*
	*A list of boards and sub-boards, used for web forms.
	*
	*@modified 4/19/10
	*
	*@access public
	*/
	public function boardSelect(){

		global $lang, $db;

		$db->SQL = "SELECT id, Board FROM ebb_boards WHERE type='2' OR type='3'";
		$boardList = $db->query();

		$boardlist = '<select name="board" class="text">
		<option value="">'.$lang['selboard'].'</option>';
		while ($boardR = mysql_fetch_assoc ($boardList)){
			$boardlist .= '<option value="'.$boardR['id'].'">'.$boardR['Board'].'</option>';
		}
		$boardlist .= '</select>';
		return ($boardlist);
	}
}//end class.
?>
