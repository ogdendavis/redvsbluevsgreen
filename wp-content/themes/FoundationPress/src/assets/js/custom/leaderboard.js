export default {
  init() {
    drawTeamLeaderboard();
    addSort();
  }
}

function drawTeamLeaderboard(sortBy = 'Points') {
  // teamLeaderBoardData is exposed from the back end as a JSON object

  const firstPlace = teamLeaderboardData.red.rank === 1 ? teamLeaderboardData.red :
                     teamLeaderboardData.blue.rank === 1 ? teamLeaderboardData.blue : teamLeaderboardData.green;

  const secondPlace = teamLeaderboardData.red.rank === 2 ? teamLeaderboardData.red :
                      teamLeaderboardData.green.rank === 2 ? teamLeaderboardData.green :
                      teamLeaderboardData.blue;

  const thirdPlace = teamLeaderboardData.red.rank === 3 ? teamLeaderboardData.red :
                     teamLeaderboardData.blue.rank === 3 ? teamLeaderboardData.blue :
                     teamLeaderboardData.green;

  drawTeam(firstPlace, 1, sortBy);
  drawTeam(secondPlace, 2, sortBy);
  drawTeam(thirdPlace, 3, sortBy);

}

function drawTeam(team, place, sortBy = 'Points') {
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
  })

  // athletes.sort(function(a,b) {
  //   // Remember, lower points is better!
  //   return a.tcfPointTotal - b.tcfPointTotal;
  // });

  const sortedAthletes = sortAthletes(athletes, sortBy);

  // Populate the team info (empty in front-page.php)
  const tabContentSelector = containerId + ' div.accordion-content';
  const tabContent = document.querySelector(tabContentSelector);

  let tabContentContent = '';
  // Table setup
  tabContentContent += '<table class="teamLeaderboard__athleteTable" id="' + team.color + 'Leaderboard">';
  tabContentContent += '<thead><tr><th>Athlete</th><th>Gender</th><th>Points</th><th>19.1</th><th>19.2</th><th>19.3</th><th>19.4</th><th>19.5</th></thead>';
  // Populate athlete rows
  sortedAthletes.forEach(function(athlete, index) {
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

function sortAthletes(athletes, column) {
  // Function to enable sorting by column

  if (column === 'Points') {
    // If sorting by overall score
    athletes.sort(function(a,b) {
      return a.tcfPointTotal - b.tcfPointTotal;
    });
  }
  else if (column.split('.').length > 1) {
    // If sorting by one workout score
    // Get the index we'll need to find the correct score
    const thisWOD = Number(column.split('.')[1]) - 1;

    // Protect against someone trying to sort by a WOD that has no score yet
    if (athletes[0].scores.length > thisWOD) {
      athletes.sort(function(a,b) {
        return a.scores[thisWOD].tcfPoints - b.scores[thisWOD].tcfPoints;
      });
    }
    else {
      // If they clicked on a WOD with no score, return overall score sort
      athletes.sort(function(a,b) {
        return a.tcfPointTotal - b.tcfPointTotal;
      });
    }

  }
  else if (column === 'Athlete') {
    // If sorting by name
    athletes.sort(function(a,b) {
      if (a.entrant.competitorName < b.entrant.competitorName) {
        return -1;
      }
      else if (a.entrant.competitorName > b.entrant.competitorName) {
        return 1;
      }
      return 0;
    });
  }
  else if (column === 'Gender') {
    // Sort by points first, so you see gender results in points order
    athletes.sort(function(a,b) {
      return a.tcfPointTotal - b.tcfPointTotal;
    });
    // Now sort by gender
    athletes.sort(function(a,b) {
      if (a.entrant.gender < b.entrant.gender) {
        return -1;
      }
      else if (a.entrant.gender > b.entrant.gender) {
        return 1;
      }
      return 0;
    });
  }

  return athletes;
}

function addSort(scope = 'all') {
  // After all tables are drawn, this adds an event listener to each table header
  const targetHeaders = scope === 'all' ?
      document.querySelectorAll('table.teamLeaderboard__athleteTable th') :
      document.querySelectorAll('table#' + scope + 'Leaderboard th');

  targetHeaders.forEach(function(header) {
    header.addEventListener('click', clickManager);
  });
}

function clickManager(event) {
  // Event listener for table headers, used to sort leaderboards
  // Grab sort criterion from header that was clicked
  const sortBy = event.target.innerText;

  // Get id from the leaderboard table, and use that to get the leaderboard JSON for that team
  const teamColor = event.target.parentNode.parentNode.parentNode.id.replace('Leaderboard','');
  const team = teamLeaderboardData[teamColor];

  // Re-draw the leaderboard, and re-add event listeners to the headers
  drawTeam(team, team.rank, sortBy);
  addSort(team.color);
}
