<?php

$menu = array(
	'overview' => array(
		'title' => 'Work items',
		'url' => 'http://dev.xubuntu.org/#tab-overview',
		'children' => array(
			'overview' => array(
				'title' => 'Overview',
				'url' => 'http://dev.xubuntu.org/#tab-overview'
			),
			'details' => array(
				'title' => 'Details',
				'url' => 'http://dev.xubuntu.org/#tab-details'
			),
			'burndown' => array(
				'title' => 'Burndown',
				'url' => 'http://dev.xubuntu.org/#tab-burndown'
			),
			'timeline' => array(
				'title' => 'Timeline',
				'url' => 'http://dev.xubuntu.org/#tab-timeline'
			),
		),
	),
	'wiki' => array(
		'title' => 'Wiki',
		'url' => 'http://wiki.xubuntu.org/',
		'children' => array(
			'wiki_sitemap' => array(
				'title' => 'Sitemap',
				'url' => 'http://wiki.xubuntu.org/start?do=index',
			),
		),
	),
	'calendar' => array(
		'title' => 'Calendar',
		'url' => 'http://dev.xubuntu.org/#tab-calendar'
	),
	'irc' => array(
		'title' => 'IRC',
		'url' => 'http://webchat.freenode.net/?channels=xubuntu-devel&nick=tracker.&prompt=1&uio=MTE9MjE131',
		'target' => '_blank',
	),
);

dev_xubuntu_org_menu( $menu );

function dev_xubuntu_org_menu( $menu ) {
	if( is_array( $menu ) ) {
		echo '<ul class="menu">';
		foreach( $menu as $id => $page ) {
			if( isset( $page['children'] ) ) {
				echo '<li class="menu-item-has-children">';
				echo '<a href="' . $page['url'] . '">' . $page['title'] . '</a>';
				echo '<ul class="sub-menu">';
					foreach( $page['children'] as $c_id => $c_page ) {
						echo dev_xubuntu_org_menu_link( $c_page['url'], $c_page['title'], $c_page['target'] );
					}
				echo '</ul>';
				echo '</li>';
			} else {
				echo dev_xubuntu_org_menu_link( $page['url'], $page['title'], $page['target'] );
			}
		}
		echo '</ul>';
	}
}

function dev_xubuntu_org_menu_link( $url, $title, $target = null ) {
	if( $target != null ) {
		return '<li><a href="' . $url . '" target="' . $target . '">' . $title . '</a></li>';
	} else {
		return '<li><a href="' . $url . '">' . $title . '</a></li>';
	}
}

?>