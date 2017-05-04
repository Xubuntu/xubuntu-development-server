<?php

function tpl_pagetitle_clean( $id = null, $ret = false ) {
	global $ACT;

	if( is_null( $id ) ) {
		global $ID;
		$id = $ID;
	}

	$page_title = $id;
	if( useHeading( 'navigation' ) ) {
		$first_heading = p_get_first_heading( $id );
		if( $first_heading ) { $page_title = $first_heading; }
	}

	if( $ret ) {
		return hsc( $page_title );
	} else {
		print hsc( $page_title );
		return true;
	}
}