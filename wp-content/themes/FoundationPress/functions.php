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

  // Add teens! Just 14-15 range, since we don't have any 16-17, this year
  $teen_boys = json_decode(file_get_contents('https://games.crossfit.com/competitions/api/v1/competitions/open/2019/leaderboards?division=14&region=0&scaled=0&sort=0&occupation=0&page=1&affiliate=4259'))->leaderboardRows;
  $teen_girls = json_decode(file_get_contents('https://games.crossfit.com/competitions/api/v1/competitions/open/2019/leaderboards?division=15&region=0&scaled=0&sort=0&occupation=0&page=1&affiliate=4259'))->leaderboardRows;

  // Put all athletes into one big array, and return it
  $all_athletes = array_merge($all_men, $all_women, $teen_boys, $teen_girls);
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
        $wod = $athlete_record->ordinal - 1;

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
    // Tied scores will share the lowest (best) points available for the score
    foreach($athletes as $athlete) {
      $athlete->scores[$i]->tcfPoints = min(array_keys($wod_scores, $athlete->scores[$i]->score)) + 1;
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

  // Helper function to generate team points by week
  function score_team_one_wod ( $team, $wod ) {
    $team_score = 0;
    foreach ($team as $athlete) {
      $team_score += $athlete->scores[$wod]->tcfPoints;
    }
    return $team_score;
  }

  // Helper function to generate team total points
  // This will be used as a tiebreaker!
  function score_team_total_points( $team ) {
    $score = 0;
    foreach ( $team as $athlete ) {
      $score += $athlete->tcfPointTotal;
    }
    return $score;
  }

  // Helper function to generate team overall ranking points
  // Teams are scored as if they were athletes -- 1st place in a week gets 1
  // point, 2nd place gets 2, and 3rd gets 3. Leader is the team with the fewest
  // points so far in competition, etc.
  function score_teams_overall( $all_scores_object ) {
    // Create object to hold weekly scores (ranks)
    $overall_scores = new stdClass();
    $overall_scores->red = 0;
    $overall_scores->blue = 0;
    $overall_scores->green = 0;

    // Go through each WOD, and calculate scores to add to object
    $number_wods = count((array)$all_scores_object->red->wods);
    for ($i = 0; $i < $number_wods; $i++) {
      // Get team scores for that WOD
      $red = $all_scores_object->red->wods->{$i};
      $blue = $all_scores_object->blue->wods->{$i};
      $green = $all_scores_object->green->wods->{$i};

      $best_score = min($red, $blue, $green);
      if ($best_score === $red) {
        $best_team = 'red';
      }
      else if ($best_score === $blue) {
        $best_team = 'blue';
      }
      else {
        $best_team = 'green';
      }

      $worst_score = max($red, $blue, $green);
      if ($worst_score === $red) {
        $worst_team = 'red';
      }
      else if ($worst_score === $blue) {
        $worst_team = 'blue';
      }
      else {
        $worst_team = 'green';
      }

      $used = array($best_team, $worst_team);
      if (!in_array('red', $used)) {
        $mid_team = 'red';
      }
      else if (!in_array('blue', $used)) {
        $mid_team = 'blue';
      }
      else {
        $mid_team = 'green';
      }

      $overall_scores->{$best_team} += 1;
      $overall_scores->{$mid_team} += 2;
      $overall_scores->{$worst_team} += 3;
    }

    return $overall_scores;
  }

  // Helper function to rank teams. Last thing done, assumes object has weekly
  // scores (wods array), total score (overall), and overall points
  // Returns object with team ranks!
  function rank_teams( $teams ) {
    // First: overall_points
    // Tiebreak: total score (overall)

    // Create result object
    $ranks = new stdClass();
    $ranks->red = 0;
    $ranks->blue = 0;
    $ranks->green = 0;

    // Sort by overall_points (main ranking criterion) first
    foreach ($teams as $one_team) {
      $team_points = $one_team->overall_points;
      if ($team_points <= $teams->red->overall_points && $team_points <= $teams->blue->overall_points && $team_points <= $teams->green->overall_points) {
        $ranks->{$one_team->color} = 1;
      }
      elseif ($team_points >= $teams->red->overall_points && $team_points >= $teams->blue->overall_points && $team_points >= $teams->green->overall_points) {
        $ranks->{$one_team->color} = 3;
      }
      else {
        $ranks->{$one_team->color} = 2;
      }
    }

    // Helper function to break ties. Modifies $ranks
    function break_ties($team1, $team2, $place, &$ranks) {
      if ($place === 3) {
        // If teams are tied at spots 2 & 3, they'll both end up with a rank of 3
        $better = 2;
        $worse = 3;
      }
      else if ($place === 1) {
        // If teams are tied at spots 1 & 2, they'll both end up with a rank of 1
        $better = 1;
        $worse = 2;
      }
      if ($teams->{$team1}->overall < $teams->{$team2}->overall) {
        $ranks->{$team1} = $better;
        $ranks->{$team2} = $worse;
      }
      else {
        $ranks->{$team2} = $better;
        $ranks->{$team1} = $worse;
      }
    }

    // Check for a tied rank. If it exists, fix it with total score (overall)
    if ($ranks->red === $ranks->blue) {
      break_ties('red', 'blue', $ranks->red, $ranks);
    }
    else if ($ranks->red === $ranks->green) {
      break_ties('red', 'green', $ranks->red, $ranks);
    }
    else if ($ranks->blue === $ranks->green) {
      break_ties('blue', 'green', $ranks->blue, $ranks);
    }

    return $ranks;
  }

  // Create a return object
  $team_scores = new stdClass();

  // Add team color within object. For use when creating team-based leaderboard
  // in display_team_leaderboard
  $team_scores->red->color = 'red';
  $team_scores->blue->color = 'blue';
  $team_scores->green->color = 'green';

  // Add weekly scores
  $wods = count(reset($red_team)->scores);
  for ($i = 0; $i < $wods; $i++) {
    $team_scores->red->wods->{$i} = score_team_one_wod( $red_team, $i );
    $team_scores->blue->wods->{$i} = score_team_one_wod( $blue_team, $i );
    $team_scores->green->wods->{$i} = score_team_one_wod( $green_team, $i );
  }

  // Add overall scores
  $team_scores->red->overall = score_team_total_points( $red_team );
  $team_scores->blue->overall = score_team_total_points( $blue_team );
  $team_scores->green->overall = score_team_total_points( $green_team );

  $overall_points = score_teams_overall( $team_scores );

  // Add overall points to team objects
  foreach ($overall_points as $team => $team_points) {
    $team_scores->{$team}->overall_points = $team_points;
  }

  // Rank teams using overall_points first, and then overall (total team score)
  // as tiebreaker

  $overall_ranks = rank_teams($team_scores);

  foreach ($overall_ranks as $team_color => $team_rank) {
    $team_scores->{$team_color}->rank = $team_rank;
  }
  
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
