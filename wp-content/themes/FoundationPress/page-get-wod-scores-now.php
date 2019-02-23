<?php
/**
 * Template for webhook page to get WOD scores from games.crossfit.com
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
				<br>
				Response:<br>
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
