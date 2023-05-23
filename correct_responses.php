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
$scoresArray = []; // Add this line at the beginning

$categories = ['country', 'city', 'mountain', 'waters', 'plants', 'animals', 'names'];

$score = 0;
$scoreForResponse = 0;

foreach($responsesArray as $userId => $responses) {
    $userScore = []; 
    foreach($categories as $category) {
        $correctAnswers = getCorrectAnswers($db, $category, $letter);
        $normalizedCorrectAnswers = array_map('normalize', $correctAnswers);
        $normalizedCorrectAnswers = array_map('removePrefixes', $normalizedCorrectAnswers);

        if(isset($responses[$category])) {
            $normalizedResponse = normalize($responses[$category]);
            $normalizedResponse = removePrefixes($normalizedResponse);
        } else {
            $normalizedResponse = '';
        }
        if(empty($normalizedResponse)) {
            $scoreForResponse = 0;
        } else {
            $unique=true;
            if(in_array($normalizedResponse, $normalizedCorrectAnswers)) {
                $scoreForResponse = 5;

                foreach($responsesArray as $otherUserId => $otherResponses) {
                    if($otherUserId != $userId && normalize(removePrefixes($otherResponses[$category])) == $normalizedResponse) {
                        $unique = false;
                        break;
                    }
                }

                if($unique) {
                    $scoreForResponse += 5;
                }
            }
        }

        $score += $scoreForResponse;
        
        $userScore[$category] = $scoreForResponse;
        $scoreForResponse = 0;
    }
    $userScore['total'] = $score; 
    $scoresArray[$userId] = $userScore; 
    $score = 0;
}

$finalArray = [];
foreach($responsesArray as $userId => $responses) {
    $userScore = $scoresArray[$userId];
    foreach ($responses as $category => $response) {
        $finalArray[$userId][$category] = ["response" => $response, "score" => $userScore[$category]];
    }
    $finalArray[$userId]['total'] = $userScore['total'];
}

echo json_encode($finalArray);