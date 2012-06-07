<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * boards.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 05/29/2012
*/

/**
 * Board Controller.
 * @abstract EBB_Controller 
 */
class Boards extends EBB_Controller {

	function __construct() {
 		parent::__construct();
		$this->load->model(array('Boardmodel', 'Boardaccessmodel'));
		$this->load->helper(array('common', 'posting'));

	}
	
	/**
	 * Index Page for this controller.
	 * @example index.php
	 * @example index.php/
	 * @example index.php/boards
	 * @example index.php/boards/index
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
			$this->db->select('id, Board, Description, last_update, Posted_User, tid, last_page, Category')->from('ebb_boards')->where('type', 2)->where('Category',$row->id)->order_by("B_Order", "asc");
			$query2 = $this->db->get();
			foreach ($query2->result() as $row2) {

				#board rules sql.
				$this->Boardaccessmodel->GetBoardAccess($row2->id);

				#see if user can view the board.
				if ($this->Groupmodel->validateAccess(0, $this->Boardaccessmodel->getBRead())){
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
		  'NOTIFY_TYPE' => $this->notifyType,
		  'NOTIFY_MSG' =>  $this->notifyMsg,
		  'LANG' => $this->lng,
		  'TimeFormat' => $this->timeFormat,
		  'TimeZone' => $this->timeZone,
		  'LANG_WELCOME'=> $this->lang->line('loggedinas'),
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
		  'LANG_MEMBERLIST' => $this->lang->line('members'),
		  'LANG_PROFILE' => $this->lang->line('profile'),
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
	 * @example index.php/boards/viewboard/5
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
			if ($this->Groupmodel->validateAccess(0, $this->Boardaccessmodel->getBRead()) == FALSE){
				$CanRead = FALSE;
			} else {
				$CanRead = TRUE;
			}


			#see if user can post a topic or not.
			if ($this->Groupmodel->validateAccess(0, $this->Boardaccessmodel->getBPost()) == FALSE){
				$CanPost = FALSE;
			}elseif($this->Groupmodel->validateAccess(1, 37) == FALSE){
				$CanPost = FALSE;
			} else {
				$CanPost = TRUE;
			}

			#see if user can post a topic poll or not.
			if ($this->Groupmodel->validateAccess(0, $this->Boardaccessmodel->getBPoll()) == FALSE){
				$CanPostPoll = FALSE;
			}elseif($this->Groupmodel->validateAccess(1, 35) == FALSE){
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
		$config = array();
		$config['base_url'] = $this->boardUrl . 'index.php/boards/viewboard/'.$id;
		$config['total_rows'] = GetCount($id, 'TopicCount');
		$config['per_page'] = $this->preference->getPreferenceValue("per_page");
		$config['uri_segment'] = 4;
		$config['full_tag_open'] = '<div class="pagination">';
		$config['full_tag_close'] = '</div>';
		$config['next_tag_open'] = '<span class="nextpage">';
		$config['next_tag_close'] = '</span>';
		$config['prev_tag_open'] = '<span class="prevpage">';
		$config['prev_tag_close'] = '</span>';
		$config['cur_tag_open'] = '<span class="currentpage">';
		$config['cur_tag_close'] = '</span>';
		$config['next_link'] = '&raquo;';
		$config['prev_link'] = '&laquo;';
		$config['first_link'] = $this->lang->line('pagination_first');
		$config['last_link'] = $this->lang->line('pagination_last');
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
		  'NOTIFY_TYPE' => $this->notifyType,
		  'NOTIFY_MSG' =>  $this->notifyMsg,
		  'LANG' => $this->lng,
		  'TimeFormat' => $this->timeFormat,
		  'TimeZone' => $this->timeZone,
		  'LANG_WELCOME'=> $this->lang->line('loggedinas'),
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
		  'LANG_MEMBERLIST' => $this->lang->line('members'),
		  'LANG_PROFILE' => $this->lang->line('profile'),
		  'LANG_POWERED' => $this->lang->line('poweredby'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby'),
		  'LANG_BTNLOCKED' => $this->lang->line('btnlocked'),
		  'LANG_BTNNEWTOPIC' => $this->lang->line('newtopic'),
		  'LANG_BTNNEWPOLL' => $this->lang->line('newpoll'),
		  'groupAccess' => $this->groupAccess,
		  'BOARDID' => $id,
		  'BOARDCOUNT' => GetCount($id, 'TopicCount'),
		  'BOARDDATA' => $this->Boardmodel->GetTopics($id, $config['per_page'], $this->uri->segment(4)),
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
	 * @example index.php/boards/viewtopic/5
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
		$config = array();
		$config['base_url'] = $this->boardUrl . 'index.php/boards/viewtopic/'.$id;
		$config['total_rows'] = GetCount($id, 'TopicReplies');
		$config['per_page'] = $this->preference->getPreferenceValue("per_page");
		$config['uri_segment'] = 4;
		$config['full_tag_open'] = '<div class="pagination">';
		$config['full_tag_close'] = '</div>';
		$config['next_tag_open'] = '<span class="nextpage">';
		$config['next_tag_close'] = '</span>';
		$config['prev_tag_open'] = '<span class="prevpage">';
		$config['prev_tag_close'] = '</span>';
		$config['cur_tag_open'] = '<span class="currentpage">';
		$config['cur_tag_close'] = '</span>';
		$config['next_link'] = '&raquo;';
		$config['prev_link'] = '&laquo;';
		$config['first_link'] = $this->lang->line('pagination_first');
		$config['last_link'] = $this->lang->line('pagination_last');
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
		  'NOTIFY_TYPE' => $this->notifyType,
		  'NOTIFY_MSG' =>  $this->notifyMsg,
		  'LANG' => $this->lng,
		  'TimeFormat' => $this->timeFormat,
		  'TimeZone' => $this->timeZone,
		  'LANG_WELCOME'=> $this->lang->line('loggedinas'),
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
		  'LANG_MEMBERLIST' => $this->lang->line('members'),
		  'LANG_PROFILE' => $this->lang->line('profile'),
		  'LANG_POWERED' => $this->lang->line('poweredby'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby'),
		  'LANG_BTNLOCKED' => $this->lang->line('btnlocked'),
		  'LANG_BTNREPLY' => $this->lang->line('postreply'),
		  'LANG_BTNMOVETOPIC' => $this->lang->line('movetopic'),
		  'LANG_BTNDELETETOPIC' => $this->lang->line('btndeletetopic'),
		  'LANG_BTNLOCKTOPIC' => $this->lang->line('btnlocktopic'),
		  'LANG_BTNUNLOCKTOPIC' => $this->lang->line('btnunlocktopic'),
		  'LANG_BTNEDITPOST' => $this->lang->line('editpost'),
		  'LANG_BTNDELETEPOST' => $this->lang->line('btndeletemessage'),
		  'LANG_BTNQUOTEAUTHOR' => $this->lang->line('btnquoteauthor'),
		  'LANG_BTNPMAUTHOR' => $this->lang->line('btnpmauthor'),
		  'LANG_BTNREPORTPOST' => $this->lang->line('report2mod'),
		  'groupAccess' => $this->groupAccess,
		  'LANG_PRINT' =>  $this->lang->line('ptitle'),
		  'LANG_POSTCOUNT' => $this->lang->line('postcount'),
		  'LANG_DELPROMPT' => $this->lang->line('topiccon'),
		  'LANG_DELPROMPT2' => $this->lang->line('postcon'),
		  'TOPICID' => $id,
		  'DISABLE_SMILES' => $disable_smiles,
		  'BOARDPREF_SMILES' => $boardpref_smiles,
		  'DISABLE_BBCODE' => $disable_bbcode,
		  'BOARDPREF_BBCODE' => $boardpref_bbcode,
		  'BOARDPREF_IMAGE' => $boardpref_image,
		  'TOPIC_LOCKED' =>$this->Topicmodel->getLocked(),
		  'LANG_WARNLEVEL' => $this->lang->line('warnlevel'),
		  'TOPIC_TYPE' => $this->Topicmodel->getTopicType(),
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
		  'REPLYDATA' => $this->Topicmodel->GetReplies($id, $config['per_page'], $this->uri->segment(4)),
		  'PAGINATION' => $this->pagination->create_links(),
		  'FORM_PAGE' => $this->uri->segment(4),
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
		  'QREPLYFORM' => form_open('boards/reply/'.$id, array('name' => 'frmQReply')),
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
	 * @example index.php/boards/newtopic/5
	*/
	public function newtopic($bid) {
		//LOAD LIBRARIES
		$this->load->library(array('encrypt', 'email', 'form_validation'));
        $this->load->helper(array('form', 'user', 'posting'));
		
		//get board settings.
		$this->Boardmodel->GetBoardSettings($bid);
		
		//see if user can post on this board.
		if (!$this->Groupmodel->validateAccess(0, $this->Boardaccessmodel->getBPost())){
			show_error($this->lang->line('nowrite'),403,$this->lang->line('error'));
		} else {
			if (!$this->Groupmodel->ValidateAccess(1, 37)){
				show_error($this->lang->line('nowrite'),403,$this->lang->line('error'));
			}
		}
		
		#see if user can mark topics as important.
		if ($this->Groupmodel->ValidateAccess(1, 39)){
			$CanImportant = TRUE;
		} else {
			$CanImportant = FALSE;
		}
		
		//setup validation rules.
        $this->form_validation->set_rules('topic', $this->lang->line('topic'), 'required|min_length[5]|max_length[50]|callback_SpamFilter|xss_clean');
        $this->form_validation->set_rules('post', $this->lang->line('topicbody'), 'required|min_length[10]|callback_SpamFilter|xss_clean');
		$this->form_validation->set_error_delimiters('<div class="ui-widget" style="width: 45%;"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;text-align:left;"><p id="validateResSvr">', '</p></div></div>');

		//see if any validation rules failed.
		if ($this->form_validation->run() == FALSE) {
			$this->FORM_newtopic($bid, false);
		} else {
			
			//flood check,
			if (flood_check("posting", $this->Usermodel->getLastPost())) {
				#setup error session.
				$this->session->set_flashdata('NotifyType', 'error');
				$this->session->set_flashdata('NotifyMsg', $this->lang->line('flood'));

				#direct user.
				redirect('/boards/viewboard/'.$bid, 'location');
				exit();
			}
			
			//load topic & attachments model.
			$this->load->model(array('Topicmodel', 'Attachmentsmodel'));
			
			//CREATE NEW TOPIC.
			$no_smile = ($this->input->post('no_smile', TRUE) == FALSE) ? FALSE : TRUE;
			$no_bbcode = ($this->input->post('no_bbcode', TRUE) == FALSE) ? FALSE : TRUE;
			$subscribe = ($this->input->post('subscribe', TRUE) == FALSE) ? FALSE : TRUE;
			//$page = ($this->input->post('page', TRUE) == FALSE) ? null : $this->input->post('page', TRUE);
			$time = time();
			
			//create new topic.
			$this->Topicmodel->setAuthor($this->logged_user);
			$this->Topicmodel->setBid($bid);
			$this->Topicmodel->setTopic($this->input->post('topic', TRUE));
			$this->Topicmodel->setBody($this->input->post('post', TRUE));
			$this->Topicmodel->setDisableBbCode($no_bbcode);
			$this->Topicmodel->setDisableSmiles($no_smile);
			$this->Topicmodel->setImportant($this->input->post('post_type', TRUE));
			$this->Topicmodel->setIp(detectProxy());
			$this->Topicmodel->setLastUpdate($time);
			$this->Topicmodel->setOriginalDate($time);
			$this->Topicmodel->setLocked(0);
			$this->Topicmodel->setPostedUser($this->logged_user);
			$this->Topicmodel->setQuestion(null);
			$this->Topicmodel->setTopicType(0);
			$this->Topicmodel->setViews(0);
			
			$newTopicId = $this->Topicmodel->CreateTopic(); //create topic, get Topic ID.
			
			//see if user wants to subscribe to this topic.
			if ($subscribe) {
				subscriptionManager($this->logged_user, $newTopicId, "subscribe");
			}

			#update board & topic details.
			update_board($bid, $newTopicId, $time, $this->logged_user);
			update_topic($newTopicId, $time, $this->logged_user);
			
			//update user's last post.
			update_user($this->logged_user);
			
			#see if this board can allow post count increase.
			if($this->Boardmodel->getPostIncrement() == 1){
				//get current post count then add on to it.
				post_count($this->logged_user);
			}
			
			//validate user can attach files.
			if($this->Groupmodel->ValidateAccess(1, 26)){ 
				#see if user uploaded a file, if so lets assign the file to the topic.
				$this->db->select('id')
					->from('ebb_attachments')
					->where('Username', $this->logged_user)
					->where('tid', 0)
					->where('pid', 0);
				$query = $this->db->get();
				
				//see if we have anything to assign first.
				if($query->num_rows() > 0) {
					foreach ($query->result() as $row) {
						#add attachment to db for listing purpose.
						$this->Attachmentsmodel->AssignAttachment($newTopicId, 0, $row->id);
					}
				}
			}
			
			//direct user to topic.
			redirect('/boards/viewtopic/'.$newTopicId, 'location');
			
		}
	}
	
