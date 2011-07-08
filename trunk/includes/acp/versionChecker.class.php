<?php
if (!defined('IN_EBB') ) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: versionChecker.class.php
Last Modified: 6/24/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

class versionChecker{

	#define data member.
	private $updateAddr = "http://elite-board.us/updates/versionChecker_v3.xml";
	private $updareAddrBeta = "http://elite-board.us/updates/versionChecker_beta.xml";
	private $showBeta = false;
	private $major;
	private $minor;
	private $patch;
	private $build;

  /**
	 *__construct
	 *creates our object and alidates user can use this service.
	 *@access public
     */
	public function __construct() {
		global $boardPref;

		//see if user wishes to see beta releases.
		if ($this->showBeta == true){
			$UpdateUrl = $this->updareAddrBeta;
		} else {
			$UpdateUrl = $this->updateAddr;
		}

		// use curl if it exists
		if (function_exists('curl_init')) {
			
			//ensures we get a valid response from server, if not, lets fail this.
			if(curlLoadFromUrl($UpdateUrl) == null){
				$this->major = 0;
			}else {
				$xml = new SimpleXMLElement(curlLoadFromUrl($UpdateUrl));

				//get our current version data.
				$this->major = $xml->main;
				$this->minor = $xml->secondary;
				$this->patch = $xml->patch;
				$this->build = $xml->build;
			}
		}else{
			$xml = simplexml_load_file($UpdateUrl);

			//get our current version data.
			$this->major = $xml->main;
			$this->minor = $xml->secondary;
			$this->patch = $xml->patch;
			$this->build = $xml->build;
		}
	}

   /**
		*clears our data members.
		* @access public
	    * */
	public function __destruct(){
		$this->showBeta = false;
		unset($major);
		unset($minor);
		unset($patch);
		unset($build);
	}

	
 /**
	*verifyVersion
	*Verify user has latest version installed.
	*@modified 6/24/11
	*@return bool $versionStatus - true=current;false=outdated.
	*@access public
	*/
	public function verifyVersion(){
	    global $boardPref;

		//major equals 0 means something failed somewhere, auto kill.
		if($this->major == 0){
			return null;
		}

		#check major release number.
		if($this->major < $boardPref->getPreferenceValue("version_main")){
			$versionStatus = false;
		}else{
			#check minor release number.
			if($this->minor < $boardPref->getPreferenceValue("version_minor")){
				$versionStatus = false;
			}else{
				#check patch release.
				if($this->patch < $boardPref->getPreferenceValue("version_patch")){
					$versionStatus = false;
				}else{
					#check build.
					if($this->build != $boardPref->getPreferenceValue("version_build")){
						$versionStatus = false;
					}else{
						$versionStatus = true;
					}#END BUILD CHECK.
				}#END PATCH CHECK.
			}#END MINOR CHECK.
		}#END MAJOR CHECK.
		
		return ($versionStatus);
	}

}//END CLASS
?>
