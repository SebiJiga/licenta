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
            
            $image_path = 'uploads/' . $user_id . '.' . $image_ext;
            move_uploaded_file($image_tmp, $image_path);
            $stmt = $db->prepare('UPDATE users SET profile_picture = :profile_picture WHERE id = :id');
            $stmt->execute(['profile_picture' => $image_path, 'id' => $user_id]);
            $_SESSION['profile_picture'] = $image_path;
        }
    }
     // Redirect back to profile page
     header('Location: profile.php');
     exit;
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>TOMAPAN</title>
    <link rel="stylesheet" type="text/css" href="styles.css">

</head>
<body>
<header>
    <div class="header-left">
        <a href="index.php" class="text-decoration">TOMAPAN</a>
    </div>
    <div class="header-right">
        <div class="user-info">
            <a href="profile.php">
                <?php if(isset($_SESSION['profile_picture'])) { ?>
                    <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="User profile picture">
                <?php } else { ?>
                    <img src="Sample_User_Icon.png" alt="User profile picture">
                    <?php } ?>
            </a>
            <span><?php echo $_SESSION['username']; ?></span>
        </div>
        <a href="logout.php" class="logout-btn">Log out</a>
    </div>
</header>


<div class="main-container">
		<form method="POST" action="profile.php" enctype="multipart/form-data">
			<div class="form-group">
				<label for="profile_picture">Profile Picture:</label>
				<input type="file" name="profile_picture" id="profile_picture">
			</div>
			<div class="form-group">
				<label for="username">Username:</label>
				<input type="text" name="username" id="username">
			</div>
            <form>
			<div class="button-container">
				<button class="button-save-changes"type="submit">Save Changes</button>
			    <form method="POST" action="delete_profile_picture.php">
				<button class="button-delete" type="submit">Delete Profile Picture</button>
			    </form>
            </div>
	</div>
	</div>
</body>
</html>
