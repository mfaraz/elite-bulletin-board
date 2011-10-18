<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
*
* SWIFT MAILER CONFIGURATION
*
*/

$config['SMTP_HOST'] = ''; //Host of SMTP Server.
$config['SMTP_PORT'] = ''; //Port of SMTP Server.
$config['SMTP_USER'] = ''; //Username to access SMTP Server.
$config['SMTP_PASS'] = ''; //Password to access SMTP Server.
$config['SMTP_ENCRYPTION'] = ''; //SMTP Encryption (ssl, tls)
$config['SENDMAIL_PATH'] = ''; //Sendmail Path.
$config['ENABLE_ANTIFLOOD'] = true; //Enable Mail Anti-Flood.
$config['ENABLE_TEMPLATE'] = true; //Enable Email Templates.
$config['ANTIFLOOD_TIME'] = '30'; //Time in seconds, before mailer pauses.
$config['ANTIFLOOD_EMAILS'] = '100'; //Amount of emails before mailer pauses.
?>
