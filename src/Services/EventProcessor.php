<?php

declare(strict_types=1);

namespace professionalweb\IntegrationHub\Supervisor\Services;

use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Dispatcher;
use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Supervisor;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\EventData;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services\FieldMapper;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services\RequestProcessor;

/**
 * Class EventProcessor
 * @package professionalweb\IntegrationHub\Supervisor\Services
 */
class EventProcessor implements RequestProcessor
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var Supervisor
     */
    private $supervisor;

    /**
     * @var FieldMapper
     */
    private $fieldMapper;

    public function __construct(Dispatcher $dispatcher, Supervisor $supervisor, FieldMapper $fieldMapper)
    {
        $this->setDispatcher($dispatcher)->setSupervisor($supervisor)->setFieldMapper($fieldMapper);
    }

    /**
     * Process event
     */
    public function event(EventData $event): RequestProcessor
    {
        if (($nextProcess = $this->getSupervisor()->nextProcess($event)) !== null) {
            $mapped = [];
            if (!empty($map = $nextProcess->getMapping())) {
                $mapped = $this->getFieldMapper()->map($map, $event->getData());
            }
            $event->setData($mapped);
            $this->getDispatcher()->dispatch($event, $nextProcess);
        }

        return $this;
    }

    public function getSupervisor(): Supervisor
    {
        return $this->supervisor;
    }

    /**
     * @return EventProcessor
     */
    public function setSupervisor(Supervisor $supervisor): self
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    public function getFieldMapper(): FieldMapper
    {
        return $this->fieldMapper;
    }

    /**
     * @return $this
     */
    public function setFieldMapper(FieldMapper $fieldMapper): self
    {
        $this->fieldMapper = $fieldMapper;

        return $this;
    }

    public function getDispatcher(): Dispatcher
    {
        return $this->dispatcher;
    }

    /**
     * @return EventProcessor
     */
    public function setDispatcher(Dispatcher $dispatcher): self
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }
}