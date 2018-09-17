<?php namespace professionalweb\IntegrationHub\Supervisor\Providers;

use Illuminate\Support\ServiceProvider;
use professionalweb\IntegrationHub\Supervisor\Service\Supervisor;
use professionalweb\IntegrationHub\Supervisor\Service\Dispatcher;
use professionalweb\IntegrationHub\Supervisor\Service\EventProcessor;
use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Supervisor as ISupervisor;
use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Dispatcher as IDispatcher;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services\RequestProcessor;

class SupervisorProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
    }

    public function boot(): void
    {
        $this->app->singleton(ISupervisor::class, Supervisor::class);
        $this->app->singleton(IDispatcher::class, Dispatcher::class);
        $this->app->singleton(RequestProcessor::class, EventProcessor::class);
    }
}