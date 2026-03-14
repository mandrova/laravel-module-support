<?php

namespace VBKSolutions\LaravelModuleSupport\Console\Commands;

use Illuminate\Console\Command;
use VBKSolutions\LaravelModuleSupport\Services\ModuleManager;
use Throwable;

class ModuleDisableCommand extends Command
{
    protected $signature = 'module:disable {name}';

    protected $description = 'Disable a module';

    public function handle(ModuleManager $manager): int
    {
        $name = (string) $this->argument('name');

        try {
            $manager->disable($name);
            $this->info("Module [{$name}] disabled.");

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
