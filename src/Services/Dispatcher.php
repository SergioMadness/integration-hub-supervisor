<?php namespace professionalweb\IntegrationHub\Supervisor\Service;

use Illuminate\Foundation\Bus\DispatchesJobs;
use professionalweb\IntegrationHub\IntegrationHubCommon\Jobs\NewEvent;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\EventData;
use professionalweb\IntegrationHub\IntegrationHubCommon\Events\EventToProcess;
use professionalweb\IntegrationHub\IntegrationHubDB\Interfaces\Models\ProcessOptions;
use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Dispatcher as IDispatcher;

/**
 * Service that send event data to next step.
 * Through event or queue
 * @package professionalweb\IntegrationHub\Supervisor\Service
 */
class Dispatcher implements IDispatcher
{
    use DispatchesJobs {
        dispatch as protected dispatchToQueue;
    }

    /**
     * Dispatch event
     *
     * @param EventData      $event
     * @param ProcessOptions $processOptions
     *
     * @return IDispatcher
     */
    public function dispatch(EventData $event, ProcessOptions $processOptions): IDispatcher
    {
        if ($processOptions->isRemote()) {
            if (!empty($processOptions->getQueue())) {
                $this->toQueue($event, $processOptions);
            } elseif ($processOptions->getHost()) {
                $this->byAPI($event, $processOptions);
            }
        } else {
            $this->sendEvent($event, $processOptions);
        }

        return $this;
    }

    /**
     * Send event to processor through API
     *
     * @param EventData      $event
     * @param ProcessOptions $processOptions
     */
    protected function byAPI(EventData $event, ProcessOptions $processOptions): void
    {
        // TODO: call api
    }

    /**
     * Add event to queue
     *
     * @param EventData      $event
     * @param ProcessOptions $processOptions
     */
    protected function toQueue(EventData $event, ProcessOptions $processOptions): void
    {
        $this->dispatchToQueue(
            (new NewEvent($event, $processOptions))->onQueue($processOptions->getQueue())
        );
    }

    /**
     * Send event to local processor
     *
     * @param EventData      $event
     * @param ProcessOptions $processOptions
     */
    protected function sendEvent(EventData $event, ProcessOptions $processOptions): void
    {
        event(new EventToProcess($event, $processOptions));
    }
}