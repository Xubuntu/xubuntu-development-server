jQuery( function( ) {
	// Show and hide elements
	jQuery( '.show-on-js' ).show( );
	jQuery( '.hide-on-js' ).hide( );
	jQuery( '.no-js' ).removeClass( 'no-js' );

	// Create navigation menu
	jQuery( '.data-tabs' ).empty( );
	links_right = jQuery( '<div class="right">' );
	jQuery( '.data-tab' ).each( function( e ) {
		link_title = jQuery( this ).attr( 'data-link-title' );
		link_url = jQuery( this ).attr( 'id' );
		link_align = jQuery( this ).attr( 'data-link-align' );

		if( jQuery( this ).hasClass( 'hidden' ) ) {
			link = '<a style="display: none;" class="hidden" href="#tab-' + link_url + '">' + link_title + '</a>';
		} else {
			link = '<a href="#tab-' + link_url + '">' + link_title + '</a>';
		}

		if( link_align == 'right' ) {
			jQuery( links_right ).append( jQuery( link ) );
		} else {
			jQuery( '.data-tabs' ).append( jQuery( link ) );
		}
	} );
	jQuery( '.data-tabs' ).append( links_right );


	// Update the tab content when user goes "back" or "forward" in browser
	window.addEventListener( 'popstate', open_tab );

	// Tabs: See if we want to open a specific tab on load
	open_tab( );

	// Tabs: Handle tab opening
	jQuery( '.data-tabs a' ).click( function( e ) {
		// Change the URL
		window.location.hash = jQuery( this ).attr( 'href' );

		// Run required actions
		open_tab( );
	} );

	// Actions to be taken on window resize
	jQuery( window ).resize( function( e ) {
		if( window.tab == '#tab-burndown' ) {
			draw_burndown_line( );
		}
		if( window.tab == '#tab-irc' ) {
			resize_irc_iframe( );
		}
	} );

	// Whiteboard
	jQuery( '.progress a.whiteboard' ).click( function( e ) {
		jQuery( this ).closest( 'tr' ).next( 'tr.whiteboard' ).toggle( );
	} );

	// Highlight events
	jQuery( '#highlight_events' ).click( function( e ) {
		jQuery( '#burndown_chart .event' ).toggleClass( 'highlight' );
		e.preventDefault( );
	} );
} );

function draw_burndown_line( ) {
	jQuery( '#burndown_line' ).remove( );

	b_h = jQuery( '#burndown_chart' ).height( );
	b_w = jQuery( '#burndown_chart' ).width( );
	width = Math.sqrt( Math.pow( b_h, 2 ) + Math.pow( b_w, 2 ) );
	rotation_rad = Math.atan2( b_h, b_w );
	rotation = rotation_rad * 180 / Math.PI;

	burndown_line = '<div id="burndown_line" style="width: ' + width + 'px; transform: rotate( ' + rotation + 'deg );" />';
	jQuery( '#burndown_chart' ).append( jQuery( burndown_line ) );
}

function resize_irc_iframe( ) {
	content_padding = jQuery( '#content' ).css( 'padding-top' );

	jQuery( '#irc-frame' ).css( 'margin-top', '-' + content_padding );

	viewport_height = jQuery( window ).height( );
	header_height = jQuery( '#header' ).height( );
	body_border = jQuery( 'body' ).css( 'border-top-width' );
	frame_height = parseInt( viewport_height ) - parseInt( header_height ) - parseInt( body_border ) - 12;

	jQuery( '#irc-frame' ).css( 'height', frame_height + 'px' );
}

function open_tab( ) {
	options = window.location.hash.split( '/' );
	tab = options[0];
	params = options[1];

	// Show the tab
	jQuery( '.data-tabs a' ).removeClass( 'current' );
	jQuery( '.data-tab' ).hide( );

	if( tab.length > 0 ) {
		target = '#' + tab.substr( 5 );
		jQuery( target ).show( );
		jQuery( '.data-tabs a[href=' + tab + ']' ).addClass( 'current' );
	} else {
		jQuery( '.data-tab' ).first( ).show( );
		jQuery( '.data-tabs > a:first-child' ).addClass( 'current' );
	}

	// Revert changes (essentially when closing a tab)
	if( tab != '#tab-irc' ) {
		jQuery( '#footer' ).show( );
		jQuery( '#content' ).css( 'height', 'auto' );
		jQuery( '#content .inside' ).css( 'width', '90%' );
	}
	if( tab != '#tab-details' ) {
		wi_clear_all_filters( false );
	}

	// Run actions specific to tabs
	if( tab == '#tab-details' ) {
		// When no sort is applied by the user, add a visual indication for the default sort
		if( jQuery( '#workitems thead th.sort' ).size( ) == 0 ) {
			jQuery( '#workitems thead th.assignee' ).addClass( 'sort sort-asc' );
			jQuery( '#workitems thead th.assignee' ).attr( 'data-sort-order', 'asc' );
		}

		// Apply filters
		if( params && params.length > 0 ) {
			var sort_column, sort_order;

			filters = params.split( '+' );
			filters.forEach( function( current_filter ) {
				filter = current_filter.split( '=' );
				values = [];

				// Filters
				current_filter = jQuery( '#wi_filters .filter[data-shorthand="' + filter[0] + '"' );
				if( current_filter.hasClass( 'filter-dropdown' ) ) {
					// Check value validity
					filter[1].split( ',' ).forEach( function( value ) {
						if( current_filter.find( 'a[data-value="' + value + '"]' ).length == 1 ) {
							values[values.length] = value;
							current_filter.find( 'a[data-value="' + value + '"]' ).addClass( 'checked' );
						}
					} );

					if( values.length > 0 ) {
						current_filter.attr( 'data-value', values.join( ',' ) );
						if( values.length > 1 ) {
							current_filter.find( 'span.filter-title' ).text( 'Multiple...' );
						} else {
							current_filter.find( 'span.filter-title' ).text( current_filter.find( 'a[data-value="' + filter[1] + '"]' ).text( ) );
						}
					}
				}
				if( current_filter.hasClass( 'filter-text' ) ) {
					current_filter.find( 'input' ).val( filter[1] );
				}

				// Sort
				if( filter[0] == 'sort' ) {
					if( jQuery( '#workitems th[data-column="' + filter[1] + '"]' ).length > 0 ) {
						sort_column = filter[1];
					}
				}
				if( filter[0] == 'sortdir' ) {
					if( filter[1] == 'asc' || filter[1] == 'desc' ) {
						sort_order = filter[1];
					}
				}
				if( sort_column ) {
					if( !sort_order ) {
						sort_order = 'asc';
					}
					wi_apply_sort( sort_column, sort_order );
				}
			} );
		}

		wi_refresh_table( );
		jQuery( '#filter-text input' ).focus( );
	}
	if( tab == '#tab-burndown' ) {
		// Draw the burndown line
		draw_burndown_line( );
	}
	if( tab == '#tab-calendar' ) {
		if( jQuery( '#calendar-frame' ).attr( 'src' ).length < 1 ) {
			jQuery( '#calendar-frame' ).attr( 'src', iframe_src.calendar );
		}
	}
	if( tab == '#tab-irc' ) {
		if( jQuery( '#irc-frame' ).attr( 'src' ).length < 1 ) {
			jQuery( '#irc-frame' ).attr( 'src', iframe_src.irc );
		}

		jQuery( '#footer' ).hide( );
		jQuery( '#content' ).css( 'height', '0' );
		jQuery( '#irc' ).css( 'margin-bottom', '0' );
		jQuery( '#content .inside' ).css( 'width', '100%' );

		resize_irc_iframe( );
	}
}