<?php

declare(strict_types=1);

namespace professionalweb\IntegrationHub\Supervisor\Listeners;

use professionalweb\IntegrationHub\IntegrationHubCommon\Events\NewRequest;
use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Supervisor;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\EventData;
use professionalweb\IntegrationHub\IntegrationHubCommon\Events\EventToSupervisor;

class RequestReturnedListener
{
    private Supervisor $supervisor;

    public function __construct(Supervisor $supervisor)
    {
        $this->setSupervisor($supervisor);
    }

    /**
     * Update event status end send it further
     */
    public function handle(EventToSupervisor $event): void
    {
        $request = $this->getSupervisor()->processResponse(
            $event->getProcessResponse()
        );
        if (in_array($request->getStatus(), [EventData::STATUS_NEW, EventData::STATUS_QUEUE], true)) {
            event(new NewRequest($request));
        }
    }

    public function getSupervisor(): Supervisor
    {
        return $this->supervisor;
    }

    /**
     * @return $this
     */
    public function setSupervisor(Supervisor $supervisor): self
    {
        $this->supervisor = $supervisor;

        return $this;
    }
}