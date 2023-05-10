<?php
require_once('functions.php');
require_once('db_connection.php');
session_start();

if(!is_logged_in()){
    header('Location: login.php');
    exit();
}

// Check if the room code is set
if (!isset($_POST['room_code'])) {
    header("Location: index.php");
    exit;
}

// Get the room code from the form
$room_code = $_POST['room_code'];

// Check if the room exists in the database
$stmt = $db->prepare("SELECT * FROM rooms WHERE code = :code");
$stmt->execute(array(':code' => $room_code));
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    $_SESSION['error'] = "Invalid room code";
    header("Location: index.php");
    exit;
}

// Set the room_code for the user
$user_id = $_SESSION['id'];
$stmt = $db->prepare("UPDATE users SET room_code = ? WHERE id = ?");
$stmt->execute([$room_code, $user_id]);


// Redirect to the room page
header("Location: room.php?code=" . urlencode($room_code));
exit;
?>