	/**
	 * post new topic poll on board.
	 * @example index.php/boards/newpoll/5
	*/
	public function newpoll($bid) {
		//LOAD LIBRARIES
		$this->load->library(array('encrypt', 'email', 'form_validation'));
        $this->load->helper(array('form', 'user', 'posting'));
		
		//get board settings.
		$this->Boardmodel->GetBoardSettings($bid);
		
		//see if user can post on this board.
		if (!$this->Groupmodel->validateAccess(0, $this->Boardaccessmodel->getBPoll())){
			show_error($this->lang->line('nopoll'),403,$this->lang->line('error'));
		} else {
			if (!$this->Groupmodel->ValidateAccess(1, 35)){
				show_error($this->lang->line('nopoll'),403,$this->lang->line('error'));
			}
		}
		
		#see if user can mark topics as important.
		if ($this->Groupmodel->ValidateAccess(1, 39)){
			$CanImportant = TRUE;
		} else {
			$CanImportant = FALSE;
		}
		
		//setup validation rules.
        $this->form_validation->set_rules('topic', $this->lang->line('topic'), 'required|min_length[5]|max_length[50]|callback_SpamFilter|xss_clean');
        $this->form_validation->set_rules('post', $this->lang->line('topicbody'), 'required|min_length[10]|callback_SpamFilter|xss_clean');
		$this->form_validation->set_rules('question', $this->lang->line('question'), 'required|min_length[5]|max_length[50]|xss_clean');
		$this->form_validation->set_rules('pollOpts', $this->lang->line('polloptionfield'), 'required|callback_PollOptionValidation|xss_clean');
		$this->form_validation->set_error_delimiters('<div class="ui-widget" style="width: 45%;"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;text-align:left;"><p id="validateResSvr">', '</p></div></div>');

		//see if any validation rules failed.
		if ($this->form_validation->run() == FALSE) {
			$this->FORM_newtopic($bid, true);
		} else {
			
			//flood check,
			if (flood_check("posting", $this->Usermodel->getLastPost())) {
				#setup error session.
				$this->session->set_flashdata('NotifyType', 'error');
				$this->session->set_flashdata('NotifyMsg', $this->lang->line('flood'));

				#direct user.
				redirect('/boards/viewboard/'.$bid, 'location');
				exit();
			}
			
			//load topic & attachments model.
			$this->load->model(array('Topicmodel', 'Attachmentsmodel'));
			
			//CREATE NEW TOPIC.
			$no_smile = ($this->input->post('no_smile', TRUE) == FALSE) ? FALSE : TRUE;
			$no_bbcode = ($this->input->post('no_bbcode', TRUE) == FALSE) ? FALSE : TRUE;
			$subscribe = ($this->input->post('subscribe', TRUE) == FALSE) ? FALSE : TRUE;
			$pollOptions = explode(PHP_EOL, $this->input->post('pollOpts', TRUE));
			$time = time();
			
			//create new topic.
			$this->Topicmodel->setAuthor($this->logged_user);
			$this->Topicmodel->setBid($bid);
			$this->Topicmodel->setTopic($this->input->post('topic', TRUE));
			$this->Topicmodel->setBody($this->input->post('post', TRUE));
			$this->Topicmodel->setDisableBbCode($no_bbcode);
			$this->Topicmodel->setDisableSmiles($no_smile);
			$this->Topicmodel->setImportant($this->input->post('post_type', TRUE));
			$this->Topicmodel->setIp(detectProxy());
			$this->Topicmodel->setLastUpdate($time);
			$this->Topicmodel->setOriginalDate($time);
			$this->Topicmodel->setLocked(0);
			$this->Topicmodel->setPostedUser($this->logged_user);
			$this->Topicmodel->setQuestion($this->input->post('question', TRUE));
			$this->Topicmodel->setTopicType(1);
			$this->Topicmodel->setViews(0);
			
			$newTopicId = $this->Topicmodel->CreateTopic(); //create topic, get Topic ID.
			
			//loop through our poll options.
			for ($i = 0; $i <= count($pollOptions)-1; $i++) {
				//ensure no blank values get through.
				if (strlen($pollOptions[$i]) > 1) {
					$this->Topicmodel->CreatePoll($pollOptions[$i], $newTopicId); //create poll options
				}
			}
			
			//see if user wants to subscribe to this topic.
			if ($subscribe) {
				subscriptionManager($this->logged_user, $newTopicId, "subscribe");
			}

			#update board & topic details.
			update_board($bid, $newTopicId, $time, $this->logged_user);
			update_topic($newTopicId, $time, $this->logged_user);
			
			//update user's last post.
			update_user($this->logged_user);
			
			#see if this board can allow post count increase.
			if($this->Boardmodel->getPostIncrement() == 1){
				//get current post count then add on to it.
				post_count($this->logged_user);
			}
			
			//validate user can attach files.
			if($this->Groupmodel->ValidateAccess(1, 26)){ 
				#see if user uploaded a file, if so lets assign the file to the topic.
				$this->db->select('id')
					->from('ebb_attachments')
					->where('Username', $this->logged_user)
					->where('tid', 0)
					->where('pid', 0);
				$query = $this->db->get();
				
				//see if we have anything to assign first.
				if($query->num_rows() > 0) {
					foreach ($query->result() as $row) {
						#add attachment to db for listing purpose.
						$this->Attachmentsmodel->AssignAttachment($newTopicId, 0, $row->id);
					}
				}
			}
			
			//direct user to topic.
			redirect('/boards/viewtopic/'.$newTopicId, 'location');
			
		}
	}
	
