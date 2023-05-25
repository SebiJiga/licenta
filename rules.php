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
    <div id="rules"></div>                     
    <img src="eng_flag.jpg" id="en_flag">
    <img src="ro_flag.jpg" id="ro_flag">
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
        const profilePicture = document.getElementById('profilePicture');
        const profilePopupOverlay = document.getElementById('profilePopupOverlay');
        const profilePopupClose = document.getElementById('profilePopupClose');

        profilePicture.addEventListener('click', (e) => {
            e.preventDefault(); // this line prevents the default behavior of the anchor tag
            profilePopupOverlay.classList.add('popup-show');
        });

        profilePopupClose.addEventListener('click', () => {
            profilePopupOverlay.classList.remove('popup-show');
        });

        var rules = {
        "en": "<b>The rules of the game are:</b><br>\
        You can create or join a room<br>\
        If you create a room, you will be the creator. You can choose the number of rounds and how long a round will last<br>\
        When creating the room, a code is automatically generated. Share the code with your friends and they will join in the room created by you.<br>\
        By pressing the 'Start game' button, you will be redirected to the game where each round a letter will be automatically generated and you will have to write Countries, Cities, Mountains, Waters, Plants, Animals, Names. Any unique answer among all users receives 10 points, a non-unique answer receives 5 points. Answers must start with the generated letter, and be correct. Correct means to exist in the database.<br>\
        At the end of the game, the player with the most points after all rounds is considered the winner.",

        "ro": "<b>Regulile jocului sunt urmatoarele:</b><br>\
        Poti crea sau intra intr-o camera<br>\
        Daca creezi o camera, vei fi creatorul. Vei putea alege numarul de runde si cat va dura o runda<br>\
        La crearea camerei, un cod este generat automat. Impartaseste codul cu prietenii tai iar ei se vor alatura in camera creata de tine.<br>\
        La apasarea butonului 'Start game', veti fi redirectionati catre joc unde in fiecare runda o litera va fi generata automat si va trebui sa scrii Tari, Orase, Munti, Ape, Plante, Animale, Nume. Orice raspuns unic printre toti utilizatorii primeste 10 puncte, un raspuns care nu e unic primeste 5 puncte. Raspunsurile trebuie sa inceapa cu litera generata, si sa fie corecte. Corecte inseamna sa existe in data de baze.<br>\
        La sfarsit de joc, jucatorul cu cele mai multe puncte dupa toate rundele este considerat castigator."
    };

        document.getElementById("en_flag").addEventListener("click", function () {
            document.getElementById("rules").innerHTML = rules["en"];
        });

        document.getElementById("ro_flag").addEventListener("click", function () {
            document.getElementById("rules").innerHTML = rules["ro"];
        });
    });
    </script>
</body>

</html>