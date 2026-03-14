<?php

namespace VBKSolutions\LaravelModuleSupport\Console\Commands;

use Illuminate\Console\Command;
use VBKSolutions\LaravelModuleSupport\Services\ModuleManager;
use Throwable;

class ModuleEnableCommand extends Command
{
    protected $signature = 'module:enable {name}';

    protected $description = 'Enable a module';

    public function handle(ModuleManager $manager): int
    {
        $name = (string) $this->argument('name');

        try {
            $manager->enable($name);
            $this->info("Module [{$name}] enabled.");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
