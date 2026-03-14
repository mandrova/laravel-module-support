<?php

namespace VBKSolutions\LaravelModuleSupport\Console\Commands;

use Illuminate\Console\Command;
use VBKSolutions\LaravelModuleSupport\Services\ModuleManager;

class ModuleListCommand extends Command
{
    protected $signature = 'module:list
                            {--enabled : Only show enabled modules}
                            {--disabled : Only show disabled modules}
                            {--w-deps : Include dependencies in the table}
                            {--w-providers : Include providers in the table}';

    protected $description = 'List application modules';

    public function handle(ModuleManager $manager): int
    {
        if ($this->option('enabled') && $this->option('disabled')) {
            $this->error('Use either --enabled or --disabled, not both.');

            return self::FAILURE;
        }

        $tableHeaders = ['Module', 'State', 'Description'];

        if ($this->option('w-deps')) {
            $tableHeaders[] = 'Dependencies';
        }

        if ($this->option('w-providers')) {
            $tableHeaders[] = 'Providers';
        }

        if ($this->option('enabled')) {
            $this->newLine();
            $this->line('Enabled Modules:');
            $this->table($tableHeaders, $this->buildTable($manager->enabled()));

            return self::SUCCESS;
        }

        if ($this->option('disabled')) {
            $this->newLine();
            $this->line('Disabled Modules:');
            $this->table($tableHeaders, $this->buildTable($manager->disabled()));

            return self::SUCCESS;
        }

        $this->newLine();
        $this->line('Enabled Modules:');
        $this->table($tableHeaders, $this->buildTable($manager->enabled()));
        $this->newLine();
        $this->line('Disabled Modules:');
        $this->table($tableHeaders, $this->buildTable($manager->disabled()));

        return self::SUCCESS;
    }

    /**
     * @param array<int|string, mixed> $modules
     * @return array<int, array<int, string>>
     */
    protected function buildTable(array $modules): array
    {
        $rows = [];

        foreach ($modules as $module) {
            $row = [
                $module->getName(),
                $module->isEnabled() ? 'enabled' : 'disabled',
                $module->definition()->getDescription() ?: '-',
            ];

            if ($this->option('w-deps')) {
                $row[] = implode(', ', $module->getDependencies()) ?: '-';
            }

            if ($this->option('w-providers')) {
                $row[] = implode(', ', $module->getProviders()) ?: '-';
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
