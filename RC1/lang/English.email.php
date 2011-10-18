<?php
if (!defined('IN_EBB') ) {
die("<b>!!ACCESS DENIED HACKER!!</b>");
}
/**
 *@Filename: English.email.php
 *@Last Modified: 3/12/2011
 *@Author: Elite Bulletin Board <http://elite-board.us>
*/

//email message for PM Notify.
function pm_notify(){

	return "Hello {pm-receiver},

	{pm-sender} has written you an PM titled, {pm-subject}.

	{boardAddr}/PM.php?action=read&id={pm-id}

	If you wish to stop receiving these notices, just edit your profile.
	======
	This is an automated message, please do not reply to this email.";
}
//email message for digest subscription.
function digest(){

	return "hi {username},

	You have received this because {author} has replied to {topic}.

	{boardAddr}/viewtopic.php?tid={tid}&pid={pid}#{pid}

	There may be other replies to this topic, but you will not receive any new emails until you view this topic.

	If you wish to stop receiving notices about this topic, go to your control panel and click on Subscription Settings.

	regards,

	{title} Staff
	======
	This is an automated message, please do not reply to this email.";
}
//email message for registering account.
function none_confirm(){

	return "Welcome {username},

	You just joined {title}.

	Please remember your login details as your password is encrypted and will require a reset if you forget.

	to login go here:
	{boardAddr}/login.php

	If you wish to modify your profile, you will have to login first, then click on Profile.

	if you have any questions please email the admin of the board.

	{title} Staff
	======
	This is an automated message, please do not reply to this email.";
}
//email verify for user.
function user_confirm(){

	return "Hello,

	You are receiving this because you have registered an account on {title}.

	Please remember your login details as your password is encrypted and will require a reset if you forget.

	But before you can login you have to activate your account by clicking on the link below.

	{boardAddr}/login.php?mode=verify_acct&u={username}&key={key}

	If you don't want to be a member of this community, please contact the administrator of the board so they can delete the account.

	Regards,

	{title} staff
	======
	This is an automated message, please do not reply to this email.";
}
//email verify for admin.
function admin_confirm(){

	return "Hello {username},

	You are receiving this because you have registered an account on {title}.
	
	Please remember your login details as your password is encrypted and will require a reset if you forget.

	Before you can do anything on this board, the administrator has requested that they review your account first. When
	they make their decision, you will get an email whether they accept you or not.

	Regards,

	{title} staff
	======
	This is an automated message, please do not reply to this email.";
}
//email to inform of approved review.
function accept_user(){

	return "Hello,

	You have been approved to become a member of {title}. You may login at:

	{boardAddr}/login.php

	Please remember your login details as your password is encrypted and will require a reset if you forget.

	Regards,

	{title} staff
	======
	This is an automated message, please do not reply to this email.";
}
//email to inform of denied review.
function deny_user(){

	return "Hello,

	The administrator at {title} has rejected your request to join the board.

	To find out why, you may contact them and ask them why they made that decision.

	Regards,

	{title} staff
	======
	This is an automated message, please do not reply to this email.";
}
//lost password email.
function pwd_reset(){

	return "hello,

	you are receiving this email because you requested a password reset from {title}.
	
	If you did not request this, report this to the board administrator.
	
	For security Reasons, your account is disabled until you reverify yourself.
	
	{boardAddr}/login.php?mode=verify_acct&u={username}&key={key}

	a new password was made for you, below is the new password.

	{new-pwd}

	to change it, Click on Profile.
	
	IP Address that sent out the request: {IPAddr}
	
	If this is NOT your IP, report this to your board administrator IMMEDIATELY!

	{title} staff
	======
	This is an automated message, please do not reply to this email.";
}
//report post email.
function report_topic(){
	return "Hello,
	
	It has come to our attention that a user is abusing the board. Below is what the reported user has written:

	Reason for report: {reason}
	Message: {msg}

	the topic can be found at:
	
	{boardurl}/viewtopic.php?bid={bid}&tid={tid}
	======
	This is an automated message, please do not reply to this email.";
}
function report_post(){	

	return "Hello,

	It has come to our attention that a user is abusing the board. Below is what the reported user has written:

	Reason for report: {reason}
	Message: {msg}

	the topic can be found at:

	{boardurl}/viewtopic.php?bid={bid}&tid={tid}#{pid}
	======
	This is an automated message, please do not reply to this email.";
}
#warn user notifications.
function email_notify_warn(){

	return "Hello,

	A moderator at {title} has altered your warning level. Below is what the moderator has written:

	{body}

	======
	This is an automated message, please do not reply to this email.";
}
?>
