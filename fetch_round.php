<?php
require_once 'functions.php';
require_once 'db_connection.php';
session_start();

if (!is_logged_in() || !isset($_SESSION['room_code'])) {
    echo json_encode(['error' => 'User not the game creator']);
    exit;
}

$room_code = $_POST['room_code'] ?? '';

if (empty($room_code)) {
    echo json_encode(['error' => 'No room code provided']);
    exit;
}

$stmt = $db->prepare("SELECT MAX(round_number) as max_round FROM rounds WHERE room_code = ?");
$stmt->execute([$room_code]);
$max_round = $stmt->fetch(PDO::FETCH_ASSOC)['max_round'];

if (empty($max_round)) {
    echo json_encode(['error' => 'No rounds for this room code']);
    exit;
}

$stmt = $db->prepare("SELECT letter FROM rounds WHERE room_code = ? AND round_number = ?");
$stmt->execute([$room_code, $max_round]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result !== false) {
    $letter = $result['letter'];
    echo json_encode(['letter' => $letter]);
} else {
    echo json_encode(['error' => 'No round found for this room code and round number']);
    exit;
}
?>
