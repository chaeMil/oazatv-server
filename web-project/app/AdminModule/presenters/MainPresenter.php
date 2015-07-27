<?php

namespace App\AdminModule;

use Nette,
    Model;

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
    
    function __construct(Nette\Database\Context $database, Model\AdminFacade $adminFacade) {
        $this->model = $adminFacade;
        $this->database = $database;
    }
    
    function renderDefault() {
        
        if(!$this->user->isLoggedIn()) {
            $this->redirect("Sign:in");
        }
        
        $this->getTemplateVariables($this->getUser()->getId());
    }
}