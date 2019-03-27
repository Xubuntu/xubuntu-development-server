<!DOCTYPE html>
<?php
	include 'inc/init.php';
	init_status( );
	global $series;
?>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="fi" xml:lang="fi">
<head>
	<title>Xubuntu Developers</title>
	<meta charset="UTF-8" />

	<link rel="stylesheet" href="http://dev.xubuntu.org/common/reset.css" media="all" />
	<link rel="stylesheet" href="http://dev.xubuntu.org/common/style.css" media="all" />
	<link rel="stylesheet" href="http://dev.xubuntu.org/common/style-dev.css" media="all" />
	<link rel="stylesheet" href="http://dev.xubuntu.org/common/style-common.css" media="all" />

	<link rel="stylesheet" href="style/style.css" media="all" />

	<script type='text/javascript' src='inc/lib/jquery-2.1.4.min.js'></script>
	<script type='text/javascript' src='inc/status.js'></script>
	<script type='text/javascript' src='inc/status_workitems.js'></script>
	<?php teams_js( ); iframe_srcs( ); ?>

	<link rel="shortcut icon" href="style/favicon.png" />
</head>
<body>

<div id="header_outer">
	<div id="header_art"></div>

	<div id="header">
		<div id="logo">
			<a href="http://dev.xubuntu.org/"><img alt="Xubuntu Developers" src="style/xubuntu-developers-logo.png" /></a>
		</div>
	</div>
	<div id="navi_outer">
		<div id="navi">
			<div class="group navigation nd">
				<?php include( '/var/www/dev.xubuntu.org/htdocs/common/menu.php' ); ?>
			</div>
		</div>
	</div>
</div>

<div id="content_outer">
	<div id="content" class="group">
		<div id="main_outer">
			<section id="main">
				<div class="post-post">
					<div class="post-toolbar group">
						<p class="tools">
							<a class="button primary" href="#tab-overview">Overview</a><a class="button" href="#tab-details">Details</a><a class="button" href="#tab-burndown">Burndown</a><a class="button" href="#tab-timeline">Timeline</a>
						</p>
					</div>
					<?php print_tabs( ); ?>
				</div>
			</section>
		</div>
	</div>
</div>

<div id="footer_outer">
	<div id="footer">
		<p>
			Work item data from <a href="https://launchpad.net/">Launchpad</a>. The cache was last updated at <?php echo get_cache_time( $series['series'] ); ?>.
			Calendar data is maintained in <a href="https://www.google.com/calendar/">Google Calendar</a>.
		</p>
	</div>
</div>

</body>
</html>
