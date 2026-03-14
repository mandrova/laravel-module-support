<?php

namespace VBKSolutions\LaravelModuleSupport\Data;

use InvalidArgumentException;

final readonly class ModuleDefinition
{
    /**
     * @param array<int, string> $dependencies
     * @param array<int, string> $providers
     */
    public function __construct(
        private string $name,
        private string $version,
        private string $description,
        private string $author,
        private array $dependencies = [],
        private array $providers = [],
    ) {
        if ($this->name === '') {
            throw new InvalidArgumentException('Module name cannot be empty.');
        }

        if ($this->version === '') {
            throw new InvalidArgumentException('Module version cannot be empty.');
        }

        foreach ($this->dependencies as $dependency) {
            if (! is_string($dependency) || $dependency === '') {
                throw new InvalidArgumentException('Module dependencies must contain non-empty strings.');
            }
        }

        foreach ($this->providers as $provider) {
            if (! is_string($provider) || $provider === '') {
                throw new InvalidArgumentException('Module providers must contain non-empty strings.');
            }
        }

        if (in_array($this->name, $this->dependencies, true)) {
            throw new InvalidArgumentException("Module [{$this->name}] cannot depend on itself.");
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @return array<int, string>
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * @return array<int, string>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'description' => $this->description,
            'author' => $this->author,
            'dependencies' => $this->dependencies,
            'providers' => $this->providers,
        ];
    }
}
