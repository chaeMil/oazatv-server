<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\VideoManager;

/**
 * Description of VideoPresenter
 *
 * @author chaemil
 */
class VideoPresenter extends BaseSecuredPresenter {
    
    public $database;
    private $videoManager;

    function __construct(Nette\Database\Context $database, VideoManager $videoManager) {
        $this->database = $database;
        $this->videoManager = $videoManager;
    }
    
    public function renderDetail($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->video = $this->videoManager->getVideoFromDB($id);
    }
}
