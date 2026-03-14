<?php

namespace VBKSolutions\LaravelModuleSupport\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class ModuleTestCommand extends Command
{
    protected $signature = 'module:test
                            {module : The module name}
                            {--filter= : Only run tests matching the given filter}
                            {--parallel : Run tests in parallel}
                            {--processes= : Number of parallel processes}
                            {--stop-on-failure : Stop on first failure}';

    protected $description = 'Run tests for a single module';

    public function handle(): int
    {
        $module = Str::studly((string) $this->argument('module'));
        $modulesPath = rtrim((string) config('modules.path', base_path('modules')), '/');
        $testsPath = "{$modulesPath}/{$module}/Tests";

        if (! File::isDirectory($testsPath)) {
            $this->error("Tests directory not found for module [{$module}].");
            $this->line("Expected: {$testsPath}");

            return self::FAILURE;
        }

        $command = $this->buildCommand($testsPath);

        $this->line("Running tests for module [{$module}]...");
        $this->newLine();

        $result = Process::path(base_path())
            ->forever()
            ->run($command, function (string $type, string $output) {
                $this->output->write($output);
            });

        $this->newLine();

        if ($result->successful()) {
            $this->info("Module [{$module}] tests passed.");

            return self::SUCCESS;
        }

        $this->error("Module [{$module}] tests failed.");

        return self::FAILURE;
    }

    protected function buildCommand(string $testsPath): string
    {
        $relativeTestsPath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $testsPath);

        $parts = [
            escapeshellarg(PHP_BINARY),
            'artisan',
            'test',
            escapeshellarg($relativeTestsPath),
        ];

        if ($filter = $this->option('filter')) {
            $parts[] = '--filter=' . escapeshellarg((string) $filter);
        }

        if ($this->option('parallel')) {
            $parts[] = '--parallel';
        }

        if ($processes = $this->option('processes')) {
            $parts[] = '--processes=' . (int) $processes;
        }

        if ($this->option('stop-on-failure')) {
            $parts[] = '--stop-on-failure';
        }

        return implode(' ', $parts);
    }
}
