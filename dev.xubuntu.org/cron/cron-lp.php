<?php

include 'init.php';
include 'launchpad.php';

global $active_series, $db;

foreach( $active_series as $series ) {
	get_specifications( $series );

	$query = $db->prepare( 'INSERT INTO meta( meta_key, meta_value )
		VALUES( :meta_key, :meta_value )
		ON DUPLICATE KEY UPDATE meta_value = VALUES( meta_value )' );
	$query->execute( array( ':meta_key' => 'cache_' . $series['series'], ':meta_value' => time( ) ) );
}


?>
