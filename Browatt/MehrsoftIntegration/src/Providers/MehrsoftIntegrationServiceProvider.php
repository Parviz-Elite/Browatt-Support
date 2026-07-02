<?php

namespace Browatt\MehrsoftIntegration\Providers;

use Illuminate\Support\ServiceProvider;
use Browatt\MehrsoftIntegration\Client\SoapMehrsoftClient;
use Browatt\MehrsoftIntegration\Contracts\MehrsoftClient;

class MehrsoftIntegrationServiceProvider extends ServiceProvider
{
    protected string $name = 'MehrsoftIntegration';

    protected string $nameLower = 'mehrsoftintegration';

    /**
     * Register module services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            module_path($this->name, 'config/config.php'),
            $this->nameLower
        );

        $this->app->bind(MehrsoftClient::class, SoapMehrsoftClient::class);
    }

    /**
     * Bootstrap module services.
     */
    public function boot(): void
    {
        $this->publishes([
            module_path($this->name, 'config/config.php') => config_path('mehrsoftintegration.php'),
        ], 'config');
    }
}
