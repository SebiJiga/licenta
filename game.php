<?php
require_once('functions.php');
require_once('db_connection.php');
session_start();

if (!is_logged_in()) {
  header('Location: login.php');
  exit();
}

if (!isset($_SESSION['room_code'])) {
  header('Location: login.php');
  exit();
} else {
  $room_code = $_SESSION['room_code'];
}

$stmt = $db->prepare("SELECT timer, rounds FROM rooms WHERE code = ?");
$stmt->execute([$room_code]);
$game_settings = $stmt->fetch(PDO::FETCH_ASSOC);


$timerDuration = $game_settings['timer'];
$rounds = $game_settings['rounds'];

?>

<!DOCTYPE html>
<html>

<head>
  <title>TOMAPAN - Game</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
  <script src="https://cdn.socket.io/4.6.1/socket.io.js"></script>
</head>


<body>
  <div class="TOMAPAN">
    <b class="item1">Tari</b>
    <input type="text" class="item2" id="tari" disabled>
    <b class="item3"> Orase</b>
    <input type="text" class="item4" id="orase" disabled>
    <b class="item5">Munti</b>
    <input type="text" class="item6" id="munti" disabled>
    <b class="item7">Ape</b>
    <input type="text" class="item8" id="ape" disabled>
    <b class="item9">Plante</b>
    <input type="text" class="item10" id="plante" disabled>
    <b class="item11">Animale</b>
    <input type="text" class="item12" id="animale" disabled>
    <b class="item13">Nume</b>
    <input type="text" class="item14" id="nume" disabled>
  </div>
  <div class="TOMAPAN-TIMER" id="timer-display">
    <?php echo $timerDuration; ?>
  </div>
  <div class="TOMAPAN-ROUNDS" id="rounds-display">
    <?php echo $rounds; ?>
  </div>

  <div class="TOMAPAN-SCORE">
    <h1 id="total-game-score">0</h1>
    <p>Score
    <p>
  </div>

  <div id="loading-spinner" style="display: none;">
    <div class="loader"></div>
  </div>
  <div id="random-letter"></div>
  <div class="status-text">
    <b id="status-text"></b>
  </div>
  <table id="response-table">
    <tr class="response-table-first">
      <th>User</th>
      <th>Tari</th>
      <th>Orase</th>
      <th>Munti</th>
      <th>Ape</th>
      <th>Plante</th>
      <th>Animale</th>
      <th>Nume</th>
      <th>Scor</th>
      <div class="response-table-content">
    </tr>
    <!-- The rows for each user will be added here -->'
    </div>
  </table>



  <script>
    document.addEventListener('DOMContentLoaded', (event) => {
      var socket = io('http://localhost:3000');

      socket.on('fetchCorrectResponses', () => {
        console.log("It works");
        updateResponses();
      });

      socket.on('fetchScore', () => {
        console.log("Scores fetched");
        scores();
      });

      socket.on('startRoundCountdown', (data) => {
        let countdownTime = data.countdownTime;

        let countdownInterval = setInterval(() => {
          countdownTime--;

          console.log(`Time remaining: ${countdownTime} seconds`);

          document.getElementById('status-text').textContent = `Next round will start in ${countdownTime} seconds...`;

          if (countdownTime <= 0) {
            clearInterval(countdownInterval);
            console.log("Next round started");

            if (currentRound <= roundsRemaining) {
              createRound();
            }
            startWaitingCountdown();
          }
        }, 1000);
      });


      let defaultTimerDuration = <?php echo $timerDuration ?> * 1000;
      let timerDuration = defaultTimerDuration / 1000;


      function fetchUsers() {
        fetch('fetch_users.php')
          .then(response => response.json())
          .then(data => {
            console.log('Fetched users: ', data.users);
            data.users.forEach(user => {
              if (user) {
                addUserRow(user);
              } else {
                console.error("Undefined user encountered in fetchUsers.");
              }
            });
          })
          .catch(error => console.error('Error fetching users: ', error));
      }


      function addUserRow(user) {
        console.log('addUserRow called with user: ', user);
        if (!user) {
          console.error("addUserRow called with undefined user");
          return;
        }
        let table = document.getElementById('response-table');
        let row = document.createElement('tr');
        row.id = 'user-' + user.id;

        let nameCell = document.createElement('td');
        nameCell.textContent = user.username;
        row.appendChild(nameCell);

        let categories = ['country', 'city', 'mountain', 'waters', 'plants', 'animals', 'names'];
        categories.forEach(category => {
          let cell = document.createElement('td');
          cell.id = 'user-' + user.id + '-' + category;
          cell.textContent = ''; // No responses yet
          row.appendChild(cell);
        });

        let totalScoreCell = document.createElement('td');
        totalScoreCell.id = 'user-' + user.id + '-total-score';
        totalScoreCell.className = 'table-score';
        totalScoreCell.textContent = '';
        row.appendChild(totalScoreCell);

        table.appendChild(row);
      }

      function clearUserRows() {
        let table = document.getElementById('response-table');
        let rows = table.getElementsByTagName('tr');
        while (rows.length > 1) {
          table.removeChild(rows[1]);
        }
      }


      var countdownElement = document.getElementById('timer-display');
      var countdown;
      function startTimer() {
        countdown = setInterval(function () {
          console.log("In the interval");
          timerDuration--;
          countdownElement.innerText = timerDuration;

          if (timerDuration <= 0) {
            clearInterval(countdown);
            endRound();
            setGameState('reviewing')
          }
        }, 1000);

        document.getElementById('tari').disabled = false;
        document.getElementById('orase').disabled = false;
        document.getElementById('munti').disabled = false;
        document.getElementById('ape').disabled = false;
        document.getElementById('plante').disabled = false;
        document.getElementById('animale').disabled = false;
        document.getElementById('nume').disabled = false;
      }

      let roundsRemaining = <?php echo $rounds; ?>;
      var countdownRounds = document.getElementById('rounds-display');
      let roundsToDisplay = roundsRemaining;
      let currentLetter = '';


      let currentRound = 1;

      function createRound() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'create_round.php', true);
        xhr.onload = function () {
          if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.error && response.error === 'Maximum rounds reached') {
              // handle maximum rounds reached
              alert('Maximum rounds reached');
              return;
            }
            if (response.message && response.message === 'User not the game creator') {
              // Handle when user is not the game creator
              console.log('User not the game creator');
              return;
            }

            socket.emit('roundCreated', {
              round: response.round,
              letter: response.letter
            });
          } else {
            console.error('An error occurred during the transaction');
          }
        };
        var formData = new FormData();
        formData.append('room_code', <?php echo json_encode($room_code); ?>);
    formData.append('letter', currentLetter);
    xhr.send(formData);
      }

    function startRound() {
      if (currentRound > roundsRemaining) {
        endGame();
        return;
      }

      fetchLetter();
      setTimeout(startTimer, 1500);
      currentRound++;
    }

    function fetchLetter() {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'fetch_round.php', true);
      xhr.onload = function () {
        if (xhr.status === 200) {
          var response = JSON.parse(xhr.responseText);
          if (response.letter) {
            currentLetter = response.letter;
            var letterElement = document.getElementById('random-letter');
            var parent = letterElement.parentNode;
            var clone = letterElement.cloneNode(true);
            clone.textContent = response.letter;
            parent.removeChild(letterElement);
            parent.appendChild(clone);

            document.getElementById('loading-spinner').style.display = 'none'; // Hide the loading spinner
          }
        } else {
          console.error('An error occurred during the transaction');
        }
      };

      var formData = new FormData();
      formData.append('room_code', <?php echo json_encode($room_code); ?>);
      xhr.send(formData);
    }

    function endRound() {
      submitResponses();

      roundsToDisplay--;

      countdownRounds.innerText = roundsToDisplay;
      document.getElementById('rounds-display').textContent = roundsToDisplay;


      var inputs = ['tari', 'orase', 'munti', 'ape', 'plante', 'animale', 'nume'];
      inputs.forEach(function (inputId) {
        var input = document.getElementById(inputId);
        input.disabled = true;
        input.value = '';
      });

      if (roundsToDisplay === 0) {
        endGame();
      }
      timerDuration = defaultTimerDuration / 1000;

    }

    function submitResponses() {

      var country = document.getElementById('tari').value;
      var city = document.getElementById('orase').value;
      var mountain = document.getElementById('munti').value;
      var waters = document.getElementById('ape').value;
      var plants = document.getElementById('plante').value;
      var animals = document.getElementById('animale').value;
      var names = document.getElementById('nume').value;

      fetch('submit_responses.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          room_code: <?php echo json_encode($room_code); ?>,
          round_number: currentRound - 1,
          responses: {
          country: country,
          city: city,
          mountain: mountain,
          waters: waters,
          plants: plants,
          animals: animals,
          names: names
        }
  }),
})
  .then(response => response.json())
      .then(data => {
        console.log('Success:', data);
        socket.emit('responsesSaved');
      });
}

    function updateResponses() {

      fetch('fetch_responses.php', {

        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ round_number: currentRound - 1 }),
      })
        .then(response => response.json())
        .then(data => {
          let responses = data.responses;

          responses.forEach(response => {
            let categories = ['country', 'city', 'mountain', 'waters', 'plants', 'animals', 'names'];
            categories.forEach(category => {
              let cell = document.getElementById('user-' + response.user_id + '-' + category);
              if (cell) {
                console.log(`Updating cell for user ${response.user_id} and category ${category}`);
                cell.textContent = response[category];
              } else {
                console.error(`Cell for user ${response.user_id} and category ${category} not found`);
              }
            });
          });

          socket.emit('responsesUpdated');
        });
    }

    let totalGameScore = 0;

    function scores() {
      fetch('correct_responses.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          room_code: <?php echo json_encode($room_code); ?>,
          round_number: currentRound - 1,
        }),
      })
    .then(response => response.json())
      .then(data => {
        console.log(data);
        Object.entries(data).forEach(([userId, userScore]) => {
          let categories = ['country', 'city', 'mountain', 'waters', 'plants', 'animals', 'names'];
          categories.forEach(category => {
            let cell = document.getElementById('user-' + userId + '-' + category);
            if (cell) {
              console.log(`Updating cell for user ${userId} and category ${category}`);
              cell.textContent = userScore[category].response + ' (' + userScore[category].score + ')';
            } else {
              console.error(`Cell for user ${userId} and category ${category} not found`);
            }
          });

          let totalScoreCell = document.getElementById('user-' + userId + '-total-score');
          if (totalScoreCell) {
            totalScoreCell.textContent = userScore['total'];
          } else {
            console.error(`Total score cell for user ${userId} not found`);
          }

          totalGameScore += userScore['total'];
          document.getElementById('total-game-score').textContent = totalGameScore;

          setTimeout(() => {
            socket.emit('startNextRound');
            console.log('startNextRound socket called');
          }, 10 * 1000);
        });
      });
    }





    function endGame() {
      // Handle the end of the game
    }

    let waitingCountdownDuration = 5;
    let waitingCountdown;
    let isCountdownStarted = false;


    function startWaitingCountdown() {
      isCountdownStarted = true;
      waitingCountdown = setInterval(() => {
        document.getElementById('status-text').textContent = "The game will start in " + waitingCountdownDuration + " seconds. Prepare yourself!";
        if (waitingCountdownDuration <= 0) {
          clearInterval(waitingCountdown);
          document.getElementById('status-text').textContent = 'The game is now in progress! Hurry up and fill the boxes with your responses!';

          waitingCountdownDuration = 5;
          isCountdownStarted = false;

        } else {
          waitingCountdownDuration--;
        }
      }, 1000);

      setTimeout(() => {
        setGameState('playing');

      }, waitingCountdownDuration * 1000);
    }

    socket.on('startCountdown', () => {
      startWaitingCountdown();
    });


    let roomCode = '<?php echo $room_code ?>';
    function setGameState(state) {
      let formData = new FormData();
      formData.append('room_code', roomCode);
      formData.append('status', state);

      fetch('set_game_state.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.succes) {
            console.log(data.succes);
          } else {
            console.error(data.error);
          }
        })
    }

    socket.on('fetchCorrectResponses', () => {
      console.log("it works");
      //updateResponses();
      //scores();
    });

    let lastState = null;
    function checkGameState() {
      fetch('get_game_state.php')
        .then(response => response.json())
        .then(data => {
          const statusText = document.getElementById('status-text');
          switch (data.status) {
            case 'waiting':
              if (lastState !== 'waiting') {
                socket.emit('startWaitingCountdown');

                fetchUsers();
                createRound();
              }
              break;

            case 'playing':
              if (lastState !== 'playing') {
                startRound();
              }
              break;

            case 'reviewing':
              if (lastState !== 'reviewing') {
                statusText.textContent = 'Reviewing the results...';


              }
              break;
          }
          lastState = data.status;


        });
      setTimeout(checkGameState, 3000);
    }

    checkGameState();
    });


  </script>
</body>

</html>