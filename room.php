<?php
require_once 'db_connection.php';
require_once 'functions.php';
session_start();

if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}
if (!isset($_GET['code'])) {

    // Redirect the user back to the main page if the code is not set
    header('Location: index.php');
    exit;
}

$room_code = $_GET['code'];
// Fetch users from the database
$stmt = $db->prepare("SELECT * FROM users WHERE room_code = ?");
$stmt->execute([$room_code]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT * FROM rooms WHERE code = ?");
$stmt->execute([$room_code]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    $_SESSION['error'] = "Invalid room code";
    header("Location: index.php");
    exit;
}


$is_creator = $room['creator_id'] == $_SESSION['id'];

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
            <a href="leave_room.php" class="text-decoration">TOMAPAN</a>
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
    <?php if ($is_creator): ?>
        <div class="room-container">
            <p class="room-paragraf">Share this code with other players to invite them to the room.</p>
            <h1 class="room-code">
                <?php echo $room_code; ?>
            </h1>
        </div>
    <?php else: ?>
        <div class="room-container-not-creator">
            <p class="room-paragraf">Share this code with other players to invite them to the room.</p>
            <h1 class="room-code">
                <?php echo $room_code; ?>
            </h1>
        </div>
    <?php endif; ?>
    <div class="users-list-container">
        <h2>Players in the room:</h2>
        <ul class="users-list" id="users-list">
            <?php foreach ($users as $user): ?>
                <li>
                    <?php if (isset($user['profile_picture'])) { ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="User profile picture"
                            class="player-profile-picture">
                    <?php } else { ?>
                        <img src="Sample_User_Icon.png" alt="User profile picture" class="player-profile-picture">
                    <?php } ?>
                    <?php echo htmlspecialchars($user['username']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>


    <?php if ($is_creator): ?>
        <div class="game-settings">
            <label for="timer">Round timer</label>
            <label for="timer">(seconds)</label>
            <select class="timer-settings" id="timer">
                <option value="60">60</option>
                <option value="120">120</option>
                <option value="180">180</option>
                <option value="99999">a lot</option>
            </select>
        </div>

        <div class="rounds"> 
            <label for="rounds">Number of rounds:</label>
            <select class="rounds-settings" id="rounds">
                <option value="5">5 rounds</option>
                <option value="10">10 rounds</option>
                <option value="26">Whole alphabet (26 rounds)</option>
                
            </select>
        </div>

        <button class="start-game-button" id="start-game">Start Game</button>


    <?php endif; ?>




    <script>
        window.onload = function () {
            var startGameButton = document.getElementById('start-game');
            if (startGameButton) {
                startGameButton.addEventListener('click', function () {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'start_game.php', true);

                    var formData = new FormData();
                    formData.append('timer', document.getElementById('timer').value);
                    formData.append('rounds', document.getElementById('rounds').value);

                    xhr.send(formData);
                })
            }
        }

        function fetchUsers() {
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    var users = response.users;
                    if (response.game_started) {
                        window.location.href = 'game.php';
                        return;
                    }

                    var usersList = document.getElementById('users-list');
                    usersList.innerHTML = '';

                    for (var i = 0; i < users.length; i++) {
                        var userItem = document.createElement('li');
                        userItem.className = 'user-item';

                        var img = document.createElement('img');
                        img.className = 'player-profile-picture';
                        img.src = users[i].profile_picture || 'default_profile_picture.jpg';
                        img.alt = users[i].username;

                        var span = document.createElement('span');
                        span.textContent = users[i].username;

                        userItem.appendChild(img);
                        userItem.appendChild(span);
                        usersList.appendChild(userItem);
                    }
                }
            };

            xhr.open('GET', 'fetch_users.php', true);
            xhr.send();
        }
        setInterval(fetchUsers, 1000);


        ///////////////////////////////////script for pop-up
        document.addEventListener('DOMContentLoaded', function () {
            const profilePicture = document.getElementById('profilePicture');
            const profilePopupOverlay = document.getElementById('profilePopupOverlay');
            const profilePopupClose = document.getElementById('profilePopupClose');

            profilePicture.addEventListener('click', (e) => {
                e.preventDefault(); // stop navigating to 'profile.php'
                e.stopPropagation(); // stop the event from bubbling up the DOM tree
                profilePopupOverlay.classList.add('popup-show');
            });

            profilePopupClose.addEventListener('click', () => {
                profilePopupOverlay.classList.remove('popup-show');
            });
        });


    </script>
</body>

</html>