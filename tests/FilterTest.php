<?php

use Ahmmmmad11\Filters\Tests\Fixtures\Filters\UsersFilterForTest;
use Ahmmmmad11\Filters\Tests\Fixtures\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('posts');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('status');
        $table->string('password')->nullable();
    });

    User::query()->insert([
        ['name' => 'Alice', 'status' => 'active', 'password' => 'secret'],
        ['name' => 'Bob', 'status' => 'inactive', 'password' => 'secret'],
    ]);

    config()->set('filters.per_page', 2);
    request()->query->remove('per_page');
});

it('loads the query lazily once', function () {
    $filter = new UsersFilterForTest;

    $filter->get();
    $filter->get();

    expect($filter->filterCalls)->toBe(1);
});

it('executes callback customizations before fetching records', function () {
    $filter = new UsersFilterForTest;

    $result = $filter->execute(fn ($query) => $query->where('status', 'active'))->get();
    $firstUser = $result->first();
    expect($firstUser)->not->toBeNull();
    /** @var User $firstUser */
    expect($result)->toHaveCount(1);
    expect($firstUser->getAttribute('name'))->toBe('Alice');
});

it('forwards eloquent methods and stays chainable', function () {
    $filter = new UsersFilterForTest;

    $result = $filter->where('name', 'Alice')->get();
    $firstUser = $result->first();
    expect($firstUser)->not->toBeNull();
    /** @var User $firstUser */
    expect($result)->toHaveCount(1);
    expect($firstUser->getAttribute('status'))->toBe('active');
});

it('uses request per_page when paginate rows are not passed', function () {
    request()->query->set('per_page', 1);

    $paginator = (new UsersFilterForTest)->paginate();

    expect($paginator)->not->toBeNull()
        ->and($paginator->perPage())->toBe(1);
});

it('throws for unknown forwarded methods', function () {
    (new UsersFilterForTest)->methodThatDoesNotExist();
})->throws(BadMethodCallException::class);

it('does not fallback to filters.per_page when per_page request is missing', function () {
    $paginator = (new UsersFilterForTest)->paginate();

    expect($paginator)->not->toBeNull()
        ->and($paginator->perPage())->not->toBe(config('filters.per_page'));
});
