<?php
  /**
  * The template for the homepage (when set to static page)
  *
  */

  get_header(); ?>

<?php // This will hold the code for grabbing & displaying results from games.crossfit.com! Trying to use the REST api, now...
// I'll probably end up writing the front-end in javascript. Here are the URLs to use for GET requests:
// base: /tcf-athletes/v1 (may need to add /wp-json to beginning)
// everybody: /all (or any error in final bit of path will give all)
// by gender: /sort/men, /sort/women
// by team: /sort/green, /sort/blue/, /sort/red
// All should return sorted by overall placement
// To return total team scores: /teams
?>
<?php
  // Modify request by adding parameters to the end
  // e.g. /tcf-athletes/v1/standings?teams=all&gender=male
  // $request = new WP_REST_Request( 'GET', '/tcf-athletes/v1/update-wod/2018/3' );
  // $response = rest_do_request($request);
  // var_dump($response);
?>

<main>
  <div class="grid-container">
    <div class="grid-x grid-margin-x">

      <div class="cell medium-8 small-12 leaderboard-container">
        <?php // Top-level tabs ?>
        <ul class="tabs" data-tabs id="leaderboard-tabs">
          <li class="tabs-title is-active">
            <a href="#teamScores" aria-selected="true">Teams</a>
          </li>
          <li class="tabs-title">
            <a href="#individualScores" aria-selected="false">Individuals</a>
          </li>
        </ul>

        <?php // Tab content, including sub-tabs ?>
        <div class="tabs-content" data-tabs-content="leaderboard-tabs">

          <div class="tabs-panel is-active" id="teamScores">

            <p>This is where the team leaderboard will go!</p>

          </div>

          <div class="tabs-panel no-padding" id="individualScores">

            <?php // The sub-tablist should be identical for teams and individuals, except for ids ?>

            <div class="cell medium-4">
              <ul class="tabs" data-tabs id="individual-leaderboard">
                <li class="tabs-title is-active">
                  <a href="#individual-overall-leaderboard" aria-selected="true">Overall</a>
                </li>
                <li class="tabs-title">
                  <a href="#individual-women-leaderboard" aria-selected="false">Women</a>
                </li>
                <li class="tabs-title">
                  <a href="#individual-men-leaderboard" aria-selected="false">Men</a>
                </li>
              </ul>
            </div>

            <div class="cell medium-8">
              <div class="tabs-content" data-tabs-content="individual-leaderboard">
                <div class="tabs-panel is-active" id="individual-overall-leaderboard">
                  <p>This is the mixed-gender leaderboard!</p>
                </div>
                <div class="tabs-panel" id="individual-women-leaderboard">
                  <p>This is the women's leaderboard!</p>
                </div>
                <div class="tabs-panel" id="individual-men-leaderboard">
                  <p>This is the men's leaderboard!</p>
                </div>
              </div>
            </div>

          </div> <!-- tabs-panel -->
        </div><!-- tabs-content -->
      </div><!-- leaderboard-container -->

      <div class="cell medium-4 small-12 analysis-container">
        <?php
          $args = array('numberposts' => 1);
          $post = get_posts($args)[0];
          echo '<div class="analysis_title">' . $post->post_title . '</div>';
          echo '<div class="analysis__excerpt">' . wp_trim_words($post->post_content, 55, '<a class="analysis__more-link" href="' . $post->guid . '"> &hellip;(read more)</a>') . '</div>';
        ?>
      </div>

      <div class="cell small-12 images">
        <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Front Page Bottom') ) : ?>
        <?php endif;?>
      </div>

    </div>
  </div> <!-- grid-container -->

</main>
<?php get_footer();
