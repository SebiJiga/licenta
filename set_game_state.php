<?php 
require_once('functions.php');
require_once('db_connection.php');
session_start();

if (!is_logged_in() || !isset($_SESSION['room_code'])) {
  header('Location: login.php');
  exit();
}

$room_code = $_POST['room_code'];
$status = $_POST['status'];

$stmt = $db->prepare("UPDATE rooms SET status = ? WHERE code = ?");
$stmt->execute([$status, $room_code]);

echo json_encode(['succes' => "Game state changed to $status"]);
?>