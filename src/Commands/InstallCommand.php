<?php

namespace Preprio\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'prepr:install';

    protected $description = 'Install all of the Prepr GraphQL SDK resources';

    public function handle(): int
    {
        $this->comment('Publishing Prepr configuration...');

        $this->callSilent('vendor:publish', [
            '--tag' => 'prepr-config',
            '--force' => true,
        ]);

        $target = app_path('Queries/graphql.config.yml');

        if (!is_file($target)) {
            $this->comment('Publishing Prepr GraphQL config...');
            $this->callSilent('vendor:publish', [
                '--tag' => 'prepr-queries',
            ]);
            $this->line('Created: app/Queries/graphql.config.yml');
        }

        $this->info('Prepr GraphQL SDK installed successfully.');
        $this->newLine();
        $this->line('Set these environment variables in your .env:');
        $this->line('- PREPR_ENDPOINT=https://graphql.prepr.io/<your-access-token>');
        $this->line('- PREPR_TIMEOUT=30');
        $this->line('- PREPR_CONNECT_TIMEOUT=10');

        return self::SUCCESS;
    }
}
