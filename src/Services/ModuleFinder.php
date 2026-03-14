<?php

namespace VBKSolutions\LaravelModuleSupport\Services;

use Illuminate\Support\Facades\File;
use VBKSolutions\LaravelModuleSupport\Data\ModuleDefinition;
use RuntimeException;

class ModuleFinder
{
    /**
     * @return array<string, ModuleDefinition>
     */
    public function all(): array
    {
        $modulesPath = (string) config('modules.path', base_path('modules'));

        if (! File::exists($modulesPath)) {
            return [];
        }

        $definitions = [];

        foreach (File::directories($modulesPath) as $modulePath) {
            $moduleConfig = $modulePath . '/module.php';

            if (! File::exists($moduleConfig)) {
                continue;
            }

            $definition = require $moduleConfig;

            if (! $definition instanceof ModuleDefinition) {
                throw new RuntimeException('Module config file must return an instance of ModuleDefinition.');
            }

            $definitions[$definition->getName()] = $definition;
        }

        return $definitions;
    }
}
