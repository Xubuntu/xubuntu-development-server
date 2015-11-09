<?php

/*  Modify this file to include people who want mail reminders
 *  and rename to 'mail.php'
 *
 */

# 'nick' is a Launchpad ID
# 'interval' is either 'daily' or 'weekly'

global $mail_prefs;

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