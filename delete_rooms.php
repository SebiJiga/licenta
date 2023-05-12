<?php
require_once 'db_connection.php';

// Define the maximum age of rooms in seconds
$max_age = 6 * 60 * 60; // 6 hours

// Calculate the timestamp for the cutoff point
$cutoff_time = time() - $max_age;

// Delete rooms that are older than the cutoff time
$stmt = $db->prepare("DELETE FROM rooms WHERE created_at < ?");
$stmt->execute([date("Y-m-d H:i:s", $cutoff_time)]);
?>
