<?php
session_start();
define('IN_EBB', true);
/**
 * logout.ajax.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 7/26/2011
*/

#load up libraries.
require_once "../config.php";
require_once FULLPATH."/includes/function.php";
require_once FULLPATH."/includes/loginmgr.class.php";
require_once FULLPATH."/includes/preference.class.php";
require_once FULLPATH."/includes/templateEngine.php";
require_once FULLPATH."/includes/notifySys.php";
require_once FULLPATH."/includes/MySQL.php";

#call up the db class.
$db = new dbMySQL();

#call up preference class.
$boardPref = new preference();

#we just need this so the template engine doesn't act-up.
$style = 1;

#see if board directory is at the root.
if($boardPref->getPreferenceValue("board_directory") == "/"){
	$boardDir = '';
}else{
	$boardDir = $boardPref->getPreferenceValue("board_directory");
};
#see if user is using SESSIONS or is using COOKIES.
if(isset($_SESSION['ebb_user'])){
	#call user class.
	$usrAuth = new login($db->filterMySQL($_SESSION['ebb_user']), $db->filterMySQL($_SESSION['ebb_pass']));

	#execute logout procedure.
	$usrAuth->logOut();

	#redirect user to index.php
	redirect('index.php', false, 0);
}elseif(isset($_COOKIE['ebbuser'])){
	#call user class.
	$usrAuth = new login($db->filterMySQL($_COOKIE['ebbuser']), $db->filterMySQL($_COOKIE['ebbpass']));

	#execute logout procedure.
	$usrAuth->logOut();

	#redirect user to index.php
	redirect('index.php', false, 0);
}else{
	$displayMsg = new notifySys("INVALID LOGOUT METHOD!(".$_COOKIE['ebbuser'].")(".$_SESSION['ebb_user'].")", true);
	$displayMsg->genericError();
}
?>