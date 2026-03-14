<?php

namespace VBKSolutions\LaravelModuleSupport\Services;

use VBKSolutions\LaravelModuleSupport\Data\ResolvedModule;
use VBKSolutions\LaravelModuleSupport\Repositories\ModuleStateRepository;
use VBKSolutions\LaravelModuleSupport\Support\ModuleSorter;
use RuntimeException;

class ModuleManager
{
    public function __construct(
        protected ModuleRegistry $registry,
        protected ModuleStateRepository $states,
        protected ModuleDependencyGuard $guard,
        protected ModuleSorter $sorter,
    ) {
    }

    /**
     * @return array<string, ResolvedModule>
     */
    public function all(): array
    {
        return $this->registry->all();
    }

    /**
     * @return array<string, ResolvedModule>
     */
    public function enabled(): array
    {
        return $this->sorter->sort($this->registry->enabled());
    }

    /**
     * @return array<string, ResolvedModule>
     */
    public function disabled(): array
    {
        return $this->sorter->sort($this->registry->disabled());
    }

    public function find(string $moduleName): ?ResolvedModule
    {
        return $this->registry->find($moduleName);
    }

    public function enable(string $moduleName): void
    {
        if (! $this->registry->find($moduleName)) {
            throw new RuntimeException("Module [{$moduleName}] not found.");
        }

        $this->guard->assertCanEnable($moduleName, $this->registry);
        $this->states->set($moduleName, true);
    }

    public function disable(string $moduleName): void
    {
        if (! $this->registry->find($moduleName)) {
            throw new RuntimeException("Module [{$moduleName}] not found.");
        }

        $this->guard->assertCanDisable($moduleName, $this->registry);
        $this->states->set($moduleName, false);
    }
}
