<?php
if (!defined('IN_EBB')) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: XMLProcessor.php
Last Modified: 2/18/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

class XMLProcessor{

	#define data member.
	public $xmlAddr;


	/**
	*getTitle
	*
	*Obtain the title tag in an XML File.
	*
	*@modified 4/19/10
	*
	*@access public
	*/
	public function getTitle(){
	
	    #load XML File.
        $xml = simplexml_load_file($this->xmlAddr);
		
		#get title tag.
		return($xml->channel->title);
	}

	/**
	*getDesc
	*
	*Obtain the description tag in an XML File.
	*
	*@modified 4/19/10
	*
	*@access public
	*/
	public function getDesc(){

	    #load XML File.
        $xml = simplexml_load_file($this->xmlAddr);

		#get title tag.
		return($xml->channel->description);
	}
	
	/**
	*getLink
	*
	*Obtain the link tag in an XML File.
	*
	*@modified 4/19/10
	*
	*@access public
	*/
	public function getLink(){

	    #load XML File.
        $xml = simplexml_load_file($this->xmlAddr);

		#get title tag.
		return($xml->channel->link);
	}

	/**
	*getItem
	*
	*Obtain items from an XML File.
	*
	*@modified 4/19/10
	*
	*@access public
	*/
	public function getItem(){
	
	    #load XML File.
        $xml = simplexml_load_file($this->xmlAddr);
        
        #define item looping variable.
        $xmlItems = '';

		#loop through the items inside this channel.
		foreach ($xml->channel->item as $xmlItem){

			#get title.
			$xmlItems .= '<b><a href="'.$xml->channel->item->link.'">'.$xml->channel->item->title.'</a></b><br />'.nl2br($xml->channel->item->description) . '<br />-'.$xml->channel->item->pubDate.'<hr />';

		}#END ITEM FOREACH.

		return ($xmlItems);
	}#END FUNCT.



}//END CLASS.
?>
