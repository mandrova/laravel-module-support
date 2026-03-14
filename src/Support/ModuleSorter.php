<?php

namespace VBKSolutions\LaravelModuleSupport\Support;

use VBKSolutions\LaravelModuleSupport\Data\ResolvedModule;
use RuntimeException;

class ModuleSorter
{
    /**
     * @param array<string, ResolvedModule> $modules
     * @return array<string, ResolvedModule>
     */
    public function sort(array $modules): array
    {
        $sorted = [];
        $visited = [];
        $visiting = [];

        $visit = function (ResolvedModule $module) use (&$visit, &$sorted, &$visited, &$visiting, $modules): void {
            $name = $module->getName();

            if (isset($visited[$name])) {
                return;
            }

            if (isset($visiting[$name])) {
                throw new RuntimeException("Circular dependency detected for module [{$name}].");
            }

            $visiting[$name] = true;

            foreach ($module->getDependencies() as $dependency) {
                if (! isset($modules[$dependency])) {
                    throw new RuntimeException(
                        "Missing dependency [{$dependency}] for module [{$name}]."
                    );
                }

                $visit($modules[$dependency]);
            }

            unset($visiting[$name]);
            $visited[$name] = true;
            $sorted[$name] = $module;
        };

        foreach ($modules as $module) {
            $visit($module);
        }

        return $sorted;
    }
}
