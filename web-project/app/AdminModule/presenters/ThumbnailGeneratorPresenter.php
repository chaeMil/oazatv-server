<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\VideoManager,
 Model\ThumbnailGenerator;

/**
 * Description of ThumbnailGeneratorPresenter
 *
 * @author chaemil
 */
class ThumbnailGeneratorPresenter extends BaseSecuredPresenter {
    
    public $database;
    private $videoManager;
    private $generator;

    function __construct(Nette\Database\Context $database, VideoManager $videoManager,
     \Model\ThumbnailGenerator $generator) {
        $this->database = $database;
        $this->videoManager = $videoManager;
        $this->generator = $generator;
    }
    
    public function renderDefault() {
        $videosWithoutThumb = $this->database->table(VideoManager::TABLE_NAME)
                ->where(VideoManager::COLUMN_THUMB_FILE, "")->fetchAll();
        
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->videosWithoutThumb = $videosWithoutThumb;
    }
    
    public function renderCreate($videoId) {
        $this->getTemplateVariables($this->getUser()->getId());
        $this->template->video = $this->videoManager->getVideoFromDB($videoId, 2);
        $this->template->userId = $this->getUser()->getId();
    }
    
    public function actionGenerate($videoId, $userId,  $file, $hour, $minute, $second) {
        $this->generator->generate($videoId, $userId, $file, $hour, $minute, $second); 
        $this->template->videoId = $videoId;
        $this->template->userId = $userId;
        $this->template->hour = \App\StringUtils::addLeadingZero($hour, 2);
        $this->template->minute = \App\StringUtils::addLeadingZero($minute, 2);
        $this->template->second = \App\StringUtils::addLeadingZero($second, 2);;
    }
    
    public function actionUseAsThumb($videoId, $file) {
        $this->videoManager->useExternalFileAsThumb($videoId, $file);
        $this->flashMessage("Miniatura nastavena", "success");
        $this->redirect("ThumbnailGenerator:create", $videoId);
    }
    
    public function actionExportToImageEditor($inputFile) {
        $this->redirect("ImageEditor:", $inputFile);
    }
}
