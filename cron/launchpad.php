<?php

$api_url_base = 'https://api.launchpad.net/';
$api_version = 'devel';
$api_url = $api_url_base . $api_version;

function fetch_json( $url, $check_etag = false ) {
	$file = file_get_contents( $url );
	$json = json_decode( $file, true );

	return $json;
}

function get_specifications( $series = null ) {
	/*
    *  Get work items for specification dependencies for the active series
    *  
    */
	global $api_url, $db;

	if( !$series ) {
		global $default_series;
		$series = $default_series;
	}

	$burndown = array( 'total' => 0, 'BLOCKED' => 0, 'TODO' => 0, 'POSTPONED' => 0, 'INPROGRESS' => 0, 'DONE' => 0 );
	$update_stamp = time( );

	// The API URL for the specification to fetch...
	// Example: https://api.launchpad.net/devel/ubuntu/+spec/topic-w-flavor-xubuntu/dependencies
	$url = $api_url . '/ubuntu/+spec/' . $series['blueprint'] . '/dependencies';

	// Fetch data from Launchpad
	$json = fetch_json( $url );

	foreach( $json['entries'] as $entry ) {
		$bugs = array( );
		$items = array( );
		$item_count = 0;

		// Work items
		$wi_lines = explode( "\n", $entry['workitems_text'] );
		foreach( $wi_lines as $line ) {
			preg_match( "/\[(.*)\] (.*)\: (.*)/", $line, $out );
			if( isset( $out[3] ) ) {
				$items[] = $out;
				if( 'POSTPONED' != $out[3] ) {
					$item_count++;
					$burndown['total']++;
				}
				$burndown[$out[3]]++;
			}
		}

		// Linked bugs
		// TODO: Get default assignee from the blueprint
		$bugs = get_bug_collection( $entry['bugs_collection_link'] );
		foreach( $bugs as $bug ) {
			$items[] = $bug;
			$item_count++;
			$burndown['total']++;
			$burndown[$bug[3]]++;
		}

		// Spec-related queries
		$whiteboard = str_replace( "\n", "<br />", $entry['whiteboard'] );
		$whiteboard = preg_replace( '/https?:\/\/[\w\-\.!~#?&=+\*\'"(),\/]+/', '<a href="$0">$0</a>', $whiteboard );

		$query = $db->prepare( 'INSERT INTO specs( spec, series, name, url, whiteboard, item_count )
			VALUES( :spec, :series, :name, :url, :whiteboard, :item_count )
			ON DUPLICATE KEY UPDATE name = VALUES( name ), url = VALUES( url ), whiteboard = VALUES( whiteboard ), item_count = VALUES( item_count )' );
		$query->execute( array( ':spec' => $entry['name'], ':series' => $series['series'], ':name' => $entry['title'], ':url' => $entry['web_link'], ':whiteboard' => $whiteboard, ':item_count' => $item_count ) );

		// Item-related queries
		foreach( $items as $item ) {
			if( $item[3] == 'DONE' ) {
				$date_done = gmdate( 'Y-m-d' );
			} else {
				$date_done = null;
			}

			$query = $db->prepare( 'INSERT INTO status( series, spec, description, nick, status, date_done, last_update )
				VALUES( :series, :spec, :description, :nick, :status, :date_done, :last_update )
				ON DUPLICATE KEY UPDATE spec = VALUES( spec ), status = VALUES( status ), last_update = VALUES( last_update ), date_done = CASE WHEN date_done IS NULL THEN VALUES( date_done ) ELSE date_done END' );
			$query->execute( array( ':series' => $series['series'], ':spec' => $entry['name'], ':description' => $item[2], ':nick' => $item[1], ':status' => $item[3], ':date_done' => $date_done, ':last_update' => $update_stamp ) );
		}

		$query = $db->prepare( 'DELETE FROM status WHERE series LIKE :series AND spec LIKE :spec AND last_update != :last_update' );
		$query->execute( array( ':series' => $series['series'], ':spec' => $entry['name'], ':last_update' => $update_stamp ) );
	}

	$query = $db->prepare( 'INSERT INTO history( series, date, items_total, items_inprogress, items_done )
		VALUES( :series, :date, :items_total, :items_inprogress, :items_done )
		ON DUPLICATE KEY UPDATE items_total = VALUES( items_total ), items_inprogress = VALUES( items_inprogress ), items_done = VALUES( items_done )' );
	$query->execute( array( ':series' => $series['series'], ':date' => gmdate( 'Y-m-d' ), ':items_total' => $burndown['total'], ':items_inprogress' => $burndown['INPROGRESS'], ':items_done' => $burndown['DONE'] ) );
}

function get_bug_collection( $url, $default_assignee = 'xubuntu-dev' ) {
	/*
	 *  Get bugs linked to a specification
    *  
    */

	$bugs = array( );

	$json = fetch_json( $url );

	foreach( $json['entries'] as $entry ) {
		$assignees = array( );
		$statuses = array( );

		// Let's figure out the assignee and status
		// TODO: Check etag
		$json_tasks = fetch_json( $entry['bug_tasks_collection_link'] );

		foreach( $json_tasks['entries'] as $entry_task ) {
			if( $entry_task['status'] != 'Won\'t Fix' && $entry_task['status'] != 'Invalid' && $entry_task['status'] != 'Expired' ) {
				$statuses[] = $entry_task['status'];

				if( $entry_task['assignee_link'] != null ) {
					preg_match( "/~(.*)/", $entry_task['assignee_link'], $out );
					$assignees[] = $out[1];
				}
			}
		}

		// Create an item for the bug only if there are any 'open' statuses
		if( count( $statuses ) > 0 ) {
			$title = '<a href="' . $entry['web_link'] . '">LP #' . $entry['id'] . '</a> ' . $entry['title'];

			if( count( $assignees ) == 1 ) {
				$assignee = $assignees[0];
			} else {
				// If there are no assignees, or multiple of them, fall back to the default assignee
				$assignee = $default_assignee;
			}

			$status_counts = array_count_values( $statuses );
			if( isset( $status_counts['Fix Released'] ) && $status_counts['Fix Released'] == count( $statuses ) ) {
				// All tasks are 'Fix Released'
				$status = 'DONE';
			} elseif( isset( $status_counts['In Progress'] ) || isset( $status_counts['Fix Committed'] ) || isset( $status_counts['Fix Released'] ) ) {
				// At least one status is 'In Progress', 'Fix Committed' or 'Fix Released'
				$status = 'INPROGRESS';
			} else {
				$status = 'TODO';
			}

			$bugs[] = array(
				1 => $assignee,
				2 => $title,
				3 => $status,
				4 => $entry['id']
			);
		}
	}

	return $bugs;
}

function get_users( ) {
	/*
	 *  Get user data for users mentioned in the database
    *  
    */
	global $db;

	foreach( $db->query( 'SELECT nick FROM status GROUP BY nick' ) as $row ) {
		$users[] = get_user_data( $row['nick'] );
	}

	foreach( $users as $user ) {
		$query = $db->prepare( 'INSERT INTO users( nick, name, memberships )
			VALUES( :nick, :name, :memberships )
			ON DUPLICATE KEY UPDATE name = VALUES( name ), memberships = VALUES( memberships )' );
		$query->execute( array( ':nick' => $user['nick'], ':name' => $user['name'], ':memberships' => $user['memberships'] ) );
	}
}

function get_user_data( $nick ) {
	global $api_url;

	$teams = array( );

	$url = $api_url . '/~' . $nick;
	$json = fetch_json( $url );

	$user['nick'] = $nick;
	$user['name'] = $json['display_name'];
	if( !$json['is_team'] ) {
		// Get memberships
		// TODO: Check etag
		$memberships_json = fetch_json( $json['memberships_details_collection_link'] );

		foreach( $memberships_json['entries'] as $team ) {
			preg_match( "/~(.*)/", $team['team_link'], $name );
			$teams[] = $name[1];
		}
	}
	$user['memberships'] = implode( ',', $teams );

	return $user;
}

function get_events( $series ) {
	global $api_url, $db;

	$query = $db->prepare( 'DELETE FROM events WHERE series = :series' );
	$query->execute( array( ':series' => $series['series'] ) );

	// Get whiteboard for the umbrella blueprint
	// TODO: Check etag
	$url = $api_url . '/ubuntu/+spec/' . $series['blueprint'] . '/whiteboard';
	$json = fetch_json( $url );

	$rows = explode( "\n", $json );
	foreach( $rows as $row ) {
		preg_match( "/^@([0-9]{4}-[0-1][0-9]-[0-3][0-9]):(.*)/", $row, $data );
		if( count( $data ) > 0 ) {
			$query = $db->prepare( 'INSERT INTO events( series, date, event ) VALUES( :series, :date, :event );' );
			$query->execute( array( ':series' => $series['series'], ':date' => $data[1], ':event' => $data[2] ) );
		}
	}

}
?>