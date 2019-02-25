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

// Helper function to pull data from stored JSON object
// JSON object was created with functions in legacy-functions.php
function get_current_local_leaderboard() {
  // Refer to webroot/site-assets/json for data structure
  $path = ABSPATH . 'site-assets/json/2019athletes.json';
  $leaderboard = json_decode( file_get_contents( $path ) );
  return $leaderboard;
}

// Helper function to write data to stored JSON object
// Takes a php variable representing the full leaderboard
function write_to_local_leaderboard( $leaderboard_variable ) {
  $jsonObject = json_encode($leaderboard_variable);
  // Get the path for where to store the object, and write it!
  $local_path = ABSPATH . 'site-assets/json/2019athletes.json';
  file_put_contents($local_path, $jsonObject);
}

function get_current_team_scores() {
  // Refer to webroot/site-assets/json for data structure
  $path = ABSPATH . 'site-assets/json/2019teams.json';
  $scores = json_decode( file_get_contents( $path ) );
  return $scores;
}

function write_local_team_scores( $team_scores_variable ) {
  $jsonObject = json_encode($team_scores_variable);
  // Get the path for where to store the object, and write it!
  $local_path = ABSPATH . 'site-assets/json/2019teams.json';
  file_put_contents($local_path, $jsonObject);
}

// Helper function to pull raw data from games site
function get_current_games_leaderboard() {

  // Have to pull by gender, because that's how the games site does it
  $all_men = json_decode(file_get_contents('https://games.crossfit.com/competitions/api/v1/competitions/open/2019/leaderboards?division=1&region=0&scaled=0&sort=0&occupation=0&page=1&affiliate=4259'))->leaderboardRows;
  $all_women = json_decode(file_get_contents('https://games.crossfit.com/competitions/api/v1/competitions/open/2019/leaderboards?division=2&region=0&scaled=0&sort=0&occupation=0&page=1&affiliate=4259'))->leaderboardRows;

  // Put all athletes into one big array, and return it
  $all_athletes = array_merge($all_men, $all_women);
  return $all_athletes;
}

// Function to pull data from games site and use it to add scores to local leaderboard
// This will NOT override scores already in local JSON object
// This function will NOT add new athletes
// To import all new info, use JSON object creation function (name?!)
// Edits or updates to single WOD scores should be manually done in the JSON object
function get_wod_scores_from_games_site() {

  // Get local leaderboard to modify
  $local_leaderboard = get_current_local_leaderboard();
  // Get games leaderboard for data
  $games_leaderboard = get_current_games_leaderboard();

  $updated_records = 0;
  foreach ($games_leaderboard as $games_athlete) {
    // We used competitorId to set athlete index in local JSON object
    $id = $games_athlete->entrant->competitorId;

    // Check that the athlete is in the local leaderboard first
    if ( isset( $local_leaderboard->{$id} ) ) {
      $scores = $games_athlete->scores;

      // Go through the score object for each WOD for this athlete
      foreach ($scores as $athlete_record) {
        // Get the ordinal from the score
        $wod = $athlete_record->ordinal;

        // Check that the that athlete doesn't already have a score for that WOD, and that there is a score in the games leaderboard
        if ( !isset( $local_leaderboard->{$id}->scores[$wod]->score ) && isset( $scores[$wod]->score ) ) {
          // Add absolute score, and note if it's scaled or not
          $local_leaderboard->{$id}->scores[$wod]->score = $scores[$wod]->score;
          $local_leaderboard->{$id}->scores[$wod]->scaled = $scores[$wod]->scaled;
          $updated_records += 1;
        }
      }
    }
  }

  // Update (write-over) the local JSON leaderboard
  write_to_local_leaderboard( $local_leaderboard );

  // Return to validate success in browser
  return 'Updated ' . $updated_records . ' records in local leaderboard';
}

