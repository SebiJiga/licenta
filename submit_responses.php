<?php

require_once 'db_connection.php';
require_once('functions.php');
session_start();

$responses = json_decode($_POST['responses'], true);
$userId = $_SESSION['id'];


?>