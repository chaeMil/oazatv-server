<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
Model\ArchiveMenuManager,
 App\EventLogger;

/**
 * Description of VideoPresenter
 *
 * @author chaemil
 */
class ArchiveMenuPresenter extends BaseSecuredPresenter {
    
    public $database;
    public $archiveMenuManager;

    function __construct(Nette\Database\Context $database, 
            ArchiveMenuManager $archiveMenuManager) {
        $this->database = $database;
        $this->archiveMenuManager = $archiveMenuManager;
    }
    
    public function renderList() {       
        $this->getTemplateVariables($this->getUser()->getId());
        
        $this->template->menus = $this->archiveMenuManager->getMenusFromDB(2);
    }
    
    public function renderCreateMenu() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    public function renderDetail($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $menu = $this->archiveMenuManager->getMenuFromDB($id, 2);
        $this->template->menu = $menu;
        
        if (!isset($menu['id'])) {
            $this->error('Požadované menu neexistuje!');
        }
        
        $this['menuForm']->setDefaults($menu->toArray());
        
    }
    
    public function createComponentMenuForm() {        
        $form = new Nette\Application\UI\Form;
        
        $published = array(
            '0' => 'Ne',
            '1' => 'Ano',
        );
        
        $form->addText('name_cs', 'název česky')
                ->setRequired()
                ->setAttribute("class", "form-control");

        $form->addText('name_en', 'název anglicky')
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addText('tags', 'tagy')
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addSelect("visible", "zveřejneno")
                ->setItems($published)
                ->setAttribute("class", "form-control");

        $form->addSubmit('send', 'Uložit')
                ->setAttribute("class", "btn-lg btn-success btn-block");

        // call method signInFormSucceeded() on success
        $form->onSuccess[] = $this->menuSucceeded;

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }
    
    
    public function menuSucceeded($form) {
        $vals = $form->getValues();
        
        $status = $this->archiveMenuManager->saveMenuToDB($vals);
        
        if ($status) {
            EventLogger::log('user '.$this->getUser()->getIdentity()
                    ->login.' updated menu '.$vals->id,
                EventLogger::ACTIONS_LOG);
            
            $this->flashMessage("Změny úspěšně uloženy", "success");
        } else {
            $this->flashMessage("Nic nebylo změněno", "info");
        }
        
        $this->redirect("ArchiveMenu:List");
    }
    
    public function actionDeleteMenu($id) {
        $this->archiveMenuManager->deleteMenu($id);
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted menu '.$id, 
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Menu bylo smazáno!", "danger");
        $this->redirect("ArchiveMenu:List");
    }

    public function actionMoveMenuUp($id) {
        $this->archiveMenuManager->moveMenuUp($id);
        $this->redirect("ArchiveMenu:List");
    }

    public function actionMoveMenuDown($id) {
        $this->archiveMenuManager->moveMenuDown($id);
        $this->redirect("ArchiveMenu:List");
    }
}

