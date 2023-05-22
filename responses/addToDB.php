<?php
require_once '../db_connection.php';

$file = fopen('plants.txt', 'r');

while (!feof($file)) {
    $line = fgets($file);
    $countryArray = explode(',', $line);
    $country = trim($countryArray[0]);

    if ($country != '') {
        $stmt = $db->prepare('SELECT COUNT(*) FROM correct_responses WHERE word = ?');
        $stmt->execute([$country]);
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            $stmt = $db->prepare('INSERT INTO correct_responses (category, word) VALUES (?, ?)');
            $stmt->execute(['plants', $country]);
        }
    }
}


fclose($file);

echo "Countries added successfull";
?>
