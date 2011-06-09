<?php
define('IN_EBB', true);
/**
 * pm.ajax.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 4/13/2011
*/

#make sure the call is from an AJAX request.
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
	die("<strong>THIS PAGE CANNOT BE ACCESSED DIRECTLY!</strong>");
}

require_once "config.php";
require_once FULLPATH."/header.php";
require_once FULLPATH."/includes/PM.class.php";
require_once FULLPATH."/includes/swift/swift_required.php";

if(isset($_GET['action'])){
	$action = var_cleanup($_GET['action']);
}else{
	$action = '';
}

#see if user can access PM.
if($groupPolicy->validateAccess(1, 27) == false){
    $displayMsg = new notifySys($lang['accessdenied'], true);
	$displayMsg->displayError();
}

//block guest users.
if($logged_user == "guest"){
	$error = new notifySys($lang['guesterror'], true);
	$error->genericError();
}

#set some posting rules.
$allowsmile = 1;
$allowbbcode = 1;
$allowimg = 0;

switch ($action){
	case 'viewmsg';

	break;
	case 'newmsg':

	break;
	case 'reply':

	break;
	case 'delete':
		#validate PM ID.
		if((!isset($_GET['id'])) or (empty($_GET['id']))){
			$displayMsg = new notifySys($lang['nopmid'], true);
			$displayMsg->displayError();
		}else{
			$id = $db->filterMySQL($_GET['id']);
		}

		$pmObj = new PM(); #setup PM object.
		$pmObj->pmID = $id; #set PM ID.
		$pmObj->DeleteMessage(); #delete defined PM.
	break;
	case 'movemsg';
		
	break;
	default:
		
	break;
}

?>
