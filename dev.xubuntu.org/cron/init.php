<?php

include "db.php";

init_status_series( );
init_status_active_series( );

function init_status_series( ) {
	global $db, $default_series;

	foreach( $db->query( 'SELECT * FROM series WHERE default_series = 1;' ) as $row ) {
		$default_series = $row;
	}
}

function init_status_active_series( ) {
	global $db, $active_series;

	$query = $db->prepare( 'SELECT * FROM series WHERE active_series = 1;' );
	$query->execute( );
	$active_series = $query->fetchAll( );
}

?>