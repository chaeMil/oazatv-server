<?php

namespace App\AdminModule;

use Nette,
    Model,
    App\StringUtils;

/**
 * Presenter for all common sites in administration
 * 
 * @author Michal Mlejnek <chaemil72@gmail.com>
 */
class MainPresenter extends BaseSecuredPresenter {
    
    /**
     * Load model classes for operate with db
     */
    
    public $database;
    private $userManager;
    private $queueManager;
    private $bugReport;
    
    function __construct(Nette\Database\Context $database,
            \App\Model\UserManager $userManager,
            \Model\VideoConvertQueueManager $queueManager,
            Model\BugReport $bugReport) {
        $this->database = $database;
        $this->userManager = $userManager;
        $this->queueManager = $queueManager;
        $this->bugReport = $bugReport;
    }
    
    function renderDefault() {
        
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->lastloginString = StringUtils::
            timeElapsedString($this->getUser()->getIdentity()->data['lastlogin_time']);
        $isConvertingVideo = $this->queueManager->getQueueCount(Model\VideoConvertQueueManager::STATUS_CONVERTING);
        $awaitingConversion = $this->queueManager->getQueueCount(\Model\VideoConvertQueueManager::STATUS_WAITING);
        
        $this->template->isConvertingVideo = $isConvertingVideo;
        $this->template->awaitingConversion = $awaitingConversion;
        $this->template->unresolvedBugs = $this->bugReport->countUnresolvedBugs();
    }
}