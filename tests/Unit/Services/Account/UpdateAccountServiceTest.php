<?php

use App\Models\Account;
use App\Models\User;
use App\Services\Account\UpdateAccountService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

test('update account service updates account name', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['name' => 'Old Name']);
    $service = new UpdateAccountService();

    $updated = $service->execute($user, $account, [
        'name' => 'New Name',
        'type' => 'checking',
    ]);

    expect($updated->name)->toBe('New Name');
    expect($updated->id)->toBe($account->id);
});

test('update account service updates account type', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['type' => 'checking']);
    $service = new UpdateAccountService();

    $updated = $service->execute($user, $account, [
        'name' => $account->name,
        'type' => 'saving',
    ]);

    expect($updated->type)->toBe('saving');
});

test('update account service persists changes to database', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['name' => 'Original']);
    $service = new UpdateAccountService();

    $service->execute($user, $account, [
        'name' => 'Updated',
        'type' => 'investment',
    ]);

    $retrieved = Account::find($account->id);
    expect($retrieved->name)->toBe('Updated');
    expect($retrieved->type)->toBe('investment');
});

test('update account service prevents unauthorized user from updating account', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $account = Account::factory()->for($user1)->create();
    $service = new UpdateAccountService();

    expect(fn () => $service->execute($user2, $account, [
        'name' => 'Hacked',
        'type' => 'checking',
    ]))->toThrow(AccessDeniedHttpException::class);
});

test('update account service does not modify balance on update', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create(['balance' => 500]);
    $service = new UpdateAccountService();

    $service->execute($user, $account, [
        'name' => 'New Name',
        'type' => 'saving',
    ]);

    expect($account->fresh()->balance)->toEqual(500);
});

test('update account service returns updated account instance', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create();
    $service = new UpdateAccountService();

    $result = $service->execute($user, $account, [
        'name' => 'Updated',
        'type' => 'checking',
    ]);

    expect($result)->toBeInstanceOf(Account::class);
    expect($result->name)->toBe('Updated');
    expect($result->user_id)->toBe($user->id);
});

test('update account service updates multiple fields at once', function () {
    $user = User::factory()->create();
    $account = Account::factory()->for($user)->create([
        'name' => 'Old',
        'type' => 'checking',
    ]);
    $service = new UpdateAccountService();

    $updated = $service->execute($user, $account, [
        'name' => 'New Account Name',
        'type' => 'investment',
    ]);

    expect($updated->name)->toBe('New Account Name');
    expect($updated->type)->toBe('investment');
});

test('update account service requires authorization check', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $account = Account::factory()->for($owner)->create();
    $service = new UpdateAccountService();

    $exception = null;
    try {
        $service->execute($other, $account, [
            'name' => 'Attacker',
            'type' => 'checking',
        ]);
    } catch (AccessDeniedHttpException $e) {
        $exception = $e;
    }

    expect($exception)->not->toBeNull();
    expect($account->fresh()->name)->not->toBe('Attacker');
});
