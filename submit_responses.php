<?php
require_once 'db_connection.php';
require_once 'functions.php';
session_start();

if (!is_logged_in() || !isset($_SESSION['room_code'])) {
    exit;
}


$room_code = $_SESSION['room_code'];
$user_id = $_SESSION['id'];

$stmt = $db->prepare("INSERT INTO 
responses (user_id, room_code, country, city, mountain, waters, plants, animals, names) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$responses = json_decode($_POST['responses'], true);
$stmt->execute(array_merge([$user_id, $room_code], array_values($responses)));
?>