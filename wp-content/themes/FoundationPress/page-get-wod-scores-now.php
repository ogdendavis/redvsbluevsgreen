<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

get_header(); ?>

<!-- page.php -->
<?php get_template_part( 'template-parts/featured-image' ); ?>
<div class="main-container no-grid">
	<main class="main-content">

		<?php if ( current_user_can('administrator') ) : ?>

			<?php
			$request = new WP_REST_Request( 'GET', '/tcf-athletes/v1/get-wod-scores-now' );
			$response = rest_do_request($request);
			?>

			<div class="webhook webhook--response">
				Sent request to update local leaderboard with info from games.crossfit.com.<br>
				Response:<br>
				<br>
				<?php var_dump($response); ?>
			</div>

		<?php else: ?>

			<div class="webhook webhook--notloggedin">
				You need to be logged in to an administrative account for this page to work!
				<?php wp_login_form(); ?>
			</div>

		<?php endif; ?>

	</main>
</div>
<?php
get_footer();
