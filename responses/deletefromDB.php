<?php
require_once '../db_connection.php';

$stmt = $db->prepare("DELETE FROM correct_responses WHERE category = ?");
$stmt->execute(['animals']);

echo "Entries deleted successfully!";
?>
