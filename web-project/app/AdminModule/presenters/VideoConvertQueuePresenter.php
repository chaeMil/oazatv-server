<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\VideoConvertQueueManager,
 Model\VideoManager,
 Model\ConversionManager,
 Model\ConversionProfilesManager,
 App\EventLogger;

/**
 * Description of VideoConvertQueuePresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class VideoConvertQueuePresenter extends BaseSecuredPresenter {
    
    public $database;
    private $videoManager;
    private $queueManager;
    private $conversionManager;
    private $conversionProfilesManager;
    
    function __construct(Nette\Database\Context $database, 
            VideoManager $videoManager, VideoConvertQueueManager $queueManager,
            ConversionManager $conversionManager, 
            ConversionProfilesManager $conversionProfilesManager) {
        $this->database = $database;
        $this->videoManager = $videoManager;
        $this->queueManager = $queueManager;
        $this->conversionManager = $conversionManager;
        $this->conversionProfilesManager = $conversionProfilesManager;
    }
    
    public function renderDefault() {
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->queueVideos = $this->queueManager->getQueue("ASC", 100);
        $this->template->videoManager = $this->videoManager;
        $this->template->profilesManager = $this->conversionProfilesManager;
    }
    
    public function renderNavbarQueue() {
        $this->template->queueVideos = $this->queueManager->getQueue("DESC", 100);
        $this->template->videoManager = $this->videoManager;
        $this->template->conversionManager = $this->conversionManager;
    }
    
    public function renderQueueItem($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->queueId = $id;
        $this->template->conversionProfilesManager = $this->conversionProfilesManager;
    }
    
    public function renderQueueItemAjax($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $queueItem = $this->queueManager->getVideoFromQueueByQueueId($id);
        $this->template->queueItem = $queueItem;
        $this->template->conversionManager = $this->conversionManager;
        $this->template->video = $this->videoManager->getVideoFromDB($queueItem->video_id, 2);
        $this->template->thumbs = $this->videoManager->getThumbnails($queueItem->video_id);
    }
    
    public function actionRemoveFromQueue($id) {
        $queueItem = $this->queueManager->getVideoFromQueueByQueueId($id);
        if ($queueItem->status == VideoConvertQueueManager::STATUS_CONVERTING) {
            $this->flashMessage("Nelze odebrat právě se konvertuje!", "warning");
            $this->redirect("VideoConvertQueue:");
        } else {
            EventLogger::log('user '.$this->getUser()->getIdentity()->login.' removed video '.$queueItem->video_id.' from convert queue', 
                EventLogger::CONVERSION_LOG);
            
            $this->queueManager->removeFromQueue($id);
            $this->flashMessage("Úspěšně odebráno", "success");
            $this->redirect("VideoConvertQueue:");
        }
    }
}
