<?php namespace professionalweb\IntegrationHub\Supervisor\Service;

use professionalweb\IntegrationHub\IntegrationHubDB\Models\Request;
use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Dispatcher;
use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Supervisor;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services\FieldMapper;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services\RequestProcessor;

/**
 * Class EventProcessor
 * @package professionalweb\IntegrationHub\Supervisor\Service
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
     *
     * @param Request $event
     *
     * @return RequestProcessor
     */
    public function event(Request $event): RequestProcessor
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

    /**
     * @return Dispatcher
     */
    public function getDispatcher(): Dispatcher
    {
        return $this->dispatcher;
    }

    /**
     * @param Dispatcher $dispatcher
     *
     * @return EventProcessor
     */
    public function setDispatcher(Dispatcher $dispatcher): self
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * @return Supervisor
     */
    public function getSupervisor(): Supervisor
    {
        return $this->supervisor;
    }

    /**
     * @param Supervisor $supervisor
     *
     * @return EventProcessor
     */
    public function setSupervisor(Supervisor $supervisor): self
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    /**
     * @return FieldMapper
     */
    public function getFieldMapper(): FieldMapper
    {
        return $this->fieldMapper;
    }

    /**
     * @param FieldMapper $fieldMapper
     *
     * @return $this
     */
    public function setFieldMapper(FieldMapper $fieldMapper): self
    {
        $this->fieldMapper = $fieldMapper;

        return $this;
    }
}