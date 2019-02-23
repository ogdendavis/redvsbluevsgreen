<?php
/**
 * Template for webhook page to score athletes in local leaderboard.
 * Will use raw games.crossfit.com scores in local leaderboard to create custom
 * tcfPoints score for use in in-house competition
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
			$request = new WP_REST_Request( 'GET', '/tcf-athletes/v1/score-athletes-now' );
			$response = rest_do_request($request);
			?>

			<div class="webhook webhook--response">
				Sent request to score athletes by in-house ranking.<br>
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
