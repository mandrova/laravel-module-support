<?php

namespace VBKSolutions\LaravelModuleSupport\Services;

use VBKSolutions\LaravelModuleSupport\Data\ResolvedModule;
use VBKSolutions\LaravelModuleSupport\Repositories\ModuleStateRepository;

class ModuleRegistry
{
    public function __construct(
        protected ModuleFinder $finder,
        protected ModuleStateRepository $states,
    ) {
    }

    /**
     * @return array<string, ResolvedModule>
     */
    public function all(): array
    {
        $definitions = $this->finder->all();
        $stateMap = $this->states->all();
        $modules = [];

        foreach ($definitions as $name => $definition) {
            $modules[$name] = new ResolvedModule(
                definition: $definition,
                enabled: (bool) ($stateMap[$name] ?? false),
            );
        }

        return $modules;
    }

    public function find(string $name): ?ResolvedModule
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * @return array<string, ResolvedModule>
     */
    public function enabled(): array
    {
        return array_filter(
            $this->all(),
            fn (ResolvedModule $module) => $module->isEnabled(),
        );
    }

    /**
     * @return array<string, ResolvedModule>
     */
    public function disabled(): array
    {
        return array_filter(
            $this->all(),
            fn (ResolvedModule $module) => ! $module->isEnabled(),
        );
    }
}
