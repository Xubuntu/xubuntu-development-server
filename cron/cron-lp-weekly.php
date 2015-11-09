<?php

include 'init.php';
include 'launchpad.php';

get_users( );

foreach( $active_series as $series ) {
	get_events( $series );
}

?>
