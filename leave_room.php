<?php
require_once 'db_connection.php';
require_once 'functions.php';
session_start();

if(!is_logged_in()){
    header('Location: login.php');
    exit();
}
session_start();

$user_id = $_SESSION['id'];
$stmt = $db->prepare("UPDATE users SET room_code = NULL WHERE id = ?");
$stmt->execute([$user_id]);

header('Location: index.php');
?>