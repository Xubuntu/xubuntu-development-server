<?php

function show_progress( ) {
	global $db, $series, $specs;

	$query = $db->prepare( 'SELECT count(*) as count, spec, status FROM status WHERE series = :series GROUP BY spec, status' );
	$query->execute( array( ':series' => $series['series'] ) );
	$spec_statuses = $query->fetchAll( );

	foreach( $spec_statuses as $status ) {
		$data['num'] = $status['count'];
		$data['percent'] = $status['count'] / $specs[$status['spec']]['item_count'] * 100;
		$progress[$status['spec']][$status['status']] = $data;
	}

	echo '<table class="progress">';
	echo '<thead><tr>';
	echo '<th>Specification</th>';
	echo '<th>Actions</th>';
	echo '<th>Progress</th>';
	echo '</tr></thead>';
	echo '<tbody>';

	foreach( $progress as $spec => $statuses ) {
		$green = $statuses['DONE']['percent'];
		$yellow = $green + $statuses['INPROGRESS']['percent'];

		if( $statuses['DONE']['num'] == null ) {
			$statuses['DONE']['num'] = '0';
		}
		$content = $statuses['DONE']['num'] . ' of ' . $specs[$spec]['item_count'] . ' (' . round( (int) $green ) . '%)';

		echo '<tr class="spec">';
		echo '<td class="title"><a href="#tab-details/spec=' . $spec . '">' . $specs[$spec]['name'] . '</a></td>';
		echo '<td class="actions">';
		echo '<a href="' . $specs[$spec]['url'] . '">Edit</a> ';
		if( strlen( $specs[$spec]['whiteboard'] ) > 0 ) {
			echo '<a class="whiteboard" href="#">Whiteboard</a>';
		}
		echo '</td>';
		echo '<td class="progress_bar">' . progress_bar( $content, $green, $yellow ) . '</td>';
		echo '</tr>';

		if( strlen( $specs[$spec]['whiteboard'] ) > 0 ) {
			echo '<tr class="whiteboard">';
			echo '<td colspan="3">' . $specs[$spec]['whiteboard'] . '</td>';
			echo '</tr>';
		}

		$green_total += $statuses['DONE']['num'];
		$yellow_total += $statuses['INPROGRESS']['num'];
		$total += $specs[$spec]['item_count'];
	}

	$green_total_percent = $green_total / $total * 100;
	$yellow_total_percent = ( $green_total + $yellow_total ) / $total * 100;

	echo '<tr class="total">';
	echo '<td class="title" colspan="2">Total</td>';
	$content = $green_total . ' of ' . $total . ' (' . floor( $green_total_percent ) . '%)';
	echo '<td class="progress_bar">' . progress_bar( $content, $green_total_percent, $yellow_total_percent ) . '</td>';
	echo '</tr>';

	echo '</tbody>';
	echo '</table>';
}

