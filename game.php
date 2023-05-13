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
    var countdownElement = document.getElementById('timer-display');
    var timerDuration = <?php echo isset($_SESSION['timer']) ? $_SESSION['timer'] : '0'; ?>;
    
    console.log("Timer duration: " + timerDuration);

    var countdown = setInterval(function () {
        console.log("In the interval");
        timerDuration--;
        countdownElement.innerText = timerDuration;

        if(timerDuration <= 0) {
            clearInterval(countdown);
        }  
    }, 1000);

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
        'country': contry,
        'city': city,
        'mountai':mountain,
        'waters':waters,
        'plants':plants,
        'animals':animals,
        'names':names
      }));

      xhr.send(formData);
    }
});
</script>
<body>
<div class="TOMAPAN">
<b class="item1">Tari</b>
<input type="text" class="item2" id="tari">
<b class="item3"> Orase</b> 
<input type="text" class="item4" id="orase">
<b class="item5">Munti</b>
<input type="text" class="item6" id="munti">
<b class="item7">Ape</b>
<input type="text" class="item8" id="ape">
<b class="item9">Plante</b>
<input type="text" class="item10" id="plante">
<b class="item11">Animale</b>
<input type="text" class="item12" id="animale">
<b class="item13">Nume</b>
<input type="text" class="item14" id="nume">
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
</body>
</html>
