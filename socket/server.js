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

  //handle 'createRound'
  socket.on('roundCreated', (data) => {
    io.emit('roundCreated', data);
  });

  //handle "submit responses"
  socket.on('responsesSaved', () => {
    io.emit('fetchCorrectResponses');

  });

  //handle update the score 
  socket.on('responsesUpdated', () => {
    io.emit('fetchScore');
  });

  socket.on('startNextRound', () => {
    io.emit('startRoundCountdown', { countdownTime: 20 });
  });

  //////////////////////////////CHAT////////////////////////

  socket.on('newUser', (data) => {
    socket.user = data;
    socket.room_code = data.room_code;
    socket.join(data.room_code);
    socket.emit('chatMessage', { user: 'System', text: `Welcome ${data.user}!`});
    socket.broadcast.to(data.room_code).emit('chatMessage', { user: 'System', text: `${data.user} has joined the room.` });
  });

  socket.on('chatMessage', (data) => {
    io.to(socket.room_code).emit('chatMessage', data);
  });


});

server.listen(3000, () => {
  console.log('listening on *:3000');
});
