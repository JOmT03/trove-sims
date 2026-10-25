<?php

use App\Models\User;

test('public registration is turned off', function () {
    $this->get('/register')->assertRedirect('/login');

    $this->post('/register', [
        'first_name' => 'Test', 'last_name' => 'User', 'email' => 'test@example.com',
        'password' => 'password', 'password_confirmation' => 'password',
    ])->assertRedirect('/login');

    expect(User::count())->toBe(0);
    $this->assertGuest();
});
