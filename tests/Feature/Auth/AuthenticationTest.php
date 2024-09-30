<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});



test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});


test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});
