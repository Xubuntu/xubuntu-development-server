<?php

if (!defined('DOKU_INC')) die(); /* must be run from within DokuWiki */
header('X-UA-Compatible: IE=edge,chrome=1');

include 'functions.php';

$hasSidebar = page_findnearest($conf['sidebar']);
$showSidebar = $hasSidebar && ($ACT=='show');
?><!DOCTYPE html>
<html lang="<?php echo $conf['lang'] ?>" dir="<?php echo $lang['direction']; ?>">
<head>
	<meta charset="utf-8" />
	<title><?php tpl_pagetitle_clean( ); ?> &lsaquo; <?php echo strip_tags( $conf['title'] ); ?></title>
	<meta name="viewport" content="width=device-width,initial-scale=1" />
	<link rel="shortcut icon" href="http://tracker.xubuntu.org/style/favicon.png" />

	<link rel="stylesheet" href="http://dev.xubuntu.org/common/reset.css" />
	<link rel="stylesheet" href="http://dev.xubuntu.org/common/style.css" />
	<link rel="stylesheet" href="http://dev.xubuntu.org/common/style-dev.css" />
	<link rel="stylesheet" href="http://dev.xubuntu.org/common/style-common.css" />

	<?php tpl_metaheaders( ); ?>
</head>

<body id="body-dokuwiki">

<div id="header_outer">
	<div id="header_art"></div>

	<div id="header">
		<div id="logo">
			<a href="http://dev.xubuntu.org/"><img alt="Xubuntu Developers" src="http://dev.xubuntu.org/style/xubuntu-developers-logo.png" /></a>
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
		<div id="main_outer" class="group">
			<section id="main">
				<div class="post-post">
					<div class="post-toolbar group">
						<p class="tools">
							<?php
								tpl_actionlink( 'edit' );
								tpl_actionlink( 'revisions', '', '', 'History' );
								tpl_actionlink( 'media', '', '', 'Media' );
							?>
							Last modified: <?php echo dformat( $INFO['lastmod'] ); ?> by <?php echo $INFO['editor']; ?>
						</p>
						<p class="tools right">
							<?php
								tpl_actionlink( 'index' );
							?>
						</p>
					</div>

					<?php if( html_msgarea( ) ) { ?>
						<h2>Information</h2>
						<?php html_msgarea( ); ?>
					<?php } ?>

					<?php tpl_content( false ); ?>
				</div>
			</section>
		</div><!-- #main_outer -->
	</div>
</div>

<div id="footer_outer">
	<div id="footer">
		<div class="widgets_flex">
			<div class="widgets group">
				<div class="widget">
					<p>Last modified: <?php echo dformat( $INFO['lastmod'] ); ?> by <?php echo $INFO['editor']; ?></p>
					<ul>
						<?php
							tpl_toolsevent( 'footer_user', array(
								tpl_action( 'edit', true, 'li', true ),
								tpl_action( 'revisions', true, 'li', true, '', '', 'History' ),
								tpl_action( 'media', true, 'li', true, '', '', 'Media' ),
							) );
						?>
					</ul>
				</div>
				<div class="widget">
					<ul>
						<?php
							tpl_toolsevent( 'footer_user', array(
								tpl_action( 'index', true, 'li', true ),
								tpl_action( 'profile', true, 'li', true, '', '', 'Profile (' . $INFO['client'] . ')' ),
								tpl_action( 'admin', true, 'li', true ),
								tpl_action( 'login', true, 'li', true ),
							) );
						?>
					</ul>
				</div>
				<div class="widget">
					<?php include( 'tpl_footer.php' ); ?>
				</div>
			</div>
		</div>
	</div>
</div>

	<div class="no"><?php tpl_indexerWebBug( ); /* provide DokuWiki housekeeping, required in all templates */ ?></div>
</body>
</html>
