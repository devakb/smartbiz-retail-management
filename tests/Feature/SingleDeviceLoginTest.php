<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a second login invalidates the first session', function () {
    $user = User::factory()->create([
        'email' => 'device@smartbiz.test',
        'password' => 'password',
        'session_version' => 0,
    ]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $firstSessionVersion = (int) session('session_version');
    expect($firstSessionVersion)->toBe(1);
    $this->assertAuthenticatedAs($user);

    // End the first device session the same way a real logout would.
    $this->post(route('logout'))->assertRedirect(route('login'));
    $this->assertGuest();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $user->refresh();
    expect($user->session_version)->toBe(2)
        ->and((int) session('session_version'))->toBe(2);

    // Restore the first device's stale session version while still authenticated.
    $this->actingAs($user)->withSession([
        'session_version' => $firstSessionVersion,
    ]);

    $this->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', 'Signed in on another device.');

    $this->assertGuest();
});
