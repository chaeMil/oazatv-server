<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\SongsManager,
 App\EventLogger;

/**
 * Description of VideoPresenter
 *
 * @author chaemil
 */
class SongsPresenter extends BaseSecuredPresenter {
    
    public $database;
    public $songsManager;

    function __construct(Nette\Database\Context $database, 
            SongsManager $songsManager) {
        $this->database = $database;
        $this->songsManager = $songsManager;
    }
    
    public function renderList() {       
        $this->getTemplateVariables($this->getUser()->getId());
        
        $this->template->songs = $this->songsManager
                ->getSongsFromDB();
    }
    
    public function renderCreateSong() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    public function renderDetail($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $song = $this->songsManager->getSongFromDB($id);
        $this->template->song = $song;
        
        if (!isset($song['id'])) {
            $this->error('Požadovaná chvála neexistuje!');
        }
        
        $songArray = $song->toArray();
        $songArray['body'] = htmlspecialchars_decode($songArray['body']);
        $songArray['body'] = \App\StringUtils::removeStyleTag($songArray['body']);
        
        $this['songForm']->setDefaults($songArray);
        
    }
    
    public function createComponentSongForm() {        
        $form = new Nette\Application\UI\Form;
        
        $form->addHidden('id');
        
        $form->addText('name', 'název')
                ->setAttribute("class", "form-control")
                ->setRequired();

        $form->addText('tag', 'TAG')
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addText('author', 'autor')
                ->setAttribute("class", "form-control");
        
        $form->addTextArea('body', 'text')
                ->setHtmlId('bodyEditor')
                ->setAttribute("class", "form-control");

        $form->addSubmit('send', 'Uložit')
                ->setAttribute("class", "btn-lg btn-success btn-block");
        

        // call method signInFormSucceeded() on success
        $form->onSuccess[] = [$this, 'songSucceeded'];

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }
    
    
    public function songSucceeded($form) {
        $vals = $form->getValues();
        
        $status = $this->songsManager->saveSongToDB($vals);
        
        if ($status) {
            EventLogger::log('user '.$this->getUser()->getIdentity()
                    ->login.' updated song '.$vals->id,
                EventLogger::ACTIONS_LOG);
            
            $this->flashMessage("Změny úspěšně uloženy", "success");
        } else {
            $this->flashMessage("Nic nebylo změněno", "info");
        }
        $this->redirect("Songs:List");
    }
    
    public function actionDeleteSong($id) {
        $this->songsManager->deleteSong($id);
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted song '.$id, 
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Chvála byla smazána!", "danger");
        $this->redirect("Songs:List");
    }
}

