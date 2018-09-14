<?php namespace professionalweb\IntegrationHub\Supervisor\Providers;

use professionalweb\IntegrationHub\Supervisor\Listeners\NewRequestListener;
use professionalweb\IntegrationHub\Supervisor\Listeners\RequestReturnedListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Events\NewRequest;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Events\EventToSupervisor;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        NewRequest::class        => [
            NewRequestListener::class,
        ],
        EventToSupervisor::class => [
            RequestReturnedListener::class,
        ],
    ];
}
