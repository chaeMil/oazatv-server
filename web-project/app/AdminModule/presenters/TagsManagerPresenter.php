<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\TagsManager;

/**
 * Description of TagsManager
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class TagsManagerPresenter extends BaseSecuredPresenter {
    
    public $database;
    private $tagsManager;

    function __construct(Nette\Database\Context $database, TagsManager $tagsManager) {
        $this->database = $database;
        $this->tagsManager = $tagsManager;
    }
    
    public function renderDefault() {
        $this->getTemplateVariables($this->getUser()->getId());
        
        $this->template->tagArray = $this->tagsManager->tagCloud();
    }
}
