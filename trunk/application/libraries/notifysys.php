<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * notifySys.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 10/10/2011
*/

class notifysys{

    #declare data members
    
	/**
	 * Error message to present to user.
	 * @var string
	*/
	private $msg;
	
	/**
	 * Header of Error Message
	 * @var string
	*/
	private $errorHeader;
	
	
	/**
	 * Display debug information.
	 * @var boolean
	*/
	private $debugStat;
	
	/**
	 * File Error Occurred.
	 * @var string
	*/
	private $errFile;
	
	/**
	 * Line Number Error Occurred.
	 * @var integer
	*/
	private $errLine;
	
	/**
	 * @var object CodeIgniter object.
	*/
	private $ci;

	/**
	 * Build Error Message.
	 * @version 10/10/11
	 * @param string $message - Message to be displayed later.
	 * @param boolean $titlestat - determines if breadcumb will be displayed or not.
	 * @param boolean $debug - See if we wish to see debugging details (default value is false).
	 * @param string $file - The filename where the error occured at (default value is N/A).
	 * @param string $line - The line where the error occured at (default value is N/A).
	 * @access public
	*/
	public function __construct($params){

		$this->ci =& get_instance();
		
		#define some values to use in the error class.
		$this->msg = $params['message'];
		$this->errorHeader = $params['eheader'];
		$this->debugStat = $params['debug'];
		$this->errFile = $params['file'];
		$this->errLine = $params['line'];
	}
	
    /**
	 *  Clears all data members.
	 * @version 5/18/11
	 * @access public
	*/
    public function __destruct(){
		unset($this->msg);
	    unset($this->displayTitle);
	    unset($this->debugStat);
		unset($this->errFile);
	    unset($this->errLine);
	}

    /**
	 * Displays error message that will match the current style being used by the user.
	 * @version 7/21/11
	 * @access public
	*/
	public function displayError(){

		global $title, $lang, $style;

		//see if we're in install-mode
		if (!defined('EBBINSTALLED')) {
			$tpl = new templateEngine(0, "error", "installer");
		} else {
			$tpl = new templateEngine($style, "error");
		}

		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[error]",
		"ERRORMSG" => "$this->msg",
		"FILE" => "$this->errFile",
		"LINE" => "$this->errLine"));

		#see if the titlebar show display.
		if ($this->displayTitle == false){
			$tpl->removeBlock("titlebar");
		}
		
		#see if we need debugging info(filename & line of error).
		if($this->debugStat == false){
		    $tpl->removeBlock("debug");
		}
	
		#output result
		echo $tpl->outputHtml();

		#halt further processing.
		exit;
	}

    /**
		* Displays general message that will match the current style being used by the user.
		* @version 7/21/11
		* @access public
	*/
	public function displayMessage(){
	
		global $title, $lang, $style;

		//see if we're in install-mode
		if (!defined('EBBINSTALLED')) {
			$tpl = new templateEngine(0, "error-message", "installer");
		} else {
			$tpl = new templateEngine($style, "error-message");
		}
		
		$tpl->parseTags(array(
		"TITLE" => "$title",
		"LANG-TITLE" => "$lang[info]",
		"ERRORMSG" => "$this->msg",
		"FILE" => "$this->errFile",
		"LINE" => "$this->errLine"));

		#see if the titlebar show display.
		if ($this->displayTitle == false){
			$tpl->removeBlock("titlebar");
		}

		#see if we need debugging info(filename & line of error).
		if($this->debugStat == false){
		    $tpl->removeBlock("debug");
		}

		#output result
		echo $tpl->outputHtml();
	}

	/**
	 * Setup Error for Ajax Requests.
	 * @param str $type - what type  of message are we displaying.
	 * @access Public
	 * @version 7/21/2011
	*/
	public function displayAjaxError($type){
		global $style;

		//see if we're in install-mode
		if (!defined('EBBINSTALLED')) {
			$tpl = new templateEngine(0, "error-ajax", "installer");
		} else {
			$tpl = new templateEngine($style, "error-ajax");
		}
		
		$tpl->parseTags(array(
		"ERRORMSG" => "$this->msg"));

		#see if the titlebar show display.
		if ($type == "success"){
			$tpl->removeBlock("error");
			$tpl->removeBlock("warning");
		} elseif ($type == "warning") {
			$tpl->removeBlock("error");
			$tpl->removeBlock("success");
		} else {
			$tpl->removeBlock("success");
			$tpl->removeBlock("warning");
		}

		#output result
		echo $tpl->outputHtml();
	}

    /**
	 * Displays generic error message used when style isn't important or loaded.
	 * @version 10/7/11
	 * @access public
	*/
	public function genericError(){

    	#see if we need debugging info(filename & line of error).
		if($this->debugStat == false){
			show_error($this->msg, 500, $this->errorHeader);
		}else{
			show_error($this->msg.'<hr />File:'.$this->errFile.'<br />Line:'.$this->errLine, 500, $this->errorHeader);
		}
	}
}
?>