function work_items_list_new( ) {
	global $db, $series, $specs, $users;

	// NEW FILTERS
	echo '<div id="wi_filters" class="toolbar show-on-js">';
	// Filter by text search
	echo '<div id="filter-text" class="filter filter-text" data-shorthand="text">Text <input type="text" name="filter-text" />';
	echo '<a href="" class="clear">X</a></div>';
	// Filter by assignee
	echo '<div id="filter-assignee" data-column="assignee" class="filter filter-dropdown filter-dropdown-multi" data-shorthand="a">Assignee ';
	echo '<ul><li><span class="filter-title" data-default="Everybody">Everybody</span><ul class="items">';
	// TODO: Only show users that have work items in the current series
	foreach( $users as $nick => $name ) {
		echo '<li><a href="#" data-value="' . $nick . '">' . $name . '</a></li>';
	}
	echo '</ul></li></ul>';
	echo '<a href="" class="clear">X</a></div>';
	// Filter by specification
	echo '<div id="filter-specification" data-column="specification" class="filter filter-dropdown filter-dropdown-multi" data-shorthand="spec">Specification ';
	echo '<ul><li><span class="filter-title" data-default="All specifications">All specifications</span><ul class="items">';
	foreach( $specs as $spec ) {
		echo '<li><a href="#" data-value="' . $spec['spec'] . '">' . $spec['short_name'] . '</a></li>';
	}
	echo '</ul></li></ul>';
	echo '<a href="" class="clear">X</a></div>';
	// Filter by status
	echo '<div id="filter-status" data-column="status" class="filter filter-dropdown filter-dropdown-multi" data-shorthand="s">Status ';
	echo '<ul><li><span class="filter-title" data-default="All statuses">All statuses</span><ul class="items">';
	echo '<li><a href="#" data-value="todo">To Do</a></li>';
	echo '<li><a href="#" data-value="inprogress">In Progress</a></li>';
	echo '<li><a href="#" data-value="blocked">Blocked</a></li>';
	echo '<li><a href="#" data-value="done">Done</a></li>';
	echo '<li><a href="#" data-value="postponed">Postponed</a></li>';
	echo '</ul></li></ul>';
	echo '<a href="" class="clear">X</a></div>';
	// Clear all filters
	echo '<a class="action" id="filter-clear" href="#">Clear filters and sort</a>';
	// Permalink
	echo '<div id="permalink_container">';
	echo '<span class="action">Permalink</span>';
	echo '<a id="permalink" href="#tab-details">Work item details</a>';
	echo '</div>';
	echo '</div>';

	echo '<table id="workitems">';
	echo '<thead>';
	echo '<tr>';
	echo '<th data-column="assignee" class="assignee" title="Sort by assignee"><span>Assignee</span></th>';
	echo '<th data-column="item" class="item" title="Sort by work item status"><span>Work item</span></th>';
	echo '<th data-column="specification" class="specification" title="Sort by specification" colspan="2"><span>Specification</span></th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	$query = $db->prepare( 'SELECT * FROM status WHERE series = :series ORDER BY nick, FIELD( status, "TODO", "INPROGRESS", "BLOCKED", "DONE", "POSTPONED" ), spec ASC' );
	$query->execute( array( ':series' => $series['series'] ) );
	$items = $query->fetchAll( );

	foreach( $items as $item ) {
		$data_status = strtolower( $item['status'] );
		// Add row with data attributes for sorting
		echo '<tr data-status="' . $data_status . '" data-assignee="' . $item['nick'] . '" data-specification="' . $item['spec'] . '" data-item="' . htmlentities( strip_tags( $item['description'] ) ) . '">';
		echo '<td data-column="assignee" class="assignee"><a href="#tab-details">' . get_user_name( $item['nick'] ) . '</a></td>';
		echo '<td data-column="item" class="item item-' . $data_status . '"><span>' . $item['description'] . '</span></td>';
		echo '<td data-column="specification" class="specification"><a href="#tab-details">' . $specs[$item['spec']]['short_name'] . '</a></td>';
		echo '<td class="specification-link"><a class="action" href="' . $specs[$item['spec']]['url'] . '">Edit</a></td>';
		echo '</tr>';
	}

	echo '</tbody>';
	echo '</table>';
}

