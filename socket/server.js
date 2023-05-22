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

  socket.on('responsesSaved', () => {
    io.emit('fetchCorrectResponses');
     
  });
});

server.listen(3000, () => {
  console.log('listening on *:3000');
});
