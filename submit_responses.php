<?php
require_once 'functions.php';
require_once 'db_connection.php';
session_start();

if (!is_logged_in() || !isset($_SESSION['room_code'])) {
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

$input = json_decode(file_get_contents('php://input'), true);
$room_code = $input['room_code'];
$round_number = $input['round_number'];
$responses = $input['responses'];
$user_id = $_SESSION['id'];


$stmt = $db->prepare('SELECT * FROM rounds WHERE room_code = ? AND round_number = ?');
$stmt->execute([$room_code, $round_number]);

if ($stmt->rowCount() > 0) {
// Insert the new response
$query = "INSERT INTO responses (user_id, room_code, country, city, mountain, waters, plants, animals, names, round_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $db->prepare($query);
$stmt->execute([$user_id, $room_code, $responses['country'], $responses['city'], $responses['mountain'], $responses['waters'], $responses['plants'], $responses['animals'], $responses['names'], $round_number]);
//set game status to 'reviewing'
$stmt = $db->prepare("UPDATE rooms SET status = 'reviewing' WHERE code = ?");
$stmt->execute([$room_code]);

echo json_encode(['Responses:' => 'submitted']);
} else {
    echo json_encode([
        'Responses' => 'error',
        'room_code' => $room_code,
        'round_number' => $round_number
    ]);
    
}

?>
