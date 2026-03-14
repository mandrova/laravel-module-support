<?php

namespace VBKSolutions\LaravelModuleSupport\Services;

use VBKSolutions\LaravelModuleSupport\Data\ResolvedModule;
use RuntimeException;

class ModuleDependencyGuard
{
    public function assertCanEnable(string $moduleName, ModuleRegistry $registry): void
    {
        $module = $registry->find($moduleName);

        if (! $module instanceof ResolvedModule) {
            throw new RuntimeException("Module [{$moduleName}] not found.");
        }

        foreach ($module->getDependencies() as $dependency) {
            $resolvedDependency = $registry->find($dependency);

            if (! $resolvedDependency instanceof ResolvedModule) {
                throw new RuntimeException(
                    "Module [{$moduleName}] requires missing dependency [{$dependency}]."
                );
            }

            if (! $resolvedDependency->isEnabled()) {
                throw new RuntimeException(
                    "Module [{$moduleName}] requires enabled dependency [{$dependency}]."
                );
            }
        }
    }

    public function assertCanDisable(string $moduleName, ModuleRegistry $registry): void
    {
        foreach ($registry->enabled() as $module) {
            if (in_array($moduleName, $module->getDependencies(), true)) {
                throw new RuntimeException(
                    "Cannot disable [{$moduleName}] because enabled module [{$module->getName()}] depends on it."
                );
            }
        }
    }
}
