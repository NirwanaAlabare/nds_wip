import Echo from 'laravel-echo';
import socketio from 'socket.io-client';

// import { io } from "socket.io-client";

// // Jadikan Socket.io tersedia secara global
// window.io = io;

window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: window.location.hostname + ":6001",
    transports: ['websocket'],
});

