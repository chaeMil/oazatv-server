<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\PreachersManager,
 App\EventLogger,
 App\StringUtils;

/**
 * Description of VideoPresenter
 *
 * @author chaemil
 */
class PreachersPresenter extends BaseSecuredPresenter {
    
    public $database;
    public $preachersManager;

    function __construct(Nette\Database\Context $database, 
        PreachersManager $preachersManager) {
        $this->database = $database;
        $this->preachersManager = $preachersManager;
    }
    
    public function renderList() {       
        $this->getTemplateVariables($this->getUser()->getId());
        
        $this->template->preachers = $this->preachersManager
                ->getPreachersFromDB();
    }
    
    public function renderCreatePreacher() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    public function renderDetail($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $preacher = $this->preachersManager->getPreacherFromDB($id);
        $this->template->preacher = $preacher;
        
        if (!isset($preacher['id'])) {
            $this->error('Požadovaný kazatel neexistuje!');
        }
        
        $this['preacherForm']->setDefaults($preacher->toArray());
        
    }
    
    public function createComponentPreacherForm() {        
        $form = new Nette\Application\UI\Form;
        
        $form->addHidden('id');
        
        $form->addText('name', 'jméno')
                ->setAttribute("class", "form-control")
                ->setRequired();

        $form->addText('tags', 'tagy')
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addUpload('file', 'fotka')
                ->setAttribute("class", "form-control");
        
        $form->addTextArea('about_cs', 'info česky')
                ->setHtmlId('aboutCsEditor')
                ->setAttribute("class", "form-control");
        
        $form->addTextArea('about_en', 'info anglicky')
                ->setHtmlId('aboutEnEditor')
                ->setAttribute("class", "form-control");

        $form->addSubmit('send', 'Uložit')
                ->setAttribute("class", "btn-lg btn-success btn-block");
        

        $form->onSuccess[] = $this->preacherSucceeded;

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }
    
    
    public function preacherSucceeded($form) {
        $vals = $form->getValues();
        
        $file = $vals['file'];
        unset($vals['file']);
       
        $status = $this->preachersManager->savePreacherToDB($vals);
        
        if(!$status) {
            $filename = $vals['id'];
        } else {
            $filename = $status;
        }
        
        if ($file->isOk()) {
            $extension = "jpg";
            $newName = PREACHERS_FOLDER.$filename.".".$extension;
            if(file_exists($newName)) {
                unlink($newName);
                $this->preachersManager->deletePhotoThumbnails($filename);
            }
            $file->move($newName);
            chmod($newName, 0777);
            $this->preachersManager->generatePhotoThumbnails($filename);
        }
        
        if ($status) {
            EventLogger::log('user '.$this->getUser()->getIdentity()
                    ->login.' updated preacher '.$vals->id,
                EventLogger::ACTIONS_LOG);
            
            $this->flashMessage("Změny úspěšně uloženy", "success");
        } else {
            $this->flashMessage("Nic nebylo změněno", "info");
        }
        
        $this->redirect("Preachers:detail", $filename);
    }
    
    public function actionDeletePreacher($id) {
        $this->preachersManager->deletePreacher($id);
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted preacher '.$id, 
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Kazatel byl smazán!", "danger");
        $this->redirect("Preachers:List");
    }
}

