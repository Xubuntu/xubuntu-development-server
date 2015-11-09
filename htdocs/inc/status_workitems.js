var first_load = true;

jQuery( function( e ) {
	jQuery( '#wi_filters .filter-dropdown' ).attr( 'data-value', '' );

	// FILTERS
	// Listen to text filter changes
	jQuery( '#wi_filters #filter-text input' ).on( 'input propertychange paste', function( e ) {
		wi_refresh_table( );
	} );

	// Listen to dropdown filter changes
	jQuery( '#wi_filters .filter-dropdown-single ul a' ).click( function( e ) {
		// TODO: This code isn't used, review if start using
		jQuery( this ).closest( '.filter-dropdown-single' ).attr( 'data-value', jQuery( this ).attr( 'data-value' ) );
		jQuery( this ).closest( '.filter-dropdown-single' ).find( 'span.filter-title' ).text( jQuery( this ).text( ) );

		wi_refresh_table( );
		e.preventDefault( );
	} );

	jQuery( '#wi_filters .filter-dropdown-multi ul a' ).click( function( e ) {
		link = jQuery( this );
		link.toggleClass( 'checked' );
		var orig_value = link.closest( '.filter-dropdown-multi' ).attr( 'data-value' );
		var new_value = '';

		if( link.hasClass( 'checked' ) ) {
			if( orig_value.length == 0 ) {
				new_value = link.attr( 'data-value' );
			} else {
				new_value = orig_value + ',' + link.attr( 'data-value' );
			}
		} else {
			orig_value.split( ',' ).forEach( function( value ) {
				if( value != link.attr( 'data-value' ) ) {
					new_value = new_value + value + ',';
				}
			} );
		}
		new_value = new_value.replace( /,\s*$/, '' );
		link.closest( '.filter-dropdown-multi' ).attr( 'data-value', new_value );

		if( new_value.split( ',' ).length > 1 ) {
			link.closest( '.filter-dropdown-multi' ).find( 'span.filter-title' ).text( 'Multiple...' );
		} else if( new_value.split( ',' ).length == 1 ) {
			if( new_value[0] != null ) {
				link.closest( '.filter-dropdown-multi' ).find( 'span.filter-title' ).text( link.closest( '.filter-dropdown-multi' ).find( 'a.checked' ).text( ) );
			} else {
				link.closest( '.filter-dropdown-multi' ).find( 'span.filter-title' ).text( link.closest( '.filter-dropdown-multi' ).find( 'span.filter-title' ).attr( 'data-default' ) );
			}
		}

		wi_refresh_table( );
		e.preventDefault( );
	} );

	// Listen to filters from the table
	// TODO: Make these not depend on IDs
	jQuery( '#workitems .assignee a' ).click( function( e ) {
		assignee = jQuery( this ).closest( 'tr' ).attr( 'data-assignee' );
		jQuery( '#wi_filters #filter-assignee' ).attr( 'data-value', assignee );
		jQuery( '#wi_filters #filter-assignee span.filter-title' ).text( jQuery( '#wi_filters #filter-assignee a[data-value="' + assignee + '"]' ).text( ) );
		wi_refresh_table( );
	} );

	jQuery( '#workitems .specification a' ).click( function( e ) {
		specification = jQuery( this ).closest( 'tr' ).attr( 'data-specification' );
		jQuery( '#wi_filters #filter-specification' ).attr( 'data-value', specification );
		jQuery( '#wi_filters #filter-specification span.filter-title' ).text( jQuery( '#wi_filters #filter-specification a[data-value="' + specification + '"]' ).text( ) );
		wi_refresh_table( );
	} );

	// Clear individual filters
	jQuery( '#wi_filters .filter-dropdown a.clear' ).click( function( e ) {
		filter = jQuery( this ).closest( '.filter' );
		filter.find( 'span.filter-title' ).text( filter.find( 'span.filter-title' ).attr( 'data-default' ) );

		filter.attr( 'data-value', '' );
		filter.find( 'a.checked' ).removeClass( 'checked' );

		wi_refresh_table( );
		e.preventDefault( );
	} );

	jQuery( '#wi_filters .filter-text a.clear' ).click( function( e ) {
		jQuery( this ).siblings( 'input' ).val( '' );

		wi_refresh_table( );
		e.preventDefault( );
	} );

	// Clear all filters
	// TODO: Bind to Escape key
	jQuery( '#filter-clear' ).click( function( e ) {
		jQuery( '#wi_filters .filter-text input' ).val( '' );

		jQuery( '#wi_filters .filter-dropdown' ).each( function( e ) {
			jQuery( this ).find( 'span.filter-title' ).text( jQuery( this ).find( 'span.filter-title' ).attr( 'data-default' ) );
		} );

		jQuery( '#wi_filters .filter-dropdown-single' ).each( function( e ) {
			jQuery( this ).removeAttr( 'data-value' );
		} );

		jQuery( '#wi_filters .filter-dropdown-multi' ).each( function( e ) {
			jQuery( this ).attr( 'data-value', '' );
			jQuery( this ).find( 'span.filter-title' ).text( jQuery( this ).find( 'span.filter-title' ).attr( 'data-default' ) );
			jQuery( this ).find( 'a.checked' ).removeClass( 'checked' );
		} );

		wi_apply_sort( 'assignee', 'asc' );

		wi_refresh_table( );
		e.preventDefault( );
	} );

	// SORT
	// When a work items table column header is clicked, sort the table by that column
	jQuery( '#workitems thead th' ).click( function( e ) {
		if( jQuery( this ).attr( 'data-sort-order' ) == 'asc' ) {
			sort_order = 'desc';
		} else {
			sort_order = 'asc';
		}
		wi_apply_sort( jQuery( this ).attr( 'data-column' ), sort_order );

		wi_refresh_table( );
	} );
} );

