<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\ServerMonitorManager,
 Nette\Application\Responses\JsonResponse;

/**
 * Description of ServerMonitorPresenter
 *
 * @author chaemil
 */
class ServerMonitorPresenter  extends BaseSecuredPresenter {
    
    public $database;
    private $container;
    private $serverMonitorManager;
    
    function __construct(Nette\Database\Context $database,
            Nette\DI\Container $container,
            ServerMonitorManager $serverMonitorManager) {
        $this->database = $database;
        $this->container = $container;
        $this->serverMonitorManager = $serverMonitorManager;
    }

    
    public function renderDefault() {
        $this->getTemplateVariables($this->getUser()->getId());

        $this->template->dataVolume = $this->getDataVolume();
        $this->template->systemVolume = $this->getSystemVolume();
        $this->template->server = SERVER;
    }
    
    public function actionAjaxData() {
        $cpuTemp = $this->serverMonitorManager->getCPUTemp();
        $cpuLoad = $this->serverMonitorManager->getCPULoad();
        //$dataVolume = $this->getDataVolume();
        //$systemVolume = $this->getSystemVolume();
        
        $response['cpu'] = array('cpuTemp' => $cpuTemp, 'cpuLoad' => $cpuLoad);
        //$response['volumes'] = array('dataVolume' => $dataVolume, 'systemVolume' => $systemVolume);
        
        $this->sendResponse(new JsonResponse($response));
    }
    
    public function actionSaveCpuLog() {
        $this->serverMonitorManager->saveCpuLog();
        $httpResponse = $this->container->getByType('Nette\Http\Response');
        $httpResponse->setCode(\Nette\Http\Response::S200_OK);
        $this->terminate();
    }
    
    public function renderLoadCpuLog($items) {
        $cpuLog = $this->serverMonitorManager->getCpuLog($items);
        $this->sendResponse(new JsonResponse($cpuLog));
    }
    
    private function getDataVolume() {
        $dataVolume['used'] = $this->serverMonitorManager->getUsedDataDiskSpace();
        $dataVolume['total'] = $this->serverMonitorManager->getTotalDataDiskSpace();
        $dataVolume['free'] = $this->serverMonitorManager->getFreeDataDiskSpace();
        $dataVolume['usedPercent'] = $this->serverMonitorManager->getUsedDataDiskPercent();
        $dataVolume['usedH'] = $this->serverMonitorManager->getUsedDataDiskSpace(true);
        $dataVolume['totalH'] = $this->serverMonitorManager->getTotalDataDiskSpace(true);
        $dataVolume['freeH'] = $this->serverMonitorManager->getFreeDataDiskSpace(true);
        
        return $dataVolume;
    }
    
    private function getSystemVolume() {
        $systemVolume['used'] = $this->serverMonitorManager->getUsedSystemDiskSpace();
        $systemVolume['total'] = $this->serverMonitorManager->getTotalsystemDiskSpace();
        $systemVolume['free'] = $this->serverMonitorManager->getFreeSystemDiskSpace();
        $systemVolume['usedPercent'] = $this->serverMonitorManager->getUsedSystemDiskPercent();
        $systemVolume['usedH'] = $this->serverMonitorManager->getUsedSystemDiskSpace(true);
        $systemVolume['totalH'] = $this->serverMonitorManager->getTotalsystemDiskSpace(true);
        $systemVolume['freeH'] = $this->serverMonitorManager->getFreeSystemDiskSpace(true);
        
        return $systemVolume;
    }
}
