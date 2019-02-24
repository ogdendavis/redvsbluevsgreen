export default {
  init() {
    drawTeamLeaderboard();
  }
}

function drawTeamLeaderboard() {
  const firstPlace = teamLeaderboardData.red.rank === 1 ? teamLeaderboardData.red :
                     teamLeaderboardData.blue.rank === 1 ? teamLeaderboardData.blue : teamLeaderboardData.green;

  const secondPlace = teamLeaderboardData.red.rank === 2 ? teamLeaderboardData.red :
                      teamLeaderboardData.blue.rank === 2 ? teamLeaderboardData.blue :
                      teamLeaderboardData.green;

  const thirdPlace = teamLeaderboardData.red.rank === 3 ? teamLeaderboardData.red :
                     teamLeaderboardData.blue.rank === 3 ? teamLeaderboardData.blue :
                     teamLeaderboardData.green;

  drawTeam(firstPlace, 1);
  drawTeam(secondPlace, 2);
  drawTeam(thirdPlace, 3);

}

function drawTeam(team, place) {
  const containerId = place === 1 ? '#teamAccordionFirst' :
                      place === 2 ? '#teamAccordionSecond' :
                      '#teamAccordionThird';

  const container = document.querySelector(containerId);

  // Populate the accordion tab title (empty in front-page.php)
  const tabTitleSelector = containerId + ' a.accordion-title';
  const tabTitle = document.querySelector(tabTitleSelector);

  // Proper casing for team
  const teamName = team.color === 'red' ? 'Red Team' :
                   team.color === 'blue' ? 'Blue Team': 'Green Team';
  // Get path for team icon with wings
  const imagePath = window.location.origin + window.location.pathname + 'site-assets/' + team.color + '-team-wings-302x96.png';
  // Spell out the team's place
  const properPlace = place === 1 ? '1st Place' :
                      place === 2 ? '2nd Place' :
                      '3rd Place';

  // Add content to the tab, with styling classes!
  let tabTitleContent = ''
  tabTitleContent += '<span class="teamLeaderboard__teamPlace">' + properPlace + '</span>';
  tabTitleContent += '<img src="' + imagePath + '" class="teamLeaderboard__icon">'
  tabTitleContent += '<h2 class="teamLeaderboard__teamName">' + teamName + '</h2>';
  tabTitleContent += '<span class="teamLeaderboard__teamScore">' + team.overall + ' points</span>';
  tabTitle.innerHTML = tabTitleContent;

  // Create an array containing all athlete objects, sorted by overall points
  // This will be used to populate the team info tab!
  const athletes = Object.keys(team.athletes).map(function(i) {
    return team.athletes[i];
  }).sort(function(a,b) {
    // Remember, lower points is better!
    return a.tcfPointTotal > b.tcfPointTotal;
  });
  console.log(athletes);
  // Populate the team info (empty in front-page.php)
  const tabContentSelector = containerId + ' div.accordion-content';
  const tabContent = document.querySelector(tabContentSelector);

  let tabContentContent = '';
  // Table setup
  tabContentContent += '<table class="teamLeaderboard__athleteTable">';
  tabContentContent += '<thead><tr><th>Athlete</th><th>Gender</th><th>Points</th><th>19.1</th><th>19.2</th><th>19.3</th><th>19.4</th><th>19.5</th></thead>';
  // Populate athlete rows
  athletes.forEach(function(athlete, index) {
    tabContentContent += '<tr>';
    tabContentContent += '<td>' + athlete.entrant.competitorName + '</td>';
    tabContentContent += '<td>' + athlete.entrant.gender + '</td>';
    tabContentContent += '<td>' + athlete.tcfPointTotal + '</td>';
    // Hard-coding number of WODs -- this could cause trouble later
    for (let i=0; i<5 ; i++) {
      tabContentContent += '<td>'
      tabContentContent += athlete.scores[i] ? athlete.scores[i].tcfPoints : '--';
      tabContentContent += '</td>';
    }
  });
  // Add it to the accordion tab!
  tabContent.innerHTML = tabContentContent;

}
