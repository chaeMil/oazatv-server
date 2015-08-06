<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\VideoConvertQueueManager,
 Model\VideoManager;

/**
 * Description of VideoConvertQueuePresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class VideoConvertQueuePresenter extends BaseSecuredPresenter {
    
    public $database;
    private $videoManager;
    private $queueManager;
    
    function __construct(Nette\Database\Context $database, 
            VideoManager $videoManager, \Model\VideoConvertQueueManager $queueManager) {
        $this->database = $database;
        $this->videoManager = $videoManager;
        $this->queueManager = $queueManager;
    }
    
    public function renderDefault() {
        $this->getTemplateVariables($this->getUser()->getId());        
        $this->template->queueVideos = $this->queueManager->getQueue("ASC");
    }
    
    public function renderNavbarQueue() {
        $this->template->queueVideos = $this->queueManager->getQueue("ASC");
    }
    
    public function actionRemoveFromQueue($id) {
        $queueItem = $this->queueManager->getVideoFromQueueByQueueId($id);
        if ($queueItem->status == VideoConvertQueueManager::STATUS_CONVERTING) {
            $this->flashMessage("Nelze odebrat právě se konvertuje!", "warning");
            $this->redirect("VideoConvertQueue:");
        } else {
            $this->queueManager->removeFromQueue($id);
            $this->flashMessage("Úspěšně odebráno", "success");
            $this->redirect("VideoConvertQueue:");
        }
    }
}
