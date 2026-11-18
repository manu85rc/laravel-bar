<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal compartido por todo el personal (admin, super_admin y staff).
// Se usa para difundir en tiempo real los cambios de pedidos.
Broadcast::channel('staff', function ($user) {
    return $user->isStaff();
});
