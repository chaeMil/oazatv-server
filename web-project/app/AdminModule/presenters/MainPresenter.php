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
    function __construct(Model\AdminFacade $adminFacade) {
        $this->model = $adminFacade;
    
    }
}