function wi_refresh_table( ) {
	if( first_load == true ) {
		first_load = false;
	} else {
		window.location.href = '#tab-details';
	}

	wi_apply_text_filter( );
	wi_apply_dropdown_filters( );
	wi_hide_repeat_usernames( );
	wi_update_permalink( );
}

function wi_apply_text_filter( ) {
	jQuery( '#workitems tbody tr' ).removeClass( 'filter-text-hide' );

	search_string = jQuery( '#wi_filters #filter-text input' ).val( );
	if( search_string.length ) {
		jQuery( '#workitems tbody tr' ).each( function( e ) {
			search_from = jQuery( this ).attr( 'data-item' );

			if( search_from.toLowerCase( ).indexOf( search_string ) < 0 ) {
			jQuery( this ).addClass( 'filter-text-hide' );
			}
		} );
		jQuery( '#wi_filters #filter-text a.clear' ).show( );
	} else {
		jQuery( '#wi_filters #filter-text a.clear' ).hide( );
	}
}

function wi_apply_dropdown_filters( ) {
	jQuery( '#wi_filters .filter-dropdown' ).each( function( e ) {
		// Get column and remove existing filters
		column = jQuery( this ).attr( 'data-column' );
		hide_class = 'filter-' + column + '-hide';
		jQuery( '#workitems tbody tr' ).removeClass( hide_class );

		// Check which values we want to show
		if( jQuery( this ).attr( 'data-value' ) ) {
			jQuery( '#workitems tbody tr' ).addClass( hide_class );

			values = jQuery( this ).attr( 'data-value' ).split( ',' );
			values.forEach( function( value ) {
				jQuery( '#workitems tbody tr[data-' + column + '="' + value + '"]' ).removeClass( hide_class );
			} );
			jQuery( '#wi_filters .filter[data-column="' + column + '"] a.clear' ).show( );
		} else {
			jQuery( '#wi_filters .filter[data-column="' + column + '"] a.clear' ).hide( );
		}
	} );
}

