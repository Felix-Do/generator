<?php
namespace LaravelRocket\Generator\Commands;

use LaravelRocket\Generator\FileUpdaters\Services\RegisterServiceFileUpdater;
use LaravelRocket\Generator\Generators\Models\RepositoryGenerator;
use LaravelRocket\Generator\Generators\Models\RepositoryInterfaceGenerator;
use LaravelRocket\Generator\Services\DatabaseService;
use function ICanBoogie\singularize;

class ModelGenerator extends MWBGenerator
{
    protected $name = 'rocket:make:repository';

    protected $signature = 'rocket:make:repository {name} {--json=}';

    protected $description = 'Create Repository';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->tables = $this->getTablesFromMWBFile();
        if ($this->tables === false) {
            return false;
        }
        $this->getAppJson();

        $this->databaseService = new DatabaseService($this->config, $this->files);
        $databaseName          = $this->databaseService->resetDatabase();

        $this->databaseService->dropDatabase();

        return true;
    }

    protected function normalizeName(string $name): string
    {
        if (ends_with(strtolower($name), 'repository')) {
            $name = substr($name, 0, strlen($name) - 10);
        }

        return ucfirst(camel_case(singularize($name)));
    }

    protected function generateService()
    {
        /** @var \LaravelRocket\Generator\Generators\NameBaseGenerator[] $generators */
        $generators = [
            new RepositoryGenerator($this->config, $this->files, $this->view),
            new RepositoryInterfaceGenerator($this->config, $this->files, $this->view),
        ];

        /** @var \LaravelRocket\Generator\FileUpdaters\NameBaseFileUpdater[] $fileUpdaters */
        $fileUpdaters = [
            new RegisterServiceFileUpdater($this->config, $this->files, $this->view),
        ];

        $name = $this->normalizeName($this->argument('name'));

        $this->output('Processing '.$name.'Service...', 'green');
        foreach ($generators as $generator) {
            $generator->generate($name, $this->json);
        }
        foreach ($fileUpdaters as $fileUpdater) {
            $fileUpdater->insert($name);
        }
    }
}
