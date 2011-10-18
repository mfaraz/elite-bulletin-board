<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
	* boards.php
	* @package Elite Bulletin Board v3
	* @author Elite Bulletin Board Team <http://elite-board.us>
	* @copyright  (c) 2006-2011
	* @license http://opensource.org/licenses/gpl-license.php GNU Public License
	* @version 10/4/2011
*/


class Boards extends EBB_Controller {

	function __construct() {
 		parent::__construct();
		$this->load->model('Boardmodel');
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

		//SQL CI style.
		$query = $this->db->query("SELECT id, Board FROM ebb_boards WHERE type='1' ORDER BY B_Order");
		foreach ($query->result() as $row) {

        	$data[] = $row;

			//build second query.
			$query2 = $this->db->query("SELECT id, Board, Description, last_update, Posted_User, Post_Link, Category FROM ebb_boards WHERE type='2' AND Category=? ORDER BY B_Order", $row->id);
			foreach ($query2->result() as $row2) {

				#board rules sql.
				$boardRule = $this->db->query("SELECT B_Read FROM ebb_board_access WHERE B_id=?", $row2->id);
				$readAccess = $boardRule->row();

				#see if user can view the board.
				if ($this->grouppolicy->validateAccess(0, $readAccess->B_Read) == true){
					$data2[] = $row2;
				}

			}
		}

		#setup filters.
		$this->twig->_twig_env->addFilter('counter', new Twig_Filter_Function('GetCount'));
		$this->twig->_twig_env->addFilter('ReadStat', new Twig_Filter_Function('CheckReadStatus'));
		$this->twig->_twig_env->addFilter('SubBoards', new Twig_Filter_Function('getSubBoard'));
		$this->twig->_twig_env->addFilter('PostedDate', new Twig_Filter_Function('datetimeFormatter'));
		$this->twig->_twig_env->addFunction('boardInfo', new Twig_Function_Function('boardStats'));

		//render to HTML.
		echo $this->twig->render($this->style, 'board_index', array (
		  'pageTitle'=> $this->title,
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
		'LOGINFORM' => form_open('login/LogIn'),
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
		'showInfoBox' => $this->preference->getPreferenceValue("infobox_status")->pref_value,
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
		"NEWESTMEMBER" => "new user tag",
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

		//load pagination library
		$this->load->library('pagination');

		//setup pagination.
		$config['base_url'] = base_url() . 'board/viewboard/'.$id;
		$config['total_rows'] = $this->Boardmodel->CountTopics($id);
		$config['per_page'] = 20;
		$config['uri_segment'] = 4;

		$this->pagination->initialize($config);

	}

	/**
	 * shows topics & all replies tied to it.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/boards/viewtopic/5/1
	*/
	public function viewtopic($id) {

		//load pagination library
		$this->load->library('pagination');

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
