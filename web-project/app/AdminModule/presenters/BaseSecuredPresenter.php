<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

/**
 * Description of BaseSecuredPresenter
 *
 * @author chaemil
 */
class BaseSecuredPresenter extends BasePresenter {
    
    function startup() {
        
        $this->getUser()->getStorage()->setNamespace('admin');
        
        if (!$this->getUser()->isLoggedIn()) {
            if (!$this->getUser()->isInRole("administrator")) {
                $this->redirect('Sign:in');
            }
        }
        
        parent::startup();
    }
    
}
