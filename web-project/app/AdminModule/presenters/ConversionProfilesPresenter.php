<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\ConversionProfilesManager,
 App\EventLogger;

/**
 * Description of VideoPresenter
 *
 * @author chaemil
 */
class ConversionProfilesPresenter extends BaseSecuredPresenter {
    
    public $database;
    public $conversionProfilesManager;

    function __construct(Nette\Database\Context $database, 
        ConversionProfilesManager $conversionProfilesManager) {
        $this->database = $database;
        $this->conversionProfilesManager = $conversionProfilesManager;
    }
    
    public function renderList() {       
        $this->getTemplateVariables($this->getUser()->getId());
        
        $this->template->profiles = $this->conversionProfilesManager
                ->getProfilesFromDB();
    }
    
    public function renderCreateProfile() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    public function renderDetail($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $profile = $this->conversionProfilesManager->getProfileFromDB($id);
        $this->template->profile = $profile;
        
        if (!isset($profile['id'])) {
            $this->error('Požadovaný profil neexistuje!');
        }
        
        $this['conversionProfileForm']->setDefaults($profile->toArray());
        
    }
    
    public function createComponentConversionProfileForm() {        
        $form = new Nette\Application\UI\Form;
        
        $form->addHidden('id');
        
        $form->addText('name', 'název')
                ->setRequired()
                ->setAttribute("class", "form-control");

        $form->addText('audio_bitrate', 'audio bitrate')
                ->setRequired()
                ->setAttribute("type", "number")
                ->setAttribute("min", 32)
                ->setAttribute("max", 320)
                ->setAttribute("step", 1)
                ->setAttribute("class", "form-control");
        
        $form->addText('video_bitrate', 'video bitrate')
                ->setRequired()
                ->setAttribute("type", "number")
                ->setAttribute("min", 350)
                ->setAttribute("max", 10000)
                ->setAttribute("step", 1)
                ->setAttribute("class", "form-control");

        $form->addSubmit('send', 'Uložit')
                ->setAttribute("class", "btn-lg btn-success btn-block");
        

        // call method signInFormSucceeded() on success
        $form->onSuccess[] = $this->profileSucceeded;

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }
    
    
    public function profileSucceeded($form) {
        $vals = $form->getValues();
        
        $status = $this->conversionProfilesManager->saveProfileToDB($vals);
        
        if ($status) {
            EventLogger::log('user '.$this->getUser()->getIdentity()
                    ->login.' updated conversion profile '.$vals->id,
                EventLogger::ACTIONS_LOG);
            
            $this->flashMessage("Změny úspěšně uloženy", "success");
        } else {
            $this->flashMessage("Nic nebylo změněno", "info");
        }
        $this->redirect("ConversionProfiles:List");
    }
    
    public function actionDeleteProfile($id) {
        $this->conversionProfilesManager->deleteProfile($id);
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted conversion profile '.$id, 
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Profil byl smazán!", "danger");
        $this->redirect("ConversionProfiles:List");
    }
}

