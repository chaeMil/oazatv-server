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
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
    
}
