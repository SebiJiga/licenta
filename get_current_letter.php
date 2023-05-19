<?php
require_once 'functions.php';
require_once 'db_connection.php';
session_start();

if (!is_logged_in() || !isset($_SESSION['room_code'])) {
    echo json_encode(['error' => 'User not logged in or room code not set']);
    exit;
}

$room_code = $_SESSION['room_code'];

// Get the current round number
$stmt = $db->prepare("SELECT letter FROM rounds WHERE room_code = ? ORDER BY round_number DESC LIMIT 1");
$stmt->execute([$room_code]);
$letter = $stmt->fetchColumn();

echo json_encode(['letter' => $letter]);

?>
