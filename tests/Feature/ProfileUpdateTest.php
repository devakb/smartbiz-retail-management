<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated users can update their name and email', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@smartbiz.test',
        'session_version' => 1,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->put(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'new@smartbiz.test',
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Profile updated successfully.',
        ]);

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('new@smartbiz.test');
});

test('profile page is available to authenticated users', function () {
    $user = User::factory()->create([
        'session_version' => 1,
    ]);

    $this->actingAs($user)
        ->withSession(['session_version' => 1])
        ->get(route('profile'))
        ->assertOk();
});
