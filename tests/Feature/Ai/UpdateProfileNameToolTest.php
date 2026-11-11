<?php

use App\Ai\Agents\ChatAgent;
use App\Ai\Tools\UpdateProfileName;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Laravel\Ai\Tools\Request;

use function Pest\Laravel\actingAs;

it('changes the name on the profile of the current user', function (): void {
    $user = User::factory()->create(['name' => 'Ada Lovelace']);
    $otherUser = User::factory()->create(['name' => 'Charles Babbage']);

    actingAs($user);

    $result = (new UpdateProfileName)->handle(new Request(['name' => 'Ada King']));

    expect((string) $result)->toBe('The profile name is now Ada King.')
        ->and($user->refresh()->name)->toBe('Ada King')
        ->and($otherUser->refresh()->name)->toBe('Charles Babbage');
});

it('rejects a name the profile cannot hold', function (string $name): void {
    $user = User::factory()->create(['name' => 'Ada Lovelace']);

    actingAs($user);

    expect(fn () => (new UpdateProfileName)->handle(new Request(['name' => $name])))
        ->toThrow(ValidationException::class);

    expect($user->refresh()->name)->toBe('Ada Lovelace');
})->with([
    'empty' => [''],
    'longer than 255 characters' => [str_repeat('a', 256)],
]);

it('needs approval before it runs', function (): void {
    $approval = (new UpdateProfileName)->shouldRequestApproval(new Request(['name' => 'Ada King']));

    expect($approval?->reason)->toBe('This will change the name on your profile.');
});

it('is available to the chat agent', function (): void {
    expect(collect((new ChatAgent)->tools())->whereInstanceOf(UpdateProfileName::class))->toHaveCount(1);
});