function show_burndown( ) {
	/*
	 *  Show the burndown chart
	 *
	 */
	global $db, $series;

	$day_count = day_difference( $series['date_start'], $series['date_end'] );
	$previous_date = $series['date_start'];

	/* This controls the padding between bars and is in percentage of the full chart width */
	$bar_padding = 0.2;

	$query = $db->prepare( 'SELECT max(items_total) as max FROM history WHERE series = :series AND date >= :date_start AND date <= :date_end' );
	$query->execute( array( ':series' => $series['series'], ':date_start' => $series['date_start'], ':date_end' => $series['date_end'] ) );
	$result = $query->fetch( );
	$max = $result['max'];

	$padding = ( $day_count - 1 ) * $bar_padding;
	$bar_width = ( 100 - $padding ) / $day_count;

	echo '<div class="toolbar show-on-js">';
	echo '<a class="action" href="#" id="highlight_events" title="Click to toggle highlights">Highlight events</a>';
	echo '</div>';

	echo '<div id="burndown_chart" class="no-js">';
	echo "\n\t\t\t";
	echo '<div id="burndown_bars">';

	$query = $db->prepare( 'SELECT * FROM history WHERE series = :series AND date >= :date_start AND date <= :date_end ORDER BY date ASC' );
	$query->execute( array( ':series' => $series['series'], ':date_start' => $series['date_start'], ':date_end' => $series['date_end'] ) );
	$dates = $query->fetchAll( );

	foreach( $dates as $date ) {
		// If we have empty record days, print invisible bars for them
		if( day_difference( $date['date'], $previous_date ) > 1 ) {
			for( $i = 1; $i < day_difference( $date['date'], $previous_date ); $i++ ) {
				echo "\n\t\t\t\t";
				echo '<div class="bar_wrapper" style="width: ' . $bar_width . '%; padding-right: ' . $bar_padding . '%;">';
				echo '<div class="bar" style="background-color: rgba( 0, 0, 0, 0 ); height: 100%; width: 100%;"></div>';
				echo '</div>';
			}
		}

		$height = $date['items_total'] / $max * 100;
		$done_percentage = floor( $date['items_done'] / $date['items_total'] * 100 );
		echo "\n\t\t\t\t";
		echo '<div class="bar_wrapper" style="width: ' . $bar_width . '%; padding-right: ' . $bar_padding . '%;" title="' . $date['date'] . ': ' . $date['items_done'] . ' of ' . $date['items_total'] . ' (' . $done_percentage . '%)">';
		echo progress_bar_percent( null, $date['items_total'], $date['items_done'], $date['items_inprogress'], array( 'direction' => 'bottom', 'height' => $height ) );
		echo '</div>';

		$previous_date = $date['date'];
	}
	echo "\n\t\t\t";
	echo '</div>';

	// Events
	$query = $db->prepare( 'SELECT * FROM events WHERE series = :series' );
	$query->execute( array( ':series' => $series['series'] ) );
	$events = $query->fetchAll( );

	foreach( $events as $event ) {
		$days = day_difference( $event['date'], $series['date_start'] );
		$padding_left = ( $days - 1 ) * $bar_padding;
		$days_left = $days * $bar_width;
		$margin_left = $padding_left + $days_left;

		if( $event['date'] < gmdate( 'Y-m-d' ) ) {
			$class = 'past';
		} else {
			$class = 'future';
		}

		echo "\n\t\t\t";
		echo '<div class="event ' . $class . '" style="left: ' . $margin_left . '%;">';
		echo '<span title="' . $event['date'] . ' / ' . $event['event'] . '">' . $event['date'] . ' / ' . $event['event'] . '</span>';
		echo '</div>';
	}

	// Burndown line for non-JS users
	echo '<div class="burndown_line_nojs hide-on-js"></div>';

	echo '</div>';
}

function show_timeline( ) {
	global $db, $series, $specs;

	$old_date = null;

	$query = $db->prepare( 'SELECT * FROM status WHERE series = :series AND status = :status ORDER BY date_done DESC, description ASC' );
	$query->execute( array( ':series' => $series['series'], ':status' => 'DONE' ) );
	$done_items = $query->fetchAll( );

	if( is_array( $done_items ) ) {
		echo '<ul class="timeline">';
		foreach( $done_items as $item ) {
			$month = substr( $item['date_done'], 5, 2 );
			if( $old_group != $month && $old_group != 'Earlier' ) {
				if( $item['date_done'] < $series['date_start'] ) {
					echo '<li class="group-title">Earlier</li>';
					$old_group = 'Earlier';
				} else {
					$date = new DateTime( $item['date_done'] );
					echo '<li class="group-title">' . $date->format( 'F Y' ) . '</li>';
					$old_group = $month;
				}
			}

			echo '<li class="done">';
			if( $old_group != 'Earlier' ) {
				if( substr( $item['date_done'], 8, 2 ) != $old_day ) {
					echo '<span class="date">' . (int) substr( $item['date_done'], 8, 2 ) . '</span> ';
				} else {
					echo '<span class="date"></span>';
				}
				$old_day = substr( $item['date_done'], 8, 2 );
			}
			echo $item['description'];
			echo ' <span class="assignee">completed by <a href="#tab-details/' . $item['nick'] . '">' . get_user_name( $item['nick'] ) . '</a></span>';
			echo ' <span class="specification">in ' . $specs[$item['spec']]['short_name'] . '</span>';
			echo '</li>';
		}
		echo '</ul>';
	}
}

?>