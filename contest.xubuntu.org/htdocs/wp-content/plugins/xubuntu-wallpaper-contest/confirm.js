jQuery( function( ) {
	jQuery( 'a.delete-submission' ).click( function( e ) {
		var confirm_delete = window.confirm( 'Are you sure you want to delete the submission?' );
		if( confirm_delete != true ) {
			e.preventDefault( );
		}
	} );
} );