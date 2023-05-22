<?php
require_once 'functions.php';
require_once 'db_connection.php';
session_start();

if (!is_logged_in() || !isset($_SESSION['room_code'])) {
    echo json_encode(['error' => 'User not logged in or room code not set']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$room_code = $data['room_code'];
$round_number = $data['round_number'];

$stmt = $db->prepare('SELECT letter FROM rounds WHERE room_code = ? AND round_number = ?');
$stmt->execute([$room_code, $round_number]);
$letter = $stmt->fetchColumn();

$stmt = $db->prepare('SELECT * FROM responses WHERE room_code = ? AND round_number = ?');
$stmt->execute([$room_code, $round_number]);

$responsesArray = [];

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $responsesArray[$row['user_id']] = [
        'country' => $row['country'],
        'city' => $row['city'],
        'mountain' => $row['mountain'],
        'waters' => $row['waters'],
        'plants' => $row['plants'],
        'animals' => $row['animals'],
        'names' => $row['names']
    ];
    
}
echo json_encode($responsesArray);


$categories = ['country', 'city', 'mountain', 'waters', 'plants', 'animals', 'names'];

$score = 0;
$scoreForResponse = 0;

foreach($responsesArray as $userId => $responses) {
    foreach($categories as $category) {
        $correctAnswers = getCorrectAnswers($db, $category, $letter);
        $normalizedCorrectAnswers = array_map('normalize', $correctAnswers);

        $response = isset($responses[$category]) ? $responses[$category] : '';
        $normalizedResponse = normalize($response);

        if(empty($normalizedResponse)) {
            $scoreForResponse = 0;
        } else {
            if(in_array($normalizedResponse, $normalizedCorrectAnswers)) {
                $scoreForResponse = 5;
            }

            // $unique = true;
            // foreach($responsesArray as $otherUserId => $otherResponses) {
            //     if($otherUserId != $userId && $otherResponses[$category] == $normalizedResponse) {
            //         $unique = false;
            //         break;
            //     }
            // }
            // if($unique) {
            //     $scoreForResponse += 5;
            // }
        }

        $score += $scoreForResponse;
        
        // Display the response and score for this response
        echo "User $userId's response for $category: " . $responses[$category] . ". Score for this response: $scoreForResponse\n";
        $scoreForResponse = 0;
    }
    // Display the total score for this user
    echo "User $userId's total score: $score\n";
    // Reset the score for the next user
    $score = 0;
}


?>
