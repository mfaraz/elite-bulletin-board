<?php
if (!defined('IN_EBB') ) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: templateEngine.php
Last Modified: 7/11/2010

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

class templateEngine{

    #declare data members
    private $styleDir;
    private $output;
    private $tags = array();
        
	/**
	*__construct
	*
	*Opens defined template file from defined template style.
	*
	*@modified 3/9/10
	*
	*@param styleID[int] - Style ID to look for.
	*@param $file[str] - template file to open.
	*
	*@access public
	*/
	public function __construct($styleID, $file){
	
		global $db, $boardDir;
		
		#do a check to see if the styleID used is valid.
		if($this->StyleCheck($styleID) == 0){
			$error = new notifySys("Invalid Style Selected.", false, true, __FILE__, __LINE__);
			$error->genericError();
		}else{
			#get the style template path from the db.
			$db->SQL = "SELECT Temp_Path FROM ebb_style WHERE id='$styleID'";
			$theme = $db->fetchResults();
		}

		#set styleDir to the path of the requested styleID.
		$this->styleDir = trailingSlashRemover($_SERVER['DOCUMENT_ROOT']).'/'.$boardDir.'/template/'.$theme['Temp_Path'].'/';
		
		#see if template file exists.
		if (!file_exists($this->styleDir.$file.'.htm')){
			#throw new Exception('Template file ('.$this->styleDir.'/'.$file.'.htm) was not found.');
            $error = new notifySys('Template file ('.$this->styleDir.$file.'.htm) was not found.', false, true, __FILE__, __LINE__);
			$error->genericError();
		}else{
			#get the contents of the template file
			$contents = file_get_contents($this->styleDir.$file.'.htm');
			
			#see if template file is empty.
			if(empty($contents)){
				$error = new notifySys('Template file is empty.', false, true, __FILE__, __LINE__);
				$error->genericError();
			}else{
				#Add template contents into output variable.
				$this->output = $contents;				
			}			 
		}		
	}

 	/**
	*__destruct
	*
	*Cleans any data that was set in defining the object.
	*
	*@modified 8/14/09
	*
	*@access public
	*/
	public function __destruct(){

		unset($this->styleDir);
	    unset($this->output);
	    unset($this->tags);
	}

 	/**
	*styleCheck
	*
	*An internal function to ensure the user is using a valid style ID.
	*
	*@modified 8/14/09
	*
	*@param styleID[int] - style ID to validate.
	*
	*@access private
	*/
	private function StyleCheck($styleID){

		global $db;
		
	    #get the style template path from the db.
		$db->SQL = "SELECT id FROM ebb_style where id='$styleID'";
		$validateStyle = $db->affectedRows();
		
		#return the numeric value.
		return ($validateStyle);
	}
	
 	/**
	*displayPath
	*
	*Provides full path to the defined style.
	*
	*@modified 8/14/09
	*
	*@param styleID[int] - style ID to gain path from.
	*
	*@access public
	*/
	public function displayPath($styleID){
	
		global $boardDir, $db;
		
		#get the style template path from the db.
		$db->SQL = "SELECT Temp_Path FROM ebb_style WHERE id='$styleID' LIMIT 1";
		$theme = $db->fetchResults();
	
		$tempPath = '/'.$boardDir.'/template/'.$theme['Temp_Path'].'/';
	    return($tempPath);
	}
	
 	/**
	*parseTags
	*
	*Parsing function that will parse the tags used in the template engine.
	*
	*@modified 3/9/10
	*
	*@param tags[str] - tags that will be parsed.
	*
	*@access public
	*/
	public function parseTags($tags){
	
		if(count($tags)<1){
            $error = new notifySys('No tags were found to be parsed.', false, true, __FILE__, __LINE__);
			$error->genericError();
        }
		
		foreach($tags as $tag=>$data){
			#if data is array, traverse recursive array of tags
            if(is_array($data)){
            	$this->output = preg_replace("/\{$tag/",'', $this->output);
            }
            $this->output = str_replace('{'.$tag.'}', $data, $this->output);
        }
	}
	
 	/**
	*removeBlock
	*
	*removes a block of code from output.
	*
	*@modified 7/11/10
	*
	*@param blockName[str] - black that will removed from the template output.
	*
	*@access public
	*/
	public function removeBlock($blockName){

    	#search a match for the expression
    	preg_match ('#<!-- START ' . $blockName . ' -->([^*]+)<!-- END ' . $blockName . ' -->#', $this->output, $emptyBlock);

    	#replace the match with an empty string
    	$this->output = str_replace($emptyBlock, '', $this->output);
	}
	
 	/**
	*getBlocks
	*
	*Look for and parse blocks.
	*
	*@modified 8/14/09
	*
	*@param block[str] - name of block to look for.
	*
	*@access public
	*/
	public function getBlock($block){

		preg_match ('#<!-- START '. $block . ' -->([^.]+)<!-- END '. $block . ' -->#',$this->output,$this->return);
		$code = str_replace ('<!-- START '. $block . ' -->', "", $this->return[0]);
		$code = str_replace ('<!-- END '  . $block . ' -->', "", $code);
		return $code;
	}

 	/**
	*replaceBlockTags
	*
	*Simple MySQL addition to getBlock that will parse simple MySQL-based blocks.
	*
	*@modified 12/8/09
	*
	*@param blockName[str] - name of block to look for.
	*@param query[str] - MySQL query to use.
	*
	*@access public
	*/
	public function replaceBlockTags($blockName, $q) {

		while ($tags = mysql_fetch_assoc($q)) {
			$block = $this->getBlock($blockName);
			foreach ($tags as $tag => $data) {
				$block = str_replace("{" . $tag . "}", $data, $block);
			}
			$blockPage .= $block;
		}
		$this->output = str_replace($this->return[0], $blockPage, $this->output);
		unset($blockPage);
	} 
	
 	/**
	*outputHtml
	*
	*Output final product after final parsing.
	*
	*@modified 8/14/09
	*
	*@access public
	*/
	public function outputHtml(){
		return $this->output;
	}
}
?>
