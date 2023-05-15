<?php
session_start();
require_once 'db_connection.php';
require_once 'functions.php';

if (!is_logged_in()) {
header('Location: login.php');
exit();
}

$user_id = $_SESSION['id'];

if (!isset($_SESSION['profile_picture'])) {
    header('Location: profile.php');
    exit();
}

$image_path = $_SESSION['profile_picture'];

if (file_exists($image_path)) {
    unlink($image_path);
}


$stmt = $db->prepare('UPDATE users SET profile_picture = NULL WHERE id = :id');
$stmt->execute(['id' => $user_id]);

unset($_SESSION['profile_picture']);

header('Location: index.php');
exit();
?>