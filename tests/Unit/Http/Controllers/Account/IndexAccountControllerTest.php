<?php

declare(strict_types=1);

use App\Http\Controllers\Account\IndexAccountController;
use App\Models\User;
use App\Services\Account\IndexAccountService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tests\TestCase;

test('controller returns success status in response', function () {
    $user = Mockery::mock(User::class);
    $accounts = new Collection([]);

    $service = Mockery::mock(IndexAccountService::class);
    $service->shouldReceive('execute')->with($user)->once()->andReturn($accounts);

    $controller = new IndexAccountController($service);
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('user')->andReturn($user);

    $response = $controller($request);

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->status())->toBe(200);

    $content = $response->getOriginalContent();
    expect($content['status'])->toBe('success');
    expect($content['data'])->not->toBeNull();
});

test('controller calls service execute method with authenticated user', function () {
    $user = Mockery::mock(User::class);
    $accounts = new Collection([]);

    $service = Mockery::mock(IndexAccountService::class);
    $service->shouldReceive('execute')
        ->with($user)
        ->once()
        ->andReturn($accounts);

    $controller = new IndexAccountController($service);
    $request = Mockery::mock(Request::class);
    $request->shouldReceive('user')->andReturn($user);

    $controller($request);

    $service->shouldHaveReceived('execute')->once();
    expect(true)->toBeTrue();
});

test('controller returns formatted collection data', function () {
    $user = Mockery::mock(User::class);

    $accountMock = (object) ['id' => 1, 'name' => 'Test Account', 'type' => 'savings', 'balance' => 100.00];
    $accounts = new Collection([$accountMock]);

    $service = Mockery::mock(IndexAccountService::class);
    $service->shouldReceive('execute')->andReturn($accounts);

    $controller = new IndexAccountController($service);
    $request = Mockery::mock(Request::class)->shouldReceive('user')->andReturn($user)->getMock();

    $response = $controller($request);
    $content = $response->getOriginalContent();

    expect($content['status'])->toBe('success');
    expect($content['data'])->not->toBeNull();
});

test('controller handles empty accounts collection', function () {
    $user = Mockery::mock(User::class);
    $accounts = new Collection([]);

    $service = Mockery::mock(IndexAccountService::class);
    $service->shouldReceive('execute')->andReturn($accounts);

    $controller = new IndexAccountController($service);
    $request = Mockery::mock(Request::class)->shouldReceive('user')->andReturn($user)->getMock();

    $response = $controller($request);
    $content = $response->getOriginalContent();

    expect($content['status'])->toBe('success');
    expect($content['data'])->not->toBeNull();
});

test('controller returns json response with proper headers', function () {
    $user = Mockery::mock(User::class);
    $accounts = new Collection([]);

    $service = Mockery::mock(IndexAccountService::class);
    $service->shouldReceive('execute')->andReturn($accounts);

    $controller = new IndexAccountController($service);
    $request = Mockery::mock(Request::class)->shouldReceive('user')->andReturn($user)->getMock();

    $response = $controller($request);

    expect($response->headers->get('Content-Type'))->toContain('application/json');
});
