<?php
if (!defined('IN_EBB') ) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: validation.class.php
Last Modified: 1/10/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

class validation{

 	/**
	*validateEmail
	*
	*validate Emails by RegEx and by MX Checking.
	*
	*@modified 1/10/11
	*
	*@param str[str] - email to validate.
	*
	*@access public
	*/
	public function validateEmail($str){

		#Level 1 - see if anything is there.
		if (!empty($str)){
		    #Level 2 - see if it meets the correct format(name@domain)
			if(preg_match("(\w[-._\w]*\w@\w[-._\w]*\w\.\w{2,3})",$str)){
			    #Level 3 - see if the MX record is valid.
	            if(checkdnsrr(array_pop(explode("@",$str)),"MX")){
					return (true);
				}else{
					return (false);
				}#END L3.
			}else{
				return (false);
			}#END L2.
		}else{
			return (false);
		}#END L1.
	}#END FUNCT.
	
 	/**
	*bannedEmails
	*
	*validate Emails by DB to see if its blacklisted.
	*
	*@modified 7/12/10
	*
	*@param str[str] - email to validate.
	*
	*@access public
	*/
    #NOTE: rewrite this to simplify the logic of this function.
	public function bannedEmails($str){

		global $db;

		#domain check.
		$checkDomain = explode("@", $str);

		$db->SQL = "SELECT match_type, ban_item FROM ebb_banlist WHERE ban_type='Email' AND ban_item like '$checkDomain[1]' or ban_item='$str'";
		$emailMatchChk = $db->affectedRows();
		$emailBanSQL = $db->query();

		#see if the email being used is blacklisted or not.
		if ($emailMatchChk == 0){
			return (false);
		}else{
			while ($row = mysql_fetch_assoc($emailBanSQL)) {
				if ($row['match_type'] == "Wildcard") {
					return(true);
				}else{
					if ($row['ban_item'] == $str) {
						return(true);
					}#END EXACT CHECK.
				}#END WILDCARD CHECK.
			}#END WHILE LOOP.
		}#END SQL COUNT CHECK.
	}#END FUNCT.
	
 	/**
	*blacklistedUsernames
	*
	*validate usernames by DB to see if its blacklisted.
	*
	*@modified 7/12/10
	*
	*@param str[str] - username to validate.
	*
	*@access public
	*/
	#NOTE: rewrite this to simplify the logic of this function.
	public function blacklistedUsernames($str){

		global $db;

		$db->SQL = "SELECT blacklisted_username FROM ebb_blacklist";
		$result = $db->query();

		while($row = mysql_fetch_assoc($result)) {
			if (stristr($str, $row['blacklisted_username']) == true) {
				return(true);
			}else{
				return(false);
			}#END CHECK.
		}#END WHILE LOOP.
	}#END FUNCT.

 	/**
	*validateNumeric
	*
	*validate string is a number.
	*
	*@modified 8/10/09
	*
	*@param str[str] - string to validate.
	*
	*@access public
	*/
	public function validateNumeric($str){
	
		#Level 1 - see if anything is there.
		if(!empty($str)){
		    #Level 2 - See if the variable is a number.
			if(is_numeric($str)){
			    return (true);
			}else{
			    return (false);
			}#END L2.
		}else{
			return (false);
		}#END L1.
	}#END FUNCT.
	
 	/**
	*validateAlpha
	*
	*validate string contains only alpha characters.
	*
	*@modified 8/10/09
	*
	*@param str[str] - string to validate.
	*
	*@access public
	*/
	public function validateAlpha($str){

		#Level 1 - check for empty string.
		if(!empty($str)){
			#Level 2 - perform a Regex check to determine the string is all alpha.
			if(preg_match("/^[a-zA-Z]+$/", $str)){
				return (true);
			}else{
			    return (false);
			}#END L2.
		}else{
		    return(false);
		}#END L1.
	}#END FUNCT.
	
 	/**
	*validateAlphaNumeric
	*
	*validate string contains only alpha and numeric characters.
	*
	*@modified 8/10/09
	*
	*@param str[str] - string to validate.
	*
	*@access public
	*/
	public function validateAlphaNumeric($str){
	
		#Level 1 - check for empty string.
		if(!empty($str)){
		    #Level 2 - perform a Regex check to determine the string is either alpha or numeric.
			if(preg_match("/^[a-zA-Z0-9]+$/", $str)){
			    return(true);
			}else{
			    return(false);
			}#END L2.
		}else{
			return(false);
		}#END L1.
	}#END FUNCT.
}//END CLASS
?>
