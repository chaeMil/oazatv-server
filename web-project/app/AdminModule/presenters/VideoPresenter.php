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
    
    public function renderList() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    public function renderDetail($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $video = $this->videoManager->getVideoFromDB($id);
        
        $this->template->video = $video;
        $this['videoBasicInfoForm']->setDefaults($video->toArray());
    }
    
    public function createComponentVideoBasicInfoForm() {        
        $form = new Nette\Application\UI\Form;
        
        $form->addHidden('id')
                ->setRequired();
        
        $form->addText('name_cs', 'Název česky')
                ->setRequired()
                ->setAttribute("class", "form-control");

        $form->addText('name_en', 'Název anglicky')
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addText('date', 'Datum')
                ->setRequired()
                ->setHtmlId("datepicker")
                ->setAttribute("class", "form-control");
        
        $form->addText('tags', 'Tagy')
                ->setRequired()
                ->setAttribute("class", "form-control")
                ->setAttribute("data-role", "tagsinput");

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
            $this->flashMessage("Změny úspěšně uloženy", "success");
        } else {
            $this->flashMessage("Nic nebylo změněno", "info");
        }
    }
}
