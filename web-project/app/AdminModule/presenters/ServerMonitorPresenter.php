<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\ServerMonitorManager;

/**
 * Description of ServerMonitorPresenter
 *
 * @author chaemil
 */
class ServerMonitorPresenter  extends BaseSecuredPresenter {
    
    public $database;
    private $serverMonitorManager;
    
    function __construct(Nette\Database\Context $database,
            ServerMonitorManager $serverMonitorManager) {
        $this->database = $database;
        $this->serverMonitorManager = $serverMonitorManager;
    }

    
    public function renderDefault() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
}
