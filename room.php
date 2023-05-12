<?php
require_once('functions.php');
require_once('db_connection.php');
session_start();

if(!is_logged_in()){
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

<script>
window.onload = function() {
    var startGameButton =document.getElementById('start-game');
    if(startGameButton) {
        startGameButton.addEventListener('click', function() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'start_game.php', true);
            
            var formData = new FormData();
            formData.append('timer', document.getElementById('timer').value);

            xhr.send(formData);
        })
    }
}

function fetchUsers() {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            var users = response.users;
            if(response.game_started) {
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

</script>

<body>
<header>
    <div class="header-left">
        <a href="leave_room.php" class="text-decoration">TOMAPAN</a>
    </div>
    <div class="header-right">
        <div class="user-info">
            <a href="leave_room_profile.php">
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
<?php if ($is_creator): ?>
    <div class="room-container">
        <h1 class ="room-code"> <?php echo $room_code; ?></h1>
        <p class="room-paragraf">Share this code with other players to invite them to the room.</p>
    </div>
    <?php else :?>
    <div class="room-container-not-creator">
        <h1 class ="room-code"> <?php echo $room_code; ?></h1>
        <p class="room-paragraf">Share this code with other players to invite them to the room.</p>
    </div>
    <?php endif; ?>
    <div class="users-list-container">
        <h2>Players in the room:</h2>
            <ul class="users-list"id="users-list">
                <?php foreach ($users as $user): ?>
                <li>
                    <?php if(isset($user['profile_picture'])) { ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="User profile picture" class="player-profile-picture">
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
        <label for="timer">Round timer (in seconds):</label>
        <input class="timer-settings" type="number" id="timer" value="60" min="10" max="240">
    </div>
    <button class="start-game-button" id="start-game">Start Game</button>

<?php endif; ?>

    

</body>
</html>
