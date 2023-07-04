<?php
require_once 'db_connection.php';
require_once 'functions.php';
session_start();

if (!is_logged_in()) {
    header('Location: login.php');
    exit();
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
                <a href="profile.php" id="profilePicture">
                    <?php if (isset($_SESSION['profile_picture'])) { ?>
                        <img src="<?php echo $_SESSION['profile_picture']; ?>" alt="User profile picture">
                    <?php } else { ?>
                        <img src="Sample_User_Icon.png" alt="User profile picture">
                    <?php } ?>
                </a>
                <span>
                    <?php echo $_SESSION['username']; ?>
                </span>
            </div>
            <a href="logout.php" class="logout-btn">Log out</a>
        </div>
    </header>


    <div class="main-container">
        <div class="button-container">
            <a href="creation_room.php" class="button">Create room</a>
            <button onclick="openPopup()" class="button enter-game-btn">Enter a game</button>
            <a href="rules.php" class="button">Rules</a>
        </div>
    </div>

    <div class="popup-profile" id="profilePopupOverlay">
        <div class="popup-container">
            <span class="popup-close" id="profilePopupClose">&times;</span>
            <div class="popup-profile-content">
                <form method="POST" action="profile.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="profile_picture">Profile Picture:</label>
                        <input type="file" name="profile_picture" id="profile_picture">
                    </div>
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" name="username" id="username">
                    </div>
                    <div class="popup-profile-button-container">
                        <button class="button-save-changes" type="submit">Save Changes</button>
                        <button class="button-delete" formaction="delete_profile_picture.php" type="submit">Delete
                            Profile Picture</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <div class="popup-overlay" id="popupOverlay">
        <div class="popup-container">
            <span class="popup-close" id="popupClose">&times;</span>
            <div class="popup-content">
                <form id="enterRoomForm" method="post" action="enter_room.php">
                    <label for="roomCodeInput">Enter room code:</label>
                    <input type="text" id="roomCodeInput" name="room_code"
                        placeholder="Note that the code is case sensitive">
                    <button type="submit">Enter</button>
                </form>
            </div>
        </div>
    </div>


    <script>
        function openPopup() {
            document.getElementById("popupOverlay").classList.add("popup-show");
        }


        function closePopup() {
            document.getElementById("popupOverlay").classList.remove("popup-show");
        }

        const enterRoomBtn = document.querySelector('.enter-game-btn');
        const popupOverlay = document.getElementById('popupOverlay');
        const popupClose = document.getElementById('popupClose');

        enterRoomBtn.addEventListener('click', () => {
            popupOverlay.classList.add('popup-show');
        });

        popupClose.addEventListener('click', () => {
            popupOverlay.classList.remove('popup-show');
        })

        const profilePicture = document.getElementById('profilePicture');
        const profilePopupOverlay = document.getElementById('profilePopupOverlay');
        const profilePopupClose = document.getElementById('profilePopupClose');

        profilePicture.addEventListener('click', (e) => {
            e.preventDefault();
            profilePopupOverlay.classList.add('popup-show');
        });

        profilePopupClose.addEventListener('click', () => {
            profilePopupOverlay.classList.remove('popup-show');
        });


    </script>
</body>

</html>