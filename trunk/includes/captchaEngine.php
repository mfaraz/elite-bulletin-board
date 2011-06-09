<?php
session_start();
/**
Filename: captchaEngine.php
Last Modified: 7/11/2010

Term of Use:
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
*/

/**
This CAPTCHA Engine will make use of Q & A, which will be randomly drawn out.

This method will be a better way of filtering spambots.

Plus part will be admins can add onto question array() to customize questions
and make it more suitable for their own theme(s) or complexity.
*/

/**
SETTINGS:

You are free to add new questions or even replace the current set with a new set that will better match your targeted audience.

If you find many false positives, try to make the questions easier.
*/

#array list of Q&A.
$q = array("2+2=?", "Type in the word 'PHP'", "1+1=?", "Type the word 'Apple'",
"Number of U.S. States","How many days in a year?", "How many days in a week?",
"How many weeks in a year?", "Type in 'Bob'", "3+3=?", "10-2=?", "What are these?; Red, Green, Blue, Yellow",
"What are these?; 2,4,6,8,...", "What are these? Windows, Mac, Linux", "2x2=?",
"6 divided by 2=?", "Type in the word 'pizza'", "July 4th is what U.S. Holiday?", "First U.S President", "7x10=?");

$a = array("4", "PHP", "2", "Apple", "50", "365", "7", "52", "Bob", "6", "8",
"colors", "even numbers", "operating systems", "4", "3", "pizza", "Independence Day", "George Washington", "70");

/**
DO NOT ALTER ANYTHING PAST THIS POINT!!
*/

#Define a table with colors (the values are the RGB components for each color).
$colors[0] = array(119,136,153);
$colors[1] = array(16,78,139);
$colors[2] = array(122,139,139);
$colors[3] = array(96,123,139);
$colors[4] = array(123,104,238);
$colors[5] = array(24,116,205);

#randmize some values.
$randQ = array_rand($q, 1);
$randColor = rand(0, 5);

//Creates an image from a jpeg file.
$bgImg = imagecreatefromjpeg("../images/noise3.jpg");

#get text color.
$textColor = imagecolorallocate($bgImg, $colors[$randColor][0],$colors[$randColor][1], $colors[$randColor][2]);

#We place the answer in a session for checking later on.
$_SESSION['CAPTCHA_Ans'] = $a[$randQ];

#Get text ready for the image.
imagestring($bgImg, 3, 0, 10, $q[$randQ], $textColor);

#Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

#always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

#HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

#HTTP/1.0
header("Pragma: no-cache");
header('Content-type: image/jpeg');

#Output image to browser
imagejpeg($bgImg);

#Destroys the image
imagedestroy($bgImg);
?>
