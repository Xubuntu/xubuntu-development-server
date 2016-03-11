<?php
/*
 *  Plugin Name: Xubuntu Wallpaper Contest
 *  Description: Allows users to send submissions to a wallpaper contest and administrators to vote on the submissions
 *  Author: Pasi Lallinaho
 *  Version: 2016-feb
 *  Author URI: http://open.knome.fi/
 *  Plugin URI: http://wordpress.knome.fi/
 *
 *  License: GNU General Public License v2 or later
 *  License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

add_action( 'admin_init', 'xwpc_admin_init' );

function xwpc_admin_init( ) {
	// TODO: Allow an administrator to pick roles linked to these capabilities
	$role = get_role( 'administrator' );
	$role->add_cap( 'xwpc_vote' );
	$role->add_cap( 'xwpc_see_results' );
}

add_action( 'admin_menu', 'xwpc_admin_menu' );

function xwpc_admin_menu( ) {
	add_object_page( 'Wallpaper Contest', 'Contest', 'read', 'xwpc_main', 'xwpc_ui_main', 'dashicons-thumbs-up' );
	add_submenu_page( 'xwpc_main', 'Wallpaper Contest', 'Start Here', 'read', 'xwpc_main', 'xwpc_ui_main' );
	add_submenu_page( 'xwpc_main', 'New Submission', 'New Submission', 'read', 'xwpc_new', 'xwpc_ui_new' );
//	add_submenu_page( 'xwpc_main', 'Terms', 'Terms and Guidelines', 'read', 'xwpc_terms', 'xwpc_ui_terms' );

	add_submenu_page( 'xwpc_main', 'Vote!', 'Vote!', 'xwpc_vote', 'xwpc_vote', 'xwpc_ui_vote' );
	add_submenu_page( 'xwpc_main', 'Results', 'Vote Results', 'xwpc_see_results', 'xwpc_vote_results', 'xwpc_ui_vote_results' );
}

add_action( 'admin_enqueue_scripts', 'xwpc_admin_enqueue_scripts' );

function xwpc_admin_enqueue_scripts( ) {
	// TODO: Only enqueue when needed
	wp_enqueue_style( 'xwpc-admin', plugins_url( 'admin.css', __FILE__ ) );

	wp_enqueue_script( 'xwpc-vote', plugins_url( 'vote.js', __FILE__ ), array( 'jquery' ) );
	$strings = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'ajaxnonce' => wp_create_nonce( 'xwpc-vote' ),
		'user' => get_current_user_id( ),
	);
	wp_localize_script( 'xwpc-vote', 'xwpc', $strings );
}

add_action( 'wp_ajax_xwpc_vote', 'xwpc_ajax_vote' );

function xwpc_ajax_vote( ) {
	check_ajax_referer( 'xwpc-vote', 'security' );

	$option = get_post_meta( $_POST['id'], 'xwpc_votes', true );
	$option[$_POST['user']] = $_POST['value'];

	$r = update_post_meta( $_POST['id'], 'xwpc_votes', $option );

	if( $r !== false ) {
		echo '1';
	} else {
		echo '0';
	}

	wp_die( );
}

function xwpc_ui_main( ) {
	$errors = array( );
	$success = false;

	if( isset( $_GET['_wpnonce_xwpc_delete_submission'] ) ) {
		if( check_admin_referer( 'xwpc-delete-submission_' . $_GET['id'], '_wpnonce_xwpc_delete_submission' ) ) {
			if( false === wp_delete_attachment( $_GET['id'] ) ) {
				$errors[] = 'Error in deleting submission. Please contact an admin.';
			} else {
				$success = true;
			}
		} else {
			exit( 'Oops.' );
		}
	}
	?>
	<div class="wrap">
		<h1>Xubuntu 16.04 Wallpaper Contest</h1>

		<p><strong>Welcome to the Xubuntu 16.04 Wallpaper Contest!</strong><p>
		<p>If you are new here, start by reading the <a href="<?php echo home_url( '/help/terms/' ); ?>">Terms and Guidelines</a>. After you've done that, you can <a href="<?php echo admin_url( 'admin.php?page=xwpc_new' ); ?>">submit your own entry</a>!</p>
		<p>Once you have submissions, they will appear below, where you can also delete them if you wish.</p>

		<?php
			if( count( $errors ) > 0 ) {
				echo '<div class="notice notice-error"><p>' . implode( '<br />', $errors ) . '</p></div>';
			} elseif( $success == true ) {
				echo '<div class="notice notice-success"><p>Your submission was deleted!</p></div>';
			}

			$args = array(
				'author' => get_current_user_id( ),
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'posts_per_page' => -1,
				'meta_key' => 'xwpc_submission',
				'meta_value' => '1',
			);
			$media_query = new WP_Query( $args );

			if( $media_query->have_posts( ) ) {
				echo '<h2>Your submissions</h2>';
				echo '<div class="submissions list">';
				while( $media_query->have_posts( ) ) {
					$media_query->the_post( );
					echo '<div class="item">';
					echo '<div class="info">';
					echo '<h3>' . get_the_title( ) . '</h3>';
					echo '<p class="sub"><strong>Attribution:</strong></p>' . wpautop( get_post_meta( get_the_ID( ), 'xwpc_attribution', true ) );
					echo '<p class="sub"><strong>License:</strong></p>' . wpautop( get_post_meta( get_the_ID( ), 'xwpc_licence', true ) );
					$delete_url = wp_nonce_url( admin_url( 'admin.php?page=xwpc_main&id=' . get_the_ID( ) ), 'xwpc-delete-submission_' . get_the_ID( ), '_wpnonce_xwpc_delete_submission' );
					echo '<a class="delete-submission" href="' . $delete_url . '">Delete this submission</a>';
					echo '</div>';
					echo '<div class="image">' . wp_get_attachment_image( get_the_ID( ), 'medium' ) . '</div>';
					echo '</div>';
				}
				echo '</div>';
			}
			wp_reset_postdata( );
		?>
	</div>
	<?php
}

function xwpc_ui_new( ) {
	global $_POST, $_FILES;
	$errors = array( );
	$success = false;

	if( isset( $_POST['_nonce_xwpc_submission'] ) ) {
		if( check_admin_referer( 'xwpc_submission', '_nonce_xwpc_submission' ) ) {
			// Check if data is valid
			if( $_POST['xwpc-acceptterms'] != "on" ) {
				$errors[] = 'You need to accept the Terms and Guidelines.';
			}
			if( !$_POST['xwpc-attribution'] ) {
				$errors[] = 'Please specify the attribution name.';
			}
			if( !$_POST['xwpc-licence'] ) {
				$errors[] = 'You need to specify a licence.';
			} elseif( $_POST['xwpc-licence'] == 'other' && !$_POST['xwpc-licence-other-details'] ) {
				$errors[] = 'You need to specify custom licence details.';
			}
			if( !$_FILES['xwpc-submission']['size'] ) {
				$errors[] = 'You need to select a file to upload.';
			}

			$filetype = mime_content_type( $_FILES['xwpc-submission']['tmp_name'] );
			$allowed_filetypes = array( 'image/jpg', 'image/jpeg', 'image/png', 'image/svg+xml' );
			if( !in_array( $filetype, $allowed_filetypes ) ) {
				$errors[] = 'The filetype is not allowed. Allowed filetypes are JPG, PNG and SVG.';
			}

			// Process data if no errors occurred.
			if( count( $errors ) == 0 ) {
				$fu = wp_handle_upload( $_FILES['xwpc-submission'], array( 'test_form' => false ) );

				if( $fu['file'] && !isset( $fu['error'] ) ) {
					// Add the image to the media library
					$info = array( );

					$info[] = '<strong>Attribution:</strong> ' . $_POST['xwpc-attribution'];
					if( $_POST['xwpc-licence'] == 'cc-by' ) {
						$info[] = '<strong>Licence:</strong> CC-BY-SA 3.0';
						$licence = 'CC-BY-SA 3.0';
					} else {
						$info[] = '<strong>Licence:</strong> ';
						$info[] = $_POST['xwpc-licence-other-details'];
						$licence = $_POST['xwpc-licence-other-details'];
					}

					if( $_POST['xwpc-name'] ) {
						$title = $_POST['xwpc-name'];
					} else {
						$title = 'Untitled';
					}

					$attachment = array(
						'post_mime_type' => $filetype,
						'post_title' => $title,
						'post_content' => '',
						'post_status' => 'inherit'
					);

					$attach_id = wp_insert_attachment( $attachment, $fu['file'] );
					require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
					$attach_data = wp_generate_attachment_metadata( $attach_id, $fu['file'] );
					wp_update_attachment_metadata( $attach_id, $attach_data );

					add_post_meta( $attach_id, 'xwpc_submission', 1, true );
					add_post_meta( $attach_id, 'xwpc_attribution', $_POST['xwpc-attribution'], true );
					add_post_meta( $attach_id, 'xwpc_licence', $licence, true );

					$success = true;
				} else {
					$errors[] = $fu['error'];
				}

				// TODO: Refill attribution field
			} else {
				// TODO: Refill attribution field
				// TODO: Refill name of work field
				// TODO: Reselect license
			}
		} else {
			exit( 'Oops.' );
		}
	}
	?>
	<div class="wrap">
		<h1>New Submission</h1>

		<?php
			if( count( $errors ) > 0 ) {
				echo '<div class="notice notice-error"><p>' . implode( '<br />', $errors ) . '</p></div>';
			} elseif( $success == true ) {
				echo '<div class="notice notice-success"><p>Your submission was received!</p></div>';
			}
		?>

		<form id="form-xwpc" action="<?php echo admin_url( 'admin.php?page=xwpc_new' ); ?>" method="post" enctype="multipart/form-data">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">Terms and Guidelines</th>
						<td>
							<label for="xwpc-acceptterms">
								<input id="xwpc-acceptterms" name="xwpc-acceptterms" type="checkbox" />
								I have read and accept the <a href="<?php echo home_url( '/help/terms/' ); ?>">Terms and Guidelines</a>
							</label>
							<p class="description">All submissions must adhere to the Terms and Guidelines for the competition or they will not considered eligible to win.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="xwpc-attribution">Attribution</label>
						<td>
							<input id="xwpc-attribution" name="xwpc-attribution" value="" type="text" class="regular-text ltr" />
							<p class="description">Specify the attribution name you would like to be used with your submission. Do not insert copyright or year, just the name.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="xwpc-name">Name of work (optional)</label>
						<td>
							<input id="xwpc-attribution" name="xwpc-name" value="" type="text" class="regular-text ltr" />
							<p class="description">If you want to name your work, you can do it here.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Licence</th>
						<td>
							<label for="xwpc-licence-ccby">
								<input id="xwpc-licence-ccby" name="xwpc-licence" type="radio" value="cc-by" />
								<strong>Creative Commons, CC-BY</strong>
								<p class="description"><strong>Recommended.</strong> A license that allows the Xubuntu team to use the wallpaper freely in the distribution while always attributing the work to you.</p>
							</label>
							<br /><hr /><br />
							<label for="xwpc-licence-other">
								<input id="xwpc-licence-other" name="xwpc-licence" type="radio" value="other" />
								<strong>Other, please specify details below</strong>
								<p class="description">Please note that the license will be evaluated by the Xubuntu team to make sure it is eligible. If the licence doesn't permit the use that the Xubuntu team would like it to, your submission will be uneligible for the competition.</p>
								<br />
								<textarea name="xwpc-licence-other-details" class="large-text" cols="50" rows="5"></textarea>
								<p class="description">License name URLs and other details.</p>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="xwpc-submission">Select file to upload</label></th>
						<td>
							<input id="xwpc-submission" name="xwpc-submission" type="file" />
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="xwpc-submit">Ready to go?</label></th>
						<td>
							<input id="xwpc-submit" name="xwpc-submit" type="submit" class="button button-primary" value="Submit" />
						</td>
					</tr>
				</tbody>
			</table>
			<?php wp_nonce_field( 'xwpc_submission', '_nonce_xwpc_submission' ); ?>
		</form>
	</div>
	<?php
}

function xwpc_ui_terms( ) {
	?>
	<div class="wrap">
		<h1>Terms and Guidelines</h1>

		<h2>Subject Matter</h2>
		<p>It is important to note Ubuntu – and hence Xubuntu – is shipped to users from every part of the globe. Your images should be considerate of this diversity and refrain from the following.</p>
		<ul style="list-style-type: disc; margin-left: 1em;">
			<li>No brand names or trademarks of any kind.</li>
			<li>No illustrations some may consider inappropriate, offensive, hateful, tortuous, defamatory, slanderous or libelous.</li>
			<li>No sexually explicit or provocative images.</li>
			<li>No images of weapons or violence.</li>
			<li>No alcohol, tobacco, or drug use imagery.</li>
			<li>No designs which promotes bigotry, racism, hatred or harm against groups or individuals; or promotes discrimination based on race, gender, religion, nationality, disability, sexual orientation or age.</li>
			<li>No religious, political, or nationalist imagery.</li>
		</ul>

		<h2>Design Requirements</h2>
		<ul style="list-style-type: disc; margin-left: 1em;">
			<li>The final dimension should be 2560 x 1600 pixels.</li>
			<li>Attribution must be declared if the submission is based on another design.</li>
		</ul>

		<h2>Background Guidelines</h2>
		<ul style="list-style-type: disc; margin-left: 1em;">
			<li>Avoid prominent use of the Xubuntu (or Ubuntu or Xfce) logo. It appears in enough places already.</li>
			<li>No version numbers. Some individuals may desire to use an older theme, or use the latest theme in their older version of Ubuntu. Let your submission be about choice and do not use version numbers in your artwork.</li>
			<li>Avoid text, it calls for attention too much and will likely look bad when scaled. Plus it can't be translated easily.</li>
			<li>Be careful with small patterns, they might become uneven when scaled.</li>
			<li>Consider how the wallpaper will interact with the panels, icons and windows.</li>
			<li>Show restraint in your use of color tone and contrast. The wallpaper sets the scene for other elements, it is not the main act.</li>
		</ul>
	</div>
	<?php
}

function xwpc_ui_vote( ) {
	if( !current_user_can( 'xwpc_vote' ) ) {
		exit( 'Oops' );
	}
	?>
	<div class="wrap">
		<h1>Voting</h1>

		<p>To vote, click on the plus/minus buttons. Once the button is highlighted, your vote is registered. When you come back to this page, you will see your current votes activated. To change your vote, simply press the other button.</p>

		<?php
			$user = get_current_user_id( );

			$args = array(
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'posts_per_page' => -1,
				'meta_key' => 'xwpc_submission',
				'meta_value' => '1',
			);
			$media_query = new WP_Query( $args );

			if( $media_query->have_posts( ) ) {
				echo '<div class="submissions compact">';
				while( $media_query->have_posts( ) ) {
					$media_query->the_post( );
					$id = get_the_ID( );
					$author = get_the_author( );
					echo '<div class="item" value="' . $id . '">';
					echo '<div class="image"><a href="' . wp_get_attachment_url( $id ) . '" target="_blank">' . wp_get_attachment_image( $id, 'medium' ) . '</a></div>';
					echo '<div class="info">';
					echo '<h3>' . get_the_title( ) . '</h3>';
					echo '<p class="sub"><strong>Submitted by:</strong></p><p><a href="http://launchpad.net/~' . $author . '">' . $author . '</a></p>';
					echo '<p class="sub"><strong>Attribution:</strong></p>' . wpautop( get_post_meta( get_the_ID( ), 'xwpc_attribution', true ) );
					echo '<p class="sub"><strong>License:</strong></p>' . wpautop( get_post_meta( get_the_ID( ), 'xwpc_licence', true ) );
					echo '</div>';
					echo '<div class="vote">';
					$votes = get_post_meta( $id, 'xwpc_votes', true );
					if( !isset( $votes[$user] ) ) {
						echo '<a class="up unsel" value="1" href="#" title="Vote up">+</a> ';
						echo '<a class="down unsel" value="-1" href="#" title="Vote down (not preferred or ineligible)">&ndash;</a>';
					} elseif( $votes[$user] == 1 ) {
						echo '<a class="up" value="1" href="#" title="Vote up">+</a> ';
						echo '<a class="down unsel" value="-1" href="#" title="Vote down (not preferred or ineligible)">&ndash;</a>';
					} elseif( $votes[$user] == -1 ) {
						echo '<a class="up unsel" value="1" href="#" title="Vote up">+</a> ';
						echo '<a class="down" value="-1" href="#" title="Vote down (not preferred or ineligible)">&ndash;</a>';
					}
					echo '<em class="result"></em>';
					echo '</div>';
					echo '</div>';
				}
				echo '</div>';
			}
			wp_reset_postdata( );
		?>
	</div>
	<?php
}

function xwpc_ui_vote_results( ) {
	if( !current_user_can( 'xwpc_vote' ) ) {
		exit( 'Oops' );
	}
	$vote_totals = array( );
	?>
	<div class="wrap">
		<h1>Vote Results</h1>

		<?php
			$args = array(
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'posts_per_page' => -1,
				'meta_key' => 'xwpc_submission',
				'meta_value' => '1',
			);
			$media_query = new WP_Query( $args );

			if( $media_query->have_posts( ) ) {
				while( $media_query->have_posts( ) ) {
					$media_query->the_post( );
					$id = get_the_ID( );
					$vote_total = get_post_meta( $id, 'xwpc_votes', true );
					if( is_array( $vote_total ) ) {
						if( array_sum( $vote_total ) > 0 ) {
							$results[$id] = array(
								'votes' => array_sum( $vote_total ),
								'author' => get_the_author( ),
							);
						}
					}
				}
			}
			wp_reset_postdata( );

			if( count( $results ) > 0 ) {
				arsort( $results );

				echo '<p>Showing all submissions with a positive value ordered from highest to lowest.</p>';
				echo '<div class="submissions results">';
				foreach( $results as $id => $data ) {
					echo '<div class="item" value="' . $id . '">';
					echo '<div class="result">' . $data['votes'] . '</div>';
					echo '<div class="image"><a href="' . wp_get_attachment_url( $id ) . '" target="_blank">' . wp_get_attachment_image( $id, 'medium' ) . '</a></div>';
					echo '<div class="info">';
					echo '<h3>' . get_the_title( $id ) . '</h3>';
					echo '<p class="sub"><strong>Submitted by:</strong></p><p><a href="http://launchpad.net/~' . $data['author'] . '">' . $data['author'] . '</a></p>';
					echo '<p class="sub"><strong>Attribution:</strong></p>' . wpautop( get_post_meta( $id, 'xwpc_attribution', true ) );
					echo '<p class="sub"><strong>License:</strong></p>' . wpautop( get_post_meta( $id, 'xwpc_licence', true ) );
					echo '</div>';
					echo '</div>';
				}
				echo '</div>';
			} else {
				echo '<p>No results to show yet!</p>';
			}
		?>
	</div>
	<?php
}