// Helper function to calculate custom tcf score for athletes! Lower is better.
function score_athletes( &$athletes ) {
  // Counting the number of wod scores on the first athlete, assuming that it
  // will be the same for all athletes
  $wods = count(reset($athletes)->scores);

  // Calculate score for each WOD
  for ($i = 0; $i < $wods; $i++) {
    // Create array containing all scores for one WOD
    $wod_scores = array();
    foreach($athletes as $athlete) {
      $wod_scores[] = $athlete->scores[$i]->score;
    }
    // Sort scores high to low
    rsort($wod_scores);

    // Assign each athlete a point total based on how well he/she scored overall
    // Tied scores will share the highest (worst) points available for the score
    foreach($athletes as $athlete) {
      $athlete->scores[$i]->tcfPoints = max(array_keys($wod_scores, $athlete->scores[$i]->score)) + 1;
    }
  }

  // Now calculate overall score
  foreach($athletes as $athlete) {
    // Create point total variable
    $athlete->tcfPointTotal = 0;
    // Add points from each WOD to the total
    foreach($athlete->scores as $score) {
      $athlete->tcfPointTotal += $score->tcfPoints;
    }
  }

  // Return athletes
  return $athletes;
}

function score_local_leaderboard() {
  $local_leaderboard = get_current_local_leaderboard();
  $scored_local_leaderboard = score_athletes( $local_leaderboard );
  write_to_local_leaderboard( $scored_local_leaderboard );
  return 'Scored athletes!';
}

// Function to sort athletes and return only the requested ones (by team or gender)
// Takes a string, and returns a subset of the leaderboard that fits the parameter
function sort_athletes( $parameter ) {
  // Get athletes!
  $athletes = get_current_local_leaderboard();

  // Figure out how we'll sort them
  switch ( $parameter ) {
    case 'red':
      $filter_field = 'team';
      $filter_value = 'red';
      break;
    case 'blue':
      $filter_field = 'team';
      $filter_value = 'blue';
      break;
    case 'green':
      $filter_field = 'team';
      $filter_value = 'green';
      break;
    case 'men':
      $filter_field = 'gender';
      $filter_value = 'M';
      break;
    case 'women':
      $filter_field = 'gender';
      $filter_value = 'F';
      break;
    default:
      // If filters don't match, by default return all athletes
      return $athletes;
  }

  // array_filter doesn't really work on objects! DUH!
  // Filter for the group of athletes requested
  // $result = array_filter($athletes, function($athlete) use ($filter_field, $filter_value) {
  //   return ($athlete->entrant->{$filter_field} == $filter_value ? true : false);
  // });

  // Create a new object to hold the return
  $result = new stdClass();

  // Filter all athletes to populate the result object
  foreach ( $athletes as $athlete ) {
    if ( $athlete->entrant->{$filter_field} === $filter_value ) {
      $result->{$athlete->entrant->competitorId} = $athlete;
    }
  }

  // And return!
  return $result;
}

