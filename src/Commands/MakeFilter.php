<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;
use SplFileObject;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use function Laravel\Prompts\confirm;

class MakeFilter extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filter:make
                            {name : The name of the worker}
                            {--model=}
                            {--relations}
                            {--ignore-fields=}
    ';

    protected $model;

    protected $relationMethods = [
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
        return trim($this->option('model'));
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Http\Filters';
    }

    protected function filterStubFile(): string
    {
        return __DIR__.'/Stubs/filter.stub';
    }

    protected function getStub(): string
    {
        $stub = null;

        if ($this->option('model')) {
            $stub = '/Stubs/filter.plain.stub';
        }

        if ($this->option('relations')) {
            $stub = '/Stubs/filter.relations.stub';
        }

        return $this->resolveStubPath($stub);
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
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

        if ($this->option('model')) {
            $replace = $this->buildModelReplacements($replace);
        }

        if (!$this->option('ignore-fields')) {
            $replace = $this->buildFieldsReplacements($replace);
        }

        if ($this->option('relations')) {
            $replace = $this->buildRelationsReplacements($replace);
        }

        $replace["use {$controllerNamespace}\Filter;\n"] = '';

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace): array
    {
        $modelClass = $this->parseModel($this->option('model'));

        if (! class_exists($modelClass) && confirm("A {$modelClass} model does not exist. Do you want to generate it?", default: true)) {
            $this->call('make:model', ['name' => $modelClass]);
        }

        $this->model = $modelClass;

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            '{{ namespacedModel }}' => $modelClass,
            '{{namespacedModel}}' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            '{{ model }}' => class_basename($modelClass) .'::class' ,
            '{{model}}' => class_basename($modelClass) . '::class',
        ]);
    }

    protected function buildFieldsReplacements($replace): array
    {
        //get model table and then get all table fields
        $all_fields = Schema::getColumnListing((new $this->model)->getTable());

        //exclude hidden fields
        $fields = array_diff($all_fields, (new $this->model)->getHidden());


        return array_merge($replace, [
            '{{ fields }}' =>  collect(array_values($fields)),
        ]);
    }

    protected function buildRelationsReplacements($replace): array
    {
        return array_merge($replace, [
            '{{ relations }}' =>  $this->getRelations($this->laravel->make($this->model)),
        ]);
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseModel($model): string
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model'],
        ];
    }

    /**
     * Get the relations from the given model.
     */
    protected function getRelations(Model $model): \Illuminate\Support\Collection
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

}