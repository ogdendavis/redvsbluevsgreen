<?php
/* Functions that were used in inital leaderboard planning & setup, but are no
 * longer needed. Kept here for reference, or to bust 'em out next year
 */

 // LEGACY - the function used to initially create the locally-hosted JSON
 // object with the 2018 leaderboard, for testing
 function instantiate_leaderboard_2018() {
   $leaderboard_path_2018 = ABSPATH . '/assets/json/rvbvg2018.json';
   // $rvbvg = json_decode(file_get_contents($leaderboard_path));
   // $rvbvg->test2 = "This is a writing test";
   // $returnJSON = json_encode($rvbvg);
   // file_put_contents($leaderboard_path, $returnJSON);
   // return $rvbvg;
   $all_men = json_decode(file_get_contents('https://games.crossfit.com/competitions/api/v1/competitions/open/2018/leaderboards?division=1&region=0&scaled=0&sort=0&occupation=0&page=1&affiliate=4259'))->leaderboardRows;
   $all_women = json_decode(file_get_contents('https://games.crossfit.com/competitions/api/v1/competitions/open/2018/leaderboards?division=2&region=0&scaled=0&sort=0&occupation=0&page=1&affiliate=4259'))->leaderboardRows;
   $all_athletes = array_merge($all_men, $all_women);

   // Easier than unsetting all the variables we don't want -- create a new
   // object to hold the variables we do want!
   $rvbvg2018 = new stdClass();

   foreach ($all_athletes as $athlete) {
     // Making the key for the athlete object in our local version the same as
     // the competitorId assigned by the games site. This will help later with
     //  matching new scores pulled from the games site with the correct athlete
     // in the local leaderboard
     $i = $athlete->entrant->competitorId;

     // entrant -- we want to keep competitorId, competitorName, gender, profilePicS3key
     $rvbvg2018->{$i}->entrant->competitorId = $athlete->entrant->competitorId;
     $rvbvg2018->{$i}->entrant->competitorName = $athlete->entrant->competitorName;
     $rvbvg2018->{$i}->entrant->gender = $athlete->entrant->gender;
     $rvbvg2018->{$i}->entrant->profilePicS3key = $athlete->entrant->profilePicS3key;
     // entrant -- we want to add team (empty, for now)
     $rvbvg2018->{$i}->entrant->team = '';

     //ui -- we don't want anything from there!

     //scores -- we want to keep the score from some WODs for now, so we can test adding scores, later
     $rvbvg2018->{$i}->scores[0]->score = $athlete->scores[0]->score;
     $rvbvg2018->{$i}->scores[0]->scaled = $athlete->scores[0]->scaled;
     $rvbvg2018->{$i}->scores[1]->score = $athlete->scores[1]->score;
     $rvbvg2018->{$i}->scores[1]->scaled = $athlete->scores[1]->scaled;
     $rvbvg2018->{$i}->scores[2]->score = $athlete->scores[2]->score;
     $rvbvg2018->{$i}->scores[2]->scaled = $athlete->scores[2]->scaled;
     //scores -- we want to add tcfPoints (empty, for now)
     $rvbvg2018->{$i}->scores[0]->tcfPoints = 0;
     $rvbvg2018->{$i}->scores[1]->tcfPoints = 0;
     $rvbvg2018->{$i}->scores[2]->tcfPoints = 0;

     //We don't care about overallRank, because we'll calculate that differently
     // But we do want tcfPointTotal!
     $rvbvg2018->{$i}->tcfPointTotal = 0;
   } // end foreach athlete

   // Encode the object as JSON, and write it to the local path
   $json2018 = json_encode($rvbvg2018);
   file_put_contents($leaderboard_path_2018, $json2018);

   // We did it!
   return 'Success: New local 2018 leaderboard created!';
 }

 // LEGACY - route used to create 2018 leaderboard
 add_action( 'rest_api_init', function () {
   register_rest_route( 'tcf-athletes/v1', '/new-leaderboard-2018', array(
     'methods' => 'GET',
     'callback' => 'instantiate_leaderboard_2018',
   ) );
 } );

 // LEGACY - Function to get leaderboard data from games site and append team info
 function get_athletes() {
   // MODIFY to use stored data, or pull from games site


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

   // Compute custom score for gender-combined leaderboard
   $scored_athletes = score_athletes($all_athletes);

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

   // Return all athletes, with teams appended
   return $all_athletes;
 }

 // LEGACY - Generic route to get athletes from games site, sorted by overall standing
 add_action( 'rest_api_init', function () {
   register_rest_route( 'tcf-athletes/v1', '/all', array(
     'methods' => 'GET',
     'callback' => 'get_athletes',
   ) );
 } );

 // Function to pull data from games site and use it to add scores to local leaderboard
 // CAUTION: Will overwrite scores alread in stored local JSON object
 // This function will NOT add new athletes
 function import_games_leaderboard_wod( $request ) {
   // $year is the year of the open (leaderboards exist for 2018 and 2019)
   $year = $request['year'];
   // $wodindex is the index of the wod in the athlete object, zero-indexed
   // For example, to update 18.1, you'd run update_leaderboard( 2018, 0 );
   $wodindex = $request['wodindex'];

   // Get local leaderboard to modify
   $local_leaderboard = get_local_leaderboard( $year );
   // Get games leaderboard for data
   $games_leaderboard = get_games_leaderboard( $year );

   foreach ($games_leaderboard as $games_athlete) {
     // We used competitorId to set athlete index in local JSON object
     $id = $games_athlete->entrant->competitorId;
     if ( isset( $local_leaderboard->{$id} ) ) {
       $local_leaderboard->{$id}->scores[$wodindex]->score = $games_athlete->scores[$wodindex]->score;
     }
   }

   // Write the updates to a JSON object
   $jsonObject = json_encode($local_leaderboard);
   // Get the path for where to store the object, and write it!
   $local_path = ABSPATH . '/assets/json/rvbvg' . $year . '.json';
   file_put_contents($local_path, $jsonObject);

   return 'Updated WOD with index ' . $wodindex . ' from ' . $year . '.';
 }
