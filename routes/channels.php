<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Dunia 3D Work Tracker: siapa yang online, posisi dan chat lewat whisper antar browser
Broadcast::channel('work-tracker-world', function ($user) {
    if ($user->roles->whereIn('nama_role', ['superadmin'])->count() === 0) {
        return false;
    }

    return ['id' => $user->id, 'name' => $user->name];
});
