<?php

use App\Ai\Agents\ChatAgent;
use App\Ai\Tools\Sum;
use Laravel\Ai\Tools\Request;

it('sums two numbers', function (float|int $a, float|int $b, string $sum): void {
    expect((string) (new Sum)->handle(new Request(['a' => $a, 'b' => $b])))->toBe($sum);
})->with([
    'integers' => [2, 3, '5'],
    'floats' => [1.5, 2.25, '3.75'],
    'negative numbers' => [-4, 1, '-3'],
]);

it('is available to the chat agent', function (): void {
    expect(collect((new ChatAgent)->tools())->whereInstanceOf(Sum::class))->toHaveCount(1);
});
