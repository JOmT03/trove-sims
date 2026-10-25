<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/profile')->assertOk()->assertSee($user->first_name);
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/profile', [
        'first_name' => 'Rio',
        'last_name' => 'Esparagoza',
        'mobile_no' => '09171234567',
        'email' => 'test@example.com',
    ])->assertSessionHasNoErrors()->assertRedirect('/profile');

    $user->refresh();
    expect($user->first_name)->toBe('Rio')
        ->and($user->last_name)->toBe('Esparagoza')
        ->and($user->mobile_no)->toBe('09171234567')
        ->and($user->email)->toBe('test@example.com');
});

test('users cannot delete their own account; accounts are deactivated instead', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->delete('/profile', ['password' => 'password'])->assertForbidden();

    expect(User::find($user->id))->not->toBeNull();
});
