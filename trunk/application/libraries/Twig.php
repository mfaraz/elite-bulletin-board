<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}

/**
 * Twig.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 12/01/2011
 * CREDIT
 * @author Bennet Matschullat <bennet.matschullat@giantmedia.de>
 * @since 07.03.2011 - 12:00:39
 */

/**
 * This will help interactTwig within Codeigniter.
*/
class Twig {

	#
	# LOCAL CONSTANCE
	#

    const TWIG_CONFIG_FILE = "twig";
    
	#
	# DATA MEMBERS
	#

    protected $_template_dir;
    protected $_cache_dir;    
    private $ci;
    public $_twig_env;
    
    public function __construct() {
        $this->ci =& get_instance();
        $this->ci->config->load(self::TWIG_CONFIG_FILE); // load config file
        
        // set include path for twig
        ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . APPPATH . 'third_party/Twig');
        require_once (string) 'Autoloader.php';
        
        // register autoloader        
        Twig_Autoloader::register();
        log_message('debug', 'twig autoloader loaded');
        
        // init paths
        $this->_template_dir = $this->ci->config->item('template_dir');
        $this->_cache_dir = $this->ci->config->item('cache_dir');
                
        // load environment
        $loader = new Twig_Loader_Filesystem($this->_template_dir, $this->_cache_dir);
        $this->_twig_env = new Twig_Environment($loader);	
		
		//global Filters/Functions goes here.
		$this->_twig_env->addFunction('URL_TAG', new Twig_Function_Function('anchor'));
		$this->_twig_env->addFunction('LinkTag', new Twig_Function_Function('link_tag'));
		$this->_twig_env->addFunction('IMG', new Twig_Function_Function('img'));
		$this->_twig_env->addFunction('nl_2_br', new Twig_Function_Function('nl2br'));
		$this->_twig_env->addFilter('PostedDate', new Twig_Filter_Function('datetimeFormatter'));
    }

	/**
	 * render a twig template file
	 * @param int $styleID The style ID to look for.
	 * @param string $template template name
	 * @param array $data contains all varnames'
	 * @param boolean $render
	 * @param boolean $return
	 * @return mixed
	 * @version 12/01/11
	*/
    public function render($styleID, $template, $data = array(), $render = true) {

		#do a check to see if the styleID used is valid.
		if($this->StyleCheck($styleID) == 0){
			show_error('Invalid Style Selected.<hr />File:'.__FILE__.'<br />Line:'.__LINE__, 500, $ci->lang->line('error'));
		}else{
			#get the style template path from the db.
			$this->ci->db->select('Temp_Path')->from('ebb_style')->where('id', $styleID);
			$styleQ = $this->ci->db->get();
			$theme = $styleQ->row();

			//load up the template.
			$template = $this->_twig_env->loadTemplate($theme->Temp_Path.'/'.$template.'.twig');
			return ($render)?$template->render($data):$template;
		}
    }

	/**
	 * An internal function to ensure the user is using a valid style ID.
	 * @param integer $styleID style to validate
	 * @access Private
	 * @return boolean
	*/
	private function StyleCheck($styleID){
	    #get the style template path from the db.
		$this->ci->db->select('id')->from('ebb_style')->where('id', $styleID);
		$validateStyle = $this->ci->db->get();

		#return the numeric value.
		return ($validateStyle->num_rows());
	}

	/**
	 * render a twig template file with no style required.
	 * @param string $template template name
	 * @param array $data contains all varnames'
	 * @param boolean $render
	 * @return mixed
	 * @version 12/01/11
	*/
	public function renderNoStyle($template, $data = array(), $render = true) {
        $template = $this->_twig_env->loadTemplate($template);
        //log_message('debug', 'twig template loaded');
        return ($render)?$template->render($data):$template;
    }   
}