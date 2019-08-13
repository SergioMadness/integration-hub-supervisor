<?php namespace professionalweb\IntegrationHub\Supervisor\Listeners;

use professionalweb\IntegrationHub\IntegrationHubCommon\Events\NewRequest;
use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Supervisor;
use professionalweb\IntegrationHub\IntegrationHubCommon\Events\EventToSupervisor;

class RequestReturnedListener
{
    /**
     * @var Supervisor
     */
    private $supervisor;

    public function __construct(Supervisor $supervisor)
    {
        $this->setSupervisor($supervisor);
    }

    /**
     * Update event status end send it further
     *
     * @param EventToSupervisor $event
     */
    public function handle(EventToSupervisor $event): void
    {
        event(
            new NewRequest(
                $this->getSupervisor()->processResponse(
                    $event->getProcessResponse()
                )
            )
        );
    }

    /**
     * @return Supervisor
     */
    public function getSupervisor(): Supervisor
    {
        return $this->supervisor;
    }

    /**
     * @param Supervisor $supervisor
     *
     * @return $this
     */
    public function setSupervisor(Supervisor $supervisor): self
    {
        $this->supervisor = $supervisor;

        return $this;
    }
}