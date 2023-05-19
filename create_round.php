<?php
require_once 'functions.php';
require_once 'db_connection.php';
session_start();

if (!is_logged_in() || !isset($_SESSION['room_code'])) {
    echo json_encode(['error' => 'User not logged in or room code not set']);
    exit;
}

$room_code = $_POST['room_code'];
$letter = generateRandomLetter();

$stmt = $db->prepare("SELECT creator_id FROM rooms WHERE code = ?");
$stmt->execute([$room_code]);
$creator_id = $stmt->fetchColumn();

if ($_SESSION['id'] == $creator_id) {
    // Get the maximum allowed rounds for this room
    $stmt = $db->prepare("SELECT rounds FROM rooms WHERE code = ?");
    $stmt->execute([$room_code]);
    $max_allowed_rounds = $stmt->fetch(PDO::FETCH_ASSOC)['rounds'];

    // Get the current round number
    $stmt = $db->prepare("SELECT MAX(round_number) as max_round FROM rounds WHERE room_code = ?");
    $stmt->execute([$room_code]);
    $max_round = $stmt->fetch(PDO::FETCH_ASSOC)['max_round'];
    $round_number = $max_round ? $max_round + 1 : 1;

    // Check if the round_number is less than or equal to the max_allowed_rounds
    if ($round_number <= $max_allowed_rounds) {
        $start_time = date('Y-m-d H:i:s');

        $stmt=$db->prepare("INSERT INTO rounds (room_code, round_number, start_time, letter) VALUE (?, ?, ?, ?)");
        $stmt->execute([$room_code, $round_number, $start_time, $letter]);

        echo json_encode(['success' => 'New round created', 'round' => $round_number, 'letter' => $letter]);

    } else {
        echo json_encode(['error' => 'Maximum rounds reached']);
    }
} else {
    echo json_encode(['error' => 'User not the game creator']);
}

?>