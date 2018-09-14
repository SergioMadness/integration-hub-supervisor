<?php namespace professionalweb\IntegrationHub\Supervisor\Interfaces\Services;

use professionalweb\IntegrationHub\IntegrationHubDB\Models\Request;
use professionalweb\IntegrationHub\IntegrationHubDB\Interfaces\Models\ProcessOptions;

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
}