<?php

declare(strict_types=1);

namespace professionalweb\IntegrationHub\Supervisor\Listeners;

use professionalweb\IntegrationHub\IntegrationHubCommon\Events\NewRequest;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services\RequestProcessor;

class NewRequestListener
{
    private RequestProcessor $requestProcessor;

    public function __construct(RequestProcessor $requestProcessor)
    {
        $this->setRequestProcessor($requestProcessor);
    }

    public function handle(NewRequest $event): void
    {
        $this->getRequestProcessor()->event($event->request);
    }

    public function getRequestProcessor(): RequestProcessor
    {
        return $this->requestProcessor;
    }

    /**
     * @return $this
     */
    public function setRequestProcessor(RequestProcessor $requestProcessor): self
    {
        $this->requestProcessor = $requestProcessor;

        return $this;
    }
}