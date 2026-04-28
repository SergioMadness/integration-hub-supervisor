<?php

declare(strict_types=1);

namespace professionalweb\IntegrationHub\Supervisor\Interfaces\Services;

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
     * @return ProcessOptions
     */
    public function nextProcess(EventData $request): ?ProcessOptions;

    /**
     * Update request status
     */
    public function processResponse(ProcessResponse $response): EventData;
}