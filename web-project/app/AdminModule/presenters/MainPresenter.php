<?php

namespace App\AdminModule;

use Nette,
    Model,
    Latte\Helpers;

/**
 * Presenter for all common sites in administration
 * 
 * @author Michal Mlejnek <chaemil72@gmail.com>
 */
class MainPresenter extends BasePresenter {
    
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
    }
}