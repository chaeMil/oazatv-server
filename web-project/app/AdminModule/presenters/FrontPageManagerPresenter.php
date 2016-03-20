<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\FrontPageManager,
 App\EventLogger;

/**
 * Description of VideoPresenter
 *
 * @author chaemil
 */
class FrontPageManagerPresenter extends BaseSecuredPresenter {
    
    public $database;
    public $frontPageManager;

    function __construct(Nette\Database\Context $database, 
            FrontPageManager $frontPageManager) {
        $this->database = $database;
        $this->frontPageManager = $frontPageManager;
    }
    
    public function renderRowsList() {       
        $this->getTemplateVariables($this->getUser()->getId());
        
        $this->template->rows = $this->frontPageManager->getRowsFromDB();
        $this->template->frontPageManager = $this->frontPageManager;
    }
    
    public function renderCreateRow() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    public function createComponentCreateRowForm() {        
        $form = new Nette\Application\UI\Form;
        
        $form->addHidden('id');
        
        $form->addText('name', 'název')
                ->setRequired()
                ->setAttribute("class", "form-control");

        $form->addSubmit('send', 'Vytvořit novou pozici')
                ->setAttribute("class", "btn-lg btn-success btn-block");
        

        // call method signInFormSucceeded() on success
        $form->onSuccess[] = $this->rowSucceeded;

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }
    
    
    public function rowSucceeded($form) {
        $vals = $form->getValues();
        
        $status = $this->frontPageManager->saveRowToDB($vals);
        
        if ($status) {
            EventLogger::log('user '.$this->getUser()->getIdentity()
                    ->login.' updated frontpage > row '.$vals->id,
                EventLogger::ACTIONS_LOG);
            
            $this->flashMessage("Změny úspěšně uloženy", "success");
        } else {
            $this->flashMessage("Nic nebylo změněno", "info");
        }
    }
    
    public function actionDeleteRow($id) {
        $this->frontPageManager->deleteRow($id);
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted frontpage > row '.$id, 
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Pozice byla smazána!", "danger");
        $this->redirect("FrontPageManager:RowsList");
    }
    
    public function actionMoveRowUp($id) {
        $this->frontPageManager->moveRow($id, -1);
        $this->flashMessage("Změny úspěšně uloženy", "success");
        $this->redirect("RowsList");
    }
    
    public function actionMoveRowDown($id) {
        $this->frontPageManager->moveRow($id, 1);
        $this->flashMessage("Změny úspěšně uloženy", "success");
        $this->redirect("RowsList");
    }
  
}

