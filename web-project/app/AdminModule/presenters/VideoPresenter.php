<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\VideoManager,
 App\EventLogger;

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
    
    public function renderList() {       
        $this->getTemplateVariables($this->getUser()->getId());
        
        $this->template->videos = $this->videoManager
                ->getVideosFromDB(0, 9999, VideoManager::COLUMN_DATE." DESC");
    }
    
    public function renderDetail($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $video = $this->videoManager->getVideoFromDB($id);
        
        $this->template->video = $video;
       
        $this->template->originalFileInfo = $this->videoManager->getOriginalFileInfo($video->id);;
        $this->template->originalFile = VideoManager::COLUMN_ORIGINAL_FILE;
        $this->template->mp4File = VideoManager::COLUMN_MP4_FILE;
        $this->template->mp3File = VideoManager::COLUMN_MP3_FILE;
        $this->template->webmFile = VideoManager::COLUMN_WEBM_FILE;
        $this->template->thumbFile = VideoManager::COLUMN_THUMB_FILE;
        $this->template->thumbs = $this->videoManager->getThumbnails($id);
        $this['videoBasicInfoForm']->setDefaults($video->toArray());
    }
    
    public function createComponentVideoBasicInfoForm() {        
        $form = new Nette\Application\UI\Form;
        
        $form->addHidden('id')
                ->setRequired();
        
        $published = array(
            '0' => 'Ne',
            '1' => 'Ano',
        );
        
        $form->addSelect("published", "zveřejneno")
                ->setItems($published)
                ->setAttribute("class", "form-control");
        
        $form->addText('name_cs', 'název česky')
                ->setRequired()
                ->setAttribute("class", "form-control");

        $form->addText('name_en', 'název anglicky')
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addText('date', 'datum')
                ->setRequired()
                ->setHtmlId("datepicker")
                ->setAttribute("class", "form-control");
        
        $form->addText('tags', 'tagy')
                ->setRequired()
                ->setAttribute("class", "form-control")
                ->setAttribute("data-role", "tagsinput");
        
        $form->addText("categories", "kategorie:")
                ->setHtmlId("categories")
                ->setAttribute("class", "form-control");
        
        $form->addTextArea('description_cs', 'popis česky')
                ->setAttribute("class", "form-control");
        
        $form->addTextArea('description_en', 'popis anglicky')
                ->setAttribute("class", "form-control");
        
        $form->addTextArea("note", "interní poznámka")
                ->setAttribute("class", "form-control");

        $form->addSubmit('send', 'Uložit')
                ->setAttribute("class", "btn-lg btn-success btn-block");
        

        // call method signInFormSucceeded() on success
        $form->onSuccess[] = $this->videoBasicInfoSucceeded;

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }
    
    public function videoBasicInfoSucceeded($form) {
        $vals = $form->getValues();
        
        $status = $this->videoManager->saveVideoToDB($vals);
        
        if ($status) {
            EventLogger::log('user '.$this->getUser()->getIdentity()->login.' updated video '.$vals->id, 
                EventLogger::ACTIONS_LOG);
            
            $this->flashMessage("Změny úspěšně uloženy", "success");
        } else {
            $this->flashMessage("Nic nebylo změněno", "info");
        }
    }
    
    public function actionUseOriginalFileAs($id, $target) {
        $this->videoManager->useOriginalFileAs($id, $target);
        
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' used original file as '.$target.' in video '.$id, 
                EventLogger::ACTIONS_LOG);
        
        $this->flashMessage("Originání soubor použit jako: ".$target, "success");
        $this->redirect("Video:Detail#files", $id);
    }
    
    public function actionDeleteVideoFile($id, $file) {
        $this->videoManager->deleteVideoFile($id, $file);
        if ($file == VideoManager::COLUMN_THUMB_FILE) {
            $this->videoManager->deleteThumbnails($id);
        }
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted '.$file.' from video '.$id, 
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Soubor byl smazán", "danger");
        $this->redirect("Video:Detail#files", $id);
    }
    
    public function actionConvertFile($id, $input, $target) {
        $this->videoManager->addVideoToConvertQueue($id, $input, $target);
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' aded '.$input.' from video '.$id.' to conversion queue, target format is '.$target, 
                EventLogger::CONVERSION_LOG);
        $this->flashMessage("Soubor byl přidán do fronty", "info");
        $this->redirect("Video:Detail#files", $id);
    }
    
    public function actionDeleteVideo($id) {
        $this->videoManager->deleteVideo($id);
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted video '.$id, 
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Video bylo smazáno!", "danger");
        $this->redirect("Video:List");
    }
}
