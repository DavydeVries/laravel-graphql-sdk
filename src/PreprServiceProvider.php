<?php

namespace Preprio;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Preprio\Commands\InstallCommand;

class PreprServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerPublishing();
        $this->registerMacros();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/prepr.php', 'prepr');

        $this->app->singleton(PreprClient::class, function (): PreprClient {
            return new PreprClient();
        });
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../config/prepr.php' => config_path('prepr.php'),
        ], 'prepr-config');

        $this->publishes([
            __DIR__ . '/../Queries/graphql.config.yml' => app_path('Queries/graphql.config.yml'),
        ], 'prepr-queries');
    }

    protected function registerMacros(): void
    {
        Http::macro('prepr', function (array $data) {
            $client = app(PreprClient::class);

            return $client->sendRequest($this, $data);
        });

        PendingRequest::macro('prepr', function (array $data) {
            $client = app(PreprClient::class);

            return $client->sendRequest($this, $data);
        });
    }
}
