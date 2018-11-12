<?php namespace professionalweb\IntegrationHub\Supervisor\Service;

use professionalweb\IntegrationHub\IntegrationHubDB\Models\Request;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\EventData;
use professionalweb\IntegrationHub\IntegrationHubDB\Traits\UseFlowRepository;
use professionalweb\IntegrationHub\IntegrationHubDB\Traits\UseRequestRepository;
use professionalweb\IntegrationHub\Supervisor\Exceptions\WrongProcessPathException;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services\Filter;
use professionalweb\IntegrationHub\IntegrationHubDB\Traits\UseProcessOptionsRepository;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Services\FieldMapper;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\Models\ProcessOptions;
use professionalweb\IntegrationHub\IntegrationHubDB\Interfaces\Repositories\FlowRepository;
use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Supervisor as ISupervisor;
use professionalweb\IntegrationHub\IntegrationHubDB\Interfaces\Repositories\RequestRepository;
use professionalweb\IntegrationHub\IntegrationHubDB\Interfaces\Repositories\ProcessOptionsRepository;

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
     * @param Request $request
     *
     * @return null|ProcessOptions
     * @throws \Exception
     */
    public function nextProcess(Request $request): ?ProcessOptions
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
        if ($flow->isConditional($currentStep)) {
            $nextStep = array_first($this->getFilter()->filter($flow->getCondition($currentStep), $request->getData()));
        } else {
            $nextStep = $flow->getNext($currentStep);
        }

        if ($nextStep !== null) {
            $processOptions = $this->getProcessOptionsRepository()->model($nextStep);
        }

        $this->getRequestRepository()->save(
            $request->setCurrentStep($flow->id, $currentStep)
        );

        return $processOptions;
    }

    /**
     * Update request status
     *
     * @param EventData  $request
     * @param string     $processId
     * @param bool       $processSucceed
     * @param null|mixed $processResponse
     *
     * @return Request
     */
    public function processResponse(EventData $request, string $processId, $processSucceed = true, $processResponse = null): Request
    {
        $requestRepository = $this->getRequestRepository();
        /** @var Request $requestModel */
        $requestModel = $requestRepository->model($request->getId());

        $data = $requestModel->getData();
        $data[$processId] = $request->getData();
        $requestModel
            ->setCurrentStep($requestModel->getCurrentFlow(), $processId)
            ->setProcessResponse($processId, $processResponse, $processSucceed)
            ->setData($data);

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