<?php
require_once 'db_connection.php';
require_once 'functions.php';
session_start();

if (!is_logged_in()) {
    exit;
}

// Handle incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);
$room_code = $data['room_code'];

$stmt = $db->prepare("DELETE FROM rooms WHERE code = ?");
$stmt->execute([$room_code]);

// Send a response back to the front-end
echo json_encode(['message' => 'Room deleted']);
?>
