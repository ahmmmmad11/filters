<?php

use Ahmmmmad11\Filters\Tests\Fixtures\Models\User as FixtureUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

function generatedFiltersPath(?string $file = null): string
{
    $path = app_path('Http/Filters');

    return $file ? $path.DIRECTORY_SEPARATOR.$file : $path;
}

beforeEach(function () {
    Schema::dropIfExists('posts');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->nullable();
        $table->string('password')->nullable();
    });

    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable();
        $table->string('title');
    });

    if (! class_exists('Workbench\\App\\Models\\User', false)) {
        class_alias(FixtureUser::class, 'Workbench\\App\\Models\\User');
    }

    if (! class_exists('App\\Models\\User', false)) {
        class_alias(FixtureUser::class, 'App\\Models\\User');
    }

    File::deleteDirectory(generatedFiltersPath());
});

afterEach(function () {
    File::deleteDirectory(generatedFiltersPath());
});

it('generates a filter and infers model from filter name', function () {
    expect(Artisan::call('filter:make', ['name' => 'UsersFilter']))->toBe(0);

    $generatedPath = generatedFiltersPath('UsersFilter.php');

    expect(File::exists($generatedPath))->toBeTrue();

    $content = File::get($generatedPath);

    expect($content)->toContain('Models\\User;')
        ->and($content)->toContain('allowedFilters(')
        ->and($content)->toContain('name')
        ->and($content)->not->toContain('password');
});

it('includes relations when relations option is used', function () {
    expect(Artisan::call('filter:make', [
        'name' => 'UsersWithRelationsFilter',
        '--model' => 'User',
        '--relations' => true,
    ]))->toBe(0);

    $generatedPath = generatedFiltersPath('UsersWithRelationsFilter.php');

    expect(File::exists($generatedPath))->toBeTrue();

    $content = File::get($generatedPath);

    expect($content)->toContain('->allowedIncludes(');
});

it('fails for invalid model names', closure: function () {
    Artisan::call('filter:make', [
        'name' => 'BrokenFilter',
        '--model' => 'User$',
    ]);
})->throws(InvalidArgumentException::class, 'Model name contains invalid characters.');
