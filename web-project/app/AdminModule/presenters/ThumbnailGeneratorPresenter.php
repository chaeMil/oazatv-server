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
 * Description of ThumbnailGeneratorPresenter
 *
 * @author chaemil
 */
class ThumbnailGeneratorPresenter extends BaseSecuredPresenter {
    
    public $database;
    private $videoManager;

    function __construct(Nette\Database\Context $database, VideoManager $videoManager) {
        $this->database = $database;
        $this->videoManager = $videoManager;
    }
    
    public function renderDefault() {
        $videosWithoutThumb = $this->database->table(VideoManager::TABLE_NAME)
                ->where(VideoManager::COLUMN_THUMB_FILE, "")->fetchAll();
        
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->videosWithoutThumb = $videosWithoutThumb;
    }
    
    public function renderCreate($videoId) {
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->video = $this->videoManager->getVideoFromDB($videoId);
    }
}
