<?php
require_once('functions.php');
require_once('db_connection.php');
session_start();

if(!is_logged_in()){
    header('Location: login.php');
    exit();
}

$timerDuration = $_SESSION['timer'];
$rounds = $_SESSION['rounds'];
?>

<!DOCTYPE html>
<html>
<head>
  <title>TOMAPAN - Game</title>
  <link rel="stylesheet" type="text/css" href="styles.css">
</head>

<script>
document.addEventListener('DOMContentLoaded', (event) => {
    let defaultTimerDuration = <?php echo isset($_SESSION['timer']) ? $_SESSION['timer'] : '0'; ?>;
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
      var waters =document.getElementById('ape').value;
      var plants =document.getElementById('plante').value;
      var animals = document.getElementById('animale').value;
      var names = document.getElementById('nume').value;

      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'submit_response.php', true);

      var formData = new FormData();
      formData.append('responses', JSON.stringify ({
        'country': country,
        'city': city,
        'mountain':mountain,
        'waters':waters,
        'plants':plants,
        'animals':animals,
        'names':names
      }));

      xhr.send(formData);
    }

    let roundsRemaining = <?php echo isset($_SESSION['rounds']) ? $_SESSION['rounds'] : '0'; ?>;
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
      setTimeout(startTimer,1500);
    }

    function endRound() {
      submitResponses();


      // Disable the input fields and clear their values
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
  <?php echo isset($_SESSION['timer']) ? $_SESSION['timer'] : '0'; ?> 
</div>
<div class="TOMAPAN-ROUNDS" id="rounds-display">
  <?php echo isset($_SESSION['rounds']) ? $_SESSION['rounds'] : '0'; ?> 
</div>

<div class="TOMAPAN-SCORE">
  <h1>0</h1>
  <p>Score<p>
</div>

<div id="random-letter"></div>
</body>
</html>
