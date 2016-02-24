<?php

/*  Modify this file with your tracker information and people who want mail
 *  reminders and rename to 'mail.php'
 *
 */

global $mail_prefs;

$mail_prefs['from'] = 'noreply@example.com';
$mail_prefs['tracker_baseurl'] = 'http://tracker.example.com/';
$mail_prefs['tracker_title'] = 'Status tracker';

# 'nick' is a Launchpad ID
# 'interval' is either 'daily' or 'weekly'

$mail_prefs = array(
	array(
		'nick' => 'contributor_a',
		'email' => 'c.a@example.com',	
		'interval' => 'daily'
	),
	array(
		'nick' => 'contributor_b',
		'email' => 'ceebee@example.com',
		'interval' => 'weekly'
	),
);

?>