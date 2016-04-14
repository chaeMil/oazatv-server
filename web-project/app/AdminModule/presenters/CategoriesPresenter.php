<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\CategoriesManager,
 App\EventLogger;

/**
 * Description of VideoPresenter
 *
 * @author chaemil
 */
class CategoriesPresenter extends BaseSecuredPresenter {
    
    public $database;
    public $categoriesManager;

    function __construct(Nette\Database\Context $database, 
            CategoriesManager $categoriesManager) {
        $this->database = $database;
        $this->categoriesManager = $categoriesManager;
    }
    
    public function renderList() {       
        $this->getTemplateVariables($this->getUser()->getId());
        
        $this->template->categories = $this->categoriesManager
                ->getCategoriesFromDB();
    }
    
    public function renderCreateCategory() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    public function renderDetail($id) {
        $this->getTemplateVariables($this->getUser()->getId());
        $category = $this->categoriesManager->getCategoryFromDB($id);
        $this->template->category = $category;
        
        if (!isset($category['id'])) {
            $this->error('Požadovaná kategorie neexistuje!');
        }
        
        $this['categoryForm']->setDefaults($category->toArray());
        
    }
    
    public function createComponentCategoryForm() {        
        $form = new Nette\Application\UI\Form;
        
        $form->addHidden('id');
        
        $form->addText('name_cs', 'název česky')
                ->setRequired()
                ->setAttribute("class", "form-control");

        $form->addText('name_en', 'název anglicky')
                ->setRequired()
                ->setAttribute("class", "form-control");
        
        $form->addText('color', 'barva (HTML)')
                ->setRequired()
                ->setAttribute("class", "form-control color-picker");

        $form->addSubmit('send', 'Uložit')
                ->setAttribute("class", "btn-lg btn-success btn-block");

        // call method signInFormSucceeded() on success
        $form->onSuccess[] = $this->categorySucceeded;

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }
    
    
    public function categorySucceeded($form) {
        $vals = $form->getValues();
        
        $status = $this->categoriesManager->saveCategoryToDB($vals);
        
        if ($status) {
            EventLogger::log('user '.$this->getUser()->getIdentity()
                    ->login.' updated category '.$vals->id,
                EventLogger::ACTIONS_LOG);
            
            $this->flashMessage("Změny úspěšně uloženy", "success");
        } else {
            $this->flashMessage("Nic nebylo změněno", "info");
        }
    }
    
    public function actionDeleteCategory($id) {
        $this->categoriesManager->deleteCategory($id);
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted category '.$id, 
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Kategorie byla smazána!", "danger");
        $this->redirect("Categories:List");
    }
}

