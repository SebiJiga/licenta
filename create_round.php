<?php
 require_once 'functions.php';
 require_once 'db_connection.php';
 session_start();
 
 if(!is_logged_in() || !isset($_SESSION['room_code'])) {
     exit;
 }

 $room_code = $_POST['room_code'];

 $stmt = $db->prepare("INSERT INTO rounds (room_code) VALUES (?)");
 $stmt->execute([$room_code]);
?>
