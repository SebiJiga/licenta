<?php
require_once 'functions.php';
require_once 'db_connection.php';
session_start();

if (!is_logged_in() || !isset($_SESSION['room_code'])) {
    exit;
}

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);


$room_code = $_SESSION['room_code'];
$round_number = $data['round_number'];

// Fetch the responses
$query = "SELECT * FROM responses WHERE room_code = :room_code AND round_number = :round_number";

$statement = $db->prepare($query);
$statement->execute([':room_code' => $room_code, ':round_number' => $round_number]);

$responses = $statement->fetchAll(PDO::FETCH_ASSOC);
echo json_encode([
    'responses' => $responses,
    'roundNumber' => $round_number
]);
?>
