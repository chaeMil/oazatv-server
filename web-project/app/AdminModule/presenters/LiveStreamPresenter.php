<?php

namespace App\AdminModule;

use Nette,
 Nette\Application\UI\Form,
 Model\LiveStreamManager;

/**
 * Presenter for all common sites in administration
 * 
 * @author Michal Mlejnek <chaemil72@gmail.com>
 */
class LiveStreamPresenter extends BaseSecuredPresenter {
    
    public $database;
    public $liveStreamManager;

    function __construct(Nette\Database\Context $database, 
            LiveStreamManager $liveStreamManager) {
        $this->database = $database;
        $this->liveStreamManager = $liveStreamManager;
    }

    public function renderDefault() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    public function createComponentValuesForm() {        
        $form = new Nette\Application\UI\Form;
        
        $values = $this->liveStreamManager->loadValues();
        
        $translations = array(
            'on_air' => 'OnAir',
            'youtube_link' => 'Youtube video ID např.: M7lc1UVf-VE',
            'bottom_text_cs' => 'Text pod přehrávačem (cz)',
            'bottom_text_en' => 'Text pod přehrávačem (en)',);
        
        $types = array(
            'on_air' => 'on_air',
            'youtube_link' => 'text',
            'bottom_text_cs' => 'textarea',
            'bottom_text_en' => 'textarea',);
        
        foreach($values as $key => $value) {
            
            switch ($types[$key]) {
                case 'text':   
                    $form->addText($key, $translations[$key])
                        ->setValue($value)
                        ->setAttribute("class", "form-control");
                        break;
                case 'textarea':
                    $form->addTextArea($key, $translations[$key])
                        ->setValue($value)
                        ->setAttribute("class", "form-control ckeditor");
                    break;
                case 'on_air':
                    $form->addSelect($key, $translations[$key])
                        ->setItems(array('online' => 'online', 'offline' => 'offline'))
                        ->setValue($value)
                        ->setAttribute("class", "form-control");
                    break;
            }
            
            
        }
        
        $form->addSubmit('send', 'Uložit')
                ->setAttribute("class", "btn-lg btn-success btn-block");
        
        // call method signInFormSucceeded() on success
        $form->onSuccess[] = $this->valuesSucceeded;

        // setup Bootstrap form rendering
        $this->bootstrapFormRendering($form);

        return $form;
    }
    
    public function valuesSucceeded(Form $form) {
        $values = $form->getValues();
        $this->liveStreamManager->saveValues($values);
        
        $this->flashMessage("Úspěšně uloženo!", "success");
        $this->redirect("default");
    }
    
}
    