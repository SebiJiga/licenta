<?php
session_start();

if (!isset($_GET['code'])) {
    // Redirect the user back to the main page if the code is not set
    header('Location: index.php');
    exit;
}

$room_code = $_GET['code'];
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
<div class="room-container">
    <h1 class ="room-code"> <?php echo $room_code; ?></h1>
    <p class="room-paragraf">Share this code with other players to invite them to the room.</p>
</div>
</body>
</html>
