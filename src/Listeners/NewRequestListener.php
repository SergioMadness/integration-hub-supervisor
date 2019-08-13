<?php namespace professionalweb\IntegrationHub\Supervisor\Listeners;

use professionalweb\IntegrationHub\IntegrationHubCommon\Events\NewRequest;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services\RequestProcessor;

class NewRequestListener
{
    /**
     * @var RequestProcessor
     */
    private $requestProcessor;

    public function __construct(RequestProcessor $requestProcessor)
    {
        $this->setRequestProcessor($requestProcessor);
    }

    public function handle(NewRequest $event): void
    {
        $this->getRequestProcessor()->event($event->request);
    }

    /**
     * @return RequestProcessor
     */
    public function getRequestProcessor(): RequestProcessor
    {
        return $this->requestProcessor;
    }

    /**
     * @param RequestProcessor $requestProcessor
     *
     * @return $this
     */
    public function setRequestProcessor(RequestProcessor $requestProcessor): self
    {
        $this->requestProcessor = $requestProcessor;

        return $this;
    }
}