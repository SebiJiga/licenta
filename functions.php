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
?>
