jQuery( function( ) {
	jQuery( '.submissions .vote a' ).click( function( e ) {
		var data = {
			'action': 'xwpc_vote',
			'security': xwpc.ajaxnonce,
			'user': xwpc.user,
			'id': jQuery( this ).closest( '.item' ).attr( 'value' ),
			'value': jQuery( this ).attr( 'value' ),
		};
		current = jQuery( this );
		jQuery.post( xwpc.ajaxurl, data, function( response ) {
			if( response == 1 ) {
				current.closest( '.vote' ).children( 'a' ).addClass( 'unsel' );
				current.removeClass( 'unsel' );
			}
			console.log( response );
		} );

		e.preventDefault( );
	} );
} );