function wi_apply_sort( column, sort_order ) {
	items = jQuery( '#workitems tbody tr' ).clone( true, true );

	// Revert to default sort first...
	items = wi_sort_alphabetically( items, 'specification', 'asc' );
	items = wi_sort_by_status( items, sort_order, 'asc' );
	items = wi_sort_alphabetically( items, 'assignee', 'asc' );

	if( column == 'item' ) {
		// Clicked on the work item column, sort by status
		items = wi_sort_by_status( items, sort_order );
	} else {
		// Clicked on any other column, sort alphabetically
		items = wi_sort_alphabetically( items, column, sort_order );
	}

	// Execute the sort
	jQuery( '#workitems tbody tr' ).remove( );
	items.appendTo( '#workitems tbody' );
	jQuery( '#workitems thead th' ).removeAttr( 'data-sort-order' );
	jQuery( '#workitems thead th.' + column ).attr( 'data-sort-order', sort_order );

	// Give a visual indication of the sorted column
	jQuery( '#workitems thead th' ).removeClass( 'sort sort-asc sort-desc' );
	jQuery( '#workitems thead th.' + column ).addClass( 'sort sort-' + sort_order );
}

function wi_hide_repeat_usernames( ) {
	jQuery( '#workitems tr .assignee' ).removeClass( 'hide-repeat-username' );
	var old_assignee = '';

	jQuery( '#workitems .assignee' ).each( function( e ) {
		if( jQuery( this ).closest( 'tr' ).css( 'display' ) != 'none' ) {
			if( jQuery( this ).text( ) == old_assignee ) {
				jQuery( this ).addClass( 'hide-repeat-username' );
			}
			old_assignee = jQuery( this ).text( );
		}
	} );
}

function wi_update_permalink( ) {
	values = [];

	// Filters
	jQuery( '#wi_filters .filter' ).each( function( e ) {
		if( jQuery( this ).hasClass( 'filter-text' ) ) {
			value = jQuery( this ).find( 'input' ).val( );
		}
		if( jQuery( this ).hasClass( 'filter-dropdown' ) ) {
			value = jQuery( this ).attr( 'data-value' );
		}
		if( value && value.length > 0 ) {
			values[values.length] = jQuery( this ).attr( 'data-shorthand' ) + '=' + value;
		}
	} );

	// Sort
	sort_column = jQuery( '#workitems th.sort' );
	values[values.length] = 'sort=' + sort_column.attr( 'data-column' );
	values[values.length] = 'sortdir=' + sort_column.attr( 'data-sort-order' );

	jQuery( '#permalink' ).attr( 'href', '#tab-details/' + values.join( '+' ) );
}

function wi_sort_alphabetically( items, column, sort_order ) {
	items.sort( function( a, b ) {
		var asort = jQuery( a ).attr( 'data-' + column );
		var bsort = jQuery( b ).attr( 'data-' + column );

		if( sort_order == 'asc' ) {
			if( asort > bsort ) { 
				return 1;
			}

			if( asort < bsort ) {
				return -1;
			}
		}
		if( sort_order == 'desc' ) {
			if( asort > bsort ) { 
				return -1;
			}

			if( asort < bsort ) {
				return 1;
			}
		}

		return 0;
	} );

	return items;
}

function wi_sort_by_status( items, sort_order ) {
	// The preferred order for statuses
	var statuses = [ "todo", "inprogress", "blocked", "done", "postponed" ];

	items.sort( function( a, b ) {
		var asort = jQuery.inArray( jQuery( a ).attr( 'data-status' ), statuses );
		var bsort = jQuery.inArray( jQuery( b ).attr( 'data-status' ), statuses );

		if( sort_order == 'asc' ) {
			if( asort > bsort ) { 
				return 1;
			}

			if( asort < bsort ) {
				return -1;
			}
		}
		if( sort_order == 'desc' ) {
			if( asort > bsort ) { 
				return -1;
			}

			if( asort < bsort ) {
				return 1;
			}
		}

		return 0;
	} );

	return items;
}