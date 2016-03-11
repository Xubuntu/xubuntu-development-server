<?php

/*
 *  Initialize
 *
 */

function init_status( ) {
	include 'common.php';
	include 'output.php';

	include '../config/db.php';

	init_status_series( );

	init_spec_data( );
	init_user_data( );
}

function init_status_series( ) {
	global $db, $series;

	if( isset( $_GET['s'] ) ) {
		$query = $db->prepare( 'SELECT * FROM series WHERE series = :series' );
		$query->execute( array( ':series' => $_GET['s'] ) );
		$series = $query->fetchAll( )[0];
	} else {
		foreach( $db->query( 'SELECT * FROM series WHERE default_series = 1' ) as $row ) {
			$series = $row;
		}
	}
}

function init_spec_data( ) {
	global $db, $series, $specs;

	$query = $db->prepare( 'SELECT spec, name, url, item_count, whiteboard FROM specs WHERE series = :series' );
	$query->execute( array( ':series' => $series['series'] ) );
	$specs_raw = $query->fetchAll( );

	foreach( $specs_raw as $spec_raw ) {
		$specs[$spec_raw['spec']] = $spec_raw;
		$specs[$spec_raw['spec']]['link_slug'] = '<a href="' . $spec_raw['url'] . '">' . $spec_raw['spec'] . '</a>';
		$specs[$spec_raw['spec']]['link_name'] = '<a href="' . $spec_raw['url'] . '">' . $spec_raw['name'] . '</a>';

		$specs[$spec_raw['spec']]['short_name'] = preg_replace( "/(.*): (.*)/", "$2", $spec_raw['name'] );
	}
}

function init_user_data( ) {
	global $db, $series, $users;

	$query = $db->prepare( 'SELECT status.nick, users.name FROM status LEFT JOIN users ON status.nick = users.nick WHERE status.series = :series GROUP BY nick ORDER BY nick ASC' );

	$query->execute( array( ':series' => $series['series'] ) );
	$users_raw = $query->fetchAll( );

	foreach( $users_raw as $user_raw ) {
		if( $user_raw['name'] == null ) { $user_raw['name'] = $user_raw['nick']; }
		$users[$user_raw['nick']] = $user_raw['name'];
	}
}

?>
