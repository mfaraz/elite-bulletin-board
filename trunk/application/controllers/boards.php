<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * boards.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 02/20/2012
*/

/**
 * @class Boards
 * Board Controller.
 */
class Boards extends EBB_Controller {

	function __construct() {
 		parent::__construct();
		$this->load->model(array('Boardmodel', 'Boardaccessmodel'));
		$this->load->helper(array('common', 'posting'));

	}
	
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards
	 *	- or -
	 * 		http://example.com/index.php/boards/index
	 */
	public function index() {

		$this->load->helper(array('boardindex', 'topic', 'user', 'form'));
		$this->load->library('datetime_52', 'encrypt');

		$data = array();
		$data2 = array();

		//SQL CI style.
		$this->db->select('id, Board')->from('ebb_boards')->where('type', 1)->order_by("B_Order", "asc");
		$query = $this->db->get();
		foreach ($query->result() as $row) {

        	$data[] = $row;

			//build second query.
			$this->db->select('id, Board, Description, last_update, Posted_User, Post_Link, Category')->from('ebb_boards')->where('type', 2)->where('Category',$row->id)->order_by("B_Order", "asc");
			$query2 = $this->db->get();
			foreach ($query2->result() as $row2) {

				#board rules sql.
				//TODO: use entity here
				$this->db->select('B_Read')->from('ebb_board_access')->where('B_id',$row2->id);
				$readAccessQ = $this->db->get();
				$readAccess = $readAccessQ->row();

				#see if user can view the board.
				if ($this->Groupmodel->validateAccess(0, $readAccess->B_Read)){
					$data2[] = $row2;
				}

			}
		}

		#setup filters.
		$this->twig->_twig_env->addFilter('counter', new Twig_Filter_Function('GetCount'));
		$this->twig->_twig_env->addFilter('ReadStat', new Twig_Filter_Function('CheckReadStatus'));
		$this->twig->_twig_env->addFilter('SubBoards', new Twig_Filter_Function('getSubBoard'));
		$this->twig->_twig_env->addFunction('boardInfo', new Twig_Function_Function('boardStats'));

		//render to HTML.
		echo $this->twig->render($this->style, 'board_index', array (
		  'boardName' => $this->title,
		  'pageTitle'=> $this->lang->line('index'),
		  'BOARD_URL' => $this->boardUrl,
		  'APP_URL' => $this->boardUrl.APPPATH,
		  'NOTIFY_TYPE' => $this->session->flashdata('NotifyType'),
		  'NOTIFY_MSG' =>  $this->session->flashdata('NotifyMsg'),
		  'LANG' => $this->lng,
		  'TimeFormat' => $this->timeFormat,
		  'TimeZone' => $this->timeZone,
		  'LANG_WELCOME'=> $this->lang->line('welcome'),
		  'LANG_WELCOMEGUEST' => $this->lang->line('welcomeguest'),
		  'LOGGEDUSER' => $this->logged_user,
		  'LANG_JSDISABLED' => $this->lang->line('jsdisabled'),
		  'LANG_INFO' => $this->lang->line('info'),
		  'LANG_LOGIN' => $this->lang->line('login'),
		  'LANG_LOGOUT' => $this->lang->line('logout'),
		  'LOGINFORM' => form_open('login/LogIn', array('name' => 'frmQLogin')),
		  'LANG_USERNAME' => $this->lang->line('username'),
		  'LANG_REGISTER' => $this->lang->line('register'),
		  'LANG_PASSWORD' => $this->lang->line('pass'),
		  'LANG_FORGOT' => $this->lang->line('forgot'),
		  'LANG_REMEMBERTXT' => $this->lang->line('remembertxt'),
		  'LANG_QUICKSEARCH' => $this->lang->line('quicksearch'),
		  'LANG_SEARCH' => $this->lang->line('search'),
		  'LANG_CP' => $this->lang->line('admincp'),
		  'LANG_NEWPOSTS' => $this->lang->line('newposts'),
		  'LANG_HOME' => $this->lang->line('home'),
		  'LANG_HELP' => $this->lang->line('help'),
		  'LANG_MEMBERLIST' => $this->lang->line('profile'),
		  'LANG_PROFILE' => $this->lang->line('logout'),
		  'LANG_POWERED' => $this->lang->line('poweredby'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby'),
		  'groupAccess' => $this->groupAccess,
		  'showInfoBox' => $this->preference->getPreferenceValue("infobox_status"),
		  'LANG_TICKER' => $this->lang->line('ticker_txt'),
		  'ANNOUNCEMENT' => informationPanel(),
		  'LANG_BOARD' => $this->lang->line('boards'),
		  'LANG_TOPIC' => $this->lang->line('topics'),
		  'LANG_POST' => $this->lang->line('posts'),
		  'LANG_LASTPOSTDATE' => $this->lang->line('lastposteddate'),
		  "LANG_RSS" => $this->lang->line('viewfeed'),
		  'Category' => $data,
		  'Boards' => $data2,
		  "LANG_BOARDSTAT" => $this->lang->line('boardstatus'),
		  "LANG_ICONGUIDE" => $this->lang->line('iconguide'),
		  "LANG_NEWESTMEMBER" => $this->lang->line('newestmember'),
		  "LANG_TOTALTOPIC" => $this->lang->line('topics'),
		  "LANG_TOTALPOST" => $this->lang->line('posts'),
		  "LANG_TOTALUSER" => $this->lang->line('membernum'),
		  "LANG_NEWPOST" => $this->lang->line('newpost'),
		  "LANG_OLDPOST" => $this->lang->line('oldpost'),
		  "LANG_WHOSONLINE" => $this->lang->line('whosonline'),
		  "LANG_ONLINEKEY" => $this->lang->line('onlinekey'),
		  "LANG_LOGGED_ONLINE" => $this->lang->line('membernum'),
		  "LANG_GUEST_ONLINE" => $this->lang->line('guestonline'),
		  "WHOSONLINE"=> whosonline()
		));
	}

	/**
	 * shows list of topics.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/viewboard/5
	*/
	public function viewboard($id){

		//don't allow user to view category boards.
		if (!isset($id) OR (empty($id)) OR (!is_numeric($id))) {
			show_error($this->lang->line('nobid'),404,$this->lang->line('error'));
		}else {

			if ($this->Boardmodel->ValidateBoardID($id) == 0) {
				show_error($this->lang->line('doesntexist'),404,$this->lang->line('error'));
			}elseif ($this->Boardmodel->getType() == 1) {
				redirect('/', 'location');
				exit();
			}

			//load entity.
			$this->Boardmodel->GetBoardSettings($id);
			$this->Boardaccessmodel->GetBoardAccess($id);

			//
			// Permission Validation
			//

			#see if user can view this topic.
			if ($this->Groupmodel->validateAccess(0, $this->Boardaccessmodel->getBRead()) == false){
				//show_error($this->lang->line('noread'), 403, $this->lang->line('error'));
				$CanRead = FALSE;
			} else {
				$CanRead = TRUE;
			}


			#see if user can post a topic or not.
			if ($this->Groupmodel->validateAccess(0, $this->Boardaccessmodel->getBPost()) == false){
				$CanPost = FALSE;
			}elseif($this->Groupmodel->validateAccess(1, 37) == false){
				$CanPost = FALSE;
			} else {
				$CanPost = TRUE;
			}

			#see if user can post a topic poll or not.
			if ($this->Groupmodel->validateAccess(0, $this->Boardaccessmodel->getBPoll()) == false){
				$CanPostPoll = FALSE;
			}elseif($this->Groupmodel->validateAccess(1, 35) == false){
				$CanPostPoll = FALSE;
			} else {
				$CanPostPoll = TRUE;
			}

		}

		//load pagination library
		$this->load->helper(array('boardindex', 'topic', 'user', 'form'));
		$this->load->library(array('datetime_52', 'encrypt', 'pagination', 'breadcrumb'));

		//record user coming in here
		if ((CheckReadStatus($id, $this->logged_user) == FALSE) AND ($this->logged_user !== "guest")){
			$data = array(
			   'Board' => $id,
			   'User' => $this->logged_user
			);
			$this->db->insert('ebb_read_board', $data);
		}
		
		//setup pagination.
		$config['base_url'] = $this->boardUrl . 'index.php/boards/viewboard/'.$id;
		$config['total_rows'] = GetCount($id, 'TopicCount');
		$config['per_page'] = $this->preference->getPreferenceValue("per_page");
		$config['uri_segment'] = 4;
		$this->pagination->initialize($config);

		//add breadcrumbs
		$this->breadcrumb->append_crumb($this->title, '/');
		$this->breadcrumb->append_crumb($this->Boardmodel->getBoard(), '/viewboard');

        #setup filters.
		$this->twig->_twig_env->addFilter('counter', new Twig_Filter_Function('GetCount'));
		$this->twig->_twig_env->addFilter('TopicReadStat', new Twig_Filter_Function('readTopicStat'));
		$this->twig->_twig_env->addFunction('Attachment', new Twig_Function_Function('HasAttachment'));
		$this->twig->_twig_env->addFunction('SubBoardCount', new Twig_Function_Function('GetSubBoardCount'));
		$this->twig->_twig_env->addFilter('ReadStat', new Twig_Filter_Function('CheckReadStatus'));
		$this->twig->_twig_env->addFilter('SubBoards', new Twig_Filter_Function('getSubBoard'));

		//render to HTML.
		echo $this->twig->render($this->style, 'viewboard', array (
		  'boardName' => $this->title,
		  'pageTitle'=> $this->lang->line('viewboard').' - '.$this->Boardmodel->getBoard(),
		  'BOARD_URL' => $this->boardUrl,
		  'APP_URL' => $this->boardUrl.APPPATH,
		  'NOTIFY_TYPE' => $this->session->flashdata('NotifyType'),
		  'NOTIFY_MSG' =>  $this->session->flashdata('NotifyMsg'),
		  'LANG' => $this->lng,
		  'TimeFormat' => $this->timeFormat,
		  'TimeZone' => $this->timeZone,
		  'LANG_WELCOME'=> $this->lang->line('welcome'),
		  'LANG_WELCOMEGUEST' => $this->lang->line('welcomeguest'),
		  'LOGGEDUSER' => $this->logged_user,
		  'LANG_JSDISABLED' => $this->lang->line('jsdisabled'),
		  'LANG_INFO' => $this->lang->line('info'),
		  'LANG_LOGIN' => $this->lang->line('login'),
		  'LANG_LOGOUT' => $this->lang->line('logout'),
		  'LOGINFORM' => form_open('login/LogIn', array('name' => 'frmQLogin')),
		  'LANG_USERNAME' => $this->lang->line('username'),
		  'LANG_REGISTER' => $this->lang->line('register'),
		  'LANG_PASSWORD' => $this->lang->line('pass'),
		  'LANG_FORGOT' => $this->lang->line('forgot'),
		  'LANG_REMEMBERTXT' => $this->lang->line('remembertxt'),
		  'LANG_QUICKSEARCH' => $this->lang->line('quicksearch'),
		  'LANG_SEARCH' => $this->lang->line('search'),
		  'LANG_CP' => $this->lang->line('admincp'),
		  'LANG_NEWPOSTS' => $this->lang->line('newposts'),
		  'LANG_HOME' => $this->lang->line('home'),
		  'LANG_HELP' => $this->lang->line('help'),
		  'LANG_MEMBERLIST' => $this->lang->line('profile'),
		  'LANG_PROFILE' => $this->lang->line('logout'),
		  'LANG_POWERED' => $this->lang->line('poweredby'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby'),
		  'groupAccess' => $this->groupAccess,
		  'BOARDID' => $id,
		  'BOARDCOUNT' => GetCount($id, 'TopicCount'),
		  'BOARDDATA' => $this->Boardmodel->GetTopics($id, $config['per_page'], 0),
		  'SUBBOARDDATA' => $this->Boardmodel->GetSubBoards($id),
		  'PAGINATION' => $this->pagination->create_links(),
		  'BREADCRUMB' => $this->breadcrumb->output(),
		  'LANG_NOREAD' => $this->lang->line('noread'),
		  'LANG_NOPOST' => $this->lang->line('nopost'),
		  'CANREAD_TOPIC' => $CanRead,
		  'CANPOST_TOPIC' => $CanPost,
		  'CANPOST_POLL' => $CanPostPoll,
		  'LANG_NEWPOST' => $this->lang->line('newpost'),
		  'LANG_OLDPOST' => $this->lang->line('oldpost'),
		  'LANG_BOARD' => $this->lang->line('boards'),
		  'LANG_TOPIC' => $this->lang->line('topics'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby'),
		  'LANG_REPLIES' => $this->lang->line('replies'),
		  'LANG_POSTVIEWS' => $this->lang->line('views'),
		  'LANG_POST' => $this->lang->line('posts'),
		  'LANG_LASTPOSTDATE' => $this->lang->line('lastposteddate'),
		  'LANG_LASTPOSTEDBY' => $this->lang->line('lastpost'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby')
		));
	}

	/**
	 * shows topics & all replies tied to it.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/viewtopic/5
	*/
	public function viewtopic($id) {

		//load library & helpers
		$this->load->helper(array('boardindex', 'topic', 'user', 'form', 'posting', 'group', 'attachment'));
		$this->load->library(array('datetime_52', 'encrypt', 'pagination', 'breadcrumb'));

		//load topic model.
		$this->load->model('Topicmodel');

		//load entities
		$this->Topicmodel->GetTopicData($id);
		$this->Boardmodel->GetBoardSettings($this->Topicmodel->getBid());
		$this->Boardaccessmodel->GetBoardAccess($this->Topicmodel->getBid());

		//
		// Permission Validation
		//

		#see if user can view this topic.
		if ($this->Groupmodel->validateAccess(0, $this->Boardaccessmodel->getBRead()) == false){
			show_error($this->lang->line('noread'), 403, $this->lang->line('error'));
		}
		
		#see if user can post a reply or not.
		if ($this->Groupmodel->validateAccess(0, $this->Boardaccessmodel->getBReply())){
			//see if we got any group-based permission overwritting the board-level permission.
			if($this->Groupmodel->validateAccess(1, 38)){
				$CanReply = TRUE;
			} else {
				$CanReply = FALSE;
			}
		} else {
			$CanReply = FALSE;
		}
		
		#see if user can vote.
		if ($this->Groupmodel->ValidateAccess(0, $this->Boardaccessmodel->getBVote())) {
			//see if we got any group-based permission overwritting the board-level permission.
			if($this->Groupmodel->ValidateAccess(1, 36)) {
				$CanVote = TRUE;
			} else {
				$CanVote = FALSE;
			}
		} else {
			$CanVote = TRUE;
		}

		#see if this user can delete a topic as a moderator/admin.
		if($this->Groupmodel->ValidateAccess(1, 21)){
			$CanDeleteACL = TRUE;
		} else {
			$CanDeleteACL = FALSE;
		}

		#see if user can move topic.
		if($this->Groupmodel->ValidateAccess(1, 23)){
			$CanMoveACL = TRUE;
		} else {
			$CanMoveACL = FALSE;
		}

		#see if user can lock/unlock a topic.
		if($this->Groupmodel->ValidateAccess(1, 22)){
			$CanToggleLockACL = TRUE;
		} else {
			$CanToggleLockACL = FALSE;
		}

		#see if user can alter warning levels.
		if($this->Groupmodel->validateAccess(1, 25)){
			$canAlterWarn = TRUE;
		} else {
			$canAlterWarn = FALSE;
		}

		#see if user can view poster's IP.
		if($this->Groupmodel->ValidateAccess(1, 24)){
			$canSeeIP = TRUE;
		} else {
			$canSeeIP = FALSE;
		}

		#see if user can alter topic.
		if($this->Groupmodel->ValidateAccess(1, 38)){
			$CanEdit = TRUE;
		} else {
			$CanEdit = FALSE;
		}

		#see if user can delete topic.
		if($this->Groupmodel->ValidateAccess(1, 21)){
			$CanDelete = TRUE;
		} else {
			$CanDelete = FALSE;
		}

		//
		// Setup Pagination.
		//
		$config['base_url'] = $this->boardUrl . 'boards/viewtopic/'.$id;
		$config['total_rows'] = GetCount($id, 'TopicReplies');
		$config['per_page'] = $this->preference->getPreferenceValue("per_page");
		$config['uri_segment'] = 4;

		$this->pagination->initialize($config);
		
		//add breadcrumbs
		$this->breadcrumb->append_crumb($this->title, '/');
		$this->breadcrumb->append_crumb($this->Boardmodel->getBoard(), '/boards/viewboard/'.$this->Topicmodel->getBid());
		$this->breadcrumb->append_crumb($this->Topicmodel->getTopic(), '/boards/viewtopic');

		 #setup filters.
		$this->twig->_twig_env->addFilter('counter', new Twig_Filter_Function('GetCount'));
		$this->twig->_twig_env->addFilter('TopicReadStat', new Twig_Filter_Function('readTopicStat'));
		$this->twig->_twig_env->addFilter('ReadStat', new Twig_Filter_Function('CheckReadStatus'));
		$this->twig->_twig_env->addFunction('FormatMsg', new Twig_Function_Function('FormatTopicBody'));
		$this->twig->_twig_env->addFunction('Spam_Filter', new Twig_Function_Function('language_filter'));
		$this->twig->_twig_env->addFunction('ATTACH_BAR', new Twig_Function_Function('GetAttachments'));
		$this->twig->_twig_env->addFunction('ATTACH_FILE_SIZE', new Twig_Function_Function('getFileSize'));
		$this->twig->_twig_env->addFunction('MATH_ROUND', new Twig_Function_Function('Round'));
		$this->twig->_twig_env->addFunction('CALC_VOTE', new Twig_Function_Function('CalcVotes'));
		$this->twig->_twig_env->addFunction('VOTECHECK', new Twig_Function_Function('CheckVoteStatus'));

		//Grab some settings.
		$disable_bbcode = $this->Topicmodel->getDisableBbCode();
		$disable_smiles = $this->Topicmodel->getDisableSmiles();
		$boardpref_bbcode = $this->Boardmodel->getBbCode();
		$boardpref_smiles = $this->Boardmodel->getSmiles();
		$boardpref_image = $this->Boardmodel->getImage();

		//render to HTML.
		echo $this->twig->render($this->style, 'viewtopic', array (
		  'boardName' => $this->title,
		  'pageTitle'=> $this->lang->line('viewtopic').' - '.$this->Topicmodel->getTopic(),
		  'BOARD_URL' => $this->boardUrl,
		  'APP_URL' => $this->boardUrl.APPPATH,
		  'NOTIFY_TYPE' => $this->session->flashdata('NotifyType'),
		  'NOTIFY_MSG' =>  $this->session->flashdata('NotifyMsg'),
		  'LANG' => $this->lng,
		  'TimeFormat' => $this->timeFormat,
		  'TimeZone' => $this->timeZone,
		  'LANG_WELCOME'=> $this->lang->line('welcome'),
		  'LANG_WELCOMEGUEST' => $this->lang->line('welcomeguest'),
		  'LOGGEDUSER' => $this->logged_user,
		  'LANG_JSDISABLED' => $this->lang->line('jsdisabled'),
		  'LANG_INFO' => $this->lang->line('info'),
		  'LANG_LOGIN' => $this->lang->line('login'),
		  'LANG_LOGOUT' => $this->lang->line('logout'),
		  'LOGINFORM' => form_open('login/LogIn', array('name' => 'frmQLogin')),
		  'LANG_USERNAME' => $this->lang->line('username'),
		  'LANG_REGISTER' => $this->lang->line('register'),
		  'LANG_PASSWORD' => $this->lang->line('pass'),
		  'LANG_FORGOT' => $this->lang->line('forgot'),
		  'LANG_REMEMBERTXT' => $this->lang->line('remembertxt'),
		  'LANG_QUICKSEARCH' => $this->lang->line('quicksearch'),
		  'LANG_SEARCH' => $this->lang->line('search'),
		  'LANG_CP' => $this->lang->line('admincp'),
		  'LANG_NEWPOSTS' => $this->lang->line('newposts'),
		  'LANG_HOME' => $this->lang->line('home'),
		  'LANG_HELP' => $this->lang->line('help'),
		  'LANG_MEMBERLIST' => $this->lang->line('profile'),
		  'LANG_PROFILE' => $this->lang->line('logout'),
		  'LANG_POWERED' => $this->lang->line('poweredby'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby'),
		  'groupAccess' => $this->groupAccess,
		  'LANG_PRINT' =>  $this->lang->line('ptitle'),
		  'LANG_POSTCOUNT' => $this->lang->line('postcount'),
		  'TOPICID' => $id,
		  'DISABLE_SMILES' => $disable_smiles,
		  'BOARDPREF_SMILES' => $boardpref_smiles,
		  'DISABLE_BBCODE' => $disable_bbcode,
		  'BOARDPREF_BBCODE' => $boardpref_bbcode,
		  'BOARDPREF_IMAGE' => $boardpref_image,
		  'TOPIC_LOCKED' =>$this->Topicmodel->getLocked(),
		  'LANG_WARNLEVEL' => $this->lang->line('warnlevel'),
		  'TOPIC_TYPE' => $this->Topicmodel->getType(),
		  'TOPIC_SUBJECT' => $this->Topicmodel->getTopic(),
		  'TOPIC_BODY' => $this->Topicmodel->getBody(),
		  'TOPIC_AUTHOR' => $this->Topicmodel->getAuthor(),
		  'TOPIC_IP' => $this->Topicmodel->getIp(),
		  'TOPIC_POSTEDON' => $this->Topicmodel->getOriginalDate(),
		  'AUTHOR_GROUPNAME' => $this->Topicmodel->getGroupProfile(),
		  'AUTHOR_GROUPLEVEL' => $this->Topicmodel->getGroupAccess(),
		  'AUTHOR_AVATAR' => $this->Topicmodel->getAvatar(),
		  'AUTHOR_GID' => $this->Topicmodel->getGid(),
		  'AUTHOR_POSTCOUNT' => $this->Topicmodel->getPostCount(),
		  'AUTHOR_SIG' => $this->Topicmodel->getSig(),
		  'AUTHOR_WARNLEVEL' => $this->Topicmodel->getWarningLevel(),
		  'AUTHOR_CTITLE' => $this->Topicmodel->getCustomTitle(),
		  'LANG_DOWNLOADS' => $this->lang->line('downloadct'),
		  'LANG_ATTACHMENTS' => $this->lang->line('attachments'),
		  'LANG_POSTED' => $this->lang->line('postedon'),
		  'LANG_IP'  => $this->lang->line('ipmod'),
		  'LANG_IPLOGGED' => $this->lang->line('iplogged'),
          'POLL_QUESTION' => $this->Topicmodel->getQuestion(),
          'POLLDATA' => $this->Topicmodel->GetPoll($id),
          'LANG_VOTE' => $this->lang->line('castvote'),
		  'LANG_TOTAL' => $this->lang->line('total'),
		  'TOTAL_VOTES' => GetCount($id, 'PollCount'),
		  'REPLYDATA' => $this->Topicmodel->GetReplies($id, $config['per_page'], 0),
		  'PAGINATION' => $this->pagination->create_links(),
		  'BREADCRUMB' => $this->breadcrumb->output(),
		  'CANPOST_REPLY' => $CanReply,
		  'CANVOTE' => $CanVote,
		  'GAC_WARNINGLEVELS' => $canAlterWarn,
		  'GAC_SEEIP' => $canSeeIP,
		  'GAC_EDITTOPIC' => $CanEdit,
		  'GAC_DELETETOPIC' => $CanDelete,
		  'GAC_DELETEACL' => $CanDeleteACL,
		  'GAC_MOVEACL' => $CanMoveACL,
		  'GAC_TOGGLELOCKACL' => $CanToggleLockACL,
		  'LANG_NEWPOST' => $this->lang->line('newpost'),
		  'LANG_OLDPOST' => $this->lang->line('oldpost'),
		  'LANG_BOARD' => $this->lang->line('boards'),
		  'LANG_TOPIC' => $this->lang->line('topics'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby'),
		  'LANG_REPLIES' => $this->lang->line('replies'),
		  'LANG_POSTVIEWS' => $this->lang->line('views'),
		  'LANG_POST' => $this->lang->line('posts'),
		  'LANG_LASTPOSTDATE' => $this->lang->line('lastposteddate'),
		  'LANG_LASTPOSTEDBY' => $this->lang->line('lastpost'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby'),
		  'SMILES' => form_smiles(),
		  'LANG_REPLY' => $this->lang->line('btnreply'),
		  'LANG_OPTIONS' => $this->lang->line('options'),
		  'LANG_DISABLERTF' => $this->lang->line('disablertf'),
		  'LANG_SMILES' => $this->lang->line('moresmiles'),
		  'LANG_NOTIFY' => $this->lang->line('notify'),
		  'LANG_DISABLESMILES' => $this->lang->line('disablesmiles'),
		  'LANG_DISABLEBBCODE' => $this->lang->line('disablebbcode')
		));
	}
	
	/**
	 * post new topic on board.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/newtopic/5
	*/
	public function newtopic() {

	}
	
	/**
	 * reply to a topic.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/reply/5
	*/
	public function reply($id) {

	}
	
	/**
	 * vote on a poll.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/vote/5
	*/
	public function vote($id) {

	}
	
	/**
	 * report a topic.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/reporttopic/5
	*/
	public function reporttopic($id) {

	}
	
	/**
	 * report a reply.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/reportpost/5
	*/
	public function reportpost($id) {

	}
	
	/**
	 * edit a topic.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/edittopic/5
	*/
	public function edittopic($id) {

	}
	
	/**
	 * delets topic and all things assocated with it.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/deletetopic/5
	*/
	public function deletetopic($id) {

	}
	
	/**
	 * edit a reply.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/editpost/5
	*/
	public function editpost($id) {

	}
	
	/**
	 * delete a reply and everything assoicated with it.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/deletepost/5
	*/
	public function deletepost($id) {

	}
	
	/**
	 * Getan RSS Feed for the selected Board.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/boardFeed/5
	*/
	public function boardFeed($id) {

	}
	
	/**
	 * Getan RSS Feed for the latest posts on the board.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/boardFeed/5
	*/
	public function latestPost() {

	}
	
}

/* Location: ./application/controllers/boards.php */
