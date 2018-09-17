<?php namespace professionalweb\IntegrationHub\Supervisor\Interfaces\Services;

use professionalweb\IntegrationHub\IntegrationHubDB\Models\Request;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\EventData;
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
     * @param Request $request
     *
     * @return ProcessOptions
     */
    public function nextProcess(Request $request): ?ProcessOptions;

    /**
     * Update request status
     *
     * @param EventData $request
     *
     * @param string    $processId
     *
     * @return Request
     */
    public function processResponse(EventData $request, string $processId): Request;
}