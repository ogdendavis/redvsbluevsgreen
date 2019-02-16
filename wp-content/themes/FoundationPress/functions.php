<?php
/**
 * Author: Ole Fredrik Lie
 * URL: http://olefredrik.com
 *
 * FoundationPress functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

/** Various clean up functions */
require_once( 'library/cleanup.php' );

/** Required for Foundation to work properly */
require_once( 'library/foundation.php' );

/** Format comments */
require_once( 'library/class-foundationpress-comments.php' );

/** Register all navigation menus */
require_once( 'library/navigation.php' );

/** Add menu walkers for top-bar and off-canvas */
require_once( 'library/class-foundationpress-top-bar-walker.php' );
require_once( 'library/class-foundationpress-mobile-walker.php' );

/** Create widget areas in sidebar and footer */
require_once( 'library/widget-areas.php' );

/** Return entry meta information for posts */
require_once( 'library/entry-meta.php' );

/** Enqueue scripts */
require_once( 'library/enqueue-scripts.php' );

/** Add theme support */
require_once( 'library/theme-support.php' );

/** Add Nav Options to Customer */
require_once( 'library/custom-nav.php' );

/** Change WP's sticky post class */
require_once( 'library/sticky-posts.php' );

/** Configure responsive image sizes */
require_once( 'library/responsive-images.php' );

/** Gutenberg editor support */
require_once( 'library/gutenberg.php' );

/** If your site requires protocol relative url's for theme assets, uncomment the line below */
// require_once( 'library/class-foundationpress-protocol-relative-theme-assets.php' );

/* Register widget area at bottom of home page -- will hold photos */
if ( function_exists('register_sidebar') )
  register_sidebar(array(
    'name' => 'Front Page Bottom',
    'before_widget' => '<div class = "images__widget">',
    'after_widget' => '</div>',
    'before_title' => '<h2>',
    'after_title' => '</h2>',
  )
);

// Function to get leaderboard data and append team info
function get_athletes() {
    /*
     * This whole thing uses 2018 data (for now). Update it once the Open
     * officially starts!
    */

    // Hard-code IDs of athletes by team
    $red_team_ids = array("413402", "131824", "1297646", "931856", "1304621", "1167840", "404218", "521316", "1266833", "530374", "738293", "182909", "145716", "96588", "959720", "1312258", "1012382");
    $blue_team_ids = array("1294483", "110387", "1269102", "108900", "796546", "1333866", "324345", "170108", "781668", "50884", "262288", "908366", "47227", "731205", "1207039", "1210751", "1297587");
    $green_team_ids = array("665310", "665310", "498403", "300060", "1036743", "1185747", "1299276", "66647", "354357", "662788", "559773", "1092875", "944486", "517107", "913881", "327442", "932950");

    // Fetch live men's & women's leaderboards from games.crossfit.com
    $all_men = json_decode(file_get_contents('https://games.crossfit.com/competitions/api/v1/competitions/open/2018/leaderboards?division=1&region=0&scaled=0&sort=0&occupation=0&page=1&affiliate=4259'))->leaderboardRows;
    $all_women = json_decode(file_get_contents('https://games.crossfit.com/competitions/api/v1/competitions/open/2018/leaderboards?division=2&region=0&scaled=0&sort=0&occupation=0&page=1&affiliate=4259'))->leaderboardRows;

    // Put all athletes into one big array!
    $all_athletes = array_merge($all_men, $all_women);

    // Add team indicator to each entrant
    foreach($all_athletes as $athlete) {
      if( in_array($athlete->entrant->competitorId, $red_team_ids) ) {
        $athlete->entrant->team = 'red';
      }
      elseif( in_array($athlete->entrant->competitorId, $blue_team_ids) ) {
        $athlete->entrant->team = 'blue';
      }
      elseif( in_array($athlete->entrant->competitorId, $green_team_ids) ) {
        $athlete->entrant->team = 'green';
      }
    }

    // For REST API testing, just return the whole thing, unsorted, for now
    return $all_athletes;
}
// Register athlete info retrieval with REST API!
add_action( 'rest_api_init', function () {
  register_rest_route( 'athletes', '/json', array(
    'methods' => 'GET',
    'callback' => 'get_athletes',
  ) );
} );
