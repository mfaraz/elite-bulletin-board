<?php
if (!defined('BASEPATH')) {exit('No direct script access allowed');}
/**
 * SwiftMailer.php
 * @package Elite Bulletin Board v3
 * @author Elite Bulletin Board Team <http://elite-board.us>
 * @copyright  (c) 2006-2011
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 9/6/2011
*/

class SwiftMailer {

	private $ci;	


	/**
	* Loads Swift Mailer Library
	*/
	public function __construct($mailType, $subject, $from, $to, $body, $batch, $emlTemplate) {
	
		//load up configuration object.
		$this->ci =& get_instance();
        $this->ci->config->load('SwiftMailer'); // load config file
	
		// set include path for twig
        ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . APPPATH . 'third_party/swift');
        require_once (string) 'swift_required.php';
        
        //see what way we're sending out emails.
        switch($mailType){
        case 'mail':
        	$this->SendByMail($subject, $from, $to, $body, $batch, $emlTemplate);
        break;
        case 'sendmail':
        	$this->SendBySendmail($subject, $from, $to, $body, $batch, $emlTemplate);
        break;
        case 'smtp':
        	$this->SendBySMTP($subject, $from, $to, $body, $batch, $emlTemplate);
        break;
        default:
        	die('invalid selection.');
        break;
        }

	}
	
	//send by mail().
	private function SendByMail($subject, $from, $to, $body, $batch, $emlTemplate) {
		//Create the Transport
		$transport = Swift_MailTransport::newInstance();

		//Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);
		
		#build email.
		$message = Swift_Message::newInstance($subject)
			->setFrom($from) //Set the From address
			->setTo($to)
			->setBody($body); //set email body
		
		//see if anti-flood is enabled.
		if ($this->ci->config->item('ENABLE_ANTIFLOOD')) {
			//setup anti-flood plugin.
			if ($this->ci->config->item('ANTIFLOOD_TIME') == ""){
				$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin($this->ci->config->item('ANTIFLOOD_EMAILS'))); //pauses after X amount of emails.
			} else {
				$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin($this->ci->config->item('ANTIFLOOD_EMAILS'), $this->ci->config->item('ANTIFLOOD_TIME'))); //pauses after X amount of seconds
			}
		}
		
		//see if mailer template is enabled.
		if ($this->ci->config->item('ENABLE_TEMPLATE')) {
			#setup mailer template.
			$decorator = new Swift_Plugins_DecoratorPlugin($emlTemplate);
			$mailer->registerPlugin($decorator);
		}
		
		#Send email(s).
		if($batch) {
			$mailer->batchSend($message);
		} else {
			$mailer->Send($message);		
		}
	}
	
	//Sends Email by Sendmail.
	private function SendBySendmail($subject, $from, $to, $body, $batch, $emlTemplate) {
	
		//Create the Transport
		$transport = Swift_SendmailTransport::newInstance($this->ci->config->item('SENDMAIL_PATH').' -bs');

		//Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);
		
		#build email.
		$message = Swift_Message::newInstance($subject)
			->setFrom($from) //Set the From address
			->setTo($to)
			->setBody($body); //set email body
		
		//see if anti-flood is enabled.
		if ($this->ci->config->item('ENABLE_ANTIFLOOD')) {
			//setup anti-flood plugin.
			if ($this->ci->config->item('ANTIFLOOD_TIME') == ""){
				$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin($this->ci->config->item('ANTIFLOOD_EMAILS'))); //pauses after X amount of emails.
			} else {
				$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin($this->ci->config->item('ANTIFLOOD_EMAILS'), $this->ci->config->item('ANTIFLOOD_TIME'))); //pauses after X amount of seconds
			}
		}
		
		//see if mailer template is enabled.
		if ($this->ci->config->item('ENABLE_TEMPLATE')) {
			#setup mailer template.
			$decorator = new Swift_Plugins_DecoratorPlugin($emlTemplate);
			$mailer->registerPlugin($decorator);
		}
		
		#Send email(s).
		if($batch) {
			$mailer->batchSend($message);
		} else {
			$mailer->Send($message);		
		}	
	}
	
	//Sends Email by SMTP.
	private function SendBySMTP($subject, $from, $to, $body, $batch, $emlTemplate) {
	
		#see if we're using some form of encryption.
		if ($this->ci->config->item('SMTP_ENCRYPTION') == ""){
			//Create the Transport
			$transport = Swift_SmtpTransport::newInstance($this->ci->config->item('SMTP_HOST'), $this->ci->config->item('SMTP_PORT'))
				->setUsername($this->ci->config->item('SMTP_USER'))
				->setPassword($this->ci->config->item('SMTP_PASS'));
		} else{
			//Create the Transport
			$transport = Swift_SmtpTransport::newInstance($this->ci->config->item('SMTP_HOST'), $this->ci->config->item('SMTP_PORT'), $this->ci->config->item('SMTP_ENCRYPTION'))
				->setUsername($this->ci->config->item('SMTP_USER'))
				->setPassword($this->ci->config->item('SMTP_PASS'));
		}

		//Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);
		
		#build email.
		$message = Swift_Message::newInstance($subject)
			->setFrom($from) //Set the From address
			->setTo($to)
			->setBody($body); //set email body
		
		//see if anti-flood is enabled.
		if ($this->ci->config->item('ENABLE_ANTIFLOOD')) {
			//setup anti-flood plugin.
			if ($this->ci->config->item('ANTIFLOOD_TIME') == ""){
				$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin($this->ci->config->item('ANTIFLOOD_EMAILS'))); //pauses after X amount of emails.
			} else {
				$mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin($this->ci->config->item('ANTIFLOOD_EMAILS'), $this->ci->config->item('ANTIFLOOD_TIME'))); //pauses after X amount of seconds
			}
		}
		
		//see if mailer template is enabled.
		if ($this->ci->config->item('ENABLE_TEMPLATE')) {
			#setup mailer template.
			$decorator = new Swift_Plugins_DecoratorPlugin($emlTemplate);
			$mailer->registerPlugin($decorator);
		}
		
		#Send email(s).
		if($batch) {
			$mailer->batchSend($message);
		} else {
			$mailer->Send($message);		
		}
	}
}
?>
