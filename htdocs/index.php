<!DOCTYPE html>
<?php include 'inc/init.php'; init_status( ); global $series; ?>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="fi" xml:lang="fi">
<head>
	<title>Xubuntu status tracker</title>
	<meta charset="UTF-8" />

	<link rel="stylesheet" href="style/reset.css" media="all" />
	<link rel="stylesheet" href="style/style.css" media="all" />

	<script type='text/javascript' src='inc/lib/jquery-2.1.4.min.js'></script>
	<script type='text/javascript' src='inc/status.js'></script>
	<script type='text/javascript' src='inc/status_workitems.js'></script>
	<?php teams_js( ); iframe_srcs( ); ?>

	<link rel="shortcut icon" href="style/favicon.png" />
</head>
<body>

<div id="header">
	<div class="inside">
		<h1>
			<img src="style/logo.svg" style="height: 1em; width: auto; margin-right: 0.3em; margin-bottom: -0.15em;" />
			<?php echo $series['name']; ?>
		</h1>
		<div class="data-tabs">
			<a href="#overview">Overview</a>
			<a href="#details">Details</a>
			<a href="#burndown">Burndown</a>
			<a href="#timeline">Timeline</a>
		</div>
	</div>
</div>

<div id="content">
	<div class="inside">
		<div class="data-tab" id="overview" data-link-title="Overview">
			<h2 class="no-js">Overview</h2>
			<?php show_progress( ); ?>
		</div>

		<div class="data-tab" id="details" data-link-title="Details">
			<h2 class="no-js">Work item details</h2>
			<?php work_items_list_new( ); ?>
		</div>

		<div class="data-tab" id="burndown" data-link-title="Burndown">
			<h2 class="no-js">Burndown chart</h2>
			<?php show_burndown( ); ?>
		</div>

		<div class="data-tab" id="timeline" data-link-title="Timeline">
			<h2 class="no-js">Timeline</h2>
			<?php show_timeline( ); ?>
		</div>

		<div class="data-tab show-on-js full" id="wiki" data-link-title="Wiki" data-link-align="right">
			<h2 class="no-js hide-on-js">Wiki</h2>
			<iframe id="wiki-frame" style="width: 100%; height: 200px;" src=""></iframe>
		</div>

		<div class="data-tab show-on-js" id="calendar" data-link-title="Calendar" data-link-align="right">
			<h2 class="no-js">Team Calendar</h2>
			<iframe id="calendar-frame" src="" width="900" height="410" frameborder="0" scrolling="no"></iframe>
			<ul>
				<li><strong>Google calendar ID</strong> 383qgn907l43kd425bteqjg850@group.calendar.google.com</li>
				<li><strong>Sharing</strong> <a href="https://www.google.com/calendar/feeds/383qgn907l43kd425bteqjg850%40group.calendar.google.com/public/basic">XML</a>, <a href="https://www.google.com/calendar/ical/383qgn907l43kd425bteqjg850%40group.calendar.google.com/public/basic.ics">iCal</a>, <a href="https://www.google.com/calendar/embed?src=383qgn907l43kd425bteqjg850%40group.calendar.google.com&amp;ctz=Etc/GMT">HTML</a></li>
			</ul>
		</div>

		<div class="data-tab show-on-js full" id="irc" data-link-title="IRC" data-link-align="right">
			<h2 class="no-js hide-on-js">IRC</h2>
			<iframe id="irc-frame" style="width: 100%; height: 200px;" src=""></iframe>
		</div>
	</div>
</div>

<div id="footer">
	<div class="inside">
		<p>Work item data from <a href="https://launchpad.net/">Launchpad</a>. The cache was last updated at <?php echo get_cache_time( $series['series'] ); ?>.</p>
		<p>
			Calendar data is maintained in <a href="https://www.google.com/calendar/">Google Calendar</a>.
			Webchat is provided by <a href="http://freenode.net/">Freenode</a>.
		</p>
	</div>
</div>

</body>
</html>
