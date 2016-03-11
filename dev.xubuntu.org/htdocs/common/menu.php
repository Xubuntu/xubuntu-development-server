<?php

$menu = array(
	'overview' => array(
		'title' => 'Work items',
		'url' => 'http://dev.xubuntu.org/',
	),
	'wiki' => array(
		'title' => 'Wiki',
		'url' => 'http://wiki.xubuntu.org/',
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
			echo dev_xubuntu_org_menu_link( $page['url'], $page['title'], $page['target'] );
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