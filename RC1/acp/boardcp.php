<?php
define('IN_EBB', true);
/**
Filename: boardcp.php
Last Modified: 7/25/2011

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
require_once FULLPATH."/includes/acp/boardAdministration.class.php";

if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = ''; 
}
#get title.
switch($action){
case 'board_order':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 1) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$boardcp = $lang['boardmenu'].' - '.$lang['boardsetup'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'board_add':
case 'board_add_process':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 1) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$boardcp = $lang['boardmenu'].' - '.$lang['addnew'];
	$helpTitle = $help['addboardtitle'];
	$helpBody = $help['addboardbody'];
break;
case 'board_modify':
case 'board_modify_process':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 1) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$boardcp = $lang['boardmenu'].' - '.$lang['modifyboard'];
	$helpTitle = $help['addboardtitle'];
	$helpBody = $help['addboardbody'];
break;
case 'board_delete':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 1) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$boardcp = $lang['boardmenu'].' - '.$lang['delboard'];
	$helpTitle = $help['nohelptitle'];
	$helpBody = $help['nohelpbody'];
break;
case 'prune':
case 'prune_process':
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 2) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$boardcp = $lang['boardmenu'].' - '.$lang['prune'];
	$helpTitle = $help['pruneboardtitle'];
	$helpBody = $help['pruneboardbody'];
break;
default:
	#see if user has access to this portion of the script.
	if($groupPolicy->validateAccess(1, 1) == false){
		$error = new notifySys($lang['noaccess'], true);
		$error->displayError();
	}
	$boardcp = $lang['boardmenu'].' - '.$lang['boardsetup'];
	$helpTitle = $help['boardmanagetitle'];
	$helpBody = $help['boardmanagebody'];
break;
}

$tpl = new templateEngine($style, "acp_header");
$tpl->parseTags(array(
    "TITLE" => "$title",
    "PAGETITLE" => "$boardcp",
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
	"LANG-WELCOME" => "$txt[welcome]",
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
switch( $action ){
case 'board_order':
	#see if a board id was defined
	if(isset($_GET['id'])){
		$id = $db->filterMySQL(var_cleanup($_GET['id']));
	}else{
		$error = new notifySys($lang['nobid'], true);
		$error->displayError();
	}

	#see if board type was defined.
	if(isset($_GET['type'])){
		$type = $db->filterMySQL(var_cleanup($_GET['type']));
	}else{
		$error = new notifySys($lang['noboardtype'], true);
		$error->displayError();
	}

	$o = $db->filterMySQL(var_cleanup($_GET['o']));

	#set error values to default.
	$error = 0;
	$errormsg = '';
	if ($type == 1){
		$cat = 0;
	}else{
		$cat = $db->filterMySQL(var_cleanup($_GET['cat']));
	}
	$db->SQL = "SELECT B_Order, Board FROM ebb_boards WHERE id='$id' AND type='$type'";
	$order_r = $db->fetchResults();

	if ($o == "up"){
		#error check.
		if ($order_r['B_Order'] == 1){
			#setup error session.
			$_SESSION['errors'] = $lang['ontop'];

            #direct user.
			redirect('acp/boardcp.php', false, 0);
		}else{
		    #move every else down & set current board up.
			$newOrder = $order_r['B_Order'] - 1;
			$moveUp = $newOrder + 1;

			//move other boards.
			$db->SQL = "UPDATE ebb_boards SET B_Order='$moveUp' WHERE B_Order='$newOrder' AND Category='$cat' AND type='$type' AND id!='$id'";
			$db->query();

			//move current board.
			$db->SQL = "UPDATE ebb_boards SET B_Order='$newOrder' WHERE id='$id'";
			$db->query();

			#log this into our audit system.
			$acpAudit = new auditSystem();
			$acpAudit->logAction("Repositioned ".$order_r['Board'], $acpUsr, time(), detectProxy());

			//bring user back to board section
			redirect('acp/boardcp.php', false, 0);
		}
	}

	#set board down a number.
	if ($o == "down"){
		$db->SQL = "SELECT id FROM ebb_boards WHERE Category='$cat'";
		$ct = $db->affectedRows();

		//see if user is trying to go lower than they can.
		if($order_r['B_Order'] == $ct){
			#setup error session.
			$_SESSION['errors'] = $lang['onbottom'];

            #direct user.
			redirect('acp/boardcp.php', false, 0);
		}else{
            #move every else down & set current board up.
			$newOrder = $order_r['B_Order'] + 1;
			$moveDwn = $neworder - 1;

			//move other boards.
			$db->SQL = "UPDATE ebb_boards SET B_Order='$moveDwn' WHERE B_Order='$newOrder' AND Category='$cat' AND type='$type' AND id!='$id'";
			$db->query();

   			//move current board.
			$db->SQL = "UPDATE ebb_boards SET B_Order='$neworder' WHERE id='$id'";
			$db->query();

			#log this into our audit system.
			$acpAudit = new auditSystem();
			$acpAudit->logAction("Repositioned ".$order_r['Board'], $acpUsr, time(), detectProxy());

			//bring user back to board section
			redirect('acp/boardcp.php', false, 0);
		}
	}
break;
case 'board_add':
	if(isset($_GET['type'])){
		$type = $db->filterMySQL(var_cleanup($_GET['type']));
	}else{
		$error = new notifySys($lang['noboardtype'], true);
		$error->displayError();
	}

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

	#call board administrator class.
	$boardManager = new boardAdministration();
	if($type == 2){
		$parentBoardList = $boardManager->parentBoardSelection("parent");
	}else{
	    $parentBoardList = $boardManager->parentBoardSelection("child");
	}

	#new board form.
	$tpl = new templateEngine($style, "cp-newboard");
	$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[admincp]",
		"LANG-ADDPARENTBOARD" => "$lang[newparentboard]",
		"LANG-ADDBOARD" => "$lang[newboard]",
		"LANG-ADDCHILDBOARD" => "$lang[newsubboard]",
		"BOARDTYPE" => "$type",
		"LANG-BOARDNAME" => "$lang[boardname]",
		"LANG-DESCRIPTION" => "$lang[description]",
		"LANG-BOARDPERMISSION" => "$lang[boardpermissions]",
		"LANG-BOARDSETTINGS" => "$lang[boardsettings]",
		"LANG-ON" => "$lang[on]",
		"LANG-OFF" => "$lang[off]",
		"LANG-READACCESS" => "$lang[boardread]",
		"LANG-WRITEACCESS" => "$lang[boardwrite]",
		"LANG-REPLYACCESS" => "$lang[boardreply]",
		"LANG-VOTEACCESS" => "$lang[boardvote]",
		"LANG-POLLACCESS" => "$lang[boardpoll]",
		"LANG-ACCESS-PRIVATE" => "$lang[access_private]",
		"LANG-ACCESS-ADMINS" => "$lang[access_admin]",
		"LANG-ACCESS-ADMINSMODS" => "$lang[access_admin_mod]",
		"LANG-ACCESS-ALL" => "$lang[access_all]",
		"LANG-ACCESS-NONE" => "$lang[access_none]",
		"LANG-ACCESS-REG" => "$lang[access_users]",
		"LANG-PARENT" => "$lang[parentboard]",
		"PARENT" => "$parentBoardList",
		"LANG-POSTINCREMENT" => "$lang[postincrement]",
		"LANG-YES" => "$lang[yes]",
		"LANG-NO" => "$lang[no]",
		"LANG-BBCODE" => "$lang[bbcode]",
		"LANG-SMILE" => "$post[smiles]",
		"LANG-IMG" => "$lang[img]",
		"LANG-SUBMIT" => "$lang[addboard]"));

	#do some decision making.
	if($type == 1){
		$tpl->removeBlock("boardTitle");
		$tpl->removeBlock("childBoardTitle");
		$tpl->removeBlock("childBoard");
	}else if($type == 2){
		$tpl->removeBlock("parentBoardTitle");
		$tpl->removeBlock("childBoardTitle");
		$tpl->removeBlock("parentBoard");
	}else{
		$tpl->removeBlock("parentBoardTitle");
		$tpl->removeBlock("boardTitle");
		$tpl->removeBlock("parentBoard");
	}
	echo $tpl->outputHtml();
break;
case 'board_add_process':
	if(isset($_GET['type'])){
		$type = $db->filterMySQL(var_cleanup($_GET['type']);
	}else{
		$error = new notifySys($lang['noboardtype'], true);
		$error->displayError();;
	}

	#process based on board type.
	if ($type == 1){
		$board_name = $db->filterMySQL(var_cleanup($_POST['board_name']));
		$description = 'null';
		$readaccess = $db->filterMySQL(var_cleanup($_POST['readaccess']);
		$writeaccess = 4;
		$replyaccess = 4;
		$voteaccess = 4;
		$pollaccess = 4;
		$catsel = 0;
		$bbcode = 0;
		$increment = 0;
		$smiles = 0;
		$img = 0;

		#error check.
		if(empty($board_name)){
			#setup error session.
			$_SESSION['errors'] = $lang['boardnameerror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if(strlen($board_name) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longboardname'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if ($readaccess == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['noreadsetting'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}

		//get board order
		$db->SQL = "SELECT id FROM ebb_boards where type='1'";
		$ct = $db->affectedRows();

		$board_order = $ct + 1;
	}else{
		$board_name = $db->filterMySQL(var_cleanup($_POST['board_name']));
		$description = $db->filterMySQL(var_cleanup($_POST['description']));
		$readaccess = $db->filterMySQL(var_cleanup($_POST['readaccess']));
		$writeaccess = $db->filterMySQL(var_cleanup($_POST['writeaccess']));
		$replyaccess = $db->filterMySQL(var_cleanup($_POST['replyaccess']));
		$voteaccess = $db->filterMySQL(var_cleanup($_POST['voteaccess']));
		$pollaccess = $db->filterMySQL(var_cleanup($_POST['pollaccess']));
		$catsel = $db->filterMySQL(var_cleanup($_POST['catsel']));
		$increment = $db->filterMySQL(var_cleanup($_POST['increment']));
		$bbcode = $db->filterMySQL(var_cleanup($_POST['bbcode']));
		$smiles = $db->filterMySQL(var_cleanup($_POST['smiles']));
		$img = $db->filterMySQL(var_cleanup($_POST['img']));

		//do some error checking.
		if (empty($board_name)){
			#setup error session.
			$_SESSION['errors'] = $lang['boardnameerror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if (empty($description)){
			#setup error session.
			$_SESSION['errors'] = $lang['descriptionerror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if ($readaccess == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['noreadsetting'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if ($writeaccess == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['nowritesetting'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if ($replyaccess == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['noreplysetting'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if ($voteaccess == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['novotesetting'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if ($pollaccess == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['nopollsetting'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if (empty($catsel)){
			#setup error session.
			$_SESSION['errors'] = $lang['parenterror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if($increment == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['incrementerror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if ($bbcode == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['bbcodeerror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if ($smiles == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['smileserror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if ($img == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['imgerror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}
		if(strlen($board_name) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longboardname'];

            #direct user.
			redirect('acp/boardcp.php?action=board_add&amp;type='.$type, false, 0);
		}

		#decide board order on type of board.
		if($type == 2){
			//get board order
			$db->SQL = "SELECT id FROM ebb_boards where type='2' and Category='$catsel'";
			$ct = $db->affectedRows();
		}else{
			$db->SQL = "SELECT id FROM ebb_boards where type='3' and Category='$catsel'";
			$ct = $db->affectedRows();
		}

		#order to set new board to.
		$board_order = $ct + 1;
	}
	//process the query.
	$db->SQL = "INSERT INTO ebb_boards (Board, Description, type, Category, Smiles, Post_Increment, BBcode, Image, B_Order) VALUES('$board_name', '$description', '$type', '$catsel', '$smiles', '$increment', '$bbcode', '$img', '$board_order')";
	$db->query();

	//insert the permission rules into the permission table.
	$db->SQL = "SELECT id FROM ebb_boards ORDER BY id DESC LIMIT 1";
	$r_id = $db->fetchResults();

	// process query.
	$db->SQL = "INSERT INTO ebb_board_access (B_Read, B_Post, B_Reply, B_Vote, B_Poll, B_id) VALUES('$readaccess', '$writeaccess', '$replyaccess', '$voteaccess', '$pollaccess', '$r_id[id]')";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Created New Board: ".$board_name, $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/boardcp.php', false, 0);
break;
case 'board_modify':
	if(isset($_GET['type'])){
		$type = $db->filterMySQL(var_cleanup($_GET['type']));
	}else{
		$error = new notifySys($lang['noboardtype'], true);
		$error->displayError();
	}

   	#call board administrator class.
	$boardManager = new boardAdministration();
	if($type == 1){
		#see if a board id was defined
		if(isset($_GET['id'])){
			$id = $db->filterMySQL(var_cleanup($_GET['id']));
		}else{
			$error = new notifySys($lang['nobid'], true);
			$error->displayError();
		}

		#SQL to check if the selected item is in the DB.
		$db->SQL = "SELECT Board FROM ebb_boards WHERE id='$id' AND type='$type'";
		$boardData = $db->fetchResults();
		$validatePBoardID = $db->affectedRows();

		#see if item is a category.
		if($validatePBoardID == 0){
			$error = new notifySys($lang['notfound'], true);
			$error->displayError();
		}
		//get permission values.
		$db->SQL = "SELECT B_Read FROM ebb_board_access WHERE B_id='$id'";
		$permission = $db->fetchResults();

		#get value needed for permission select box.
		$readAccess = $boardManager->readAccessSelection();
	}else if($type == 2){
		#see if a board id was defined
		if(isset($_GET['id'])){
			$id = $db->filterMySQL(var_cleanup($_GET['id']));
		}else{
			$error = new notifySys($lang['nobid'], true);
			$error->displayError();
		}

		#get data from db about the selected item, also validate data.
		$db->SQL = "SELECT Board, Description, Post_Increment, BBcode, Smiles, Image, Category FROM ebb_boards WHERE id='$id' AND type='$type'";
		$boardData = $db->fetchResults();
		$validateCBoardID = $db->affectedRows();

		#see if item is a board.
		if($validateCBoardID == 0){
			$error = new notifySys($lang['notfound'], true);
			$error->displayError();
		}

		//get permission values.
		$db->SQL = "SELECT B_Read, B_Post, B_Reply, B_Vote, B_Poll FROM ebb_board_access where B_id='$id'";
		$permission = $db->fetchResults();

	    #Get list of Parent Boards.
		$parentBoardList = $boardManager->parentBoardSelection("parent");
		
		#get values needed for permission select box.
		$readAccess = $boardManager->readAccessSelection();
		$writeAccess = $boardManager->writeAccessSelection();
		$replyAccess = $boardManager->replyAccessSelection();
		$pollAccess = $boardManager->pollAccessSelection();
		$voteAccess = $boardManager->voteAccessSelection();
	}else{
		#see if a board id was defined
		if(isset($_GET['id'])){
			$id = $db->filterMySQL(var_cleanup($_GET['id']);
		}else{
			$error = new notifySys($lang['nobid'], true);
			$error->displayError();
		}

		#get data from db about the selected item, also validate data.
		$db->SQL = "SELECT Board, Description, Post_Increment, BBcode, Smiles, Image, Category FROM ebb_boards WHERE id='$id' AND type='$type'";
		$boardData = $db->fetchResults();
		$validateCBoardID = $db->affectedRows();

		#see if item is a board.
		if($validateCBoardID == 0){
			$error = new notifySys($lang['notfound'], true);
			$error->displayError();
		}

		//get permission values.
		$db->SQL = "SELECT B_Read, B_Post, B_Reply, B_Vote, B_Poll FROM ebb_board_access where B_id='$id'";
		$permission = $db->fetchResults();

	    #Get list of Parent Boards.
	    $parentBoardList = $boardManager->parentBoardSelection("child");
	    
	    #get values needed for permission select box.
 		$readAccess = $boardManager->readAccessSelection();
		$writeAccess = $boardManager->writeAccessSelection();
		$replyAccess = $boardManager->replyAccessSelection();
		$pollAccess = $boardManager->pollAccessSelection();
		$voteAccess = $boardManager->voteAccessSelection();
	}

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

	#edit board form.
	$tpl = new templateEngine($style, "cp-modifyboard");
	$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[admincp]",
		"LANG-MODIFYBOARD" => "$lang[modifyboard]",
		"BOARDTYPE" => "$type",
		"ID" => "$id",
		"LANG-BOARDNAME" => "$lang[boardname]",
		"BOARDNAME" => "$boardData[Board]",
		"LANG-DESCRIPTION" => "$lang[description]",
		"DESCRIPTION" => "$boardData[Description]",
		"LANG-BOARDPERMISSION" => "$lang[boardpermissions]",
		"LANG-BOARDSETTINGS" => "$lang[boardsettings]",
		"LANG-ON" => "$lang[on]",
		"LANG-OFF" => "$lang[off]",
		"LANG-READACCESS" => "$lang[boardread]",
		"READACCESS" => "$readAccess",
		"LANG-WRITEACCESS" => "$lang[boardwrite]",
		"WRITEACCESS" => "$writeAccess",
		"LANG-REPLYACCESS" => "$lang[boardreply]",
		"REPLYACCESS" => "$replyAccess",
		"LANG-VOTEACCESS" => "$lang[boardvote]",
		"VOTEACCESS" => "$voteAccess",
		"LANG-POLLACCESS" => "$lang[boardpoll]",
		"POLLACCESS" => "$pollAccess",
		"LANG-PARENT" => "$lang[parentboard]",
		"PARENT" => "$parentBoardList",
		"LANG-POSTINCREMENT" => "$lang[postincrement]",
		"LANG-YES" => "$lang[yes]",
		"LANG-NO" => "$lang[no]",
		"LANG-BBCODE" => "$lang[bbcode]",
		"LANG-SMILE" => "$lang[smiles]",
		"LANG-IMG" => "$lang[img]",
		"LANG-SUBMIT" => "$lang[submit]"));

	#do some decision making.
	if($type == 1){
		$tpl->removeBlock("childBoard");
	}else{
		$tpl->removeBlock("parentBoard");

		//post increment detect.
		if($boardData['Post_Increment'] == 1){
   			$tpl->removeBlock("noIncrement");
		}else{
   			$tpl->removeBlock("yesIncrement");
		}

		//bbcode detect.
		if ($boardData['BBcode'] == 1){
   			$tpl->removeBlock("bbcodeOff");
		}else{
   			$tpl->removeBlock("bbcodeOn");
		}

		//smiles detect.
		if ($boardData['Smiles'] == 1){
   			$tpl->removeBlock("smilesOff");
		}else{
   			$tpl->removeBlock("smilesOn");
		}

		//image detect.
		if ($boardData['Image'] == 1){
   			$tpl->removeBlock("imgOff");
		}else{
   			$tpl->removeBlock("imgOn");
		}
	}
	echo $tpl->outputHtml();
break;
case 'board_modify_process':
	if(isset($_GET['type'])){
		$type = $db->filterMySQL(var_cleanup($_GET['type']));
	}else{
		$error = new notifySys($lang['noboardtype'], true);
		$error->displayError();
	}

	#see if a board id was defined
	if(isset($_GET['id'])){
		$id = $db->filterMySQL(var_cleanup($_GET['id']));
	}else{
		$error = new notifySys($lang['nobid'], true);
		$error->displayError();
   	}

	#process data based on board type.
	if($type == 1){
		$modify_board_name = $db->filterMySQL(var_cleanup($_POST['board_name']));
		$modify_description = 'null';
		$modify_readaccess = $db->filterMySQL(var_cleanup($_POST['readaccess']));
		$modify_writeaccess = 4;
		$modify_replyaccess = 4;
		$modify_voteaccess = 4;
		$modify_pollaccess = 4;
		$modify_catsel = 0;
		$increment = 0;
		$modify_bbcode = 0;
		$modify_smiles = 0;
		$modify_img = 0;

		#do some error checking.
		if(empty($modify_board_name)){
			#setup error session.
			$_SESSION['errors'] = $lang['boardnameerror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if(strlen($modify_board_name) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longboardname'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if ($modify_readaccess == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['noreadsetting'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
	}else{
		$modify_board_name = $db->filterMySQL(var_cleanup($_POST['board_name']));
		$modify_description = $db->filterMySQL(var_cleanup($_POST['description']));
		$modify_readaccess = $db->filterMySQL(var_cleanup($_POST['readaccess']));
		$modify_writeaccess = $db->filterMySQL(var_cleanup($_POST['writeaccess']));
		$modify_replyaccess = $db->filterMySQL(var_cleanup($_POST['replyaccess']));
		$modify_voteaccess = $db->filterMySQL(var_cleanup($_POST['voteaccess']));
		$modify_pollaccess = $db->filterMySQL(var_cleanup($_POST['pollaccess']));
		$modify_catsel = $db->filterMySQL(var_cleanup($_POST['catsel']));
		$increment = $db->filterMySQL(var_cleanup($_POST['increment']));
		$modify_bbcode = $db->filterMySQL(var_cleanup($_POST['bbcode']));
		$modify_smiles = $db->filterMySQL(var_cleanup($_POST['smiles']));
		$modify_img = $db->filterMySQL(var_cleanup($_POST['img']));

		//do some error checking.
		if (empty($modify_board_name)){
			#setup error session.
			$_SESSION['errors'] = $lang['boardnameerror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if (empty($modify_description)){
			#setup error session.
			$_SESSION['errors'] = $lang['descriptionerror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if ($modify_readaccess == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['noreadsetting'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if ($modify_writeaccess == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['nowritesetting'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if ($modify_replyaccess == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['noreplysetting'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if ($modify_voteaccess == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['novotesetting'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if ($modify_pollaccess == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['nopollsetting'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if (empty($modify_catsel)){
			#setup error session.
			$_SESSION['errors'] = $lang['parenterror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if($increment == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['incrementerror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if ($modify_bbcode == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['bbcodeerror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if ($modify_smiles == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['smileserror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if ($modify_img == ""){
			#setup error session.
			$_SESSION['errors'] = $lang['imgerror'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
		if(strlen($modify_board_name) > 50){
			#setup error session.
			$_SESSION['errors'] = $lang['longboardname'];

            #direct user.
			redirect('acp/boardcp.php?action=board_modify&amp;id='.$id.'&amp;type='.$type, false, 0);
		}
	}

	//process the query.
	$db->SQL = "UPDATE ebb_boards SET Board='$modify_board_name', Description='$modify_description', Category='$modify_catsel', Smiles='$modify_smiles', Post_Increment='$increment', BBcode='$modify_bbcode', Image='$modify_img' WHERE type='$type' AND id='$id'";
	$db->query();

	//modify the permission table.
	$db->SQL = "UPDATE ebb_board_access SET B_Read='$modify_readaccess', B_Post='$modify_writeaccess', B_Reply='$modify_replyaccess', B_Vote='$modify_voteaccess', B_Poll='$modify_pollaccess' WHERE B_id='$id'";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Modified Board: ".$modify_board_name, $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/boardcp.php', false, 0);
break;
case 'board_delete':
	#see if board type is defined.
	if(isset($_GET['type'])){
		$type = $db->filterMySQL(var_cleanup($_GET['type']));
	}else{
		$error = new notifySys($lang['noboardtype'], true);
		$error->displayError();
	}
		
	#see if a board id was defined
	if(isset($_GET['id'])){
		$id = $db->filterMySQL(var_cleanup($_GET['id']));
	}else{
		$error = new notifySys($lang['nobid'], true);
		$error->displayError();
	}

	#perform action based on board type.
	if($type == 1){
		//get needed details for deleting other items.
		$db->SQL = "Select id FROM ebb_boards WHERE Category='$id'";
		$parentBoardQuery = $db->query();

		while($topic = mysql_fetch_assoc($parentBoardQuery)){
			#get topic details.
			$db->SQL = "Select tid FROM ebb_topics WHERE bid='$topic[id]'";
			$topicQuery = $db->query();

			while($topicID = mysql_fetch_assoc($topicQuery)){
				//delete polls made by topics in this board.
				$db->SQL = "DELETE FROM ebb_poll WHERE tid='$topicID[tid]'";
				$db->query();

				#delete any votes.
				$db->SQL = "DELETE FROM ebb_votes WHERE tid='$topicID[tid]'";
				$db->query();

				//delete read status from topics made in this board.
				$db->SQL = "DELETE FROM ebb_read WHERE Topic='$topicID[tid]'";
				$db->query();

				//delete any user subscriptions for topics made in this board.
				$db->SQL = "DELETE FROM ebb_topic_watch WHERE tid='$topicID[tid]'";
				$db->query();

				#delete any attachments thats tied to a topic under this board.
				$db->SQL = "select Filename from ebb_attachments where tid='$topicID[tid]'";
				$attach_r = $db->fetchResults();
				$attach_chk = $db->affectedRows();

				if($attach_chk == 1){
					#delete file from web space.
					@unlink(FULLPATH.'/uploads/'. $attach_r['Filename']);

					#delete entry from db.
					$db->SQL = "DELETE FROM ebb_attachments WHERE tid='$topicID[tid]'";
					$db->query();
				}
			}

			//delete topics made in that board.
			$db->SQL = "DELETE FROM ebb_topics WHERE bid='$topic[id]'";
			$db->query();

			//delete read status from the db.
			$db->SQL = "DELETE FROM ebb_read WHERE Board='$topic[id]'";
			$db->query();

			#get post details.
			$db->SQL = "Select pid FROM ebb_posts WHERE bid='$topic[id]'";
			$post_r = $db->query();

			#delete any thing tied to posts.
			while($post = mysql_fetch_assoc($post_r)){
				#delete any attachments thats tied to a post under this board.
				$db->SQL = "select Filename from ebb_attachments where pid='$post[pid]'";
				$attach_r2 = $db->fetchResults();
				$attach_chk2 = $db->affectedRows();

				if($attach_chk2 == 1){
					#delete file from web space.
					@unlink(FULLPATH.'/uploads/'. $attach_r2['Filename']);

					#delete entry from db.
					$db->SQL = "DELETE FROM ebb_attachments WHERE pid='$post[pid]'";
					$db->query();
				}
			}

			//delete posts made in that board.
			$db->SQL = "DELETE FROM ebb_posts WHERE bid='$topic[id]'";
			$db->query();

			//delete the permission rules set for this board.
			$db->SQL = "DELETE FROM ebb_board_access WHERE B_id='$topic[id]'";
			$db->query();

			//delete the moderator list for this board.
			$db->SQL = "DELETE FROM ebb_grouplist WHERE board_id='$topic[id]'";
			$db->query();
		}

		//delete board.
		$db->SQL = "DELETE FROM ebb_boards WHERE id='$id'";
		$db->query();

		#log this into our audit system.
		$acpAudit = new auditSystem();
		$acpAudit->logAction("Deleted Board", $acpUsr, time(), detectProxy());

		//bring user back to board section
		redirect('acp/boardcp.php', false, 0);
	}else{
		//get topic details for deleting other items.
		$db->SQL = "Select tid FROM ebb_topics WHERE bid='$id'";
		$boarddel_query = $db->query();

		while($topic = mysql_fetch_assoc($boarddel_query)){
			//delete polls made by topics in this board.
			$db->SQL = "DELETE FROM ebb_poll WHERE tid='$topic[tid]'";
			$db->query();

			#delete any votes.
			$db->SQL = "DELETE FROM ebb_votes WHERE tid='$topic[tid]'";
			$db->query();

			//delete read status from topics made in this board.
			$db->SQL = "DELETE FROM ebb_read WHERE Topic='$topic[tid]'";
			$db->query();

			//delete any user subscriptions for topics made in this board.
			$db->SQL = "DELETE FROM ebb_topic_watch WHERE tid='$topic[tid]'";
			$db->query();

			#delete any attachments thats tied to a topic under this board.
			$db->SQL = "select Filename from ebb_attachments where tid='$topic[tid]'";
			$attach_r = $db->fetchResults();
			$attach_chk = $db->affectedRows();

			if($attach_chk == 1){
				#delete file from web space.
				@unlink(FULLPATH.'/uploads/'. $attach_r['Filename']);

				#delete entry from db.
				$db->SQL = "DELETE FROM ebb_attachments WHERE tid='$topic[tid]'";
				$db->query();
			}
		}

		//delete board.
		$db->SQL = "DELETE FROM ebb_boards WHERE id='$id'";
		$db->query();

		//delete topics made in that board.
		$db->SQL = "DELETE FROM ebb_topics WHERE bid='$id'";
		$db->query();

		//delete read status from the db.
		$db->SQL = "DELETE FROM ebb_read WHERE Board='$id'";
		$db->query();

		#get post details.
		$db->SQL = "Select pid FROM ebb_posts WHERE bid='$id'";
		$post_r = $db->query();

		#delete any thing tied to posts.
		while($post = mysql_fetch_assoc($post_r)){
			#delete any attachments thats tied to a post under this board.
			$db->SQL = "select Filename from ebb_attachments where pid='$post[pid]'";
			$attach_r2 = $db->fetchResults();
			$attach_chk2 = $db->affectedRows();

			if($attach_chk2 == 1){
				#delete file from web space.
				@unlink(FULLPATH.'/uploads/'. $attach_r2['Filename']);

				#delete entry from db.
				$db->SQL = "DELETE FROM ebb_attachments WHERE pid='$post[pid]'";
				$db->query();
			}
		}

		//delete posts made in that board.
		$db->SQL = "DELETE FROM ebb_posts WHERE bid='$id'";
		$db->query();

		//delete the permission rules set for this board.
		$db->SQL = "DELETE FROM ebb_board_access WHERE B_id='$id'";
		$db->query();

		//delete the moderator list for this board.
		$db->SQL = "DELETE FROM ebb_grouplist WHERE board_id='$id'";
		$db->query();

		#log this into our audit system.
		$acpAudit = new auditSystem();
		$acpAudit->logAction("Deleted Board", $acpUsr, time(), detectProxy());

		//bring user back to board section
		redirect('acp/boardcp.php', false, 0);
	}
break;
case 'prune':
	#display board manager.
	$boardManager = new boardAdministration();
	$pruneBoardList = $boardManager->parentBoardSelection("child");

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

	#prune board form..
	$tpl = new templateEngine($style, "cp-prune");
	$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[admincp]",
		"LANG-PRUNE" => "$lang[prune]",
		"LANG-TEXT" => "$lang[prunetxt]",
		"LANG-PRUNERULE" => "$lang[prunerule]",
		"LANG-BOARDLIST" => "$lang[pruneboard]",
		"BOARDLIST" => "$pruneBoardList",
		"LANG-SUBMIT" => "$lang[pruneboards]"));
	echo $tpl->outputHtml();
break;
case 'prune_process':
	$prune_age = $db->filterMySQL(var_cleanup($_POST['prune_age']));
	$boardsel = $db->filterMySQL(var_cleanup($_POST['boardsel']));

	//error check
	if(empty($prune_age)){
		#setup error session.
		$_SESSION['errors'] = $lang['noprunedate'];

        #direct user.
		redirect('acp/boardcp.php?action=prune', false, 0);
	}
	if(strlen($prune_age) > 3){
		#setup error session.
		$_SESSION['errors'] = $lang['longprunedate'];

        #direct user.
		redirect('acp/boardcp.php?action=prune', false, 0);
	}
	if(empty($boardsel)){
		#setup error session.
		$_SESSION['errors'] = $lang['noboardselect'];

        #direct user.
		redirect('acp/boardcp.php?action=prune', false, 0);
	}

	//perform prune.
	$time_math = 3600*24*$prune_age;
	$remove_eq = time() - $time_math;

	#get post details.
	$db->SQL = "SELECT pid FROM ebb_posts WHERE Original_Date>='$remove_eq' AND bid='$boardsel'";
	$post_r = $db->query();

	#delete any thing tied to posts.
	while($post = mysql_fetch_assoc($post_r)){
		#delete any attachments thats tied to a post under this board.
		$db->SQL = "select Filename from ebb_attachments where pid='$post[pid]'";
		$attach_r = $db->fetchResults();

		#delete file from web space.
		@unlink(FULLPATH.'/uploads/'. $attach_r['Filename']);

		#delete entry from db.
		$db->SQL = "DELETE FROM ebb_attachments WHERE pid='$post[pid]'";
		$db->query();
	}

	//process query
	$db->SQL = "DELETE FROM ebb_posts WHERE Original_Date>='$remove_eq' and bid='$boardsel'";
	$db->query();

	//get topic details for deleting other items.
	$db->SQL = "Select tid FROM ebb_topics WHERE Original_Date>='$remove_eq' and bid='$boardsel'";
	$boarddel_query = $db->query();

	#delete anything tied to a topic.
	while($topic = mysql_fetch_assoc($boarddel_query)){
		//delete polls made by topics in this board.
		$db->SQL = "DELETE FROM ebb_poll WHERE tid='$topic[tid]'";
		$db->query();

		#delete any votes.
		$db->SQL = "DELETE FROM ebb_votes WHERE tid='$topic[tid]'";
		$db->query();

		//delete read status from topics made in this board.
		$db->SQL = "DELETE FROM ebb_read WHERE Topic='$topic[tid]'";
		$db->query();

		//delete any user subscriptions for topics made in this board.
		$db->SQL = "DELETE FROM ebb_topic_watch WHERE tid='$topic[tid]'";
		$db->query();

		#delete any attachments thats tied to a topic under this board.
		$db->SQL = "select Filename from ebb_attachments where tid='$topic[tid]'";
		$attach_r2 = $db->fetchResults();

		#delete file from web space.
		@unlink(FULLPATH.'/uploads/'. $attach_r2['Filename']);

		#delete entry from db.
		$db->SQL = "DELETE FROM ebb_attachments WHERE tid='$topic[tid]'";
		$db->query();
	}

	//process query
	$db->SQL = "DELETE FROM ebb_topics WHERE Original_Date>='$remove_eq' and bid='$boardsel'";
	$db->query();

	#log this into our audit system.
	$acpAudit = new auditSystem();
	$acpAudit->logAction("Pruned Board", $acpUsr, time(), detectProxy());

	//bring user back to board section
	redirect('acp/boardcp.php', false, 0);
break;
default:
	#get board type.
	if(isset($_GET['type'])){
		$type = $db->filterMySQL(var_cleanup($_GET['type']));
	}else{
		$type = 1;
	}

	#get board id if needed.
	if(isset($_GET['bid'])){
		$bid = $db->filterMySQL(var_cleanup($_GET['bid']));
	}else{
		$bid = 0;
	}

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

	#display board manager.
	$boardManager = new boardAdministration();
	$boardManager->boardManager($type, $bid);
break;
}
#display footer
$tpl = new templateEngine($style, "footer");
$tpl->parseTags(array(
  "LANG-POWERED" => "$lang[poweredby]"));
echo $tpl->outputHtml();
ob_end_flush();
?>
