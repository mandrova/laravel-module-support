<?php

namespace VBKSolutions\LaravelModuleSupport\Providers;

use Illuminate\Support\ServiceProvider;
use VBKSolutions\LaravelModuleSupport\Console\Commands\ModuleCreateCommand;
use VBKSolutions\LaravelModuleSupport\Console\Commands\ModuleDependsOnCommand;
use VBKSolutions\LaravelModuleSupport\Console\Commands\ModuleDisableCommand;
use VBKSolutions\LaravelModuleSupport\Console\Commands\ModuleEnableCommand;
use VBKSolutions\LaravelModuleSupport\Console\Commands\ModuleListCommand;
use VBKSolutions\LaravelModuleSupport\Console\Commands\ModuleStatusCommand;
use VBKSolutions\LaravelModuleSupport\Console\Commands\ModuleTestCommand;
use VBKSolutions\LaravelModuleSupport\Repositories\ModuleStateRepository;
use VBKSolutions\LaravelModuleSupport\Services\ModuleDependencyGuard;
use VBKSolutions\LaravelModuleSupport\Services\ModuleFinder;
use VBKSolutions\LaravelModuleSupport\Services\ModuleManager;
use VBKSolutions\LaravelModuleSupport\Services\ModuleRegistry;
use VBKSolutions\LaravelModuleSupport\Support\ModuleSorter;

class LaravelModuleSupportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/modules.php', 'modules');

        $this->app->singleton(ModuleFinder::class);
        $this->app->singleton(ModuleStateRepository::class);
        $this->app->singleton(ModuleDependencyGuard::class);
        $this->app->singleton(ModuleSorter::class);

        $this->app->singleton(ModuleRegistry::class, function ($app) {
            return new ModuleRegistry(
                $app->make(ModuleFinder::class),
                $app->make(ModuleStateRepository::class),
            );
        });

        $this->app->singleton(ModuleManager::class, function ($app) {
            return new ModuleManager(
                $app->make(ModuleRegistry::class),
                $app->make(ModuleStateRepository::class),
                $app->make(ModuleDependencyGuard::class),
                $app->make(ModuleSorter::class),
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/modules.php' => config_path('modules.php'),
        ], 'module-support-config');

        /** @var ModuleManager $manager */
        $manager = $this->app->make(ModuleManager::class);

        foreach ($manager->enabled() as $module) {
            foreach ($module->getProviders() as $provider) {
                if (class_exists($provider)) {
                    $this->app->register($provider);
                }
            }
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                ModuleEnableCommand::class,
                ModuleDisableCommand::class,
                ModuleListCommand::class,
                ModuleCreateCommand::class,
                ModuleTestCommand::class,
                ModuleStatusCommand::class,
                ModuleDependsOnCommand::class,
            ]);
        }
    }
}