// Function to calculate total team score
function score_teams() {
  // Get the teams. These will have scores for any WODs already pulled in with
  // a call to the score-athletes-now webhook
  $red_team = sort_athletes( 'red' );
  $blue_team = sort_athletes( 'blue' );
  $green_team = sort_athletes( 'green' );

  // Helper function to generate an overall team score
  function score_team_overall( $team ) {
    $score = 0;
    foreach ( $team as $athlete ) {
      $score += $athlete->tcfPointTotal;
    }
    return $score;
  }

  // Helper function to generate team score by week
  function score_team_one_wod ( $team, $wod ) {
    $team_score = 0;
    foreach ($team as $athlete) {
      $team_score += $athlete->scores[$wod]->tcfPoints;
    }
    return $team_score;
  }

  // Create a return object
  $team_scores = new stdClass();

  // Add overall scores
  $team_scores->red->overall = score_team_overall( $red_team );
  $team_scores->blue->overall = score_team_overall( $blue_team );
  $team_scores->green->overall = score_team_overall( $green_team );

  // Add overall ranks
  foreach ($team_scores as $one_team) {
    $one_score = $one_team->overall;
    if ($one_score >= $team_scores->red->overall && $one_score >= $team_scores->blue->overall && $one_score >= $team_scores->green->overall) {
      $one_team->rank = 1;
    }
    elseif ($one_score <= $team_scores->red->overall && $one_score <= $team_scores->blue->overall && $one_score <= $team_scores->green->overall) {
      $one_team->rank = 3;
    }
    else {
      $one_team->rank = 2;
    }
  }

  // Add weekly scores
  $wods = count(reset($red_team)->scores) - 1;
  for ($i = 0; $i < $wods; $i++) {
    $team_scores->red->wods->{$i} = score_team_one_wod( $red_team, $i );
    $team_scores->blue->wods->{$i} = score_team_one_wod( $blue_team, $i );
    $team_scores->green->wods->{$i} = score_team_one_wod( $green_team, $i );
  }

  // Add team color within object. For use when creating team-based leaderboard
  // in display_team_leaderboard
  $team_scores->red->color = 'red';
  $team_scores->blue->color = 'blue';
  $team_scores->green->color = 'green';

  // Write scores to local team scores JSON object. This object is just written
  // over every time we run the score-teams-now webhook
  write_local_team_scores( $team_scores );

  // Return for confirmation on webhook page
  return 'Red team: ' . $team_scores->red->overall . '-- Blue team: ' . $team_scores->blue->overall . '-- Green team: ' . $team_scores->green->overall;
}

// Functions to get info to display leaderboards!
function display_team_leaderboard() {
  // Get local versions of team scores and leaderboard
  $team_scores = get_current_team_scores();
  $leaderboard = get_current_local_leaderboard();

  // Create an object that has athletes sorted into teams
  $team_leaderboard = new stdClass();

  // Add team scores
  foreach ($team_scores as $team_score) {
    $team_leaderboard->{$team_score->color} = $team_score;
  }

  // Add individuals to their teams
  foreach ($leaderboard as $athlete) {
    switch ( $athlete->entrant->team ) {
      case 'red':
        $team = 'red';
        break;
      case 'blue':
        $team = 'blue';
        break;
      case 'green':
        $team = 'green';
        break;
    }
    $team_leaderboard->{$team}->athletes->{$athlete->entrant->competitorId} = $athlete;
  }

  // We're sending this to the front end, so make it JSON!
  return json_encode($team_leaderboard);
}

// Register athlete info retrieval with REST API!
// Routes to return athletes by team or gender

// add_action( 'rest_api_init', function () {
//   register_rest_route( 'tcf-athletes/v1/sort', '/(?P<sort>\D+)', array(
//     'methods' => 'GET',
//     'callback' => 'sort_athletes',
//   ) );
// } );
//
// // Route to return team scores
// add_action( 'rest_api_init', function () {
//   register_rest_route( 'tcf-athletes/v1', '/teams', array(
//     'methods' => 'GET',
//     'callback' => 'score_teams',
//   ) );
// } );

// Route to cause local leaderboard to update with info for one WOD from games site
add_action( 'rest_api_init', function () {
  register_rest_route( 'tcf-athletes/v1', '/get-wod-scores-now', array(
    'methods' => 'GET',
    'callback' => 'get_wod_scores_from_games_site',
  ) );
} );

// Route to create tcf Points for athletes in local leaderboard
add_action( 'rest_api_init', function () {
  register_rest_route( 'tcf-athletes/v1', '/score-athletes-now', array(
    'methods' => 'GET',
    'callback' => 'score_local_leaderboard',
  ) );
} );

// Route to score teams in local leaderboard
add_action( 'rest_api_init', function () {
  register_rest_route( 'tcf-athletes/v1', '/score-teams-now', array(
    'methods' => 'GET',
    'callback' => 'score_teams',
  ) );
} );

// Route to send team leaderboard JSON object to the front end
add_action( 'rest_api_init', function () {
  register_rest_route( 'tcf-athletes/v1', '/get-team-leaderboard', array(
    'methods' => 'GET',
    'callback' => 'display_team_leaderboard',
  ) );
} );
x
