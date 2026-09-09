<?php

use App\Models\Webinar;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('webinar.chat.{webinarId}', function ($user, $webinarId) {
    $webinar = Webinar::find($webinarId);

    return $webinar && ($user->hasRole('super-admin') ||
        ($user->hasRole('sub-admin') && $user->assignedWebinars()->whereKey($webinarId)->exists()) ||
        ($user->hasRole('learner') && $webinar->chat_enabled && $webinar->registrations()->where('user_id', $user->id)->exists()));
});

Broadcast::channel('webinar.room.{webinarId}', function ($user, $webinarId) {
    $webinar = Webinar::find($webinarId);

    return $webinar && ($user->hasRole('super-admin') || ($user->hasRole('sub-admin') && $user->assignedWebinars()->whereKey($webinarId)->exists()) || ($user->hasRole('learner') && $webinar->registrations()->where('user_id', $user->id)->exists()));
});

Broadcast::channel('webinar.manage.{webinarId}', function ($user, $webinarId) {
    return $user->hasRole('super-admin') || ($user->hasRole('sub-admin') && $user->assignedWebinars()->whereKey($webinarId)->exists());
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
