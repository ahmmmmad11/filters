<?php

namespace Ahmmmmad11\Filters\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;
use ReflectionMethod;
use SplFileObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\confirm;

#[AsCommand(name: 'filter:make')]
class MakeFilter extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'filter:make';

    protected string $model;

    protected array $relationMethods = [
        'hasMany',
        'hasManyThrough',
        'hasOneThrough',
        'belongsToMany',
        'hasOne',
        'belongsTo',
        'morphOne',
        'morphTo',
        'morphMany',
        'morphToMany',
        'morphedByMany',
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create New Filter Class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Filter';

    protected function getModel(): string
    {
        if ($this->option('model')) {
            return trim($this->option('model'));
        }

        $split_name = Str::ucsplit($this->argument('name'));

        return Str::singular($split_name[0]);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.config('filters.path');
    }

    protected function getStub(): string
    {
        $stub = null;

        if ($this->getModel()) {
            $stub = '/Stubs/filter.plain.stub';
        }

        if ($this->option('relations')) {
            $stub = '/Stubs/filter.relations.stub';
        }

        return $this->resolveStubPath($stub);
    }

    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    protected function buildClass($name): array|string
    {
        $controllerNamespace = $this->getNamespace($name);

        $replace = [];

        if ($this->getModel()) {
            $replace = $this->buildModelReplacements($replace);
        }

        if (! $this->option('ignore-fields')) {
            $replace = $this->buildFieldsReplacements($replace);
        }

        if ($this->option('relations')) {
            $replace = $this->buildRelationsReplacements($replace);
        }

        $replace["use $controllerNamespace\Filter;\n"] = '';

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Build the model replacement values.
     */
    protected function buildModelReplacements(array $replace): array
    {
        $modelClass = $this->parseModel($this->getModel());

        if (! class_exists($modelClass) && confirm("A $modelClass model does not exist. Do you want to generate it?", default: true)) {
            $this->call('make:model', ['name' => $modelClass]);
        }

        $this->model = $modelClass;

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            '{{ namespacedModel }}' => $modelClass,
            '{{namespacedModel}}' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            '{{ model }}' => class_basename($modelClass).'::class',
            '{{model}}' => class_basename($modelClass).'::class',
        ]);
    }

    protected function buildFieldsReplacements($replace): array
    {
        //get model table and then get all table fields
        $all_fields = Schema::getColumnListing((new $this->model)->getTable());

        //exclude hidden fields
        $fields = array_diff($all_fields, (new $this->model)->getHidden());

        return array_merge($replace, [
            '{{ fields }}' => collect(array_values($fields)),
        ]);
    }

    protected function buildRelationsReplacements($replace): array
    {
        try {
            return array_merge($replace, [
                '{{ relations }}' => $this->getRelations($this->laravel->make($this->model)),
            ]);
        } catch (BindingResolutionException) {
            return [];
        }
    }

    /**
     * Get the fully-qualified model class name.
     */
    protected function parseModel(string $model): string
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }

    /**
     * Get the relations from the given model.
     */
    protected function getRelations(Model $model): Collection
    {
        return collect(get_class_methods($model))
            ->map(fn ($method) => new ReflectionMethod($model, $method))
            ->reject(
                fn (ReflectionMethod $method) => $method->isStatic()
                    || $method->isAbstract()
                    || $method->getDeclaringClass()->getName() === Model::class
            )
            ->filter(function (ReflectionMethod $method) {
                $file = new SplFileObject($method->getFileName());
                $file->seek($method->getStartLine() - 1);
                $code = '';
                while ($file->key() < $method->getEndLine()) {
                    $code .= trim($file->current());
                    $file->next();
                }

                return collect($this->relationMethods)
                    ->contains(fn ($relationMethod) => str_contains($code, '$this->'.$relationMethod.'('));
            })
            ->map(function (ReflectionMethod $method) use ($model) {
                $relation = $method->invoke($model);

                if (! $relation instanceof Relation) {
                    return null;
                }

                return [
                    'name' => $method->getName(),
                ];
            })
            ->filter()
            ->values()
            ->pluck('name');
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'specify model for the filter'],
            ['relations', 'r', InputOption::VALUE_NONE, 'include model relations'],
            ['ignore-fields', 'i', InputOption::VALUE_OPTIONAL, 'exclude fields fields to be filtered by'],
        ];
    }
}
