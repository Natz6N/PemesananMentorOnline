<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

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

// Channel private untuk mentor
Broadcast::channel('mentor.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id && $user->role === 'mentor';
});

// Channel private untuk student
Broadcast::channel('student.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id && $user->role === 'student';
});

// Channel presence untuk admin
Broadcast::channel('presence-admin', function (User $user) {
    if ($user->role === 'admin') {
        return ['id' => $user->id, 'name' => $user->name];
    }
    return false;
});
