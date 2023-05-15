<?php
require_once('functions.php');
require_once('db_connection.php');
session_start();

if(!is_logged_in()){
    header('Location: login.php');
    exit();
}

$room_code = $_SESSION['room_code'];

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
</head>

<script>
document.addEventListener('DOMContentLoaded', (event) => {
    fetch('fetch_users.php')
      .then(response => response.json())
      .then(data => {
        let users = data.users

        users.forEach(user => {
          addUserRow(user);
        })
      })

      function addUserRow(user) {
        let table = document.getElementById('response-table');
        let row = document.createElement('tr');
        row.id = 'user-' + user.id;
        let nameCell = document.createElement('td');
        nameCell.textContent = user.username;
        row.appendChild(nameCell);

        let categories = ['country', 'city', 'mountain', 'waters', 'plants', 'animals', 'names'];
        categories.forEach(category => {
        let cell = document.createElement('td');
        cell.textContent = ''; // No responses yet
        row.appendChild(cell);
        });

    
        table.appendChild(row);
      }

    let defaultTimerDuration = <?php echo $timerDuration ?>;
    let timerDuration = defaultTimerDuration;
    var countdownElement = document.getElementById('timer-display');

    var countdown;
    function startTimer() {
        countdown = setInterval(function () {
            console.log("In the interval");
            timerDuration--;
            countdownElement.innerText = timerDuration;

            if(timerDuration <= 0) {
                clearInterval(countdown);
                timerDuration = defaultTimerDuration; // Reset the timer
                endRound();
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

    function submitResponses() {
    var country = document.getElementById('tari').value;
    var city = document.getElementById('orase').value;
    var mountain = document.getElementById('munti').value;
    var waters = document.getElementById('ape').value;
    var plants = document.getElementById('plante').value;
    var animals = document.getElementById('animale').value;
    var names = document.getElementById('nume').value;

    // update the table
    var userId = <?php echo $_SESSION['id']; ?>; 
    var row = document.getElementById('user-' + userId);
    if (row) {
        row.children[1].textContent = country;
        row.children[2].textContent = city;
        row.children[3].textContent = mountain;
        row.children[4].textContent = waters;
        row.children[5].textContent = plants;
        row.children[6].textContent = animals;
        row.children[7].textContent = names;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'submit_responses.php', true);

    var formData = new FormData();
    formData.append('responses', JSON.stringify ({
        'country': country,
        'city': city,
        'mountain': mountain,
        'waters': waters,
        'plants': plants,
        'animals': animals,
        'names': names
    }));

    xhr.send(formData);
}

function updateResponses() {
  fetch('fetch_responses.php')
    .then(response => response.json())
    .then(data => {
      let responses = data.responses;

      responses.forEach(response => {
        let row = document.getElementById('user-' + response.userId);
        if (row) {
          row.children[1].textContent = response.country;
          row.children[2].textContent = response.city;
          row.children[3].textContent = response.mountain;
          row.children[4].textContent = response.waters;
          row.children[5].textContent = response.plants;
          row.children[6].textContent = response.animals;
          row.children[7].textContent = response.names;
        }
      })
    })
}

  setInterval(updateResponses, defaultTimerDuration/2)

    let roundsRemaining = <?php echo $rounds; ?>;
    let usedLetters = [];
    let currentLetter = '';

    function generateLetter() {
      let letter;
      do {
        letter = String.fromCharCode(65 + Math.floor(Math.random() * 26));
      } while (usedLetters.includes(letter));

      usedLetters.push(letter);
      currentLetter = letter;
      document.getElementById('random-letter').textContent = currentLetter;
    }
    
    function startRound() {
      roundsRemaining--;
      if (roundsRemaining < 0) {
        endGame();
        return;
      }

      generateLetter();

      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'create_round.php', true);

      var formData = new FormData();
      formData.append('room_code', <?php echo json_encode($room_code); ?>);
  
      xhr.send(formData);

      setTimeout(startTimer,1500);
    }

    function endRound() {
      submitResponses();
      document.getElementById('rounds-display').textContent = roundsRemaining;
    

      var inputs = ['tari', 'orase', 'munti', 'ape', 'plante', 'animale', 'nume'];
      inputs.forEach(function(inputId) {
      var input = document.getElementById(inputId);
      input.disabled = true;
      input.value = '';
      });
      setTimeout(startRound, 1000);
    }

    function endGame() {
      // Handle the end of the game
    }

    // Start the first round
    startRound();


});
  
</script>
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
  <h1>0</h1>
  <p>Score<p>
</div>

<div id="random-letter"></div>

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
    </tr>
    <!-- The rows for each user will be added here -->
</table>


</body>
</html>
