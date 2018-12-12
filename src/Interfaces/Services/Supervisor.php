<?php namespace professionalweb\IntegrationHub\Supervisor\Interfaces\Services;

use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\EventData;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\ProcessResponse;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Models\ProcessOptions;

/**
 * Interface for event supervisor
 * @package professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services
 */
interface Supervisor
{
    /**
     * Add/update event
     *
     * @param EventData $request
     *
     * @return ProcessOptions
     */
    public function nextProcess(EventData $request): ?ProcessOptions;

    /**
     * Update request status
     *
     * @param ProcessResponse $response
     *
     * @return EventData
     */
    public function processResponse(ProcessResponse $response): EventData;
}