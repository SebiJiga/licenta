<?php 
require_once 'db_connection.php';
require_once 'functions.php';
session_start();

if (!is_logged_in() || !isset($_SESSION['room_code'])) {
    exit;
}

$room_code = $_SESSION['room_code'];

$stmt = $db->prepare("UPDATE rooms SET game_started = 1 WHERE code = ?");
$stmt->execute([$room_code]);

$_SESSION['timer'] = $_POST['timer'];

?>