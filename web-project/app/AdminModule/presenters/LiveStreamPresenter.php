<?php

namespace App\AdminModule;

use Nette,
 Model\LiveStreamManager;

/**
 * Presenter for all common sites in administration
 * 
 * @author Michal Mlejnek <chaemil72@gmail.com>
 */
class LiveStreamPresenter extends BaseSecuredPresenter {
    
    public $database;
    public $liveStreamManager;

    function __construct(Nette\Database\Context $database, 
            LiveStreamManager $liveStreamManager) {
        $this->database = $database;
        $this->liveStreamManager = $liveStreamManager;
    }

    public function renderDefault() {
        $this->getTemplateVariables($this->getUser()->getId());
        dump($this->liveStreamManager->loadValues());
    }
    
}