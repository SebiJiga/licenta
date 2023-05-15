<?php 
require_once 'db_connection.php';
require_once 'functions.php';

session_start(); 

if(!is_logged_in()){
    header('Location: login.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    //update username if new username is provided
    if (!empty($_POST['username'])) {
        $username = trim($_POST['username']);
        $user_id = $_SESSION['id'];
        $stmt = $db->prepare('UPDATE users SET username = :username WHERE id = :id');
        $stmt->execute(['username' => $username, 'id' => $user_id]);
        $_SESSION['username'] = $username;
    }

    if(!empty($_FILES['profile_picture']['name'])) {
        $user_id = $_SESSION['id'];
        $image_name = $_FILES['profile_picture']['name'];
        $image_size = $_FILES['profile_picture']['size'];
        $image_tmp = $_FILES['profile_picture']['tmp_name'];
        $image_type = $_FILES['profile_picture']['type'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];


        if(!in_array($image_ext, $allowed_extensions)) {
            $error = 'Invalid file type. Please choose a JPG, JPEG, PNG, or GIF file.';
        } elseif ($image_size > 5000000) {
            $error = 'File size too large. Please choose a file smaller than 5MB.';
        } else {
            if (!file_exists('uploads')) {
                mkdir('uploads');
            }
            
            $image_path = __DIR__ . '/uploads/' . $user_id . '.' . $image_ext;
            move_uploaded_file($image_tmp, $image_path);

            $relative_image_path = 'uploads/' . $user_id . '.' . $image_ext;
            $stmt = $db->prepare('UPDATE users SET profile_picture = :profile_picture WHERE id = :id');
            $stmt->execute(['profile_picture' => $relative_image_path, 'id' => $user_id]);
            $_SESSION['profile_picture'] = $relative_image_path;

        }
    }
     
     header('Location: index.php');
     exit;
}
?>

