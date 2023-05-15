<?php
 require_once 'functions.php';
 require_once 'db_connection.php';
 session_start();
 
 if(!is_logged_in() || !isset($_SESSION['room_code'])) {
     exit;
 }

 $stmt = $db->prepare("SELECT id FROM rounds WHERE room_code = ? ORDER BY round_number DESC LIMIT 1");
 $stmt->execute([$room_code]);
 $current_round_id = $stmt->fetchColumn();
 
$query = "SELECT * FROM responses WHERE round_id = :round_id";
$statement = $db->prepare($query);
$statement->execute([':round_id' => $currentRoundId]);

// Fetch the responses
$responses = $statement->fetchAll(PDO::FETCH_ASSOC);

// Return the responses as a JSON object
echo json_encode(['responses' => $responses]);
?>
