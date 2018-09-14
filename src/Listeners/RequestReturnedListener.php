<?php namespace professionalweb\IntegrationHub\Supervisor\Listeners;

use professionalweb\IntegrationHub\IntegrationHubCommon\Events\NewRequest;
use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Supervisor;
use professionalweb\IntegrationHub\IntegrationHubCommon\Events\EventToSupervisor;

class RequestReturnedListener
{
    /**
     * Update event status end send it further
     *
     * @param EventToSupervisor $event
     * @param Supervisor        $supervisor
     */
    public function handler(EventToSupervisor $event, Supervisor $supervisor): void
    {
        event(new NewRequest($supervisor->processResponse($event->eventData, $event->processId)));
    }
}