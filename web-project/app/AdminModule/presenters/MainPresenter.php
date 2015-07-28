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
    
    private $model;
    public $database;
    private $userManager;
    
    function __construct(Nette\Database\Context $database, 
            Model\AdminFacade $adminFacade, \App\Model\UserManager $userManager) {
        $this->model = $adminFacade;
        $this->database = $database;
        $this->userManager = $userManager;
    }
    
    function renderDefault() {
        
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->lastloginString = StringUtils::
            timeElapsedString($this->getUser()->getIdentity()->data['lastlogin_time']);
    }
}