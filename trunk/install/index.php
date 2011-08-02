 <?php
define('IN_EBB', true);
/**
Filename: index.php
Last Modified: 7/21/2011

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

require_once "../includes/function.php";
require_once "../includes/notifySys.php";
require_once "../includes/templateEngine.php";

$tpl = new templateEngine(0, "installer-index", "installer");
echo $tpl->outputHtml();
?>
