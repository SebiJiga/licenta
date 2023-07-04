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
    <script src="https://cdn.socket.io/4.6.1/socket.io.js"></script>
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
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="60">60</option>
                <option value="120">120</option>
                <option value="180">180</option>
                <option value="99999999">a lot</option>
            </select>
        </div>

        <div class="rounds">
            <label for="rounds">Number of rounds:</label>
            <select class="rounds-settings" id="rounds">
                <option value ="3">3 rounds</option>
                <option value="5">5 rounds</option>
                <option value="10">10 rounds</option>
                <option value="26">Whole alphabet (26 rounds)</option>

            </select>
        </div>

        <button class="start-game-button" id="start-game">Start Game</button>



    <?php endif; ?>

    <div class="chat-container">
        <div class="chat-messages-container" id="chat-messages">
        </div>
        <form id="chat-form" class="chat-form">
            <div class="input-container">
                <input type="text" id="chat-input" placeholder="Enter your message">
                <input type="submit" value="Send" class="send-button">
                <button id="xand0-button" class="xand0-button">Play Tic-Tac-Toe</button>
                <button id="cards-button" class="cards-button" onclick="openCardWindow()">Play Card-War</button>
            </div>
        </form>
    </div>

    <div id="gameContainer">
        <table id="ticTacToeTable">
            <tr>
                <td class="cell" data-row="0" data-col="0"></td>
                <td class="cell" data-row="0" data-col="1"></td>
                <td class="cell" data-row="0" data-col="2"></td>
            </tr>
            <tr>
                <td class="cell" data-row="1" data-col="0"></td>
                <td class="cell" data-row="1" data-col="1"></td>
                <td class="cell" data-row="1" data-col="2"></td>
            </tr>
            <tr>
                <td class="cell" data-row="2" data-col="0"></td>
                <td class="cell" data-row="2" data-col="1"></td>
                <td class="cell" data-row="2" data-col="2"></td>
            </tr>
        </table>
    </div>


    <script>
        window.onload = function () {
            var socket = io('http://localhost:3000');

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

            var chatForm = document.getElementById('chat-form');
            var chatInput = document.getElementById('chat-input');
            var chatMessages = document.getElementById('chat-messages');

            socket.emit('newUser', { user: '<?php echo $_SESSION['username']; ?>', room_code: '<?php echo $room_code; ?>' });
            socket.on('chatMessage', (data) => {
                var messageItem = document.createElement('li');

                var messageText = document.createElement('span');
                messageText.textContent = data.text;
                messageText.className = 'message-text';

                var usernameSpan = document.createElement('span');
                usernameSpan.textContent = data.user;
                usernameSpan.className = 'username-chat';

                var img = document.createElement('img');
                img.className = 'message-profile-picture';
                img.src = data.profile_picture || 'Sample_User_icon.png';
                img.alt = data.user;

                if (data.user === '<?php echo $_SESSION['username']; ?>') {
                    messageItem.className = 'my-message';
                    messageItem.appendChild(messageText);
                    //messageItem.appendChild(usernameSpan);
                    messageItem.appendChild(img);
                } else {
                    messageItem.className = 'their-message';
                    messageItem.appendChild(img);
                    messageItem.appendChild(usernameSpan);
                    messageItem.appendChild(messageText);
                }



                chatMessages.appendChild(messageItem);

                scrollToBottom();

            });

            chatForm.addEventListener('submit', (e) => {
                e.preventDefault();
                if (chatInput.value) {
                    socket.emit('chatMessage', { text: chatInput.value, user: '<?php echo $_SESSION['username']; ?>', profile_picture: '<?php echo $_SESSION['profile_picture']; ?>' });
                    chatInput.value = '';
                }
            });


            document.getElementById('xand0-button').addEventListener('click', function () {
                var user = '<?php echo $_SESSION['username']; ?>';
                var messageSender = 'You sent an invitation to play Tic-Tac-Toe';


                var sentRequestMessage = document.createElement('li');
                sentRequestMessage.textContent = messageSender;
                sentRequestMessage.classList.add('my-message');


                chatMessages.appendChild(sentRequestMessage);
                scrollToBottom();


                socket.emit('gameRequest', { user: user, room_code: '<?php echo $room_code; ?>' });
            });

            socket.on('gameRequestReceived', (data) => {
                var sender = data.sender;
                var roomCode = data.room_code;

                var receivedRequestMessage = document.createElement('li');
                receivedRequestMessage.textContent = sender + ' sent an invitation to play tic tac toe. Press this to play with him!';
                receivedRequestMessage.classList.add('their-message');

                var acceptButton = document.createElement('button');
                acceptButton.textContent = 'Play Now';
                acceptButton.className = "xand0-accept";
                acceptButton.addEventListener('click', function () {
                    socket.emit('gameRequestAccepted', { room_code: roomCode, sender: sender, receiver: '<?php echo $_SESSION['username']; ?>' });
                    receivedRequestMessage.style.display = 'none';
                    console.log('button pressed' + 'sender: ' + sender);
                });
                receivedRequestMessage.appendChild(acceptButton);
                chatMessages.appendChild(receivedRequestMessage);
                scrollToBottom();
            });

            socket.on('gameStarted', function (data) {

                var roomCode = data.room_code;
                var sender = data.sender;
                var receiver = data.receiver;

                console.log('X AND 0 between ' + sender + ' and ' + receiver);

                var currentUser = '<?php echo $_SESSION['username']; ?>';
                console.log(currentUser);
                var isSender = (currentUser === sender);
                var isReceiver = (currentUser === receiver);
                var currentPlayer = isSender ? 'X' : 'O';
                if (currentPlayer === 'X') {
                    var xMessage = document.createElement('div');
                    xMessage.id = 'xMessage';
                    xMessage.textContent = 'You are playing as X. You have the first move';
                    chatMessages.appendChild(xMessage);
                    scrollToBottom();

                } else if (currentPlayer === 'O') {
                    var oMessage = document.createElement('div');
                    oMessage.id = 'oMessage';
                    oMessage.textContent = 'You are playing as O. Your opponent has the first move';
                    chatMessages.appendChild(oMessage);
                    scrollToBottom();

                }
                var currentPlayerTurn = 'X'

                if (isSender || isReceiver) {
                    var gameContainer = document.getElementById('gameContainer');
                    gameContainer.style.display = 'block';

                    var cells = document.getElementsByClassName('cell');

                    for (var i = 0; i < cells.length; i++) {
                        cells[i].addEventListener('click', function () {
                            if (!this.disabled && currentPlayerTurn == currentPlayer) {
                                var row = this.getAttribute('data-row');
                                var col = this.getAttribute('data-col');
                                //var playerSymbol = isSender ? 'X' : 'O';

                                this.textContent = currentPlayer;
                                this.disabled = true;


                                socket.emit('moveMade', { room_code: roomCode, row: row, col: col, symbol: currentPlayer, sender: sender, receiver: receiver });
                            }
                        });
                    }

                    socket.on('moveUpdated', function (data) {
                        var row = data.row;
                        var col = data.col;
                        var symbol = data.symbol;
                        console.log(sender + ' is sender and receiver is ' + receiver);

                        var cell = document.querySelector('[data-row="' + row + '"][data-col="' + col + '"]');
                        cell.textContent = symbol;
                        cell.classList.add(symbol === 'X' ? 'x-symbol' : 'o-symbol')
                        cell.disabled = true;
                        console.log('cell updated ' + cell);





                        if (xand0win(symbol)) {
                            currentPlayerTurn = null;
                            console.log('xand0win function called');
                            var winner = symbol === 'X' ? sender : receiver;

                            var winnerMessage = document.createElement('div');
                            winnerMessage.id = 'winnerMessage';
                            winnerMessage.textContent = 'Winner is ' + winner + '!! Congratulations. Choose what you want to do next';

                            var playAgainButtoon = document.createElement('button');
                            playAgainButtoon.textContent = 'Play Again';
                            playAgainButtoon.className = "xand0-accept";

                            var quitGameButton = document.createElement('button');
                            quitGameButton.textContent = 'Quit Game';
                            quitGameButton.className = "xand0-accept";

                            winnerMessage.appendChild(playAgainButtoon);
                            winnerMessage.appendChild(quitGameButton);
                            chatMessages.appendChild(winnerMessage);
                            scrollToBottom();


                            playAgainButtoon.addEventListener('click', function () {
                                socket.emit('playAgain', { room_code: roomCode });
                                currentPlayerTurn = 'X';
                            });


                            quitGameButton.addEventListener('click', function () {
                                socket.emit('quitGame', { room_code: roomCode });
                            })
                        } else {
                            var allCellsFilled = true;
                            var cells = document.getElementsByClassName('cell');
                            for (var i = 0; i < cells.length; i++) {
                                if (cells[i].textContent === '') {
                                    allCellsFilled = false;
                                    break
                                }
                            }

                            if (allCellsFilled) {
                                for (var i = 0; i < cells.length; i++) {
                                    cells[i].textContent = '';
                                    cells[i].disabled = false;
                                }
                            }
                            else {
                                console.log(currentPlayerTurn)
                                currentPlayerTurn = (currentPlayerTurn === 'X') ? 'O' : 'X';
                                console.log(currentPlayerTurn);
                            }
                        }

                    });
                }

            });

            socket.on('playAgainCalled', function () {
                var cells = document.getElementsByClassName('cell');
                var winnerMessage = document.getElementById('winnerMessage');
                winnerMessage.textContent = 'New game has started';
                for (var i = 0; i < cells.length; i++) {
                    cells[i].textContent = '';
                    cells[i].disabled = false;
                }

            });

            socket.on('quitGameCalled', function () {
               resetGame();
            });

            function xand0win(symbol) {
                const winningCombinations = [
                    // lines
                    [[0, 0], [0, 1], [0, 2]],
                    [[1, 0], [1, 1], [1, 2]],
                    [[2, 0], [2, 1], [2, 2]],
                    // columns
                    [[0, 0], [1, 0], [2, 0]],
                    [[0, 1], [1, 1], [2, 1]],
                    [[0, 2], [1, 2], [2, 2]],
                    // diagonals
                    [[0, 0], [1, 1], [2, 2]],
                    [[0, 2], [1, 1], [2, 0]]
                ];

                for (let combination of winningCombinations) {
                    const [a, b, c] = combination;
                    const cellA = document.querySelector('[data-row="' + a[0] + '"][data-col="' + a[1] + '"]');
                    const cellB = document.querySelector('[data-row="' + b[0] + '"][data-col="' + b[1] + '"]');
                    const cellC = document.querySelector('[data-row="' + c[0] + '"][data-col="' + c[1] + '"]');

                    if (cellA.textContent === symbol && cellB.textContent === symbol && cellC.textContent === symbol) {
                        return true;
                    }
                }

                return false;
            }

            function resetGame() {
                var cells = document.getElementsByClassName('cell');
                for (var i = 0; i < cells.length; i++) {
                    cells[i].textContent = '';
                    cells[i].disabled = false;
                    cells[i].style.backgroundCOlor = 'white';
                }

                
                var xMessage = document.getElementById('xMessage');
                var oMessage = document.getElementById('oMessage');
                var winnerMessage = document.getElementById('winnerMessage');

                var gameContainer = document.getElementById('gameContainer');
                gameContainer.style.display = 'none';
                
                if (xMessage) {
                    xMessage.parentNode.removeChild(xMessage);
                }
                if (oMessage) {
                    oMessage.parentNode.removeChild(oMessage);
                }
                if (winnerMessage) {
                    winnerMessage.parentNode.removeChild(winnerMessage);
                }

                currentPlayerTurn = 'X'; // Reset the current player's turnF
                if(currentPlayer === 'X') {
                    currentPlayer === '';
                } else if (currentPlayer === 'O' ) {
                    currentPlayer === '';
                }
            }
            
            const chatMessagesContainer = document.getElementById("chat-messages");

            function scrollToBottom() {
                chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
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
        }

        ////////////////////////////////////////////CARDS GAME

        function openCardWindow() {
            var height = window.screen.height;
            var popup = window.open("cardwar.html", "CardWarPopup", "width=600,height=" + height);
        }

    </script>
</body>

</html>