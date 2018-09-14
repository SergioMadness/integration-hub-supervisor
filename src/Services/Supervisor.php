<?php namespace professionalweb\IntegrationHub\Supervisor\Service;

use professionalweb\IntegrationHub\IntegrationHubCommon\Interfaces\EventData;
use professionalweb\IntegrationHub\IntegrationHubDB\Models\Request;
use professionalweb\IntegrationHub\IntegrationHubDB\Traits\UseFlowRepository;
use professionalweb\IntegrationHub\IntegrationHubDB\Interfaces\Models\ProcessOptions;
use professionalweb\IntegrationHub\IntegrationHubDB\Traits\UseProcessOptionsRepository;
use professionalweb\IntegrationHub\IntegrationHubDB\Interfaces\Repositories\FlowRepository;
use professionalweb\IntegrationHub\Supervisor\Interfaces\Services\Supervisor as ISupervisor;
use professionalweb\IntegrationHub\Supervisor\Exceptions\WrongProcessPathException;
use professionalweb\IntegrationHub\IntegrationHubDB\Interfaces\Repositories\ProcessOptionsRepository;

/**
 * Service that resolve next step of processing
 * @package professionalweb\IntegrationHub\Supervisor\Service
 */
class Supervisor implements ISupervisor
{
    use UseFlowRepository, UseProcessOptionsRepository;

    public function __construct(FlowRepository $flowRepository, ProcessOptionsRepository $processOptionsRepository)
    {
        $this->setFlowRepository($flowRepository)
            ->setProcessOptionsRepository($processOptionsRepository);
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
        // TODO: Implement updateStatus() method.
    }
}