<?php namespace professionalweb\IntegrationHub\Supervisor\Interfaces\Services;

use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\EventData;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Models\ProcessOptions;

/**
 * Interface for process dispatcher
 * @package professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services
 */
interface Dispatcher
{
    /**
     * Dispatch event
     *
     * @param EventData      $event
     * @param ProcessOptions $processOptions
     *
     * @return Dispatcher
     */
    public function dispatch(EventData $event, ProcessOptions $processOptions): self;
}