import Echo from 'laravel-echo';
import socketio from 'socket.io-client';

window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: window.location.hostname + ":6001",
    transports: ['websocket'],
});

