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
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($this->videoManager->countVideos());
        $paginator->setItemsPerPage(30);
        $paginator->setPage(1);
        
        $this->getTemplateVariables($this->getUser()->getId());
        
        $this->template->videos = $this->videoManager
                ->getVideosFromDB($paginator->getLength(), $paginator->getOffset(), "id");
    }
    
    public function renderDetail($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $video = $this->videoManager->getVideoFromDB($id);
        
        $this->template->video = $video;
       
        $this->template->originalFileInfo = $this->videoManager->getOriginalFileInfo($video->id);;
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
            $this->flashMessage("Změny úspěšně uloženy", "success");
        } else {
            $this->flashMessage("Nic nebylo změněno", "info");
        }
    }
}
