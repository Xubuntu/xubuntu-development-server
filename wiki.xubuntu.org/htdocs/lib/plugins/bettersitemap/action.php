<?php

if(!defined('DOKU_INC')) die();

class action_plugin_bettersitemap extends Dokuwiki_Action_Plugin {
	public function register( Doku_Event_Handler $controller ) {
		$controller->register_hook( 'TPL_ACT_RENDER', 'BEFORE', $this, '_render_sitemap' );
	}

	public function _render_sitemap( Doku_Event $event, $param ) {
		if( $event->data == 'index' ) {
			bettersitemap_output( );

			$event->preventDefault( );
		}
	}
}

function bettersitemap_output( ) {
	global $conf;

	$opts = array(
		'depth' => 0,
		'listdirs' => true,
		'listfiles' => true
	);
	search( $data, $conf['datadir'], 'search_universal', $opts );

	echo p_locale_xhtml('index');
	echo '<div class="sitemap" style="margin-bottom: 1.5em;">';
	echo html_buildlist( $data, '', 'html_list_index_bettersitemap', 'html_li_index_bettersitemap' );
	echo '</div>';
}

function html_list_index_bettersitemap( $item ) {
	global $ID, $conf;
	$nofollow = ($ID != $conf['start'] || $conf['sitemap']) ? ' rel="nofollow"' : '';

	$ret = '';
	if( $item['type'] == 'd' ) {
		// Check if a ":start" page exists for this namespace
		$opts = array( 'idmatch' => $item['id'] . ':start', 'listfiles' => true, 'firsthead' => true );
		search( $data, $conf['datadir'], 'search_universal', $opts );

		if( isset( $data[0]['title'] ) ) {
			$title = $data[0]['title'];
		} else {
			$title = $item['id'];
		}

		$ret .= '<a href="' . wl( $item['id'] . ':start' ) . '" ' . $nofollow . '><strong>' . $title . '</strong></a>';
	} else {
		if( substr( $item['id'], strlen( $item['id'] ) - 6 ) != ':start' ) {
			$ret .= html_wikilink( ':' . $item['id'], useHeading( 'navigation' ) ? null : noNS( $item['id'] ) );
		}
	}

	return $ret;
}

function html_li_index_bettersitemap( $item ) {
	if( substr( $item['id'], strlen( $item['id'] ) - 6 ) != ':start' ) {
		if( $item['type'] == 'f' ) {
			return '<li class="level' . $item['level'] . '">';
		} else {
			return '<li>';
		}
	}
}