<?php

namespace VBKSolutions\LaravelModuleSupport\Repositories;

use Illuminate\Support\Facades\File;

class ModuleStateRepository
{
    protected string $path;

    public function __construct()
    {
        $this->path = (string) config('modules.status_repo', base_path('bootstrap/cache/module-statuses.php'));
    }

    /**
     * @return array<string, bool>
     */
    public function all(): array
    {
        if (! File::exists($this->path)) {
            return [];
        }

        $states = require $this->path;

        return is_array($states) ? $states : [];
    }

    public function isEnabled(string $module): bool
    {
        return (bool) ($this->all()[$module] ?? false);
    }

    public function set(string $module, bool $enabled): void
    {
        $states = $this->all();
        $states[$module] = $enabled;

        $this->write($states);
    }

    /**
     * @param array<string, bool> $states
     */
    public function write(array $states): void
    {
        $content = '<?php return ' . var_export($states, true) . ';';

        File::ensureDirectoryExists(dirname($this->path));
        File::put($this->path, $content);
    }
}
