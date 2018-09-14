<?php namespace professionalweb\IntegrationHub\Supervisor\Listeners;

use professionalweb\IntegrationHub\IntegrationHubCommon\Events\NewRequest;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services\RequestProcessor;

class NewRequestListener
{
    public function handler(NewRequest $event, RequestProcessor $requestProcessor): void
    {
        $requestProcessor->event($event->request);
    }
}