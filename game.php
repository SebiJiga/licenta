<?php
require_once('functions.php');
require_once('db_connection.php');
session_start();

if(!is_logged_in()){
    header('Location: login.php');
    exit();
}

$timerDuration = $_SESSION['timer'];
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
});
</script>
<body>
<div class="TOMAPAN">
<b class="item1">Tari</b>
<input type="text" class="item2">
<b class="item3"> Orase</b> 
<input type="text" class="item4">
<b class="item5">Munti</b>
<input type="text" class="item6" >
<b class="item7">Ape</b>
<input type="text" class="item8">
<b class="item9">Plante</b>
<input type="text" class="item10">
<b class="item11">Animale</b>
<input type="text" class="item12">
<b class="item13">Nume</b>
<input type="text" class="item14">
</div>
<div class="TOMAPAN-TIMER" id="timer-display"><?php echo isset($_SESSION['timer']) ? $_SESSION['timer'] : '0'; ?> </div>
<div class="TOMAPAN-SCORE">
  <h1>Score</h1>
  <h1>0</h1>
</div>
</body>
</html>
