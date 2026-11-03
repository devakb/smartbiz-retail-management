<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

test('guests cannot access protected app routes', function (string $uri) {
    $this->get($uri)->assertRedirect(route('login'));
})->with([
    '/pos',
    '/products',
    '/inventory',
    '/orders',
    '/customers',
    '/users',
    '/settings',
    '/reports',
]);

test('authenticated users can logout from a csrf post', function () {
    $user = User::factory()->create([
        'session_version' => 1,
    ]);

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('users can authenticate with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'cashier@smartbiz.test',
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);

    $user->refresh();
    expect($user->session_version)->toBe(1)
        ->and(session('session_version'))->toBe(1);
});
