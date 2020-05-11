<?php namespace professionalweb\IntegrationHub\Supervisor\Service;

use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\EventData;
use professionalweb\IntegrationHub\IntegrationHubCommon\Traits\UseFlowRepository;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\ProcessResponse;
use professionalweb\IntegrationHub\Supervisor\Exceptions\WrongProcessPathException;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services\Filter;
use professionalweb\IntegrationHub\IntegrationHubCommon\Traits\UseRequestRepository;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services\FieldMapper;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Models\ProcessOptions;
use professionalweb\IntegrationHub\IntegrationHubCommon\Traits\UseProcessOptionsRepository;
use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Supervisor as ISupervisor;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Repositories\FlowRepository;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Repositories\RequestRepository;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Repositories\ProcessOptionsRepository;

/**
 * Service that resolve next step of processing
 * @package professionalweb\IntegrationHub\Supervisor\Service
 */
class Supervisor implements ISupervisor
{
    use UseFlowRepository, UseProcessOptionsRepository, UseRequestRepository;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var FieldMapper
     */
    private $mapper;

    public function __construct(FlowRepository $flowRepository,
                                ProcessOptionsRepository $processOptionsRepository,
                                RequestRepository $requestRepository,
                                Filter $filter,
                                FieldMapper $mapper)
    {
        $this
            ->setFlowRepository($flowRepository)
            ->setProcessOptionsRepository($processOptionsRepository)
            ->setRequestRepository($requestRepository)
            ->setFilter($filter)
            ->setMapper($mapper);
    }

    /**
     * Add/update event
     *
     * @param EventData $request
     *
     * @return null|ProcessOptions
     * @throws \Exception
     */
    public function nextProcess(EventData $request): ?ProcessOptions
    {
        $flowRepository = $this->getFlowRepository();
        $currentFlow = $request->getCurrentFlow();
        if (empty($currentFlow)) {
            $flow = $flowRepository->getDefault();
        } else {
            $flow = $flowRepository->model($currentFlow);
        }
        if ($flow === null) {
            throw new WrongProcessPathException();
        }
        $processOptions = null;
        $currentStep = $request->getCurrentStep();
        $nextStep = null;
        if (!empty($currentStep) && $flow->isConditional($currentStep)) {
            $nextStepId = array_first($this->getFilter()->filter($flow->getCondition($currentStep), $request->getData()));
            $nextStep = $nextStepId !== null ? $flow->getNode($nextStepId) : null;
        } else {
            $nextStep = $flow->getNext($currentStep);
        }

        if ($nextStep !== null) {
            $request->setStatus(EventData::STATUS_QUEUE);
            $processOptions = $this->getProcessOptionsRepository()->model($nextStep->getSubsystemId());
        } else {
            $request->setStatus(EventData::STATUS_SUCCESS);
        }

        $this->getRequestRepository()->save(
            $request->setCurrentStep($flow->id, $currentStep)->setNextStep($flow->id, $nextStep === null ? '' : $nextStep->getId())
        );

        return $processOptions;
    }

    /**
     * Update request status
     *
     * @param ProcessResponse $response
     *
     * @return EventData
     */
    public function processResponse(ProcessResponse $response): EventData
    {
        $request = $response->getEventData();
        $requestRepository = $this->getRequestRepository();
        /** @var EventData $requestModel */
        $requestModel = $requestRepository->model($request->getId());

        $data = $requestModel->getData();
        $processId = $response->getProcessId();
        $data[$requestModel->getNextStep()] = $request->getData();
        $requestModel
            ->setProcessResponse($processId, $response->getProcessResponse(), $response->isSucceed())
            ->setData($data);
        if (!$response->isSucceed()) {
            $requestModel->incAttempts()->stopPropagation();
            if ($requestModel->getAttemptQty() > 6) {
                $requestModel->setStatus(EventData::STATUS_FAILED);
            } else {
                $requestModel->setStatus(EventData::STATUS_RETRY);
            }
        } else {
            $requestModel->dropAttempts()->move();
        }

        $requestRepository->save($requestModel);

        return $requestModel;
    }

    //<editor-fold desc="Getters and setters">

    /**
     * @return Filter
     */
    public function getFilter(): Filter
    {
        return $this->filter;
    }

    /**
     * @param Filter $filter
     *
     * @return $this
     */
    public function setFilter(Filter $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return FieldMapper
     */
    public function getMapper(): FieldMapper
    {
        return $this->mapper;
    }

    /**
     * @param FieldMapper $mapper
     *
     * @return $this
     */
    public function setMapper(FieldMapper $mapper): self
    {
        $this->mapper = $mapper;

        return $this;
    }
    //</editor-fold>
}