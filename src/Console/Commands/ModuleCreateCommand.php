<?php

namespace VBKSolutions\LaravelModuleSupport\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModuleCreateCommand extends Command
{
    protected $signature = 'module:create {name?}';

    protected $description = 'Create a new application module';

    private string $moduleDirectory;

    public function handle(): int
    {
        $name = (string) ($this->argument('name') ?: $this->ask('Enter the name of the module'));
        $name = Str::studly($name);

        if ($name === '') {
            $this->error('Module name cannot be empty.');

            return self::FAILURE;
        }

        $modulesPath = rtrim((string) config('modules.path', base_path('modules')), '/');
        $this->moduleDirectory = "{$modulesPath}/{$name}";

        if (File::exists($this->moduleDirectory)) {
            $this->error("Module {$name} already exists.");

            return self::FAILURE;
        }

        $version = (string) $this->ask('Enter the version of the module', '1.0.0');
        $description = (string) $this->ask('Enter the description of the module', '');

        $createApiRoutes = (bool) $this->confirm('Do you want to create API routes?', false);
        $createMigrations = (bool) $this->confirm('Do you want to create migrations?', false);
        $createTests = (bool) $this->confirm('Do you want to create tests?', false);
        $createTranslations = (bool) $this->confirm('Do you want to create translations?', false);
        $createProviders = (bool) $this->confirm('Do you want to create providers?', false);
        $createEvents = (bool) $this->confirm('Do you want to create events?', false);

        if ($createApiRoutes || $createMigrations || $createTranslations || $createEvents) {
            $createProviders = true;
        }

        File::ensureDirectoryExists($this->moduleDirectory);

        $this->buildModuleDefinitionFile($name, $version, $description, $createProviders, $createEvents);

        if ($createProviders) {
            $this->buildModuleServiceProviderFile(
                $name,
                $createApiRoutes,
                $createMigrations,
                $createTranslations,
                $createEvents,
            );
        }

        if ($createEvents) {
            $this->buildModuleEventServiceProviderFile($name);
        }

        if ($createTests) {
            File::ensureDirectoryExists($this->moduleDirectory . '/Tests/Feature');
            File::ensureDirectoryExists($this->moduleDirectory . '/Tests/Unit');
            $this->line("Created: {$this->moduleDirectory}/Tests");
        }

        if ($createTranslations) {
            File::ensureDirectoryExists($this->moduleDirectory . '/Resources/lang');
            $this->line("Created: {$this->moduleDirectory}/Resources/lang");
        }

        if ($createMigrations) {
            File::ensureDirectoryExists($this->moduleDirectory . '/Database/Migrations');
            File::ensureDirectoryExists($this->moduleDirectory . '/Database/Seeders');
            File::ensureDirectoryExists($this->moduleDirectory . '/Database/Factories');
            $this->line("Created: {$this->moduleDirectory}/Database/Migrations");
        }

        if ($createApiRoutes) {
            $content = $this->prepareRouteFile(Str::kebab($name));
            $this->writeFile($this->moduleDirectory . '/Routes/api.php', $content);
        }

        $this->info("Module {$name} created successfully.");

        return self::SUCCESS;
    }

    protected function buildModuleDefinitionFile(
        string $name,
        string $version,
        string $description,
        bool $hasProviders,
        bool $hasEvents,
    ): void {
        $imports = [
            'use VBKSolutions\\LaravelModuleSupport\\Data\\ModuleDefinition;',
        ];

        $providers = [];

        if ($hasProviders) {
            $imports[] = "use Modules\\{$name}\\Providers\\{$name}ServiceProvider;";
            $providers[] = "{$name}ServiceProvider::class";
        }

        if ($hasEvents) {
            $imports[] = "use Modules\\{$name}\\Providers\\EventServiceProvider;";
            $providers[] = 'EventServiceProvider::class';
        }

        $importsBlock = implode("\n", $imports);
        $providersBlock = $providers === []
            ? ''
            : "\n        " . implode(",\n        ", $providers) . ",\n    ";

        $content = $this->prepareModuleDefinitionFile(
            $name,
            $version,
            $description,
            $importsBlock,
            $providersBlock,
        );

        $this->writeFile($this->moduleDirectory . '/module.php', $content);
    }

    protected function prepareModuleDefinitionFile(
        string $name,
        string $version,
        string $description,
        string $importsBlock,
        string $providersBlock,
    ): string {
        $exportedName = var_export($name, true);
        $exportedVersion = var_export($version, true);
        $exportedDescription = var_export($description, true);

        return <<<PHP
<?php

namespace Modules\\{$name};

{$importsBlock}

return new ModuleDefinition(
    name: {$exportedName},
    version: {$exportedVersion},
    description: {$exportedDescription},
    author: 'Your Name',
    dependencies: [],
    providers: [{$providersBlock}],
);
PHP;
    }

    protected function buildModuleServiceProviderFile(
        string $name,
        bool $hasApiRoutes,
        bool $hasMigrations,
        bool $hasTranslations,
        bool $hasEvents,
    ): void {
        $imports = [];
        $registers = [];
        $loads = [];

        if ($hasApiRoutes) {
            $moduleSlug = Str::kebab($name);
            $imports[] = 'use Illuminate\\Support\\Facades\\Route;';
            $loads[] = "Route::middleware('api')->prefix('api/{$moduleSlug}')->group(__DIR__ . '/../Routes/api.php');";
        }

        if ($hasMigrations) {
            $loads[] = "\$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');";
        }

        if ($hasTranslations) {
            $loads[] = "\$this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', '{$name}');";
        }

        if ($hasEvents) {
            $imports[] = "use Modules\\{$name}\\Providers\\EventServiceProvider;";
            $registers[] = "\$this->app->register(EventServiceProvider::class);";
        }

        $importsBlock = implode("\n", $imports);
        $registersBlock = implode("\n        ", $registers);
        $loadsBlock = implode("\n        ", $loads);

        $content = $this->prepareModuleServiceProviderFile(
            $name,
            $loadsBlock,
            $importsBlock,
            $registersBlock,
        );

        $this->writeFile($this->moduleDirectory . "/Providers/{$name}ServiceProvider.php", $content);
    }

    protected function prepareModuleServiceProviderFile(
        string $name,
        string $loadsBlock,
        string $importsBlock,
        string $registersBlock,
    ): string {
        return <<<PHP
<?php

namespace Modules\\{$name}\\Providers;

use Illuminate\\Support\\ServiceProvider;
{$importsBlock}

class {$name}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        {$registersBlock}
    }

    public function boot(): void
    {
        {$loadsBlock}
    }
}
PHP;
    }

    protected function buildModuleEventServiceProviderFile(string $name): void
    {
        $content = $this->prepareModuleEventServiceProviderFile($name);
        $this->writeFile($this->moduleDirectory . '/Providers/EventServiceProvider.php', $content);
    }

    protected function prepareModuleEventServiceProviderFile(string $name): string
    {
        return <<<PHP
<?php

namespace Modules\\{$name}\\Providers;

use Illuminate\\Foundation\\Support\\Providers\\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }

    public function discoverEventsWithin(): array
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
}
PHP;
    }

    protected function prepareRouteFile(string $name): string
    {
        return <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;

Route::get('/', function () {
    return response()->json([
        'module' => '{$name}',
        'status' => 'ok',
    ]);
});
PHP;
    }

    protected function writeFile(string $path, string $content): void
    {
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        $this->line("Created: {$path}");
    }
}
