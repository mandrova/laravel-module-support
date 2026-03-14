<?php

namespace VBKSolutions\LaravelModuleSupport\Data;

final readonly class ResolvedModule
{
    public function __construct(
        private ModuleDefinition $definition,
        private bool $enabled,
    ) {
    }

    public function definition(): ModuleDefinition
    {
        return $this->definition;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getName(): string
    {
        return $this->definition->getName();
    }

    /**
     * @return array<int, string>
     */
    public function getDependencies(): array
    {
        return $this->definition->getDependencies();
    }

    /**
     * @return array<int, string>
     */
    public function getProviders(): array
    {
        return $this->definition->getProviders();
    }
}
