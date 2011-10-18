<?php
if (!defined('IN_EBB')) {
	die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
Filename: MySQL.php
Last Modified: 3/9/2010

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

class dbMySQL{

	#define data member.
	public $SQL;
	private $connStr;
	private $host = DB_HOST;
	private $dbUser = DB_USER;
	private $dbPwd = DB_PASS;
	private $dbNme = DB_NAME;
	
    /**
	*__construct
	*
	*Attempt to initialize Database Connection.
	*
	*@modified 3/9/10
	*
	*@access public
	*/
	public function __construct(){
		try{
			$this->connStr = mysql_connect($this->host, $this->dbUser, $this->dbPwd) or die("Failed to connect to MySQL host.<br />". mysql_error() ."<br /><br /><strong>Line:</strong> ". __LINE__ ."<br /><strong>File:</strong> ". __FILE__);
			mysql_select_db($this->dbNme, $this->connStr) or die("Failed to select mysql DB.<br />". mysql_error() ."<br /><br /><strong>Line:</strong> ". __LINE__ ."<br /><strong>File:</strong> ". __FILE__);

			#tell connection to use UTF-8 encoding.
			mysql_set_charset('utf8', $this->connStr);
    	}catch(Exception $e){
	        $error = new notifySys($e, true, true, __FILE__, __LINE__);
			$error->genericError();
    	}
	}
	
    /**
	*__destruct
	*
	*Attempt to disconnect Database Connection.
	*
	*@modified 8/27/09
	*
	*@access public
	*/
	public function __destruct(){
		mysql_close($this->connStr);
	}

	/**
	*query
	*
	*Performs a basic MySQL query.
	*
	*@modified 3/9/10
	*
	*@access public
	*/
	public function query(){
		try{
		    $query = mysql_query($this->SQL, $this->connStr) or die("Failed to query the database<br />". mysql_error() ."<br /><br /><strong>Line:</strong> ". __LINE__ ."<br /><strong>File:</strong> ". __FILE__."<br /><br />SQL Command:</strong><br /><textarea name=\"sqlquery\" rows=\"5\" cols=\"150\" class=\"text\" readonly=readonly>".$this->SQL."</textarea><br /><br />");

	    	return($query);
    	}catch(Exception $e){
	        $error = new notifySys($e, true, true, __FILE__, __LINE__);
			$error->genericError();
    	}
	}

	/**
	*affectedRows
	*
	*Obtain a number of rows affected by an SQL query.
	*
	*@modified 3/9/10
	*
	*@access public
	*/
	public function affectedRows(){
		try{
		    $affectedRows = mysql_num_rows($this->query());

			return($affectedRows);
    	}catch(Exception $e){
	        $error = new notifySys($e, true, true, __FILE__, __LINE__);
			$error->genericError();
    	}
	}
	
	/**
	*fetchResults
	*
	*Fetch data based on SQL query.
	*
	*@modified 3/9/10
	*
	*@access public
	*/
	public function fetchResults(){
		try{
	    	$dbResult = mysql_fetch_assoc($this->query());

			return($dbResult);
    	}catch(Exception $e){
	        $error = new notifySys($e, true, true, __FILE__, __LINE__);
			$error->genericError();
    	}
	}
	
	/**
	*dbVersion
	*
	*Obtain version of MySQL in use on current connection.
	*
	*@modified 3/9/10
	*
	*@access public
	*/
	public function dbVersion(){
		try{
			$mysqlVersion = mysql_get_server_info($this->connStr);
			$mysqlVersionInfo = substr($mysqlVersion, 0, strpos($mysqlVersion, "-"));

			return($mysqlVersionInfo);
    	}catch(Exception $e){
	        $error = new notifySys($e, true, true, __FILE__, __LINE__);
			$error->genericError();
    	}
	}
	
	/**
	*filterMySQL
	*
	*Filters out anything that can cause SQL injections.
	*
	*@modified 8/27/09
	*
	*@param string $str - string that will be filtered.
	*
	*@return string $string - filtered string to use in SQL query.
	*
	*@access public
	*/
	public function filterMySQL($str){

		$string = mysql_real_escape_string(trim($str), $this->connStr);

		return($string);
	}
}
?>
