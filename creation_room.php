<?php
require_once 'db_connection.php';
require_once('functions.php');
session_start();

if(!is_logged_in()){
    header('Location: login.php');
    exit();
}


$code = generateRandomCode();

$stmt = $db->prepare("SELECT COUNT(*) FROM rooms WHERE code = ?");
$stmt->execute([$code]);
$count = $stmt->fetchColumn();

while ($count > 0) {
    $code = generateRandomCode();
    $stmt->execute([$code]);
    $count = $stmt->fetchColumn();
}

$stmt = $db->prepare("INSERT INTO rooms (code, id, created_at) VALUES (?, ?, NOW())");
$stmt->execute([$code, $_SESSION['id']]);

$_SESSION['room_code'] = $code;

header('Location: room.php?code=' . $code);
exit;
?>
