<?php
/**
 * The template for displaying the header
 *
 * Displays all of the head element and everything up until the "container" div.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>
<!doctype html>
<html class="no-js" <?php language_attributes(); ?> >
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link href="https://fonts.googleapis.com/css?family=Arimo:400,700|Roboto:400,700" rel="stylesheet">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>

	<?php if ( get_theme_mod( 'wpt_mobile_menu_layout' ) === 'offcanvas' ) : ?>
		<?php get_template_part( 'template-parts/mobile-off-canvas' ); ?>
	<?php endif; ?>

	<header class="tcfopen-site-header" role="banner">
		<div class="grid-container">
	    <div class="grid-x grid-margin-x">
				<div class="header-title cell medium-shrink">
					<h1><a href="<?php echo site_url(); ?>">TCF<span class="light-header">Open</span>2019</a></h1>
				</div>
				<div class="cell medium-auto header-team-icons">
					<img class="team-icon" src="<?php echo site_url('/site-assets/red-team-wings-302x96.png'); ?>" alt="Red Team Shield">
					<img class="team-icon" src="<?php echo site_url('/site-assets/blue-team-wings-302x96.png'); ?>" alt="Blue Team Shield">
					<img class="team-icon" src="<?php echo site_url('/site-assets/green-team-wings-302x96.png'); ?>" alt="Green Team Shield">
				</div>
			</div>
		</div>

	</header>
