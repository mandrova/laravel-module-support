<?php

namespace VBKSolutions\LaravelModuleSupport\Console\Commands;

use Illuminate\Console\Command;
use VBKSolutions\LaravelModuleSupport\Services\ModuleManager;

class ModuleDependsOnCommand extends Command
{
    protected $signature = 'module:depends-on {name}';

    protected $description = 'List modules that depend on the given module';

    public function handle(ModuleManager $manager): int
    {
        $name = (string) $this->argument('name');

        if (! $manager->find($name)) {
            $this->error("Module [{$name}] does not exist.");

            return self::FAILURE;
        }

        $rows = [];

        foreach ($manager->all() as $module) {
            if (in_array($name, $module->getDependencies(), true)) {
                $rows[] = [
                    $module->getName(),
                    $module->isEnabled() ? 'enabled' : 'disabled',
                ];
            }
        }

        if ($rows === []) {
            $this->error("No modules depend on [{$name}].");

            return self::FAILURE;
        }

        $this->newLine();
        $this->table(['Module', 'State'], $rows);

        return self::SUCCESS;
    }
}
