<?php

namespace VBKSolutions\LaravelModuleSupport\Console\Commands;

use Illuminate\Console\Command;
use VBKSolutions\LaravelModuleSupport\Services\ModuleManager;

class ModuleStatusCommand extends Command
{
    protected $signature = 'module:status {name}';

    protected $description = 'Show the status of a given module';

    public function handle(ModuleManager $manager): int
    {
        $name = (string) $this->argument('name');
        $module = $manager->find($name);

        if (! $module) {
            $this->error("Module [{$name}] not found.");

            return self::FAILURE;
        }

        $this->newLine();
        $this->table(['Module', 'Status'], [
            [$name, $module->isEnabled() ? 'Enabled' : 'Disabled'],
        ]);

        $dependencies = $module->getDependencies();
        if ($dependencies !== []) {
            $this->newLine();
            $this->table(['Dependencies'], array_map(fn (string $dependency) => [$dependency], $dependencies));
        }

        $providers = $module->getProviders();
        if ($providers !== []) {
            $this->newLine();
            $this->table(['Providers'], array_map(fn (string $provider) => [$provider], $providers));
        }

        return self::SUCCESS;
    }
}
