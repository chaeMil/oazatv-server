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
    
    function __construct(Nette\Database\Context $database,
            \App\Model\UserManager $userManager,
            \Model\VideoConvertQueueManager $queueManager) {
        $this->database = $database;
        $this->userManager = $userManager;
        $this->queueManager = $queueManager;
    }
    
    function renderDefault() {
        
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->lastloginString = StringUtils::
            timeElapsedString($this->getUser()->getIdentity()->data['lastlogin_time']);
        $this->template->queueTasks = $this->queueManager->getQueueCount(\Model\VideoConvertQueueManager::STATUS_WAITING);
    }
}