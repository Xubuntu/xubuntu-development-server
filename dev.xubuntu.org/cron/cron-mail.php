<?php

/*  Init and config
 *
 */

include 'init.php';
include '../config/mail.php';

/*  See which reminders we want to send this time
 *
 */

mail_reminders( );

function mail_reminders( ) {
	global $mail_prefs;

	foreach( $mail_prefs['users'] as $user ) {
		/* If the interval isn't set, don't send mail */
		if( isset( $user['interval'] ) ) {
			/* Send mail if the interval is 'weekly' and it's Monday, or if the interval is 'daily' */
			if( ( $user['interval'] == 'weekly' && date( 'N' ) == 1 )
				|| $user['interval'] == 'daily' ) {
				send_reminder( $user['nick'], $user['email'] );	
			}
		}
	}
}

/*  Function to send out mail
 *
 */

function send_reminder( $nick, $mail ) {
	global $db, $default_series, $mail_prefs;

	$old_status = ''; $i = 0;

	$headers = 'From: ' . $mail_prefs['from'];
	$subject = 'Summary of open work items';

	$query = $db->prepare( 'SELECT * FROM users WHERE nick = :nick LIMIT 1' );
	$query->execute( array( ':nick' => $nick ) );
	$user = $query->fetchAll( );

	$message = "Hello " . $user[0]['name'] . ",\n\nhere's a summary of your open work items. Please update them as appropriate.\n";

	$query = $db->prepare( 'SELECT * FROM status WHERE nick = :nick AND status != "DONE" AND series = :series ORDER BY FIELD( status, "TODO", "INPROGRESS", "BLOCKED", "POSTPONED" )' );
	$query->execute( array( ':nick' => $nick, ':series' => $default_series['series'] ) );
	$items = $query->fetchAll( );

	foreach( $items as $item ) {
		if( $item['status'] != $old_status ) {
			$message .= "\n" . $item['status'] . ":\n";
		}
		$message .= "- " .strip_tags( $item['description'] ) . "\n";
		$old_status = $item['status'];
		$i++;
	}
	$message .= "\n";

	$message .= "For the list of your work items, go to:\n";
	$message .= $mail_prefs['tracker_baseurl'] . "#tab-details/a=" . $nick  . "\n\n";

	$message .= "--\nSent automatically by the " . $mail_prefs['tracker_title'] . "\n" . $mail_prefs['tracker_baseurl'];

	if( $i > 0 ) {
		mail( $mail, $subject, $message, $headers );
	}
}

?>
