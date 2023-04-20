<?php 
session_start(); 
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
		<div class="button-container">
			<button class="button">Create room</button>
			<button class="button">Enter a game</button>
		</div>
	</div>
</body>
</html>
