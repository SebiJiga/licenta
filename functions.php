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
    static $usedLetters = [];

    if(count($usedLetters) == 26) {
        throw new Exception('All letters have been used.');
    }

    do {
        $letter = chr(65 + rand(0, 25));
    } while (in_array($letter, $usedLetters));

    $usedLetters[] = $letter;

    return $letter;
}


function normalize($string) {
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    $string = strtolower($string);
    return $string;
}

function removePrefixes($string) {
    // Order matters here: longer prefixes should come before shorter ones
    $prefixes = ['muntii', 'muntele', 'oceanul', 'marea', 'raul', 'lacul'];
    $string = strtolower($string);
    foreach ($prefixes as $prefix) {
        if (substr($string, 0, strlen($prefix)) == $prefix) {
            $string = trim(substr($string, strlen($prefix)));
        }
    }
    return $string;
}


function getCorrectAnswers($db, $category, $letter) {
    $stmt = $db->prepare('SELECT word FROM correct_responses WHERE category = ?');
    $stmt->execute([$category]);

    $correctAnswers = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $normalizedWord = removePrefixes(normalize($row['word']));

        if (substr($normalizedWord, 0, strlen($letter)) === strtolower($letter)) {
            $correctAnswers[] = $normalizedWord;
        }
    }

    return $correctAnswers;
}


?>

