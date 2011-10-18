<?php
define('IN_EBB', true);
/**
Filename: smiles.php
Last Modified: 2/22/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/
require_once "config.php";
require_once FULLPATH."/header.php";

#load template file.
$tpl = new templateEngine($style, "smiles-head");
$tpl->parseTags(array(
  "TITLE" => "$title",
  "LANG-TITLE" => "$lang[moresmiles]",
  "LANG-TEXT" => "$lang[smiletxt]"));

echo $tpl->outputHtml();

#setup smiles list.
$x=0; // we will use this to count to 4 later

$db->SQL = "SELECT code, img_name FROM ebb_smiles";
$smiles = $db->query();

while($smilesData = mysql_fetch_assoc($smiles)){
	if (($x % 2) == 0) {
		#load template file.
		$tpl = new templateEngine($style, "smiles-end");
		echo $tpl->outputHtml();

		$x=0; // $x is now 4 so we reset it here to start the next line
	}
	#load template file.
   	$tpl = new templateEngine($style, "smiles");
	$tpl->parseTags(array(
	"SMILEIMG" => "$smilesData[img_name]",
	"SMILECODE" => "$smilesData[code]"));
	echo $tpl->outputHtml();
	$x++; // increment $x by 1 so we get our 4
}
#load template file.
$tpl = new templateEngine($style, "smiles-foot");

echo $tpl->outputHtml();

ob_end_flush();
?>