	/**
	 * New Topics form.
	 * @version 05/29/12
	 * @access private
	*/
	private function FORM_newtopic($bid, $pollTopic) {

		//load breadcrumb library
		$this->load->library('breadcrumb');
		
		//get board settings.
		$this->Boardmodel->GetBoardSettings($bid);
		
		// add breadcrumbs
		$this->breadcrumb->append_crumb($this->title, '/boards/');
		$this->breadcrumb->append_crumb($this->Boardmodel->getBoard(), '/boards/viewboard/'.$bid);
		$this->breadcrumb->append_crumb($this->lang->line("posttopic"), '/boards/newtopic');

		//grab board preferences.
		$boardpref_bbcode = $this->Boardmodel->getBbCode();
		$boardpref_smiles = $this->Boardmodel->getSmiles();
		$boardpref_image = $this->Boardmodel->getImage();
		
		#see if user can mark topics as important.
		if ($this->Groupmodel->ValidateAccess(1, 39)){
			$CanImportant = TRUE;
		} else {
			$CanImportant = FALSE;
		}
		
		#see if poll topic is enabled and user can post a poll topic.
		if (($pollTopic) AND ($this->Groupmodel->ValidateAccess(1, 35))){
			$CanPoll = TRUE;
		} else {
			$CanPoll = FALSE;
		}
		
		#see if user can upload
		if ($this->Groupmodel->ValidateAccess(1, 26)) {
			$uploadLimit = $this->preference->getPreferenceValue("upload_limit");	
		} else {
			$uploadLimit = 0;
		}
		
		//render to HTML.
		echo $this->twig->render($this->style, 'newtopic', array (
		  'boardName' => $this->title,
		  'pageTitle'=> $this->lang->line("newtopic"),
		  'BOARD_URL' => $this->boardUrl,
		  'APP_URL' => $this->boardUrl.APPPATH,
		  'NOTIFY_TYPE' => $this->notifyType,
		  'NOTIFY_MSG' =>  $this->notifyMsg,
		  'LANG' => $this->lng,
		  'TimeFormat' => $this->timeFormat,
		  'TimeZone' => $this->timeZone,
		  'groupAccess' => $this->groupAccess,
		  'LANG_WELCOME'=> $this->lang->line('loggedinas'),
		  'LANG_WELCOMEGUEST' => $this->lang->line('welcomeguest'),
		  'LOGGEDUSER' => $this->logged_user,
		  'LANG_JSDISABLED' => $this->lang->line('jsdisabled'),
		  'LANG_INFO' => $this->lang->line('info'),
		  'LANG_LOGIN' => $this->lang->line('login'),
		  'LANG_LOGOUT' => $this->lang->line('logout'),
		  'LOGINFORM' => form_open('login/LogIn', array('name' => 'frmQLogin')),
		  'NEWTOPICFORM' => form_open('boards/newtopic/'.$bid, array('name' => 'frmNewTopic', 'id' => 'frmNewTopic')),
		  'NEWPOLLFORM' => form_open('boards/newpoll/'.$bid, array('name' => 'frmNewTopic', 'id' => 'frmNewTopic')),
		  'UPLOADFORM' => form_open_multipart('upload/do_upload/'),
		  'VALIDATIONSUMMARY' => validation_errors(),
		  'VALIDATION_USERNAME' => form_error('username'),
		  'VALIDATION_PASSWORD' => form_error('password'),
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
		  'LANG_MEMBERLIST' => $this->lang->line('members'),
		  'LANG_PROFILE' => $this->lang->line('profile'),
		  'LANG_POWERED' => $this->lang->line('poweredby'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby'),
		  'BREADCRUMB' =>$this->breadcrumb->output(),
		  "LANG_POSTINGRULES" => $this->lang->line("postingrules"),
		  "LANG_YES" => $this->lang->line("yes"),
		  "LANG_NO" => $this->lang->line("no"),
		  "LANG_ALLOWSMILES" => $this->lang->line("smiles"),
		  "ALLOWSMILES" => $boardpref_smiles,
		  "LANG_ALLOWBBCODE" => $this->lang->line("bbcode"),
		  "ALLOWBBCODE" => $boardpref_bbcode,
		  "LANG_ALLOWIMG" => $this->lang->line("img"),
		  "ALLOWIMG" => $boardpref_image,
		  "BID" => $bid,
		  "POLLOPTION" => $pollTopic,
		  "LANG_SMILES" => $this->lang->line("moresmiles"),
		  "SMILES" => form_smiles(),
		  "LANG_TOPIC" => $this->lang->line("topic"),
		  "LANG_TOPICBODY" => $this->lang->line('topicbody'),
		  "LANG_UPLOAD" => $this->lang->line("uploadfile"),
		  "LANG_CLEAR" => $this->lang->line("clearfile"),
		  "LANG_VIEWFILES" => $this->lang->line("viewfiles"),
		  "ATTACHMENTLIMIT" => $uploadLimit,
		  "LANG_DISABLERTF" => $this->lang->line("disablertf"),
		  "LANG_OPTIONS" => $this->lang->line("options"),
		  "LANG_POSTTYPE" => $this->lang->line("type"),
		  "GAC_IMPORTANT" => $CanImportant,
		  "LANG_IMPORTANT" => $this->lang->line("important"),
		  "LANG_NORMAL" => $this->lang->line("normal"),
		  "LANG_NOTIFY" => $this->lang->line("notify"),
		  "LANG_DISABLESMILES" => $this->lang->line("disablesmiles"),
		  "LANG_DISABLEBBCODE" => $this->lang->line("disablebbcode"),
		  "LANG_POLL" => $this->lang->line("polltext"),
		  "LANG_POLLOPTIONS" => $this->lang->line('polloptionfield'),
		  "LANG_QUESTION" => $this->lang->line("question"),
		  "LANG_POSTTOPIC" => $this->lang->line("posttopic")
		));
	}
	
	/**
	 * used to quote a topic or post.
	 * @param integer $tid Topic.
	 * @param integer $pid Post ID.
	 * @param integer $type topic=1;post=2.
	 * @example index.php/boards/quote/5/5/2
	 */
	public function quote($tid, $pid, $type) {
		//LOAD LIBRARIES
		$this->load->library(array('encrypt', 'email', 'form_validation'));
        $this->load->helper(array('form', 'user', 'posting'));
		
		$this->FORM_reply($tid, $pid, $type);
	}
	
	/**
	 * reply to a topic.
	 * @example index.php/boards/reply/5/20
	*/
	public function reply($tid) {
		//LOAD LIBRARIES
		$this->load->library(array('encrypt', 'email', 'form_validation'));
        $this->load->helper(array('form', 'user', 'posting'));
		
		//load topic model.
		$this->load->model('Topicmodel');
		
		//load entities
		$this->Topicmodel->GetTopicData($tid);
		$this->Boardmodel->GetBoardSettings($this->Topicmodel->getBid());
		$this->Boardaccessmodel->GetBoardAccess($this->Topicmodel->getBid());
		
		//see if user can post on this board.
		if (!$this->Groupmodel->validateAccess(0, $this->Boardaccessmodel->getBPost())){
			show_error($this->lang->line('nowrite'),403,$this->lang->line('error'));
		} else {
			if (!$this->Groupmodel->ValidateAccess(1, 37)){
				show_error($this->lang->line('nowrite'),403,$this->lang->line('error'));
			}
		}
		
		#see if user can mark topics as important.
		if ($this->Groupmodel->ValidateAccess(1, 39)){
			$CanImportant = TRUE;
		} else {
			$CanImportant = FALSE;
		}
		
		//setup validation rules.
        $this->form_validation->set_rules('reply_post', $this->lang->line('topicbody'), 'required|min_length[10]|callback_SpamFilter|xss_clean');
		$this->form_validation->set_error_delimiters('<div class="ui-widget" style="width: 45%;"><div class="ui-state-error ui-corner-all" style="padding: 0 .7em;text-align:left;"><p id="validateResSvr">', '</p></div></div>');

		//see if any validation rules failed.
		if ($this->form_validation->run() == FALSE) {
			$this->FORM_reply($tid);
		} else {
			
			//flood check,
			if (flood_check("posting", $this->Usermodel->getLastPost())) {
				#setup error session.
				$this->session->set_flashdata('NotifyType', 'error');
				$this->session->set_flashdata('NotifyMsg', $this->lang->line('flood'));

				#direct user.
				redirect('/boards/viewtopic/'.$tid, 'location');
				exit();
			}
			
			//load topic & attachments model.
			$this->load->model(array('Topicmodel', 'Attachmentsmodel'));
			
			//CREATE NEW TOPIC.
			$no_smile = ($this->input->post('no_smile', TRUE) == FALSE) ? FALSE : TRUE;
			$no_bbcode = ($this->input->post('no_bbcode', TRUE) == FALSE) ? FALSE : TRUE;
			$subscribe = ($this->input->post('subscribe', TRUE) == FALSE) ? FALSE : TRUE;
			//$page = ($this->input->post('page', TRUE) == FALSE) ? null : $this->input->post('page', TRUE);
			$time = time();
			
			//create new topic.
			$this->Topicmodel->setAuthor($this->logged_user);
			$this->Topicmodel->setBid($this->Topicmodel->getBid());
			$this->Topicmodel->setTiD($tid);
			$this->Topicmodel->setTopic(null);
			$this->Topicmodel->setBody($this->input->post('reply_post', TRUE));
			$this->Topicmodel->setDisableBbCode($no_bbcode);
			$this->Topicmodel->setDisableSmiles($no_smile);
			$this->Topicmodel->setImportant(null);
			$this->Topicmodel->setIp(detectProxy());
			$this->Topicmodel->setLastUpdate($time);
			$this->Topicmodel->setOriginalDate($time);
			$this->Topicmodel->setLocked(0);
			$this->Topicmodel->setPostedUser($this->logged_user);
			$this->Topicmodel->setQuestion(null);
			$this->Topicmodel->setTopicType(null);
			
			$newPostId = $this->Topicmodel->CreateReply(); //create post, get Post ID.
			
			//see if user wants to subscribe to this topic.
			if ($subscribe) {
				subscriptionManager($this->logged_user, $tid, "subscribe");
			}

			#update board & topic details.
			update_board($this->Topicmodel->getBid(), $tid, $time, $this->logged_user);
			update_topic($tid, $time, $this->logged_user);
			
			//update user's last post.
			update_user($this->logged_user);
			
			#see if this board can allow post count increase.
			if($this->Boardmodel->getPostIncrement() == 1){
				//get current post count then add on to it.
				post_count($this->logged_user);
			}
			
			//validate user can attach files.
			if($this->Groupmodel->ValidateAccess(1, 26)){ 
				#see if user uploaded a file, if so lets assign the file to the topic.
				$this->db->select('id')
					->from('ebb_attachments')
					->where('Username', $this->logged_user)
					->where('tid', 0)
					->where('pid', 0);
				$query = $this->db->get();
				
				//see if we have anything to assign first.
				if($query->num_rows() > 0) {
					foreach ($query->result() as $row) {
						#add attachment to db for listing purpose.
						$this->Attachmentsmodel->AssignAttachment(0, $newPostId, $row->id);
					}
				}
			}
			
			//new topic notification.
			$this->db->select('u.Email, u.Language, tw.username')
			  ->from('ebb_topic_watch tw')
			  ->join('ebb_users u', 'tw.username=u.Username', 'LEFT')
			  ->where('tw.username !=', $this->logged_user)
			  ->where('tw.tid', $tid)
			  ->where('tw.read_status', 0);
			$notificationQ = $this->db->get();
			
			//see if we have any subscribers.
			if($notificationQ->num_rows() > 0) {
				#email user.
				$config = array();
				if ($this->preference->getPreferenceValue("mail_type") == 2) {
					$config['protocol'] = 'sendmail';
					$config['mailpath'] = $this->preference->getPreferenceValue("sendmail_path");
					$this->email->initialize($config);
				} elseif ($this->preference->getPreferenceValue("mail_type") == 0) {
					$config['protocol'] = 'smtp';
					$config['smtp_host'] = $this->preference->getPreferenceValue("smtp_host");
					$config['smtp_user'] = $this->preference->getPreferenceValue("smtp_user");
					$config['smtp_pass'] = $this->preference->getPreferenceValue("smtp_pwd");
					$config['smtp_port'] = $this->preference->getPreferenceValue("smtp_port");
					$config['smtp_timeout'] = $this->preference->getPreferenceValue("smtp_timeout");
					$this->email->initialize($config);
				}

				//loop through data and bind to an array.
				foreach ($notificationQ->result() as $notify) {
					//send out email.        	
					$this->email->to($notify->Email);
					$this->email->from($this->preference->getPreferenceValue("board_email"), $this->title);
					$this->email->subject('RE: '.$this->Topicmodel->getTopic());
					$this->email->message($this->twig->renderNoStyle('/emails/'.$notify->Language.'/eml_new_reply.twig', array(
						'USERNAME' => $notify->username,
						'AUTHOR' => $this->logged_user,						  
						'TITLE' => $this->title,
						'BOARDADDR' => $this->boardUrl,
						'TOPIC_SUMMARY' => substr_replace(nl2br($this->input->post('reply_post', TRUE)),'[...]',100),
						'TID' => $tid
					)));

					//send out email.
					$this->email->send();
				}
			}
			
			//direct user to topic.
			redirect('/boards/viewtopic/'.$tid, 'location');
			
		}
	}
	
	/**
	 * Reply Form.
	 * @param integer $tid TopicID.
	 * @param integer $quoteID Topic/Post ID.
	 * @param integer $quoteType topic=1;post=2.
	 */
	private function FORM_reply($tid, $quoteID=null, $quoteType=null) {
		//load breadcrumb library
		$this->load->library('breadcrumb');
		
		//load topic model.
		$this->load->model('Topicmodel');

		//load entities
		$this->Topicmodel->GetTopicData($tid);
		$this->Boardmodel->GetBoardSettings($this->Topicmodel->getBid());
		$this->Boardaccessmodel->GetBoardAccess($this->Topicmodel->getBid());
		
		//see if a user is quoting someone.
		if (!is_null($quoteID) || !is_null($quoteType)) {
			//see if its the original topic or not.
			if ($quoteType == 1) {
				$quoteMsg = '[quote='.$this->Topicmodel->getAuthor().']'.$this->Topicmodel->getBody().'[/quote]';
			} else {
				$this->db->select('author, Body');
				$this->db->from('ebb_posts');
				$this->db->where('pid', $quoteID);
				$query = $this->db->get();
				
				//see if the post exists.
				if($query->num_rows() > 0) {
					$row = $query->row();
					$quoteMsg = '[quote='.$row->author.']'.$row->Body.'[/quote]';
				} else {
					$quoteMsg = '';
				}
			}
		} else {
			$quoteMsg = '';
		}
		
		// add breadcrumbs
		$this->breadcrumb->append_crumb($this->title, '/boards/');
		$this->breadcrumb->append_crumb($this->Boardmodel->getBoard(), '/boards/viewboard/'.$this->Topicmodel->getBid());
		$this->breadcrumb->append_crumb($this->lang->line("posttopic"), '/boards/newtopic');

		//grab board preferences.
		$boardpref_bbcode = $this->Boardmodel->getBbCode();
		$boardpref_smiles = $this->Boardmodel->getSmiles();
		$boardpref_image = $this->Boardmodel->getImage();
		
		#see if user can upload
		if ($this->Groupmodel->ValidateAccess(1, 26)) {
			$uploadLimit = $this->preference->getPreferenceValue("upload_limit");	
		} else {
			$uploadLimit = 0;
		}
		
		//render to HTML.
		echo $this->twig->render($this->style, 'reply', array (
		  'boardName' => $this->title,
		  'pageTitle'=> $this->lang->line("newtopic"),
		  'BOARD_URL' => $this->boardUrl,
		  'APP_URL' => $this->boardUrl.APPPATH,
		  'NOTIFY_TYPE' => $this->notifyType,
		  'NOTIFY_MSG' =>  $this->notifyMsg,
		  'LANG' => $this->lng,
		  'TimeFormat' => $this->timeFormat,
		  'TimeZone' => $this->timeZone,
		  'groupAccess' => $this->groupAccess,
		  'LANG_WELCOME'=> $this->lang->line('loggedinas'),
		  'LANG_WELCOMEGUEST' => $this->lang->line('welcomeguest'),
		  'LOGGEDUSER' => $this->logged_user,
		  'LANG_JSDISABLED' => $this->lang->line('jsdisabled'),
		  'LANG_INFO' => $this->lang->line('info'),
		  'LANG_LOGIN' => $this->lang->line('login'),
		  'LANG_LOGOUT' => $this->lang->line('logout'),
		  'LOGINFORM' => form_open('login/LogIn', array('name' => 'frmQLogin')),
		  'REPLYFORM' => form_open('boards/reply/'.$tid, array('name' => 'frmReply')),
		  'UPLOADFORM' => form_open_multipart('upload/do_upload/'),
		  'VALIDATIONSUMMARY' => validation_errors(),
		  'VALIDATION_USERNAME' => form_error('username'),
		  'VALIDATION_PASSWORD' => form_error('password'),
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
		  'LANG_MEMBERLIST' => $this->lang->line('members'),
		  'LANG_PROFILE' => $this->lang->line('profile'),
		  'LANG_POWERED' => $this->lang->line('poweredby'),
		  'LANG_POSTEDBY' => $this->lang->line('Postedby'),
		  'BREADCRUMB' =>$this->breadcrumb->output(),
		  "LANG_POSTINGRULES" => $this->lang->line("postingrules"),
		  "LANG_YES" => $this->lang->line("yes"),
		  "LANG_NO" => $this->lang->line("no"),
		  "LANG_ALLOWSMILES" => $this->lang->line("smiles"),
		  "ALLOWSMILES" => $boardpref_smiles,
		  "LANG_ALLOWBBCODE" => $this->lang->line("bbcode"),
		  "ALLOWBBCODE" => $boardpref_bbcode,
		  "LANG_ALLOWIMG" => $this->lang->line("img"),
		  "ALLOWIMG" => $boardpref_image,
		  "LANG_SMILES" => $this->lang->line("moresmiles"),
		  "SMILES" => form_smiles(),
		  "TID" => $tid,
		  "LANG_TOPIC" => $this->lang->line("topic"),
		  "LANG_TOPICBODY" => $this->lang->line('topicbody'),
		  "QUOTE_BODY" => $quoteMsg,
		  "LANG_UPLOAD" => $this->lang->line("uploadfile"),
		  "LANG_CLEAR" => $this->lang->line("clearfile"),
		  "LANG_VIEWFILES" => $this->lang->line("viewfiles"),
		  "ATTACHMENTLIMIT" => $uploadLimit,
		  "LANG_DISABLERTF" => $this->lang->line("disablertf"),
		  "LANG_OPTIONS" => $this->lang->line("options"),
		  "LANG_NOTIFY" => $this->lang->line("notify"),
		  "LANG_DISABLESMILES" => $this->lang->line("disablesmiles"),
		  "LANG_DISABLEBBCODE" => $this->lang->line("disablebbcode"),
		  "LANG_REPLY" => $this->lang->line("btnreply")
		));
	}
	
	/**
	 * vote on a poll.
	 * @example index.php/boards/vote/5
	*/
	public function vote($id) {

		
		//direct user to topic.
		redirect('/boards/viewtopic/'.$id, 'location');
	}
	
	/**
	 * report a topic.
	 * @example index.php/boards/reporttopic/5
	*/
	public function reporttopic($id) {
		$this->FORM_reporttopic($id);
	}
	
	/**
	 * Report Topic Form.
	 * @param integer $id
	 * @version 04/17/12
	 * @access private
	*/
	private function FORM_reporttopic($id) {
		
	}
	
	/**
	 * report a reply.
	 * @example index.php/boards/reportpost/5
	*/
	public function reportpost($id) {

	}
	
	/**
	 * edit a topic.
	 * @example index.php/boards/edittopic/5
	*/
	public function edittopic($id) {

	}
	
	/**
	 * delets topic and all things assocated with it.
	 * @example index.php/boards/deletetopic/5
	*/
	public function deletetopic($id) {

	}
	
	/**
	 * edit a reply.
	 * @example index.php/boards/editpost/5
	*/
	public function editpost($id) {

	}
	
	/**
	 * delete a reply and everything assoicated with it.
	 * @example index.php/boards/deletepost/5
	*/
	public function deletepost($id) {

	}
	
	/**
	 * Getan RSS Feed for the selected Board.
	 * @example index.php/boards/boardFeed/5
	*/
	public function boardFeed($id) {

	}
	
	/**
	 * Getan RSS Feed for the latest posts on the board.
	 * @example index.php/boards/latestpost/5
	*/
	public function latestPost() {

	}
	
}

/* Location: ./application/controllers/boards.php */