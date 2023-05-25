<?php
require_once 'functions.php';
require_once 'db_connection.php';
session_start();

if (!is_logged_in() || !isset($_SESSION['room_code'])) {
    echo json_encode(['error' => 'User not logged in or room code not set']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);
  $user_id = $data['user_id'];

  $sql = "SELECT username, profile_picture FROM users WHERE id = ?";
  $stmt = $db->prepare($sql);
  $stmt->bindParam(1, $user_id, PDO::PARAM_INT);
  $stmt->execute();
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user) {
    // Return the user's information
    echo json_encode([
      'winnerId' => $user_id,
      'username' => $user['username'],
      'profile_picture' => $user['profile_picture'],
    ]);
  } else {
    echo json_encode(['error' => 'User not found']);
  }
}
?>
