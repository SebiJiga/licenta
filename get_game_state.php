<?php
require_once 'functions.php';
require_once 'db_connection.php';
session_start();

if (!is_logged_in() || !isset($_SESSION['room_code'])) {
    exit;
}

$room_code = $_SESSION['room_code'];

$stmt = $db->prepare("SELECT status FROM rooms WHERE code = ?");
$stmt->execute([$room_code]);
$status = $stmt->fetch(PDO::FETCH_ASSOC)['status'];

echo json_encode(['status' => $status]);
?>
