<?php
require_once 'db_connection.php';
require_once 'functions.php';
session_start();

if (!is_logged_in()) {
    echo json_encode([]);
    exit();
}

$room_code = $_SESSION['room_code'];

$stmt = $db->prepare("SELECT id, username, profile_picture FROM users WHERE room_code = ?");
$stmt->execute([$room_code]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT game_started FROM rooms WHERE code = ?");
$stmt->execute([$room_code]);
$game_started = $stmt->fetchColumn();

echo json_encode(['users' => $users, 'game_started' => (bool)$game_started]);
?>