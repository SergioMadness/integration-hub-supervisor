<?php namespace professionalweb\IntegrationHub\Supervisor\Providers;

use professionalweb\IntegrationHub\IntegrationHubCommon\Events\NewRequest;
use professionalweb\IntegrationHub\Supervisor\Listeners\NewRequestListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        NewRequest::class => [
            NewRequestListener::class,
        ],
    ];
}
