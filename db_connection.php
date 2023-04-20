<?php

$db_host = "localhost";
$db_name = "TOMAPAN";
$db_user = "root";
$db_pass = "";
$db = new PDO("mysql:host=$db_host;dbname=$db_name;charset:utf8mb4",$db_user, $db_pass);

// Check connection
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

?>