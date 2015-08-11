<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;


use Nette;
/**
 * Description of ImageEditorPresenter
 *
 * @author chaemil
 */
class ImageEditorPresenter extends BaseSecuredPresenter {
    
    public function renderDefault($inputFile) {
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->inputFile = $inputFile;
    }
}
