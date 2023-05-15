<?php 
require_once 'db_connection.php';
require_once 'functions.php';
session_start();

if (!is_logged_in() || !isset($_SESSION['room_code'])) {
    exit;
}

$room_code = $_SESSION['room_code'];
$timer = $_POST['timer'];
$rounds = $_POST['rounds'];

$stmt = $db->prepare("UPDATE rooms SET game_started = 1, timer = ?, rounds = ? WHERE code = ?");
$stmt->execute([$timer, $rounds, $room_code]);

$_SESSION['timer'] = $_POST['timer'];
$_SESSION['rounds'] = $_POST['rounds'];

?>