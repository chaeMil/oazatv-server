<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\FrontPageManager,
 App\EventLogger,
 App\StringUtils;

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
        $this->template->blockDefinitions = $this->frontPageManager->getBlocksDefinitions();
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
    
    public function actionToggleRowPublished($id) {
        $this->frontPageManager->toggleRowPublished($id);
        $this->flashMessage("Změny úspěšně uloženy", "success");
        $this->redirect("RowsList");
    }
    
    public function actionCreateNewBlock($rowId, $type) {
        $values['type'] = $type;
        $blockId = $this->frontPageManager->saveBlockToDB($values);
        $this->frontPageManager->addBlockToRow($blockId, $rowId);
        
        EventLogger::log('user '.$this->getUser()->getIdentity()
                    ->login.' updated frontpage > add block '.$blockId,
                EventLogger::ACTIONS_LOG);
        
        $this->flashMessage("Změny úspěšně uloženy", "success");
        $this->redirect("RowsList");
    }
    
    public function actionDeleteBlock($id) {
        $this->frontPageManager->deleteBlock($id);
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' deleted frontpage > block '.$id, 
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Blok byl smazán!", "danger");
        $this->redirect("RowsList");
    }
    
    public function actionEditBlock($id) {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    public function createComponentEditBlock() {
        
        $definitions = $this->frontPageManager->getBlocksDefinitions();
        $id = $this->getParameter('id');
        $block = $this->frontPageManager->getBlockFromDB($id);
        if($block[FrontPageManager::COLUMN_JSON_DATA] != "") {
            $savedData = $this->frontPageManager
                    ->parseJsonBlock($block[FrontPageManager::COLUMN_JSON_DATA]);
        }
        
        $form = new Nette\Application\UI\Form;
        $form->addHidden('id')->setValue($id);
        
        if(isset($block) && sizeof($definitions) > 0) {
            
            foreach($definitions as $definition) {
                if ($definition['name'] == $block['type']) {
                    
                    $form->addHidden('definition')
                            ->setValue($definition['name']);
                    
                    foreach($definition['inputs'] as $input) {
                        switch($input['type']) {
                            case 'text':
                                if (isset($input['mutations'])) {
                                    $form->addGroup($input['name']);
                                    foreach(explode('|', $input['mutations']) as $mutation) {
                                        
                                        $savedInput = $savedData['inputs'][$input['name']][$mutation];                                        
                                        $form->addText($input['name'].'_'.$mutation, $mutation)
                                                ->setValue($savedInput)
                                                ->setAttribute("class", "form-control");
                                    }
                                } else {
                                    $form->addGroup($input['name']);
                                    
                                    $savedInput = $savedData['inputs'][$input['name']];
                                    $form->addText($input['name'], $input['name'])
                                            ->setValue($savedInput)
                                                ->setAttribute("class", "form-control");
                                }
                                
                                break;
                        }
                    }
                }
            }

            $form->addGroup("");
            $form->addSubmit('send', 'Uložit')
                ->setAttribute("class", "btn-lg btn-success btn-block");
        }
        
        $form->onSuccess[] = $this->editBlockSucceeded;

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
        
    }
    
    public function editBlockSucceeded($form) {
        $vals = $form->getValues();
        
        $jsonData = $this->frontPageManager->createJsonBlock($vals);
        $dbValues = array(
            FrontPageManager::COLUMN_ID => $vals['id'],
            FrontPageManager::COLUMN_TYPE => $vals['definition'],
            FrontPageManager::COLUMN_JSON_DATA => $jsonData);
        
        $this->frontPageManager->saveBlockToDB($dbValues);
        
        EventLogger::log('user '.$this->getUser()->getIdentity()->login.' edited frontpage > block '.$vals['id'], 
                EventLogger::ACTIONS_LOG);
        $this->flashMessage("Blok upraven!", "success");
        $this->redirect("RowsList");
    }
  
}

