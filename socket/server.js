const express = require('express');
const http = require('http');
const { Server } = require("socket.io");
const cors = require('cors');

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: 'http://localhost'
  }
});

app.use(cors());

io.on('connection', (socket) => {
  console.log('a user connected');

  socket.on('disconnect', () => {
    console.log('user disconnected');
  });

  // Handle 'startWaitingCountdown' event from the client-side
  socket.on('startWaitingCountdown', () => {
    // Emit 'startCountdown' event to the client-side
    socket.emit('startCountdown');
  });

  socket.on('startReviewingCountdown', () => {
    socket.emit('reviewingCountdown');
  })

  //handle 'createRound'
  socket.on('roundCreated', (data) => {
    io.emit('roundCreated', data);
  });

  //handle "submit responses"
  socket.on('responsesSaved', () => {
    console.log('received "responses saved"');
    io.emit('calculateScore');
  });

  socket.on('scoresCalculated', (scores) => {
    console.log('scores calculated');
    io.emit('fetchScore', scores);
  });

  //////////////////////////////CHAT////////////////////////

  socket.on('newUser', (data) => {
    socket.user = data;
    socket.room_code = data.room_code;
    socket.join(data.room_code);
  });

  socket.on('chatMessage', (data) => {
    io.to(socket.room_code).emit('chatMessage', data);
  });

  //////////////TIC TAC TOE////////////////
  var gameRequests = {};
  socket.on('gameRequest', (data) => {
    console.log('gameRequest');
    var user = data.user;
    var roomCode = data.room_code;

    gameRequests[roomCode] = { sender: user, receiver: null };
    console.log('room code=' + roomCode + ' ' + 'sender= ' + gameRequests[roomCode].sender);
    socket.to(roomCode).emit('gameRequestReceived', { sender: user, room_code: roomCode });
  });

  
  socket.on('gameRequestAccepted', function (data) {
    console.log('gameRequestAccepted');
    var roomCode = data.room_code;
    console.log('room code=' + roomCode + ' ' + gameRequests[roomCode]);

    var sender = gameRequests[roomCode] ? gameRequests[roomCode].sender : null;
    if(!sender) {
      sender = data.sender;
    }
    console.log('sender:', sender);
    var receiver = data.receiver;
    gameRequests[roomCode] = { sender: sender, receiver: receiver };
    console.log('room code=' + roomCode + ' sender = ' + gameRequests[roomCode].sender + ' receiver = ' + gameRequests[roomCode].receiver);
    
    socket.emit('gameStarted', { room_code: roomCode, sender: sender, receiver: receiver });
  socket.to(roomCode).emit('gameStarted', { room_code: roomCode, sender: sender, receiver: receiver });
  })


  socket.on('moveMade', function(data) {
    var roomCode = data.room_code;
    var row = data.row;
    var col = data.col;
    var symbol = data.symbol;


    console.log('roomCode= ' + roomCode + ' row / col = ' + row + col + ' symbol= ' + symbol);

    io.to(roomCode).emit('moveUpdated', {row: row, col: col, symbol: symbol});
  })

  socket.on('playAgain', function(data) {
    var roomCode = data.room_code;
    io.to(roomCode).emit('playAgainCalled');
  })

  socket.on('quitGame', function(data)  {
    var roomCode = data.room_code;

    gameRequests[roomCode] = { sender: null, receiver: null };

    io.to(roomCode).emit('quitGameCalled');
  })

});

server.listen(3000, () => {
  console.log('listening on *:3000');
});
