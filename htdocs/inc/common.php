<?php

function get_user_name( $nick ) {
	global $users;

	if( null != $users[$nick] ) {
		return $users[$nick];
	} else {
		return $nick;
	}
}

function get_user_link( $nick ) {
	if( $nick ) {
		return '<a href="https://launchpad.net/~' . $nick . '">' . get_user_name( $nick ) . '</a>';
	} else {
		return;
	}
}

function get_meta( $key ) {
	global $db;

	$query = $db->prepare( 'SELECT meta_value FROM meta WHERE meta_key = :meta_key' );
	$query->execute( array( ':meta_key' => $key ) );
	$value = $query->fetch( );

	return $value['meta_value'];
}

function get_cache_time( $spec ) {
	$time = get_meta( 'cache_' . $spec );

	return gmdate( 'r', $time );
}

function progress_bar_percent( $content = null, $total = null, $green = null, $yellow = null, $options = array( ) ) {
	$green_percent = $green / $total * 100;
	$yellow_percent = ( $green + $yellow ) / $total * 100;

	return progress_bar( $content, $green_percent, $yellow_percent, $options );
}

function progress_bar( $content = null, $green = null, $yellow = null, $options = array( ) ) {
	/*
	 *  Print a nicely formatted progress bar
	 *
	 */

	if( $options['direction'] ) { $direction = $options['direction']; } else { $direction = 'right'; }

	$gcol = 'rgba( 60, 180, 60, 0.8 )';
	$ycol = 'rgba( 200, 200, 80, 0.8 )';
	$rcol = 'rgba( 180, 60, 60, 0.8 )';

	$grad = 'linear-gradient( to ' . $direction . ', ' . $gcol . ' 0%, ' . $gcol . ' ' . (int) $green . '%, ' . $ycol . ' ' . (int) $green . '%, ' . $ycol . ' ' . (int) $yellow . '%, ' . $rcol . ' ' . (int) $yellow . '%, ' . $rcol . ' 100% )';

	if( $options['width'] ) { $width = ' width: ' . $options['width'] . '%;'; } else { unset( $width ); }
	if( $options['height'] ) { $height = ' height: ' . $options['height'] . '%;'; } else { unset( $height ); }

	$out = '<div class="bar" style="background: #fff ' . $grad . '; ' . $width . $height . '">';

	$out .= $content;
	$out .= '</div>';

	return $out;
}

function padding_for_days( $amount, $padding, $bar_width ) {
	$padding = ( $amount - 1 ) * $padding;
	$days = $amount * $bar_width;
	$out = $padding + $days;

	return $out;
}

function day_difference( $start, $end ) {
	$start = new DateTime( $start );
	$end = new DateTime( $end );
	$days = $end->diff( $start )->format( '%a' );

	return $days;
}

function cycle_week_num( $target_date ) {
	global $series;

	$days = day_difference( $series['date_start'], $target_date );
	$week_num = ceil( ( $days + 1 ) / 7 );

	$date_start = new DateTime( $series['date_start'] );
	$add_days = new DateInterval( 'P' . ( ( $week_num - 1 ) * 7 ) . 'D' );
	$date_start->add( $add_days );
	$start = $date_start->format( 'M j' );
	$add_days = new DateInterval( 'P6D' );
	$date_start->add( $add_days );
	$end = $date_start->format( 'M j' );

	$week = array(
		'num' => $week_num,
		'start' => $start,
		'end' => $end
	);

	return $week;
}

function teams_js( ) {
	global $db;

	$query = $db->prepare( 'SELECT nick, memberships FROM users' );
	$query->execute( );
	$user_teams = $query->fetchAll( );

	echo '<script type="text/javascript">';
	echo 'var teams = { ';
	foreach( $user_teams as $user ) {
		echo '"' . $user['nick'] . '":"' . $user['memberships'] . '",';
	}
	echo '};';
	echo '</script>';
}

function iframe_srcs( ) {
	echo '<script type="text/javascript">';
	echo 'var iframe_src = { ';
	echo '"calendar": "https://www.google.com/calendar/embed?showTitle=0&showPrint=0&showTabs=0&showCalendars=0&height=400&wkst=2&bgcolor=%23ffffff';
		echo '&src=383qgn907l43kd425bteqjg850%40group.calendar.google.com&color=%232952A3';	// Xubuntu Team calendar
		echo '&src=e_2_en%23weeknum%40group.v.calendar.google.com&color=%23856508';	// Week numbers
		echo '&src=f9ep8rig01nkuegrpcdh6jnl3udn0624%40import.calendar.google.com&color=%231B887A';	// Trello: testing schedule
	echo '&ctz=Etc%2FGMT",';
	echo '"irc": "http://webchat.freenode.net/?channels=xubuntu-devel&nick=tracker.&prompt=1&uio=MTE9MjE131",';
	echo '"wiki": "http://wiki.xubuntu.org/",';
	echo '};';
	echo '</script>';

}

?>