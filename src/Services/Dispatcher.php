<?php namespace professionalweb\IntegrationHub\Supervisor\Service;

use Illuminate\Support\Arr;
use Illuminate\Foundation\Bus\DispatchesJobs;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\EventData;
use professionalweb\IntegrationHub\IntegrationHubCommon\Events\EventToProcess;
use professionalweb\IntegrationHub\IntegrationHubCommon\Events\EventToSupervisor;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Models\ProcessOptions;
use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Dispatcher as IDispatcher;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Exceptions\ArrayException;
use professionalweb\IntegrationHub\IntegrationHubCommon\Jobs\EventToProcess as EventToProcessJob;

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
            (new EventToProcessJob($event, $processOptions))->onQueue($processOptions->getQueue())
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
        $succeed = true;
        $response = null;
        try {
            $result = event(new EventToProcess($event, $processOptions));
        } catch (ArrayException $ex) {
            $succeed = false;
            $response = $ex->getMessages();
            $result = [$event];
        } catch (\Exception $ex) {
            $succeed = false;
            $response = $ex->getMessage();
            $result = [$event];
        }
        event(new EventToSupervisor(Arr::last(Arr::where($result, function ($item) {
            return $item !== null;
        })), $processOptions->getSubsystemId(), $succeed, $response));
    }
}