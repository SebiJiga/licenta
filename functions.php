<?php 
function is_logged_in() {
    if (isset($_SESSION['id'])) {
        return true;
    } else {
        return false;
    }
}

function generateRandomCode($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

function generateRandomLetter() {
    static $usedLetters = array();

    do {
        $letter = chr(65 + rand(0,25));
    } while (in_array($letter, $usedLetters));

    $usedLetters[] = $letter;

    return $letter;
}

function getCorrectAnswers($db, $category, $letter) {
    $stmt = $db->prepare('SELECT word FROM correct_responses WHERE category = ? AND word LIKE ?');
    $stmt->execute([$category, $letter . '%']);

    $correctAnswers = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $correctAnswers[] = $row['word'];
    }

    return $correctAnswers;
}

function normalize($string) {
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = strtolower($string);
    return $string;
}
?>

