<?php namespace professionalweb\IntegrationHub\Supervisor\Service;

use professionalweb\IntegrationHub\IntegrationHubDB\Models\Request;
use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\EventData;
use professionalweb\IntegrationHub\IntegrationHubDB\Traits\UseFlowRepository;
use professionalweb\IntegrationHub\IntegrationHubDB\Traits\UseRequestRepository;
use professionalweb\IntegrationHub\Supervisor\Exceptions\WrongProcessPathException;
use professionalweb\IntegrationHub\IntegrationHubDB\Traits\UseProcessOptionsRepository;
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

    public function __construct(FlowRepository $flowRepository, ProcessOptionsRepository $processOptionsRepository, RequestRepository $requestRepository)
    {
        $this
            ->setFlowRepository($flowRepository)
            ->setProcessOptionsRepository($processOptionsRepository)
            ->setRequestRepository($requestRepository);
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
        if (($nextStep = $flow->getNext($request->getCurrentStep())) !== null) {
            $processOptions = $this->getProcessOptionsRepository()->model($nextStep);
        }

        $this->getRequestRepository()->save(
            $request->setCurrentStep($flow->id, '')
        );

        return $processOptions;
    }

    /**
     * Update request status
     *
     * @param EventData $request
     *
     * @param string    $processId
     *
     * @return Request
     */
    public function processResponse(EventData $request, string $processId): Request
    {
        $requestRepository = $this->getRequestRepository();
        /** @var Request $requestModel */
        $requestModel = $requestRepository->model($request->getId());

        $requestModel->setCurrentStep($requestModel->getCurrentFlow(), $processId);

        $requestRepository->save($requestModel);

        return $requestModel;
    }
}